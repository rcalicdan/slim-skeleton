<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Requests\SubmitFormRequest;
use Integrations\Http\Request;
use Integrations\Http\Response;

class HomeController
{
    public function index(Request $request, Response $response): Response
    {
        $name = $request->query('name', 'Guest');

        return $response->view('home', [
            'title' => "Slim 4 Skeleton Works, {$name}!",
        ]);
    }

    public function submit(Request $request, Response $response): Response
    {
        $request->validate(SubmitFormRequest::class);

        session()->getFlash()->add('success', 'Form validated and submitted successfully!');

        return $response->withStatus(302)->withHeader('Location', '/');
    }
}
