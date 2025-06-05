<?php

namespace Controllers\Canales;

use Structs\Canales\EstructuraCanales;
use Utils\Canales\Existe;
use Utils\Canales\Generar;
use Utils\Canales\Obtener;
use Models\Canales\Modelos;
use Models\Suscripciones\Modelos as SuscripcionesModelos;
use Utils\Auth\Auth;
use PDO;
use Utils\Date\Date;
use Utils\Paginacion\Paginacion;
use Utils\Usuarios\Obtener as UsuariosObtener;
use Utils\Videos\Obtener as VideosObtener;
use Utils\VideosGuardados\Generar as VideosGuardadosGenerar;
use Utils\VideosGuardados\Obtener as VideosGuardadosObtener;



class Canales extends EstructuraCanales
{
    protected $con;
    public static $tabla = "canales";
    private $ExtraExiste;
    private $ExtraGenerar;
    private $ExtraObtener;

    public function __construct($conexion)
    {
        $this->con = $conexion;
        $this->ExtraExiste = new Existe($conexion, $this);
        $this->ExtraGenerar = new Generar($conexion, $this, $this->ExtraExiste);
        $this->ExtraObtener = new Obtener($conexion, $this);
    }

    public function CrearCanal(int $usuario_id, string $nombre_canal): bool
    {

        if ($usuario_id === 0) {

            return false;
        }

        $nombreCanal = $this->ExtraGenerar->NombreCanal($nombre_canal);

        if ($nombreCanal === "") {
            return false;
        }

        $canalUsuario = "@" . $nombreCanal;

        $portada = $this->ExtraGenerar->GenerarPortada();

        $crearCanal = "INSERT INTO " . self::$tabla . "(usuario_id, nombre_canal, portada) VALUES (?, ?, ?)";

        $nuevoCanal = $this->con->prepare($crearCanal);
        $nuevoCanal->bindParam(1, $usuario_id);
        $nuevoCanal->bindParam(2, $canalUsuario);
        $nuevoCanal->bindParam(3, $portada);

        $estado = $nuevoCanal->execute();

        if (!$estado) {
            return false;
        }

        return true;

    }

    public function DatosCanal() 
    {
        $canal = file_get_contents("php://input");
        $datos = json_decode($canal, true);

        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }


        $existe = $this->ExtraExiste->ExisteCanal($datos["canal"]);

