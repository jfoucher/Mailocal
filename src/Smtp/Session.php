<?php

/*
 * This file is part of the Maillocal package.
 *
 * Copyright 2019 Jonathan Foucher
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @package Mailocal
 */

namespace App\Smtp;

use Psr\Log\LoggerInterface;
use Evenement\EventEmitterTrait;
use React\Socket\ConnectionInterface;
use React\EventLoop\LoopInterface;

/**
 * Process an incoming SMTP request.
 *
 * Authentication and delivery where not implemented as
 * pluggable service because they are too tightly
 * coupled with the session itself.
 */
abstract class Session implements SessionInterface
{
    use EventEmitterTrait;

    /**
     * Data message end of file.
     */
    const DATA_EOF = "\r\n.\r\n";

    /**
     * Timer to enforce a maximum session duration.
     *
     * @var \React\EventLoop\TimerInterface | null
     */
    protected $timer;

    /**
     * Log service.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $log;

    /**
     * React connection.
     *
     * @var \React\Socket\ConnectionInterface | null
     */
    protected $socket;

    /**
     * Message data.
     *
     * @var \App\Smtp\Message
     */
    protected $message;

    /**
     * Summary of $callback.
     *
     * @var callable|null
     */
    protected $callback;

    /**
     * Settings.
     *
     * @var SmtpSettings
     */
    protected $settings;

    /**
     * Loop instance.
     *
     * @var \React\EventLoop\LoopInterface
     */
    protected $loop;

    /**
     * Incoming DATA command size.
     *
     * @var int
     */
    protected $size;

    /**
     * Incoming DATA command data.
     *
     * @var string | null
     */
    protected $data;

    protected $from;
    protected $to;

    /**
     * Initialize al internal buffers and behaviours.
     *
     * Used during RSET command and construction.
     */
    protected function initialize()
    {
        $this->callback = null;
        $this->message = new Message();
    }

    /**
     * {@inheritdoc}
     */
    public function __construct(ConnectionInterface $socket, SmtpSettings $settings, LoggerInterface $log, LoopInterface $loop)
    {
        $this->log = $log;
        $this->socket = $socket;
        $this->settings = $settings;
        $this->initialize();
        $this->loop = $loop;
        $this->resetTimer(15);
    }

    /**
     * Reset expiration timer.
     *
     * @param int $timeout
     *   Timeout in seconds.
     */
    protected function resetTimer(int $timeout)
    {
        if ($this->timer) {
            $this->loop->cancelTimer($this->timer);
            $this->timer = null;
        }
        $this->timer = $this->loop->addTimer($timeout, [$this, 'onTimerExpired']);
    }

    /**
     * Handler for a session timer expired.
     */
    public function onTimerExpired()
    {
        $this->log->warning("SMTP session closed due to time out.");
        $this->quitCommand('');
    }

    /**
     * Write a line to the socket stream.
     *
     * @param string $line
     *   Text to write to socket.
     */
    protected function socketWriteLine($line)
    {
        $this->log->info("S: " . $line);
        $this->socket->write($line . "\r\n");
    }

    /**
     * Write a line to the socket stream.
     *
     * @param string $line
     *   Text to write to socket.
     */
    protected function socketEndLine($line)
    {
        $this->socket->end($line . "\r\n");
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->socketWriteLine($this->settings->smtp220);
        $this->socket->on('data', [$this, 'dataConnectionEventHandler']);
        $this->socket->on('close', [$this, 'closeConnectionEventHandler']);
    }

    /**
     * Handler for incoming data.
     *
     * @param string $chunk
     *   Incoming data chunk.
     */
    public function dataConnectionEventHandler(string $chunk)
    {
        // 10 seconds inactivity timeout.
        $this->resetTimer(10);
        // On the fly handlers for commands.
        if ($this->callback != null) {
            $callback = $this->callback;
            $this->callback = null;
            $this->log->info("C: <<< " . rtrim($chunk, "\r\n"));
            call_user_func_array($callback, [$chunk]);
            return;
        }
        $cmd = substr($chunk, 0, 4);
        $arg = trim(substr($chunk, 5));
        $this->dispatch($cmd, $arg);
    }

    /**
     * Summary of closeConnectionEventHandler.
     */
    public function closeConnectionEventHandler()
    {
        // Clear the socket.
        $this->socket->removeAllListeners();
        $this->socket = null;
        // Cancel the timeout timer.
        $this->loop->cancelTimer($this->timer);
        $this->timer = null;
        // Log disconnection.
        $this->log->info("Connection closed.");
    }

    /**
     * Register a callback handler to catch next message from client.
     *
     * @param callable $callback
     *   Callback to process next incoming message.
     */
    protected function registerCallbackHandler(callable $callback)
    {
        if ($this->callback != null) {
            // We cannot have overlaping callbacks,
            // something is wrong.
            $this->log->error("Overlapping callback detected.");
        }
        $this->callback = $callback;
    }

