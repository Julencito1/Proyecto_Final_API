<?php

require_once __DIR__ . '/../vendor/autoload.php';
include "../controllers/usuarios.php";
include "../database/handler.php";


header('Content-Type: application/json; charset=utf-8');



$router = new AltoRouter();
$router->setBasePath('/API_PROYECTO_IES/endpoints');

// TODO: CLASES

$usuarios = new Usuario($conexion);


$match = $router->match();


if ($match) {
    call_user_func_array($match['target'], $match['params']);
} else {
   
    echo json_encode(['message' => 'Ruta no encontrada']);
}
