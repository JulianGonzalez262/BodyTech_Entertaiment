<?php
require_once __DIR__ . '/../admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    die("ID no válido");
}

// Obtener producto
$stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->execute([$id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    die("Producto no encontrado");
}

// Obtener media
$mediaStmt = $pdo->prepare("
    SELECT * FROM producto_media WHERE producto_id = ?
");
$mediaStmt->execute([$producto['id']]);
$medias = $mediaStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Editar Producto | BodyTech</title>

    <link
        rel="stylesheet"
        href="/bodytech/public/assets/css/admin_editar_producto.css?v=1"
    >

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
                <i class="fa-solid fa-pen"></i>
                Editar Producto
            </h1>

            <p>
                Modifica la información y los archivos del producto.
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
            action="actualizar_producto.php"
            method="POST"
            enctype="multipart/form-data"
        >

            <input
                type="hidden"
                name="id"
                value="<?= $producto['id'] ?>"
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

                        <p>
                            Actualiza los datos principales del producto.
                        </p>

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
                        value="<?= htmlspecialchars($producto['nombre']) ?>"
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
                    ><?= htmlspecialchars($producto['descripcion']) ?></textarea>

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
                                step="0.01"
                                id="precio"
                                name="precio"
                                value="<?= $producto['precio'] ?>"
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
                                value="<?= $producto['stock'] ?>"
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
                            value="<?= htmlspecialchars($producto['categoria']) ?>"
                            required
                        >

                    </div>

                </div>

            </div>


            <!-- ==================================================
                 MEDIA ACTUAL
            =================================================== -->

            <div class="form-section">

                <div class="section-heading">

                    <div class="section-icon">
                        <i class="fa-solid fa-photo-film"></i>
                    </div>

                    <div>

                        <h2>Media actual</h2>

                        <p>
                            Archivos multimedia asociados a este producto.
                        </p>

                    </div>

                </div>


                <?php if (count($medias) === 0): ?>

                    <div class="empty-media">

                        <i class="fa-solid fa-folder-open"></i>

                        <span>
                            No hay archivos multimedia asociados.
                        </span>

                    </div>

                <?php else: ?>

                    <div class="media-grid">

                        <?php foreach($medias as $m): ?>

                            <div class="media-card">


                                <!-- Imagen -->

                                <?php if($m['media_type'] === 'image'): ?>

                                    <div class="media-preview">

                                        <img
                                            src="/bodytech/public/uploads/productos/<?= $m['media_path'] ?>"
                                            alt="Media del producto"
                                        >

                                    </div>

                                <?php endif; ?>


                                <!-- Video -->

                                <?php if($m['media_type'] === 'video'): ?>

                                    <div class="media-preview">

                                        <video controls>

                                            <source
                                                src="/bodytech/public/uploads/productos/<?= $m['media_path'] ?>"
                                                type="video/mp4"
                                            >

                                        </video>

                                    </div>

                                <?php endif; ?>


                                <div class="media-info">

                                    <span class="media-type">

                                        <?php if($m['media_type'] === 'image'): ?>

                                            <i class="fa-solid fa-image"></i>
                                            Imagen

                                        <?php else: ?>

                                            <i class="fa-solid fa-video"></i>
                                            Video

                                        <?php endif; ?>

                                    </span>


                                    <a
                                        href="eliminar_media_producto.php?id=<?= $m['id'] ?>&producto=<?= $producto['id'] ?>"
                                        class="delete-media"
                                    >
                                        <i class="fa-solid fa-trash"></i>
                                        Eliminar
                                    </a>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </div>


            <!-- ==================================================
                 AGREGAR NUEVA MEDIA
            =================================================== -->

            <div class="form-section">

                <div class="section-heading">

                    <div class="section-icon">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>

                    <div>

                        <h2>Agregar nueva media</h2>

                        <p>
                            Puedes agregar nuevas imágenes o videos.
                        </p>

                    </div>

                </div>


                <div class="media-upload">

                    <div class="media-icon">

                        <i class="fa-solid fa-photo-film"></i>

                    </div>


                    <div class="media-content">

                        <strong>
                            Seleccionar archivos
                        </strong>

                        <span>
                            Puedes seleccionar múltiples imágenes o videos.
                        </span>

                    </div>


                    <label
                        for="media"
                        class="media-button"
                    >

                        <i class="fa-solid fa-folder-open"></i>

                        Seleccionar

                    </label>


                    <input
                        type="file"
                        id="media"
                        name="media[]"
                        multiple
                        accept="image/,video/"
                    >

                </div>

            </div>


            <!-- ==================================================
                 BOTONES
            =================================================== -->

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

                    Actualizar Producto

                </button>

            </div>

        </form>

    </div>

</div>

</body>
</html>