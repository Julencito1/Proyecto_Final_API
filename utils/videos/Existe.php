<?php


namespace Utils\Videos;

use Controllers\Videos\Videos;
use PDO;

class Existe
{
    protected $con;
    protected $videos;


    public function __construct(PDO $conexion, Videos $videos)
    {
        $this->con = $conexion;
        $this->videos = $videos;
    }

    public static function ExisteVideo(string $identificador_video, $cx): int
    {

        $video = $identificador_video;

        $q = "SELECT EXISTS(SELECT id FROM videos WHERE identificador = ?) AS e";
        $buscarVideo = $cx->prepare($q);
        $buscarVideo->bindParam(1, $video);

        $estado = $buscarVideo->execute();
        $respuesta = $buscarVideo->fetch(PDO::FETCH_ASSOC);


        if (!$estado) {
            return false;
        }

        return $respuesta["e"];

    }

}







