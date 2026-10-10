<?php
require_once '../app/views/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

/* ================================
   1️⃣ VALIDAR ID
================================ */
$id = $_GET['id'] ?? null;

if (!$id) {
    die("Producto no válido");
}

/* ================================
   2️⃣ OBTENER PRODUCTO
================================ */
$stmt = $pdo->prepare("
    SELECT *
    FROM productos
    WHERE id = ? AND activo = 1
    LIMIT 1
");
$stmt->execute([$id]);

$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    die("Producto no encontrado o inactivo");
}

/* ================================
   3️⃣ OBTENER MEDIA
================================ */
$mediaStmt = $pdo->prepare("
    SELECT media_path, media_type
    FROM producto_media
    WHERE producto_id = ?
");
$mediaStmt->execute([$producto['id']]);

$medias = $mediaStmt->fetchAll(PDO::FETCH_ASSOC);

/* ================================
   4️⃣ ESTADÍSTICAS RESEÑAS
================================ */
$statsStmt = $pdo->prepare("
    SELECT 
        AVG(calificacion) AS promedio,
        COUNT(*) AS total_resenas
    FROM resenas_productos
    WHERE producto_id = ?
    AND resenas_productos.estado = 'aprobada'
");

$statsStmt->execute([$producto['id']]);

$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

$promedio = round($stats['promedio'] ?? 0, 1);
$totalResenas = $stats['total_resenas'] ?? 0;

/* ================================
   5️⃣ OBTENER RESEÑAS
================================ */
$resenasStmt = $pdo->prepare("
    SELECT 
        resenas_productos.*,
        usuarios.nombre AS usuario_nombre
    FROM resenas_productos

    INNER JOIN usuarios
        ON resenas_productos.usuario_id = usuarios.id

    WHERE producto_id = ?
    AND resenas_productos.estado = 'aprobada'

    ORDER BY resenas_productos.id DESC
");

$resenasStmt->execute([$producto['id']]);

$resenas = $resenasStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/bodytech/public/assets/css/producto.css">
</head>

<main class="product-page">

    <!-- ==========================================================
         FONDO
    =========================================================== -->

    <div class="product-background">
        <img
            src="/bodytech/public/assets/img/Sino.png"
            alt="Fondo BODYTECH"
        >
    </div>

    <div class="product-overlay"></div>


    <!-- ==========================================================
         CONTENIDO
    =========================================================== -->

    <div class="product-container">


        <!-- ======================================================
             MENSAJE CARRITO
        ======================================================= -->

        <?php if(isset($_GET['carrito']) && $_GET['carrito'] == 'ok'): ?>

            <div class="product-alert product-alert-success">

                <i class="fa-solid fa-circle-check"></i>

                <div>
                    <strong>Producto agregado al carrito</strong>

                    <span>
                        El producto se agregó correctamente a tu carrito.
                    </span>
                </div>

            </div>

        <?php endif; ?>


        <!-- ======================================================
             BREADCRUMB
        ======================================================= -->

        <nav class="product-breadcrumb">

            <a href="/bodytech/public/index.php">
                Inicio
            </a>

            <i class="fa-solid fa-chevron-right"></i>

            <a href="/bodytech/public/productos.php">
                Productos
            </a>

            <i class="fa-solid fa-chevron-right"></i>

            <span>
                <?= htmlspecialchars($producto['nombre']) ?>
            </span>

        </nav>


        <!-- ======================================================
             PRODUCTO PRINCIPAL
        ======================================================= -->

        <section class="product-main-card">


            <!-- ==================================================
                 GALERÍA
            =================================================== -->

            <div class="product-gallery">


                <?php if ($medias): ?>

                    <div class="product-gallery-layout">


                        <!-- ======================================
                             MINIATURAS
                        ======================================= -->

                        <div class="product-thumbnails">

                            <?php $mediaIndex = 0; ?>

                            <?php foreach ($medias as $m): ?>

                                <?php
                                    $mediaIndex++;

                                    $mediaPath =
                                        "/bodytech/public/uploads/productos/"
                                        . htmlspecialchars($m['media_path']);
                                ?>

                                <?php if ($m['media_type'] === 'image'): ?>

                                    <button
                                        type="button"
                                        class="product-thumbnail <?= $mediaIndex === 1 ? 'active' : '' ?>"
                                        data-media-target="media-<?= $mediaIndex ?>"
                                        aria-label="Ver imagen <?= $mediaIndex ?>"
                                    >

                                        <img
                                            src="<?= $mediaPath ?>"
                                            alt="<?= htmlspecialchars($producto['nombre']) ?>"
                                        >

                                    </button>

                                <?php elseif ($m['media_type'] === 'video'): ?>

                                    <button
                                        type="button"
                                        class="product-thumbnail product-thumbnail-video <?= $mediaIndex === 1 ? 'active' : '' ?>"
                                        data-media-target="media-<?= $mediaIndex ?>"
                                        aria-label="Ver video <?= $mediaIndex ?>"
                                    >

                                        <video
                                            class="product-video-thumbnail"
                                            muted
                                            preload="metadata"
                                            playsinline
                                        >
                                            <source
                                                src="<?= $mediaPath ?>"
                                                type="video/mp4"
                                            >
                                        </video>

                                        <span class="video-thumbnail-play">
                                            <i class="fa-solid fa-play"></i>
                                        </span>

                                    </button>

                                <?php endif; ?>

                            <?php endforeach; ?>

                        </div>


                        <!-- ======================================
                             MEDIA PRINCIPAL
                        ======================================= -->

                        <div class="product-main-media">

                            <?php $mediaIndex = 0; ?>

                            <?php foreach ($medias as $m): ?>

                                <?php
                                    $mediaIndex++;

                                    $mediaPath =
                                        "/bodytech/public/uploads/productos/"
                                        . htmlspecialchars($m['media_path']);
                                ?>


                                <?php if ($m['media_type'] === 'image'): ?>

                                    <div
                                        id="media-<?= $mediaIndex ?>"
                                        class="product-media-item <?= $mediaIndex === 1 ? 'active' : '' ?>"
                                    >
                                                <div class="product-image-zoom">

                                                        <img
                                                            src="<?= $mediaPath ?>"
                                                            alt="<?= htmlspecialchars($producto['nombre']) ?>"
                                                        >

                                                    </div>

                                    </div>


                                <?php elseif ($m['media_type'] === 'video'): ?>

                                    <div
                                        id="media-<?= $mediaIndex ?>"
                                        class="product-media-item <?= $mediaIndex === 1 ? 'active' : '' ?>"
                                    >

                                        <video controls>

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

                    </div>


                <?php else: ?>

                    <div class="product-no-media">

                        <i class="fa-regular fa-image"></i>

                        <span>
                            Sin imágenes disponibles.
                        </span>

                    </div>

                <?php endif; ?>


            </div>



            <!-- ==================================================
                 INFORMACIÓN DEL PRODUCTO
            =================================================== -->

            <div class="product-info">


                <!-- ==============================================
                     ESTADO
                =============================================== -->

                <div class="product-meta">

                    <span class="product-status">
                        Producto disponible
                    </span>

                    <span class="product-sold">
                        <i class="fa-solid fa-box"></i>
                        Listo para comprar
                    </span>

                </div>


                <!-- ==============================================
                     TÍTULO
                =============================================== -->

                <h1 class="product-title">

                    <?= htmlspecialchars($producto['nombre']) ?>

                </h1>


                <!-- ==============================================
                     REPUTACIÓN
                =============================================== -->

                <div class="product-rating">

                    <div class="product-stars">

                        <?php

                        $rounded = round($promedio);

                        for($i = 1; $i <= 5; $i++):

                            if($i <= $rounded):

                        ?>

                                <i class="fa-solid fa-star"></i>

                        <?php else: ?>

                                <i class="fa-regular fa-star"></i>

                        <?php

                            endif;

                        endfor;

                        ?>

                    </div>


                    <strong class="product-rating-number">
                        <?= $promedio ?>
                    </strong>


                    <a
                        href="#product-reviews"
                        class="product-review-count"
                    >
                        (<?= $totalResenas ?> reseñas)
                    </a>

                </div>


                <!-- ==============================================
                     CATEGORÍA
                =============================================== -->

                <div class="product-category">

                    <span>
                        Categoría:
                    </span>

                    <strong>
                        <?= htmlspecialchars($producto['categoria']) ?>
                    </strong>

                </div>


                <!-- ==============================================
                     PRECIO
                =============================================== -->

                <div class="product-price-box">

                    <span class="product-price-label">
                        Precio
                    </span>

                    <div class="product-price">

                        $<?= number_format($producto['precio'], 2) ?>

                        <small>COP</small>

                    </div>

                </div>


                <!-- ==============================================
                     STOCK
                =============================================== -->

                <div class="product-stock">

                    <?php if((int)$producto['stock'] > 0): ?>

                        <i class="fa-solid fa-box-open"></i>

                        <span>
                            Stock disponible:
                        </span>

                        <strong>
                            <?= $producto['stock'] ?>
                        </strong>

                    <?php else: ?>

                        <i class="fa-solid fa-circle-xmark"></i>

                        <span>
                            Producto agotado
                        </span>

                    <?php endif; ?>

                </div>

                            <!-- ==============================================
                CANTIDAD
            =============================================== -->

            <?php if ((int)$producto['stock'] > 0): ?>

                <div class="product-quantity">

                    <label for="cantidad_producto">
                        Seleccione cantidad
                    </label>

                    <select
                        id="cantidad_producto"
                        name="cantidad"
                    >

                        <?php for ($i = 1; $i <= (int)$producto['stock']; $i++): ?>

                            <option value="<?= $i ?>">
                                <?= $i ?>
                            </option>

                        <?php endfor; ?>

                    </select>

                    <small>
                        Máximo disponible: <?= (int)$producto['stock'] ?> unidad(es)
                    </small>

                </div>

            <?php endif; ?>


                <!-- ==============================================
                     DESCRIPCIÓN CORTA
                =============================================== -->

                <div class="product-short-description">

                    

                </div>


            </div>



            <!-- ==================================================
                 PANEL DE COMPRA
            =================================================== -->

            <aside class="product-purchase">


                <!-- ==============================================
                     DISPONIBILIDAD
                =============================================== -->

                <div class="purchase-status">

                    <div class="purchase-status-icon">

                        <i class="fa-solid fa-circle-check"></i>

                    </div>

                    <div>

                        <strong>
                            Producto disponible
                        </strong>

                        <span>
                            Listo para comprar
                        </span>

                    </div>

                </div>


                <!-- ==============================================
                     PRECIO PANEL
                =============================================== -->

                <div class="purchase-price">

                    <span>
                        Total
                    </span>

                    <strong>
                        $<?= number_format($producto['precio'], 2) ?>
                    </strong>

                </div>


                <!-- ==============================================
                     AGREGAR AL CARRITO
                =============================================== -->

                <form
                    action="/bodytech/public/agregar_carrito.php"
                    method="POST"
                    class="purchase-form"
                >

                    <input
                        type="hidden"
                        name="producto_id"
                        value="<?= $producto['id'] ?>"
                    >

                    <input
                        type="hidden"
                        name="cantidad"
                        id="cantidad_carrito"
                        value="1"
                    >

                    <button
                        type="submit"
                        class="product-cart-btn"
                        <?= (int)$producto['stock'] <= 0 ? 'disabled' : '' ?>
                    >

                        <i class="fa-solid fa-cart-shopping"></i>

                        Agregar al carrito

                    </button>

                </form>


                <!-- ==============================================
                     COMPRAR AHORA
                =============================================== -->

                <form
                    action="/bodytech/public/comprar_ahora.php"
                    method="POST"
                    class="purchase-form"
                >

                    <input
                        type="hidden"
                        name="producto_id"
                        value="<?= $producto['id'] ?>"
                    >

                    <input
                        type="hidden"
                        name="cantidad"
                        id="cantidad_compra"
                        value="1"
                    >

                    <button
                        type="submit"
                        class="product-buy-btn"
                        <?= (int)$producto['stock'] <= 0 ? 'disabled' : '' ?>
                    >

                        <i class="fa-solid fa-bolt"></i>

                        Comprar ahora

                    </button>

                </form>


                <!-- ==============================================
                     BENEFICIOS
                =============================================== -->

                <div class="purchase-benefits">

                    <div>

                        <i class="fa-solid fa-shield-halved"></i>

                        <span>
                            Compra segura y protegida
                        </span>

                    </div>

                    <div>

                        <i class="fa-solid fa-truck-fast"></i>

                        <span>
                            Envío disponible
                        </span>

                    </div>

                    <div>

                        <i class="fa-solid fa-circle-check"></i>

                        <span>
                            Compra respaldada
                        </span>

                    </div>

                </div>


            </aside>


        </section>



        <!-- ======================================================
             DESCRIPCIÓN COMPLETA
        ======================================================= -->

        <section class="product-description-section">

            <div class="section-heading">

                <div class="section-heading-icon">

                    <i class="fa-solid fa-circle-info"></i>

                </div>

                <div>

                    <h2>
                        Descripción del producto
                    </h2>

                    <p>
                        Conoce todos los detalles del producto.
                    </p>

                </div>

            </div>


            <div class="product-description-content">

                <?= nl2br(htmlspecialchars($producto['descripcion'])) ?>

            </div>

        </section>



        <!-- ======================================================
             RESEÑAS
        ======================================================= -->

        <section
            id="product-reviews"
            class="product-reviews-section"
        >


            <!-- ==============================================
                 HEADER RESEÑAS
            =============================================== -->

            <div class="reviews-header">

                <div>

                    <span class="reviews-overline">
                        EXPERIENCIAS DE CLIENTES
                    </span>

                    <h2>
                        Opiniones del producto
                    </h2>

                    <p>
                        Experiencias y valoraciones de otros clientes.
                    </p>

                </div>


                <div class="reviews-summary">

                    <strong>
                        <?= $promedio ?>
                    </strong>

                    <div>

                        <div class="reviews-summary-stars">

                            <?php

                            $rounded = round($promedio);

                            for($i = 1; $i <= 5; $i++):

                                if($i <= $rounded):

                            ?>

                                    <i class="fa-solid fa-star"></i>

                            <?php else: ?>

                                    <i class="fa-regular fa-star"></i>

                            <?php

                                endif;

                            endfor;

                            ?>

                        </div>

                        <span>
                            <?= $totalResenas ?> reseñas
                        </span>

                    </div>

                </div>

            </div>



            <!-- ==============================================
                 SIN RESEÑAS
            =============================================== -->

            <?php if(!$resenas): ?>

                <div class="reviews-empty">

                    <div class="reviews-empty-icon">

                        <i class="fa-regular fa-comment-dots"></i>

                    </div>

                    <h3>
                        Aún no hay reseñas
                    </h3>

                    <p>
                        Este producto todavía no tiene reseñas aprobadas.
                    </p>

                </div>

            <?php endif; ?>



            <!-- ==============================================
                 LISTA RESEÑAS
            =============================================== -->

            <div class="reviews-list">

                <?php foreach($resenas as $resena): ?>


                    <?php

                    /* =========================
                       MEDIA RESEÑA
                    ========================== */

                    $mediaStmt = $pdo->prepare("
                        SELECT *
                        FROM resenas_media
                        WHERE resena_id = ?
                    ");

                    $mediaStmt->execute([$resena['id']]);

                    $mediasResena = $mediaStmt->fetchAll(PDO::FETCH_ASSOC);

                    ?>


                    <article class="review-card">


                        <!-- ==================================
                             USUARIO
                        =================================== -->

                        <div class="review-top">


                            <div class="review-user">

                                <div class="review-avatar">

                                    <i class="fa-solid fa-user"></i>

                                </div>


                                <div>

                                    <strong>
                                        <?= htmlspecialchars($resena['usuario_nombre']) ?>
                                    </strong>

                                    <span>
                                        <?= htmlspecialchars($resena['created_at']) ?>
                                    </span>

                                </div>

                            </div>


                            <!-- ==================================
                                 ESTRELLAS
                            =================================== -->

                            <div class="review-stars">

                                <?php

                                for($i = 1; $i <= 5; $i++):

                                    if($i <= $resena['calificacion']):

                                ?>

                                        <i class="fa-solid fa-star"></i>

                                <?php else: ?>

                                        <i class="fa-regular fa-star"></i>

                                <?php

                                    endif;

                                endfor;

                                ?>

                            </div>


                        </div>



                        <!-- ==================================
                             COMENTARIO
                        =================================== -->

                        <div class="review-comment">

                            <?= nl2br(htmlspecialchars($resena['comentario'])) ?>

                        </div>



                        <!-- ==================================
                             MEDIA RESEÑA
                        =================================== -->

                        <?php if($mediasResena): ?>

                            <div class="review-media-grid">


                                <?php foreach($mediasResena as $media): ?>


                                    <?php if($media['media_type'] === 'image'): ?>

                                        <a
                                            href="/bodytech/public/uploads/resenas/<?= htmlspecialchars($media['media_path']) ?>"
                                            target="_blank"
                                            class="review-media-item"
                                        >

                                            <img
                                                src="/bodytech/public/uploads/resenas/<?= htmlspecialchars($media['media_path']) ?>"
                                                alt="Imagen de reseña"
                                            >

                                        </a>


                                    <?php elseif($media['media_type'] === 'video'): ?>

                                        <div class="review-video">

                                            <video controls>

                                                <source
                                                    src="/bodytech/public/uploads/resenas/<?= htmlspecialchars($media['media_path']) ?>"
                                                    type="video/mp4"
                                                >

                                                Tu navegador no soporta videos HTML5.

                                            </video>

                                        </div>

                                    <?php endif; ?>


                                <?php endforeach; ?>


                            </div>

                        <?php endif; ?>


                    </article>


                <?php endforeach; ?>

            </div>


        </section>



        <!-- ======================================================
             VOLVER
        ======================================================= -->

        <div class="product-back">

            <a href="/bodytech/public/productos.php">

                <i class="fa-solid fa-arrow-left"></i>

                Volver a productos

            </a>

        </div>


    </div>

</main>

<script src="/bodytech/public/assets/js/productoo.js"></script>


<?php require_once '../app/views/footer.php'; ?>