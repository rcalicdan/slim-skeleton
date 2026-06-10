<?php

declare(strict_types=1);

namespace Integrations;

use Integrations\View\BladeRenderer;
use Psr\Http\Message\ResponseInterface;

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
