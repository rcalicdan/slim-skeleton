<?php

declare(strict_types=1);

namespace Integrations\Http\Middleware;

use Integrations\Http\ResponseFactory;
use Integrations\Registry;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RateLimitMiddleware implements MiddlewareInterface
{
    /**
     * @param int $requests Max requests allowed within the window
     * @param int $window Time window in seconds
     * @param ResponseFactory|null $responseFactory Auto-resolved if null
     */
    public function __construct(
        private readonly int $requests = 60,
        private readonly int $window = 60,
        private ?ResponseFactory $responseFactory = null
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $now = time();
        
        $routeHash = md5($request->getUri()->getPath());
        $sessionKey = "rate_limit.{$routeHash}";

        /** @var array<int, int> $timestamps */
        $timestamps = session()->get($sessionKey, []);

        $activeTimestamps = array_filter($timestamps, function (int $timestamp) use ($now) {
            return $timestamp > ($now - $this->window);
        });

        if (\count($activeTimestamps) >= $this->requests) {
            return $this->generateRateLimitResponse($request);
        }

        $activeTimestamps[] = $now;
        session()->set($sessionKey, array_values($activeTimestamps));

        return $handler->handle($request);
    }

    /**
     * Generate the appropriate 429 response based on Content Negotiation.
     */
    private function generateRateLimitResponse(ServerRequestInterface $request): ResponseInterface
    {
        $factory = $this->responseFactory ?? Registry::get()?->get(ResponseFactoryInterface::class);

        if (! $factory instanceof ResponseFactory) {
            throw new \RuntimeException('ResponseFactory is not registered in the container.');
        }

        $response = $factory->createResponse(429);

        if ($this->wantsJson($request)) {
            return $response->json([
                'error'   => 'Too Many Requests',
                'message' => 'Rate limit exceeded. Please try again in a moment.',
            ]);
        }

        return $response->html(
            '<h1>429 Too Many Requests</h1><p>Rate limit exceeded. Please try again in a moment.</p>'
        );
    }

    /**
     * Determine if the client expects a JSON response.
     */
    private function wantsJson(ServerRequestInterface $request): bool
    {
        $accept = $request->getHeaderLine('Accept');
        $path = $request->getUri()->getPath();

        return str_contains($accept, 'application/json') || str_starts_with($path, '/api');
    }
}