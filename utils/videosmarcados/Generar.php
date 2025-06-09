<?php


namespace Utils\videosMarcados;
use Controllers\VideosMarcados\VideosMarcados;
use Utils\Caracteres\Caracteres;

use PDO;


class Generar 
{

    protected $con;
    protected $videos_marcados;
    
    public function __construct(PDO $conexion, VideosMarcados $videos_marcados)
    {
        $this->con = $conexion;  
        $this->videos_marcados = $videos_marcados;  
    }

    public static function GenerarIdentificador($con)
    {

        $estado = true;

        while ($estado) {
            $identificador = "";

            for ($x = 0; $x < 36; $x++) {

                $identificador .= Caracteres::$letras_numeros[rand(0, count(Caracteres::$letras_numeros) - 1)];
            }

            $consultaExiste = $con->prepare("SELECT COUNT(*) AS existe FROM " . VideosMarcados::$tabla . " WHERE identificador = :identificador");

            $consultaExiste->execute(["identificador" => $identificador]);

            $respuesta = $consultaExiste->fetch(PDO::FETCH_ASSOC);

            if ($respuesta["existe"] === 0) {
                return $identificador;
            }


        }

        return "";

    }

    
}



?>