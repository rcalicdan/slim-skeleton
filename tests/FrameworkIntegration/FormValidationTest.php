<?php

declare(strict_types=1);

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
