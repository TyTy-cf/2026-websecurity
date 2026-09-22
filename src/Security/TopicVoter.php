<?php

namespace App\Security;

use App\Entity\Post;
use App\Entity\Topic;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TopicVoter extends Voter
{
    // these values are arbitrary strings; you can use anything
    const VIEW = 'view';
    const EDIT = 'edit';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // if the voter doesn't support this attribute, return false
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
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            $vote?->addReason('The user is not logged in.');
            return false;
        }

        // you know $subject is a Post object, thanks to `supports()`
        /** @var Topic $topic */
        $topic = $subject;

        return match($attribute) {
            self::EDIT => $this->canEdit($topic, $user, $vote),
            default => throw new \LogicException('This code should not be reached!')
        };
    }


    private function canEdit(Topic $topic, User $user, ?Vote $vote): bool
    {
        // this assumes that the Post object has a `getAuthor()` method
        if ($user === $topic->getAuthor()) {
            return true;
        }

        $vote?->addReason(sprintf(
            'The logged in user (username: %s) is not the author of this post (id: %d).',
            $user->getNickname(), $topic->getId()
        ));

        return false;
    }
}
