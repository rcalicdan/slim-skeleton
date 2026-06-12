<?php

declare(strict_types=1);

namespace Integrations\Http\Middleware;

use Integrations\Auth;
use Integrations\Http\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly ResponseFactory $responseFactory) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (Auth::guest()) {
            session()->getFlash()->add('error', 'You must be logged in to access this page.');

            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', '/login');
        }

        return $handler->handle($request);
    }
}
