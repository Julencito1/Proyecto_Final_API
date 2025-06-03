<?php


namespace Models\Comentarios;

use Utils\Date\Date;

class Modelos
{

    private $comentarios;

    public function __construct($comentarios)
    {
        $this->comentarios = $comentarios;
    }

    public static function Comentarios($contenido, $identificador, $fecha_publicacion, $nombre, $avatar, $nombre_canal, $total_megusta, $total_nomegusta, $marcadoComentario, $comentariosHijos)
    {
        return [
            "contenido" => $contenido,
            "accion" => [
                "identificador" => $identificador,
            ],
            "usuario" => [
                "nombre" => $nombre,
                "canal" => [
                    "nombre" => $nombre_canal,
                ],
                "media" => [
                    "avatar" => $avatar
                ],
            ],
            "estadisticas" => [
                "megusta" => $total_megusta,
                "no_megusta" => $total_nomegusta,
                "gustado" => $marcadoComentario,
            ],
            "fecha" => [
                "fecha_publicacion" => Date::TiempoRelativo($fecha_publicacion),
            ],
            "comentarios_hijos" => $comentariosHijos
        ];
    }

    public static function ObtenerComentariosHijos($contenido, $identificador, $fecha_publicacion, $nombre, $avatar, $nombre_canal, $total_megusta, $marcadoComentarioHijo, $total_nomegusta)
    {
        return [
            "contenido" => $contenido,
            "accion" => [
                "identificador" => $identificador,
            ],
            "usuario" => [
                "nombre" => $nombre,
                "canal" => [
                    "nombre" => $nombre_canal,
                ],
                "media" => [
                    "avatar" => $avatar
                ],
            ],
            "estadisticas" => [
                "megusta" => $total_megusta,
                "no_megusta" => $total_nomegusta,
                "gustado" => $marcadoComentarioHijo,
            ],
            "fecha" => [
                "fecha_publicacion" => Date::TiempoRelativo($fecha_publicacion),
            ]
        ];
    }

    public static function ObtenerComentariosMarcados($nombre, $avatar, $contenido, $identificador_comentario, $fecha_publicacion, $tipo, $identificador_video, $gustado) 
    {
        return [
            "contenido" => $contenido,
            "accion" => [
                "identificador" => $identificador_comentario,
                "identificador_video" => $identificador_video,
            ],
            "usuario" => [
                "nombre" => $nombre,
                "media" => [
                    "avatar" => $avatar,
                ]
                ],
            "info" => [
                "tipo" => $tipo,
                "gustado" => $gustado,
                "fecha" => [
                    "fecha_publicacion" => Date::TiempoRelativo($fecha_publicacion),
                ]
            ]
        ];
    }
}

