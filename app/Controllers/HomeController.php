<?php

declare(strict_types=1);

namespace App\Controllers;

use Integrations\Http\Request;
use Integrations\Http\Response;

class HomeController
{
    public function index(Request $request, Response $response): Response
    {
        return $response->view('home', [
            'title' => "Slim 4 Skeleton Works!",
        ]);
    }
}
