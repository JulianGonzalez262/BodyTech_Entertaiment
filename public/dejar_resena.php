<?php

require_once '../app/views/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: /bodytech/public/login.php");
    exit;
}

$orden_id = $_GET['orden'] ?? null;
$producto_id = $_GET['producto'] ?? null;

if (!$orden_id || !$producto_id) {
    die("Solicitud inválida.");
}

/* =========================
   VALIDAR ORDEN
========================= */

$stmt = $pdo->prepare("
    SELECT *
    FROM ordenes
    WHERE id = ?
    AND usuario_id = ?
    AND estado_orden = 'entregado'
    LIMIT 1
");

$stmt->execute([
    $orden_id,
    $_SESSION['usuario_id']
]);

$orden = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$orden) {
    die("La orden no es válida para reseñar.");
}

/* =========================
   VALIDAR PRODUCTO EN ORDEN
========================= */

$stmt = $pdo->prepare("
    SELECT
        orden_items.*,
        productos.nombre
    FROM orden_items
    INNER JOIN productos
        ON orden_items.producto_id = productos.id
    WHERE orden_items.orden_id = ?
    AND orden_items.producto_id = ?
    LIMIT 1
");

$stmt->execute([
    $orden_id,
    $producto_id
]);

$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    die("Producto inválido.");
}

/* =========================
   VALIDAR RESEÑA DUPLICADA
========================= */

$stmt = $pdo->prepare("
    SELECT id
    FROM resenas_productos
    WHERE usuario_id = ?
    AND orden_id = ?
    AND producto_id = ?
    LIMIT 1
");

$stmt->execute([
    $_SESSION['usuario_id'],
    $orden_id,
    $producto_id
]);

$yaReseno = $stmt->fetch();

if ($yaReseno) {
    die("Ya reseñaste este producto.");
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Dejar reseña | BODYTECH</title>

    <link
        rel="stylesheet"
        href="/bodytech/public/assets/css/dejar_resena.css"
    >

</head>

<body>

<main class="resena-page">

    <div class="resena-container">

        <!-- =========================
             VOLVER
        ========================== -->

        <a
            href="/bodytech/public/ver_compra.php?id=<?= $orden_id ?>"
            class="resena-back"
        >
            <span>←</span>
            Volver a la compra
        </a>


        <!-- =========================
             ENCABEZADO
        ========================== -->

        <header class="resena-header">

            <div class="resena-header-icon">
                ⭐
            </div>

            <div>

                <span class="resena-kicker">
                    Tu experiencia
                </span>

                <h1>
                    Dejar reseña
                </h1>

                <p>
                    Comparte tu experiencia con el producto que recibiste.
                </p>

            </div>

        </header>


        <!-- =========================
             TARJETA PRINCIPAL
        ========================== -->

        <section class="resena-card">


            <!-- =========================
                 PRODUCTO
            ========================== -->

            <div class="producto-review">

                <span class="producto-review-label">
                    Producto reseñado
                </span>

                <h2>
                    <?= htmlspecialchars($producto['nombre']) ?>
                </h2>

            </div>


            <form
                action="/bodytech/public/guardar_resena.php"
                method="POST"
                enctype="multipart/form-data"
                class="resena-form"
            >

                <!-- =========================
                     DATOS OCULTOS
                ========================== -->

                <input
                    type="hidden"
                    name="orden_id"
                    value="<?= $orden_id ?>"
                >

                <input
                    type="hidden"
                    name="producto_id"
                    value="<?= $producto_id ?>"
                >


                <!-- =========================
                     CALIFICACIÓN
                ========================== -->

                <div class="form-section">

                    <div class="section-title">

                        <span class="section-number">
                            01
                        </span>

                        <div>

                            <strong>
                                Calificación
                            </strong>

                            <span>
                                ¿Qué te pareció el producto?
                            </span>

                        </div>

                    </div>


                    <div class="estrellas">

                        <input
                            type="radio"
                            name="calificacion"
                            id="star5"
                            value="5"
                            required
                        >

                        <label for="star5">
                            ★
                        </label>


                        <input
                            type="radio"
                            name="calificacion"
                            id="star4"
                            value="4"
                        >

                        <label for="star4">
                            ★
                        </label>


                        <input
                            type="radio"
                            name="calificacion"
                            id="star3"
                            value="3"
                        >

                        <label for="star3">
                            ★
                        </label>


                        <input
                            type="radio"
                            name="calificacion"
                            id="star2"
                            value="2"
                        >

                        <label for="star2">
                            ★
                        </label>


                        <input
                            type="radio"
                            name="calificacion"
                            id="star1"
                            value="1"
                        >

                        <label for="star1">
                            ★
                        </label>

                    </div>

                </div>


                <!-- =========================
                     COMENTARIO
                ========================== -->

                <div class="form-section">

                    <div class="section-title">

                        <span class="section-number">
                            02
                        </span>

                        <div>

                            <strong>
                                Tu comentario
                            </strong>

                            <span>
                                Cuéntanos cómo fue tu experiencia.
                            </span>

                        </div>

                    </div>


                    <div class="field-group">

                        <label for="comentario">
                            Comentario
                        </label>

                        <textarea
                            id="comentario"
                            name="comentario"
                            required
                            rows="6"
                            placeholder="Cuéntanos cómo fue tu experiencia..."
                        ></textarea>

                    </div>

                </div>


                <!-- =========================
                     MULTIMEDIA
                ========================== -->

                <div class="form-section">

                    <div class="section-title">

                        <span class="section-number">
                            03
                        </span>

                        <div>

                            <strong>
                                Fotos o videos
                            </strong>

                            <span>
                                Opcional
                            </span>

                        </div>

                    </div>


                    <label
                        for="media"
                        class="media-upload"
                    >

                        <div class="media-upload-icon">
                            📷
                        </div>

                        <div class="media-upload-content">

                            <strong>
                                Agregar multimedia
                            </strong>

                            <span>
                                Puedes subir imágenes o videos reales
                                del producto recibido.
                            </span>

                            <small>
                                Las reseñas con multimedia serán revisadas
                                por administración.
                            </small>

                        </div>

                        <div class="media-upload-arrow">
                            +
                        </div>

                    </label>


                    <input
                        type="file"
                        id="media"
                        name="media[]"
                        multiple
                        accept="image/*,video/mp4"
                        class="media-input"
                    >

                </div>


                <!-- =========================
                     ACCIÓN
                ========================== -->

                <div class="resena-submit">

                    <button
                        type="submit"
                        class="btn-publicar-resena"
                    >
                        <span>
                            ⭐
                        </span>

                        Publicar reseña

                    </button>

                </div>

            </form>

        </section>


        <!-- =========================
             PIE
        ========================== -->

        <div class="resena-footer-note">

            <span>🔒</span>

            <p>
                Tu reseña será publicada después de completar
                el proceso de validación correspondiente.
            </p>

        </div>

    </div>

</main>

<?php require_once '../app/views/footer.php'; ?>

</body>
</html>