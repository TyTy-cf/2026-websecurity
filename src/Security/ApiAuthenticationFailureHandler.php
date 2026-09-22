<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

/**
 * Wraps Lexik's failure handler so that a throttled login (too many attempts)
 * answers with the semantically correct HTTP 429 (Too Many Requests) instead of
 * the 401 Lexik returns for every authentication failure.
 *
 * All other failures (bad credentials, etc.) keep Lexik's default behaviour.
 */
final class ApiAuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{
    public function __construct(
        private readonly AuthenticationFailureHandlerInterface $decorated,
    ) {
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $response = $this->decorated->onAuthenticationFailure($request, $exception);

        if (!$exception instanceof TooManyLoginAttemptsAuthenticationException) {
            return $response;
        }

        // Reuse Lexik's translated body, but fix the status code (and the mirrored "code" field).
        $data = json_decode((string) $response->getContent(), true);
        if (!\is_array($data)) {
            $data = ['message' => $exception->getMessageKey()];
        }
        $data['code'] = Response::HTTP_TOO_MANY_REQUESTS;

        return new JsonResponse($data, Response::HTTP_TOO_MANY_REQUESTS);
    }
}
