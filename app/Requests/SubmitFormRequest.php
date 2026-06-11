<?php

declare(strict_types=1);

namespace App\Requests;

use Integrations\Http\FormRequest;

class SubmitFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|min:3',
            'email' => 'required|email',
        ];
    }

    public function messages(): array
    {
        return [
            'name:required' => 'Please enter your namesss.',
            'name:min' => 'Name must be at least :min characters long.',
            'email:required' => 'An email address is required.',
            'email:email' => ':attribute does not look like a valid email.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Full Name',
            'email' => 'Email Address',
        ];
    }
}
