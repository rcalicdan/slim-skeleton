<?php

declare(strict_types=1);

namespace Integrations\Http\Middleware;

use Integrations\Http\Exceptions\ValidationException;
use Integrations\Http\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ApiValidationMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly ResponseFactory $responseFactory) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (ValidationException $e) {
            $response = $this->responseFactory->createResponse(422);

            return $response->json([
                'message' => $e->getMessage(),
                'errors'  => $e->errors,
            ]);
        }
    }
}
