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

use React\EventLoop\LoopInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Symfony compatible Server.
 *
 * Extends the Server implementation to add support
 * for Symfony's event dispatcher.
 */
class ServerSymfony extends Server
{

  /**
   * Event dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
    protected $dispatcher;

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
    public function __construct(string $host, int $port, LoggerInterface $log, LoopInterface $loop, string $sessionClass, EventDispatcherInterface $dispatcher)
    {
        parent::__construct($host, $port, $log, $loop, $sessionClass);
        $this->dispatcher = $dispatcher;
        $this->on(SessionInterface::EVENT_SMTP_RECEIVED, [$this, 'onMessageReceived']);
        $this->on(CustomSession::EVENT_SMTP_AUTH_FAILED, [$this, 'onAuthFailed']);
    }

    /**
     * Listener for message received.
     *
     * @param Message $message
     *   Mail message.
     */
    public function onMessageReceived(Message $message)
    {
        // It will also be fun to dispatch this as a symfony event.
        $event = new MessageReceivedEvent($message);
        $this->dispatcher->dispatch(SessionInterface::EVENT_SMTP_RECEIVED, $event);
    }

    /**
     * Listener for auth failed.
     *
     * @param Message $message
     *   Mail message.
     * @param mixed $username
     * @param mixed $password
     */
    public function onAuthFailed(Message $message, $username, $password)
    {
        $event = new AuthFailedEvent($message, $username, $password);
        $this->dispatcher->dispatch(CustomSession::EVENT_SMTP_AUTH_FAILED, $event);
    }
}
