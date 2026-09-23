<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Strips the roles from the JWT payload.
 *
 * A JWT is only signed, not encrypted: anyone holding it can read its content.
 * The roles are reloaded from the database when the token is authenticated,
 * so shipping them in the payload leaks the user's privileges for nothing.
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
