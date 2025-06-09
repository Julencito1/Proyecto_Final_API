<?php

namespace Utils\usuarios;
use Controllers\Usuarios\Usuarios;
use PDO;


class Obtener
{
    protected $con;
    protected $usuarios;

    public function __construct(PDO $conexion, Usuarios $usuarios)
    {
        $this->con = $conexion;  
        $this->usuarios = $usuarios;  
    }

    public function ObtenerPasswordHash($email)
    {
        
            $q = "SELECT password FROM usuarios WHERE email = :email";

            $consulta = $this->con->prepare($q);

            $estado = $consulta->execute(["email" => $email]);

            $respuesta = $consulta->fetch(PDO::FETCH_ASSOC);

            if ($estado) {

                return [$respuesta["password"], "S"];

            } else {

                return ["", "N"];
            }

    }

    public function ObtenerID($email): int
    {
        $q = "SELECT id FROM usuarios WHERE email = ?";
        $consulta = $this->con->prepare($q);
        $consulta->bindParam(1, $email);

        $estado = $consulta->execute();
        $respuesta = $consulta->fetch(PDO::FETCH_ASSOC);

        if ($estado) {
            return $respuesta["id"];
        } else {
            return 0;
        }

    }

    public static function Id($identificador, $cx): int 
    {

        $identificador_usuario = $identificador;

        if ($identificador === "") 
        {
            return 0;
        }

        $q = "
            SELECT id FROM usuarios WHERE identificador = ?
        ";

        $obtenerID = $cx->prepare($q);
        $obtenerID->bindParam(1, $identificador_usuario);
        $estado = $obtenerID->execute();
        $respuesta = $obtenerID->fetch(PDO::FETCH_ASSOC);

        if (!$estado)
        {
            return 0;
        }

        return $respuesta["id"];

    }

    public static function IdPorCanalId($id, $cx): int 
    {
        $canalID = $id;

        if ($id === "") 
        {
            return 0;
        }

        $q = "
            SELECT usuario_id FROM canales WHERE id = ?
        ";

        $obtenerID = $cx->prepare($q);
        $obtenerID->bindParam(1, $canalID);
        $estado = $obtenerID->execute();
        $respuesta = $obtenerID->fetch(PDO::FETCH_ASSOC);

        if (!$estado)
        {
            return 0;
        }

        return $respuesta["usuario_id"];
    }


}

?>