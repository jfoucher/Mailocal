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

use React\Socket\ConnectionInterface;
use Evenement\EventEmitterTrait;
use Evenement\EventEmitterInterface;
use Psr\Log\LoggerInterface;
use React\Socket\Server as ReactServer;
use React\EventLoop\LoopInterface;

/**
 * Listens for incoming SMTP requests.
 */
class Server implements EventEmitterInterface
{
    use EventEmitterTrait;

    /**
     * Ip address to bind.
     *
     * @var string
     */
    protected $host;

    /**
     * SMTP port to listen.
     *
     * @var int
     */
    protected $port;

    /**
     * Logger service.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $log;

    /**
     * Loop service.
     *
     * @var \React\EventLoop\LoopInterface
     */
    protected $loop;

    /**
     * Class to use for SMTP session handling.
     *
     * @var string
     */
    protected $sessionClass;

    /**
     * Constructor, no required parameters if binding to the localhost interface.
     *
     * @param string $host
     *   IP address to bind the server to, defaults to 127.0.0.1.
     * @param int $port
     *   Port to user on the specified host address, defaults to 25.
     * @param LoggerInterface $log
     *   Logger service.
     * @param \React\EventLoop\LoopInterface $loop
     *   Loop service.
     */
    public function __construct(string $host, int $port, LoggerInterface $log, LoopInterface $loop, string $sessionClass)
    {
        $this->log = $log;
        $this->host = $host ?? '127.0.0.1';
        $this->port = $port ?? 25;
        $this->loop = $loop;
        $this->sessionClass = $sessionClass;
        // Issue a warning if the loop service is ReactPHP's default...
        if (is_a($loop, \React\EventLoop\StreamSelectLoop::class)) {
            $this->log->notice("Default loop implementation detected. Please, enable the Event or libEvent extensions for better performance.");
        }
    }

    /**
     * Get the current session class.
     *
     * @return string
     *   The class for SMTP handling.
     */
    public function getSessionClass()
    {
        return $this->sessionClass ?? Session::class;
    }

    /**
     * Start the SMTP server.
     *
     * Starts listening to the specified port.
     */
    public function run()
    {
        $server = new ReactServer($this->host.':'.$this->port, $this->loop);

        // This event triggers every time a new connection comes in.
        $server->on('connection', [$this, 'serverConnectionListener']);

        $this->log->info("Listening to port {$this->port} in host {$this->host}");
    }

    /**
     * Listener for incoming connection events.
     *
     * @param \React\Socket\ConnectionInterface $conn
     *   React connection.
     */
    public function serverConnectionListener(ConnectionInterface $conn)
    {
        $this->log->info("Incoming connection received from: {$conn->getRemoteAddress()}");
        $session_class = $this->getSessionClass();
        /* @var \App\Smtp\SessionInterface $session */
        $session = new $session_class($conn, SmtpSettings::instance(), $this->log, $this->loop);
        $instance = $this;
        // Bubble up from the session to the server.
        $session->on(SessionInterface::EVENT_SMTP_RECEIVED, function (Message $message) use ($instance) {
            $instance->emit(SessionInterface::EVENT_SMTP_RECEIVED, [$message]);
        });
        $session->on(CustomSession::EVENT_SMTP_AUTH_FAILED, function (Message $message, $username, $password) use ($instance) {
            $instance->emit(CustomSession::EVENT_SMTP_AUTH_FAILED, [$message, $username, $password]);
        });
        $session->run();
    }
}
