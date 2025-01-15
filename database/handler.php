<?php 

include "../database/database.php";

$db = new Database();

$conexion = $db->Conexion(DRIVER, HOST, PORT, DBNAME, USER, PASSWORD);
?>