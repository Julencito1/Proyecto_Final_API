<?php

namespace Controllers\Notificaciones;

use Models\Notificaciones\Modelos;
use PDO;
use Structs\Notificaciones\EstructuraNotificaciones;
use Utils\Auth\Auth;
use Utils\Usuarios\Obtener;

class Notificaciones extends EstructuraNotificaciones
{
    protected $con;
    public function __construct($conexion)
    {
        $this->con = $conexion;
    }

    public function ConteoNotificacionesUsuario()
    {

        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $q = "
            SELECT COUNT(*) AS total
            FROM notificaciones n
            LEFT JOIN usuarios u ON n.usuario_id = u.id
            WHERE u.identificador = ? 
                AND n.leida = 0
                AND n.fecha >= DATE_SUB(NOW(), INTERVAL 1 WEEK);
        ";

        $obtenerConteoNotificaciones = $this->con->prepare($q);
        $obtenerConteoNotificaciones->bindValue(1, $identificador);
        $estado = $obtenerConteoNotificaciones->execute();
        $respuesta = $obtenerConteoNotificaciones->fetch(PDO::FETCH_ASSOC);

        if (!$estado) {
            echo EstadoFAIL();
            return;
        }

        echo RespuestaOK(Modelos::ConteoNotificacionesUsuario($respuesta["total"] > 99 ? "+99" : $respuesta["total"]));
    }

    public function NotificacionesUsuario()
    {

        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        }

        $registro = file_get_contents("php://input");
        $datos = json_decode($registro, true);

        $limit = $datos["limit"];
        $offset = $datos["offset"];
        $usuarioID = Obtener::Id($identificador, $this->con);

        $q = "
           SELECT n.enlace, n.fecha, v.titulo, v.miniatura, c.nombre_canal, u.avatar FROM notificaciones n LEFT JOIN canales c ON c.id = n.canal_id LEFT JOIN videos v ON v.id = n.video_id LEFT JOIN usuarios u ON u.id = c.usuario_id WHERE n.usuario_id = ? AND n.fecha >= DATE_SUB(NOW(), INTERVAL 1 WEEK) ORDER BY n.fecha DESC LIMIT " . $limit . " OFFSET ". $offset . ";
        ";

        $obtenerNotificaciones = $this->con->prepare($q);
        $obtenerNotificaciones->bindValue(1, $usuarioID);
        
        $estado = $obtenerNotificaciones->execute();
        $respuesta = $obtenerNotificaciones->fetchAll(PDO::FETCH_ASSOC);

        if (!$estado) {
            echo EstadoFAIL();
            return; 
        }

        $notificaciones = [];

        for ($i = 0; $i < count($respuesta); $i++)
        {
            array_push($notificaciones, Modelos::NotificacionesUsuario($respuesta[$i]["enlace"], $respuesta[$i]["fecha"], $respuesta[$i]["miniatura"], $respuesta[$i]["titulo"], $respuesta[$i]["nombre_canal"], $respuesta[$i]["avatar"]));
        }

        echo RespuestaOK($notificaciones);
    }

    public function NotificacionesMarcarLeidas()
    {
        $headers = getallheaders();

        $identificador = Auth::ObtenerSemilla($headers);

        if ($identificador === "") {

            echo RespuestaFail("No se han podido obtener los datos.");
            return;
        } 

        $q = "
            UPDATE notificaciones n
            LEFT JOIN usuarios u ON u.id = n.usuario_id
            SET n.leida = 1
            WHERE n.leida = 0 AND u.identificador = ?
        ";

        $marcarLeidas = $this->con->prepare($q);
        $marcarLeidas->bindValue(1, $identificador);
        $estado = $marcarLeidas->execute();
        $respuesta = $marcarLeidas->fetch(PDO::FETCH_ASSOC);

        if (!$estado) {
            echo EstadoFAIL();
            return;
        }

        echo RespuestaOK("ok");
    }

}