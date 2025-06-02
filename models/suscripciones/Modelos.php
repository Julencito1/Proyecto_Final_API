<?php


namespace Models\Suscripciones;

class Modelos
{

    private $suscripciones;

    public function __construct($suscripciones)
    {
        $this->suscripciones = $suscripciones;
    }

    public static function SidebarSuscripcionesArray($nombre, $avatar, $nombre_canal)
    {
        return [
           
                "usuario" => [
                    "nombre" => $nombre,
                    "media" => [
                        "avatar" => $avatar,
                    ],
                    "canal" => [
                        "nombre_canal" => $nombre_canal,
                    ],
                ],
            
        ];
    }

    public static function SidebarSuscripciones($array, $mostrar)
    {
        return [
            "suscripciones" => [
                "listar" => $array,
                "mostrar" => $mostrar,
            ],
        ];
    }

    public static function CanalesQueSigo($nombre, $avatar, $nombre_canal)
    {
        return [
            "nombre" => $nombre,
            "media" => [
                "avatar" => $avatar,
            ],
            "canal" => [
                "nombre_canal" => $nombre_canal,
            ]
        ];
    }
}

