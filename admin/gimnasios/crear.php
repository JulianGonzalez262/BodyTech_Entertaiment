<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/admin/admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/sedes_oficiales.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Crear Gimnasio</title>

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >

    <!-- CSS -->
    <link
        rel="stylesheet"
        href="/bodytech/public/assets/css/admin_crear_gimnasio.css?v=1"
    >

</head>

<body>

<div class="panel">

    <!-- =========================
         HEADER
    ========================== -->

    <header class="page-header">

        <a
            href="index.php"
            class="back-button"
        >
            <i class="fa-solid fa-arrow-left"></i>
            Volver
        </a>

        <div class="page-heading">

            <div class="heading-icon">
                <i class="fa-solid fa-dumbbell"></i>
            </div>

            <div>

                <h1>
                    Nuevo gimnasio
                </h1>

                <p>
                    Registra una nueva sede de BodyTech.
                </p>

            </div>

        </div>

    </header>


    <!-- =========================
         FORMULARIO
    ========================== -->

    <main class="form-card">

        <form
            action="guardar.php"
            method="POST"
            enctype="multipart/form-data"
        >

            <!-- =========================
                 UBICACIÓN
            ========================== -->

            <section class="form-section">

                <div class="section-heading">

                    <div class="section-icon">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>

                    <div>

                        <h2>
                            Ubicación
                        </h2>

                        <p>
                            Selecciona la ubicación oficial de la sede.
                        </p>

                    </div>

                </div>


                <div class="form-row">

                    <!-- DEPARTAMENTO -->

                    <div class="form-group">

                        <label for="departamento">
                            Departamento
                        </label>

                        <div class="input-wrapper">

                            <i class="fa-solid fa-map"></i>

                            <select
                                id="departamento"
                                name="departamento"
                                required
                            >

                                <option value="">
                                    -- Seleccione un departamento --
                                </option>

                            </select>

                        </div>

                    </div>


                    <!-- CIUDAD -->

                    <div class="form-group">

                        <label for="ciudad">
                            Ciudad
                        </label>

                        <div class="input-wrapper">

                            <i class="fa-solid fa-city"></i>

                            <select
                                id="ciudad"
                                name="ciudad"
                                required
                                disabled
                            >

                                <option value="">
                                    -- Seleccione una ciudad --
                                </option>

                            </select>

                        </div>

                    </div>

                </div>


                <!-- GYM OFICIAL -->

                <div class="form-group">

                    <label for="nombre">
                        Gym oficial
                    </label>

                    <div class="input-wrapper">

                        <i class="fa-solid fa-dumbbell"></i>

                        <select
                            id="nombre"
                            name="nombre"
                            required
                            disabled
                        >

                            <option value="">
                                -- Seleccione un gym --
                            </option>

                        </select>

                    </div>

                </div>

            </section>


            <!-- =========================
                 DATOS DEL GIMNASIO
            ========================== -->

            <section class="form-section">

                <div class="section-heading">

                    <div class="section-icon">
                        <i class="fa-solid fa-building"></i>
                    </div>

                    <div>

                        <h2>
                            Información del gimnasio
                        </h2>

                        <p>
                            Agrega los datos complementarios de la sede.
                        </p>

                    </div>

                </div>


                <!-- DESCRIPCIÓN -->

                <div class="form-group">

                    <label for="descripcion">
                        Descripción
                    </label>

                    <div class="textarea-wrapper">

                        <i class="fa-regular fa-file-lines"></i>

                        <textarea
                            id="descripcion"
                            name="descripcion"
                            placeholder="Describe brevemente el gimnasio..."
                            required
                        ></textarea>

                    </div>

                </div>


                <div class="form-row">

                    <!-- DIRECCIÓN -->

                    <div class="form-group">

                        <label for="direccion">
                            Dirección
                        </label>

                        <div class="input-wrapper">

                            <i class="fa-solid fa-location-crosshairs"></i>

                            <input
                                type="text"
                                id="direccion"
                                name="direccion"
                                placeholder="Dirección del gimnasio"
                                required
                            >

                        </div>

                    </div>


                    <!-- TELÉFONO -->

                    <div class="form-group">

                        <label for="telefono">
                            Teléfono
                        </label>

                        <div class="input-wrapper">

                            <i class="fa-solid fa-phone"></i>

                            <input
                                type="text"
                                id="telefono"
                                name="telefono"
                                placeholder="Teléfono de contacto"
                            >

                        </div>

                    </div>

                </div>

            </section>


            <!-- =========================
                 MULTIMEDIA
            ========================== -->

            <section class="form-section">

                <div class="section-heading">

                    <div class="section-icon">
                        <i class="fa-solid fa-photo-film"></i>
                    </div>

                    <div>

                        <h2>
                            Multimedia
                        </h2>

                        <p>
                            Añade imágenes o videos del gimnasio.
                        </p>

                    </div>

                </div>


                <div class="media-upload">

                    <div class="upload-icon">

                        <i class="fa-solid fa-cloud-arrow-up"></i>

                    </div>

                    <div class="upload-content">

                        <h3>
                            Imágenes / Videos del gimnasio
                        </h3>

                        <p>
                            Puedes seleccionar múltiples archivos.
                        </p>

                        <label
                            for="media"
                            class="media-button"
                        >

                            <i class="fa-solid fa-folder-open"></i>

                            Seleccionar archivos

                        </label>

                        <input
                            type="file"
                            id="media"
                            name="media[]"
                            multiple
                            accept="image/*,video/*"
                        >

                        <span
                            id="file-name"
                            class="file-name"
                        >
                            Ningún archivo seleccionado
                        </span>

                    </div>

                </div>

            </section>


            <!-- =========================
                 ACTIONS
            ========================== -->

            <div class="form-actions">

                <a
                    href="index.php"
                    class="cancel-button"
                >

                    <i class="fa-solid fa-xmark"></i>

                    Cancelar

                </a>


                <button
                    type="submit"
                    class="save-button"
                >

                    <i class="fa-solid fa-floppy-disk"></i>

                    Guardar gimnasio

                </button>

            </div>

        </form>

    </main>

</div>

<script>
    const SEDES_OFICIALES =
        <?= json_encode($SEDES_OFICIALES, JSON_UNESCAPED_UNICODE) ?>;
</script>

<script src="/bodytech/public/assets/js/crear.js"></script>
 
</body>
</html>