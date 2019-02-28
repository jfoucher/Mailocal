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
     * @param null|mixed $type
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
                ->setBody(
                    'TEXT TEST EMAIL',
                    'text/plain'
                )
            ;
        } elseif ($type === 'html') {
            $message = (new \Swift_Message('Hello Email'))
                ->setFrom('send@example.com')
                ->setTo('recipient@example.com')
                ->setSubject('HTML email subject')
                ->setBody(
                    '<html><body>HTML ONLY</body></html>',
                    'text/html'
                )
            ;
        } else {
            $message = (new \Swift_Message('Hello Email'))
                ->setFrom('send@example.com')
                ->setTo('recipient@example.com')
                ->setSubject('Both email subject')
                ->setBody(
                    '<html><body>TEST TEST</body></html>',
                    'text/html'
                )
                ->addPart(
                    'TES TESTESTEST',
                    'text/plain'
                )
            ;
        }

        $mailer->send($message);
        return new Response('ok');
    }

    /**
     * @Route("/{email}", name="home")
     * @param null|mixed $email
     * @return Response
     */
    public function index($email = null)
    {
        /**
         * @var EmailRepository $repository
         */
        $repository = $this->getDoctrine()->getRepository(Email::class);
        $criteria = [];
        if ($email) {
            $criteria['to = ?'] = $email;
        }
        //$s = $request->query->get('s');

        $emails = $repository->allOrderedDateDesc($criteria);

        return $this->render('home/index.html.twig', [
            'emails' => $emails,
            'total' => $repository->count(['deletedAt' => null]),
            'recipients' => $repository->getRecipients(),
        ]);
    }
}
