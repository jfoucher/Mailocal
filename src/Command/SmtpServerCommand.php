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
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Style\SymfonyStyle;

class SmtpServerCommand extends Command
{
    protected static $defaultName = 'email:server';
    protected $server;

    public function __construct(?string $name = null, CustomServer $server)
    {
        parent::__construct($name);
        $this->server = $server;
    }

    protected function configure()
    {
        $this
            ->setDescription('SMTP server.')

        ->addOption(
            'port',
            'p',
            InputOption::VALUE_OPTIONAL,
            'Which port should the SMTP server run on?',
            getenv('SMTP_SERVER_PORT')
        )

        ->addOption(
            'allowed_hosts',
            'ah',
            InputOption::VALUE_OPTIONAL,
            'Which ip addresses should be allowed to connect to this server?',
            getenv('SMTP_SERVER_ALLOWED_HOSTS')
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->server->setPort($input->getOption('port'));
        $this->server->setAllowedHosts($input->getOption('allowed_hosts'));
        $this->server->create();

        $io = new SymfonyStyle($input, $output);
        $io->success('SMTP server now listening for messages from '.$this->server->getAllowedHosts().' on port '.$this->server->getPort());

        $this->server->getServer()->on(SessionInterface::EVENT_SMTP_RECEIVED, function (Message $message) use ($output) {
            $parser = new Parser();
            try {
                $msg = $parser->parse($message->data);
                $to = $msg->getTo()->map(function ($item) {
                    return $item->getAddress();
                })->toArray();
                $output->writeln('<info>Received message for <options=underscore>'.join(', ', $to). '</>: <options=bold>'.mb_decode_mimeheader($msg->getSubject()).'</></info>');
            } catch (InvalidAttachmentException $e) {
                $output->writeln([
                    '<error>Received message with invalid attachment</error>',
                    '<error>' . $e->getMessage() . '</error>'
                ]);
            }
        });
        $this->server->start();
    }
}
