<?php
// src/App/EventListener/JWTCreatedListener.php


namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class JWTCreatedListener
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }


    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $userID = $event->getUser()->getId();
        $payload = [];
        $payload['id'] = $userID;
        $event->setData($payload);
    }
}

?>
