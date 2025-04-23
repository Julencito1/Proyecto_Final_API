<?php

namespace Controllers\Usuarios;

use PDO;
use Structs\U\Usuarios\EstructuraUsuarios;
use Utils\Usuarios\Existe\Existe;
use Utils\Usuarios\Generar\Generar;
use Utils\Usuarios\Obtener\Obtener;

class Usuarios extends EstructuraUsuarios
{
    protected $con;
    protected static $tabla = "usuarios";
    private $ExtraExiste;
    private $ExtraGenerar;
    private $ExtraObtener;


    public function __construct($conexion)
    {
        $this->con = $conexion;
        $this->ExtraExiste = new Existe($conexion);
        $this->ExtraGenerar = new Generar($conexion);
        $this->ExtraObtener = new Obtener($conexion);
    }

    public function Registro()
    {
        $registro = file_get_contents("php://input");
        $datos = json_decode($registro, true);

        $noExiste = $this->ExtraExiste->ExisteUsuario($datos["email"]);
        $hash = password_hash($datos["contrase�a"], PASSWORD_DEFAULT);

        if ($noExiste) {

            $q = "INSERT INTO usuarios(nombre, email, contrase�a, identificador) VALUES (:nombre, :email, :contrase�a, :identificador)";

            $nuevoUsuario = $this->con->prepare($q);

            $estado = $nuevoUsuario->execute(["nombre" => $datos["nombre"], "email" => $datos["email"], "contrase�a" => $hash, "identificador" => $this->ExtraGenerar->GenerarIdentificador()]);


            if ($estado) {

                echo EstadoOK();
            } else {

                echo RespuestaFail("Algo ha salido mal");
            }

        } else {

            echo RespuestaFail("El correo ya est� en uso");
        }

    }

    public function Login()
    {
        $loginData = file_get_contents("php://input");
        $datos = json_decode($loginData, true);

        $p_hash = $this->ExtraObtener->ObtenerPasswordHash($datos["email"]);

        if (is_array($p_hash)) {

            if (in_array("S", $p_hash)) {

                if (!password_verify($datos["contrase�a"], $p_hash[0])) {

                    echo RespuestaFail("Correo o contrase�a incorrectos");

                } else {

                    $semilla = $this->con->prepare("SELECT identificador FROM " . $this->tabla . " WHERE email = :email");
                    $semilla->execute(["email" => $datos["email"]]);
                    $respuestaSemilla = $semilla->fetch(PDO::FETCH_ASSOC);

                    $jwt_token = $this->ExtraGenerar->GenerarToken($respuestaSemilla["identificador"]);

                    echo json_encode(["code" => 200, "status" => "success", "token" => $jwt_token], JSON_PRETTY_PRINT);
                }

            } else {

                echo RespuestaFail("Correo o contrase�a incorrectos");
            }

        } else {

            return InternalServerError();
        }
    }


}






?>