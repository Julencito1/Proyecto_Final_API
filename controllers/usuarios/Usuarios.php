<?php

namespace Controllers\Usuarios;

use Controllers\Canales\Canales;
use PDO;
use Structs\usuarios\EstructuraUsuarios;
use Utils\Auth\Auth;
use Utils\Usuarios\Existe;
use Utils\Usuarios\Generar;
use Utils\Usuarios\Obtener;
use Models\Usuarios\Modelos;
include __DIR__ . "../../../response/respuestas.php";

class Usuarios extends EstructuraUsuarios
{
    protected $con;
    public static $tabla = "usuarios";
    private $ExtraExiste;
    private $ExtraGenerar;
    private $ExtraObtener;
    private $Canales;
    private $Modelos;


    public function __construct($conexion)
    {
        $this->con = $conexion;
        $this->ExtraExiste = new Existe($conexion, $this);
        $this->ExtraGenerar = new Generar($conexion, $this);
        $this->ExtraObtener = new Obtener($conexion, $this);
        $this->Canales = new Canales($conexion);
        $this->Modelos = new Modelos($this);
    }

    public function Registro()
    {
        $registro = file_get_contents("php://input");
        $datos = json_decode($registro, true);

        $noExiste = $this->ExtraExiste->ExisteUsuario($datos["email"]);
        $hash = password_hash($datos["password"], PASSWORD_DEFAULT);

        if ($noExiste === 0) {

            $ident = $this->ExtraGenerar->GenerarIdentificador();
            $nombre_pasado = $datos['nombre'];
            $email_pasado = $datos['email'];
            $avatar_random = $this->ExtraGenerar->GenerarAvatar();


            $q = "INSERT INTO usuarios(nombre, email, password, identificador, avatar) VALUES (?, ?, ?, ?, ?)";

            $nuevoUsuario = $this->con->prepare($q);
            $nuevoUsuario->bindParam(1, $nombre_pasado);
            $nuevoUsuario->bindParam(2, $email_pasado);
            $nuevoUsuario->bindParam(3, $hash);
            $nuevoUsuario->bindParam(4, $ident);
            $nuevoUsuario->bindParam(5, $avatar_random);

            $estado = $nuevoUsuario->execute();

            if ($estado) {

                $usuarioID = $this->ExtraObtener->ObtenerID($datos['email']);

                if ($usuarioID === 0) {
                    echo RespuestaFail("Algo ha salido mal");
                }

                $crearCanal = $this->Canales->CrearCanal($usuarioID, $datos["nombre"]);

                if (!$crearCanal)
                {
                    echo RespuestaFail("No se pudo crear la canal");
                }

                echo EstadoOK();
            } else {

                echo RespuestaFail("Algo ha salido mal");
            }

        } else {

            echo RespuestaFail("El correo ya está en uso");

        }

    }

    public function Login()
    {

        $loginData = file_get_contents("php://input");
        $datos = json_decode($loginData, true);

        $noExiste = $this->ExtraExiste->ExisteUsuario($datos["email"]);

   
        if ($noExiste === 0)
        {
            echo RespuestaFail("Correo o contraseña incorrectos");
            return;
        }

        $p_hash = $this->ExtraObtener->ObtenerPasswordHash($datos["email"]);

        if (is_array($p_hash)) {

            if (in_array("S", $p_hash)) {

                if (!password_verify($datos["password"], $p_hash[0])) {

                    echo RespuestaFail("Correo o contraseña incorrectos");

                } else {

                    $semilla = $this->con->prepare("SELECT identificador FROM " . self::$tabla . " WHERE email = :email");
                    $semilla->execute(["email" => $datos["email"]]);
                    $respuestaSemilla = $semilla->fetch(PDO::FETCH_ASSOC);

                    $jwt_token = $this->ExtraGenerar->GenerarToken($respuestaSemilla["identificador"]);

                    echo json_encode(["code" => 200, "status" => "success", "token" => $jwt_token], JSON_PRETTY_PRINT);
                }

            } else {

                echo RespuestaFail("Correo o contraseña incorrectos");

            }

        } else {

            return InternalServerError();
        }
    }

    public function DatosUsuario()
    {

        $headers = getallheaders();
        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $q = "
            SELECT u.nombre, u.email, u.avatar, c.nombre_canal 
                FROM usuarios u
            LEFT JOIN canales c ON c.usuario_id = u.id
            WHERE u.identificador = ?;
        ";

        $datosUsuarios = $this->con->prepare($q);
        $datosUsuarios->bindParam(1, $identificador);
        $estado = $datosUsuarios->execute();
        $respuesta = $datosUsuarios->fetch(PDO::FETCH_ASSOC);

        if (!$estado || count($respuesta) === 0)
        {
         echo EstadoFAIL();
         return;
        }

        echo RespuestaOK($this->Modelos->DatosUsuario($respuesta['nombre'], $respuesta['email'], $respuesta['avatar'], $respuesta['nombre_canal']));
    }


}






?>