        if ($existe > 0)
        {

            $canal_b = $datos["canal"];

            $q = "
               SELECT c.nombre_canal, c.portada, u.nombre, u.avatar,
                (SELECT COUNT(*) FROM videos v LEFT JOIN canales c ON c.id = v.canal_id WHERE c.nombre_canal = ? AND v.estado = 'publico') AS total_videos,
                (SELECT COUNT(*) FROM suscripciones s LEFT JOIN canales c ON c.id = s.canal_id WHERE c.nombre_canal = ?) AS total_suscriptores
                FROM canales c 
                LEFT JOIN usuarios u ON c.usuario_id = u.id
                WHERE c.nombre_canal = ?;
            ";

            $obtenerCanalDatos = $this->con->prepare($q);
            $obtenerCanalDatos->bindParam(1, $canal_b);
            $obtenerCanalDatos->bindParam(2, $canal_b);
            $obtenerCanalDatos->bindParam(3, $canal_b);
            $estado = $obtenerCanalDatos->execute();
            $respuesta = $obtenerCanalDatos->fetch(PDO::FETCH_ASSOC);


            if (!$estado) 
            {
                echo EstadoFAIL();
                return;
            }

            $esSuscriptor = $this->ExtraObtener::EsSuscriptor($identificador, $datos["canal"], $this->con);
            $esActual = $this->ExtraObtener->EsActual($identificador, $datos["canal"]);

            if ($esActual === null)
            {
                echo EstadoFAIL();
                return;
            }
            

            echo RespuestaOK(Modelos::DatosCanal($respuesta["nombre_canal"], $respuesta["portada"], $respuesta["nombre"], $respuesta["avatar"], $respuesta["total_videos"], $respuesta["total_suscriptores"], $esSuscriptor, $esActual));


        } else {

            echo RespuestaFail("No se encontró el canal.", 404);
        }
    }

    public function VideosCanal()
    {

        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $canal = file_get_contents("php://input");
        $datos = json_decode($canal, true);

        $existe = $this->ExtraExiste->ExisteCanal(nombre_canal: $datos["canal"]);
        $canalActual = $datos["canal"];
        $usuarioID = UsuariosObtener::Id($identificador, $this->con);
        $limit = $datos["limit"];
        $offset = $datos["offset"];

        if ($existe > 0){

            $q = "
            
                SELECT v.titulo,
                v.identificador,
                v.miniatura,
                v.visitas,
                v.duracion,
                v.fecha_creacion
                FROM videos v
                LEFT JOIN canales c ON c.id = v.canal_id 
                WHERE c.nombre_canal = ?
                AND v.estado = 'publico' 
                ORDER BY v.fecha_creacion DESC
                LIMIT " . $limit . " OFFSET " . $offset ."
            "; 

            $obtenerVideosCanal = $this->con->prepare($q);
            $obtenerVideosCanal->bindParam(1, $canalActual);
            $estado = $obtenerVideosCanal->execute();
            $respuesta = $obtenerVideosCanal->fetchAll(PDO::FETCH_ASSOC);

            if (!$estado)
            {
                echo EstadoFAIL();
                return;
            }

            $videos = [];

            for ($x = 0;$x < count($respuesta); $x++)
            {
                $guardado = VideosGuardadosObtener::EstaGuardado($respuesta[$x]["identificador"], $usuarioID, $this->con);
                array_push($videos, Modelos::VideosCanal($guardado, $respuesta[$x]["titulo"], $respuesta[$x]["identificador"], $respuesta[$x]["miniatura"], $respuesta[$x]["visitas"], $respuesta[$x]["fecha_creacion"], $respuesta[$x]["duracion"], $canalActual));
            }

            $mas = Paginacion::ContieneMas("
            
            SELECT v.titulo,
            v.identificador,
            v.miniatura,
            v.visitas,
            v.duracion,
            v.fecha_creacion
            FROM videos v
            LEFT JOIN canales c ON c.id = v.canal_id 
            WHERE c.nombre_canal = ?
            AND v.estado = 'publico' 
            ORDER BY v.fecha_creacion DESC
            LIMIT " . $limit . " OFFSET " . $offset+20 ."
        ",$this->con, $canalActual);

            echo RespuestaOK(["datos" => [
                "pag" => $mas,
            ],
        "respuesta" => $videos]);


        } else {
            echo RespuestaFail("No se encontró el canal.");
        }
    }

    public function SobreMi() 
    {

        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $canal = file_get_contents("php://input");
        $datos = json_decode($canal, true);

        $existe = $this->ExtraExiste->ExisteCanal($datos["canal"]);
        $canalActual = $datos["canal"];

        if ($existe > 0){

            $q = "
            
                SELECT c.descripcion,
                u.pais,
                u.fecha_registro
                FROM canales c
                LEFT JOIN usuarios u ON u.id = c.usuario_id
                WHERE c.nombre_canal = ?
            "; 

            $obtenerSobreMiCanal = $this->con->prepare($q);
            $obtenerSobreMiCanal->bindParam(1, $canalActual);
            $estado = $obtenerSobreMiCanal->execute();
            $respuesta = $obtenerSobreMiCanal->fetch(PDO::FETCH_ASSOC);

            if (!$estado)
            {
                echo EstadoFAIL();
                return;
            }

            echo RespuestaOK(Modelos::SobreMi($respuesta["descripcion"], $respuesta["pais"], Date::Registro($respuesta["fecha_registro"])));

        } else {
            echo RespuestaFail("No se encontró el canal.");
        }
    }

    public function VideosPrivados()
    {

        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }


        $canal = file_get_contents("php://input");
        $datos = json_decode($canal, true);

        $existe = $this->ExtraExiste->ExisteCanal($datos["canal"]);
        $canalActual = $datos["canal"];
        $limit = $datos["limit"];
        $offset = $datos["offset"];

        if ($existe > 0){

            $q = "
            
                SELECT v.titulo,
                v.identificador,
                v.miniatura,
                v.visitas,
                v.duracion,
                v.fecha_creacion
                FROM videos v
                LEFT JOIN canales c ON c.id = v.canal_id 
                WHERE c.nombre_canal = ?
                AND v.estado = 'privado'
                ORDER BY v.fecha_creacion DESC
                LIMIT " . $limit . " OFFSET " . $offset ."
            "; 

            $obtenerVideosPrivadosCanal = $this->con->prepare($q);
            $obtenerVideosPrivadosCanal->bindParam(1, $canalActual);
            $estado = $obtenerVideosPrivadosCanal->execute();
            $respuesta = $obtenerVideosPrivadosCanal->fetchAll(PDO::FETCH_ASSOC);

            if (!$estado)
            {
                echo EstadoFAIL();
                return;
            }

            $totalVideosPrivados = $this->con->prepare("SELECT COUNT(*) AS total_privados FROM videos v LEFT JOIN canales c ON c.id = v.canal_id WHERE c.nombre_canal = ? AND v.estado = 'privado'");
            $totalVideosPrivados->bindParam(1, $canalActual);
            $estadoTotal = $totalVideosPrivados->execute();
            $respuestaTotal = $totalVideosPrivados->fetch(PDO::FETCH_ASSOC);

            if (!$estadoTotal)
            {
                echo EstadoFAIL();
                return;
            }

            $videos = [];

            for ($x = 0;$x < count($respuesta); $x++)
            {
                array_push($videos, Modelos::VideosPrivadosCanal($respuesta[$x]["titulo"], $respuesta[$x]["identificador"], $respuesta[$x]["miniatura"], $respuesta[$x]["visitas"], $respuesta[$x]["fecha_creacion"], $respuesta[$x]["duracion"], $canalActual));
            }

            $mas = Paginacion::ContieneMas("
            
            SELECT v.titulo,
                v.identificador,
                v.miniatura,
                v.visitas,
                v.duracion,
                v.fecha_creacion
                FROM videos v
                LEFT JOIN canales c ON c.id = v.canal_id 
                WHERE c.nombre_canal = ?
                AND v.estado = 'privado'
                ORDER BY v.fecha_creacion DESC
            LIMIT " . $limit . " OFFSET " . $offset+20 ."
        ",$this->con, $canalActual);

        echo RespuestaOK(["datos" => [
            "pag" => $mas,
            "total" => $respuestaTotal["total_privados"],
        ],
    "respuesta" => $videos]);


        } else {
            echo RespuestaFail("No se encontró el canal.");
        }
    }

    public function HacerPublicoVideo()
    {

        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $canal = file_get_contents("php://input");
        $datos = json_decode($canal, true);

        $existe = $this->ExtraExiste->ExisteCanal($datos["canal"]);
        $identificador_video = $datos["identificador"];

        if ($existe > 0){

            $q = "
                UPDATE videos SET estado = 'publico' WHERE identificador = ?
            "; 

            $publicoVideo = $this->con->prepare($q);
            $publicoVideo->bindParam(1, $identificador_video);
            $estado = $publicoVideo->execute();

            if (!$estado)
            {
                echo EstadoFAIL();
                return;
            }

            echo EstadoOK();

        } else {
            echo RespuestaFail("No se encontró el canal.");
        }
    }
    
    public function OcultarVideo()
    {

        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $canal = file_get_contents("php://input");
        $datos = json_decode($canal, true);

        $existe = $this->ExtraExiste->ExisteCanal($datos["canal"]);
        $identificador_video = $datos["identificador"];

        if ($existe > 0){

            $q = "
                UPDATE videos SET estado = 'privado' WHERE identificador = ?
            "; 

            $privadoVideo = $this->con->prepare($q);
            $privadoVideo->bindParam(1, $identificador_video);
            $estado = $privadoVideo->execute();

            if (!$estado)
            {
                echo EstadoFAIL();
                return;
            }

            echo EstadoOK();

        } else {
            echo RespuestaFail("No se encontró el canal.");
        }
    }

    public function BorrarVideo()
    {
        
        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $canal = file_get_contents("php://input");
        $datos = json_decode($canal, true);

        $existe = $this->ExtraExiste->ExisteCanal($datos["canal"]);
        $identificador_video = $datos["identificador"];

        if ($existe > 0){

            $q = "
                DELETE FROM videos WHERE identificador = ?
            "; 

            $borrarVideo = $this->con->prepare($q);
            $borrarVideo->bindParam(1, $identificador_video);
            $estado = $borrarVideo->execute();

            if (!$estado)
            {
                echo EstadoFAIL();
                return;
            }

            echo EstadoOK();

        } else {
            echo RespuestaFail("No se encontró el canal.");
        }
    }

    public function GuardarVideo()
    {
        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail(mensaje: "No se han podido obtener los datos.");
            return;
        }

        $canal = file_get_contents("php://input");
        $datos = json_decode($canal, true);

        $existe = $this->ExtraExiste->ExisteCanal($datos["canal"]);
        $identificador_video = $datos["identificador"];
        $usuarioID = UsuariosObtener::Id($identificador, $this->con);
        $videoID = VideosObtener::Id($identificador_video, $this->con);
        $nuevo_guardar_identificador = VideosGuardadosGenerar::GenerarIdentificador($this->con);

        if ($existe > 0){

            $q = "
                INSERT INTO videos_guardados(usuario_id, video_id, identificador) VALUES (?, ?, ?)
            "; 

            $guardarVideo = $this->con->prepare($q);
            $guardarVideo->bindParam(1, $usuarioID);
            $guardarVideo->bindParam(2, $videoID);
            $guardarVideo->bindParam(3, $nuevo_guardar_identificador);
            $estado = $guardarVideo->execute();

            if (!$estado)
            {
                echo EstadoFAIL();
                return;
            }

            echo EstadoOK();

        } else {
            echo RespuestaFail("No se encontró el canal.");
        }
    }

    public function QuitarVideo()
    {
        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $canal = file_get_contents("php://input");
        $datos = json_decode($canal, true);

        $existe = $this->ExtraExiste->ExisteCanal($datos["canal"]);
        $identificador_video = $datos["identificador"];
        $usuarioID = UsuariosObtener::Id($identificador, $this->con);
        $videoID = VideosObtener::Id($identificador_video, $this->con);

        if ($existe > 0){

            $q = "
                DELETE FROM videos_guardados WHERE video_id = ? AND usuario_id = ?
            "; 

            $quitarVideo = $this->con->prepare($q);
            $quitarVideo->bindParam(1, $videoID);
            $quitarVideo->bindParam(2, $usuarioID);
            $estado = $quitarVideo->execute();

            if (!$estado)
            {
                echo EstadoFAIL();
                return;
            }

            echo EstadoOK();

        } else {
            echo RespuestaFail("No se encontró el canal.");
        }
    }

    public function InicioCanal()
    {
        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $canal = file_get_contents("php://input");
        $datos = json_decode($canal, true);

        $existe = $this->ExtraExiste->ExisteCanal($datos["canal"]);
        $canalActual = $datos["canal"];
        $usuarioCanalId = Obtener::Id($canalActual, $this->con);
        $usuarioId = \Utils\Usuarios\Obtener::IdPorCanalId($usuarioCanalId, $this->con);

        if ($existe > 0){

            $q = "
            
                SELECT v.titulo,
                v.identificador,
                v.miniatura,
                v.visitas,
                v.duracion,
                v.fecha_creacion
                FROM videos v
                LEFT JOIN canales c ON c.id = v.canal_id 
                WHERE c.nombre_canal = ?
                AND v.estado = 'publico' 
                ORDER BY (SELECT COUNT(*) 
    FROM videos_marcados 
    WHERE video_id = v.id AND gustado = 'si') DESC
                LIMIT 3
            "; 

            $inicioCanalVideos = $this->con->prepare($q);
            $inicioCanalVideos->bindParam(1, $canalActual);
            $estado = $inicioCanalVideos->execute();
            $respuesta = $inicioCanalVideos->fetchAll(PDO::FETCH_ASSOC);

            if (!$estado)
            {
                echo EstadoFAIL();
                return;
            }

            $videos = [];

            for ($x = 0;$x < count($respuesta); $x++)
            {
                array_push($videos, Modelos::VideosPrivadosCanal( $respuesta[$x]["titulo"], $respuesta[$x]["identificador"], $respuesta[$x]["miniatura"], $respuesta[$x]["visitas"], $respuesta[$x]["fecha_creacion"], $respuesta[$x]["duracion"], $canalActual));
            }

            $qSiguiendo = "
            
                SELECT u.nombre,
                u.avatar,
                c.nombre_canal
                FROM usuarios u 
                LEFT JOIN canales c ON c.usuario_id = u.id
                LEFT JOIN suscripciones s ON s.canal_id = c.id
                WHERE s.usuario_id = ?
                ORDER BY s.fecha DESC
                LIMIT 4 
            "; 

            $canalesSigo = $this->con->prepare($qSiguiendo);
            $canalesSigo->bindParam(1, $usuarioId);
            $estado = $canalesSigo->execute();
            $respuesta = $canalesSigo->fetchAll(PDO::FETCH_ASSOC);

            if (!$estado)
            {
                echo EstadoFAIL();
                return;
            }

            $suscripciones = [];

            for ($x = 0;$x < count($respuesta); $x++)
            {
                array_push($suscripciones, SuscripcionesModelos::CanalesQueSigo($respuesta[$x]["nombre"], $respuesta[$x]["avatar"], $respuesta[$x]["nombre_canal"]));
            }

            echo RespuestaOK(["respuesta" => [
                "contenido" => $videos,
                "suscripciones" => $suscripciones,
            ]]);

        } else {
            echo RespuestaFail("No se encontró el canal.");
        }
    }

    public function CanalesQueSigo()
    {
        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $canal = file_get_contents("php://input");
        $datos = json_decode($canal, true);

        $existe = $this->ExtraExiste->ExisteCanal($datos["canal"]);
        $canalActual = $datos["canal"];
        $usuarioCanalId = Obtener::Id($canalActual, $this->con);

        if ($existe > 0){

            $q = "
            
                SELECT u.nombre,
                u.avatar,
                c.nombre_canal
                FROM usuarios u 
                LEFT JOIN canales c ON c.usuario_id = u.id
                LEFT JOIN suscripciones s ON s.canal_id = c.id
                WHERE s.usuario_id = ?;
            "; 

            $canalesSigo = $this->con->prepare($q);
            $canalesSigo->bindParam(1, $usuarioCanalId);
            $estado = $canalesSigo->execute();
            $respuesta = $canalesSigo->fetchAll(PDO::FETCH_ASSOC);

            if (!$estado)
            {
                echo EstadoFAIL();
                return;
            }

            $suscripciones = [];

            for ($x = 0;$x < count($respuesta); $x++)
            {
                array_push($suscripciones, SuscripcionesModelos::CanalesQueSigo($respuesta[$x]["nombre"], $respuesta[$x]["avatar"], $respuesta[$x]["nombre_canal"]));
            }

            echo RespuestaOK($suscripciones);

        } else {
            echo RespuestaFail("No se encontró el canal.");
        }
    }

    public function ActualizarDescripcion()
    {
        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $canal = file_get_contents("php://input");
        $datos = json_decode($canal, true);

        $existe = $this->ExtraExiste->ExisteCanal($datos["canal"]);
        $canalActual = $datos["canal"];
        $descripcionNueva = $datos["descripcion"];
        $usuarioCanalId = Obtener::Id($canalActual, $this->con);

        if ($existe > 0)
        {

            $q = "UPDATE canales SET descripcion = ? WHERE id = ?";

            $actualizarDescripcion = $this->con->prepare($q);
            $actualizarDescripcion->bindParam(1, $descripcionNueva);
            $actualizarDescripcion->bindParam(2, $usuarioCanalId);
            $estado = $actualizarDescripcion->execute();

            if (!$estado)
            {
                echo EstadoFAIL();
                return;
            }

            echo EstadoOK();

        } else {
            echo RespuestaFail("No se encontró el canal.");
        }
    }

}