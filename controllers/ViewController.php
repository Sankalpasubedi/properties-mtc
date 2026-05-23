<?php

namespace Controllers;

use Psr\Http\Message\ResponseInterface as Response;

class ViewController
{
    private string $templateDir;

    public function __construct()
    {
        $this->templateDir = __DIR__ . '/../templates';
    }

    public function renderWithLayout(Response $response, string $contentTemplate, array $data = []): Response
    {
        $file = $this->templateDir . '/layout.php';
        $data['content_template'] = $contentTemplate;

        extract($data);
        $session = &$_SESSION;

        ob_start();
        include $file;
        $content = ob_get_clean();

        $response->getBody()->write($content);
        return $response;
    }
}
