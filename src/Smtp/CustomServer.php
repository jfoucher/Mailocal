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
    protected $port;
    protected $server;
    protected $loop;

    public function __construct(LoggerInterface $logger, EventDispatcherInterface $dispatcher)
    {
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
        $this->loop = EventLoopFactory::create();
    }

    public function create() {
        if (!$this->port) {
            $this->port = 54321;
        }
        $this->server = new ServerSymfony('127.0.0.1', $this->port, $this->logger, $this->loop, CustomSession::class, $this->dispatcher);
    }

    public function start()
    {
        if (!$this->server){
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

    public function setPort($port) {
        if ($this->server){
            throw new InvalidArgumentException('The server has already been created, your port change will not be tken into account');
        }
        $this->port = $port;
        return $this;
    }
    public function getPort()
    {
        return $this->port;
    }

}