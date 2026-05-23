<?php

use Controllers\AuthController;
use Controllers\AdminController;
use Controllers\ApiSyncController;
use Controllers\SearchController;
use Controllers\LocationController;

$app->get('/', [SearchController::class, 'index']);

$app->get('/api/properties/search', [SearchController::class, 'search']);
$app->get('/api/properties/autocomplete', [SearchController::class, 'autocomplete']);

$app->get('/property/{id}', [SearchController::class, 'detail']);

$app->get('/location', [LocationController::class, 'index']);
$app->get('/api/properties/location', [LocationController::class, 'search']);

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
