<?php
require_once __DIR__ . '/../admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

/* =========================
   VALIDAR PRODUCTO
========================= */
$id = $_GET['id'] ?? null;

if(!$id){
    die("Producto inválido.");
}

/* =========================
   PRODUCTO
========================= */
$stmt = $pdo->prepare("
    SELECT *
    FROM productos
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$producto){
    die("Producto no encontrado.");
}

/* =========================
   RESEÑAS
========================= */
$resenasStmt = $pdo->prepare("
    SELECT
        resenas_productos.*,
        usuarios.nombre AS usuario_nombre
    FROM resenas_productos

    INNER JOIN usuarios
        ON resenas_productos.usuario_id = usuarios.id

    WHERE resenas_productos.producto_id = ?

    ORDER BY resenas_productos.id DESC
");

$resenasStmt->execute([$id]);

$resenas = $resenasStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Monitorear Producto</title>

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >

    <!-- CSS -->
    <link
        rel="stylesheet"
        href="/bodytech/public/assets/css/admin_monitorear_producto.css?v=1"
    >

</head>

<body>

<div class="panel">

    <!-- =========================
         HEADER
    ========================== -->

    <header class="page-header">

        <div class="header-left">

            <a
                href="index.php"
                class="back-button"
            >
                <i class="fa-solid fa-arrow-left"></i>
                Volver
            </a>

            <div class="page-heading">

                <div class="heading-icon">
                    <i class="fa-solid fa-eye"></i>
                </div>

                <div>

                    <h1>
                        Monitorear producto
                    </h1>

                    <p>
                        <?= htmlspecialchars($producto['nombre']) ?>
                    </p>

                </div>

            </div>

        </div>

    </header>


    <!-- =========================
         INTRO
    ========================== -->

    <section class="monitor-intro">

        <div class="intro-icon">
            <i class="fa-solid fa-comments"></i>
        </div>

        <div>

            <h2>
                Reseñas del producto
            </h2>

            <p>
                Revisa, aprueba o elimina las reseñas
                asociadas a este producto.
            </p>

        </div>

    </section>


    <!-- =========================
         RESEÑAS
    ========================== -->

    <section class="reviews-section">

        <?php if(!$resenas): ?>

            <div class="empty-state">

                <div class="empty-icon">
                    <i class="fa-regular fa-comment-dots"></i>
                </div>

                <h2>
                    No hay reseñas todavía
                </h2>

                <p>
                    Este producto todavía no tiene reseñas registradas.
                </p>

            </div>

        <?php endif; ?>


        <?php foreach($resenas as $r): ?>

            <?php
            /* =========================
               MULTIMEDIA
            ========================== */

            $mediaStmt = $pdo->prepare("
                SELECT *
                FROM resenas_media
                WHERE resena_id = ?
            ");

            $mediaStmt->execute([$r['id']]);

            $medias = $mediaStmt->fetchAll(PDO::FETCH_ASSOC);
            ?>


            <article class="review-card">

                <!-- =========================
                     REVIEW HEADER
                ========================== -->

                <div class="review-header">

                    <div class="review-user">

                        <div class="user-icon">
                            <i class="fa-solid fa-user"></i>
                        </div>

                        <div>

                            <h3>
                                <?= htmlspecialchars($r['usuario_nombre']) ?>
                            </h3>

                            <p>
                                <i class="fa-regular fa-calendar"></i>
                                <?= $r['created_at'] ?>
                            </p>

                        </div>

                    </div>


                    <!-- CALIFICACIÓN -->

                    <div class="rating">

                        <?php
                        for($i=1; $i<=5; $i++){

                            if($i <= $r['calificacion']){

                                echo '<i class="fa-solid fa-star"></i>';

                            }else{

                                echo '<i class="fa-regular fa-star"></i>';

                            }

                        }
                        ?>

                    </div>

                </div>


                <!-- =========================
                     COMENTARIO
                ========================== -->

                <div class="review-comment">

                    <div class="comment-label">

                        <i class="fa-regular fa-message"></i>

                        <span>
                            Comentario
                        </span>

                    </div>

                    <p>
                        <?= nl2br(htmlspecialchars($r['comentario'])) ?>
                    </p>

                </div>


                <!-- =========================
                     ESTADO
                ========================== -->

                <div class="review-status">

                    <span class="status-label">
                        Estado
                    </span>

                    <?php if($r['estado'] === 'pendiente'): ?>

                        <span class="status-badge pending">

                            <i class="fa-solid fa-clock"></i>

                            Pendiente

                        </span>

                    <?php else: ?>

                        <span class="status-badge approved">

                            <i class="fa-solid fa-circle-check"></i>

                            Aprobado

                        </span>

                    <?php endif; ?>

                </div>


                <!-- =========================
                     MULTIMEDIA
                ========================== -->

                <?php if($medias): ?>

                    <div class="media-section">

                        <div class="media-heading">

                            <i class="fa-solid fa-photo-film"></i>

                            <span>
                                Multimedia adjunta
                            </span>

                        </div>


                        <div class="media-grid">

                            <?php foreach($medias as $m): ?>

                                <?php if($m['media_type'] === 'image'): ?>

                                    <div class="media-card">

                                        <div class="media-preview">

                                            <img
                                                src="/bodytech/public/uploads/resenas/<?= htmlspecialchars($m['media_path']) ?>"
                                                alt="Imagen de la reseña"
                                            >

                                        </div>

                                        <div class="media-type">

                                            <i class="fa-regular fa-image"></i>

                                            Imagen

                                        </div>

                                    </div>

                                <?php endif; ?>


                                <?php if($m['media_type'] === 'video'): ?>

                                    <div class="media-card">

                                        <div class="media-preview">

                                            <video controls>

                                                <source
                                                    src="/bodytech/public/uploads/resenas/<?= htmlspecialchars($m['media_path']) ?>"
                                                    type="video/mp4"
                                                >

                                            </video>

                                        </div>

                                        <div class="media-type">

                                            <i class="fa-solid fa-video"></i>

                                            Video

                                        </div>

                                    </div>

                                <?php endif; ?>

                            <?php endforeach; ?>

                        </div>

                    </div>

                <?php endif; ?>


                <!-- =========================
                     ACCIONES
                ========================== -->

                <div class="review-actions">

                    <?php if($r['estado'] === 'pendiente'): ?>

                        <a
                            href="aprobar_resena.php?id=<?= $r['id'] ?>&producto=<?= $producto['id'] ?>"
                            class="action-button approve-button"
                        >

                            <i class="fa-solid fa-check"></i>

                            Aprobar reseña

                        </a>

                    <?php endif; ?>


                    <a
                        href="eliminar_resena.php?id=<?= $r['id'] ?>&producto=<?= $producto['id'] ?>"
                        class="action-button delete-button"
                        onclick="return confirm('¿Eliminar reseña definitivamente?');"
                    >

                        <i class="fa-solid fa-trash"></i>

                        Eliminar

                    </a>

                </div>

            </article>

        <?php endforeach; ?>

    </section>

</div>

</body>
</html>