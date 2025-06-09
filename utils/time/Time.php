<?php


namespace Utils\time;


class Time {

    public static function SegundosAMinutos($segundos)
    {
        $conversion = "";
        $minutos = intval($segundos / 60);
        $segundos_arr = intval($segundos % 60);
        
        if ($minutos < 10)
        {
            $conversion .= "0" . intval($segundos / 60);
        } else {

            $conversion .= intval($segundos / 60);
        }

        $conversion .= ":";

        if ($segundos_arr < 10)
        {
            $conversion .= "0" . intval($segundos % 60);
        } else {
            $conversion .= intval($segundos % 60);
        }

        return $conversion;
    }
}