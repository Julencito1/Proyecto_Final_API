<?php

namespace Utils\ComentariosHijosGustados;
use Controllers\ComentariosHijosGustados\ComentariosHijosGustados;
use PDO;

class Obtener
{
    protected $con;
    protected $comentarios_hijos_gustados;

    public function __construct(PDO $conexion, ComentariosHijosGustados $comentarios_hijos_gustados)
    {
        $this->con = $conexion;  
        $this->comentarios_hijos_gustados = $comentarios_hijos_gustados;  
    }

    public function EstaMarcado($comentarioID, $usuarioID)
    {
        
        $q = "
        
            SELECT gustado FROM comentarios_hijos_gustados WHERE comentario_id = ? AND usuario_id = ?
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