<?php 

require_once '../vendor/autoload.php';
include "../api/respuestas.php";

use Firebase\JWT\JWT;




class Usuario {

    private $con;
    private $tabla = "usuarios";

    public function __construct($conexion)
    {
        $this->con = $conexion;
    }

    private function GenerarIdentificador() 
    {

        $estado = true;
        $abc = [
                "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r",
                "s", "t", "u", "v", "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J",
                "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1",
                "2", "3", "4", "5", "6", "7", "8", "9"
            ];
        
        while ($estado) 
        {
            $identificador = "";

            for ($x = 0; $x < 36; $x++) 
            {

                $identificador .= $abc[rand(0, count($abc) - 1)];
            }
    
            $consultaExiste = $this->con->prepare("SELECT COUNT(*) AS existe FROM " . $this->tabla . " WHERE identificador = :identificador");
            
            $consultaExiste->execute(["identificador" => $identificador]);
    
            $respuesta = $consultaExiste->fetch(PDO::FETCH_ASSOC);
            
            if ($respuesta["existe"] === 0) 
            {
                return $identificador;
                break;
            } 


        }

        return "";
    
    } 

    private function GenerarToken($identificador)
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

    public function Login()
    {
        $loginData = file_get_contents("php://input");
        $datos = json_decode($loginData, true);

        $consulta = $this->con->prepare("SELECT COUNT(*) AS coincide FROM " . $this->tabla . " WHERE email = :email AND password = :password");
        $consulta->execute(["email" => $datos["email"], "password" => $datos["password"]]);
        $respuesta = $consulta->fetch(PDO::FETCH_ASSOC);

        
        if ($respuesta["coincide"] === 0) {

            echo RespuestaFail("Correo o contraseña incorrectos");
            
        } else {

            $semilla = $this->con->prepare("SELECT identificador FROM " . $this->tabla . " WHERE email = :email AND password = :password");
            $semilla->execute(["email" => $datos["email"], "password" => $datos["password"]]);
            $respuestaSemilla = $semilla->fetch(PDO::FETCH_ASSOC);

            $jwt_token = $this->GenerarToken($respuestaSemilla["identificador"]);
            
            echo json_encode(["code" => 200, "status" => "success", "token" => $jwt_token], JSON_PRETTY_PRINT);
        }
    }

    
}



?>