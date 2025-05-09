<?php

namespace Utils\Usuarios;
use Controllers\Usuarios\Usuarios;
use PDO;

class Existe
{
    protected $con;
    protected $usuarios;

    
    public function __construct(PDO $conexion, Usuarios $usuarios)
    {
        $this->con = $conexion;  
        $this->usuarios = $usuarios;  
    }

    public function ExisteUsuario($email): bool
    {
        $q = "SELECT COUNT(*) AS existe FROM usuarios WHERE email = :email";

        $consulta = $this->con->prepare($q);

        $consulta->execute(["email" => $email]);

        $respuesta = $consulta->fetch(PDO::FETCH_ASSOC);

        return $respuesta["existe"] === 0;
    }

}







?>