<?php

declare(strict_types=1);

use eftec\bladeone\BladeOne;
use Integrations\Http\ResponseFactory;
use Integrations\View\BladeRenderer;
use Odan\Session\PhpSession;
use Odan\Session\SessionInterface;
use Odan\Session\SessionManagerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

use function Integrations\cache_path;
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

        BladeOne::class => function () {
            return new BladeOne(
                config('blade.templates_path'),
                config('blade.cache_path'),
                config('blade.mode')
            );
        },

        BladeRenderer::class => function (ContainerInterface $c) {
            $renderer = new BladeRenderer(
                $c->get(BladeOne::class),
                $c->get(ResponseFactoryInterface::class)
            );

            BladeRenderer::init($renderer);

            return $renderer;
        },
    ],
];
