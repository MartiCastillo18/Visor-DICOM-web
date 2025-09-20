<?php
    require_once 'db/conexion.php';

    $sql = "SELECT * FROM historial__dicom WHERE eliminado = 0 ORDER BY fecha_subida DESC LIMIT 15";
    $resultado = $conexion->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Subir imagen DICOM</title>
  <script src="https://unpkg.com/cornerstone-core@2.3.0/dist/cornerstone.min.js"></script>
  <script src="https://unpkg.com/dicom-parser@1.8.7/dist/dicomParser.min.js"></script>
  <script src="https://unpkg.com/cornerstone-wado-image-loader@3.0.0/dist/cornerstoneWADOImageLoader.min.js"></script>

  <link rel="stylesheet" href="style.css">

</head>

<body>
  <header>
    <div class="header_content">
      <div class="header_content--name">
        <h2>DICOM RX-UT</h2>
      </div>
      <div class="header_content--items">
        
      </div>
      <div class="logo_container">
        <div class="logo"><img src="sources/logo-utm.png" alt="Logo - UTM"></div>
      </div>
    </div>
  </header>

  <div class="interface">
    <div class="file_selector">
      <form action="upload.php" method="POST" enctype="multipart/form-data" target="visorFrame">
        <input type="file" id="file_input" name="dicomFile" accept=".dcm,.jpg,.jpeg,.png" required />
        <button type="submit" id="file_button"></button>
        
        <label class="wrapper" for="file_input">
          <div id="dicomImage"></div><h3 class="wrapper--h3">Sube un archivo</h3>
          <div class="file_name"></div>
        </label>
          <label onclick="cargarHistorial()" for="file_button" id="loadBtn">Cargar DICOM</label>
      </form>
    </div>

    <div class="historial_container" id="historialContainer">
        <div id="historialComprimido">
          <h3>Historial rápido </h3>
          <div id="contenedorHistorial"></div>
        </div>

      <button class="historial_container--button" onclick="window.open('historial.php', '_blank')">Ver historial</button>
    </div>
  
    <div class="visor_box">
      <div class="visor">
        <div class="visor--buttonBox">
          <div class="buttonBox--zoomBtnBox">
            <button id="zoomInBtn" onclick="sendCommandToVisor('zoomIn')"> + </button>
            <button id="zoomOutBtn" onclick="sendCommandToVisor('zoomOut')"> - </button>
          </div>
          <button id="downloadBtn" onclick="descargarJPG()"><img src="sources/icon-download.png"  width="30px"  alt="Descargar a JPG" title="Descargar a JPG"></button>
        </div>
          <iframe name="visorFrame" id="visorFrame" src="" title="Visor DICOM">
          </iframe>

      </div>
      <div class="global_controls">
        <div class="global_controls--input_group contrast_group">
          <div>Brillo:</div> 
          <input class="slider brightness" id="brilloSlider" type="range" min="-500" max="500" value="0" oninput="sendCommandToVisor('setBrightness', this.value)">
        </div>

        <div class="global_controls--input_group brightness_group">
          <div>Contraste:</div> 
          <input class="slider constrast" id="contrasteSlider" type="range" min="1" max="1000" value="400" oninput="sendCommandToVisor('setContrast', this.value)">
        </div>

        <button class="reset" onclick="sendCommandToVisor('resetView')">Restablecer</button>
      </div>
    </div>
  </div>

<!-- ----------------------SCRIPT --------------------- -->

  <script>
    function sendCommandToVisor(action, value = null) {
      const iframe = document.getElementById("visorFrame");
      iframe.contentWindow.postMessage({ action, value }, "*");

      //Reinicia los sliders de los controles
      if (action === "resetView") {
        document.getElementById("brilloSlider").value = 0;
        document.getElementById("contrasteSlider").value = 400;
      }
    }

    // Carga el DICOM del historial al visor
    window.onload = function () {
    const params = new URLSearchParams(window.location.search);
    const archivo = params.get("file");

    if (archivo) {
      const visor = document.getElementById("visorFrame");
      if (visor) {
        visor.src = "visor.php?file=" + encodeURIComponent(archivo);
      }
    }
    };

    // DESCARGAR JPG
      function descargarJPG() {
      const visor = document.getElementById("visorFrame");
      if (visor && visor.contentWindow) {
        visor.contentWindow.postMessage({ action: "downloadJPG" }, "*");
      }
    }
    // Escuchar respuesta desde visor
    window.addEventListener("message", function(event) {
      if (event.data.tipo === "error-descarga") {
        alert("No hay imagen cargada en el visor para descargar.");
      }
    });

    // Cargar historial en index
    
    function cargarHistorial() {
        fetch("historial_comprimido.php")
        .then(res => res.text())
        .then(html => {
        document.getElementById("contenedorHistorial").innerHTML = html;
      });
    }

    document.getElementById("loadBtn").addEventListener("click", function() {
  setTimeout(() => {
    cargarHistorial();
  }, 100);
});

    // Ver imagen del historial en el visor
    function visualizarDesdeHistorial(ruta) {
      const visor = document.getElementById("visorFrame");
      visor.src = "visor.php?file=" + encodeURIComponent(ruta);
    }

    // Eliminar imagen del historial (con actualización en tiempo real)
    function eliminarDesdeHistorial(id) {
      if (!confirm("¿Eliminar esta imagen del historial?")) return;

      fetch("eliminar.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "id=" + encodeURIComponent(id)
      })
      .then(res => res.text())
      .then(() => {
        cargarHistorial(); // recargar la tabla después de eliminar
      });
    }

    // Cargar historial automáticamente al inicio
    window.addEventListener("DOMContentLoaded", cargarHistorial);
    
  </script>
  <script src="http://localhost/visor_web/script.js"></script>


</body>
</html>
