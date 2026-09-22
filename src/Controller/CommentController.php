<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentDeleteType;
use App\Repository\CommentRepository;
use App\Voter\CommentVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CommentController extends AbstractController
{

    #[Route('/commentaires/{id}/supprimer', name: 'app_comment_delete', methods: ['POST'])]
    public function delete(
        string                 $id,
        Request                $request,
        EntityManagerInterface $entityManager,
        CommentRepository      $commentRepository
    ): Response
    {
        if (null === $comment = $commentRepository->findOneBy(['id' => $id])) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted(CommentVoter::DELETE, $comment);

        $form = $this->createForm(CommentDeleteType::class, null, [
            'csrf_token_id' => 'comment_delete_' . $comment->getId(),
        ]);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            throw $this->createAccessDeniedException('Requête invalide.');
        }

        $topic = $comment->getTopic();

        $entityManager->remove($comment);
        $entityManager->flush();

        return $this->redirectToRoute('app_topic_show', ['id' => $topic->getId()]);
    }

}
