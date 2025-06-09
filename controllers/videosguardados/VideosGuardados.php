<?php

namespace Controllers\VideosGuardados;

use Models\VideosGuardados\Modelos;
use PDO;
use Utils\auth\Auth;
use Utils\paginacion\Paginacion;
use Utils\usuarios\Obtener;
use Utils\videosGuardados\Obtener as VideosGuardadosObtener;

class VideosGuardados
{
    protected $con;
    public static $tabla = "videos_guardados";
    private $ExtraExiste;
    private $ExtraGenerar;
    private $ExtraObtener;


    public function __construct($conexion)
    {
        $this->con = $conexion;
        
    }

    public function VideosGuardados()
    {

        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $canal = file_get_contents("php://input");
        $datos = json_decode($canal, true);
        $limit = $datos["limit"];
        $offset = $datos["offset"];
        $textoBuscar = "'%" . $datos["buscar"] . "%'";


        $orderby = "";

        switch ($datos["filtro"])
        {
            case "mas_recientes":
                $orderby = "vg.fecha DESC";
                break;
            case "mas_antiguos":
                $orderby = "vg.fecha ASC";
                break;
            case "mas_visualizaciones":
                $orderby = "v.visitas DESC";
                break;
            case "menos_visualizaciones":
                $orderby = "v.visitas ASC";
                break;
            default:
                $orderby = "vg.fecha DESC";
                break;
        }

        $usuarioID = Obtener::Id($identificador, $this->con);
        
        $q = "
            SELECT v.titulo,
                v.identificador AS identificador_video,
                v.miniatura,
                v.visitas,
                v.duracion,
                v.fecha_creacion,
                c.nombre_canal,
                u.avatar,
                u.nombre,
                vg.identificador
            FROM videos_guardados vg
            LEFT JOIN videos v ON vg.video_id = v.id
            LEFT JOIN canales c ON v.canal_id = c.id
            LEFT JOIN usuarios u ON c.usuario_id = u.id
            WHERE vg.usuario_id = ? AND v.estado = 'publico' AND v.titulo LIKE " . $textoBuscar . " 
            ORDER BY " . $orderby . "
            LIMIT " . $limit . " OFFSET " . $offset . "
        ";


        $datosVideosGuardados = $this->con->prepare($q);
        $datosVideosGuardados->bindParam(1, $usuarioID);
        $estado = $datosVideosGuardados->execute();
        $respuesta = $datosVideosGuardados->fetchAll(PDO::FETCH_ASSOC);


        if (!$estado)
        {
            echo EstadoFAIL();
            return;
        }

        $contenedor = [];

        for ($i = 0; $i < count($respuesta); $i++)
        {
            array_push($contenedor, Modelos::VideosGuardados($respuesta[$i]["titulo"], $respuesta[$i]["identificador_video"], $respuesta[$i]["miniatura"], $respuesta[$i]["visitas"], $respuesta[$i]["duracion"], $respuesta[$i]["fecha_creacion"], $respuesta[$i]["nombre_canal"], $respuesta[$i]["avatar"], $respuesta[$i]["nombre"], $respuesta[$i]["identificador"]));
        }

        $mas = Paginacion::ContieneMas(
    "
                SELECT v.titulo,
                    v.identificador AS identificador_video,
                    v.miniatura,
                    v.visitas,
                    v.duracion,
                    v.fecha_creacion,
                    c.nombre_canal,
                    u.avatar,
                    u.nombre,
                    vg.identificador
                FROM videos_guardados vg
                LEFT JOIN videos v ON vg.video_id = v.id
                LEFT JOIN canales c ON v.canal_id = c.id
                LEFT JOIN usuarios u ON c.usuario_id = u.id
                WHERE vg.usuario_id = ? AND v.estado = 'publico' AND v.titulo LIKE " . $textoBuscar . " 
                ORDER BY " . $orderby . "
                LIMIT " . $limit . " OFFSET " . $offset+20 . "
            ",
            $this->con,
            $usuarioID,
        );
        
        echo RespuestaOK(["contenedor" => $contenedor, "siguiente" => $mas]);

    }

    public function QuitarVideoGuardado()
    {
        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $canal = file_get_contents("php://input");
        $datos = json_decode($canal, true);

        $identificador_video = $datos["identificador"];
        $videoGuardadoID = VideosGuardadosObtener::Id($identificador_video, $this->con);

        

            $q = "
                DELETE FROM videos_guardados WHERE id = ?
            "; 

            $quitarVideo = $this->con->prepare($q);
            $quitarVideo->bindParam(1, $videoGuardadoID);
            $estado = $quitarVideo->execute();

            if (!$estado)
            {
                echo EstadoFAIL();
                return;
            }

            echo EstadoOK();

        
    }


}






?>