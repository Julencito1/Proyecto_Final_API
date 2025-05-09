<?php

namespace Utils\Canales;
use Controllers\Canales\Canales;
use PDO;
use Utils\Usuarios\Obtener as UsuariosObtener;

class Obtener
{
    protected $con;
    protected $canales;

    public function __construct(PDO $conexion, Canales $canales)
    {
        $this->con = $conexion;
        $this->canales = $canales;
    }

    public function EsSuscriptor($identificador, $canal): bool
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


        $esSuscriptorCanal = $this->con->prepare($q);
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

}

?>