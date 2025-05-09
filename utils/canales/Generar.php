<?php


namespace Utils\Canales;

use Controllers\Canales\Canales;
use PDO;
use Utils\Caracteres\Caracteres;


class Generar
{

    protected $con;
    protected $canales;
    private $existe;

    public function __construct(PDO $conexion, Canales $canales, Existe $existe)
    {
        $this->con = $conexion;
        $this->canales = $canales;
        $this->existe = $existe;
    }

    public function NombreCanal($nombre): string
    {

        if ($nombre == "") {
            return "";
        }


        $buscar = true;

        while ($buscar)
        {

            $nombre_valido = str_replace(" ", "_", $nombre);
            $nombre_valido .= "-";

            for ($i = 0; $i < 5; $i++)
            {
                $nombre_valido .= Caracteres::$letras_numeros[rand(0, count(Caracteres::$letras_numeros) - 1)];
            }

            $existe = $this->existe->ExisteCanal($nombre_valido);

            if ($existe === 0)
            {
                $buscar = false;
                return $nombre_valido;
            }
        }

        return "";

    }

    public function Portada(): string
    {
        $portadas = [
            "a",
            "b",
            "c",
        ];

        return $portadas[rand(0, count($portadas) - 1)];
    }


}


