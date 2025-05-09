<?php


namespace Utils\Suscripciones;

use Controllers\Suscripciones\Suscripciones;
use PDO;
use Utils\Caracteres\Caracteres;


class Generar
{

    protected $con;
    protected $suscripciones;
    private $existe;

    public function __construct(PDO $conexion, Suscripciones $suscripciones, Existe $existe)
    {
        $this->con = $conexion;
        $this->suscripciones = $suscripciones;
        $this->existe = $existe;
    }

    
}


