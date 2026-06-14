<?php

declare(strict_types=1);

namespace Integrations\Http\Handlers;

use Integrations\Http\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpException;
use Slim\Handlers\ErrorHandler;

class HttpErrorHandler extends ErrorHandler
{
    protected function respond(): ResponseInterface
    {
        $exception = $this->exception;
        $statusCode = 500;
        $message = 'An internal error has occurred.';

        if ($exception instanceof HttpException) {
            $statusCode = $exception->getCode();
            $message = $exception->getMessage();
        }

        if ($this->displayErrorDetails && $statusCode >= 500) {
            return parent::respond();
        }

        $response = (new ResponseFactory())->createResponse($statusCode);

        $accept = $this->request->getHeaderLine('Accept');
        $path = $this->request->getUri()->getPath();

        if (str_contains($accept, 'application/json') || str_starts_with($path, '/api')) {
            return $response->json([
                'error'   => $statusCode,
                'message' => $message,
            ]);
        }

        try {
            return $response->view("errors.{$statusCode}", [
                'statusCode' => $statusCode,
                'message'    => $message,
            ]);
        } catch (\Throwable $e) {
            try {
                return $response->view('errors.default', [
                    'statusCode' => $statusCode,
                    'message'    => $message,
                ]);
            } catch (\Throwable $e) {
                return $response->html("<h1>Error {$statusCode}</h1><p>{$message}</p>");
            }
        }
    }
}