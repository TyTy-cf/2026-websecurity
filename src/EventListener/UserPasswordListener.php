<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Point de passage unique pour tout changement de mot de passe, quelle que soit la porte d'entrée
 * (formulaire, API, commande, fixtures…) : dès qu'un User est flushé avec un plainPassword,
 * celui-ci est validé contre la PasswordPolicy puis haché. Impossible d'enregistrer un mot de
 * passe non conforme, et impossible d'oublier de le hacher.
 *
 * preFlush (et non prePersist/preUpdate) : plainPassword n'étant pas mappé, un simple changement
 * de mot de passe ne produit aucun changeset et preUpdate ne serait jamais déclenché.
 */
#[AsEntityListener(event: Events::preFlush, entity: User::class)]
final readonly class UserPasswordListener
{
    public function __construct(
        private ValidatorInterface $validator,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function preFlush(User $user, PreFlushEventArgs $args): void
    {
        $plainPassword = $user->getPlainPassword();

        if (null === $plainPassword) {
            return;
        }

        $violations = $this->validator->validateProperty($user, 'plainPassword');

        if (\count($violations) > 0) {
            throw new ValidationFailedException($user, $violations);
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        $user->setPlainPassword(null);
    }
}
