<?php
require_once __DIR__ . '/../admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Crear Producto | BodyTech</title>

    <!-- CSS -->
    <link rel="stylesheet" href="/bodytech/public/assets/css/admin_crear_producto.css?v=1">

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >
</head>

<body>

<div class="panel">

    <!-- ======================================================
         ENCABEZADO
    ======================================================= -->

    <div class="header">

        <div class="header-title">

            <h1>
                <i class="fa-solid fa-plus"></i>
                Crear Producto
            </h1>

            <p>
                Registra un nuevo producto para el marketplace.
            </p>

        </div>


        <a href="index.php" class="back-button">
            <i class="fa-solid fa-arrow-left"></i>
            Volver a productos
        </a>

    </div>


    <!-- ======================================================
         FORMULARIO
    ======================================================= -->

    <div class="form-card">

        <form
            action="guardar_producto.php"
            method="POST"
            enctype="multipart/form-data"
        >


            <!-- ==================================================
                 INFORMACIÓN DEL PRODUCTO
            =================================================== -->

            <div class="form-section">

                <div class="section-heading">

                    <div class="section-icon">
                        <i class="fa-solid fa-box"></i>
                    </div>

                    <div>
                        <h2>Información del producto</h2>
                        <p>Completa los datos principales del producto.</p>
                    </div>

                </div>


                <!-- Nombre -->

                <div class="form-group">

                    <label for="nombre">
                        Nombre
                    </label>

                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        required
                    >

                </div>


                <!-- Descripción -->

                <div class="form-group">

                    <label for="descripcion">
                        Descripción
                    </label>

                    <textarea
                        id="descripcion"
                        name="descripcion"
                        required
                    ></textarea>

                </div>


                <!-- Precio / Stock -->

                <div class="form-row">

                    <div class="form-group">

                        <label for="precio">
                            Precio
                        </label>

                        <div class="input-wrapper">

                            <span class="input-icon">
                                <i class="fa-solid fa-dollar-sign"></i>
                            </span>

                            <input
                                type="number"
                                id="precio"
                                name="precio"
                                step="0.01"
                                required
                            >

                        </div>

                    </div>


                    <div class="form-group">

                        <label for="stock">
                            Stock
                        </label>

                        <div class="input-wrapper">

                            <span class="input-icon">
                                <i class="fa-solid fa-boxes-stacked"></i>
                            </span>

                            <input
                                type="number"
                                id="stock"
                                name="stock"
                                required
                            >

                        </div>

                    </div>

                </div>


                <!-- Categoría -->

                <div class="form-group">

                    <label for="categoria">
                        Categoría
                    </label>

                    <div class="input-wrapper">

                        <span class="input-icon">
                            <i class="fa-solid fa-tag"></i>
                        </span>

                        <input
                            type="text"
                            id="categoria"
                            name="categoria"
                            required
                        >

                    </div>

                </div>

            </div>


            <!-- ==================================================
                 MULTIMEDIA
            =================================================== -->

            <div class="form-section">

                <div class="section-heading">

                    <div class="section-icon">
                        <i class="fa-solid fa-photo-film"></i>
                    </div>

                    <div>
                        <h2>Imágenes y videos</h2>
                        <p>Agrega el contenido multimedia del producto.</p>
                    </div>

                </div>


                <div class="media-upload">

                    <div class="media-icon">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>

                    <div class="media-content">

                        <strong>
                            Seleccionar archivos
                        </strong>

                        <span>
                            Puedes seleccionar imágenes o videos.
                        </span>

                    </div>

                    <label for="media" class="media-button">
                        <i class="fa-solid fa-folder-open"></i>
                        Seleccionar
                    </label>

                    <input
                        type="file"
                        id="media"
                        name="media[]"
                        multiple
                        accept="image/*,video/*"
                    >

                </div>

            </div>


            <!-- ==================================================
                 ESTADO
            =================================================== -->

            <div class="form-section">

                <div class="section-heading">

                    <div class="section-icon">
                        <i class="fa-solid fa-toggle-on"></i>
                    </div>

                    <div>
                        <h2>Estado</h2>
                        <p>Define si el producto estará disponible.</p>
                    </div>

                </div>


                <div class="form-group">

                    <label for="activo">
                        Estado del producto
                    </label>

                    <div class="select-wrapper">

                        <i class="fa-solid fa-circle-check"></i>

                        <select
                            id="activo"
                            name="activo"
                        >

                            <option value="1">
                                Activo
                            </option>

                            <option value="0">
                                Inactivo
                            </option>

                        </select>

                    </div>

                </div>

            </div>


            <!-- ==================================================
                 BOTONES
            =================================================== -->

            <div class="form-actions">

                <a href="index.php" class="cancel-button">
                    <i class="fa-solid fa-xmark"></i>
                    Cancelar
                </a>


                <button type="submit" class="save-button">

                    <i class="fa-solid fa-floppy-disk"></i>

                    Guardar Producto

                </button>

            </div>


        </form>

    </div>

</div>

</body>
</html>