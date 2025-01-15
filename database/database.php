<?php 

include "../config.php";

class Database {

    public function Conexion($Driver, $Host, $Port, $DBName, $User, $Password) 
    {

        $conn = new PDO($Driver. ":host=" . $Host . ":" . $Port . ";dbname=" . $DBName, $User, $Password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        return $conn;
    }
}

?>