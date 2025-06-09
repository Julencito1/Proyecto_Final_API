<?php

namespace Controllers\Historial;
use Models\Historial\Modelos;
use PDO;
use Utils\Auth\Auth;
use Utils\Historial\Generar;
use Utils\Historial\Obtener;
use Utils\Paginacion\Paginacion;
use Utils\Usuarios\Obtener as UsuariosObtener;
use Utils\Videos\Existe;
use Utils\Videos\Obtener as VideosObtener;

class Historial
{
    protected $con;
    public static $tabla = "historial";
    private $ExtraExiste;
    private $ExtraGenerar;
    private $ExtraObtener;


    public function __construct($conexion)
    {
        $this->con = $conexion;
        $this->ExtraGenerar = new Generar($conexion, $this);
        $this->ExtraObtener = new Obtener($conexion, $this);
    }

    public function AlmacenarHistorial()
    {
        
        $historial = file_get_contents("php://input");
        $datos = json_decode($historial, true);

        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $identificador_video = $datos["identificador"];

        $existe = Existe::ExisteVideo($identificador_video, $this->con);

        if ($existe)
        {

            $guardadoRecientemente = $this->ExtraObtener->GuardadoMismoDia($identificador_video, $identificador);
            $usuarioID = UsuariosObtener::Id($identificador, $this->con);
            $videoID = VideosObtener::Id($identificador_video, $this->con);
            $generarIdentificador = $this->ExtraGenerar->GenerarIdentificador();
            $visto = Obtener::Visto($identificador_video, $identificador, $this->con);


            if (!$guardadoRecientemente)
            {
                $q = "
                    INSERT INTO historial(usuario_id, video_id, identificador) VALUES (?, ?, ?)
                ";

                $almacenarHistorial = $this->con->prepare($q);
                $almacenarHistorial->bindParam(1, $usuarioID);
                $almacenarHistorial->bindParam(2, $videoID);
                $almacenarHistorial->bindParam(3, $generarIdentificador);
                $estado = $almacenarHistorial->execute();
                
                if (!$estado)
                {
                    echo EstadoFAIL();
                    return;
                }

                echo EstadoOK();
            } else {

                echo EstadoOK();
            }

            if (!$visto)
            {
                $q = "
                    UPDATE videos SET visitas = visitas + 1 WHERE id = ?
                ";

                $aumentarVista = $this->con->prepare($q);
                $aumentarVista->bindParam(1, $videoID);
                $estado = $aumentarVista->execute();
                
                if (!$estado)
                {
                    echo EstadoFAIL();
                    return;
                }

                echo EstadoOK();
            }

        } else {
            echo RespuestaFail("No se encontró el video.", 404);
        }

    }

    public function ObtenerHistorial()
    {
        $historial = file_get_contents("php://input");
        $datos = json_decode($historial, true);

        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }
        
        $offset = $datos["offset"];
        $busqueda = "'%" . $datos["busqueda"] . "%'";
       
        $usuarioID = UsuariosObtener::Id($identificador, $this->con);
        
        $q = "
            SELECT 
                v.titulo,
                v.identificador,
                v.miniatura,
                v.visitas,
                v.duracion,
                v.fecha_creacion,
                u.avatar,
                u.nombre,
                h.fecha_visualizacion
            FROM historial h
            LEFT JOIN videos v ON h.video_id = v.id
            LEFT JOIN canales c ON v.canal_id = c.id
            LEFT JOIN usuarios u ON c.usuario_id = u.id
            WHERE h.usuario_id = ? AND v.estado = 'publico' AND v.titulo LIKE " . $busqueda . " 
            ORDER BY fecha_visualizacion DESC
            LIMIT 20 OFFSET " . $offset . "
        ";

        $obtenerHistorial = $this->con->prepare($q);
        $obtenerHistorial->bindParam(1, $usuarioID);
        $estado = $obtenerHistorial->execute();
        $respuesta = $obtenerHistorial->fetchAll(PDO::FETCH_ASSOC);
        
        if (!$estado)
        {
            echo EstadoFAIL();
            return;
        }

        $videosHistorial = [];

        for ($i = 0; $i < count($respuesta); $i++)
        {
            array_push($videosHistorial, Modelos::ObtenerHistorial($respuesta[$i]["titulo"], $respuesta[$i]["identificador"], $respuesta[$i]["miniatura"], $respuesta[$i]["visitas"], $respuesta[$i]["duracion"], $respuesta[$i]["fecha_creacion"], $respuesta[$i]["avatar"], $respuesta[$i]["nombre"], $respuesta[$i]["fecha_visualizacion"]));
        }

        $mas = Paginacion::ContieneMas(
            "
            SELECT 
                v.titulo,
                v.identificador,
                v.miniatura,
                v.visitas,
                v.duracion,
                v.fecha_creacion,
                u.avatar,
                u.nombre,
                h.fecha_visualizacion
            FROM historial h
            LEFT JOIN videos v ON h.video_id = v.id
            LEFT JOIN canales c ON v.canal_id = c.id
            LEFT JOIN usuarios u ON c.usuario_id = u.id
            WHERE h.usuario_id = ? AND v.estado = 'publico' AND v.titulo LIKE " . $busqueda . " 
            ORDER BY fecha_visualizacion DESC
            LIMIT 20 OFFSET " . $offset + 20 . "
        ", $this->con, $usuarioID
        );

        echo RespuestaOK(["historial" => $videosHistorial, "mas" => $mas]);
    }


}






?>