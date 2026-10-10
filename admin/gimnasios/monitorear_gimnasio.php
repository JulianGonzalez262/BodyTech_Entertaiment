<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/admin/admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

/* =========================
   VALIDAR GIMNASIO
========================= */
$id = $_GET['id'] ?? null;

if (!$id) {
    die("Gimnasio inválido.");
}

/* =========================
   OBTENER GIMNASIO
========================= */
$stmt = $pdo->prepare("
    SELECT *
    FROM gimnasios
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$gimnasio = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$gimnasio) {
    die("Gimnasio no encontrado.");
}

/* =========================
   OBTENER RESEÑAS
========================= */
$resenasStmt = $pdo->prepare("
    SELECT
        rg.*,
        u.nombre AS usuario_nombre
    FROM resenas_gimnasios rg

    INNER JOIN usuarios u
        ON rg.usuario_id = u.id

    WHERE rg.gimnasio_id = ?

    ORDER BY rg.id DESC
");

$resenasStmt->execute([$id]);

$resenas = $resenasStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Monitorear gimnasio</title>

    <link rel="stylesheet"
          href="/bodytech/public/assets/css/admin_monitorear_gimnasio.css?v=1">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>

<main class="admin-monitor-page">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <header class="monitor-header">

        <div class="header-title">

            <div class="header-icon">
                <i class="fa-solid fa-eye"></i>
            </div>

            <div>

                <span class="header-kicker">
                    MONITOREO DE GIMNASIO
                </span>

                <h1>
                    <?= htmlspecialchars($gimnasio['nombre']) ?>
                </h1>

                <p>
                    Comentarios y calificaciones realizadas por los usuarios.
                </p>

            </div>

        </div>


        <a href="index.php" class="back-button">

            <i class="fa-solid fa-arrow-left"></i>

            Volver

        </a>

    </header>


    <!-- =====================================================
         RESUMEN
    ====================================================== -->

    <section class="monitor-summary">

        <div class="summary-icon">

            <i class="fa-solid fa-comments"></i>

        </div>

        <div>

            <span class="summary-label">
                OPINIONES REGISTRADAS
            </span>

            <strong>
                <?= count($resenas) ?>
            </strong>

        </div>

    </section>


    <!-- =====================================================
         SECCIÓN DE RESEÑAS
    ====================================================== -->

    <section class="reviews-section">

        <div class="section-heading">

            <div class="section-icon">

                <i class="fa-solid fa-star"></i>

            </div>

            <div>

                <h2>
                    Opiniones de usuarios
                </h2>

                <p>
                    Revisa las experiencias compartidas sobre este gimnasio.
                </p>

            </div>

        </div>


        <?php if (!$resenas): ?>

            <!-- =================================================
                 ESTADO VACÍO
            ================================================== -->

            <div class="empty-reviews">

                <div class="empty-icon">

                    <i class="fa-regular fa-comment-dots"></i>

                </div>

                <h3>
                    No existen opiniones todavía
                </h3>

                <p>
                    Este gimnasio aún no cuenta con comentarios o
                    calificaciones de usuarios.
                </p>

            </div>


        <?php else: ?>


            <!-- =================================================
                 LISTA DE RESEÑAS
            ================================================== -->

            <div class="reviews-list">

                <?php foreach ($resenas as $r): ?>

                    <article class="review-card">


                        <!-- =====================================
                             CABECERA RESEÑA
                        ====================================== -->

                        <div class="review-header">

                            <div class="user-info">

                                <div class="user-avatar">

                                    <i class="fa-solid fa-user"></i>

                                </div>

                                <div>

                                    <h3>
                                        <?= htmlspecialchars($r['usuario_nombre']) ?>
                                    </h3>

                                    <span class="review-date">

                                        <i class="fa-regular fa-calendar"></i>

                                        <?= htmlspecialchars($r['created_at']) ?>

                                    </span>

                                </div>

                            </div>


                            <!-- =================================
                                 CALIFICACIÓN
                            ================================== -->

                            <div class="rating">

                                <?php
                                for ($i = 1; $i <= 5; $i++):
                                ?>

                                    <?php if ($i <= $r['calificacion']): ?>

                                        <i class="fa-solid fa-star"></i>

                                    <?php else: ?>

                                        <i class="fa-regular fa-star"></i>

                                    <?php endif; ?>

                                <?php endfor; ?>

                                <span class="rating-number">
                                    <?= (int) $r['calificacion'] ?>/5
                                </span>

                            </div>

                        </div>


                        <!-- =====================================
                             COMENTARIO
                        ====================================== -->

                        <div class="review-comment">

                            <div class="comment-icon">

                                <i class="fa-solid fa-quote-left"></i>

                            </div>

                            <p>
                                <?= nl2br(htmlspecialchars($r['comentario'])) ?>
                            </p>

                        </div>


                        <!-- =====================================
                             ACCIONES
                        ====================================== -->

                        <div class="review-actions">

                            <a
                                href="eliminar_resena_gimnasio.php?id=<?= $r['id'] ?>&gimnasio=<?= $gimnasio['id'] ?>"
                                class="delete-button"
                                onclick="return confirm('¿Eliminar esta opinión definitivamente?');"
                            >

                                <i class="fa-solid fa-trash"></i>

                                Eliminar opinión

                            </a>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </section>


    <!-- =====================================================
         FOOTER
    ====================================================== -->

    <footer class="monitor-footer">

        <a href="index.php">

            <i class="fa-solid fa-arrow-left"></i>

            Volver a gimnasios

        </a>

    </footer>


</main>

</body>
</html>