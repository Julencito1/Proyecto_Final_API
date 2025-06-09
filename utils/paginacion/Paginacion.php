<?php

namespace Utils\paginacion;
use PDO;

class Paginacion {

    public static function ContieneMas($sql, $con, $canalActual) :bool
    {
        
        $consulta = $con->prepare($sql);
        $consulta->bindParam(1, $canalActual);
        $estado = $consulta->execute();
        $respuesta = $consulta->fetchAll(PDO::FETCH_ASSOC);

        if  (!$estado)
        {
            return false;
        }   
        
        return count($respuesta) > 0;
    }

    public static function ContieneMasVideo($sql, $con, $videoID) :bool
    {
        
        $consulta = $con->prepare($sql);
        $consulta->bindParam(1, $videoID);
        $estado = $consulta->execute();
        $respuesta = $consulta->fetchAll(PDO::FETCH_ASSOC);

        if  (!$estado)
        {
            return false;
        }   
        
        return count($respuesta) > 0;
    }

    public static function NoParametro($sql, $con) :bool
    {
        $consulta = $con->prepare($sql);
        $estado = $consulta->execute();
        $respuesta = $consulta->fetchAll(PDO::FETCH_ASSOC);

        if  (!$estado)
        {
            return false;
        }   
        
        return count($respuesta) > 0;
    }

    public static function ContieneDobleParametro($sql, $con, $param1, $param2) :bool
    {
        
        $consulta = $con->prepare($sql);
        $consulta->bindParam(1, $param1);
        $consulta->bindParam(2, $param2);
        $estado = $consulta->execute();
        $respuesta = $consulta->fetchAll(PDO::FETCH_ASSOC);

        if  (!$estado)
        {
            return false;
        }   
        
        return count($respuesta) > 0;
    }

    
}