<?php

namespace Controllers\Categorias;

use Models\Canales\Modelos;
use Models\Videos\Modelos as VideosModelos;
use PDO;
use Utils\auth\Auth;
use Utils\categorias\Existe;
use Utils\categorias\Obtener;
use Utils\paginacion\Paginacion;

class Categorias
{
    protected $con;
    private $ExtraExiste;
    private $ExtraGenerar;
    private $ExtraObtener;

    public function __construct($conexion)
    {
        $this->con = $conexion;
        
    }

    public function ObtenerCategorias()
    {
        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $q = "SELECT nombre FROM categorias";

        $obtenerCategorias = $this->con->prepare($q);
        $estado = $obtenerCategorias->execute();
        $respuesta = $obtenerCategorias->fetchAll(PDO::FETCH_ASSOC);

        if (!$estado)
        {
            echo EstadoFAIL();
            return;
        }

        echo RespuestaOK(["categorias" => $respuesta]);
    }

    public function TopCategorias()
    {

        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }


        $q = "
            SELECT cat.nombre
                FROM categorias cat
                LEFT JOIN videos v ON v.categoria_id = cat.id
                GROUP BY cat.id, cat.nombre
                ORDER BY COUNT(v.id) DESC
                LIMIT 5;
        ";

        $topCategorias = $this->con->prepare($q);
        $estado = $topCategorias->execute();
        $respuesta = $topCategorias->fetchAll(PDO::FETCH_ASSOC);

        if (!$estado)
        {
            echo EstadoFAIL();
            return;
        }

        echo RespuestaOK($respuesta);

    }

    public function VideosCategoria()
    {
        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        } 

        $filtroCategoria = file_get_contents("php://input");
        $datos = json_decode($filtroCategoria, true);
        $categoriaCuerpo = $datos["categoria"];
        $offset = $datos["offset"];


        $existe = Existe::ExisteCategoria($categoriaCuerpo, $this->con);

        if ($existe)
        {

            $idCategoria = Obtener::Id($categoriaCuerpo, $this->con);
            
            $q = "
                SELECT
                v.titulo,
                v.identificador,
                v.miniatura,
                v.visitas,
                v.duracion,
                v.fecha_creacion,
                u.nombre,
                u.avatar
            FROM videos v
            LEFT JOIN canales c ON c.id = v.canal_id
            LEFT JOIN usuarios u ON u.id = c.usuario_id
            WHERE v.estado = 'publico' AND v.categoria_id = ?
            ORDER BY v.visitas DESC
            LIMIT 20 OFFSET ". $offset ."
            ";

            $videosCategoria = $this->con->prepare($q);
            $videosCategoria->bindParam(1, $idCategoria);
            $estado = $videosCategoria->execute();
            $respuesta = $videosCategoria->fetchAll(PDO::FETCH_ASSOC);

            if (!$estado)
            {
                echo EstadoFAIL();
                return;
            }

            $videos = [];

            for ($k = 0; $k < count($respuesta); $k++)
            {
                array_push($videos, VideosModelos::VideosRecomendados($respuesta[$k]["titulo"], $respuesta[$k]["identificador"], $respuesta[$k]["miniatura"], $respuesta[$k]["visitas"], $respuesta[$k]["fecha_creacion"], $respuesta[$k]["duracion"], $respuesta[$k]["nombre"], $respuesta[$k]["avatar"]));
            }

            $mas = Paginacion::ContieneMasVideo(
                "
                SELECT
                v.titulo,
                v.identificador,
                v.miniatura,
                v.visitas,
                v.duracion,
                v.fecha_creacion,
                u.nombre,
                u.avatar
            FROM videos v
            LEFT JOIN canales c ON c.id = v.canal_id
            LEFT JOIN usuarios u ON u.id = c.usuario_id
            WHERE v.estado = 'publico' AND v.categoria_id = ?
            ORDER BY v.visitas 
            LIMIT 20 OFFSET ". $offset + 20 ."
            ", $this->con, $idCategoria
            );

            echo RespuestaOK(["videos" => $videos, "mas" => $mas]);

        } else {

            echo RespuestaFail("No se encontró la categoría.", 404);
        }
    }


    

}