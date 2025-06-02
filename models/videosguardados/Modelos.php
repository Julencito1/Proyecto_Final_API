<?php

namespace Models\VideosGuardados;

use Utils\Time\Time;

class Modelos {

    public static function VideosGuardados($titulo, $identificador, $miniatura, $visitas, $duracion, $fecha_creacion, $nombre_canal, $avatar, $nombre, $identificador_vg)
    {
        return [
            "video" => [
                "titulo" => $titulo,
                "link" => [
                    "url" => $identificador,
                ],
                "media" => [
                    "miniatura" => $miniatura,
                ],
                "estadisticas" => [
                    "visitas" => $visitas,
                    "duracion" => Time::SegundosAMinutos($duracion),
                    "fecha" => [
                        "fecha_creacion" => $fecha_creacion,
                    ],
                ],
                "info" => [
                    "canal" => [
                        "nombre_canal" => $nombre_canal,
                    ],
                    "usuario" => [
                        "nombre" => $nombre,
                        "media" => [
                            "avatar" => $avatar,
                        ]
                    ]
                        ],
                        "guardado" => [
                            "identificador" => $identificador_vg,
                        ]
            ]
        ];
    }
}

