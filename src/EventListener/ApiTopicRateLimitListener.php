<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;

#[AsEventListener(event: KernelEvents::REQUEST, method: 'onKernelRequest', priority: 10)]
class ApiTopicRateLimitListener
{
    public function __construct(
        private readonly RateLimiterFactory $apiTopicLimiter,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api/topic') || 'GET' !== $request->getMethod()) {
            return;
        }

        $limit = $this->apiTopicLimiter->create($request->getClientIp())->consume();

        if (!$limit->isAccepted()) {
            $event->setResponse(new JsonResponse(
                ['error' => 'Trop de requêtes, réessayez plus tard.'],
                429,
            ));
        }
    }
}
