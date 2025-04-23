<?php

namespace Utils\Videos;
use Utils\Caracteres\Caracteres;
use PDO;

class Generar extends Videos
{
    
    protected function GenerarIdentificador()
    {

        $estado = true;

        while ($estado) {
            $identificador = "";

            for ($x = 0; $x < 20; $x++) {

                $identificador .= Caracteres::$letras_numeros[rand(0, count(Caracteres::$letras_numeros) - 1)];
            }

            $consultaExiste = $this->con->prepare("SELECT COUNT(*) AS existe FROM " . self::$tabla . " WHERE identificador = :identificador");

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