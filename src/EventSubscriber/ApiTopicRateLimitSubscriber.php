<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

/**
 * Rate-limit the public topics API (GET /api/topic) by client IP.
 *
 * Uses the "app_api_topic" limiter declared in config/packages/framework.yaml.
 */
final class ApiTopicRateLimitSubscriber implements EventSubscriberInterface
{
    public function __construct(
        // The service id follows the limiter name: "limiter.<name>".
        #[Autowire(service: 'limiter.app_api_topic')]
        private readonly RateLimiterFactoryInterface $apiTopicLimiter,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        // Priority < 32 so the router has already set the "_route" attribute.
        return [KernelEvents::REQUEST => ['onKernelRequest', 20]];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        // Target the topics collection operation only.
        // Alternative, routing-agnostic check: $request->getPathInfo() === '/api/topic'
        if ('_api_/topic_get_collection' !== $request->attributes->get('_route')) {
            return;
        }

        // One bucket per client IP.
        $limit = $this->apiTopicLimiter->create($request->getClientIp())->consume(1);

        if (!$limit->isAccepted()) {
            $retryAfter = max(1, $limit->getRetryAfter()->getTimestamp() - time());

            throw new TooManyRequestsHttpException(
                $retryAfter,
                'Trop de requêtes, veuillez réessayer plus tard.',
            );
        }
    }
}
