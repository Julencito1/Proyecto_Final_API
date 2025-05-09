<?php


namespace Models\Notificaciones;

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
                    "fecha" => $fecha,
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

