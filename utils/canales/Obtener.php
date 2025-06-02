<?php

namespace Utils\Canales;
use Controllers\Canales\Canales;
use PDO;

class Obtener
{
    protected $con;
    protected $canales;

    public function __construct(PDO $conexion, Canales $canales)
    {
        $this->con = $conexion;
        $this->canales = $canales;
    }

    public static function EsSuscriptor($identificador, $canal, $cx): bool
    {

        if ($identificador === "") return false;
       
        $q = "
            SELECT EXISTS(

                SELECT s.id 
                FROM suscripciones s
                LEFT JOIN usuarios u ON u.id = s.usuario_id
                LEFT JOIN canales c ON c.id = s.canal_id
                WHERE u.identificador = ?
                AND c.nombre_canal = ? 
            ) AS existe

        ";

        $identificadorUsuarioActual = $identificador;
        $canalVisitado = $canal;


        $esSuscriptorCanal = $cx->prepare($q);
        $esSuscriptorCanal->bindParam(1, $identificadorUsuarioActual);
        $esSuscriptorCanal->bindParam(2, $canalVisitado);
        $estado = $esSuscriptorCanal->execute();
        $respuesta = $esSuscriptorCanal->fetch(PDO::FETCH_ASSOC);

        if (!$estado)
        {
            echo EstadoFAIL();
            return false;
        }

        return $respuesta["existe"] > 0;

    }

    public static function EsSuscriptorVideo($usuarioID, $identificador_video, $cx): bool
    {

        $consultaIdCanal = "SELECT canal_id FROM videos WHERE identificador = ?";

        $idCanal = $cx->prepare($consultaIdCanal);
        $idCanal->bindParam(1, $identificador_video);
        $estadoIdCanal = $idCanal->execute();
        $respuestaCanal = $idCanal->fetch(PDO::FETCH_ASSOC);

        if (!$estadoIdCanal)
        {
            echo EstadoFAIL();
            return false;
        }
       
        $q = "
            SELECT EXISTS(

                SELECT id FROM suscripciones WHERE usuario_id = ? AND canal_id = ?
            ) AS existe

        ";
        


        $esSuscriptorCanal = $cx->prepare($q);
        $esSuscriptorCanal->bindParam(1, $usuarioID);
        $esSuscriptorCanal->bindParam(2, $respuestaCanal["canal_id"]);
        $estado = $esSuscriptorCanal->execute();
        $respuesta = $esSuscriptorCanal->fetch(PDO::FETCH_ASSOC);

        if (!$estado)
        {
            echo EstadoFAIL();
            return false;
        }

        return $respuesta["existe"] > 0;

    }

    public static function Id($canal, $cx): int 
    {

        $nombre_canal = $canal;

        if ($nombre_canal === "") 
        {
            return 0;
        }

        $q = "
        SELECT id FROM canales WHERE nombre_canal = ?
        ";

        $obtenerID = $cx->prepare($q);
        $obtenerID->bindParam(1, $nombre_canal);
        $estado = $obtenerID->execute();
        $respuesta = $obtenerID->fetch(PDO::FETCH_ASSOC);

        if (!$estado) 
        {
            return 0;
        }

        return $respuesta["id"];
    }

    public function EsActual($identificador, $canal): bool|null
    {

        if ($identificador === "") return false;

        $canalID = self::Id($canal, $this->con);

        $q = "
            SELECT c.id FROM canales c
            LEFT JOIN usuarios u ON u.id = c.usuario_id
            WHERE u.identificador = ?
        ";

        $comprobar = $this->con->prepare($q);
        $comprobar->bindParam(1, $identificador);
        $estado = $comprobar->execute();
        $respuesta = $comprobar->fetch(PDO::FETCH_ASSOC);

        if (!$estado)
        {
            return null;
        }

        return $respuesta["id"] === $canalID;

    }

    public static function EsActualPorVideo($identificador_video, $usuarioID, $cx): bool
    {
        $q = "
            SELECT u.id 
            FROM videos v
            LEFT JOIN canales c ON c.id = v.canal_id
            LEFT JOIN usuarios u ON u.id = c.usuario_id
            WHERE v.identificador = ?
        ";

        $obtenerUsuarioIDVideo = $cx->prepare($q);
        $obtenerUsuarioIDVideo->bindParam(1, $identificador_video);
        $estado = $obtenerUsuarioIDVideo->execute();
        $respuesta = $obtenerUsuarioIDVideo->fetch(PDO::FETCH_ASSOC);

        if (!$estado)
        {
            return true;
        }

        return $respuesta["id"] === $usuarioID;
    }

}

?>