<?php

session_start();

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    $ext = strtolower(pathinfo($uri, PATHINFO_EXTENSION));
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
    ];
    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
        readfile(__DIR__ . $uri);
        return;
    }
}

require_once 'vendor/autoload.php';
require_once 'bootstrap.php';
require_once 'routes.php';

// Run app
$app->run();
