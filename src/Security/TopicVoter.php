<?php

namespace App\Security;

use App\Entity\Topic;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TopicVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';
    const ADD = 'add';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (self::ADD === $attribute) {
            return true;
        }

        if (!in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }

        if (!$subject instanceof Topic) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        /** @var Topic $topic */
        $topic = $subject;

        return match($attribute) {
            self::VIEW => $this->canView(),
            self::EDIT => $this->canEdit($topic, $token, $vote),
            self::ADD => $this->canAdd($token, $vote),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    private function canView(): bool
    {
        return true;
    }

    private function canEdit(Topic $topic, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            $vote?->addReason('The user is not logged in.');
            return false;
        }

        if ($user === $topic->getAuthor()) {
            return true;
        }

        $vote?->addReason(sprintf(
            'The logged in user (username: %s) is not the author of this topic (id: %d).',
            $user->getNickname(), $topic->getId()
        ));

        return false;
    }

    private function canAdd(TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            $vote?->addReason('The user is not logged in.');
            return false;
        }

        return true;
    }
}