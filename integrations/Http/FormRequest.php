<?php

declare(strict_types=1);

namespace Integrations\Http;

use Integrations\Http\Exceptions\ValidationException;
use Somnambulist\Components\Validation\Factory as ValidationFactory;

abstract class FormRequest
{
    public function __construct(protected readonly Request $request) {}

    abstract public function rules(): array;

    public function messages(): array
    {
        return [];
    }

    public function attributes(): array
    {
        return [];
    }

    /**
     * Normalize or sanitize raw input before validation rules run.
     * Override this to trim, cast, or reshape data.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function prepareForValidation(array $data): array
    {
        return $data;
    }

    /**
     * Runs after validation passes. Use this for cross-field checks
     * or business logic that can't be expressed as simple rules.
     * Must return the (optionally modified) validated data.
     *
     * @param array<string, mixed> $validated
     *
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    public function after(array $validated): array
    {
        return $validated;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    public function validate(): array
    {
        $factory = new ValidationFactory();
        $raw = (array) ($this->request->getParsedBody() ?? $this->request->getQueryParams());

        $data = $this->prepareForValidation($raw);
        $validation = $factory->make($data, $this->rules());

        foreach ($this->attributes() as $field => $alias) {
            $validation->setAlias($field, $alias);
        }

        if (! empty($this->messages())) {
            $validation->messages()->add('en', $this->messages());
        }

        $validation->validate();

        if ($validation->fails()) {
            throw new ValidationException($validation->errors()->firstOfAll());
        }

        return $this->after($validation->getValidData());
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->request->input($key, $default);
    }
}
