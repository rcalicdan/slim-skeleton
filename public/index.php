<?php

declare(strict_types=1);

error_reporting(E_ALL & ~E_DEPRECATED);

use DI\ContainerBuilder;
use Integrations\Http\Request;
use Integrations\Registry;
use Integrations\View\BladeRenderer;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Factory\AppFactory;

use function Rcalicdan\ConfigLoader\config;

require_once __DIR__ . '/../vendor/autoload.php';

$containerBuilder = new ContainerBuilder();

$containerBuilder->useAutowiring(config('container.settings.autowire', true));
$containerBuilder->useAttributes(config('container.settings.use_attributes', true));

$cachePath = config('container.settings.cache_path');
if (! empty($cachePath)) {
    $containerBuilder->enableCompilation($cachePath);
}

$containerBuilder->addDefinitions(config('container.dependency_map', []));

$container = $containerBuilder->build();
Registry::set($container);
AppFactory::setContainer($container);

$responseFactory = $container->get(ResponseFactoryInterface::class);
$app = AppFactory::create($responseFactory);

$container->get(BladeRenderer::class);

(require __DIR__ . '/../config/middleware.php')($app);
(require __DIR__ . '/../config/routes.php')($app);

$request = Request::createFromGlobals();

$app->run($request);
