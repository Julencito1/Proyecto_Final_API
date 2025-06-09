<?php

namespace Utils\respuestasComentarios;

use Controllers\RespuestasComentarios\RespuestasComentarios;
use PDO;

class Obtener
{
    protected $con;
    protected $respuestas_comentarios;

    public function __construct(PDO $conexion, RespuestasComentarios $respuestas_comentarios)
    {
        $this->con = $conexion;  
        $this->respuestas_comentarios = $respuestas_comentarios;  
    }

    public static function Id($identificador, $cx)
    {
        $q = "
            SELECT id FROM respuestas_comentarios WHERE identificador = ?
        
        ";

        $obtenerID = $cx->prepare($q);
        $obtenerID->bindParam(1, $identificador);
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