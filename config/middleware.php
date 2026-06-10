<?php

declare(strict_types=1);

use Integrations\Http\Middleware\WebValidationMiddleware;
use Odan\Session\Middleware\SessionStartMiddleware;
use Slim\App;

use function Rcalicdan\ConfigLoader\env;

return function (App $app): void {
    /**
     * -------------------------------------------------------------------------
     * Application Global Middleware
     * -------------------------------------------------------------------------
     * Note: Slim executes middleware in LIFO (Last In, First Out) order.
     * The LAST middleware added here is the FIRST one to execute on a request.
     *
     * Example custom middleware:
     * $app->add(\App\Middleware\ExampleMiddleware::class);
     * 
     * -------------------------------------------------------------------------
     * Core Slim Middleware
     * -------------------------------------------------------------------------
     */

    /**
     * Parses JSON, form data, etc. in the request body.
     */
    $app->addBodyParsingMiddleware();

    /**
     * Handles routing. This must be resolved before other custom middleware
     * to map requests to their correct controllers.
     */
    $app->addRoutingMiddleware();

    /**
     * Error handling. This must be the absolute last middleware added so that
     * it wraps all other middleware and catches any unhandled exceptions.
     */
    $app->addErrorMiddleware(
        displayErrorDetails: (bool) env('APP_DEBUG', true),
        logErrors: true,
        logErrorDetails: true,
    );

    /**
     * -------------------------------------------------------------------------
     * Global Session & Validation Middleware (Default: Web)
     * -------------------------------------------------------------------------
     * The middlewares below are enabled globally for standard web applications.
     * Due to Slim's LIFO (Last In, First Out) execution order, these are declared
     * after core middleware to ensure they execute first when a request arrives.
     *
     * - SessionStartMiddleware executes first, starting the PHP session safely.
     * - WebValidationMiddleware executes second, catching any validation errors,
     *   flashing them to the session, and redirecting the user back to the form.
     *
     *  API vs. Web Notice:
     * If you are building a headless or JSON-only API, you should comment out or
     * remove these two middlewares and enable the ApiValidationMiddleware instead:
     *
     * $app->add(\Integrations\Http\Middleware\ApiValidationMiddleware::class);
     *
     * IMPORTANT: Do NOT run both WebValidationMiddleware and ApiValidationMiddleware
     * globally at the same time. They handle validation failures in conflicting
     * ways (one redirects back, while the other returns a JSON payload).
     *
     * If your application serves both web pages and API endpoints, keep this file
     * clean by removing the validation middlewares globally and applying them
     * selectively via Route Groups in `config/routes.php`.
     */
    $app->add(WebValidationMiddleware::class);
    $app->add(SessionStartMiddleware::class);
};
