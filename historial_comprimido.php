<?php
require_once 'db/conexion.php';

$sql = "SELECT * FROM historial__dicom WHERE eliminado = 0 ORDER BY fecha_subida DESC LIMIT 15";
$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="http://localhost/visor_web/style.css">
</head>
<body>
    <table class='tableBox--table'>
    <thead>
        <tr>
            <th>#</th>
            <th>Archivo</th>
            <th>Fecha</th>
            <th class="th--actions">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($resultado && $resultado->num_rows > 0): ?>
        <?php $i = 1; ?>
        <?php while ($fila = $resultado->fetch_assoc()): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($fila['nombre_archivo']) ?></td>
                <td><?= $fila['fecha_subida'] ?></td>
                <td>
                    <div id="td--BtnBox">
                        <button id="seeBtn" onclick="visualizarDesdeHistorial('<?= htmlspecialchars(basename($fila['ruta'])) ?>')">Ver</button>
                        <button id="deleteBtn" onclick="eliminarDesdeHistorial(<?= $fila['id'] ?>)">Eliminar</button>
                    </div>
                </td>
            </tr>
        <?php endwhile; ?>
        <?php else: ?>
        <tr><td colspan="4">No hay imágenes recientes</td></tr>
        <?php endif; ?>
    </tbody>
    </table>
</body>
</html>