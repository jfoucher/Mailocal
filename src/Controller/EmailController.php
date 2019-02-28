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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class EmailController extends AbstractController
{
    /**
     * @param int $id
     * @Route("/emails/{id}", name="deleteEmail", methods={"DELETE"})
     * @return Response
     */
    public function delete($id)
    {
        $repository = $this->getDoctrine()->getRepository(Email::class);
        $em = $this->getDoctrine()->getManager();
        $email = $repository->find((int)$id);
        /**
         * @var Email $email
         */
        $email->setDeletedAt(new \DateTime());
        $em->persist($email);
        $em->flush();

        return new JsonResponse([
            'status' => 'ok',
            'message' => 'email deleted',
        ], 200);
    }
    /**
     * @param int $id
     * @Route("/emails/markRead/{id}", name="markRead", methods={"PUT"})
     * @return Response
     */
    public function markRead($id)
    {
        $repository = $this->getDoctrine()->getRepository(Email::class);
        $em = $this->getDoctrine()->getManager();
        $email = $repository->find((int)$id);
        /**
         * @var Email $email
         */
        $email->setReadAt(new \DateTime());
        $em->persist($email);
        $em->flush();

        return new JsonResponse([
            'status' => 'ok',
            'message' => 'email marked as read',
        ], 200);
    }

    /**
     * @param int $last
     * @Route("/emails/new/{last}", name="newEmails", methods={"GET"})
     * @return Response
     */
    public function newEmails($last)
    {
        /**
         * @var EmailRepository $repository
         */
        $repository = $this->getDoctrine()->getRepository(Email::class);
        $criteria = [
            'id > ?' => $last
        ];

        $emails = array_map(function ($email) {
            return $this->renderView('partials/email-row.html.twig', ['email' => $email]);
        }, $repository->allOrderedDateDesc($criteria));

        return new Response(join('', $emails));
    }
}
