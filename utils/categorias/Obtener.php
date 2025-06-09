<?php

namespace Utils\Categorias;
use Controllers\Categorias\Categorias;
use PDO;


class Obtener
{
    protected $con;
    protected $categorias;

    public function __construct(PDO $conexion, Categorias $categorias)
    {
        $this->con = $conexion;  
        $this->categorias = $categorias;  
    }


    public static function Id($nombre, $cx)
    {
        
        $q = "
            SELECT id FROM categorias WHERE nombre = ?
        ";

        $obtenerId = $cx->prepare($q);
        $obtenerId->bindParam(1, $nombre);
        $estado = $obtenerId->execute();
        $respuesta = $obtenerId->fetch(PDO::FETCH_ASSOC);


        if (!$estado)
        {
            return 0;
        }
       
        return $respuesta['id'];

    }
    

}

?>