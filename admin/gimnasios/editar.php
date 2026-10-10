<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/admin/admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int) $_GET['id'];
$admin_id = $_SESSION['admin_id'];

// Obtener gimnasio
$stmt = $pdo->prepare(
    "SELECT * FROM gimnasios 
     WHERE id = :id AND created_by = :admin_id"
);
$stmt->execute([
    ':id' => $id,
    ':admin_id' => $admin_id
]);

$gimnasio = $stmt->fetch();

if (!$gimnasio) {
    header('Location: index.php');
    exit;
}

// Obtener media asociada
$mediaStmt = $pdo->prepare(
    "SELECT id, media_path, media_type
     FROM gimnasio_media
     WHERE gimnasio_id = :gimnasio_id
     ORDER BY created_at ASC"
);
$mediaStmt->execute([
    ':gimnasio_id' => $id
]);

$medias = $mediaStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Editar gimnasio</title>

    <link rel="stylesheet"
          href="/bodytech/public/assets/css/admin_editar_gimnasio.css?v=1">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>

<body>

<main class="admin-edit-page">

    <!-- =====================================================
         HEADER
    ====================================================== -->
    <header class="admin-header">

        <div class="header-title">

            <div class="header-icon">
                <i class="fa-solid fa-pen-to-square"></i>
            </div>

            <div>
                <span class="header-kicker">GESTIÓN DE GIMNASIOS</span>
                <h1>Editar gimnasio</h1>
                <p>Actualiza la información y el contenido multimedia.</p>
            </div>

        </div>

        <a href="index.php" class="back-button">
            <i class="fa-solid fa-arrow-left"></i>
            Volver
        </a>

    </header>


    <!-- =====================================================
         FORMULARIO
    ====================================================== -->
    <form action="update.php"
          method="POST"
          enctype="multipart/form-data"
          class="gym-edit-form">

        <input type="hidden"
               name="id"
               value="<?= $gimnasio['id'] ?>">


        <!-- =================================================
             INFORMACIÓN DEL GIMNASIO
        ================================================== -->
        <section class="form-card">

            <div class="section-heading">

                <div class="section-icon">
                    <i class="fa-solid fa-building"></i>
                </div>

                <div>
                    <h2>Información del gimnasio</h2>
                    <p>Modifica los datos principales del establecimiento.</p>
                </div>

            </div>


            <div class="form-grid">

                <!-- Nombre -->
                <div class="form-group full-width">

                    <label for="nombre">
                        <i class="fa-solid fa-dumbbell"></i>
                        Nombre
                    </label>

                    <div class="input-wrapper">
                        <i class="fa-solid fa-dumbbell input-icon"></i>

                        <input
                            type="text"
                            id="nombre"
                            name="nombre"
                            value="<?= htmlspecialchars($gimnasio['nombre']) ?>"
                            required
                        >
                    </div>

                </div>


                <!-- Ciudad -->
                <div class="form-group">

                    <label for="ciudad">
                        <i class="fa-solid fa-location-dot"></i>
                        Ciudad
                    </label>

                    <div class="input-wrapper">
                        <i class="fa-solid fa-location-dot input-icon"></i>

                        <input
                            type="text"
                            id="ciudad"
                            name="ciudad"
                            value="<?= htmlspecialchars($gimnasio['ciudad']) ?>"
                            required
                        >
                    </div>

                </div>


                <!-- Teléfono -->
                <div class="form-group">

                    <label for="telefono">
                        <i class="fa-solid fa-phone"></i>
                        Teléfono
                    </label>

                    <div class="input-wrapper">
                        <i class="fa-solid fa-phone input-icon"></i>

                        <input
                            type="text"
                            id="telefono"
                            name="telefono"
                            value="<?= htmlspecialchars($gimnasio['telefono']) ?>"
                        >
                    </div>

                </div>


                <!-- Descripción -->
                <div class="form-group full-width">

                    <label for="descripcion">
                        <i class="fa-solid fa-align-left"></i>
                        Descripción
                    </label>

                    <div class="textarea-wrapper">
                        <textarea
                            id="descripcion"
                            name="descripcion"
                            required
                        ><?= htmlspecialchars($gimnasio['descripcion']) ?></textarea>
                    </div>

                </div>

            </div>

        </section>


        <!-- =================================================
             MEDIA ACTUAL
        ================================================== -->
        <section class="form-card">

            <div class="section-heading">

                <div class="section-icon">
                    <i class="fa-solid fa-photo-film"></i>
                </div>

                <div>
                    <h2>Media actual</h2>
                    <p>Administra las imágenes y videos asociados al gimnasio.</p>
                </div>

            </div>


            <?php if (!empty($medias)): ?>

                <div class="media-grid">

                    <?php foreach ($medias as $media): ?>

                        <article class="media-card">

                            <div class="media-preview">

                                <?php if ($media['media_type'] === 'image'): ?>

                                    <img
                                        src="/bodytech/public/uploads/gimnasios/<?= htmlspecialchars($media['media_path']) ?>"
                                        alt="Media del gimnasio"
                                    >

                                    <span class="media-type image-type">
                                        <i class="fa-solid fa-image"></i>
                                        Imagen
                                    </span>

                                <?php elseif ($media['media_type'] === 'video'): ?>

                                    <video controls>
                                        <source
                                            src="/bodytech/public/uploads/gimnasios/<?= htmlspecialchars($media['media_path']) ?>"
                                            type="video/mp4"
                                        >

                                        Tu navegador no soporta video.
                                    </video>

                                    <span class="media-type video-type">
                                        <i class="fa-solid fa-video"></i>
                                        Video
                                    </span>

                                <?php endif; ?>

                            </div>


                            <div class="media-footer">

                                <label class="delete-media">

                                    <input
                                        type="checkbox"
                                        name="eliminar_media[]"
                                        value="<?= $media['id'] ?>"
                                    >

                                    <span class="checkbox-custom">
                                        <i class="fa-solid fa-check"></i>
                                    </span>

                                    <span>
                                        Eliminar este archivo
                                    </span>

                                </label>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="empty-media">

                    <div class="empty-icon">
                        <i class="fa-regular fa-images"></i>
                    </div>

                    <h3>No hay contenido multimedia</h3>

                    <p>
                        Este gimnasio todavía no tiene imágenes o videos asociados.
                    </p>

                </div>

            <?php endif; ?>

        </section>


        <!-- =================================================
             NUEVA MEDIA
        ================================================== -->
        <section class="form-card">

            <div class="section-heading">

                <div class="section-icon">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>

                <div>
                    <h2>Agregar nueva media</h2>
                    <p>Puedes agregar una o varias imágenes o videos.</p>
                </div>

            </div>


            <label class="upload-area" for="media">

                <div class="upload-icon">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>

                <div class="upload-content">

                    <strong>Seleccionar archivos</strong>

                    <span>
                        Imágenes o videos · Puedes seleccionar varios
                    </span>

                    <small id="media-file-name">
                        Ningún archivo seleccionado
                    </small>

                </div>

                <input
                    type="file"
                    id="media"
                    name="media[]"
                    multiple
                    accept="image/*,video/*"
                >

            </label>

        </section>


        <!-- =================================================
             ACCIONES
        ================================================== -->
        <div class="form-actions">

            <a href="index.php" class="cancel-button">
                <i class="fa-solid fa-xmark"></i>
                Cancelar
            </a>

            <button type="submit" class="save-button">
                <i class="fa-solid fa-floppy-disk"></i>
                Guardar cambios
            </button>

        </div>

    </form>

</main>

<script src="/bodytech/public/assets/js/editar.js"></script>

</body>
</html>