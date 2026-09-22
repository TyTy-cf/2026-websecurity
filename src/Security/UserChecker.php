<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Blocks authentication for accounts that have not confirmed their email yet.
 *
 * A pending account keeps a non-null activation code until the user clicks the
 * link sent by email (see SecurityController::validateRegistration).
 *
 * The check runs in checkPostAuth (after the password has been verified) and
 * throws the very same BadCredentialsException as a wrong password: the response
 * (message and timing) is indistinguishable, so it never discloses that the
 * account exists or that it is merely pending activation.
 */
final class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (null !== $user->getActivationCode()) {
            throw new BadCredentialsException();
        }
    }
}
