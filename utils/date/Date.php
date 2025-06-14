<?php

namespace Utils\Date;

use DateTime;
use DateTimeZone;

class Date {

    public static function Registro($fecha): string
    {
        $fecha_solo = explode(" ", $fecha)[0];

        $meses = [
            "01" => "Enero",
            "02" => "Febrero",
            "03" => "Marzo",
            "04" => "Abril",
            "05" => "Mayo",
            "06" => "Junio",
            "07" => "Julio",
            "08" => "Agosto",
            "09" => "Septiembre",
            "10" => "Octubre",
            "11" => "Noviembre",
            "12" => "Diciembre",
        ];

        $year = explode("-", $fecha_solo)[0];
        $mes = explode("-", $fecha_solo)[1];

        return $meses[$mes] . ", " . $year;
    }

    public static function FechaVisualizacion($fecha): string
    {
        $fecha_solo = explode(" ", $fecha)[0];

        $meses = [
            "01" => "Enero",
            "02" => "Febrero",
            "03" => "Marzo",
            "04" => "Abril",
            "05" => "Mayo",
            "06" => "Junio",
            "07" => "Julio",
            "08" => "Agosto",
            "09" => "Septiembre",
            "10" => "Octubre",
            "11" => "Noviembre",
            "12" => "Diciembre",
        ];

        $year = explode("-", $fecha_solo)[0];
        $mes = explode("-", $fecha_solo)[1];
        $dia = explode("-", $fecha_solo)[2];

        return $dia. " " . $meses[$mes] . ", " . $year;
    }

    public static function TiempoRelativo($fecha): string
    {
        $zona = new DateTimeZone('Europe/Madrid');
        $zona_utc = new DateTimeZone('UTC');
       

        $fecha_pasada = new DateTime($fecha, $zona_utc);
        $fecha_pasada->setTimezone($zona);

        $actual = new DateTime('now', $zona);

        $diferencia = $actual->diff($fecha_pasada);

        $unidades = [
            'y' => 'año',
            'm' => 'mes',
            'd' => 'día',
            'h' => 'hora',
            'i' => 'minuto',
            's' => 'segundo'
        ];

        foreach ($unidades as $clave => $texto) {
            $valor = $diferencia->$clave;

            if ($valor > 0) {
                
               if ($texto === "mes") {

                    $plural = ($valor > 1) ? $texto . 'es' : $texto;
                    return "hace $valor $plural";

               } else {

                    $plural = ($valor > 1) ? $texto . 's' : $texto;
                    return "hace $valor $plural";
                    
               }
                
            }
        }

        return "hace un momento"; 
    }
}