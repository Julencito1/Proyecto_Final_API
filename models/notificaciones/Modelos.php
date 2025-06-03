<?php


namespace Models\Notificaciones;

use Utils\Date\Date;

class Modelos
{

    private $notificaciones;

    public function __construct($notificaciones)
    {
        $this->notificaciones = $notificaciones;
    }

    public static function ConteoNotificacionesUsuario($total)
    {
        return [
            "notificaciones" =>
                [
                 "total" => $total,
                ],
        ];
    }

    public static function NotificacionesUsuario($enlace, $fecha, $miniatura, $titulo, $nombre_canal, $avatar)
    {
        return [

            "notificaciones" => [
                "info" => [
                    "link" => [
                        "enlace" => $enlace,
                    ],
                    "fecha" => Date::TiempoRelativo($fecha),
                ],
                "contenido" => [
                    "video" => [
                        "media" => [
                            "miniatura" => $miniatura,
                        ],
                        "extra" => [
                            "titulo" => $titulo,
                        ],
                        "usuario" => [
                            "canal" => [
                                "nombre" => $nombre_canal,
                            ],
                            "media" => [
                                "avatar" => $avatar,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}

