<?php

declare(strict_types=1);

use Integrations\Http\Request;
use Integrations\Http\Response;
use Integrations\Http\ValidatedData;


it('retrieves input from parsed body or query fallback', function () {
    $request = Request::createTestRequest('POST', '/test-route?query_param=query_value');
    $request = $request->withParsedBody(['body_param' => 'body_value']);

    expect($request->input('body_param'))->toBe('body_value')      
        ->and($request->input('query_param'))->toBe('query_value')  
        ->and($request->input('missing', 'default'))->toBe('default');
});

it('checks if input values exist and are filled', function () {
    $request = Request::createTestRequest('POST', '/test-route?empty_param=');
    $request = $request->withParsedBody(['filled_param' => 'hello']);

    expect($request->has('filled_param'))->toBeTrue()
        ->and($request->has('empty_param'))->toBeTrue()
        ->and($request->has('missing_param'))->toBeFalse()
        ->and($request->filled('filled_param'))->toBeTrue()
        ->and($request->filled('empty_param'))->toBeFalse();
});

it('can validate direct array rules on the request object', function () {
    $request = Request::createTestRequest('POST', '/submit');
    $request = $request->withParsedBody([
        'username' => 'testuser',
        'email'    => 'test@example.com',
    ]);

    $validated = $request->validate([
        'username' => 'required|min:3',
        'email'    => 'required|email',
    ]);

    expect($validated)->toBeInstanceOf(ValidatedData::class)
        ->and($validated->get('username'))->toBe('testuser')
        ->and($validated->get('email'))->toBe('test@example.com');
});


it('generates a json response and honors your status code fallback logic', function () {
    $response = new Response();

    $response = $response->withStatus(201)->json(['message' => 'Created']);

    expect($response->getStatusCode())->toBe(201)
        ->and($response->getHeaderLine('Content-Type'))->toBe('application/json')
        ->and((string) $response->getBody())->toBe('{"message":"Created"}');
});

it('generates a correct html response', function () {
    $response = new Response();

    $response = $response->html('<h1>Hello World</h1>', 202);

    expect($response->getStatusCode())->toBe(202)
        ->and($response->getHeaderLine('Content-Type'))->toBe('text/html')
        ->and((string) $response->getBody())->toBe('<h1>Hello World</h1>');
});