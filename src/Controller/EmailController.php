<?php

namespace App\Controller;

use App\Entity\Email;
use App\Repository\EmailRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

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
        $email->setDeletedAt(new \DateTime());
        $em->persist($email);
        $em->flush();

        return new JsonResponse([
            'status' => 'ok',
            'message' => 'email deleted',
        ], 200);
    }
}
