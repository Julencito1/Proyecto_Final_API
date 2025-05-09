<?php

namespace Utils\Suscripciones;
use Controllers\Suscripciones\Suscripciones;
use PDO;


class Obtener
{
    protected $con;
    protected $suscripciones;

    public function __construct(PDO $conexion, Suscripciones $suscripciones)
    {
        $this->con = $conexion;
        $this->suscripciones = $suscripciones;
    }



}

?>