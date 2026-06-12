<?php

declare(strict_types=1);

return [
    /**
     * -------------------------------------------------------------------------
     * Console Commands
     * -------------------------------------------------------------------------
     * Register your custom Symfony Console commands here.
     * Because they are resolved through the DI container, they support
     * full constructor autowiring!
     */
    'commands' => [
        Integrations\Commands\ClearCacheCommand::class,
        Integrations\Commands\GenerateKeyCommand::class,
    ],
];
