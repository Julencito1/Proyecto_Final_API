<?php

namespace Controllers\RespuestasComentarios;

use Utils\Auth\Auth;
use Utils\Comentarios\Obtener as ComentariosObtener;
use Utils\RespuestasComentarios\Generar;
use Utils\RespuestasComentarios\Obtener as RespuestasComentariosObtener;
use Utils\Usuarios\Obtener;
use Utils\Videos\Existe as VideosExiste;


class RespuestasComentarios
{
    protected $con;
    public static $tabla = "respuestas_comentarios";
    private $ExtraExiste;
    private $ExtraGenerar;
    private $ExtraObtener;


    public function __construct($conexion)
    {
        $this->con = $conexion;
        $this->ExtraObtener = new RespuestasComentariosObtener($conexion, $this);
        $this->ExtraGenerar = new Generar($conexion, $this);
    }

    public function PublicarComentarioHijo()
    {

        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $video = file_get_contents("php://input");
        $datos = json_decode($video, true);

        $identificador_video = $datos["identificador"];
        $identificador_comentario = $datos["identificador_comentario"];
        $existe = VideosExiste::ExisteVideo($identificador_video, $this->con);

        if ($existe)
        {

            $usuarioID = Obtener::Id($identificador, $this->con);
            $generarIdentificador = $this->ExtraGenerar->GenerarIdentificador();
            $comentario_padre_id = ComentariosObtener::Id($identificador_comentario, $this->con);
            $comentario = $datos["comentario"];

            $q  = "
                INSERT INTO respuestas_comentarios(comentario_padre_id, usuario_id, contenido, identificador) VALUES (?, ?, ?, ?)
            ";

            $comentar = $this->con->prepare($q);
            $comentar->bindParam(1, $comentario_padre_id);
            $comentar->bindParam(2, $usuarioID);
            $comentar->bindParam(3, $comentario);
            $comentar->bindParam(4, $generarIdentificador);
            $estado = $comentar->execute();

            if (!$estado)
            {
                echo EstadoFAIL();
                return;
            }

            echo EstadoOK();

        } else {
            echo RespuestaFail("No se encontró el video.", 404);
        }

    }

}






?>