<?php

declare(strict_types=1);

use Integrations\Http\Middleware\RateLimitMiddleware;
use Integrations\Http\Request;
use Integrations\Http\Response;
use Odan\Session\SessionInterface;

beforeEach(function () {
    $this->session = $this->container->get(SessionInterface::class);
    $this->session->delete('rate_limit');
});

it('allows requests within the rate limit and blocks on exceed with retry headers', function () {
    $this->app->get('/throttle-web', function (Request $request, Response $response) {
        return $response->html('OK');
    })->add(new RateLimitMiddleware(requests: 2, window: 10));

    $request = Request::createTestRequest('GET', '/throttle-web');

    $res1 = $this->app->handle($request);
    expect($res1->getStatusCode())->toBe(200)
        ->and($res1->getHeaderLine('X-RateLimit-Limit'))->toBe('2')
        ->and($res1->getHeaderLine('X-RateLimit-Remaining'))->toBe('1')
    ;

    $res2 = $this->app->handle($request);
    expect($res2->getStatusCode())->toBe(200)
        ->and($res2->getHeaderLine('X-RateLimit-Remaining'))->toBe('0')
    ;

    $res3 = $this->app->handle($request);

    expect($res3->getStatusCode())->toBe(429)
        ->and($res3->getHeaderLine('X-RateLimit-Remaining'))->toBe('0')
        ->and($res3->hasHeader('Retry-After'))->toBeTrue()
        ->and((string) $res3->getBody())->toContain('<h1>429 Too Many Requests</h1>')
    ;
});

it('returns a structured json response when api requests are throttled', function () {
    $this->app->get('/api/throttle', function (Request $request, Response $response) {
        return $response->json(['status' => 'OK']);
    })->add(new RateLimitMiddleware(requests: 1, window: 5));

    $request = Request::createTestRequest('GET', '/api/throttle');

    $res1 = $this->app->handle($request);
    expect($res1->getStatusCode())->toBe(200);
    $res2 = $this->app->handle($request);
    $data = json_decode((string) $res2->getBody(), true);

    expect($res2->getStatusCode())->toBe(429)
        ->and($res2->getHeaderLine('Content-Type'))->toBe('application/json')
        ->and($data['error'])->toBe('Too Many Requests')
        ->and($data['retry_after'])->toBeGreaterThan(0);
});
