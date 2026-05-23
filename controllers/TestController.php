<?php

namespace Controllers;

use Psr\Http\Message\ResponseInterface as Response;

class TestController
{
    private ViewController $view;

    public function __construct(ViewController $view)
    {
        $this->view = $view;
    }

    public function index(Response $response): Response
    {
        return $this->view->renderWithLayout($response, 'test/test.php', [
            'title' => 'Test Page',
        ]);
    }

}
