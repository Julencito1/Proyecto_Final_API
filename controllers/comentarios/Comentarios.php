<?php

namespace Controllers\Comentarios;

use Models\Comentarios\Modelos;
use PDO;
use Utils\Auth\Auth;
use Utils\Comentarios\Generar;
use Utils\Comenarios\Obtener;
use Utils\Comentarios\Obtener as ComentariosObtener;
use Utils\Paginacion\Paginacion;
use Utils\Usuarios\Obtener as UsuariosObtener;
use Utils\Videos\Existe;
use Utils\Videos\Obtener as VideosObtener;

class Comentarios
{
    protected $con;
    public static $tabla = "comentarios";
    private $ExtraExiste;
    private $ExtraGenerar;
    private $ExtraObtener;


    public function __construct($conexion)
    {
        $this->con = $conexion;
        $this->ExtraGenerar = new Generar($conexion, $this);
        $this->ExtraObtener = new ComentariosObtener($conexion, $this);
    }

    
    public function ObtenerComentarios()
    {
        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $video = file_get_contents("php://input");
        $datos = json_decode($video, true);

        $identificador_video = $datos["identificador"];
        $limit = $datos["limit"];
        $offset = $datos["offset"];

        $existe = Existe::ExisteVideo($identificador_video, $this->con);
        $usuarioID = UsuariosObtener::Id($identificador, $this->con);

        if ($existe)
        {
            $videoID = VideosObtener::Id($identificador_video, $this->con);

            $q = "
            
                SELECT
                    c.contenido,
                    c.identificador,
                    c.fecha_publicacion,
                    u.nombre,
                    u.avatar,
                    ca.nombre_canal,
                    (SELECT COUNT(*) FROM comentarios_gustados WHERE comentario_id = c.id AND gustado = 'si') AS total_megusta,
                    (SELECT COUNT(*) FROM comentarios_gustados WHERE comentario_id = c.id AND gustado = 'no') AS total_nomegusta
                FROM comentarios c
                LEFT JOIN usuarios u ON c.usuario_id = u.id
                LEFT JOIN canales ca ON ca.usuario_id = u.id
                WHERE c.video_id = ?
                LIMIT " . $limit ." OFFSET ". $offset ." 
            ";

            $comentariosVideo = $this->con->prepare($q);
            $comentariosVideo->bindParam(1, $videoID);
            $estado = $comentariosVideo->execute();
            $respuesta = $comentariosVideo->fetchAll(PDO::FETCH_ASSOC);

            if (!$estado)
            {
                echo EstadoFAIL();
                return;
            }

            $comentarios = [];

            for ($k = 0; $k < count($respuesta); $k++)
            {
                $identificadorComentarioActual = $respuesta[$k]["identificador"];
                $comentarioID = $this->ExtraObtener::Id($identificadorComentarioActual, $this->con);

                $comentariosHijo = $this->ExtraObtener->ComentariosHijo($comentarioID, $usuarioID);
                $marcadoComentario = $this->ExtraObtener::EstaMarcadoComentario($comentarioID, $usuarioID, $this->con);

                array_push($comentarios, Modelos::Comentarios($respuesta[$k]["contenido"], $respuesta[$k]["identificador"], $respuesta[$k]["fecha_publicacion"], $respuesta[$k]["nombre"], $respuesta[$k]["avatar"], $respuesta[$k]["nombre_canal"], $respuesta[$k]["total_megusta"], $respuesta[$k]["total_nomegusta"], $marcadoComentario, $comentariosHijo));
                
            }

            $mas = Paginacion::ContieneMasVideo(
                "
            
                SELECT
                    c.contenido,
                    c.identificador,
                    c.fecha_publicacion,
                    u.nombre,
                    u.avatar,
                    ca.nombre_canal
                FROM comentarios c
                LEFT JOIN usuarios u ON c.usuario_id = u.id
                LEFT JOIN canales ca ON ca.usuario_id = u.id
                WHERE c.video_id = ?
                LIMIT " . $limit ." OFFSET ". $offset+20 ." 
            ",
            $this->con,
            $videoID
            );

            $total_comentarios = "
                SELECT COUNT(*) AS c FROM comentarios WHERE video_id = ?
            ";

            $totalComentarios = $this->con->prepare($total_comentarios);
            $totalComentarios->bindParam(1, $videoID);
            $estadoTC = $totalComentarios->execute();
            $respuestaTC = $totalComentarios->fetch(PDO::FETCH_ASSOC);

            if (!$estadoTC)
            {
                echo EstadoFAIL();
                return;
            }

            echo RespuestaOK(["comentarios" => $comentarios, "total" => $respuestaTC["c"], "mas" => $mas]);


        } else {

            echo RespuestaFail("No se encontró el video.", 404);
        }
    }

