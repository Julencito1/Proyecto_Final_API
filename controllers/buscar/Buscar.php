<?php

namespace Controllers\Buscar;

use Models\Canales\Modelos;
use Models\Videos\Modelos as VideosModelos;
use PDO;
use Utils\auth\Auth;
use Utils\paginacion\Paginacion;

class Buscar
{
    protected $con;
    private $ExtraExiste;
    private $ExtraGenerar;
    private $ExtraObtener;

    public function __construct($conexion)
    {
        $this->con = $conexion;
        
    }

    public function Resultados()
    {
        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $busqueda = file_get_contents("php://input");
        $datos = json_decode($busqueda, true);

        $contenidoBusqueda = "'%" . $datos["contenido"] . "%'";
        $offsetCanales = $datos["offsetC"];
        $offsetVideos = $datos["offsetV"];

        $qCanales = "
            SELECT c.nombre_canal,
                c.descripcion,
                u.nombre,
                u.avatar,
                (SELECT COUNT(*) FROM suscripciones s WHERE s.canal_id = c.id) AS total_suscriptores
            FROM canales c 
            LEFT JOIN usuarios u ON u.id = c.usuario_id
            WHERE c.nombre_canal LIKE " . $contenidoBusqueda . " OR u.nombre LIKE " . $contenidoBusqueda . "
            LIMIT 3 OFFSET " . $offsetCanales . ";
        ";

        $resultadosCanales = $this->con->prepare($qCanales);
        $estado = $resultadosCanales->execute();
        $respuesta = $resultadosCanales->fetchAll(PDO::FETCH_ASSOC);

        if (!$estado)
        {
            echo EstadoFAIL();
            return;
        }

        $canales = [];

        for ($o = 0; $o < count($respuesta); $o++)
        {
            array_push($canales, Modelos::ResultadosBusqueda($respuesta[$o]["nombre_canal"], $respuesta[$o]["descripcion"], $respuesta[$o]["nombre"], $respuesta[$o]["avatar"], $respuesta[$o]["total_suscriptores"]));
        }

        $masCanales = Paginacion::NoParametro(
            "
            SELECT c.nombre_canal,
                c.descripcion,
                u.nombre,
                u.avatar,
                (SELECT COUNT(*) FROM suscripciones s WHERE s.canal_id = c.id) AS total_suscriptores
            FROM canales c 
            LEFT JOIN usuarios u ON u.id = c.usuario_id
            WHERE c.nombre_canal LIKE " . $contenidoBusqueda . " OR u.nombre LIKE " . $contenidoBusqueda . "
            LIMIT 3 OFFSET " . $offsetCanales + 3 . ";
        ", $this->con
        );

        $qVideos = "
            SELECT v.titulo,
                v.descripcion,
                v.identificador,
                v.miniatura,
                v.visitas,
                v.duracion,
                v.fecha_creacion,
                c.nombre_canal,
                u.nombre,
                u.avatar,
                cat.nombre AS nombre_cat
            FROM videos v
            LEFT JOIN canales c ON c.id = v.canal_id
            LEFT JOIN usuarios u ON c.usuario_id = u.id
            LEFT JOIN categorias cat ON cat.id = v.categoria_id 
            WHERE v.estado = 'publico' AND v.titulo LIKE " . $contenidoBusqueda . "
            LIMIT 17 OFFSET " . $offsetVideos . "
        ";

        $resultadosVideos = $this->con->prepare($qVideos);
        $estadoVideos = $resultadosVideos->execute();
        $respuestaVideos = $resultadosVideos->fetchAll(PDO::FETCH_ASSOC);

        if (!$estadoVideos)
        {
            echo EstadoFAIL();
            return; 
        }

        $videos = [];

        for ($i = 0; $i < count($respuestaVideos); $i++)
        {
            array_push($videos, VideosModelos::ResultadosBusqueda($respuestaVideos[$i]["titulo"], $respuestaVideos[$i]["descripcion"], $respuestaVideos[$i]["identificador"], $respuestaVideos[$i]["miniatura"], $respuestaVideos[$i]["visitas"], $respuestaVideos[$i]["fecha_creacion"], $respuestaVideos[$i]["duracion"], $respuestaVideos[$i]["nombre"], $respuestaVideos[$i]["nombre_canal"], $respuestaVideos[$i]["avatar"], $respuestaVideos[$i]["nombre_cat"]));
        }

        $masVideos = Paginacion::NoParametro(
            "
            SELECT v.titulo,
                v.descripcion,
                v.identificador,
                v.miniatura,
                v.visitas,
                v.duracion,
                v.fecha_creacion,
                c.nombre_canal,
                u.nombre,
                u.avatar,
                cat.nombre AS nombre_cat
            FROM videos v
            LEFT JOIN canales c ON c.id = v.canal_id
            LEFT JOIN usuarios u ON c.usuario_id = u.id
            LEFT JOIN categorias cat ON cat.id = v.categoria_id 
            WHERE v.estado = 'publico' AND v.titulo LIKE " . $contenidoBusqueda . "
            LIMIT 17 OFFSET " . $offsetVideos + 17 . "
        ", $this->con
        );

        echo RespuestaOK([
            "canales" => $canales,
            "videos" => $videos,
            "paginacion" => [
                "canales" => $masCanales,
                "videos" => $masVideos
            ]
        ]);

    }
    

}