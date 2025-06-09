<?php

namespace Controllers\Videos;

use Models\Videos\Modelos;
use PDO;
use Utils\Auth\Auth;
use Utils\Canales\Obtener as CanalesObtener;
use Utils\Historial\Obtener as HistorialObtener;
use Utils\Paginacion\Paginacion;
use Utils\Usuarios\Obtener as UsuariosObtener;
use Utils\Videos\Existe as VideosExiste;
use Utils\Videos\Obtener;
use Utils\VideosGuardados\Obtener as VideosGuardadosObtener;
use Utils\VideosMarcados\Generar as VideosMarcadosGenerar;
use Utils\VideosMarcados\Obtener as VideosMarcadosObtener;

class Videos
{
    protected $con;
    public static $tabla = "videos";
    private $ExtraExiste;
    private $ExtraGenerar;
    private $ExtraObtener;
    private $Modelos;


    public function __construct($conexion)
    {
        $this->con = $conexion;
        $this->ExtraObtener = new Obtener($conexion, $this);
        $this->Modelos = new Modelos($this);
    }

    public function ObtenerDatosVideo()
    {
        $video = file_get_contents("php://input");
        $datos = json_decode($video, true);

        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $identificador_video = $datos["identificador"];

        $existe = VideosExiste::ExisteVideo($identificador_video, $this->con);

        if ($existe)
        {

            $q = "
                SELECT v.titulo, 
                v.descripcion, 
                v.video,
                v.identificador,
                v.visitas,
                v.duracion,
                (SELECT COUNT(*) FROM videos_marcados WHERE video_id = v.id AND gustado = 'si') AS me_gusta,
                (SELECT COUNT(*) FROM videos_marcados WHERE video_id = v.id AND gustado = 'no') AS no_megusta,
                (SELECT COUNT(*) FROM suscripciones s WHERE s.canal_id = v.canal_id) AS total_suscriptores,
                v.fecha_creacion,
                c.nombre AS categoria,
                ca.nombre_canal,
                u.nombre,
                u.avatar
                FROM videos v
                LEFT JOIN categorias c ON c.id = v.categoria_id
                LEFT JOIN canales ca ON ca.id = v.canal_id
                LEFT JOIN usuarios u ON u.id = ca.usuario_id
                WHERE v.identificador = ?
            ";

            $datosVideo = $this->con->prepare($q);
            $datosVideo->bindParam(1, $identificador_video);
            $estado = $datosVideo->execute();
            $respuesta = $datosVideo->fetch(PDO::FETCH_ASSOC);

            if (!$estado){

                echo EstadoFAIL();
                return;
            }

            echo RespuestaOK($this->Modelos->DatosVideo($respuesta["categoria"], $respuesta["titulo"], $respuesta["descripcion"], $respuesta["video"], $respuesta["identificador"], $respuesta["visitas"], $respuesta["duracion"], $respuesta["me_gusta"], $respuesta["no_megusta"], $respuesta["total_suscriptores"], $respuesta["fecha_creacion"], $respuesta["nombre_canal"], $respuesta["nombre"], $respuesta["avatar"]));

        } else {

            echo RespuestaFail("No se encontró el video.", 404);
        }
    }

    public function Estadisticas()
    {
        $historial = file_get_contents("php://input");
        $datos = json_decode($historial, true);

        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $identificador_video = $datos["identificador"];

        $existe = VideosExiste::ExisteVideo($identificador_video, $this->con);

        if ($existe)
        {
            $usuarioID = UsuariosObtener::Id($identificador, $this->con);

            $yaVisto = HistorialObtener::Visto($identificador_video, $identificador, $this->con);
            $yaGuardado = VideosGuardadosObtener::EstaGuardado($identificador_video, $usuarioID, $this->con);
            $gustado = VideosMarcadosObtener::Gustado($identificador_video, $identificador, $this->con);
            $esPublico = Obtener::EsPublico($identificador_video, $this->con);
            $esActual = CanalesObtener::EsActualPorVideo($identificador_video, $usuarioID, $this->con);
            $suscriptor= CanalesObtener::EsSuscriptorVideo($usuarioID, $identificador_video, $this->con);

            echo RespuestaOK($this->Modelos->EstadisticasVideo($yaVisto, $yaGuardado, $gustado, $esPublico, $esActual, $suscriptor));

        } else {

            echo RespuestaFail("No se encontró el video.", 404);
        }
    }

