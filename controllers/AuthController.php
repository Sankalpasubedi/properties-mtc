<?php

namespace Controllers;

use Models\AdminUser;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class AuthController
{
    private AdminUser $adminUser;
    private ViewController $view;
    
    public function __construct(AdminUser $adminUser, ViewController $view)
    {
        $this->adminUser = $adminUser;
        $this->view = $view;
    }
    public function loginForm(Request $request, Response $response): Response
    {
        if (!empty($_SESSION['admin_logged_in'])) {
            return $response->withHeader('Location', '/admin')->withStatus(302);
        }
        return $this->view->renderWithLayout($response, 'admin/login.php', [
            'title' => 'Admin Login',
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    public function login(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        if (!$this->validateCsrfToken($data['csrf_token'] ?? '')) {
            return $this->view->renderWithLayout($response, 'admin/login.php', [
                'title' => 'Admin Login',
                'error' => 'Invalid CSRF token.',
                'csrf_token' => $this->generateCsrfToken(),
            ]);
        }

        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';

        if ($username === '' || $password === '') {
            return $this->view->renderWithLayout($response, 'admin/login.php', [
                'title' => 'Admin Login',
                'error' => 'Username and password are required.',
                'csrf_token' => $this->generateCsrfToken(),
            ]);
        }

        if ($this->adminUser->verifyPassword($username, $password)) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            session_regenerate_id(true);
            return $response->withHeader('Location', '/admin')->withStatus(302);
        }

        return $this->view->renderWithLayout($response, 'admin/login.php', [
            'title' => 'Admin Login',
            'error' => 'Invalid credentials.',
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }
    public function logout(Request $request, Response $response): Response
    {
        $_SESSION = [];
        session_destroy();
        return $response->withHeader('Location', '/admin/login')->withStatus(302);
    }
    private function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }
    private function validateCsrfToken(string $token): bool
    {
        if (empty($_SESSION['csrf_token']) || $token === '') {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}