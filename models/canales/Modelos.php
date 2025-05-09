<?php


namespace Models\Canales;

class Modelos
{

    private $canales;

    public function __construct($canales)
    {
        $this->canales = $canales;
    }

    public static function DatosCanal($nombre_canal, $portada, $nombre, $avatar, $total_videos, $total_suscriptores, $esSuscriptor, $esActual)
    {
        return [

            "canal" => [
                "apodos" => [
                    "nombre_canal" => $nombre_canal,
                    "nombre_usuario" => $nombre,
                ],
                "media" => [
                    "portada" => $portada,
                    "usuario" => [
                        "avatar" => $avatar,
                    ],
                ],
                "info" => [
                    "estadisticas" => [
                        "suscriptores" => $total_suscriptores,
                        "videos" => $total_videos,
                    ],
                ],
                "suscriptor" => $esSuscriptor,
                "actual" => $esActual,
            ],

        ];
    }


    public static function VideosCanal($titulo, $identificador, $miniatura, $visitas, $fecha_creacion, $duracion, $canal)
    {
        return [
            "canal" => [
                "videos" => [
                    "contenido" => [
                        "titulo" => $titulo,
                    ],
                    "link" => [
                        "ruta" => "?canal=" . $canal . "&ref=". $identificador,
                    ],
                    "media" => [
                        "miniatura" => $miniatura,
                    ],
                    "estadisticas" => [
                        "visitas" => $visitas,
                        "duracion" => $duracion,
                        "fecha" => [
                            "fecha_creacion" => $fecha_creacion,
                        ]
                    ]
                ]
            ]
        ];
    }

    public static function SobreMi($descripcion, $pais, $fecha_registro)
    {
        return [
            "canal" => [
                "info" => [
                    "descripcion" => $descripcion,
                    "localizacion" => [
                        "pais" => $pais,
                    ],
                    "fechas" => [
                        "fecha_registro" => $fecha_registro,
                    ]
                ]
            ]
        ];
    }
}

