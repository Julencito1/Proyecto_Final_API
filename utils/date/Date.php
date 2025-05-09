<?php

namespace Utils\Date;

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
}