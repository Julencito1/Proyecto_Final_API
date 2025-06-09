<?php


namespace Utils\usuarios;
use Controllers\Usuarios\Usuarios;
use Utils\Caracteres\Caracteres;
use Firebase\JWT\JWT;
use PDO;


class Generar 
{

    protected $con;
    protected $usuarios;
    
    public function __construct(PDO $conexion, Usuarios $usuarios)
    {
        $this->con = $conexion;  
        $this->usuarios = $usuarios;  
    }

    public function GenerarIdentificador()
    {

        $estado = true;

        while ($estado) {
            $identificador = "";

            for ($x = 0; $x < 36; $x++) {

                $identificador .= Caracteres::$letras_numeros[rand(0, count(Caracteres::$letras_numeros) - 1)];
            }

            $consultaExiste = $this->con->prepare("SELECT COUNT(*) AS existe FROM " . Usuarios::$tabla . " WHERE identificador = :identificador");

            $consultaExiste->execute(["identificador" => $identificador]);

            $respuesta = $consultaExiste->fetch(PDO::FETCH_ASSOC);

            if ($respuesta["existe"] === 0) {
                return $identificador;
            }


        }

        return "";

    }

    public function GenerarToken($identificador)
    {
        $exp = time() + 1000000;
        $s_key = "18dddd6d-bef4-44fe-9b92-f67030332b3f";
        $jwt_method = "HS256";

        $datos = [
            "seed" => $identificador,
            "exp" => $exp,
        ];

        $token = JWT::encode($datos, $s_key, $jwt_method);

        return $token;
    }

    public function GenerarAvatar(): string
    {
        $avatares = [
            "newtube_avatar1.webp",
            "newtube_avatar2.webp",
            "newtube_avatar3.webp",
            "newtube_avatar4.webp",
            "newtube_avatar5.webp",
            "newtube_avatar6.webp",
            "newtube_avatar7.webp",
            "newtube_avatar8.webp",
            "newtube_avatar9.webp",
        ];

        return "http://localhost:8081/file?file=./usuarios/" . $avatares[rand(0, count($avatares) - 1)];
    }

    
}



?>