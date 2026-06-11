<?php

declare(strict_types=1);

namespace Integrations\Http;

class ValidatedData
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(private readonly array $data) {}

    /**
     * Retrieve a specific validated value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Determine if a validated value exists.
     */
    public function has(string $key): bool
    {
        return \array_key_exists($key, $this->data);
    }

    /**
     * Get a subset of the validated data.
     */
    public function only(string ...$keys): array
    {
        return \array_intersect_key($this->data, \array_flip($keys));
    }

    /**
     * Get all validated data except for a specified array of keys.
     */
    public function except(string ...$keys): array
    {
        return \array_diff_key($this->data, \array_flip($keys));
    }

    /**
     * Get all of the validated data.
     * 
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Get all of the validated data as an array.
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}