    /**
     * Dispatch a command for processing.
     *
     * @param string $cmd
     *   SMTP command.
     * @param string $arg
     *   Command arguments.
     */
    protected function dispatch(string $cmd, string $arg)
    {
        $this->log->info("C: {$cmd} " . mb_strimwidth($arg, 0, 50, "..."));
        $cmd = strtoupper($cmd);
        switch ($cmd) {
          case 'HELO':
            $this->heloCommand($arg);
            break;

          case 'EHLO':
            $this->ehloCommand($arg);
            break;

          case 'AUTH':
            $this->authCommand($arg);
            break;

          case 'MAIL':
            $this->mailCommand($arg);
            break;

          case 'RCPT':
            $this->rcptCommand($arg);
            break;

          case 'DATA':
            // Data command might take longer
            // with big attachments and the such.
            $this->resetTimer(20);
            $this->dataCommand($arg);
            break;

          case 'RSET':
            $this->rsetCommand($arg);
            break;

          case 'HELP':
            $this->helpCommand($arg);
            break;

          case 'QUIT':
            $this->quitCommand($arg);
            break;

          case 'NOOP':
            $this->noopCommand($arg);
            break;

          default:
            $this->notImplemented();
        }
    }

    /**
     * Process an EHLO command.
     *
     * @param string $arg
     *   Command arguments.
     */
    protected function ehloCommand(string $arg)
    {
        $this->socketWriteLine('250-This host supports a few commands');
        // We don't support CRAM-5 authentication because
        // this SMTP is designed to authenticate against
        // an external service (another SMTP, Amazon) and so
        // supporting this type of auth would require sincronyzing
        // the auth against the external service (i.e. obtain the challange
        // first from the remote server...) which is a challange, or impossible
        // to deal with if you do not have access
        // on the remote system.
        $this->socketWriteLine('250-AUTH LOGIN PLAIN');
        $this->socketWriteLine('250 HELP');
    }

    /**
     * Process a HELO command.
     *
     * @param string $arg
     *   Command arguments.
     */
    protected function heloCommand(string $arg)
    {
        $this->socketWriteLine($this->settings->smtp250);
    }

    /**
     * Process an AUTH command.
     *
     * @param string $arg
     *   Command arguments.
     */
    protected function authCommand(string $arg)
    {
        if (strcasecmp(substr($arg, 0, 5), 'PLAIN') === 0) {
            $this->authPlain(substr($arg, 6));
            return;
        }

        if (strcasecmp(substr($arg, 0, 5), 'LOGIN') === 0) {
            $this->authLogin(substr($arg, 6));
            return;
        }

        $this->socketWriteLine($this->settings->smtp504);
    }

    /**
     * Login auth.
     *
     * @param string $arg
     *   Command arguments.
     */
    protected function authLogin(string $arg)
    {
        if (!empty($arg)) {
            $this->authLoginUsernameCallback($arg);
            return;
        }
        $this->socketWriteLine("334 " . base64_encode("Username"));
        $this->registerCallbackHandler([$this, 'authLoginUsernameCallback']);
    }

    /**
     * AUTH LOGIN username callback.
     *
     * @param string $arg
     *   Input argument.
     */
    public function authLoginUsernameCallback($arg)
    {
        $this->message->username = base64_decode(trim($arg, "\r\n"));
        $this->socketWriteLine("334 " . base64_encode("Password"));
        $this->registerCallbackHandler([$this, 'authPasswordCallback']);
    }

    /**
     * AUTH LOGIN password callback.
     *
     * @param string $arg
     *   Input argument.
     */
    public function authPasswordCallback($arg)
    {
        $this->message->password = base64_decode(trim($arg, "\r\n"));
        $this->auth('', $this->message->username, $this->message->password);
    }

    /**
     * Plain auth command handler.
     *
     * @param string $arg
     *   Command arguments.
     */
    protected function authPlain(string $arg)
    {
        // Some clients directly blast the authentication details.
        if (!empty($arg)) {
            $this->authPlainReceived($arg);
            return;
        }
        $this->socketWriteLine("334");
        $instance = $this;
        $this->registerCallbackHandler(function ($arg) use ($instance) {
            $instance->authPlainReceived($arg);
        });
    }

    /**
     * Plain auth command execution.
     *
     * @param string $arg
     *   Command argument.
     */
    protected function authPlainReceived(string $arg)
    {
        $arg = (string)base64_decode($arg);
        // For this to be valid the null character needs to be there at least twice.
        if (substr_count($arg, chr(0)) !== 2) {
            $this->socketWriteLine($this->settings->smtp535);
            return;
        }
        list($auth_id, $user_id, $password) = explode(chr(0), $arg);
        if (empty($user_id) || empty($password)) {
            $this->socketWriteLine($this->settings->smtp535);
            return;
        }
        $this->auth($auth_id, $user_id, $password);
    }

    /**
     * Auth.
     *
     * Stores authentication credentials and calls
     * the real authentication implementation.
     *
     * @param string $auth_id
     *   Authentication type.
     * @param string $user_id
     *   User id.
     * @param string $password
     *   Password.
     */
    protected function auth(string $auth_id, string $user_id, string $password)
    {
        $this->message->username = $user_id;
        $this->message->password = $password;
        if ($this->authenticate($auth_id, $user_id, $password)) {
            $this->socketWriteLine($this->settings->smtp235);
        } else {
            $this->socketWriteLine($this->settings->smtp535);
        }
    }

