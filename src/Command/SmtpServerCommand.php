<?php
// src/Command/SaveEmail.php

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
use Symfony\Component\Console\Output\OutputInterface;
use App\Email\Parser;
use App\Smtp\CustomServer;
use Symfony\Component\Process\Process;

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
        $process = new Process(
            ['php', 'bin/console','server:run']
        );
        $process->disableOutput();
        $process->start();
        $this->server->setPort(2525);
        $this->server->create();


        $output->writeln('<info>SMTP server now listening for messages on port '.$this->server->getPort().'</info>');
        $this->server->getServer()->on(SessionInterface::EVENT_SMTP_RECEIVED, function(Message $message) use ($output) {
            $parser = new Parser();
            try {
                $msg = $parser->parse($message->data);
                $to = $msg->getTo()->map(function($item) {
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
