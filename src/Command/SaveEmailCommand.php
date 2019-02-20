<?php
// src/Command/SaveEmail.php

namespace App\Command;

use App\Entity\Email;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use MS\Email\Parser\Parser;

class SaveEmailCommand extends Command
{
    protected static $defaultName = 'email:save';
    private $em;

    public function __construct(?string $name = null, EntityManagerInterface $em) {
        parent::__construct($name);

        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Save emails from postfix.')
            ->setHelp('This command saves emails from postfix')

            ->addArgument('from', InputArgument::REQUIRED, 'From?')
            ->addArgument('size', InputArgument::OPTIONAL, 'Size?')
            ->addArgument('to', InputArgument::OPTIONAL, 'To?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (0 === ftell(STDIN)) {
            $contents = '';
            while (!feof(STDIN)) {
                $contents .= fread(STDIN, 1024);
            }
        } else {
            throw new \RuntimeException("Please provide full email text to STDIN.");
        }

        $parser = new Parser();
        $message = $parser->parse($contents);
        $email = new Email();
        $email->setHeaders(explode("\n\n", $contents)[0]);
        $email->setHtml($message->getHtmlBody());
        $email->setText($message->getTextBody());
        $email->setSubject($message->getSubject());
        $email->setFrom($message->getFrom()->getAddress());
        $email->setFromName($message->getFrom()->getName());
        $to = $message->getTo()->map(function($item) {
            return $item->getAddress();
        })->toArray();
        $email->setTo(join(', ', $to));
        $email->setCreatedAt($message->getDateAsDateTime());
        try {
            $this->em->persist($email);
            $this->em->flush();
        }catch (\Exception $e) {

        }

    }
}
