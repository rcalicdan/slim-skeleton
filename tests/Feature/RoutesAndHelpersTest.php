<?php

declare(strict_types=1);

use Integrations\Http\Request;
use Integrations\Http\Response;

it('generates correct urls using the route helper', function () {
    $this->app->get('/test-route/{id}', function ($request, $response) {
        return $response;
    })->setName('test.route');

    expect(route('test.route', ['id' => '42']))->toBe('/test-route/42')
        ->and(route('test.route', ['id' => '42'], ['sort' => 'asc']))->toBe('/test-route/42?sort=asc');
});

it('performs fluent route redirects', function () {
    $this->app->get('/target/{id}', function ($request, $response) {
        return $response;
    })->setName('target.route');

    $response = new Response();
    $redirect = $response->routeRedirect('target.route', ['id' => '99']);

    expect($redirect->getStatusCode())->toBe(302)
        ->and($redirect->getHeaderLine('Location'))->toBe('/target/99');
});

it('redirects back to the referer using response->back()', function () {
    $this->app->get('/submit-action', function (Request $request, Response $response) {
        return $response->back('/fallback');
    });

    $request = Request::createTestRequest('GET', '/submit-action')
        ->withHeader('Referer', '/previous-page');
        
    $response = $this->app->handle($request);

    expect($response->getStatusCode())->toBe(302)
        ->and($response->getHeaderLine('Location'))->toBe('/previous-page');
});

it('provides accurate current and previous urls via global helpers', function () {
    $this->app->get('/url-test', function (Request $request, Response $response) {
        return $response->json([
            'current' => current_url(),
            'current_with_query' => current_url(true),
            'previous' => previous_url('/default'),
        ]);
    });

    $request = Request::createTestRequest('GET', '/url-test?foo=bar')
        ->withHeader('Referer', '/some-referer');

    $response = $this->app->handle($request);
    $data = json_decode((string) $response->getBody(), true);

    expect($data['current'])->toBe('/url-test')
        ->and($data['current_with_query'])->toBe('/url-test?foo=bar')
        ->and($data['previous'])->toBe('/some-referer');
});

it('generates the correct html for form method spoofing', function () {
    expect(method_field('PUT'))->toBe('<input type="hidden" name="_METHOD" value="PUT"/>')
        ->and(method_field('patch'))->toBe('<input type="hidden" name="_METHOD" value="PATCH"/>')
        ->and(method_field('delete'))->toBe('<input type="hidden" name="_METHOD" value="DELETE"/>');
});

it('intercepts and routes spoofed PUT requests via standard POST payload', function () {
    $this->app->put('/users/{id}', function (Request $request, Response $response) {
        return $response->json([
            'actual_method' => $request->getMethod(),
            'id' => $request->route('id')
        ]);
    });

    $response = $this->post('/users/15', [
        '_METHOD' => 'PUT',
        'name' => 'Alice'
    ]);

    $data = json_decode((string) $response->getBody(), true);

    expect($response->getStatusCode())->toBe(200)
        ->and($data['actual_method'])->toBe('PUT')
        ->and($data['id'])->toBe('15');
});

it('intercepts and routes spoofed DELETE requests via standard POST payload', function () {
    $this->app->delete('/users/{id}', function (Request $request, Response $response) {
        return $response->json([
            'actual_method' => $request->getMethod(),
            'id' => $request->route('id')
        ]);
    });

    $response = $this->post('/users/22', [
        '_METHOD' => 'DELETE'
    ]);

    $data = json_decode((string) $response->getBody(), true);

    expect($response->getStatusCode())->toBe(200)
        ->and($data['actual_method'])->toBe('DELETE')
        ->and($data['id'])->toBe('22');
});