<?php

require_once "../vendor/autoload.php";
use Controllers\Usuarios\Usuarios;
use Conexion\Database;


header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if ($method == "OPTIONS") {
    die();
}

$env = parse_ini_file('.env');

$db = new Database();

$con = $db->Conexion($env["DRIVER"], $env["HOST"], $env["PORT"], $env["DATABASE"], $env["USER"], $env["PASSWORD"]);

$router = new AltoRouter();

$usuarios = new Usuarios($con);

$router->map('POST', '/signup', function() use ($usuarios)
    {
        
        $usuarios->Registro();
    }
);



$coincide = $router->match();

if ($coincide && is_callable($coincide['target']))
{
    call_user_func_array($coincide['target'], $coincide['params']);

} 
else 
{
    echo "No se encontro la ruta";
}




?>