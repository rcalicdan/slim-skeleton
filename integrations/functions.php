<?php

declare(strict_types=1);

use Integrations\Registry;
use Integrations\View\BladeRenderer;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Interfaces\RouteParserInterface;

if (! function_exists('blade_view')) {
    /**
     * Render a Blade template.
     *
     * @param string $template The path to the template file.
     * @param array<string, mixed> $data The data to pass to the template.
     * @param ResponseInterface|null $response The response to use.
     *
     * @return ResponseInterface The rendered response.
     */
    function blade_view(
        string $template,
        array $data = [],
        ?ResponseInterface $response = null
    ): ResponseInterface {
        return BladeRenderer::getInstance()->render($template, $data, $response);
    }
}

if (! function_exists('cache_path')) {
    /**
     * Set the cache path for and handle permissions.
     *
     * @param string $path The cache path to set.
     *
     * @return string The cache path.
     */
    function cache_path(string $path): string
    {
        $cachePath = $path;

        if (! is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }

        return $cachePath;
    }
}

if (! function_exists('session')) {
    /**
     * Get the session instance or retrieve a session value.
     *
     * @param string|null $key The session key to retrieve, or null to get the session instance.
     * @param mixed $default The fallback value if the session key does not exist.
     *
     * @return SessionInterface|mixed Returns the SessionInterface manager if $key is null; otherwise, the retrieved value.
     */
    function session(?string $key = null, mixed $default = null): mixed
    {
        $container = Registry::get();

        if ($container === null) {
            return $default;
        }

        /** @var SessionInterface $session */
        $session = $container->get(SessionInterface::class);

        if ($key === null) {
            return $session;
        }

        return $session->get($key, $default);
    }
}

if (! function_exists('old')) {
    /**
     * Retrieve old input value from the session.
     */
    function old(string $key, mixed $default = null): mixed
    {
        $old = session('old') ?? [];

        return $old[$key] ?? $default;
    }
}

if (! function_exists('error')) {
    /**
     * Retrieve the first validation error message for a field.
     */
    function error(string $key): ?string
    {
        $errors = session('errors') ?? [];

        if (isset($errors[$key]) && is_string($errors[$key])) {
            return $errors[$key];
        }

        return null;
    }
}

if (! function_exists('has_error')) {
    /**
     * Check if a specific field has a validation error.
     */
    function has_error(string $key): bool
    {
        $errors = session('errors') ?? [];

        return isset($errors[$key]);
    }
}

if (! function_exists('error_all')) {
    /**
     * Retrieve all validation error messages.
     *
     * @return array<string, string>
     */
    function error_all(): array
    {
        return session('errors') ?? [];
    }
}

if (! function_exists('route')) {
    /**
     * Generate a URL for a named route.
     *
     * @param string $routeName The name of the route.
     * @param array<string, string> $data Route parameters (e.g. ['id' => 1]).
     * @param array<string, string> $queryParams Query string parameters.
     *
     * @return string The generated URL.
     */
    function route(string $routeName, array $data = [], array $queryParams = []): string
    {
        $container = Registry::get();

        if ($container === null) {
            return '';
        }

        $parser = $container->get(RouteParserInterface::class);

        return $parser->urlFor($routeName, $data, $queryParams);
    }
}

if (! function_exists('current_url')) {
    /**
     * Get the current URL.
     *
     * @param bool $withQuery Whether to include the query string.
     */
    function current_url(bool $withQuery = false): string
    {
        $container = Registry::get();
        if (! $container) {
            return '';
        }

        /** @var Integrations\Http\Request $request */
        $request = $container->get(Integrations\Http\Request::class);

        return $withQuery ? $request->fullUrl() : $request->url();
    }
}

if (! function_exists('previous_url')) {
    /**
     * Get the previous URL.
     */
    function previous_url(string $fallback = '/'): string
    {
        $container = Registry::get();
        if (! $container) {
            return $fallback;
        }

        /** @var Integrations\Http\Request $request */
        $request = $container->get(Integrations\Http\Request::class);

        return $request->previousUrl($fallback);
    }
}

if (! function_exists('method_field')) {
    /**
     * Generate a hidden HTML input field to spoof HTTP verbs.
     */
    function method_field(string $method): string
    {
        $cleanedMethod = strtoupper(trim($method, "'\" "));

        return '<input type="hidden" name="_method" value="' . $cleanedMethod . '"/>';
    }
}

