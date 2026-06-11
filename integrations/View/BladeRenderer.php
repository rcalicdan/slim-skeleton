<?php

declare(strict_types=1);

namespace Integrations\View;

use eftec\bladeone\BladeOne;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class BladeRenderer
{
    private static ?self $instance = null;

    public function __construct(
        private readonly BladeOne $blade,
        private readonly ResponseFactoryInterface $responseFactory
    ) {
    }

    /**
     * Initialize the BladeRenderer instance.
     *
     * @param self $instance The BladeRenderer instance to use.
     */
    public static function init(self $instance): void
    {
        self::$instance = $instance;
    }

    /**
     * Get the Singleton BladeRenderer instance.
     *
     * @return self The BladeRenderer instance.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            throw new RuntimeException('BladeRenderer not initialized.');
        }

        return self::$instance;
    }

    /**
     * Render a Blade template.
     *
     * @param string $template The path to the template file.
     * @param array<string, mixed> $data The data to pass to the template.
     * @param ResponseInterface|null $response The response to use.
     *
     * @return ResponseInterface The rendered response.
     */
    public function render(string $template, array $data = [], ?ResponseInterface $response = null): ResponseInterface
    {
        $this->blade->csrf_token = (string) session('_token');
        $response ??= $this->responseFactory->createResponse();
        $response->getBody()->write($this->blade->run($template, $data));

        return $response;
    }
}
