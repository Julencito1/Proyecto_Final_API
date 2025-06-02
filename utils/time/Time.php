<?php


namespace Utils\Time;


class Time {

    public static function SegundosAMinutos($segundos)
    {
        $conversion = "";
        $minutos = round($segundos / 60);
        $segundos_arr = round($segundos % 60);
        
        if ($minutos < 10)
        {
            $conversion .= "0" . round($segundos / 60);
        } else {

            $conversion .= round($segundos / 60);
        }

        $conversion .= ":";

        if ($segundos_arr < 10)
        {
            $conversion .= "0" . round($segundos % 60);
        } else {
            $conversion .= round($segundos % 60);
        }

        return $conversion;
    }
}