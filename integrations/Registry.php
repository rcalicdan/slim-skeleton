<?php

declare(strict_types=1);

namespace Integrations;

use Psr\Container\ContainerInterface;

class Registry
{
    private static ?ContainerInterface $container = null;

    /**
     * Store the container instance statically.
     */
    public static function set(ContainerInterface $container): void
    {
        self::$container = $container;
    }

    /**
     * Retrieve the stored container instance.
     */
    public static function get(): ?ContainerInterface
    {
        return self::$container;
    }
}