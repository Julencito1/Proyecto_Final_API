<?php

namespace Models\VideosMarcados;

use Utils\Date\Date;
use Utils\Time\Time;

class Modelos {

    public static function ObtenerVideosMarcados($titulo, $identificador, $miniatura, $visitas, $duracion, $fecha_creacion, $nombre_canal, $avatar, $nombre_usuario, $identificador_vm, $gustado_vm) 
    {
        return [
            "contenido" => [
                "titulo" => $titulo,
                "canal" => [
                    "nombre_canal" => $nombre_canal,
                    "usuario" => [
                        "nombre" => $nombre_usuario,
                    ]
                ]
            ],
            "link" => [
                "identificador_video" => $identificador,
                "identificador_vm" => $identificador_vm,
            ],
            "media" => [
                "video" => [
                    "miniatura" => $miniatura,
                ],
                "usuario" => [
                    "avatar" => $avatar,
                ]
            ],
            "estadisticas" => [
                "visitas" => $visitas,
                "duracion" => Time::SegundosAMinutos($duracion),
                "fechas" => [
                    "fecha_creacion" => Date::TiempoRelativo($fecha_creacion),
                ],
                "gustado_vm" => $gustado_vm
            ]
        ];
    }

}

