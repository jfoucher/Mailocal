<?php

namespace App\Controller;

use App\Entity\Email;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('home/index.html.twig', [
            'emails' => $this->getDoctrine()->getRepository(Email::class)->findAll(),
        ]);
    }

    /**
     * @param \Swift_Mailer $mailer
     * @Route("/mail", name="mail")
     * @return Response
     */
    public function mail(\Swift_Mailer $mailer)
    {
        $message = (new \Swift_Message('Hello Email'))
            ->setFrom('send@example.com')
            ->setTo('recipient@example.com')
            ->setSubject('test subject')
            ->setBody('<html><body>TEST TEST</body></html>',
                'text/html'
            )
            ->addPart('TES TESTESTEST',
                'text/plain'
            )
        ;

        $mailer->send($message);
        return new Response('ok');
    }
}
