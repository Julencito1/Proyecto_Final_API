<?php

include "../../controllers/usuarios.php";

class ObtenerSql extends Usuario
{
    
    public function Select_ID_Token(): string
    {
        $sql = "SELECT id FROM " . $this->tabla ." WHERE identificador = :identificador";

        return $sql;
    }



}





?>