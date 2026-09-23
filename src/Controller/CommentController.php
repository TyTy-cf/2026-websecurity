<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Security\CommentVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class CommentController extends AbstractController
{

    #[Route('/commentaires/{id}/supprimer', name: 'app_comment_delete', methods: ['POST'])]
    #[IsGranted(CommentVoter::DELETE, 'comment')]
    #[IsCsrfTokenValid('delete-item', tokenKey: 'token')]
    public function delete(
        Comment                 $comment,
        EntityManagerInterface $entityManager,
        CommentRepository      $commentRepository
    ): Response
    {
        $topic = $comment->getTopic();

        $entityManager->remove($comment);
        $entityManager->flush();

        return $this->redirectToRoute('app_topic_show', ['id' => $topic->getId()]);
    }

}
