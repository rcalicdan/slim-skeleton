<?php

declare(strict_types=1);

namespace Integrations\Http;

use Psr\Http\Message\ResponseFactoryInterface;

class ResponseFactory implements ResponseFactoryInterface
{
    /**
     * Create a new custom Response.
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): Response
    {
        $response = new Response($code);

        if ($reasonPhrase !== '') {
            /** @var Response $response */
            $response = $response->withStatus($code, $reasonPhrase);
        }

        return $response;
    }
}
