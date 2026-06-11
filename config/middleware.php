<?php

declare(strict_types=1);

use Integrations\Http\Middleware\BindRequestMiddleware;
use Integrations\Http\Middleware\CsrfMiddleware;
use Integrations\Http\Middleware\WebValidationMiddleware;
use Odan\Session\Middleware\SessionStartMiddleware;
use Slim\App;
use Slim\Middleware\MethodOverrideMiddleware;
use Slim\Middleware\RoutingMiddleware;

use function Rcalicdan\ConfigLoader\env;

return function (App $app): void {
    /**
     * -------------------------------------------------------------------------
     * Application Global Middleware
     * -------------------------------------------------------------------------
     * IMPORTANT: Slim processes middleware in LIFO (Last In, First Out) order.
     * Middleware added LAST runs FIRST. The execution order is therefore the
     * reverse of the registration order below.
     *
     * Execution order (first to last):
     *   ErrorMiddleware → SessionStart → Csrf → WebValidation → Bind → MethodOverride → Routing → Controller
     * -------------------------------------------------------------------------
     */

    /**
     * Parses JSON, form data, etc. in the request body.
     * Must run early so all middleware above it can access parsed body data.
     */
    $app->addBodyParsingMiddleware();

    /**
     * Routing Middleware — registered manually (instead of addRoutingMiddleware())
     * so we can control its position relative to MethodOverrideMiddleware.
     * Runs first in the chain; matches the request to a route.
     */
    $app->add(new RoutingMiddleware(
        $app->getRouteResolver(),
        $app->getRouteCollector()->getRouteParser()
    ));

    /**
     * Method Override Middleware.
     * Must run BEFORE routing (i.e. added AFTER RoutingMiddleware in LIFO order)
     * so it can rewrite POST + _method=PUT/PATCH/DELETE before the router sees it.
     */
    $app->add(MethodOverrideMiddleware::class);

    /**
     * Binds the current (possibly method-overridden) request to the container
     * for global helpers like request().
     */
    $app->add(BindRequestMiddleware::class);

    /**
     * -------------------------------------------------------------------------
     * Global Session & Validation Middleware (Default: Web)
     * -------------------------------------------------------------------------
     */
    $app->add(WebValidationMiddleware::class);
    $app->add(CsrfMiddleware::class);
    $app->add(SessionStartMiddleware::class);

    /**
     * Error handling. Must be the absolute LAST middleware added so that
     * it wraps everything and catches any unhandled exceptions.
     */
    $app->addErrorMiddleware(
        displayErrorDetails: (bool) env('APP_DEBUG', true),
        logErrors: true,
        logErrorDetails: true,
    );
};
