<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class CommentController extends AbstractController
{

	#[Route('/commentaires/{id}/supprimer', name: 'app_comment_delete', methods: ['POST'])]
	#[IsGranted('DELETE', 'comment')]
	public function delete(
		Comment                $comment,
		EntityManagerInterface $entityManager,
		Request                $request
	): Response
	{
		if (!$this->isCsrfTokenValid('delete-comment-' . $comment->getId(), $request->request->get('_token')))
		{
			throw $this->createAccessDeniedException('Invalid CSRF token.');
		}

		$topic = $comment->getTopic();

		$entityManager->remove($comment);
		$entityManager->flush();

		return $this->redirectToRoute('app_topic_show', ['id' => $topic->getId()]);
	}

}
