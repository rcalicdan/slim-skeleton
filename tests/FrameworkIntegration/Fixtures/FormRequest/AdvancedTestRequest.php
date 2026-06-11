<?php

declare(strict_types=1);

namespace Tests\FrameworkIntegration\Fixtures\FormRequest;

use Integrations\Http\FormRequest;

class AdvancedTestRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string',
        ];

        if ($this->getMethod() === 'POST' && $this->route('id') !== null) {
            $rules['id'] = 'required|integer';
        }

        if ($this->filled('company')) {
            $rules['tax_id'] = 'required|string';
        }

        return $rules;
    }
}
