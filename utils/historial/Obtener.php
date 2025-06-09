<?php

namespace Utils\historial;
use Controllers\Historial\Historial;
use PDO;
use Utils\Usuarios\Obtener as UsuariosObtener;
use Utils\Videos\Obtener as VideosObtener;

class Obtener
{
    protected $con;
    protected $historial;

    public function __construct(PDO $conexion, Historial $historial)
    {
        $this->con = $conexion;  
        $this->historial = $historial;  
    }

    public function GuardadoMismoDia($identificador_video, $identificador_usuario): bool
    {

        $usuarioID = UsuariosObtener::Id($identificador_usuario, $this->con);
        $videoID = VideosObtener::Id($identificador_video, $this->con);

        $q = "
            SELECT EXISTS(
                SELECT id FROM historial WHERE DATE(fecha_visualizacion) = CURDATE() AND usuario_id = ? AND video_id = ?
            ) AS c
        ";

        $comprobar = $this->con->prepare($q);
        $comprobar->bindParam(1, $usuarioID);
        $comprobar->bindParam(2, $videoID);
        $estado = $comprobar->execute();
        $respuesta = $comprobar->fetch(PDO::FETCH_ASSOC);

        if (!$estado)
        {
            return true;
        }

        return $respuesta["c"] > 0;
    }

    public static function Visto($identificador_video, $identificador_usuario, $cx): bool
    {
        $usuarioID = UsuariosObtener::Id($identificador_usuario, $cx);
        $videoID = VideosObtener::Id($identificador_video, $cx);

        $q = "
        
            SELECT EXISTS(
                SELECT id FROM historial WHERE usuario_id = ? AND video_id = ?
            ) AS c
        ";

        $comprobarVisto = $cx->prepare($q);
        $comprobarVisto->bindParam(1, $usuarioID);
        $comprobarVisto->bindParam(2, $videoID);
        $estado = $comprobarVisto->execute();
        $respuesta = $comprobarVisto->fetch(PDO::FETCH_ASSOC);

        if (!$estado)
        {
            return true;
        }

        return $respuesta["c"] > 0;
    }


}

?>