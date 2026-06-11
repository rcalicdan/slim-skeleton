<?php

declare(strict_types=1);

namespace Integrations\Http\Middleware;

use Integrations\Http\Exceptions\ValidationException;
use Integrations\Http\ResponseFactory;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class WebValidationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ResponseFactory $responseFactory,
        private readonly SessionInterface $session
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $response = $handler->handle($request);

            $this->session->delete('errors');
            $this->session->delete('old');

            return $response;
        } catch (ValidationException $e) {
            $this->session->set('errors', $e->errors);
            $this->session->set('old', (array) ($request->getParsedBody() ?? []));

            $this->session->getFlash()->add('error', 'Please check the form for errors.');

            $referer = $request->getHeaderLine('Referer') ?: '/';

            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $referer)
            ;
        }
    }
}
