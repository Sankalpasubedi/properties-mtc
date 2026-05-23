<?php

namespace Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Response as SlimResponse;

class AuthMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        if (empty($_SESSION['admin_logged_in'])) {
            return (new SlimResponse())->withHeader('Location', '/admin/login')->withStatus(302);
        }
        return $handler->handle($request);
    }
}
