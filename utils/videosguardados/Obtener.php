<?php

namespace Utils\videosGuardados;
use Controllers\VideosGuardados\VideosGuardados;
use PDO;


class Obtener
{
    protected $con;
    protected $videos_guardados;

    public function __construct(PDO $conexion, VideosGuardados $videos_guardados)
    {
        $this->con = $conexion;  
        $this->videos_guardados = $videos_guardados;  
    }

    public static function EstaGuardado($identificador, $usuarioID, $conexion): bool
    {
        
        if ($identificador === "")
        {
            return false;
        }

        $identificador_videos = $identificador;
        $usuario = $usuarioID;

        $q = "
            SELECT EXISTS(
                SELECT vg.id FROM videos_guardados vg
                LEFT JOIN videos v ON v.id = vg.video_id
                WHERE vg.usuario_id = ? AND v.identificador = ?
            ) AS guardado
    
        ";

        $guardado = $conexion->prepare($q);
        $guardado->bindParam(1, $usuario);
        $guardado->bindParam(2, $identificador);
        $estado = $guardado->execute();
        $respuesta = $guardado->fetch(PDO::FETCH_ASSOC);

        if (!$estado)
        {
            return false;
        }

        return $respuesta["guardado"] > 0;

    }

    public static function Id($identificador, $cx): int 
    {
        $identificadorVideoGuardado = $identificador;

        if ($identificador === "") 
        {
            return 0;
        }

        $q = "
            SELECT id FROM videos_guardados WHERE identificador = ?
        ";

        $obtenerID = $cx->prepare($q);
        $obtenerID->bindParam(1, $identificadorVideoGuardado);
        $estado = $obtenerID->execute();
        $respuesta = $obtenerID->fetch(PDO::FETCH_ASSOC);

        if (!$estado)
        {
            return 0;
        }

        return $respuesta["id"];
    }
}

?>