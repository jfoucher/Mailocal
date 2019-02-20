<?php
/**
 * CustomServer.php
 *
 * Created By: jonathan
 * Date: 20/02/2019
 * Time: 19:22
 */

namespace App\Smtp;


use Psr\Log\LoggerInterface;
use React\EventLoop\Factory as EventLoopFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CustomServer
{
    protected $logger;
    protected $dispatcher;

    public function __construct(LoggerInterface $logger, EventDispatcherInterface $dispatcher)
    {
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    public function start() {
        $loop = EventLoopFactory::create();
        $server = new ServerSymfony('127.0.0.1', 54321, $this->logger, $loop, CustomSession::class, $this->dispatcher);
        $server->run();
        $loop->run();
    }
}