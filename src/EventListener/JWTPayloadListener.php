<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: 'lexik_jwt_authentication.on_jwt_created')]
final class JWTPayloadListener
{
    public function __invoke(JWTCreatedEvent $event): void
    {
        $payload = $event->getData();
        unset($payload['roles']);
        $event->setData($payload);
    }
}
