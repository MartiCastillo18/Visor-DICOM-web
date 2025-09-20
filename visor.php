<?php
$file = isset($_GET['file']) ? $_GET['file'] : '';
$path = 'uploads/' . $file;
$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
$isDicom = in_array($extension, ['dcm']);
$isImage = in_array($extension, ['jpg', 'jpeg', 'png']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Visor <?= $isDicom ? 'DICOM' : 'Imagen' ?></title>
  <style>
    body {
      margin: 0;
      padding: 0;
      height: 100vh;
      background-color: rgb(173, 0, 0);
      display: flex;
      justify-content: center;  
      align-items: center;      
      overflow: hidden;
    }
    #dicomImage, #imageContainer {
      width: 100%;
      height: 100%;
      background-color: #000;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    #imageElement {
      max-width: 100%;
      max-height: 100%;
      object-fit: contain;
      transition: transform 0.3s ease, filter 0.3s ease;
    }
    .controls-info {
      position: absolute;
      top: 10px;
      left: 10px;
      color: white;
      background: rgba(0,0,0,0.5);
      padding: 5px 10px;
      border-radius: 5px;
      font-size: 12px;
    }
  </style>
</head>
<body>
  <?php if ($isDicom): ?>
    <div id="dicomImage"></div>
    
    <script src="https://unpkg.com/dicom-parser@1.8.6/dist/dicomParser.min.js"></script>
    <script src="https://unpkg.com/cornerstone-core@2.4.0/dist/cornerstone.min.js"></script>
    <script src="https://unpkg.com/cornerstone-wado-image-loader@3.0.0/dist/cornerstoneWADOImageLoader.min.js"></script>
    
    <script>
      let originalViewport = null;
      let originalVOI = null;
      let imageId = null;
      const element = document.getElementById('dicomImage');
      cornerstone.enable(element);
      cornerstoneWADOImageLoader.external.cornerstone = cornerstone;

      const loadDicom = async () => {
        const response = await fetch("<?= $path ?>");
        const blob = await response.blob();
        const file = new File([blob], "<?= $file ?>");
        imageId = cornerstoneWADOImageLoader.wadouri.fileManager.add(file);

        cornerstone.loadImage(imageId).then(image => {
          cornerstone.displayImage(element, image);
          originalViewport = cornerstone.getDefaultViewportForImage(element, image);
          originalVOI = {
            windowCenter: image.windowCenter,
            windowWidth: image.windowWidth
          };
          cornerstone.setViewport(element, originalViewport);
        });
      };
      loadDicom();
    </script>

  <?php elseif ($isImage): ?>
    <!-- VISOR IMÁGENES PNG/JPG -->
    <div id="imageContainer">
      <img src="<?= $path ?>" alt="Imagen cargada" id="imageElement">
    </div>
    <div class="controls-info">Modo: Imagen (<?= strtoupper($extension) ?>)</div>
    
    <script>
      let scale = 1;
      let brightness = 0;
      let contrast = 100;
      const imageElement = document.getElementById('imageElement');
      const originalTransform = imageElement.style.transform;
      const originalFilter = imageElement.style.filter;

      function applyTransformations() {
        imageElement.style.transform = `scale(${scale})`;
        imageElement.style.filter = `brightness(${100 + brightness}%) contrast(${contrast}%)`;
      }

      function downloadImage() {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = imageElement.naturalWidth;
        canvas.height = imageElement.naturalHeight;
        
        ctx.filter = `brightness(${100 + brightness}%) contrast(${contrast}%)`;
        ctx.drawImage(imageElement, 0, 0, canvas.width, canvas.height);
        
        const link = document.createElement('a');
        link.href = canvas.toDataURL('image/jpeg', 0.95);
        link.download = 'imagen_editada.jpg';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      }
    </script>

  <?php else: ?>
    <!-- ARCHIVO NO SOPORTADO -->
    <div style="color: white; text-align: center;">
      <h2>Formato no soportado</h2>
      <p>El archivo <?= $file ?> no es compatible.</p>
    </div>
  <?php endif; ?>

  <!-- SISTEMA DE CONTROLES UNIFICADO -->
  <script>
    window.addEventListener("message", (event) => {
      const { action, value } = event.data;
      
      <?php if ($isDicom): ?>
        // CONTROLES PARA DICOM
        const viewport = cornerstone.getViewport(element);
        switch(action) {
          case "zoomIn": viewport.scale += 0.1; break;
          case "zoomOut": viewport.scale -= 0.1; break;
          case "setBrightness": viewport.voi.windowCenter = parseInt(value); break;
          case "setContrast": viewport.voi.windowWidth = parseInt(value); break;
          case "resetView":
            if (originalViewport && originalVOI) {
              const newViewport = { ...originalViewport };
              newViewport.voi = { ...originalVOI };
              cornerstone.setViewport(element, newViewport);
            }
            break;
          case "downloadJPG":
            if (!element.querySelector("canvas")) {
                window.parent.postMessage({ tipo: "error-descarga" }, "*");
                break;
            }

            const canvasOriginal = element.querySelector("canvas");
            
            // Crear canvas temporal con el mismo tamaño
            const canvas = document.createElement("canvas");
            canvas.width = canvasOriginal.width;
            canvas.height = canvasOriginal.height;

            // Dibujar el contenido del canvas original en el temporal
            const ctx = canvas.getContext("2d");
            ctx.drawImage(canvasOriginal, 0, 0);

            // Crear enlace de descarga
            canvas.toBlob(function(blob) {
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = "imagen_dicom.jpg";
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                // Liberar memoria
                URL.revokeObjectURL(link.href);
            }, "image/jpeg", 0.95);
            break;
        }
        if (action !== "downloadJPG" && action !== "resetView") {
          cornerstone.setViewport(element, viewport);
        }

      <?php elseif ($isImage): ?>
        // CONTROLES PARA IMÁGENES PNG/JPG
        switch(action) {
          case "zoomIn": scale += 0.1; break;
          case "zoomOut": scale = Math.max(0.1, scale - 0.1); break;
          case "setBrightness": brightness = parseInt(value); break;
          case "setContrast": contrast = parseInt(value); break;
          case "resetView":
            scale = 1;
            brightness = 0;
            contrast = 100;
            imageElement.style.transform = originalTransform;
            imageElement.style.filter = originalFilter;
            return;
          case "downloadJPG": downloadImage(); return;
        }
        applyTransformations();
      <?php endif; ?>
    });
  </script>
</body>
</html>