    public function MeGusta()
    {
        $video_canal = file_get_contents("php://input");
        $datos = json_decode($video_canal, true);

        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $identificador_video = $datos["identificador"];

        $existe = VideosExiste::ExisteVideo($identificador_video, $this->con);

        if ($existe)
        {

            $si = "si";
            $estadoMarcado = VideosMarcadosObtener::Gustado($identificador_video, $identificador, $this->con);
            $usuarioID = UsuariosObtener::Id($identificador, $this->con);
            $videoID = $this->ExtraObtener->Id($identificador_video, $this->con);

            if ($estadoMarcado === "sindef")
            {

                $generarIdentificador = VideosMarcadosGenerar::GenerarIdentificador($this->con);

                $q = "
                    INSERT INTO videos_marcados(usuario_id, video_id, identificador, gustado) VALUES (?, ?, ?, ?)
                ";

                $megusta = $this->con->prepare($q);
                $megusta->bindParam(1, $usuarioID);
                $megusta->bindParam(2, $videoID);
                $megusta->bindParam(3, $generarIdentificador);
                $megusta->bindParam(4, $si);
                $estado = $megusta->execute();
                

                if (!$estado)
                {
                    echo EstadoFAIL();
                    return;
                }

                echo EstadoOK();

            } else {

                $q = "
                    UPDATE videos_marcados SET gustado = 'si' WHERE video_id = ? AND usuario_id = ?
                ";

                $megusta = $this->con->prepare($q);
                $megusta->bindParam(1, $videoID);
                $megusta->bindParam(2, $usuarioID);
                $estado = $megusta->execute();
                

                if (!$estado)
                {
                    echo EstadoFAIL();
                    return;
                }

                echo EstadoOK();
            }

        } else {
            echo RespuestaFail("No se encontró el video.", 404);
        }
    }

    public function NoMeGusta()
    {
        $video_canal = file_get_contents("php://input");
        $datos = json_decode($video_canal, true);

        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $identificador_video = $datos["identificador"];

        $existe = VideosExiste::ExisteVideo($identificador_video, $this->con);

        if ($existe)
        {
            $no = "no";
            $estadoMarcado = VideosMarcadosObtener::Gustado($identificador_video, $identificador, $this->con);
            $usuarioID = UsuariosObtener::Id($identificador, $this->con);
            $videoID = $this->ExtraObtener->Id($identificador_video, $this->con);

            if ($estadoMarcado === "sindef")
            {

                $generarIdentificador = VideosMarcadosGenerar::GenerarIdentificador($this->con);

                $q = "
                    INSERT INTO videos_marcados(usuario_id, video_id, identificador, gustado) VALUES (?, ?, ?, ?)
                ";

                $megusta = $this->con->prepare($q);
                $megusta->bindParam(1, $usuarioID);
                $megusta->bindParam(2, $videoID);
                $megusta->bindParam(3, $generarIdentificador);
                $megusta->bindParam(4, $no);
                $estado = $megusta->execute();
                

                if (!$estado)
                {
                    echo EstadoFAIL();
                    return;
                }

                echo EstadoOK();

            } else {

                $q = "
                    UPDATE videos_marcados SET gustado = 'no' WHERE video_id = ? AND usuario_id = ?
                ";

                $megusta = $this->con->prepare($q);
                $megusta->bindParam(1, $videoID);
                $megusta->bindParam(2, $usuarioID);
                $estado = $megusta->execute();
                

                if (!$estado)
                {
                    echo EstadoFAIL();
                    return;
                }

                echo EstadoOK();
            }

        } else {
            echo RespuestaFail("No se encontró el video.", 404);
        }
    }

