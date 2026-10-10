const data = SEDES_OFICIALES;

const departamentoSelect =
    document.getElementById('departamento');

const ciudadSelect =
    document.getElementById('ciudad');

const gymSelect =
    document.getElementById('nombre');

const mediaInput =
    document.getElementById('media');

const fileName =
    document.getElementById('file-name');


Object.keys(data).forEach(dep => {

    const option =
        document.createElement('option');

    option.value = dep;

    option.textContent = dep;

    departamentoSelect.appendChild(option);

});


/* ==========================================================
   DEPARTAMENTO → CIUDADES
========================================================== */

departamentoSelect.addEventListener('change', () => {

    ciudadSelect.innerHTML =
        '<option value="">-- Seleccione una ciudad --</option>';

    gymSelect.innerHTML =
        '<option value="">-- Seleccione un gym --</option>';

    ciudadSelect.disabled = true;

    gymSelect.disabled = true;


    const ciudades =
        data[departamentoSelect.value];

    if (!ciudades) {
        return;
    }


    Object.keys(ciudades).forEach(ciudad => {

        const option =
            document.createElement('option');

        option.value = ciudad;

        option.textContent = ciudad;

        ciudadSelect.appendChild(option);

    });


    ciudadSelect.disabled = false;

});


/* ==========================================================
   CIUDAD → GYMS
========================================================== */

ciudadSelect.addEventListener('change', () => {

    gymSelect.innerHTML =
        '<option value="">-- Seleccione un gym --</option>';

    gymSelect.disabled = true;


    const gyms =
        data[
            departamentoSelect.value
        ][
            ciudadSelect.value
        ];

    if (!gyms) {
        return;
    }


    gyms.forEach(gym => {

        const option =
            document.createElement('option');

        option.value = gym;

        option.textContent = gym;

        gymSelect.appendChild(option);

    });


    gymSelect.disabled = false;

});


/* ==========================================================
   MOSTRAR ARCHIVOS SELECCIONADOS
========================================================== */

mediaInput.addEventListener('change', () => {

    const files = mediaInput.files;

    if (!files.length) {

        fileName.textContent =
            'Ningún archivo seleccionado';

        return;

    }


    if (files.length === 1) {

        fileName.textContent =
            files[0].name;

        return;

    }


    fileName.textContent =
        `${files.length} archivos seleccionados`;

});