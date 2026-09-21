<?php

namespace App\Security;

use App\Entity\Topic;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TopicVoter extends Voter
{
	const EDIT = 'EDIT';

	/**
	 * @inheritDoc
	 */
	protected function supports(string $attribute, mixed $subject): bool
	{
		return $subject instanceof Topic && $attribute === self::EDIT;
	}

	/**
	 * @inheritDoc
	 */
	protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
	{
		return $token->getUser() && $subject->getAuthor() === $token->getUser();
	}
}