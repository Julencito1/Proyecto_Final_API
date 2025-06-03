<?php

namespace Utils\Date;

use DateTime;

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

    public static function TiempoRelativo($fecha): string
    {

        $actual = new DateTime();
        $fecha_pasada  = new DateTime($fecha);

        $diferencia = $actual->diff($fecha_pasada);
        $mensaje = "";

        $unidadesM = [
            "y" => "año",
            "m" => "mes",
            "d" => "día",
            "h" => "hora",
            "i" => "minuto",
            "s" => "segundo",
        ];

        foreach ($diferencia as $x => $y)
        {
            if ($y > 0)
            {
            
                if ($y > 1 && $x === "m")
                {
                    $mensaje = "hace " . $y . " " . $unidadesM[$x] . "es";
                } else if ($y > 1 && $x !== "m") {

                    $mensaje = "hace " . $y . " " . $unidadesM[$x] . "s";
                } else if ($y > 1 && !in_array($y, $unidadesM)) 
                {
                    $mensaje = "hace un momento";
                }
                else {
                    $mensaje = "hace " . $y . " " . $unidadesM[$x];
                }

                break;
            }
        }

        return $mensaje;
    }
}