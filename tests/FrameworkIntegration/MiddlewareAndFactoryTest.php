<?php

declare(strict_types=1);

use Integrations\Http\Exceptions\ValidationException;
use Integrations\Http\Middleware\ApiValidationMiddleware;
use Integrations\Http\Middleware\CsrfMiddleware;
use Integrations\Http\Middleware\WebValidationMiddleware;
use Integrations\Http\Request;
use Integrations\Http\Response;
use Integrations\Http\ResponseFactory;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

it('response factory creates custom response objects with reason phrases', function () {
    $factory = new ResponseFactory();

    $response = $factory->createResponse(201, 'Created Successfully');

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->getStatusCode())->toBe(201)
        ->and($response->getReasonPhrase())->toBe('Created Successfully')
    ;
});

it('api validation middleware catches validation exceptions and returns json', function () {
    $handler = new class () implements RequestHandlerInterface {
        public function handle(ServerRequestInterface $request): ResponseInterface
        {
            throw new ValidationException(['email' => 'The email field is invalid.']);
        }
    };

    $request = Request::createTestRequest('POST', '/api/submit');
    $middleware = $this->container->get(ApiValidationMiddleware::class);
    $response = $middleware->process($request, $handler);

    expect($response->getStatusCode())->toBe(422)
        ->and($response->getHeaderLine('Content-Type'))->toBe('application/json')
        ->and((string) $response->getBody())->toContain('The email field is invalid.')
    ;
});

it('web validation middleware catches exceptions and redirects back with session data', function () {
    $handler = new class () implements RequestHandlerInterface {
        public function handle(ServerRequestInterface $request): ResponseInterface
        {
            throw new ValidationException(['name' => 'The name is required.']);
        }
    };

    $request = Request::createTestRequest('POST', '/submit');
    $request = $request->withParsedBody(['name' => '']);
    $middleware = $this->container->get(WebValidationMiddleware::class);
    $response = $middleware->process($request, $handler);

    expect($response->getStatusCode())->toBe(302)
        ->and($response->getHeaderLine('Location'))->toBe('/')
    ;

    $session = $this->container->get(SessionInterface::class);

    expect($session->get('errors'))->toHaveKey('name')
        ->and($session->get('old'))->toHaveKey('name', '')
    ;
});

it('csrf middleware generates a token on GET requests', function () {
    $session = $this->container->get(SessionInterface::class);
    expect($session->has('_token'))->toBeFalse();

    $handler = new class () implements RequestHandlerInterface {
        public function handle(ServerRequestInterface $request): ResponseInterface
        {
            return new Response();
        }
    };

    $request = Request::createTestRequest('GET', '/');
    $middleware = $this->container->get(CsrfMiddleware::class);

    $middleware->process($request, $handler);

    expect($session->has('_token'))->toBeTrue()
        ->and($session->get('_token'))->not->toBeEmpty()
    ;
});

it('csrf middleware blocks state changing requests with invalid tokens', function () {
    $session = $this->container->get(SessionInterface::class);
    $session->set('_token', 'valid-token');

    $handler = new class () implements RequestHandlerInterface {
        public function handle(ServerRequestInterface $request): ResponseInterface
        {
            return new Response();
        }
    };

    $request = Request::createTestRequest('POST', '/submit');
    $request = $request->withParsedBody(['_token' => 'invalid-token']);

    $middleware = $this->container->get(CsrfMiddleware::class);
    $response = $middleware->process($request, $handler);

    expect($response->getStatusCode())->toBe(403)
        ->and((string) $response->getBody())->toContain('CSRF token is missing or invalid.')
    ;
});
