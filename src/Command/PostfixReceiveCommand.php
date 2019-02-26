<?php
// src/Command/SaveEmail.php

namespace App\Command;

use App\Email\InvalidAttachmentException;
use App\Entity\Email;
use App\EventSubscriber\SmtpReceivedSubscriber;
use App\Smtp\CustomSession;
use App\Smtp\Message;
use App\Smtp\MessageReceivedEvent;
use App\Smtp\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Email\Parser;
use App\Smtp\CustomServer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Process\Process;

class PostfixReceiveCommand extends Command
{
    protected static $defaultName = 'email:postfix';
    protected $logger;
    protected $dispatcher;

    public function __construct($name = null, LoggerInterface $logger, EventDispatcherInterface $dispatcher)
    {
        parent::__construct($name);
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    protected function configure()
    {
        $this->setDescription('Email piped by postfix is handled here');
        $this->addArgument('sender');
        $this->addArgument('size');
        $this->addArgument('recipient');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger->info('Received an email from postfix');
        if (0 === ftell(STDIN)) {
            $contents = '';
            while (!feof(STDIN)) {
                $contents .= fread(STDIN, 1024);
            }
        } else {
            throw new \RuntimeException("Please provide a filename or pipe template content to STDIN.");
        }
        $this->logger->info($input->getArgument('sender'));
        $this->logger->info($input->getArgument('size'));
        $this->logger->info($input->getArgument('recipient'));
        $message = new Message();
        $message->data = $contents;
        $this->dispatcher->dispatch(CustomSession::EVENT_SMTP_RECEIVED, new MessageReceivedEvent($message));
    }
}
