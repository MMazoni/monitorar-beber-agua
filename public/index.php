<?php

require __DIR__ . '/../vendor/autoload.php';

use OpenApi\Annotations as OA;

$openapi = \OpenApi\scan(__DIR__ . "../src/");
header('Content-Type: application/x-yaml');
echo $openapi->toYaml();

/**
 *  @OA\Info(
 *      title="API Beber Água",
 *      version="1.0.0",
 *      @OA\Contact(
 *          email="matheus.andrade16@fatec.sp.gov.br",
 *          name="Matheus Mazoni"
 *      )
 *  ),
 *  @OA\Server(
 *      url="https://monitorar-agua.herokuapp.com/"
 *  ),
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require __DIR__ . '/../src/Router/router.php';
