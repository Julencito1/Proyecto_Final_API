<?php

namespace Models\Usuarios;

class Modelos {

    private $usuario;

    public function __construct($usuario)
    {
        $this->usuario = $usuario;
    }
    public function DatosUsuario($nombre, $email, $avatar, $nombre_canal)
    {
        return [
            "usuario" => [
                "nombre" => $nombre,
                "email" => $email,
                "imagen" => [
                  "avatar" => $avatar,
                ],
                "canal" => [
                    "nombre_canal" => $nombre_canal,
                ],
            ],
        ];
    }
}

