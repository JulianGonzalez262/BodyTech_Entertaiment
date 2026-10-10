<?php
require_once '../../app/views/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: /bodytech/public/login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

$stmt = $pdo->prepare("
    SELECT
        notificaciones.*,
        producto_media.media_path AS producto_imagen,
        gimnasio_media.media_path AS gimnasio_imagen
    FROM notificaciones

    LEFT JOIN producto_media
        ON producto_media.producto_id = notificaciones.referencia_id
        AND notificaciones.tipo = 'producto'
        AND producto_media.id = (
            SELECT MIN(id)
            FROM producto_media
            WHERE producto_id = notificaciones.referencia_id
        )

    LEFT JOIN gimnasio_media
        ON gimnasio_media.gimnasio_id = notificaciones.referencia_id
        AND notificaciones.tipo = 'gimnasio'
        AND gimnasio_media.id = (
            SELECT MIN(id)
            FROM gimnasio_media
            WHERE gimnasio_id = notificaciones.referencia_id
        )

    WHERE notificaciones.usuario_id = ?
    ORDER BY notificaciones.created_at DESC
");

$stmt->execute([$usuario_id]);

$notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   MARCAR COMO LEÍDAS
========================= */
$stmtLeidas = $pdo->prepare("
    UPDATE notificaciones
    SET leida = 1
    WHERE usuario_id = ?
");

$stmtLeidas->execute([$usuario_id]);
?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mis Notificaciones | BODYTECH</title>

    <link
        rel="stylesheet"
        href="/bodytech/public/assets/css/notificaciones.css?v=1"
    >

</head>

<body>

<?php require_once '../../app/views/header.php'; ?>


<main class="notifications-page">

    <section class="notifications-container">


        <!-- ======================================================
             ENCABEZADO
        ======================================================= -->

        <header class="notifications-header">

            <div class="notifications-icon">

                <i class="fa-solid fa-bell"></i>

            </div>

            <div>

                <span class="notifications-label">
                    CENTRO DE NOTIFICACIONES
                </span>

                <h1>
                    Mis Notificaciones
                </h1>

                <p>
                    Mantente al día con las novedades y avisos
                    importantes de tu cuenta.
                </p>

            </div>

        </header>


        <!-- ======================================================
             CONTENIDO
        ======================================================= -->

        <section class="notifications-list">

            <?php if(empty($notificaciones)): ?>

                <div class="notifications-empty">

                    <div class="notifications-empty-icon">

                        <i class="fa-regular fa-bell-slash"></i>

                    </div>

                    <h2>
                        No tienes notificaciones
                    </h2>

                    <p>
                        Cuando haya novedades importantes sobre tu
                        cuenta, aparecerán aquí.
                    </p>

                </div>

            <?php else: ?>


                <?php foreach($notificaciones as $n): ?>

                    <?php
                        // Determinar link y ruta de imagen según el tipo
                        $link = '#';    
                        $imagenUrl = null;

                        if (
                                ($n['tipo'] === 'producto' || $n['tipo'] === 'resena_producto')
                                && $n['referencia_id']
                            ) {

                                $link = '/bodytech/public/producto.php?id=' . $n['referencia_id'];

                                if (!empty($n['producto_imagen'])) {
                                    $imagenUrl = '/bodytech/public/uploads/productos/' . $n['producto_imagen'];
                                }

                            } elseif ($n['tipo'] === 'gimnasio' && $n['referencia_id']) {

                            $link = '/bodytech/public/detalle_gimnasio.php?id=' . $n['referencia_id'];

                            if (!empty($n['gimnasio_imagen'])) {
                                $imagenUrl = '/bodytech/public/uploads/gimnasios/' . $n['gimnasio_imagen'];
                            }

                        }
                    ?>

                    <a href="<?= htmlspecialchars($link) ?>" class="notification-card-link">

                    <article class="notification-card">


                        <div class="notification-card-icon">

                            <?php if ($imagenUrl): ?>

                                <img
                                    src="<?= htmlspecialchars($imagenUrl) ?>"
                                    alt="<?= htmlspecialchars($n['titulo']) ?>"
                                    class="notification-card-image"
                                >

                            <?php else: ?>

                                <i class="fa-solid fa-bell"></i>

                            <?php endif; ?>

                        </div>


                        <div class="notification-content">

                            <div class="notification-top">

                                <h2>
                                    <?= htmlspecialchars($n['titulo']) ?>
                                </h2>

                                <span class="notification-status">
                                    <i class="fa-solid fa-check"></i>
                                    Leída
                                </span>

                            </div>


                            <p class="notification-message">

                                <?= nl2br(
                                    htmlspecialchars($n['mensaje'])
                                ) ?>

                            </p>


                            <div class="notification-date">

                                <i class="fa-regular fa-clock"></i>

                                <span>
                                    <?= htmlspecialchars($n['created_at']) ?>
                                </span>

                            </div>

                        </div>

                    </article>

                    </a>

                <?php endforeach; ?>


            <?php endif; ?>

        </section>


    </section>

</main>


<?php require_once '../../app/views/footer.php'; ?>

</body>

</html>