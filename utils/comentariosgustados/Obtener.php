<?php

namespace Utils\ComentariosGustados;
use Controllers\ComentariosGustados\ComentariosGustados;
use PDO;

class Obtener
{
    protected $con;
    protected $comentarios_gustados;

    public function __construct(PDO $conexion, ComentariosGustados $comentarios_gustados)
    {
        $this->con = $conexion;  
        $this->comentarios_gustados = $comentarios_gustados;  
    }

    public function EstaMarcado($comentarioID, $usuarioID)
    {
        
        $q = "
        
            SELECT gustado FROM comentarios_gustados WHERE comentario_id = ? AND usuario_id = ?
        ";

        $estaMarcado = $this->con->prepare($q);
        $estaMarcado->bindParam(1, $comentarioID);
        $estaMarcado->bindParam(2, $usuarioID);
        $estado = $estaMarcado->execute();
        $respuesta = $estaMarcado->fetch(PDO::FETCH_ASSOC);

        if (!$estado)
        {
            return "";
        }

        return $respuesta["gustado"] ?? "sindef";
    }
}

?>