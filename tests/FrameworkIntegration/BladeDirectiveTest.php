<?php

declare(strict_types=1);

use eftec\bladeone\BladeOne;
use Odan\Session\SessionInterface;
use Tests\FrameworkIntegration\Fixtures\Directives\TestFormatDirective;

beforeEach(function () {
    $this->templatePath = __DIR__ . '/../../templates/test-directives.blade.php';
    $this->errorTemplatePath = __DIR__ . '/../../templates/test-error-directives.blade.php';

    $blade = $this->container->get(BladeOne::class);

    $blade->directive('upper', $this->container->get(TestFormatDirective::class));

    $blade->directiveRT('multiply', function ($a, $b) {
        echo $a * $b;
    });

    $html = <<<'HTML'
    <div>
        <p>Compile-time: @upper('hello')</p>
        <p>Run-time: @multiply(5, 4)</p>
    </div>
    HTML;
    file_put_contents($this->templatePath, $html);

    $errorHtml = <<<'HTML'
    <div>
        <input type="text" class="@error('email') is-invalid @enderror">
        @error('email')
            <span class="error">{{ $message }}</span>
        @enderror
    </div>
    HTML;
    file_put_contents($this->errorTemplatePath, $errorHtml);
});

afterEach(function () {
    if (file_exists($this->templatePath)) {
        unlink($this->templatePath);
    }
    if (file_exists($this->errorTemplatePath)) {
        unlink($this->errorTemplatePath);
    }
});

it('compiles compile-time directives correctly', function () {
    $response = blade_view('test-directives');
    $html = (string) $response->getBody();

    expect($html)->toContain('<p>Compile-time: HELLO</p>');
});

it('evaluates run-time directives correctly', function () {
    $response = blade_view('test-directives');
    $html = (string) $response->getBody();

    expect($html)->toContain('<p>Run-time: 20</p>');
});

it('evaluates built-in error directives correctly', function () {
    $response = blade_view('test-error-directives');
    $content = (string) $response->getBody();

    expect($content)->not->toContain('is-invalid')
        ->and($content)->not->toContain('<span class="error">')
    ;

    $session = $this->container->get(SessionInterface::class);
    $session->set('errors', ['email' => 'The email address is already in use.']);

    $response2 = blade_view('test-error-directives');
    $content2 = (string) $response2->getBody();

    expect($content2)->toContain('class=" is-invalid "')
        ->and($content2)->toContain('<span class="error">The email address is already in use.</span>')
    ;
});
