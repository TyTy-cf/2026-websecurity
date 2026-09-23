<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

// Exercice 10 — listener et pas contrôleur, comme ça toutes les routes
// sous /api/topic sont couvertes d'un coup
#[AsEventListener(event: KernelEvents::REQUEST, priority: 16)]
final readonly class ApiRateLimitSubscriber
{
    public function __construct(
        private RateLimiterFactoryInterface $apiTopicLimiter,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$event->isMainRequest() || !str_starts_with($request->getPathInfo(), '/api/topic')) {
            return;
        }

        $limit = $this->apiTopicLimiter->create($request->getClientIp())->consume();

        if (!$limit->isAccepted()) {
            throw new TooManyRequestsHttpException(
                $limit->getRetryAfter()->getTimestamp() - time(),
                'Trop de requêtes sur l\'API. Réessayez dans un instant.',
            );
        }
    }
}
