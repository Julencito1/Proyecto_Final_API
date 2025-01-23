<?php

require_once __DIR__ . '/../vendor/autoload.php';
include "../controllers/usuarios.php";
include "../database/handler.php";


header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if($method == "OPTIONS") {
    die();
}



$router = new AltoRouter();
$router->setBasePath('/API_PROYECTO_IES/endpoints');

// TODO: CLASES

$usuarios = new Usuario($conexion);

$router->map("POST", "/signup", function() use ($usuarios) 
{
    
    $usuarios->Registro();
});

$router->map("POST", "/login", function() use ($usuarios) 
{

    $usuarios->Login();
});





$match = $router->match();


if ($match) {
    call_user_func_array($match['target'], $match['params']);
} else {
   
    echo json_encode(['message' => 'Ruta no encontrada']);
}
