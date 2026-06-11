<?php

declare(strict_types=1);

namespace Integrations\Http\Middleware;

use Integrations\Http\Request;
use Integrations\Registry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class BindRequestMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $container = Registry::get();

        if ($container instanceof \DI\Container && $request instanceof Request) {
            $container->set(Request::class, $request);
        }

        return $handler->handle($request);
    }
}
