<?php

require_once "../vendor/autoload.php";

use Conexion\Database;
use Controllers\Buscar\Buscar;
use Controllers\Categorias\Categorias;
use Controllers\Comentarios\Comentarios;
use Controllers\ComentariosGustados\ComentariosGustados;
use Controllers\ComentariosHijosGustados\ComentariosHijosGustados;
use Controllers\Historial\Historial;
use Controllers\Notificaciones\Notificaciones;
use Controllers\RespuestasComentarios\RespuestasComentarios;
use Controllers\Usuarios\Usuarios;
use Controllers\Suscripciones\Suscripciones;
use Controllers\Canales\Canales;
use Controllers\Videos\Videos;
use Controllers\VideosGuardados\VideosGuardados;
use Controllers\VideosMarcados\VideosMarcados;

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
$videos_guardados = new VideosGuardados($con);
$historial = new Historial($con);
$videos = new Videos($con);
$videosmarcados = new VideosMarcados($con);
$comentarios = new Comentarios($con);
$comentarios_gustado = new ComentariosGustados($con);
$respuestas_comentarios = new RespuestasComentarios($con);
$comentarios_hijos_gustados = new ComentariosHijosGustados($con);
$buscar = new Buscar($con);
$categorias = new Categorias($con);

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

$router->map('POST', '/suscripciones/obtener', function() use ($suscripciones)
    {
        $suscripciones->ObtenerSuscripciones();
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

$router->map('PUT', '/canal/videos/publicar', function() use ($canales)
    {
        $canales->HacerPublicoVideo();
    }
);

$router->map('PUT', '/canal/videos/ocultar', function() use ($canales)
    {
        $canales->OcultarVideo();
    }
);

$router->map('DELETE', '/canal/videos/borrar', function() use ($canales)
    {
        $canales->BorrarVideo();
    }
);

$router->map('POST', '/canal/videos/guardar', function() use ($canales)
    {
        $canales->GuardarVideo();
    }
);

$router->map('POST', '/canal/videos/quitar', function() use ($canales)
    {
        $canales->QuitarVideo();
    }
);

$router->map('POST', '/canal/videos/inicio', function() use ($canales)
    {
        $canales->InicioCanal();
    }
);

$router->map('PUT', '/canal/actualizar/descripcion', function() use ($canales)
    {
        $canales->ActualizarDescripcion();
    }
);

$router->map('POST', '/videos/guardados/obtener', function() use ($videos_guardados)
    {
        $videos_guardados->VideosGuardados();
    }
);

$router->map('DELETE', '/videos/guardados/borrar', function() use ($videos_guardados)
    {
        $videos_guardados->QuitarVideoGuardado();
    }
);

$router->map('POST', '/videos/datos/video', function() use ($videos)
    {
        $videos->ObtenerDatosVideo();
    }
);

$router->map('POST', '/videos/estadisticas', function() use ($videos)
    {
        $videos->Estadisticas();
    }
);

$router->map('POST', '/videos/megusta', function() use ($videos)
    {
        $videos->MeGusta();
    }
);

$router->map('POST', '/videos/nomegusta', function() use ($videos)
    {
        $videos->NoMeGusta();
    }
);

$router->map('POST', '/videos/quitar/marcado', function() use ($videos)
    {
        $videos->QuitarMarcado();
    }
);

$router->map('POST', '/videos/recomendados/video', function() use ($videos)
    {
        $videos->RecomendacionVideosVideo();
    }
);

$router->map('POST', '/videos/recomendados/inicio', function() use ($videos)
    {
        $videos->RecomendacionVideosInicio();
    }
);


$router->map('POST', '/historial/almacenar', function() use ($historial)
    {
        $historial->AlmacenarHistorial();
    }
);

$router->map('POST', '/historial/obtener', function() use ($historial)
    {
        $historial->ObtenerHistorial();
    }
);

$router->map('POST', '/obtener/comentarios', function() use ($comentarios): void
    {
        $comentarios->ObtenerComentarios();
    }
);

$router->map('POST', '/publicar/comentario', function() use ($comentarios): void
    {
        $comentarios->PublicarComentario();
    }
);

$router->map('POST', '/obtener/comentarios/marcados', function() use ($comentarios): void
    {
        $comentarios->ObtenerComentariosMarcados();
    }
);

$router->map('POST', '/comentarios/marcar', function() use ($comentarios_gustado): void
    {
        $comentarios_gustado->MarcadoSiComentario();
    }
);

$router->map('POST', '/comentarios/marcar/no', function() use ($comentarios_gustado): void
    {
        $comentarios_gustado->MarcadoNoComentario();
    }
);

$router->map('POST', '/comentarios/hijos/publicar', function() use ($respuestas_comentarios): void
    {
        $respuestas_comentarios->PublicarComentarioHijo();
    }
);

$router->map('POST', '/comentarios/hijos/marcar', function() use ($comentarios_hijos_gustados): void
    {
        $comentarios_hijos_gustados->MarcadoSiComentario();
    }
);

$router->map('POST', '/comentarios/hijos/marcar/no', function() use ($comentarios_hijos_gustados): void
    {
        $comentarios_hijos_gustados->MarcadoNoComentario();
    }
);

$router->map('POST', '/busqueda', function() use ($buscar): void
    {
        $buscar->Resultados();
    }
);

$router->map('GET', '/categorias/top', function() use ($categorias): void
    {
        $categorias->TopCategorias();
    }
);

$router->map('POST', '/videos/categorias/obtener', function() use ($categorias): void
    {
        $categorias->VideosCategoria();
    }
);

$router->map('GET', '/categorias/todas/obtener', function() use ($categorias): void
    {
        $categorias->ObtenerCategorias();
    }
);

$router->map('POST', '/videos/marcados/obtener', function() use ($videosmarcados): void
    {
        $videosmarcados->ObtenerVideosMarcados();
    }
);

$router->map('GET', '/holaa', function()
    {
        echo "hola";
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