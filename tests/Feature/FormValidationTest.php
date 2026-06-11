<?php

declare(strict_types=1);

use Odan\Session\SessionInterface;

it('displays the home page successfully', function () {
    $response = $this->get('/');

    expect($response->getStatusCode())->toBe(200)
        ->and((string) $response->getBody())->toContain('Slim 4 Skeleton Works')
    ;
});

it('blocks post requests with missing or invalid csrf tokens', function () {
    $response = $this->request('POST', '/submit', [
        'name' => 'John',
        'email' => 'john@example.com',
    ]);

    expect($response->getStatusCode())->toBe(403)
        ->and((string) $response->getBody())->toContain('CSRF token is missing or invalid')
    ;
});

it('fails validation when required fields are missing', function () {
    $response = $this->post('/submit', [
        'name' => '',
        'email' => '',
    ]);

    expect($response->getStatusCode())->toBe(302)
        ->and($response->getHeaderLine('Location'))->toBe('/')
    ;

    $session = $this->container->get(SessionInterface::class);
    $errors = $session->get('errors');

    expect($errors)->toHaveKey('name')
        ->and($errors)->toHaveKey('email')
    ;
});

it('fails validation when email is invalid', function () {
    $response = $this->post('/submit', [
        'name' => 'John Doe',
        'email' => 'not-an-email',
    ]);

    expect($response->getStatusCode())->toBe(302);

    $session = $this->container->get(SessionInterface::class);
    $errors = $session->get('errors');

    expect($errors)->not->toHaveKey('name')
        ->and($errors)->toHaveKey('email')
        ->and($session->get('old'))->toHaveKey('name', 'John Doe')
    ;
});

it('submits the form successfully with valid data', function () {
    $response = $this->post('/submit', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    expect($response->getStatusCode())->toBe(302)
        ->and($response->getHeaderLine('Location'))->toBe('/')
    ;

    $session = $this->container->get(SessionInterface::class);
    $flash = $session->getFlash()->get('success');

    expect($flash[0])->toBe('Form validated and submitted successfully!');
});
