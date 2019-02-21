<?php
// src/EventSubscriber/MenuBuilderSubscriber.php
namespace App\EventSubscriber;

use App\Repository\EmailRepository;
use Doctrine\ORM\EntityManagerInterface;
use KevinPapst\AdminLTEBundle\Event\SidebarMenuEvent;
use KevinPapst\AdminLTEBundle\Event\ThemeEvents;
use KevinPapst\AdminLTEBundle\Model\MenuItemModel;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MenuBuilderSubscriber implements EventSubscriberInterface
{
    protected $logger;
    protected $repo;

    public function __construct(LoggerInterface $logger, EmailRepository $repo)
    {
        $this->logger = $logger;
        $this->repo = $repo;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ThemeEvents::THEME_SIDEBAR_SETUP_MENU => ['onSetupMenu', 100],
        ];
    }

    public function onSetupMenu(SidebarMenuEvent $event)
    {
        $recipients = $this->repo->getRecipients();
        $total = $this->repo->count(['deletedAt' => null]);

        $inbox = new MenuItemModel('all_recipients', 'recipients', 'home', [], 'fas fa-users');
        $all = new MenuItemModel('all',
            'all_recipients',
            'home',
            [],
            'fas fa-inbox',
            $total,
            'green'
        );
        $email = $event->getRequest()->attributes->get('email');
        if (!$email) {
            $all->setIsActive(true);
        }
        $inbox->addChild(
            $all
        );

        if ($event->getRequest()->get('_route') === $inbox->getRoute()){
            $inbox->setIsActive(true);
        }


        foreach($recipients as $recipient) {
            $el = new MenuItemModel($recipient['to'],
                $recipient['to'],
                'home',
                ['email' => $recipient['to']],
                'fa fa-envelope',
                $recipient['num_messages'],
                'blue'
            );
            if ($email === $recipient['to']) {
                $el->setIsActive(true);
            }
            $inbox->addChild(
                $el
            );
        }

        $event->addItem($inbox);
    }

    /**
     * @param string $route
     * @param MenuItemModel[] $items
     */
    protected function activateByRoute($route, $items)
    {
        foreach ($items as $item) {
            if ($item->hasChildren()) {
                $this->activateByRoute($route, $item->getChildren());
            } elseif ($item->getRoute() == $route) {
                $item->setIsActive(true);
            }
        }
    }
}