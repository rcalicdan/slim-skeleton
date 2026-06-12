<?php

declare(strict_types=1);

namespace Integrations\Http\Middleware;

use Integrations\Auth;
use Integrations\Http\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GuestMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly ResponseFactory $responseFactory) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (Auth::check()) {
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', '/');
        }

        return $handler->handle($request);
    }
}
