<?php

define("CODEOK", 200);
define("SUCCESS", "success");
define("CODEFAIL", 500);
define("FAILED", "failed");

function InternalServerError() 
{
    return json_encode(["code" => CODEOK, "mensaje" => "Algo ha salido mal", "status" => SUCCESS ], JSON_PRETTY_PRINT);
}

function RespuestaOK($mensaje)
{
    return json_encode(["code" => CODEOK, "mensaje" => $mensaje, "status" => SUCCESS ], JSON_PRETTY_PRINT);
}

function EstadoOK() 
{
    return json_encode(["code" => CODEOK, "status" => SUCCESS], JSON_PRETTY_PRINT);
}

function RespuestaFail($mensaje)
{
    return json_encode(["code" => CODEFAIL, "mensaje" => $mensaje, "status" => FAILED ], JSON_PRETTY_PRINT);
}

function EstadoFAIL() 
{
    return json_encode(["code" => CODEFAIL, "status" => FAILED], JSON_PRETTY_PRINT);
}

?>