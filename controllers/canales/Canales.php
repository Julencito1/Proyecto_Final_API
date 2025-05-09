<?php

namespace Controllers\Canales;

use Structs\Canales\EstructuraCanales;
use Utils\Canales\Existe;
use Utils\Canales\Generar;
use Utils\Canales\Obtener;
use Models\Canales\Modelos;
use Utils\Auth\Auth;
use PDO;
use Utils\Date\Date;

include_once __DIR__ . "../../../response/respuestas.php";

class Canales extends EstructuraCanales
{
    protected $con;
    public static $tabla = "canales";
    private $ExtraExiste;
    private $ExtraGenerar;
    private $ExtraObtener;
    private $Modelos;

    public function __construct($conexion)
    {
        $this->con = $conexion;
        $this->ExtraExiste = new Existe($conexion, $this);
        $this->ExtraGenerar = new Generar($conexion, $this, $this->ExtraExiste);
        $this->ExtraObtener = new Obtener($conexion, $this);
        $this->Modelos = new Modelos($this);
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

        $portada = $this->ExtraGenerar->Portada();

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


        $existe = $this->ExtraExiste->ExisteCanal($datos["canal"]);

        if ($existe > 0)
        {

            $canal_b = $datos["canal"];

            $q = "
               SELECT c.nombre_canal, c.portada, u.nombre, u.avatar,
                (SELECT COUNT(*) FROM videos v LEFT JOIN canales c ON c.id = v.canal_id WHERE c.nombre_canal = ?) AS total_videos,
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

            $esSuscriptor = $this->ExtraObtener->EsSuscriptor($identificador, $datos["canal"]);
            $esActual = $this->ExtraObtener->EsActual($identificador, $datos["canal"]);

            if ($esActual === null)
            {
                echo EstadoFAIL();
                return;
            }
            

            echo RespuestaOK(Modelos::DatosCanal($respuesta["nombre_canal"], $respuesta["portada"], $respuesta["nombre"], $respuesta["avatar"], $respuesta["total_videos"], $respuesta["total_suscriptores"], $esSuscriptor, $esActual));


        } else {

            echo RespuestaFail("No se encontró el canal.");
        }
    }

    public function VideosCanal()
    {
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
                AND v.estado = 'publico' 
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
                array_push($videos, Modelos::VideosCanal($respuesta[$x]["titulo"], $respuesta[$x]["identificador"], $respuesta[$x]["miniatura"], $respuesta[$x]["visitas"], $respuesta[$x]["fecha_creacion"], $respuesta[$x]["duracion"], $canalActual));
            }

            echo RespuestaOK($videos);


        } else {
            echo RespuestaFail("No se encontró el canal.");
        }
    }

    public function SobreMi() 
    {
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

            $videos = [];

            for ($x = 0;$x < count($respuesta); $x++)
            {
                array_push($videos, Modelos::VideosCanal($respuesta[$x]["titulo"], $respuesta[$x]["identificador"], $respuesta[$x]["miniatura"], $respuesta[$x]["visitas"], $respuesta[$x]["fecha_creacion"], $respuesta[$x]["duracion"], $canalActual));
            }

            echo RespuestaOK($videos);


        } else {
            echo RespuestaFail("No se encontró el canal.");
        }
    }


}