<?php

declare(strict_types=1);

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Strip the "roles" claim from the JWT payload.
 *
 * Roles are always reloaded from the database (User::getRoles()) when the token
 * is authenticated, so the claim is never used for authorization: keeping it is
 * redundant and needlessly exposes the user's privilege level in a decodable payload.
 */
#[AsEventListener(event: Events::JWT_CREATED)]
final class RemoveRolesFromJwtListener
{
    public function __invoke(JWTCreatedEvent $event): void
    {
        $payload = $event->getData();
        unset($payload['roles']);
        $event->setData($payload);
    }
}
