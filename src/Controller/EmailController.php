<?php

namespace App\Controller;

use App\Entity\Email;
use App\Repository\EmailRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EmailController extends AbstractController
{
    protected EmailRepository $emailRepository;
    protected EntityManagerInterface $manager;

    public function __construct(EmailRepository $emailRepository, EntityManagerInterface $manager)
    {
        $this->emailRepository = $emailRepository;
        $this->manager = $manager;
    }

    /**
     * @param int $id
     * @Route("/emails/{id}", name="deleteEmail", methods={"DELETE"})
     * @return Response
     */
    public function delete($id)
    {
        $email = $this->emailRepository->find((int)$id);
        /**
         * @var Email $email
         */
        $email->setDeletedAt(new \DateTime());
        $this->manager->persist($email);
        $this->manager->flush();

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
        $email = $this->emailRepository->find((int)$id);
        /**
         * @var Email $email
         */
        $email->setReadAt(new \DateTime());
        $this->manager->persist($email);
        $this->manager->flush();

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
    public function newEmails($last): Response
    {
        $criteria = [
            'id > ?' => $last
        ];

        $emails = array_map(function ($email) {
            return $this->renderView('partials/email-row.html.twig', ['email' => $email]);
        }, $this->emailRepository->allOrderedDateDesc($criteria));

        return new Response(join('', $emails));
    }
}
