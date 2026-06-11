<?php

declare(strict_types=1);

namespace Integrations\Http;

use Integrations\Http\Exceptions\ValidationException;
use Integrations\Registry;
use Somnambulist\Components\Validation\Factory as ValidationFactory;

abstract class FormRequest
{
    public function __construct(protected readonly Request $request)
    {
    }

    /**
     * @return array<string, string|array<mixed>>
     */
    abstract public function rules(): array;

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [];
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function prepareForValidation(array $data): array
    {
        return $data;
    }

    /**
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
     * @return ValidatedData
     *
     * @throws ValidationException
     */
    public function validate(): ValidatedData
    {
        /** @var ValidationFactory $factory */
        $factory = Registry::get()->get(ValidationFactory::class);
        $bodyData = (array) ($this->request->getParsedBody() ?? $this->request->getQueryParams());

        $validationPayload = [...$bodyData, ...$this->request->allRouteArgs()];

        $data = $this->prepareForValidation($validationPayload);
        $validation = $factory->make($data, $this->rules());

        $validation->validate();

        if ($validation->fails()) {
            throw new ValidationException($validation->errors()->firstOfAll());
        }

        $finalData = $this->after($validation->getValidData());

        // SECURITY: Strip out route arguments before returning the validated data.
        // This forces the developer to use $request->route('id') in the controller,
        // completely eliminating the parameter overwriting risk.
        $routeKeys = array_keys($this->request->allRouteArgs());
        foreach ($routeKeys as $key) {
            unset($finalData[$key]);
        }

        return new ValidatedData($finalData);
    }

    /**
     * Retrieve a parameter from the request.
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->request->input($key, $default);
    }

    /**
     * Retrieve a specific route argument.
     */
    public function route(string $key, mixed $default = null): mixed
    {
        return $this->request->route($key, $default);
    }

    /**
     * Check if a parameter exists in the request.
     */
    public function has(string $key): bool
    {
        return $this->request->has($key);
    }

    /**
     * Check if a parameter exists and is not empty.
     */
    public function filled(string $key): bool
    {
        return $this->request->filled($key);
    }

    /**
     * Retrieve a parameter strictly from the query string.
     */
    public function query(string $key, mixed $default = null): mixed
    {
        return $this->request->query($key, $default);
    }

    /**
     * Get the HTTP request method.
     */
    public function getMethod(): string
    {
        return $this->request->getMethod();
    }
}
