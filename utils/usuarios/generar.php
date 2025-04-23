<?php


namespace Utils\Usuarios\Generar;
require_once '../../vendor/autoload.php';
include "../caracteres/caracteres.php";
use Controllers\Usuarios\Usuarios;
use Utils\Caracteres\Caracteres;
use Firebase\JWT\JWT;
use PDO;


class Generar extends Usuarios
{

    protected function GenerarIdentificador()
    {

        $estado = true;

        while ($estado) {
            $identificador = "";

            for ($x = 0; $x < 36; $x++) {

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

    protected function GenerarToken($identificador)
    {
        $exp = 20000000;
        $s_key = "18dddd6d-bef4-44fe-9b92-f67030332b3f";
        $jwt_method = "HS256";

        $datos = [
            "seed" => $identificador,
            "exp" => $exp,
        ];

        $token = JWT::encode($datos, $s_key, $jwt_method);

        return $token;
    }
}



?>