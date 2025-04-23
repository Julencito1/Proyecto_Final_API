<?php

namespace Utils\Usuarios\Obtener;
use Controllers\Usuarios\Usuarios;
use PDO;


class Obtener extends Usuarios
{

    protected function ObtenerPasswordHash($email)
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


}

?>