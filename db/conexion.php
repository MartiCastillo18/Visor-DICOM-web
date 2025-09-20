<?php

$host = "localhost";       
$usuario = "root";         
$contrasena = "";         
$base_datos = "visor_dicom";


$conexion = new mysqli($host, $usuario, $contrasena, $base_datos);


if ($conexion->connect_error) {
    die("Error al conectar a la base de datos: " . $conexion->connect_error);
}

// Establecer charset
$conexion->set_charset("utf8mb4");
?>
