<?php

declare(strict_types=1);

use function Rcalicdan\ConfigLoader\env;

return [
    /*
    |--------------------------------------------------------------------------
    | Default Session Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default session driver used by the application.
    |
    | Supported drivers:
    |   - 'php' (Default: uses PHP's native save handler)
    |   - 'database' (Save session records securely inside your database)
    |
    | To use the 'database' driver, you will write a custom SessionHandler
    | implementing PHP's native \SessionHandlerInterface (using Hibla's
    | non-blocking queries) and register it in config/container.php.
    |
    */
    'driver' => env('SESSION_DRIVER', 'php'),

    /**
     * The database table name to use for session records if using the 'database' driver.
     */
    'table' => env('SESSION_TABLE', 'sessions'),

    /*
    |--------------------------------------------------------------------------
    | Session Lifetime (Seconds)
    |--------------------------------------------------------------------------
    |
    | This option defines the number of seconds the session is allowed to
    | remain idle on both the client (via cookie) and the server (via garbage
    | collection) before it is automatically expired and wiped out.
    |
    | 7200 seconds = 2 hours
    |
    */
    'lifetime' => env('SESSION_LIFETIME', 7200),

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Settings
    |--------------------------------------------------------------------------
    |
    | Here you can configure the cookie parameters used to transmit the session
    | ID securely to the client browser.
    |
    */
    'name' => env('SESSION_COOKIE_NAME', 'app_session'),
    'path' => '/',
    'domain' => env('SESSION_COOKIE_DOMAIN'),
    'secure' => env('SESSION_COOKIE_SECURE', false),
    'httponly' => true,
    'samesite' => 'Lax',
];
