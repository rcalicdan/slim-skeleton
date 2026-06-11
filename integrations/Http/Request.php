<?php

declare(strict_types=1);

namespace Integrations\Http;

use Integrations\Http\Exceptions\ValidationException;
use Integrations\Registry;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request as SlimRequest;
use Slim\Routing\RouteContext;
use Somnambulist\Components\Validation\Factory as ValidationFactory;

class Request extends SlimRequest
{
    /**
     * Create a new custom request capturing all PHP globals.
     */
    public static function createFromGlobals(): self
    {
        $slimRequest = ServerRequestFactory::createFromGlobals();

        $request = new self(
            $slimRequest->getMethod(),
            $slimRequest->getUri(),
            new Headers($slimRequest->getHeaders()),
            $slimRequest->getCookieParams(),
            $slimRequest->getServerParams(),
            $slimRequest->getBody(),
            $slimRequest->getUploadedFiles()
        );

        /** @var self $request */
        $request = $request
            ->withParsedBody($slimRequest->getParsedBody())
            ->withQueryParams($slimRequest->getQueryParams())
        ;

        return $request;
    }

    /**
     * Create a mock request for Pest/PHPUnit testing.
     */
    public static function createTestRequest(string $method, string $uri): self
    {
        $slimRequest = (new ServerRequestFactory())->createServerRequest($method, $uri);

        return new self(
            $slimRequest->getMethod(),
            $slimRequest->getUri(),
            new Headers($slimRequest->getHeaders()),
            $slimRequest->getCookieParams(),
            $slimRequest->getServerParams(),
            $slimRequest->getBody(),
            $slimRequest->getUploadedFiles()
        );
    }

    /**
     * Retrieve a specific route argument.
     */
    public function route(string $key, mixed $default = null): mixed
    {
        $routeContext = RouteContext::fromRequest($this);
        $route = $routeContext->getRoute();

        if (! $route) {
            return $default;
        }

        return $route->getArgument($key, $default);
    }

    /**
     * Retrieve all route arguments.
     *
     * @return array<string, string>
     */
    public function allRouteArgs(): array
    {
        $routeContext = RouteContext::fromRequest($this);
        $route = $routeContext->getRoute();

        return $route ? $route->getArguments() : [];
    }

    /**
     * Get the current URL without the query string.
     */
    public function url(): string
    {
        return (string) $this->getUri()->withQuery('')->withFragment('');
    }

    /**
     * Get the current URL including the query string.
     */
    public function fullUrl(): string
    {
        return (string) $this->getUri();
    }

    /**
     * Get the previous URL from the Referer header.
     */
    public function previousUrl(string $fallback = '/'): string
    {
        $referer = $this->getHeaderLine('Referer');

        return $referer !== '' ? $referer : $fallback;
    }

    /**
     * Retrieve a parameter from the parsed body (POST/JSON) or query string (GET).
     */
    public function input(string $key, mixed $default = null): mixed
    {
        $parsedBody = $this->getParsedBody();

        if (\is_array($parsedBody) && \array_key_exists($key, $parsedBody)) {
            return $parsedBody[$key];
        }

        return $this->query($key, $default);
    }

    /**
     * Check if a parameter exists in the parsed body (POST/JSON) or query string (GET).
     */
    public function has(string $key): bool
    {
        $body = (array) ($this->getParsedBody() ?? []);
        $query = $this->getQueryParams();

        $data = [...$query, ...$body];

        return \array_key_exists($key, $data);
    }

    /**
     * Check if a parameter exists in the parsed body (POST/JSON) or query string (GET) and is not empty.
     */
    public function filled(string $key): bool
    {
        $value = $this->input($key);

        return $value !== null && $value !== '';
    }

    /**
     * Retrieve a parameter strictly from the query string (GET).
     */
    public function query(string $key, mixed $default = null): mixed
    {
        $queryParams = $this->getQueryParams();

        return $queryParams[$key] ?? $default;
    }

    /**
     * @param array<string, string|array<mixed>>|class-string<FormRequest>|FormRequest $rules
     *
     * @return ValidatedData
     *
     * @throws ValidationException|\InvalidArgumentException
     */
    public function validate(array|string|FormRequest $rules): ValidatedData
    {
        if ($rules instanceof FormRequest) {
            return $rules->validate();
        }

        if (\is_string($rules)) {
            if (! is_a($rules, FormRequest::class, true)) {
                throw new \InvalidArgumentException(
                    "{$rules} must extend " . FormRequest::class
                );
            }

            $container = Registry::get();

            if ($container instanceof \DI\Container) {
                $formRequest = $container->make($rules, ['request' => $this]);
            } else {
                $formRequest = new $rules($this);
            }

            return $formRequest->validate();
        }

        /** @var ValidationFactory $factory */
        $factory = Registry::get()->get(ValidationFactory::class);
        $data = $this->getParsedBody() ?? $this->getQueryParams();

        $validation = $factory->make((array) $data, $rules);
        $validation->validate();

        if ($validation->fails()) {
            throw new ValidationException($validation->errors()->firstOfAll());
        }

        return new ValidatedData($validation->getValidData());
    }
}