    public function PublicarComentario()
    {

        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $video = file_get_contents("php://input");
        $datos = json_decode($video, true);

        $identificador_video = $datos["identificador"];
        $existe = Existe::ExisteVideo($identificador_video, $this->con);

        if ($existe)
        {

            $usuarioID = UsuariosObtener::Id($identificador, $this->con);
            $generarIdentificador = $this->ExtraGenerar->GenerarIdentificador();
            $videoID = VideosObtener::Id($identificador_video, $this->con);
            $comentario = $datos["comentario"];

            $q  = "
                INSERT INTO comentarios(video_id, usuario_id, contenido, identificador) VALUES (?, ?, ?, ?)
            ";

            $comentar = $this->con->prepare($q);
            $comentar->bindParam(1, $videoID);
            $comentar->bindParam(2, $usuarioID);
            $comentar->bindParam(3, $comentario);
            $comentar->bindParam(4, $generarIdentificador);
            $estado = $comentar->execute();

            if (!$estado)
            {
                echo EstadoFAIL();
                return;
            }

            echo EstadoOK();

        } else {
            echo RespuestaFail("No se encontró el video.", 404);
        }

    }

    public function ObtenerComentariosMarcados()
    {
        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $comentarios = file_get_contents("php://input");
        $datos = json_decode($comentarios, true);

        $offset = $datos["offset"];
        $usuarioId = UsuariosObtener::Id($identificador, $this->con);

        $q = "
        
            
        SELECT
            u.nombre,
            u.avatar,
            c.contenido,
            c.identificador AS identificador_comentario,
            c.fecha_publicacion,
            c.tipo,
            v.identificador AS identificador_video,
            cg.gustado
        FROM comentarios_gustados cg
        LEFT JOIN comentarios c ON c.id = cg.comentario_id
        LEFT JOIN usuarios u ON u.id = c.usuario_id
        LEFT JOIN videos v ON v.id = c.video_id
        WHERE v.estado = 'publico' AND cg.usuario_id = ?

        UNION ALL

            SELECT 
            u.nombre,
            u.avatar,
            rc.contenido,
            rc.identificador AS identificador_comentario,
            rc.fecha_publicacion,
            rc.tipo,
            v.identificador AS identificador_video,
            chg.gustado
            FROM comentarios_hijos_gustados chg
            LEFT JOIN respuestas_comentarios rc ON chg.comentario_id = rc.id
            LEFT JOIN usuarios u ON rc.usuario_id = u.id
            LEFT JOIN comentarios c ON c.id = rc.comentario_padre_id
            LEFT JOIN videos v ON c.video_id = v.id
            WHERE v.estado = 'publico' AND chg.usuario_id = ?

        LIMIT 20 OFFSET ". $offset ."
        ";

        $obtenerComentariosMarcados = $this->con->prepare($q);
        $obtenerComentariosMarcados->bindParam(1, $usuarioId);
        $obtenerComentariosMarcados->bindParam(2, $usuarioId);
        $estado = $obtenerComentariosMarcados->execute();
        $respuesta = $obtenerComentariosMarcados->fetchAll(PDO::FETCH_ASSOC);

        if (!$estado)
        {
            echo EstadoFAIL();
            return;
        }

        $comentarios_marcados = [];

        for ($i = 0; $i < count($respuesta); $i++)
        {
            array_push($comentarios_marcados, Modelos::ObtenerComentariosMarcados($respuesta[$i]["nombre"], $respuesta[$i]["avatar"], $respuesta[$i]["contenido"], $respuesta[$i]["identificador_comentario"], $respuesta[$i]["fecha_publicacion"], $respuesta[$i]["tipo"], $respuesta[$i]["identificador_video"], $respuesta[$i]["gustado"]));
        }

        $mas = Paginacion::ContieneDobleParametro(
            "
        
            
        SELECT
            u.nombre,
            u.avatar,
            c.contenido,
            c.identificador AS identificador_comentario,
            c.fecha_publicacion,
            c.tipo,
            v.identificador AS identificador_video,
            cg.gustado
        FROM comentarios_gustados cg
        LEFT JOIN comentarios c ON c.id = cg.comentario_id
        LEFT JOIN usuarios u ON u.id = c.usuario_id
        LEFT JOIN videos v ON v.id = c.video_id
        WHERE v.estado = 'publico' AND cg.usuario_id = ?

        UNION ALL

            SELECT 
            u.nombre,
            u.avatar,
            rc.contenido,
            rc.identificador AS identificador_comentario,
            rc.fecha_publicacion,
            rc.tipo,
            v.identificador AS identificador_video,
            chg.gustado
            FROM comentarios_hijos_gustados chg
            LEFT JOIN respuestas_comentarios rc ON chg.comentario_id = rc.id
            LEFT JOIN usuarios u ON rc.usuario_id = u.id
            LEFT JOIN comentarios c ON c.id = rc.comentario_padre_id
            LEFT JOIN videos v ON c.video_id = v.id
            WHERE v.estado = 'publico' AND chg.usuario_id = ?

        LIMIT 20 OFFSET ". $offset + 20 ."
        ", $this->con, $usuarioId, $usuarioId
        );

        echo RespuestaOK(
            ["comentarios" => $comentarios_marcados,"mas" => $mas]
        );
    }
    


}






?>