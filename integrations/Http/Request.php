<?php

declare(strict_types=1);

namespace Integrations\Http;

use Integrations\Http\Exceptions\ValidationException;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request as SlimRequest;
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
            ->withQueryParams($slimRequest->getQueryParams());

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
     * Retrieve a parameter strictly from the query string (GET).
     */
    public function query(string $key, mixed $default = null): mixed
    {
        $queryParams = $this->getQueryParams();

        return $queryParams[$key] ?? $default;
    }

    /**
     * Validate the request data.
     * 
     * @param array<string, string|array<mixed>> $rules
     * @return array<string, mixed>
     * @throws ValidationException
     */
    public function validate(array $rules): array
    {
        $factory = new ValidationFactory();
        
        $data = $this->getParsedBody() ?? $this->getQueryParams();
        
        $validation = $factory->make((array) $data, $rules);
        $validation->validate();

        if ($validation->fails()) {
            throw new ValidationException($validation->errors()->firstOfAll());
        }

        return $validation->getValidData();
    }
}
