<?php

namespace BeberAgua\API;

use CoffeeCode\Router\Router;

define("BASE_URL", "http://localhost:8080");
$router = new Router(BASE_URL);

$router->namespace("BeberAgua\API\Domain\Controller");

$router->post("/login", "UserController:login");

$router->group("users");
$router->get("/{userid}", "UserController:show");
$router->get("/", "UserController:index");
$router->post("/", "UserController:signup");
$router->put("/{userid}", "UserController:edit");
$router->delete("/{userid}", "UserController:destroy");

$router->post("/{userid}/drink", "UserController:drinkWater");
$router->get("/{userid}/history", "UserController:history");
$router->get("/ranking", "UserController:rankToday");

$router->dispatch();