<?php


namespace Utils\Canales;

use Controllers\Canales\Canales;
use PDO;

class Existe
{
    protected $con;
    protected $canales;


    public function __construct(PDO $conexion, Canales $canales)
    {
        $this->con = $conexion;
        $this->canales = $canales;
    }

    public function ExisteCanal(string $nombre_canal): int
    {

        $canal = $nombre_canal;

        $q = "SELECT EXISTS(SELECT id FROM " . $this->canales::$tabla . " WHERE nombre_canal = ?) AS e";
        $buscarCanal = $this->con->prepare($q);
        $buscarCanal->bindParam(1, $canal);

        $estado = $buscarCanal->execute();
        $respuesta = $buscarCanal->fetch(PDO::FETCH_ASSOC);


        if (!$estado) {
            return false;
        }

        return $respuesta["e"];

    }

}







