<?php

declare(strict_types=1);

namespace Integrations\Http\Exceptions;

use Exception;

class ValidationException extends Exception
{
    /**
     * @param array<string, array<string, string>> $errors
     */
    public function __construct(public readonly array $errors)
    {
        parent::__construct('The given data was invalid.', 422);
    }
}