    /**
     * Authenticate the user.
     *
     * Authentication needs not to be done here (but could).
     *
     * Credentials are stored in the message object
     * so that you can authenticate (and deliver)
     * later.
     *
     * @param string $auth_id
     *   Authentication type.
     * @param string $user_id
     *   User id.
     * @param string $password
     *   Password.
     *
     * @return bool
     *    True if authentication was successful (or omitted).
     */
    abstract protected function authenticate(string $auth_id, string $user_id, string $password) : bool;

    /**
     * Process a MAIL command.
     *
     * @param string $arg
     *   Command arguments.
     */
    protected function mailCommand(string $arg)
    {
        $arg = trim($arg, 'FfRrOoMm: <>');
        $arr = explode('@', $arg);

        $this->from['user'] = $arr[0];
        $this->from['domain'] = $arr[1];

        $this->socketWriteLine($this->settings->smtp250);
    }

    /**
     * Process an RCPT command.
     *
     * @param string $arg
     *   Command arguments.
     */
    protected function rcptCommand(string $arg)
    {
        $arg = trim($arg, 'TtOo: <>');
        $arr = explode('@', $arg);

        $to = ['user' => $arr[0], 'domain' => $arr[1]];

        $this->to[] = $to;
        $this->socketWriteLine($this->settings->smtp250);
    }

    /**
     * Process a DATA command.
     *
     * @param string $arg
     *   Command arguments.
     */
    protected function dataCommand(string $arg)
    {

    // Must have a recipient before sending.
        if (!$this->to) {
            $this->socketWriteLine($this->settings->smtp503);
            return;
        }

        $this->socketWriteLine($this->settings->smtp354);

        // We need a chained callback for this.
        $this->registerCallbackHandler([$this, 'onDataReceived']);
    }

    /**
     * Process incoming data from DATA command.
     *
     * @param string $arg
     *   Incoming data chunk.
     */
    protected function onDataReceived(string $arg)
    {

    // If we exceed size quit.
        $this->size += strlen($arg);
        if ($this->size > $this->settings->smtpMaxSize) {
            $this->socketWriteLine($this->settings->smtp552);
            return;
        }

        $this->data .= $arg;

        // When all message contents have been sent, a single dot (“.”)
        // must be sent in a line by itself.
        // TODO: Optimize this :(.
        if (substr($this->data, -5) == self::DATA_EOF) {
            $this->size = $this->size - strlen(self::DATA_EOF);
            $this->data = substr($this->data, 0, $this->size);
            $this->dataFinished($this->data);
            $this->data = null;
            return;
        }

        // Keep reading.
        $this->registerCallbackHandler([$this, 'onDataReceived']);
    }

    /**
     * Callback for DATA command end.
     *
     * @param string $data
     *   Mail message body/data.
     */
    protected function dataFinished(string $data)
    {
        $this->message->data = $data;
        $result = $this->dataFinishedHandler();
        if (empty($result)) {
            $this->message->delivered = true;
            $this->socketWriteLine('250 OK, message accepted for delivery.');
        } else {
            $this->socketWriteLine('554 ' . $result);
        }
        $this->emit(SessionInterface::EVENT_SMTP_RECEIVED, [$this->message]);
    }

    /**
     * Called after the DATA command has finished.
     *
     * This method should process the incoming message
     * (store, validate, relay, whatever) and write
     * to the socket either a 250
     *
     * or a 5xx.
     *
     * @return string
     *   Empty string if the action was succesful, otherwise
     *   a description of the error (will be sent back to the
     *   SMTP client).
     */
    abstract protected function dataFinishedHandler() : string;

    /**
     * Process a RSET command.
     *
     * @param string $arg
     *   Command arguments.
     */
    protected function rsetCommand(string $arg)
    {
        $this->initialize();
        $this->socketWriteLine($this->settings->smtp250);
    }

    /**
     * Process a HELP command.
     *
     * @param string $arg
     *   Command arguments.
     */
    protected function helpCommand(string $arg)
    {
        $this->socketWriteLine($this->settings->smtp250);
    }

    /**
     * Process a NOOP command.
     *
     * @param string $arg
     *   Command arguments.
     */
    protected function noopCommand(string $arg)
    {
        $this->socketWriteLine($this->settings->smtp250);
    }

    /**
     * Process a QUIT command.
     *
     * @param string $arg
     *   Command arguments.
     */
    protected function quitCommand(string $arg)
    {
        $this->socketEndLine($this->settings->smtp221);
    }

    /**
     * Response for not implemented SMTP command.
     */
    protected function notImplemented()
    {
        $this->socketWriteLine($this->settings->smtp502);
    }

    /**
     * {@inheritdoc}
     */
    public function dispose()
    {
        if ($this->timer) {
            $this->loop->cancelTimer($this->timer);
            $this->timer = null;
        }
        $this->callback = null;
    }
}
