<?php

declare(strict_types=1);

return [
    /**
     * -------------------------------------------------------------------------
     * Custom Validation Rules
     * -------------------------------------------------------------------------
     * Here you may define custom validation rules. The array key is the
     * string name used in your validation arrays (e.g., 'allowed_username'),
     * and the value is the fully qualified class name of the Rule.
     *
     * Because these are resolved via the DI Container, you can safely inject
     * dependencies (like Database connections) into your Rule constructors!
     */
    'rules' => [
        //'allowed_username' => AllowedUsernameRule::class,
    ],
];
