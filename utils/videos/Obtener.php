<?php

namespace Utils\videos;
use Controllers\Videos\Videos;
use PDO;
use Utils\Usuarios\Obtener as UsuariosObtener;

class Obtener
{
    protected $con;
    protected $videos;

    public function __construct(PDO $conexion, Videos $videos)
    {
        $this->con = $conexion;
        $this->videos = $videos;
    }

    public static function Id($identificador, $con): int
    {
        if (!$identificador || $identificador === "")
        {
            return 0;
        }

        $q = "
        
            SELECT id FROM videos WHERE identificador = ?
        ";

        $identificador_video = $identificador;

        $obtenerID = $con->prepare($q);
        $obtenerID->bindParam(1, $identificador_video);
        $estado = $obtenerID->execute();
        $respuesta = $obtenerID->fetch(PDO::FETCH_ASSOC);

        if (!$estado)
        {
            return 0;
        }

        return $respuesta["id"];

    }

    public static function EsPublico($identificador, $cx): bool 
    {

        $q = "
            SELECT estado FROM videos WHERE identificador = ?
        ";

        $comprobar = $cx->prepare($q);
        $comprobar->bindParam(1, $identificador);
        $estado = $comprobar->execute();
        $respuesta = $comprobar->fetch(PDO::FETCH_ASSOC);

        if (!$estado)
        {
            return false;
        }

        return $respuesta["estado"] === "publico";

    }

    

}

?>