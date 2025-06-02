<?php

namespace Controllers\ComentariosHijosGustados;

use Utils\Auth\Auth;
use Utils\Comentarios\Obtener as ComentariosObtener;
use Utils\RespuestasComentarios\Generar;
use Utils\RespuestasComentarios\Obtener as RespuestasComentariosObtener;
use Utils\Usuarios\Obtener;

class ComentariosHijosGustados
{
    protected $con;
    public static $tabla = "comentarios_hijos_gustados";
    private $ExtraExiste;
    private $ExtraGenerar;
    private $ExtraObtener;


    public function __construct($conexion)
    {
        $this->con = $conexion;
        $this->ExtraObtener = new \Utils\ComentariosHijosGustados\Obtener($conexion, $this);
       
    }

    public function MarcadoSiComentario()
    {
        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $comentario = file_get_contents("php://input");
        $datos = json_decode($comentario, true);

        $comentarioID = RespuestasComentariosObtener::Id($datos["identificador_comentario"], $this->con);
        $usuarioID = Obtener::Id($identificador, $this->con);

        $marcadoPreviamente = $this->ExtraObtener->EstaMarcado($comentarioID, $usuarioID);
        $previoConteo = $datos["visto"];
        $opuestoConteo = $datos["op"];

        if ($marcadoPreviamente === "sindef")
        {
            $q = "
                INSERT INTO comentarios_hijos_gustados(usuario_id, comentario_id, gustado) VALUES (?, ?, 'si')
            ";

            $nuevo = $this->con->prepare($q);
            $nuevo->bindParam(1, $usuarioID);
            $nuevo->bindParam(2, $comentarioID);
            $estado = $nuevo->execute();

            if (!$estado)
            {
                echo EstadoFAIL();
                return;
            }

            echo RespuestaOK(["sig" => $previoConteo+1, "opsig" => $opuestoConteo]);
        } else if ($marcadoPreviamente === "no") {

            $q = "UPDATE comentarios_hijos_gustados SET gustado = 'si' WHERE comentario_id = ? AND usuario_id = ?";

            $actualiza = $this->con->prepare($q);
            $actualiza->bindParam(1, $comentarioID);
            $actualiza->bindParam(2, $usuarioID);
            $estado = $actualiza->execute();

            if (!$estado)
            {
                echo EstadoFAIL();
                return;
            }

            echo RespuestaOK(["sig" => $previoConteo+1, "opsig" => $opuestoConteo-1]);

        } else {

            
                $q = "
                    DELETE FROM comentarios_hijos_gustados WHERE gustado = 'si' AND comentario_id = ? AND usuario_id = ?
                ";

                $actualizar = $this->con->prepare($q);
                $actualizar->bindParam(1, $comentarioID);
                $actualizar->bindParam(2, $usuarioID);
                $estado = $actualizar->execute();

                if (!$estado)
                {
                    echo EstadoFAIL();
                    return;
                }

                echo RespuestaOK(["sig" => $previoConteo-1, "opsig" => $opuestoConteo]);

           
        }
    }

    public function MarcadoNoComentario()
    {
        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $comentario = file_get_contents("php://input");
        $datos = json_decode($comentario, true);

        $comentarioID = RespuestasComentariosObtener::Id($datos["identificador_comentario"], $this->con);
        $usuarioID = Obtener::Id($identificador, $this->con);

        $marcadoPreviamente = $this->ExtraObtener->EstaMarcado($comentarioID, $usuarioID);
        $previoConteo = $datos["visto"];
        $opuestoConteo = $datos["op"];

        
        if ($marcadoPreviamente === "sindef")
        {
            $q = "
                INSERT INTO comentarios_hijos_gustados(usuario_id, comentario_id, gustado) VALUES (?, ?, 'no')
            ";

            $nuevo = $this->con->prepare($q);
            $nuevo->bindParam(1, $usuarioID);
            $nuevo->bindParam(2, $comentarioID);
            $estado = $nuevo->execute();

            if (!$estado)
            {
                echo EstadoFAIL();
                return;
            }

            echo RespuestaOK(["sig" => $previoConteo+1, "opsig" => $opuestoConteo, "t" => $marcadoPreviamente]);
        } else if ($marcadoPreviamente === "si") {

            $q = "UPDATE comentarios_hijos_gustados SET gustado = 'no' WHERE comentario_id = ? AND usuario_id = ?";

            $actualiza = $this->con->prepare($q);
            $actualiza->bindParam(1, $comentarioID);
            $actualiza->bindParam(2, $usuarioID);
            $estado = $actualiza->execute();

            if (!$estado)
            {
                echo EstadoFAIL();
                return;
            }

            echo RespuestaOK(["sig" => $previoConteo+1, "opsig" => $opuestoConteo-1, "t" => $marcadoPreviamente]);

        } else {

            
                $q = "
                    DELETE FROM comentarios_hijos_gustados WHERE gustado = 'no' AND comentario_id = ? AND usuario_id = ?
                ";

                $actualizar = $this->con->prepare($q);
                $actualizar->bindParam(1, $comentarioID);
                $actualizar->bindParam(2, $usuarioID);
                $estado = $actualizar->execute();

                if (!$estado)
                {
                    echo EstadoFAIL();
                    return;
                }

                echo RespuestaOK(["sig" => $previoConteo-1, "opsig" => $opuestoConteo, "t" => $marcadoPreviamente]);

           
        }
    }

}






?>