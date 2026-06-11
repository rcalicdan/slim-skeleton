<?php

declare(strict_types=1);

namespace Integrations\Http\Middleware;

use Integrations\Http\ResponseFactory;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CsrfMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ResponseFactory $responseFactory,
        private readonly SessionInterface $session
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $method = $request->getMethod();

        if (\in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $parsedBody = $request->getParsedBody();

            $token = \is_array($parsedBody) ? ($parsedBody['_token'] ?? '') : '';
            $sessionToken = $this->session->get('_token', '');

            if (empty($token) || $token !== $sessionToken) {
                $response = $this->responseFactory->createResponse(403);

                return $response->json([
                    'error' => 'Forbidden',
                    'message' => 'CSRF token is missing or invalid.',
                ]);
            }
        } else {
            if (! $this->session->has('_token') || empty($this->session->get('_token'))) {
                $this->session->set('_token', bin2hex(random_bytes(20)));
            }
        }

        return $handler->handle($request);
    }
}
