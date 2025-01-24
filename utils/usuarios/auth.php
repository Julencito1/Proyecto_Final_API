<?php 

function ExisteUsuario($con, $email): bool
{
    $q = "SELECT COUNT(*) AS existe FROM usuarios WHERE email = :email";

    $consulta = $con->prepare($q);

    $consulta->execute(["email" => $email]);

    $respuesta = $consulta->fetch(PDO::FETCH_ASSOC);

    return $respuesta["existe"] === 0;
}

function ObtenerPasswordHash($con, $email)
{
    try 
    {
        $q = "SELECT password FROM usuarios WHERE email = :email";

        $consulta = $con->prepare($q);

        $estado = $consulta->execute(["email" => $email]);

        $respuesta = $consulta->fetch(PDO::FETCH_ASSOC);

        if ($estado) {

            return [$respuesta["password"], "S"];

        } else {
            
            return ["", "N"];
        }
    } catch(Exception $e) {

        return $e;
    }
    
}




?>