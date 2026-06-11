<?php

declare(strict_types=1);

use Integrations\Http\Response;

beforeEach(function () {
    $this->templatePath = __DIR__ . '/../../templates/test-rendering.blade.php';

    $html = <<<'HTML'
    <h1>Hello, {{ $name ?? 'Guest' }}!</h1>
    <form>
        @csrf
    </form>
    HTML;

    file_put_contents($this->templatePath, $html);
});

afterEach(function () {
    if (file_exists($this->templatePath)) {
        unlink($this->templatePath);
    }
});

it('renders a blade template using the global helper', function () {
    $response = blade_view('test-rendering', ['name' => 'Pest PHP']);

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->getStatusCode())->toBe(200)
        ->and((string) $response->getBody())->toContain('<h1>Hello, Pest PHP!</h1>')
    ;
});

it('automatically injects the csrf token into the blade template', function () {
    session()->set('_token', 'super-secret-test-token');

    $response = blade_view('test-rendering', ['name' => 'Security']);
    $html = (string) $response->getBody();

    expect($html)->toContain('<h1>Hello, Security!</h1>')
        ->and($html)->toContain("<input type='hidden' name='_token' value='super-secret-test-token'/>")
    ;
});

it('respects custom http status codes when rendering', function () {
    $response = blade_view('test-rendering', ['name' => 'Not Found']);

    $response = $response->withStatus(404);

    expect($response->getStatusCode())->toBe(404)
        ->and((string) $response->getBody())->toContain('<h1>Hello, Not Found!</h1>')
    ;
});

it('falls back to null coalescing if a variable is missing', function () {
    $response = blade_view('test-rendering');

    expect((string) $response->getBody())->toContain('<h1>Hello, Guest!</h1>');
});
