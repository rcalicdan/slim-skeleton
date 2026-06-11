<?php

declare(strict_types=1);

namespace App\Rules;

use Somnambulist\Components\Validation\Rule;

class AllowedUsernameRule extends Rule
{
    /**
     * The default error message.
     * :attribute and :value placeholders are automatically replaced.
     */
    protected string $message = 'The :attribute \':value\' is restricted and cannot be used.';

    /**
     * Check if the value is valid.
     *
     * @param mixed $value The value being validated.
     *
     * @return bool True if valid, false if invalid.
     */
    public function check(mixed $value): bool
    {
        $blacklist = ['admin', 'root', 'administrator', 'null', 'undefined'];

        return ! \in_array(strtolower((string) $value), $blacklist, true);
    }
}
