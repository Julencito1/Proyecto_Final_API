<?php

namespace Models\Videos;

use Utils\Date\Date;
use Utils\Time\Time;

class Modelos {

    private $video;

    public function __construct($video)
    {
        $this->video = $video;
    }
    public function EstadisticasVideo($visto, $guardado, $gustado, $esPublico, $actual, $suscriptor)
    {
        return [
            
            "visto" => $visto,
            "guardado" => $guardado,
            "gustado" => $gustado,
            "estado" => $esPublico,
            "actual" => $actual,
            "suscriptor" => $suscriptor,
        ];
    }

    public function DatosVideo($categoria, $titulo, $descripcion, $video, $identificador, $visitas, $duracion, $me_gusta, $no_megusta, $total_suscriptores, $fecha_creacion, $nombre_canal, $nombre_usuario, $avatar_usuario)
    {
        return [
            "video"=> [
                "titulo" => $titulo,
                "descripcion" => $descripcion,
                "media" => [
                    "video" => $video,
                    "duracion" => $duracion,
                ],
                "canal" => [
                    "nombre" => $nombre_canal,
                    "nombre_usuario"=> $nombre_usuario,
                    "media" => [
                        "avatar" => $avatar_usuario,
                    ],
                    "total_suscriptores" => $total_suscriptores,
                ],
                "categoria" => [
                    "nombre" => $categoria,
                ],
                "estadisticas" => [
                    "visitas" => $visitas,
                    "me_gusta" => $me_gusta,
                    "no_megusta" => $no_megusta,
                    "fecha" => [
                        "fecha_creacion" => Date::TiempoRelativo($fecha_creacion)
                    ]
                    ],
                    "extra" => [
                        "identificador" => $identificador,
                    ]
            ],
        ];
    }

    public static function VideosRecomendados($titulo, $identificador, $miniatura, $visitas, $fecha_creacion, $duracion, $nombre, $avatar_usuario)
    {
        return [
            
                    "contenido" => [
                        "titulo" => $titulo,
                    ],
                    "link" => [
                        "ruta" => $identificador,
                    ],
                    "media" => [
                        "miniatura" => $miniatura,
                    ],
                    "estadisticas" => [
                        "visitas" => $visitas,
                        "duracion" => Time::SegundosAMinutos($duracion),
                        "fecha" => [
                            "fecha_creacion" => Date::TiempoRelativo($fecha_creacion),
                        ]
                        ],
                    "canal" => [
                        "nombre_usuario" => $nombre,
                        "media" => [
                            "avatar_usuario" => $avatar_usuario,
                        ]
                    ]
                    
                
        ];
    }

    public static function ResultadosBusqueda($titulo, $descripcion, $identificador, $miniatura, $visitas, $fecha_creacion, $duracion, $nombre, $nombre_canal, $avatar_usuario, $categoria)
    {
        return [
            
                    "contenido" => [
                        "titulo" => $titulo,
                        "descripcion" => $descripcion,
                    ],
                    "link" => [
                        "ruta" => $identificador,
                    ],
                    "media" => [
                        "miniatura" => $miniatura,
                    ],
                    "estadisticas" => [
                        "visitas" => $visitas,
                        "duracion" => Time::SegundosAMinutos($duracion),
                        "fecha" => [
                            "fecha_creacion" => Date::TiempoRelativo($fecha_creacion),
                        ]
                        ],
                    "canal" => [
                        "nombre_usuario" => $nombre,
                        "nombre_canal" => $nombre_canal,
                        "media" => [
                            "avatar_usuario" => $avatar_usuario,
                        ]
                    ],
                    "categoria" => $categoria,
                    "tipo" => "video",
                    
                
        ];
    }
}

