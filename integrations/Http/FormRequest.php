<?php

declare(strict_types=1);

namespace Integrations\Http;

use Integrations\Http\Exceptions\ValidationException;
use Integrations\Registry;
use Somnambulist\Components\Validation\Factory as ValidationFactory;

abstract class FormRequest
{
    public function __construct(protected readonly Request $request) {}

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
     * @throws ValidationException
     */
    public function validate(): ValidatedData
    {
        /** @var ValidationFactory $factory */
        $factory = Registry::get()->get(ValidationFactory::class);
        $raw     = (array) ($this->request->getParsedBody() ?? $this->request->getQueryParams());

        $data       = $this->prepareForValidation($raw);
        $validation = $factory->make($data, $this->rules());

        foreach ($this->attributes() as $field => $alias) {
            $validation->setAlias($field, $alias);
        }

        if (!empty($this->messages())) {
            $validation->messages()->add('en', $this->messages());
        }

        $validation->validate();

        if ($validation->fails()) {
            throw new ValidationException($validation->errors()->firstOfAll());
        }
        
        $finalData = $this->after($validation->getValidData());

        return new ValidatedData($finalData);
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->request->input($key, $default);
    }
}
