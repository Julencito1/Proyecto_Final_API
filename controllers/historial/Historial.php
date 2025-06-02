<?php

namespace Controllers\Historial;
use PDO;
use Utils\Auth\Auth;
use Utils\Historial\Generar;
use Utils\Historial\Obtener;
use Utils\Usuarios\Obtener as UsuariosObtener;
use Utils\Videos\Existe;
use Utils\Videos\Obtener as VideosObtener;

class Historial
{
    protected $con;
    public static $tabla = "historial";
    private $ExtraExiste;
    private $ExtraGenerar;
    private $ExtraObtener;


    public function __construct($conexion)
    {
        $this->con = $conexion;
        $this->ExtraGenerar = new Generar($conexion, $this);
        $this->ExtraObtener = new Obtener($conexion, $this);
    }

    public function AlmacenarHistorial()
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

        $existe = Existe::ExisteVideo($identificador_video, $this->con);

        if ($existe)
        {

            $guardadoRecientemente = $this->ExtraObtener->GuardadoMismoDia($identificador_video, $identificador);
            $usuarioID = UsuariosObtener::Id($identificador, $this->con);
            $videoID = VideosObtener::Id($identificador_video, $this->con);
            $generarIdentificador = $this->ExtraGenerar->GenerarIdentificador();
            $visto = Obtener::Visto($identificador_video, $identificador, $this->con);


            if (!$guardadoRecientemente)
            {
                $q = "
                    INSERT INTO historial(usuario_id, video_id, identificador) VALUES (?, ?, ?)
                ";

                $almacenarHistorial = $this->con->prepare($q);
                $almacenarHistorial->bindParam(1, $usuarioID);
                $almacenarHistorial->bindParam(2, $videoID);
                $almacenarHistorial->bindParam(3, $generarIdentificador);
                $estado = $almacenarHistorial->execute();
                
                if (!$estado)
                {
                    echo EstadoFAIL();
                    return;
                }

                echo EstadoOK();
            } else {

                echo EstadoOK();
            }

            if (!$visto)
            {
                $q = "
                    UPDATE videos SET visitas = visitas + 1 WHERE id = ?
                ";

                $aumentarVista = $this->con->prepare($q);
                $aumentarVista->bindParam(1, $videoID);
                $estado = $aumentarVista->execute();
                
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

    


}






?>