<?php

define("CODIGO_OK", 200);
define("EXITO", "exito");
define("CODIGO_FAIL", 500);
define("FALLIDO", "fallido");

function RespuestaOK($mensaje)
{
    return json_encode(["code" => CODIGO_OK, "mensaje" => $mensaje, "status" => EXITO ], JSON_PRETTY_PRINT);
}

function EstadoOK() 
{
    return json_encode(["code" => CODIGO_OK, "status" => EXITO], JSON_PRETTY_PRINT);
}

function RespuestaFail($mensaje)
{
    return json_encode(["code" => CODIGO_FAIL, "mensaje" => $mensaje, "status" => FALLIDO ], JSON_PRETTY_PRINT);
}

function EstadoFAIL() 
{
    return json_encode(["code" => CODIGO_FAIL, "status" => FALLIDO], JSON_PRETTY_PRINT);
}

function InternalServerError() 
{
    return json_encode(["code" => CODIGO_FAIL, "mensaje" => "Algo ha salido mal", "status" => FALLIDO], JSON_PRETTY_PRINT);
}


?>