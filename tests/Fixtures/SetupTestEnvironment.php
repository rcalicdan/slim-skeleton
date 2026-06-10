<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use DI\Container;
use DI\ContainerBuilder;
use Integrations\Registry;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Factory\AppFactory;

use function Rcalicdan\ConfigLoader\config;

class SetupTestEnvironment
{
    /**
     * @return array{0: App, 1: Container}
     */
    public static function boot(): array
    {
        $containerBuilder = new ContainerBuilder();

        $containerBuilder->useAutowiring(config('container.settings.autowire', true));
        $containerBuilder->useAttributes(config('container.settings.use_attributes', true));
        $containerBuilder->addDefinitions(config('container.dependency_map', []));

        /** @var Container $container */
        $container = $containerBuilder->build();

        Registry::set($container);

        AppFactory::setContainer($container);

        $responseFactory = $container->get(ResponseFactoryInterface::class);
        $app = AppFactory::create($responseFactory);

        (require __DIR__ . '/../../config/middleware.php')($app);
        (require __DIR__ . '/../../config/routes.php')($app);

        return [$app, $container];
    }
}
