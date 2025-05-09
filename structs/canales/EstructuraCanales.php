<?php

namespace Structs\Canales;
use PDO;

abstract class EstructuraCanales
{
    abstract public function CrearCanal(int $usuario_id, string $nombre_canal): bool;
    abstract public function DatosCanal();
    abstract public function VideosCanal();
}