    public function QuitarMarcado()
    {
        $video_canal = file_get_contents("php://input");
        $datos = json_decode($video_canal, true);

        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $identificador_video = $datos["identificador"];

        $existe = VideosExiste::ExisteVideo($identificador_video, $this->con);

        if ($existe)
        {

            $usuarioID = UsuariosObtener::Id($identificador, $this->con);
            $videoID = $this->ExtraObtener->Id($identificador_video, $this->con);

                $q = "
                    DELETE FROM videos_marcados WHERE video_id = ? AND usuario_id = ?
                ";

                $quitarMarcado = $this->con->prepare($q);
                $quitarMarcado->bindParam(1, $videoID);
                $quitarMarcado->bindParam(2, $usuarioID);
                $estado = $quitarMarcado->execute();
                

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

    public function RecomendacionVideosVideo()
    {
        $video_canal = file_get_contents("php://input");
        $datos = json_decode($video_canal, true);

        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $identificador_video = $datos["identificador"];
        $existe = VideosExiste::ExisteVideo($identificador_video, $this->con);

        if ($existe)
        {

           $identificadores = $datos["identificadores"];
            $notIn = count($identificadores) > 0 ? "AND v.identificador NOT IN (". join(', ', $identificadores) .")" : " ";


           $q = "
            SELECT
                v.titulo,
                v.identificador,
                v.miniatura,
                v.visitas,
                v.duracion,
                v.fecha_creacion,
                u.nombre,
                u.avatar
            FROM videos v
            LEFT JOIN canales c ON c.id = v.canal_id
            LEFT JOIN usuarios u ON u.id = c.usuario_id
            WHERE v.estado = 'publico' AND v.identificador != ? ". $notIn ." 
            ORDER BY RAND() LIMIT 20;
           ";

           $videosRecomendados = $this->con->prepare($q);
           $videosRecomendados->bindParam(1, $identificador_video);
           $estado = $videosRecomendados->execute();
           $respuesta = $videosRecomendados->fetchAll(PDO::FETCH_ASSOC);

           if (!$estado)
           {
            echo EstadoFAIL();
            return;
           }

           $videosRecomendados = [];

           for ($i = 0; $i < count($respuesta); $i++)
           {

            array_push($videosRecomendados, Modelos::VideosRecomendados($respuesta[$i]["titulo"], $respuesta[$i]["identificador"], $respuesta[$i]["miniatura"], $respuesta[$i]["visitas"], $respuesta[$i]["fecha_creacion"], $respuesta[$i]["duracion"], $respuesta[$i]["nombre"], $respuesta[$i]["avatar"]));
            
           }

           $mas = Paginacion::ContieneMas(
"
            SELECT
                v.titulo,
                v.identificador,
                v.miniatura,
                v.visitas,
                v.duracion,
                v.fecha_creacion,
                u.nombre,
                u.avatar
            FROM videos v
            LEFT JOIN canales c ON c.id = v.canal_id
            LEFT JOIN usuarios u ON u.id = c.usuario_id
            WHERE v.estado = 'publico' AND v.identificador != ? ". $notIn ." 
            ORDER BY RAND() LIMIT 20;
           ", $this->con, $identificador_video
           );

           echo RespuestaOK(["videos" => $videosRecomendados, "mas" => $mas]);



        } else {
            echo RespuestaFail("No se encontró el video.", 404);
        }
    }

    public function RecomendacionVideosInicio()
    {

        $video_inicio = file_get_contents("php://input");
        $datos = json_decode($video_inicio, true);

        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        
        $identificadores = $datos["identificadores"];
        $notIn = count($identificadores) > 0 ? "AND v.identificador NOT IN (". join(', ', $identificadores) .")" : " ";

        $q = "
            SELECT
                v.titulo,
                v.identificador,
                v.miniatura,
                v.visitas,
                v.duracion,
                v.fecha_creacion,
                u.nombre,
                u.avatar
            FROM videos v
            LEFT JOIN canales c ON c.id = v.canal_id
            LEFT JOIN usuarios u ON u.id = c.usuario_id
            WHERE v.estado = 'publico' ". $notIn ." 
            ORDER BY RAND() LIMIT 20;
           ";

           $videosRecomendados = $this->con->prepare($q);
           $estado = $videosRecomendados->execute();
           $respuesta = $videosRecomendados->fetchAll(PDO::FETCH_ASSOC);

           if (!$estado)
           {
            echo EstadoFAIL();
            return;
           }

           $videosRecomendados = [];

           for ($i = 0; $i < count($respuesta); $i++)
           {

            array_push($videosRecomendados, Modelos::VideosRecomendados($respuesta[$i]["titulo"], $respuesta[$i]["identificador"], $respuesta[$i]["miniatura"], $respuesta[$i]["visitas"], $respuesta[$i]["fecha_creacion"], $respuesta[$i]["duracion"], $respuesta[$i]["nombre"], $respuesta[$i]["avatar"]));
            
           }

           $mas = Paginacion::NoParametro(
            "
                        SELECT
                            v.titulo,
                            v.identificador,
                            v.miniatura,
                            v.visitas,
                            v.duracion,
                            v.fecha_creacion,
                            u.nombre,
                            u.avatar
                        FROM videos v
                        LEFT JOIN canales c ON c.id = v.canal_id
                        LEFT JOIN usuarios u ON u.id = c.usuario_id
                        WHERE v.estado = 'publico' ". $notIn ." 
                        ORDER BY RAND() LIMIT 20;
                       ", $this->con,
                       );
            
            echo RespuestaOK(["videos" => $videosRecomendados, "mas" => $mas]);
    }


}






?>