<?php
    require_once 'db/conexion.php';

    $sql = "SELECT * FROM historial__dicom WHERE eliminado = 0 ORDER BY fecha_subida DESC LIMIT 15";
    $resultado = $conexion->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Historial de Imágenes DICOM</title>
  <link rel="stylesheet" href="style_historial.css">
</head>
<body>

<h1> Historial de Imágenes DICOM</h1>

<table>
  <thead>
    <tr>
      <th>#</th>
      <th>Archivo</th>
      <th class="th--date">Fecha</th>
      <th class="th--actions">Acciones</th>
    </tr>
  <tbody>
    <?php if ($resultado && $resultado->num_rows > 0): ?>
      <?php $contador = 1; ?>
      <?php while ($fila = $resultado->fetch_assoc()): ?>
        <tr>
          <td><?= $contador++ ?></td>
          <td><?= htmlspecialchars($fila['nombre_archivo']) ?></td>
          <td class="th--date"><?= $fila['fecha_subida'] ?></td>
          <td class="td--form">
            <div class="label_container">
              <form action="index.php" method="get">
                <input type="hidden" name="file" value="<?= htmlspecialchars(basename($fila['ruta'])) ?>">
                <button class="seeBtn" id="seeBtn" type="submit">Ver</button>
                <label hidden for="seeBtn">Ver</label>
              </form>
  
              <!-- Eliminar del historial -->
              <form action="eliminar.php" method="post" onsubmit="return confirm('¿Eliminar esta imagen del historial?');">
                <input type="hidden" name="id" value="<?= $fila['id'] ?>">
                <button class="deleteBtn" id="deleteBtn" type="submit">Eliminar</button>
                <label hidden for="deleteBtn">Borrar</label>
              </form>
            </div>
            <!-- Ver imagen en el visor -->
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr>
        <td colspan="4">No hay DICOMS en el historial</td>
      </tr>
    <?php endif; ?>
  </tbody>
</table>

</body>
</html>
