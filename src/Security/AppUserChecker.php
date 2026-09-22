<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AppUserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (null !== $user->getActivationCode()) {
            throw new CustomUserMessageAccountStatusException(
                'Identifiant Invalide',
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
