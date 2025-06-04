<?php


namespace Models\Historial;

use Utils\Date\Date;

class Modelos
{

    private $historial;

    public function __construct($historial)
    {
        $this->historial = $historial;
    }

    public static function ObtenerHistorial($titulo, $identificador, $miniatura, $visitas, $duracion, $fecha_creacion, $avatar, $nombre, $fecha_visualizacion)
    {
        return [
            "contenido" => [
                "titulo" => $titulo,
                "creador" => [
                    "nombre" => $nombre,
                    "media" => [
                        "avatar" => $avatar,
                    ]
                ]
            ],
            "link" => [
                "ruta" => $identificador,
            ],
            "media" => [
                "miniatura" => $miniatura
            ],
            "info" => [
                "visitas" => $visitas,
                "duracion" => $duracion,
                "fechas" => [
                    "fecha_creacion" => Date::TiempoRelativo($fecha_creacion),
                    "fecha_visualizacion" => Date::FechaVisualizacion($fecha_visualizacion),
                ]
            ]
        ];
    }
}

