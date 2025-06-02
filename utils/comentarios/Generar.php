<?php


namespace Utils\Comentarios;
use Controllers\Comentarios\Comentarios;
use Utils\Caracteres\Caracteres;
use PDO;


class Generar 
{

    protected $con;
    protected $comentarios;
    
    public function __construct(PDO $conexion, Comentarios $comentarios)
    {
        $this->con = $conexion;  
        $this->comentarios = $comentarios;  
    }

    public function GenerarIdentificador()
    {

        $estado = true;

        while ($estado) {
            $identificador = "";

            for ($x = 0; $x < 36; $x++) {

                $identificador .= Caracteres::$letras_numeros[rand(0, count(Caracteres::$letras_numeros) - 1)];
            }

            $consultaExiste = $this->con->prepare("SELECT COUNT(*) AS existe FROM " . Comentarios::$tabla . " WHERE identificador = :identificador");

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