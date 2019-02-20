<?php
// src/Command/SaveEmail.php

namespace App\Command;

use App\Entity\Email;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Email\Parser;
use App\Smtp\CustomServer;

class SmtpServerCommand extends Command
{
    protected static $defaultName = 'email:server';
    protected $server;

    public function __construct(?string $name = null, CustomServer $server) {
        parent::__construct($name);
        $this->server = $server;
    }

    protected function configure()
    {
        $this
            ->setDescription('SMTP server.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->server->start();
    }
}
