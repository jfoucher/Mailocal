<?php
/**
 * SmtpReceivedSubscriber.php
 *
 * Created By: jonathan
 * Date: 20/02/2019
 * Time: 19:52
 */
namespace App\EventSubscriber;
use App\Email\Parser;
use App\Entity\Email;
use App\Smtp\MessageReceivedEvent;
use App\Smtp\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SmtpReceivedSubscriber implements EventSubscriberInterface
{
    protected $logger;
    protected $em;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $em)
    {
        $this->logger = $logger;
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return [
            SessionInterface::EVENT_SMTP_RECEIVED => [
                ['processSmtpConnection', 10],
            ]
        ];
    }

    public function processSmtpConnection(MessageReceivedEvent $event)
    {
        $event->message->delivered = true;
        $parser = new Parser();
        $message = $parser->parse($event->message->data);
        $email = new Email();
        $email->setHeaders(explode("\n\n", $event->message->data)[0]);
        $email->setHtml($message->getHtmlBody());
        $email->setText($message->getTextBody());
        $this->logger->info($message->getTextBody());
        $email->setSubject(mb_decode_mimeheader($message->getSubject()));
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
            $this->logger->info('Email saved');
        }catch (\Exception $e) {
            $this->logger->error('Email NOT saved');
        }
    }
}