<?php

namespace Utils\Categorias;
use Controllers\Categorias\Categorias;
use PDO;


class Existe
{
    protected $con;
    protected $categorias;

    public function __construct(PDO $conexion, Categorias $categorias)
    {
        $this->con = $conexion;  
        $this->categorias = $categorias;  
    }


    public static function ExisteCategoria($nombre, $cx): bool
    {
        
        $q = "
            SELECT COUNT(*) AS c FROM categorias WHERE nombre = ?
        ";

        $validarExistencia = $cx->prepare($q);
        $validarExistencia->bindParam(1, $nombre);
        $estado = $validarExistencia->execute();
        $respuesta = $validarExistencia->fetch(PDO::FETCH_ASSOC);


        if (!$estado)
        {
            return false;
        }

        return $respuesta['c'] > 0;

    }
    

}

?>