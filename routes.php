<?php

use Controllers\SearchController;
use Controllers\TestController;
use Controllers\AuthController;
use Controllers\AdminController;

$app->get('/', [TestController::class, 'index']);
$app->get('/search', [SearchController::class, 'index']);





$app->get('/admin/login', [AuthController::class, 'loginForm']);
$app->post('/admin/login', [AuthController::class, 'login']);
$app->get('/admin/logout', [AuthController::class, 'logout']);




$app->group('/admin', function (\Slim\Routing\RouteCollectorProxy $group) {
    $group->get('', [AdminController::class, 'dashboard']);
    $group->get('/properties/add', [AdminController::class, 'addForm']);
    $group->post('/properties/add', [AdminController::class, 'add']);
    $group->get('/properties/edit/{id}', [AdminController::class, 'editForm']);
    $group->post('/properties/edit/{id}', [AdminController::class, 'edit']);
    $group->post('/properties/delete/{id}', [AdminController::class, 'delete']);
    $group->get('/api/sync', [ApiSyncController::class, 'sync']);
});