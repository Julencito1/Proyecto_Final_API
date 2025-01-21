<?php

define("CODEOK", 200);
define("SUCCESS", "success");
define("CODEFAIL", 500);
define("FAILED", "failed");

function RespuestaOK($mensaje)
{
    return json_encode(["code" => CODEOK, "mensaje" => $mensaje, "status" => SUCCESS ]);
}

function EstadoOK() 
{
    return json_encode(["code" => CODEOK, "status" => SUCCESS]);
}

function RespuestaFail($mensaje)
{
    return json_encode(["code" => CODEFAIL, "mensaje" => $mensaje, "status" => FAILED ]);
}

function EstadoFAIL() 
{
    return json_encode(["code" => CODEFAIL, "status" => FAILED]);
}

?>