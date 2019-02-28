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

use Evenement\EventEmitterInterface;

use Psr\Log\LoggerInterface;
use React\Socket\ConnectionInterface;
use React\EventLoop\LoopInterface;

/**
 * SMTP session interface.
 */
interface SessionInterface extends EventEmitterInterface
{
    const EVENT_SMTP_RECEIVED = 'smtp.received';

    /**
     * Bind to events and start communicating.
     */
    public function run();

    /**
     * Dispose.
     */
    public function dispose();

    /**
     * Constructor.
     *
     * @param ConnectionInterface $socket
     *   Socket connection.
     * @param SmtpSettings $settings
     *   SMTP Settings.
     * @param LoggerInterface $log
     *   Log service.
     * @param LoopInterface $loop
     *   Loop service.
     */
    public function __construct(ConnectionInterface $socket, SmtpSettings $settings, LoggerInterface $log, LoopInterface $loop);
}
