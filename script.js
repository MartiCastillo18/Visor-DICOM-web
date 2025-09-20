const fileName = document.querySelector('.file_name');
const uploadBtn = document.querySelector('#uploadBtn');
const file_input = document.querySelector('#file_input')
const loadBtn = document.querySelector('#loadBtn');
const wrapper = document.querySelector('.wrapper')
const wrapperH3 = document.querySelector('.wrapper--h3')

const element = document.getElementById('dicomImage');
cornerstone.enable(element);


// 1. Enlazar cornerstone al loader
cornerstoneWADOImageLoader.external.cornerstone = cornerstone;

// 2. Inicializar workers
cornerstoneWADOImageLoader.webWorkerManager.initialize({
    webWorkerPath: 'https://unpkg.com/cornerstone-wado-image-loader@3.0.0/dist/cornerstoneWADOImageLoaderWebWorker.min.js',
    taskConfiguration: {
        decodeTask: {
            codecsPath: 'https://unpkg.com/cornerstone-wado-image-loader@3.0.0/dist/cornerstoneWADOImageLoaderCodecs.min.js'
        }
    }
});

file_input.addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (!file) return;

    // Este imageId debe tener prefijo "wadouri:"
    const imageId = cornerstoneWADOImageLoader.wadouri.fileManager.add(file);

    console.log("imageId:", imageId); // Asegúrate que sea wadouri://...

    cornerstone.loadImage(imageId).then(function (image) {
        cornerstone.displayImage(element, image);
        //Muestra la imagen previsualizada
        element.style.opacity = "1";
        element.style.position = "relative";
        wrapper.style.border = "none";
        wrapper.style.width = "auto";
        wrapperH3.style.display = "none";


    }).catch(function (error) {
        console.error("Error al cargar imagen:", error);
        alert("Error al cargar imagen DICOM: " + error.message);
    });

    element.classList.add("dicomImage--active");
});

const regExp = /[0-9a-zA-Z\^\&\'\@\{\}\[\]\,\$\=\!\-\#\(\)\.\%\+\~\_ ]+$/;

file_input.addEventListener("change", function () {
    const file = this.files[0];
    if (!file) return;

    // Mostrar nombre del archivo
    const nombre = file.name.match(regExp);
    fileName.textContent = nombre;
});

loadBtn.addEventListener("click", function () {
    //Regresa a la vista predefinida del selector de archivos
    element.style.opacity = 0;
    element.style.position = "absolute";
    fileName.textContent = "";
    wrapper.style.border = "";
    wrapper.style.width = "100%";
    wrapperH3.style.display = "flex";
})

// Aplica scroll suave al hacer scroll con el mouse
  window.addEventListener('load', () => {
    Scrollbar.init(document.querySelector('#contenedorHistorial'), {
      damping: 0.08
    });
  });
