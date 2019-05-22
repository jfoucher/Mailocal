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
use React\EventLoop\Factory as EventLoopFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CustomServer
{
    protected $logger;
    protected $dispatcher;
    protected $port;
    protected $allowedHost;
    protected $server;
    protected $loop;

    public function __construct(LoggerInterface $logger, EventDispatcherInterface $dispatcher)
    {
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
        $this->loop = EventLoopFactory::create();
    }

    public function create()
    {
        if (!$this->port) {
            $this->port = 54321;
        }
        if (!$this->allowedHost) {
            $this->allowedHost = '127.0.0.1';
        }
        $this->server = new ServerSymfony($this->allowedHost, $this->port, $this->logger, $this->loop, CustomSession::class, $this->dispatcher);
    }

    public function start()
    {
        if (!$this->server) {
            throw new InvalidArgumentException('Please create the server before intenting to run it');
        }
        $this->server->run();
        $this->loop->run();
    }

    /**
     * @return mixed
     */
    public function getServer()
    {
        return $this->server;
    }

    public function setPort($port)
    {
        if ($this->server) {
            throw new InvalidArgumentException('The server has already been created, your port change will not be tken into account');
        }
        $this->port = $port;
        return $this;
    }
    public function getPort()
    {
        return $this->port;
    }

    public function setAllowedHosts($hosts)
    {
        if ($this->server) {
            throw new InvalidArgumentException('The server has already been created, your port change will not be tken into account');
        }
        $this->allowedHost = $hosts;
        return $this;
    }
    public function getAllowedHosts()
    {
        return $this->allowedHost;
    }
}
