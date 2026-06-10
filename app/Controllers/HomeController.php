<?php

declare(strict_types=1);

namespace App\Controllers;

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
}
