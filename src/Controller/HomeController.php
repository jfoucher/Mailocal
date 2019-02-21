<?php

namespace App\Controller;

use App\Entity\Email;
use App\Repository\EmailRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @param \Swift_Mailer $mailer
     * @Route("/mail/{type}", name="mail")
     * @return Response
     */
    public function mail(\Swift_Mailer $mailer, $type = null)
    {
        if ($type === 'text') {
            $message = (new \Swift_Message('Hello Email'))
                ->setFrom('send@example.com')
                ->setTo('recipient@example.com')
                ->setSubject('Text email subject')
                ->setBody('TEXT TEST EMAIL',
                    'text/plain'
                )
            ;

        } elseif ($type === 'html') {
            $message = (new \Swift_Message('Hello Email'))
                ->setFrom('send@example.com')
                ->setTo('recipient@example.com')
                ->setSubject('HTML email subject')
                ->setBody('<html><body>HTML ONLY</body></html>',
                    'text/html'
                )
            ;

        } else {
            $message = (new \Swift_Message('Hello Email'))
                ->setFrom('send@example.com')
                ->setTo('recipient@example.com')
                ->setSubject('Both email subject')
                ->setBody('<html><body>TEST TEST</body></html>',
                    'text/html'
                )
                ->addPart('TES TESTESTEST',
                    'text/plain'
                )
            ;
        }

        $mailer->send($message);
        return new Response('ok');
    }

    /**
     * @Route("/{email}", name="home")
     */
    public function index(Request $request, $email = null)
    {
        /**
         * @var EmailRepository $repository
         */
        $repository = $this->getDoctrine()->getRepository(Email::class);
        $criteria = [];
        if ($email) {
            $criteria['to'] = $email;
        }
        $s = $request->query->get('s');
        if ($s) {
            $criteria[''];
        }
        $emails = $repository->allOrderedDateDesc($criteria);

        return $this->render('home/index.html.twig', [
            'emails' => $emails,
            'total' => $repository->count(['deletedAt' => null]),
            'recipients' => $repository->getRecipients(),
        ]);
    }

}
