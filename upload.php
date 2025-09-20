<?php
if (isset($_FILES['dicomFile'])) {
    $file = $_FILES['dicomFile'];
    $targetDir = "uploads/";
    $fileName = basename($file["name"]);
    $targetFilePath = $targetDir . $fileName;

    // Validar extensiones permitidas
    $allowedExtensions = ['dcm', 'jpg', 'jpeg', 'png'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($fileExtension, $allowedExtensions)) {
        echo "Formato no permitido. Solo DICOM, JPG o PNG.";
        exit;
    }

    // Mueve el archivo subido a la carpeta "uploads"
    if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
        require_once 'db/conexion.php';

        // --- Lógica para limitar a 15 imágenes ---
        // 1. Contar imágenes activas
        $countResult = $conexion->query("SELECT COUNT(*) as total FROM historial__dicom WHERE eliminado = 0");
        $countRow = $countResult->fetch_assoc();
        $total = $countRow['total'];

        if ($total >= 15) {
            // 2. Obtener la imagen más antigua
            $oldestResult = $conexion->query("SELECT id, ruta FROM historial__dicom WHERE eliminado = 0 ORDER BY fecha_subida ASC LIMIT 1");
            $oldestRow = $oldestResult->fetch_assoc();
            $oldestId = $oldestRow['id'];
            $oldestRuta = $oldestRow['ruta'];

            // 3. Eliminar archivo físicamente
            if (file_exists($oldestRuta)) {
                unlink($oldestRuta);
            }

            // 4. Marcar como eliminado en la DB
            $stmt = $conexion->prepare("UPDATE historial__dicom SET eliminado = 1 WHERE id = ?");
            $stmt->bind_param("i", $oldestId);
            $stmt->execute();
        }

        // --- Insertar la nueva imagen ---
        $stmt = $conexion->prepare("INSERT INTO historial__dicom (nombre_archivo, ruta, fecha_subida, eliminado) VALUES (?, ?, NOW(), 0)");
        $stmt->bind_param("ss", $file["name"], $targetFilePath);
        $stmt->execute();

        // Redirigir al visor
        echo '<script>window.location.href = "visor.php?file=' . urlencode($fileName) . '";</script>';
        exit;
    } else {
        echo "Error al subir el archivo.";
    }
} else {
    echo "No se recibió ningún archivo.";
}
?>

?>
