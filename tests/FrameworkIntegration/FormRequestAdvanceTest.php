<?php

declare(strict_types=1);

use Integrations\Http\FormRequest;
use Integrations\Http\Request;
use Integrations\Http\Response;

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

it('has working pass-through methods to build conditional rules', function () {
    $this->app->post('/users/{id}/advanced', function (Request $request, Response $response) {
        $validated = $request->validate(AdvancedTestRequest::class);

        return $response->json($validated->toArray());
    });

    $response1 = $this->post('/users/10/advanced', [
        'name' => 'John',
        'company' => 'Acme',
    ]);

    expect($response1->getStatusCode())->toBe(302);

    $response2 = $this->post('/users/10/advanced', [
        'name' => 'John',
        'company' => 'Acme',
        'tax_id' => '12345',
    ]);

    expect($response2->getStatusCode())->toBe(200);
});

it('prevents parameter overwriting idor by protecting route arguments', function () {
    $this->app->post('/users/{id}/secure-update', function (Request $request, Response $response) {
        $validated = $request->validate(AdvancedTestRequest::class);

        return $response->json([
            'validated_id' => $validated->get('id'),
            'route_id' => $request->route('id'),
        ]);
    });

    $response = $this->post('/users/5/secure-update', [
        'id' => 1,
        'name' => 'Hacker',
    ]);

    $data = json_decode((string) $response->getBody(), true);

    expect($data['route_id'])->toBe('5')
        ->and((string) $data['validated_id'])->not->toBe('1')
    ;
});
