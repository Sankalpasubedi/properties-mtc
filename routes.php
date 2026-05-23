<?php

use Controllers\SearchController;
use Controllers\TestController;

$app->get('/', [TestController::class, 'index']);
$app->get('/search', [SearchController::class, 'index']);
