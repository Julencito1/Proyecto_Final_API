<?php


namespace Utils\videosGuardados;
use Controllers\VideosGuardados\VideosGuardados;
use Utils\Caracteres\Caracteres;
use Firebase\JWT\JWT;
use PDO;


class Generar 
{

    protected $con;
    protected $videos_guardados;
    
    public function __construct(PDO $conexion, VideosGuardados $videos_guardados)
    {
        $this->con = $conexion;  
        $this->videos_guardados = $videos_guardados;  
    }

    public static function GenerarIdentificador($con)
    {

        $estado = true;

        while ($estado) {
            $identificador = "";

            for ($x = 0; $x < 36; $x++) {

                $identificador .= Caracteres::$letras_numeros[rand(0, count(Caracteres::$letras_numeros) - 1)];
            }

            $consultaExiste = $con->prepare("SELECT COUNT(*) AS existe FROM " . VideosGuardados::$tabla . " WHERE identificador = :identificador");

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