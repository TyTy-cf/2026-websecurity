<?php

namespace App\EventListener;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;

#[AsEventListener(event: 'kernel.request', priority: 10)]
final class RateLimiterListener
{
    public function __construct(
        #[Autowire(service: 'limiter.registration')]
        private readonly RateLimiterFactory $registrationLimiter,
        #[Autowire(service: 'limiter.api_topic_list')]
        private readonly RateLimiterFactory $apiTopicListLimiter,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        $limiter = match (true) {
            'app_register' === $route && $request->isMethod('POST') => $this->registrationLimiter,
            '_api_/topic_get_collection' === $route => $this->apiTopicListLimiter,
            default => null,
        };

        if (null === $limiter) {
            return;
        }

        $limit = $limiter->create($request->getClientIp())->consume(1);

        if (!$limit->isAccepted()) {
            throw new TooManyRequestsHttpException($limit->getRetryAfter()->getTimestamp() - time());
        }
    }
}
