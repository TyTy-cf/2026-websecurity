<?php

namespace App\EventListener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

/**
 * Exercice 8 : personnalise le payload du JWT — identité = uuid, sans email ni rôles.
 */
final class JwtCreatedListener
{
    public function onJwtCreated(JWTCreatedEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        $payload = $event->getData();
        $payload['uuid'] = $user->getUuid();
        unset($payload['username'], $payload['email'], $payload['roles']);

        $event->setData($payload);
    }
}
