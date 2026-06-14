<?php

declare(strict_types=1);

namespace Integrations\Http;

use Slim\Psr7\Response as SlimResponse;

class Response extends SlimResponse
{
    /**
     * Return a JSON encoded response.
     */
    public function json(mixed $data, int $status = 0): self
    {
        $statusCode = $status > 0 ? $status : $this->getStatusCode();
        $response = $this->withStatus($statusCode)->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR));

        return $response;
    }

    /**
     * Return a raw HTML response.
     */
    public function html(string $html, int $status = 0): self
    {
        $statusCode = $status > 0 ? $status : $this->getStatusCode();
        $response = $this->withStatus($statusCode)
            ->withHeader('Content-Type', 'text/html');
        $response->getBody()->write($html);

        return $response;
    }

    /**
     * Return a rendered Blade view response.
     *
     * @param string $template The Blade template name to render.
     * @param array<string, mixed> $data The data to pass to the template.
     * @param int $status The HTTP status code to set for the response.
     */
    public function view(string $template, array $data = [], int $status = 0): self
    {
        $statusCode = $status > 0 ? $status : $this->getStatusCode();

        /** @var self $response */
        $response = blade_view($template, $data, $this)->withStatus($statusCode);

        return $response;
    }

    /**
     * Return a redirect response.
     *
     * @param string $url The URL to redirect to.
     * @param int $status The HTTP status code (default 302).
     */
    public function redirect(string $url, int $status = 302): self
    {
        /** @var self $response */
        $response = $this->withStatus($status)->withHeader('Location', $url);

        return $response;
    }

    /**
     * Return a redirect response to a named route.
     *
     * @param string $routeName The name of the route.
     * @param array<string, string> $data The route data parameters.
     * @param array<string, string> $queryParams The query string parameters.
     * @param int $status The HTTP status code (default 302).
     */
    public function routeRedirect(string $routeName, array $data = [], array $queryParams = [], int $status = 302): self
    {
        $url = route($routeName, $data, $queryParams);

        return $this->redirect($url, $status);
    }

    /**
     * Redirect the user back to their previous URL.
     *
     * @param string $fallback The fallback URL if no Referer is present.
     * @param int $status The HTTP status code (default 302).
     */
    public function back(string $fallback = '/', int $status = 302): self
    {
        $container = \Integrations\Registry::get();
        $request = $container?->get(Request::class);
        $url = $request?->previousUrl($fallback) ?? $fallback;

        return $this->redirect($url, $status);
    }
}
