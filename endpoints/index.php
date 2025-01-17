<?php

require_once __DIR__ . '/../vendor/autoload.php';


include "../controllers/usuarios.php";
include "../database/handler.php";

$router = new AltoRouter();


$router->setBasePath('/API_PROYECTO_IES/endpoints');


$router->map('GET', '/users', function() use ($conexion) {
    $user = new  Usuario($conexion);

    $user->GetUsers();
}
);


$match = $router->match();


if ($match) {
    call_user_func_array($match['target'], $match['params']);
} else {
   
    echo json_encode(['message' => 'Ruta no encontrada']);
}
