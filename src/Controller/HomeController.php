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
        $emails = array_map(function($email) {
            /**
             * @var Email $email
             */
            $email->firstLine = explode("\n", $email->getText())[0];
            return $email;
        }, $emails);

        return $this->render('home/index.html.twig', [
            'emails' => $emails,
            'total' => $repository->count(['deletedAt' => null]),
            'recipients' => $repository->getRecipients(),
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
