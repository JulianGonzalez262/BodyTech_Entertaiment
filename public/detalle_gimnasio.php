<?php
require_once '../app/views/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

/* ================================
   VALIDAR ID
================================ */
$id = $_GET['id'] ?? null;

if (!$id) {
    die("Gimnasio no válido.");
}

/* ================================
   ESTADO DE AFILIACIÓN DEL USUARIO
================================ */

$usuarioAfiliado = null;
$estaAfiliado = false;
$estaAfiliadoOtroGym = false;

if (isset($_SESSION['usuario_id'])) {

    $stmtAfiliacion = $pdo->prepare("
        SELECT id, gym_nombre, ciudad
        FROM afiliados
        WHERE usuario_id = ?
          AND estado = 'activo'
        ORDER BY created_at DESC
        LIMIT 1
    ");

    $stmtAfiliacion->execute([
        $_SESSION['usuario_id']
    ]);

    $usuarioAfiliado = $stmtAfiliacion->fetch(PDO::FETCH_ASSOC);

}

/* ================================
   OBTENER GIMNASIO
================================ */
$stmt = $pdo->prepare("
    SELECT *
    FROM gimnasios
    WHERE id = ?
    AND activo = 1
    LIMIT 1
");

$stmt->execute([$id]);

$gym = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$gym) {
    die("Gimnasio no encontrado.");
}

/* ================================
   COMPARAR AFILIACIÓN CON EL GIMNASIO
================================ */

if ($usuarioAfiliado) {

    if (
        mb_strtolower(trim($usuarioAfiliado['gym_nombre'])) ===
        mb_strtolower(trim($gym['nombre'])) &&
        mb_strtolower(trim($usuarioAfiliado['ciudad'])) ===
        mb_strtolower(trim($gym['ciudad']))
    ) {

        $estaAfiliado = true;

    } else {

        $estaAfiliadoOtroGym = true;

    }

}

/* ================================
   MEDIA
================================ */
$mediaStmt = $pdo->prepare("
    SELECT *
    FROM gimnasio_media
    WHERE gimnasio_id = ?
");

$mediaStmt->execute([$gym['id']]);

$medias = $mediaStmt->fetchAll(PDO::FETCH_ASSOC);

/* ================================
   ESTADÍSTICAS
================================ */
$statsStmt = $pdo->prepare("
    SELECT 
        AVG(calificacion) as promedio,
        COUNT(*) as total
    FROM resenas_gimnasios
    WHERE gimnasio_id = ?
");

$statsStmt->execute([$gym['id']]);

$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

$promedio = round($stats['promedio'] ?? 0, 1);
$total = $stats['total'] ?? 0;

/* ================================
   COMENTARIOS
================================ */
$comentStmt = $pdo->prepare("
    SELECT 
        resenas_gimnasios.*,
        usuarios.nombre as usuario_nombre
    FROM resenas_gimnasios

    INNER JOIN usuarios
        ON resenas_gimnasios.usuario_id = usuarios.id

    WHERE gimnasio_id = ?

    ORDER BY resenas_gimnasios.id DESC
");

$comentStmt->execute([$gym['id']]);

$comentarios = $comentStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<head><link rel="stylesheet" href="/bodytech/public/assets/css/detalle_gimnasio.css?v=1"></head>
<main class="gym-detail-page">

    <!-- ==========================================================
         CONTENEDOR PRINCIPAL
    =========================================================== -->

    <div class="gym-detail-container">

        <!-- VOLVER -->
        <a
            href="/bodytech/public/productos.php"
            class="gym-back-link"
        >
            <i class="fa-solid fa-arrow-left"></i>
            <span>Volver</span>
        </a>


        <!-- ======================================================
             PRODUCTO / GIMNASIO PRINCIPAL
        ======================================================= -->

        <section class="gym-product-layout">


                <!-- ==========================================================
            GALERÍA DEL GIMNASIO
        ========================================================== -->

        <div class="gym-gallery">

            <?php if ($medias): ?>

                <!-- MINIATURAS -->

                <div class="gym-thumbnails">

                    <?php foreach ($medias as $index => $m): ?>

                        <?php
                            $mediaPath =
                                "/bodytech/public/uploads/gimnasios/"
                                . htmlspecialchars($m['media_path']);
                        ?>

                        <?php if ($m['media_type'] === 'image'): ?>

                            <button
                                type="button"
                                class="gym-thumbnail <?= $index === 0 ? 'active' : '' ?>"
                                data-media-index="<?= $index ?>"
                                aria-label="Ver imagen <?= $index + 1 ?>"
                            >

                                <img
                                    src="<?= $mediaPath ?>"
                                    alt="<?= htmlspecialchars($gym['nombre']) ?>"
                                >

                            </button>

                        <?php elseif ($m['media_type'] === 'video'): ?>

                            <button
                                type="button"
                                class="gym-thumbnail gym-thumbnail-video <?= $index === 0 ? 'active' : '' ?>"
                                data-media-index="<?= $index ?>"
                                aria-label="Ver video <?= $index + 1 ?>"
                            >

                                <video
                                    muted
                                    preload="metadata"
                                    playsinline
                                >

                                    <source
                                        src="<?= $mediaPath ?>"
                                        type="video/mp4"
                                    >

                                </video>

                                <span class="gym-video-badge">

                                    <i class="fa-solid fa-play"></i>

                                </span>

                            </button>

                        <?php endif; ?>

                    <?php endforeach; ?>

                </div>


                <!-- ==================================================
                    MEDIA PRINCIPAL
                ================================================== -->

                <div class="gym-main-media">

                    <?php foreach ($medias as $index => $m): ?>

                        <?php
                            $mediaPath =
                                "/bodytech/public/uploads/gimnasios/"
                                . htmlspecialchars($m['media_path']);
                        ?>

                        <?php if ($m['media_type'] === 'image'): ?>

                            <div
                                class="gym-media-item <?= $index === 0 ? 'active' : '' ?>"
                                data-media-index="<?= $index ?>"
                                data-media-type="image"
                            >

                                <button
                                    type="button"
                                    class="gym-main-image-button"
                                    aria-label="Ampliar imagen"
                                >

                                    <img
                                        src="<?= $mediaPath ?>"
                                        alt="<?= htmlspecialchars($gym['nombre']) ?>"
                                    >

                                </button>

                            </div>

                        <?php elseif ($m['media_type'] === 'video'): ?>

                            <div
                                class="gym-media-item <?= $index === 0 ? 'active' : '' ?>"
                                data-media-index="<?= $index ?>"
                                data-media-type="video"
                            >

                                <video
                                    controls
                                    playsinline
                                >

                                    <source
                                        src="<?= $mediaPath ?>"
                                        type="video/mp4"
                                    >

                                    Tu navegador no soporta videos HTML5.

                                </video>

                            </div>

                        <?php endif; ?>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="gym-no-media">

                    <i class="fa-regular fa-image"></i>

                    <span>
                        No hay imágenes disponibles.
                    </span>

                </div>

            <?php endif; ?>

        </div>


            <!-- ==================================================
                 INFORMACIÓN DEL GIMNASIO
            =================================================== -->

            <div class="gym-product-info">


                <!-- CATEGORÍA -->

                <span class="gym-category">

                    <i class="fa-solid fa-dumbbell"></i>

                    Gimnasio afiliado

                </span>


                <!-- TÍTULO -->

                <h1 class="gym-title">

                    <?= htmlspecialchars($gym['nombre']) ?>

                </h1>


                <!-- REPUTACIÓN -->

                <div class="gym-rating">

                    <div class="gym-stars">

                        <?php

                            $fullStars = floor($promedio);

                            for ($i = 1; $i <= 5; $i++) {

                                if ($i <= $fullStars) {
                                    echo '<span class="star-filled">★</span>';
                                } else {
                                    echo '<span class="star-empty">☆</span>';
                                }

                            }

                        ?>

                    </div>

                    <strong>
                        <?= $promedio ?>/5
                    </strong>

                    <span>
                        (<?= $total ?> opiniones)
                    </span>

                </div>


                <!-- UBICACIÓN -->

                <div class="gym-location-card">

                    <div class="gym-location-item">

                        <div class="gym-info-icon">

                            <i class="fa-solid fa-location-dot"></i>

                        </div>

                        <div>

                            <span>Ciudad</span>

                            <strong>
                                <?= htmlspecialchars($gym['ciudad']) ?>
                            </strong>

                        </div>

                    </div>


                    <div class="gym-location-item">

                        <div class="gym-info-icon">

                            <i class="fa-solid fa-building"></i>

                        </div>

                        <div>

                            <span>Dirección</span>

                            <strong>
                                <?= htmlspecialchars($gym['direccion']) ?>
                            </strong>

                        </div>

                    </div>

                </div>


                <!-- CTA -->
<!-- ==================================================
     ESTADO DE AFILIACIÓN
================================================== -->

                <?php if ($estaAfiliado): ?>

                    <div class="gym-affiliation-status gym-affiliation-current">

                        <i class="fa-solid fa-circle-check"></i>

                        <span>
                            Ya te encuentras afiliado a este gimnasio
                        </span>

                    </div>

                    <button
                        type="button"
                        class="gym-affiliate-button gym-affiliate-disabled"
                        disabled
                    >

                        <i class="fa-solid fa-circle-check"></i>

                        <span>
                            Ya estás afiliado a esta sede
                        </span>

                    </button>


                <?php elseif ($estaAfiliadoOtroGym): ?>

                    <div class="gym-affiliation-status gym-affiliation-warning">

                        <i class="fa-solid fa-triangle-exclamation"></i>

                        <span>
                            Ya te encuentras afiliado a un gimnasio.
                            Desafíliate de tu sede actual y vuelve a realizar
                            el proceso de membresía para pertenecer a este gimnasio.
                        </span>

                    </div>

                    <button
                        type="button"
                        class="gym-affiliate-button gym-affiliate-disabled"
                        disabled
                    >

                        <i class="fa-solid fa-lock"></i>

                        <span>
                            Afiliarme a esta sede
                        </span>

                    </button>


                <?php else: ?>

                    <a
                        href="/bodytech/public/register_afiliado.php?gym=<?= (int)$gym['id'] ?>"
                        class="gym-affiliate-button"
                    >

                        <i class="fa-solid fa-user-plus"></i>

                        <span>
                            Afiliarme a esta sede
                        </span>

                        <i class="fa-solid fa-arrow-right"></i>

                    </a>

                <?php endif; ?>


                <!-- DESCRIPCIÓN -->

                <div class="gym-description">

                    <div class="gym-section-heading">

                        <i class="fa-solid fa-circle-info"></i>

                        <h2>
                            Sobre esta sede
                        </h2>

                    </div>

                    <p>

                        <?= nl2br(htmlspecialchars($gym['descripcion'])) ?>

                    </p>

                </div>

            </div>

        </section>


        <!-- ==========================================================
             OPINIONES
        =========================================================== -->

        <section class="gym-reviews-section">


            <div class="gym-reviews-header">

                <div>

                    <span class="gym-section-label">
                        EXPERIENCIAS
                    </span>

                    <h2>
                        Opiniones de usuarios
                    </h2>

                    <p>
                        Conoce las experiencias de otros usuarios
                        en esta sede.
                    </p>

                </div>

                <div class="gym-review-summary">

                    <strong>
                        <?= $promedio ?>/5
                    </strong>

                    <div class="gym-stars">

                        <?php

                            for ($i = 1; $i <= 5; $i++) {

                                if ($i <= floor($promedio)) {
                                    echo '<span class="star-filled">★</span>';
                                } else {
                                    echo '<span class="star-empty">☆</span>';
                                }

                            }

                        ?>

                    </div>

                    <span>
                        <?= $total ?> opiniones
                    </span>

                </div>

            </div>


            <!-- ======================================================
                 FORMULARIO
            ======================================================= -->

            <?php if (isset($_SESSION['usuario_id'])): ?>

                <form
                    action="/bodytech/public/guardar_resena_gimnasio.php"
                    method="POST"
                    class="gym-review-form"
                >

                    <input
                        type="hidden"
                        name="gimnasio_id"
                        value="<?= $gym['id'] ?>"
                    >


                    <div class="gym-review-form-header">

                        <div class="gym-form-icon">

                            <i class="fa-solid fa-pen"></i>

                        </div>

                        <div>

                            <h3>
                                Comparte tu experiencia
                            </h3>

                            <p>
                                Tu opinión ayudará a otros usuarios.
                            </p>

                        </div>

                    </div>


                    <!-- ESTRELLAS -->

                    <div class="gym-rating-selector">

                        <span>
                            Calificación
                        </span>

                        <div class="gym-stars-input">

                            <input
                                type="radio"
                                name="calificacion"
                                id="gym-star5"
                                value="5"
                                required
                            >

                            <label for="gym-star5">★</label>


                            <input
                                type="radio"
                                name="calificacion"
                                id="gym-star4"
                                value="4"
                            >

                            <label for="gym-star4">★</label>


                            <input
                                type="radio"
                                name="calificacion"
                                id="gym-star3"
                                value="3"
                            >

                            <label for="gym-star3">★</label>


                            <input
                                type="radio"
                                name="calificacion"
                                id="gym-star2"
                                value="2"
                            >

                            <label for="gym-star2">★</label>


                            <input
                                type="radio"
                                name="calificacion"
                                id="gym-star1"
                                value="1"
                            >

                            <label for="gym-star1">★</label>

                        </div>

                    </div>


                    <textarea
                        name="comentario"
                        required
                        placeholder="Escribe tu opinión sobre esta sede..."
                    ></textarea>


                    <button
                        type="submit"
                        class="gym-review-submit"
                    >

                        <i class="fa-solid fa-paper-plane"></i>

                        Publicar opinión

                    </button>

                </form>

            <?php endif; ?>


            <!-- ======================================================
                 LISTA DE COMENTARIOS
            ======================================================= -->

            <div class="gym-reviews-list">

                <?php if (!$comentarios): ?>

                    <div class="gym-empty-reviews">

                        <i class="fa-regular fa-comment-dots"></i>

                        <h3>
                            Aún no hay opiniones
                        </h3>

                        <p>
                            Sé el primero en compartir tu experiencia.
                        </p>

                    </div>

                <?php endif; ?>


                <?php foreach ($comentarios as $coment): ?>

                    <article class="gym-review-card">


                        <div class="gym-review-top">

                            <div class="gym-review-user">

                                <div class="gym-user-avatar">

                                    <i class="fa-solid fa-user"></i>

                                </div>

                                <div>

                                    <strong>
                                        <?= htmlspecialchars($coment['usuario_nombre']) ?>
                                    </strong>

                                    <small>
                                        <?= date(
                                            'd/m/Y',
                                            strtotime($coment['created_at'])
                                        ) ?>
                                    </small>

                                </div>

                            </div>


                            <div class="gym-review-stars">

                                <?php

                                    for ($i = 1; $i <= 5; $i++) {

                                        if ($i <= $coment['calificacion']) {
                                            echo '<span class="star-filled">★</span>';
                                        } else {
                                            echo '<span class="star-empty">☆</span>';
                                        }

                                    }

                                ?>

                            </div>

                        </div>


                        <p class="gym-review-comment">

                            <?= nl2br(
                                htmlspecialchars($coment['comentario'])
                            ) ?>

                        </p>

                    </article>

                <?php endforeach; ?>

            </div>

        </section>

    </div>

    <!-- ==========================================================
     LIGHTBOX GALERÍA
========================================================== -->

<div class="gym-lightbox" id="gymLightbox">

    <div class="gym-lightbox-content">

        <button
            type="button"
            class="gym-lightbox-close"
            id="gymLightboxClose"
            aria-label="Cerrar"
        >
            <i class="fa-solid fa-xmark"></i>
        </button>

        <button
            type="button"
            class="gym-lightbox-prev"
            id="gymLightboxPrev"
            aria-label="Imagen anterior"
        >
            <i class="fa-solid fa-chevron-left"></i>
        </button>

        <img
            src=""
            alt="Imagen del gimnasio"
            class="gym-lightbox-image"
            id="gymLightboxImage"
        >

        <button
            type="button"
            class="gym-lightbox-next"
            id="gymLightboxNext"
            aria-label="Imagen siguiente"
        >
            <i class="fa-solid fa-chevron-right"></i>
        </button>

    </div>

</div>
    
</main>
<script src="/bodytech/public/assets/js/detalle_gyms.js"></script>
<?php require_once '../app/views/footer.php'; ?>