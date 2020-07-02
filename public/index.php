<?php

require __DIR__ . '/../vendor/autoload.php';

use TestePratico\StautRH\Config\Router;
use TestePratico\StautRH\Controller\UserController;

$path = $_SERVER['PATH_INFO'];
$routes = require __DIR__ . '/../config/routes.php';

if (!array_key_exists($path, $routes)) {
    http_response_code(404);
    exit();
}

$router = new Router();

$router->go('GET', 'users', UserController::index());


