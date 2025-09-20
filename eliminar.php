<?php
require_once 'db/conexion.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id"])) {
    $id = intval($_POST["id"]);

    // Buscar la ruta del archivo a eliminar
    $consulta = $conexion->prepare("SELECT ruta FROM historial__dicom WHERE id = ?");
    $consulta->bind_param("i", $id);
    $consulta->execute();
    $resultado = $consulta->get_result();

    if ($resultado && $resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();
        $ruta = $fila["ruta"];

        // Eliminar archivo físicamente si existe
        if (file_exists($ruta)) {
            unlink($ruta);
        }

        // Marcar como eliminado en la base de datos
        $consulta = $conexion->prepare("UPDATE historial__dicom SET eliminado = 1 WHERE id = ?");
        $consulta->bind_param("i", $id);
        $consulta->execute();
    }
}

// Redirigir de nuevo al historial
header("Location: historial.php");
exit;
