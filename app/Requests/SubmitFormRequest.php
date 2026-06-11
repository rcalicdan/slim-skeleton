<?php

declare(strict_types=1);

namespace App\Requests;

use App\Services\GetNameService;
use DI\Attribute\Inject;
use Integrations\Http\FormRequest;

class SubmitFormRequest extends FormRequest
{
    #[Inject]
    private readonly GetNameService $getNameService;

    public function rules(): array
    {
        return [
            'name'  => 'required|min:3',
            'email' => 'required|email',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'  => 'Full Name ' . $this->getNameService->getName(),
            'email' => 'Email Address',
        ];
    }
}