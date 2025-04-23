<?php

namespace Utils\Usuarios\Existe;
use Controllers\Usuarios\Usuarios;
use PDO;

class Existe extends Usuarios
{

    protected function ExisteUsuario($email): bool
    {
        $q = "SELECT COUNT(*) AS existe FROM usuarios WHERE email = :email";

        $consulta = $this->con->prepare($q);

        $consulta->execute(["email" => $email]);

        $respuesta = $consulta->fetch(PDO::FETCH_ASSOC);

        return $respuesta["existe"] === 0;
    }

}







?>