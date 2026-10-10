document.addEventListener('DOMContentLoaded', function () {

    /* =========================
       CONFIGURACIÓN DESDE PHP
    ========================= */

    const configElement = document.getElementById('afiliado-config');

    if (!configElement) {
        console.error('No se encontró la configuración de afiliación.');
        return;
    }

    const config = JSON.parse(configElement.textContent);

    const data = config.sedes;
    const gymNombre = config.gym;


    /* =========================
       ELEMENTOS DEL FORMULARIO
    ========================= */

    const departamentoSelect = document.getElementById('departamento');
    const ciudadSelect = document.getElementById('ciudad');
    const gymSelect = document.getElementById('gym_nombre');


    if (!departamentoSelect || !ciudadSelect || !gymSelect) {
        console.error('No se encontraron los campos del formulario de afiliación.');
        return;
    }


    /* =========================
       CARGAR DEPARTAMENTOS
    ========================= */

    Object.keys(data).forEach(function (departamento) {

        const option = document.createElement('option');

        option.value = departamento;
        option.textContent = departamento;

        departamentoSelect.appendChild(option);

    });


    /* =========================
       CAMBIO DEPARTAMENTO
    ========================= */

    departamentoSelect.addEventListener('change', function () {

        ciudadSelect.innerHTML =
            '<option value="">-- Seleccione una ciudad --</option>';

        gymSelect.innerHTML =
            '<option value="">-- Seleccione un gym --</option>';

        ciudadSelect.disabled = true;
        gymSelect.disabled = true;


        const ciudades = data[departamentoSelect.value];

        if (!ciudades) {
            return;
        }


        Object.keys(ciudades).forEach(function (ciudad) {

            const option = document.createElement('option');

            option.value = ciudad;
            option.textContent = ciudad;

            ciudadSelect.appendChild(option);

        });


        ciudadSelect.disabled = false;

    });


    /* =========================
       CAMBIO CIUDAD
    ========================= */

    ciudadSelect.addEventListener('change', function () {

        gymSelect.innerHTML =
            '<option value="">-- Seleccione un gym --</option>';

        gymSelect.disabled = true;


        const departamento = departamentoSelect.value;
        const ciudad = ciudadSelect.value;

        const gyms = data[departamento]?.[ciudad];

        if (!gyms) {
            return;
        }


        gyms.forEach(function (gym) {

            const option = document.createElement('option');

            option.value = gym;
            option.textContent = gym;

            gymSelect.appendChild(option);

        });


        gymSelect.disabled = false;

    });


    /* =========================
       PRESELECCIONAR GYM
    ========================= */

    if (gymNombre) {

        for (const departamento in data) {

            for (const ciudad in data[departamento]) {

                const gyms = data[departamento][ciudad];

                if (!gyms.includes(gymNombre)) {
                    continue;
                }


                /* =========================
                   SELECCIONAR DEPARTAMENTO
                ========================= */

                departamentoSelect.value = departamento;

                departamentoSelect.dispatchEvent(
                    new Event('change')
                );


                /* =========================
                   SELECCIONAR CIUDAD
                ========================= */

                ciudadSelect.value = ciudad;

                ciudadSelect.dispatchEvent(
                    new Event('change')
                );


                /* =========================
                   SELECCIONAR GYM
                ========================= */

                gymSelect.value = gymNombre;


                return;

            }

        }


        console.warn(
            'El gimnasio "' +
            gymNombre +
            '" no fue encontrado en SEDES_OFICIALES.'
        );

    }

});