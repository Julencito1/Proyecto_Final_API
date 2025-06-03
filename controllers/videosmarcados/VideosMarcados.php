<?php

namespace Controllers\VideosMarcados;

use Models\VideosMarcados\Modelos;
use PDO;
use Utils\Auth\Auth;
use Utils\Paginacion\Paginacion;
use Utils\Usuarios\Obtener;

class VideosMarcados
{
    protected $con;
    public static $tabla = "videos_marcados";
    private $ExtraExiste;
    private $ExtraGenerar;
    private $ExtraObtener;


    public function __construct($conexion)
    {
        $this->con = $conexion;
        
    }

   public function ObtenerVideosMarcados()
   {

    $headers = getallheaders();

    $identificador = Auth::ObtenerSemilla($headers);

    if ($identificador === "") {

        echo RespuestaFail("No se han podido obtener los datos.");
        return;
    }

    $marcados = file_get_contents("php://input");
    $datos = json_decode($marcados, true);

    $offset = $datos["offset"];
    $usuarioId = Obtener::Id($identificador, $this->con);

    $q = "
        SELECT
            v.titulo,
            v.identificador AS identificador_video,
            v.miniatura,
            v.visitas,
            v.duracion,
            v.fecha_creacion,
            c.nombre_canal,
            u.avatar,
            u.nombre,
            vm.identificador,
            vm.gustado
        FROM videos_marcados vm
        LEFT JOIN videos v ON vm.video_id = v.id
        LEFT JOIN canales c ON c.id = v.canal_id
        LEFT JOIN usuarios u ON c.usuario_id = u.id
        WHERE v.estado = 'publico' AND vm.usuario_id = ?
        ORDER BY vm.fecha DESC
        LIMIT 20 OFFSET " . $offset ."
    ";

    $obtenerVideosMarcados  = $this->con->prepare($q);
    $obtenerVideosMarcados->bindParam(1, $usuarioId);
    $estado = $obtenerVideosMarcados->execute();
    $respuesta = $obtenerVideosMarcados->fetchAll(PDO::FETCH_ASSOC);

    if (!$estado)
    {
        echo EstadoFAIL();
        return;
    }

    $videos_marcados = [];

    for ($u = 0; $u < count($respuesta); $u++)
    {
        array_push($videos_marcados, 
        Modelos::ObtenerVideosMarcados(
            $respuesta[$u]["titulo"],
            $respuesta[$u]["identificador_video"],
            $respuesta[$u]["miniatura"],
            $respuesta[$u]["visitas"],
            $respuesta[$u]["duracion"],
            $respuesta[$u]["fecha_creacion"],
            $respuesta[$u]["nombre_canal"],
            $respuesta[$u]["avatar"],
            $respuesta[$u]["nombre"],
            $respuesta[$u]["identificador"],
            $respuesta[$u]["gustado"]
        )
        );
    }

    $mas = Paginacion::ContieneMas(
        "
        SELECT
            v.titulo,
            v.identificador AS identificador_video,
            v.miniatura,
            v.visitas,
            v.duracion,
            v.fecha_creacion,
            c.nombre_canal,
            u.avatar,
            vm.identificador,
            vm.gustado
        FROM videos_marcados vm
        LEFT JOIN videos v ON vm.video_id = v.id
        LEFT JOIN canales c ON c.id = v.canal_id
        LEFT JOIN usuarios u ON c.usuario_id = u.id
        WHERE v.estado = 'publico' AND vm.usuario_id = ?
        ORDER BY vm.fecha DESC
        LIMIT 20 OFFSET " . $offset + 20 ."
    ", $this->con, $usuarioId
    );

    echo RespuestaOK(
        ["videos_marcados" => $videos_marcados, "mas" => $mas]
    );

   }


}






?>