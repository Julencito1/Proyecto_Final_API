<?php


namespace Utils\RespuestasComentarios;
use Controllers\RespuestasComentarios\RespuestasComentarios;
use Utils\Caracteres\Caracteres;
use PDO;


class Generar 
{

    protected $con;
    protected $respuestas_comentarios;
    
    public function __construct(PDO $conexion, RespuestasComentarios $respuestas_comentarios)
    {
        $this->con = $conexion;  
        $this->respuestas_comentarios = $respuestas_comentarios;  
    }

    public function GenerarIdentificador()
    {

        $estado = true;

        while ($estado) {
            $identificador = "";

            for ($x = 0; $x < 32; $x++) {

                $identificador .= Caracteres::$letras_numeros[rand(0, count(Caracteres::$letras_numeros) - 1)];
            }

            $consultaExiste = $this->con->prepare("SELECT COUNT(*) AS existe FROM " . RespuestasComentarios::$tabla . " WHERE identificador = :identificador");

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