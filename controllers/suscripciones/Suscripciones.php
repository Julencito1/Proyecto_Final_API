<?php


namespace Controllers\Suscripciones;

use PDO;
use Structs\suscripciones\EstructuraSuscripciones;
use Models\Suscripciones\Modelos;
use Utils\Auth\Auth;
use Utils\Canales\Obtener as CanalesObtener;
use Utils\Paginacion\Paginacion;
use Utils\Usuarios\Obtener;

class Suscripciones extends EstructuraSuscripciones
{
	protected $con;

	public function __construct($conexion)
	{
		$this->con = $conexion;
	}

	public function SidebarSuscripciones()
	{
		$headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $q = "
        	SELECT u.nombre,
        	u.avatar,
        	c.nombre_canal
        	FROM suscripciones s
        	LEFT JOIN canales c ON c.id = s.canal_id
        	LEFT JOIN usuarios u ON u.id = c.usuario_id
            LEFT JOIN usuarios uc ON uc.id = s.usuario_id
        	WHERE
        		uc.identificador = ?
            LIMIT 4 OFFSET 0
        ";

        $listarSidebarSuscripciones = $this->con->prepare($q);
        $listarSidebarSuscripciones->bindValue(1, $identificador);
        $estado = $listarSidebarSuscripciones->execute();
        $respuesta = $listarSidebarSuscripciones->fetchAll(PDO::FETCH_ASSOC);

        $suscripciones = [];

        
        for ($i = 0; $i < count($respuesta); $i++)
        {
        	array_push($suscripciones, Modelos::SidebarSuscripcionesArray($respuesta[$i]["nombre"], $respuesta[$i]["avatar"], $respuesta[$i]["nombre_canal"]));
        }

        if (!$estado) {
            echo EstadoFAIL();
            return;
        }

        echo RespuestaOK(Modelos::SidebarSuscripciones($suscripciones));

	}

    public function ObtenerSuscripciones()
    {
        $usuario = file_get_contents("php://input");
        $datos = json_decode($usuario, true);

        $headers = getallheaders();
        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $offset = $datos["offset"];

        $usuarioId = Obtener::Id($identificador, $this->con);

        $q = "
        SELECT 
            u.nombre,
            u.avatar,
            c.nombre_canal
        FROM suscripciones s
        LEFT JOIN canales c ON c.id = s.canal_id
        LEFT JOIN usuarios u ON u.id = c.usuario_id
        WHERE s.usuario_id = ?
        LIMIT 20 OFFSET ". $offset ."
        ";

        $obtenerCanales = $this->con->prepare($q);
        $obtenerCanales->bindParam(1, $usuarioId);
        $estado = $obtenerCanales->execute();
        $respuesta = $obtenerCanales->fetchAll(PDO::FETCH_ASSOC);

        if (!$estado)
        {
            echo EstadoFAIL();
            return;
        }

        $canales = [];

        for ($o = 0; $o < count($respuesta); $o++)
        {

            array_push($canales, Modelos::CanalesQueSigo($respuesta[$o]["nombre"], $respuesta[$o]["avatar"], $respuesta[$o]["nombre_canal"]));
        }

        $mas = Paginacion::ContieneMas(
            "
        SELECT 
            u.nombre,
            u.avatar,
            c.nombre_canal
        FROM suscripciones s
        LEFT JOIN canales c ON c.id = s.canal_id
        LEFT JOIN usuarios u ON u.id = c.usuario_id
        WHERE s.usuario_id = ?
        LIMIT 20 OFFSET ". $offset+20 ."
        ", $this->con, $usuarioId
        );

        echo RespuestaOK(
            ["suscripciones" => $canales,"mas" => $mas]
        );

    }

    public function Suscribirse() 
    {

        $canal = file_get_contents("php://input");
        $datos = json_decode($canal, true);

        $headers = getallheaders();
        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        if ($datos["canal"] === "")
        {
            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $estadoSuscripcion = CanalesObtener::EsSuscriptor($identificador, $datos["canal"], $this->con);

        if (!$estadoSuscripcion)
        {
            $usuarioID = Obtener::Id($identificador, $this->con);
            $canalID = CanalesObtener::Id($datos["canal"], $this->con);

            if ($usuarioID === 0 || $canalID === 0) {

                echo EstadoFAIL();
                return;
            }

            $q = "
            INSERT INTO suscripciones (usuario_id, canal_id) VALUES (?, ?)
            ";

            $accion = $this->con->prepare($q);
            $accion->bindParam(1, $usuarioID);
            $accion->bindParam(2, $canalID);
            $estado = $accion->execute();
            

            if (!$estado) 
            {
                echo EstadoFAIL();
                return;
            }

            echo EstadoOK();
        } else {

            echo EstadoFAIL();
        }

    }

    public function QuitarSuscripcion()
    {
       
        $canal = file_get_contents("php://input");
        $datos = json_decode($canal, true);

        $headers = getallheaders();
        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        if ($datos["canal"] === "")
        {
            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $estadoSuscripcion = CanalesObtener::EsSuscriptor($identificador, $datos["canal"], $this->con);

        if ($estadoSuscripcion)
        {
            $usuarioID = Obtener::Id($identificador, $this->con);
            $canalID = CanalesObtener::Id($datos["canal"], $this->con);

            if ($usuarioID === 0 || $canalID === 0) {

                echo EstadoFAIL();
                return;
            }

            $q = "
            DELETE FROM suscripciones WHERE usuario_id = ? AND canal_id = ?
            ";

            $accion = $this->con->prepare($q);
            $accion->bindParam(1, $usuarioID);
            $accion->bindParam(2, $canalID);
            $estado = $accion->execute();
            

            if (!$estado) 
            {
                echo EstadoFAIL();
                return;
            }

            echo EstadoOK();

        } else {
            echo EstadoFAIL();
        }
    }
}