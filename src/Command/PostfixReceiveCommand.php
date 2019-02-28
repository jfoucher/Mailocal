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

    /**
     * PostfixReceiveCommand constructor.
     *
     * @param string|null $name
     * @param LoggerInterface $logger
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(?string $name = null, LoggerInterface $logger, EventDispatcherInterface $dispatcher)
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
        $this->addArgument('username', InputArgument::OPTIONAL);
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
        $this->logger->info($input->getArgument('username'));
        $message = new Message();
        $message->data = $contents;
        $message->username = $input->getArgument('username');

        $this->dispatcher->dispatch(CustomSession::EVENT_SMTP_RECEIVED, new MessageReceivedEvent($message));
    }
}
