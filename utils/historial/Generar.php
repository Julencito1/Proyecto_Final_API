<?php


namespace Utils\Historial;
use Controllers\Historial\Historial;
use Utils\Caracteres\Caracteres;
use PDO;


class Generar 
{

    protected $con;
    protected $historial;
    
    public function __construct(PDO $conexion, Historial $historial)
    {
        $this->con = $conexion;  
        $this->historial = $historial;  
    }

    public function GenerarIdentificador()
    {

        $estado = true;

        while ($estado) {
            $identificador = "";

            for ($x = 0; $x < 32; $x++) {

                $identificador .= Caracteres::$letras_numeros[rand(0, count(Caracteres::$letras_numeros) - 1)];
            }

            $consultaExiste = $this->con->prepare("SELECT COUNT(*) AS existe FROM " . Historial::$tabla . " WHERE identificador = :identificador");

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