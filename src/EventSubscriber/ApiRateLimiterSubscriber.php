<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

final readonly class ApiRateLimiterSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RateLimiterFactoryInterface $anonymousApiLimiter,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (
            $request->getMethod() !== 'GET'
            || !str_starts_with($request->getPathInfo(), '/sujets/')
            || !str_starts_with($request->getPathInfo(), '/inscription')
        ) {
            return;
        }

        $limiter = $this->anonymousApiLimiter->create(
            $request->getClientIp() ?? 'unknown',
        );

        $limit = $limiter->consume();

        if (!$limit->isAccepted()) {
            $event->setResponse(new Response(
                'Too Many Requests',
                Response::HTTP_TOO_MANY_REQUESTS,
                [
                    'Retry-After' => (string) $limit->getRetryAfter()->getTimestamp() - time(),
                ],
            ));
        }
    }
}
