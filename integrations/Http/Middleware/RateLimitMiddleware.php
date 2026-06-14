<?php

declare(strict_types=1);

namespace Integrations\Http\Middleware;

use Integrations\Http\Request;
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
     * @param bool $flashAndRedirect If true, web requests redirect back with a flash message instead of showing an error page.
     * @param ResponseFactory|null $responseFactory Auto-resolved if null
     */
    public function __construct(
        private readonly int $requests = 60,
        private readonly int $window = 60,
        private readonly bool $flashAndRedirect = false,
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

        $currentCount = \count($activeTimestamps);
        $remaining = max(0, $this->requests - $currentCount);

        if ($currentCount >= $this->requests) {
            $oldestTimestamp = min($activeTimestamps);
            $secondsToWait = ($oldestTimestamp + $this->window) - $now;
            $secondsToWait = max(1, $secondsToWait);

            return $this->generateRateLimitResponse($request, $secondsToWait);
        }

        $activeTimestamps[] = $now;
        session()->set($sessionKey, array_values($activeTimestamps));

        $response = $handler->handle($request);

        return $response
            ->withHeader('X-RateLimit-Limit', (string) $this->requests)
            ->withHeader('X-RateLimit-Remaining', (string) ($remaining - 1))
        ;
    }

    /**
     * Generate the appropriate 429 response based on Content Negotiation and configuration.
     */
    private function generateRateLimitResponse(ServerRequestInterface $request, int $secondsToWait): ResponseInterface
    {
        $factory = $this->responseFactory ?? Registry::get()?->get(ResponseFactoryInterface::class);

        if (! $factory instanceof ResponseFactory) {
            throw new \RuntimeException('ResponseFactory is not registered in the container.');
        }

        $message = "You are doing that too often. Please wait {$secondsToWait} seconds before trying again.";

        $response = $factory->createResponse(429)
            ->withHeader('X-RateLimit-Limit', (string) $this->requests)
            ->withHeader('X-RateLimit-Remaining', '0')
            ->withHeader('Retry-After', (string) $secondsToWait)
        ;

        if ($this->wantsJson($request)) {
            return $response->json([
                'error' => 'Too Many Requests',
                'message' => $message,
                'retry_after' => $secondsToWait,
            ]);
        }

        if ($this->flashAndRedirect) {
            session()->getFlash()->add('error', $message);

            $referer = '/';
            if ($request instanceof Request) {
                $referer = $request->previousUrl();
            } else {
                $referer = $request->getHeaderLine('Referer') ?: '/';
            }

            return $factory->createResponse(302)->withHeader('Location', $referer);
        }

        try {
            return $response->view('errors.429', [
                'statusCode' => 429,
                'message' => $message,
                'retry_after' => $secondsToWait,
            ]);
        } catch (\Throwable $e) {
            return $response->html("<h1>429 Too Many Requests</h1><p>{$message}</p>");
        }
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
