<?php

namespace App\Controller;

use App\Entity\Email;
use App\Repository\EmailRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    protected EmailRepository $emailRepository;

    public function __construct(EmailRepository $emailRepository)
    {
        $this->emailRepository = $emailRepository;
    }

    /**
     * @Route("/{email}", name="home")
     * @param null|mixed $email
     * @return Response
     */
    public function index($email = null)
    {
        $criteria = [];
        if ($email) {
            $criteria['to = ?'] = $email;
        }
        //$s = $request->query->get('s');

        $emails = $this->emailRepository->allOrderedDateDesc($criteria);

        return $this->render('home/index.html.twig', [
            'emails' => $emails,
            'total' => $this->emailRepository->count(['deletedAt' => null]),
            'recipients' => $this->emailRepository->getRecipients(),
        ]);
    }
}
