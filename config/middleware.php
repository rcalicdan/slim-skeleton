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
     * -------------------------------------------------------------------------
     * Global Session & Validation Middleware (Default: Web)
     * -------------------------------------------------------------------------
     */
    $app->add(WebValidationMiddleware::class);
    $app->add(SessionStartMiddleware::class);

    /**
     * Error handling. This must be the absolute LAST middleware added so that
     * it wraps all other middleware (runs outermost) and catches any unhandled
     * exceptions that escape your custom logic.
     */
    $app->addErrorMiddleware(
        displayErrorDetails: (bool) env('APP_DEBUG', true),
        logErrors: true,
        logErrorDetails: true,
    );
};