<?php

namespace App\Security;

use App\Entity\Comment;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CommentVoter extends Voter
{

	const DELETE = 'DELETE';

	/**
	 * @inheritDoc
	 */
	protected function supports(string $attribute, mixed $subject): bool
	{
		return $attribute === self::DELETE && $subject instanceof Comment;
	}

	/**
	 * @inheritDoc
	 */
	protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
	{
		return $token->getUser() && $subject->getAuthor() === $token->getUser();
	}
}