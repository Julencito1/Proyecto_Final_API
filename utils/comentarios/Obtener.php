<?php

namespace Utils\Comentarios;
use Controllers\Comentarios\Comentarios;
use Models\Comentarios\Modelos;
use PDO;
use Utils\RespuestasComentarios\Obtener as RespuestasComentariosObtener;

class Obtener
{
    protected $con;
    protected $comentarios;

    public function __construct(PDO $conexion, Comentarios $comentarios)
    {
        $this->con = $conexion;  
        $this->comentarios = $comentarios;  
    }

    public static function Id($identificador, $cx)
    {
        $q = "
            SELECT id FROM comentarios WHERE identificador = ?
        ";

        $obtenerID = $cx->prepare($q);
        $obtenerID->bindParam(1, $identificador);
        $estado = $obtenerID->execute();
        $respuesta = $obtenerID->fetch(PDO::FETCH_ASSOC);

        if (!$estado)
        {
            return 0;
        }

        return $respuesta["id"];
    }

    public static function EstaMarcadoComentario($comentarioID, $usuarioID, $cx)
    {

        $q = "
        
            SELECT gustado FROM comentarios_gustados WHERE comentario_id = ? AND usuario_id = ?
        ";

        $marcado = $cx->prepare($q);
        $marcado->bindParam(1, $comentarioID);
        $marcado->bindParam(2, $usuarioID);
        $estado = $marcado->execute();
        $respuesta = $marcado->fetch(PDO::FETCH_ASSOC);

        if (!$estado)
        {
            return null;
        }

        return $respuesta["gustado"] ?? null;
    }

    public static function EstaMarcadoComentarioHijo($comentarioID, $usuarioID, $cx)
    {

        $q = "
        
            SELECT gustado FROM comentarios_hijos_gustados WHERE comentario_id = ? AND usuario_id = ?
        ";

        $marcado = $cx->prepare($q);
        $marcado->bindParam(1, $comentarioID);
        $marcado->bindParam(2, $usuarioID);
        $estado = $marcado->execute();
        $respuesta = $marcado->fetch(PDO::FETCH_ASSOC);

        if (!$estado)
        {
            return null;
        }

        return $respuesta["gustado"] ?? null;
    }

    public function ComentariosHijo($id, $usuario_id)
    {

        if ($id === 0)
        {
            return [];
        }

        $usuarioID = $usuario_id;

        $q = "
            SELECT
                rc.contenido,
                rc.identificador,
                rc.fecha_publicacion,
                u.nombre,
                u.avatar,
                c.nombre_canal,
                (SELECT COUNT(*) FROM comentarios_hijos_gustados WHERE comentario_id = rc.id AND gustado = 'si') AS total_megusta,
                (SELECT COUNT(*) FROM comentarios_hijos_gustados WHERE comentario_id = rc.id AND gustado = 'no') AS total_nomegusta
            FROM respuestas_comentarios rc
            LEFT JOIN usuarios u ON rc.usuario_id = u.id
            LEFT JOIN canales c ON c.usuario_id = u.id
            WHERE rc.comentario_padre_id = ?
        ";

        $comentariosHijo = $this->con->prepare($q);
        $comentariosHijo->bindParam(1, $id);
        $estado = $comentariosHijo->execute();
        $respuesta = $comentariosHijo->fetchAll(PDO::FETCH_ASSOC);

        if (!$estado)
        {
            return [];
        }

        $comentariosHijo = [];

        for ($e = 0; $e < count($respuesta); $e++)
        {
            $comentarioHijoID = RespuestasComentariosObtener::Id($respuesta[$e]["identificador"], $this->con);
            $marcadoComentarioHijo = $this::EstaMarcadoComentarioHijo($comentarioHijoID, $usuarioID, $this->con);

            array_push($comentariosHijo, Modelos::ObtenerComentariosHijos($respuesta[$e]["contenido"], $respuesta[$e]["identificador"], $respuesta[$e]["fecha_publicacion"], $respuesta[$e]["nombre"], $respuesta[$e]["avatar"], $respuesta[$e]["nombre_canal"], $respuesta[$e]["total_megusta"], $marcadoComentarioHijo, $respuesta[$e]["total_nomegusta"]));
        }

        return $comentariosHijo;
    }

}

?>