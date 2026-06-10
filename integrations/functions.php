<?php

declare(strict_types=1);

use Integrations\Registry;
use Integrations\View\BladeRenderer;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Factory\AppFactory;

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