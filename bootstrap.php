<?php

$config = include_once 'config.php';

$container = new \DI\Container();

$container->set(PDO::class, function () use ($config) {
    return new PDO(
        "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset=utf8mb4",
        $config['db']['user'],
        $config['db']['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
});

$app = \DI\Bridge\Slim\Bridge::create($container);

$app->addErrorMiddleware(true, true, true);
