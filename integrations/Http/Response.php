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
    public function html(string $html, int $status = 200): self
    {
        $response = $this->withStatus($status)->withHeader('Content-Type', 'text/html');
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
    public function view(string $template, array $data = [], int $status = 200): self
    {
        /** @var self $response */
        $response = blade_view($template, $data, $this)->withStatus($status);

        return $response;
    }
}
