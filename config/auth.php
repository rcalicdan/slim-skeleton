<?php

declare(strict_types=1);

use function Rcalicdan\ConfigLoader\env;

return [
    /*
    |--------------------------------------------------------------------------
    | Database Table & Keys
    |--------------------------------------------------------------------------
    |
    | This option defines the database table and primary key used to fetch 
    | your authenticated users.
    |
    */
    'table' => env('AUTH_TABLE', 'users'),
    'primary_key' => env('AUTH_PRIMARY_KEY', 'id'),

    /*
    |--------------------------------------------------------------------------
    | Session Storage
    |--------------------------------------------------------------------------
    |
    | The session identifier used to store the authenticated user's ID.
    |
    */
    'session_key' => 'auth_user_id',

    /*
    |--------------------------------------------------------------------------
    | Password Hashing (Bcrypt)
    |--------------------------------------------------------------------------
    |
    | Configure the default cost rounds for password hashing.
    |
    */
    'bcrypt_rounds' => env('BCRYPT_ROUNDS', 12, convertNumeric: true),

    /*
    |--------------------------------------------------------------------------
    | Redirect Paths
    |--------------------------------------------------------------------------
    |
    | Configure the default redirect paths used by the authentication 
    | middlewares.
    |
    */
    'redirects' => [
        'guest' => env('AUTH_REDIRECT_GUEST', '/login'),
        'auth' => env('AUTH_REDIRECT_AUTH', '/'),
    ],
];
