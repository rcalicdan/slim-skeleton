<?php

declare(strict_types=1);

use eftec\bladeone\BladeOne;
use Hibla\QueryBuilder\DB;
use Hibla\QueryBuilder\Interfaces\DatabaseConnectionInterface;
use Integrations\Http\ResponseFactory;
use Integrations\View\BladeRenderer;
use Integrations\View\Directives\EndErrorDirective;
use Integrations\View\Directives\ErrorDirective;
use Integrations\View\Directives\MethodDirective;
use Integrations\View\Directives\UpperDirective;
use Odan\Session\PhpSession;
use Odan\Session\SessionInterface;
use Odan\Session\SessionManagerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Interfaces\RouteParserInterface;
use Somnambulist\Components\Validation\Factory as ValidationFactory;

use function Rcalicdan\ConfigLoader\config;
use function Rcalicdan\ConfigLoader\env;

return [
    'settings' => [
        'autowire' => true,
        'use_attributes' => true,
        'cache_path' => env('APP_ENV', 'local') === 'production'
            ? cache_path(__DIR__ . '/../var/cache/di')
            : null,
    ],

    'dependency_map' => [
        PhpSession::class => function () {
            return new PhpSession([
                'name' => 'app_session',
                'cache_expire' => 0,
            ]);
        },

        SessionManagerInterface::class => function (ContainerInterface $c) {
            return $c->get(PhpSession::class);
        },

        SessionInterface::class => function (ContainerInterface $c) {
            return $c->get(PhpSession::class);
        },

        ResponseFactoryInterface::class => function () {
            return new ResponseFactory();
        },

        RouteParserInterface::class => function (ContainerInterface $c) {
            return $c->get(App::class)->getRouteCollector()->getRouteParser();
        },

        BladeOne::class => function (ContainerInterface $c) {
            $blade = new BladeOne(
                config('blade.templates_path'),
                config('blade.cache_path'),
                config('blade.mode')
            );

            $blade->directive('upper', $c->get(UpperDirective::class));
            $blade->directive('error', $c->get(ErrorDirective::class));
            $blade->directive('enderror', $c->get(EndErrorDirective::class));
            $blade->directive('method', $c->get(MethodDirective::class));

            /** @var array<string, callable|class-string> $directives */
            $directives = config('blade.directives', []);

            foreach ($directives as $name => $handler) {
                $callable = is_string($handler) ? $c->get($handler) : $handler;
                $blade->directive($name, $callable);
            }

            /** @var array<string, callable|class-string> $directivesRt */
            $directivesRt = config('blade.directives_rt', []);

            foreach ($directivesRt as $name => $handler) {
                $callable = is_string($handler) ? $c->get($handler) : $handler;
                $blade->directiveRT($name, $callable);
            }

            return $blade;
        },

        BladeRenderer::class => function (ContainerInterface $c) {
            $renderer = new BladeRenderer(
                $c->get(BladeOne::class),
                $c->get(ResponseFactoryInterface::class)
            );

            BladeRenderer::init($renderer);

            return $renderer;
        },

        ValidationFactory::class => function (ContainerInterface $c) {
            $factory = new ValidationFactory();

            /** @var array<string, class-string> $customRules */
            $customRules = config('validation.rules', []);

            foreach ($customRules as $name => $class) {
                $factory->addRule($name, $c->get($class));
            }

            return $factory;
        },

        DatabaseConnectionInterface::class => function () {
            return DB::connection();
        },
    ],
];
