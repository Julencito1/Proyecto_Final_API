<?php

require_once "../vendor/autoload.php";

use Controllers\Notificaciones\Notificaciones;
use Controllers\Usuarios\Usuarios;
use Controllers\Suscripciones\Suscripciones;
use Controllers\Canales\Canales;
use Conexion\Database;



header('Content-Type: application/json; charset=utf-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Authorization, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if ($method == "OPTIONS") {
    header("HTTP/1.1 204 No Content");
    exit();
}


$env = parse_ini_file(__DIR__ . '/../.env');

$db = new Database();
$con = $db->Conexion($env["DRIVER"], $env["HOST"], $env["PORT"], $env["DATABASE"], $env["USER"], $env["PASSWORD"]);
$router = new AltoRouter();

$usuarios = new Usuarios($con);
$notificaciones = new Notificaciones($con);
$suscripciones = new Suscripciones($con);
$canales = new Canales($con);

$router->map('POST', '/signup', function() use ($usuarios)
    {
        
        $usuarios->Registro();
    }
);

$router->map('POST', '/login', function() use ($usuarios) 
    { 
        $usuarios->Login();
    }
);

$router->map('GET', '/usuario/datos', function() use ($usuarios)
    {
        $usuarios->DatosUsuario();
    }
);

$router->map('GET', '/notificaciones/conteo', function() use ($notificaciones)
    {
        $notificaciones->ConteoNotificacionesUsuario();
    }
);

$router->map('POST', '/notificaciones/usuario', function() use ($notificaciones)
    {
        $notificaciones->NotificacionesUsuario();
    }
);

$router->map('PUT', '/notificaciones/marcarleidas', function() use ($notificaciones)
    {
        $notificaciones->NotificacionesMarcarLeidas();
    }
);

$router->map('GET', '/suscripciones/sidebar', function() use ($suscripciones)
    {
        $suscripciones->SidebarSuscripciones();
    }
);

$router->map('POST', '/canal/agregar/suscribirse', function() use ($suscripciones)
    {
        $suscripciones->Suscribirse();
    }
);

$router->map('POST', '/canal/quitar/suscribirse', function() use ($suscripciones)
    {
        $suscripciones->QuitarSuscripcion();
    }
);

$router->map('POST', '/canales/datos', function() use ($canales)
    {
        $canales->DatosCanal();
    }
);

$router->map('POST', '/canal/videos', function() use ($canales)
    {
        $canales->VideosCanal();
    }
);

$router->map('POST', '/canal/videos/privados', function() use ($canales)
    {
        $canales->VideosPrivados();
    }
);

$router->map('POST', '/canal/sobremi', function() use ($canales)
    {
        $canales->SobreMi();
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