<?php

namespace Utils\videosMarcados;
use Controllers\VideosMarcados\VideosMarcados;
use PDO;
use Utils\Usuarios\Obtener as UsuariosObtener;
use Utils\Videos\Obtener as VideosObtener;

class Obtener
{
    protected $con;
    protected $videos_marcados;

    public function __construct(PDO $conexion, VideosMarcados $videos_marcados)
    {
        $this->con = $conexion;  
        $this->videos_marcados = $videos_marcados;  
    }

    public static function Gustado($identificador_video, $identificador_usuario, $cx): string
    {
        $usuarioID = UsuariosObtener::Id($identificador_usuario, $cx);
        $videoID = VideosObtener::Id($identificador_video, $cx);

        $q = "
            
            SELECT gustado FROM videos_marcados WHERE usuario_id = ? AND video_id = ?
            
        ";

        $gustadoC = $cx->prepare($q);
        $gustadoC->bindParam(1, $usuarioID);
        $gustadoC->bindParam(2, $videoID);
        $estado = $gustadoC->execute();
        $respuesta = $gustadoC->fetch(PDO::FETCH_ASSOC);

        if (!$estado)
        {
            return "";
        }

       
        return $respuesta["gustado"] ?? "sindef";
    }
}

?>