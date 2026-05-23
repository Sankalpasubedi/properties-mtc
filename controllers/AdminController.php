<?php

namespace Controllers;

use Models\Property;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Routing\RouteContext;

class AdminController
{
    private ViewController $view;

    public function __construct(ViewController $view)
    {
        $this->view = $view;
    }

    public function dashboard(Request $request, Response $response): Response
    {
        return $this->view->renderWithLayout($response, 'admin/dashboard.php', [
            'title' => 'Admin - Properties',
        ]);
    }
}
