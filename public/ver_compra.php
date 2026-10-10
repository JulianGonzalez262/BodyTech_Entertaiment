<?php
require_once '../app/views/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: /bodytech/public/login.php");
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    die("Compra inválida.");
}


/* ==========================================================
   ORDEN
========================================================== */

$stmt = $pdo->prepare("
    SELECT *
    FROM ordenes
    WHERE id = ?
    AND usuario_id = ?
    LIMIT 1
");

$stmt->execute([
    $id,
    $_SESSION['usuario_id']
]);

$orden = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$orden) {
    die("Compra no encontrada.");
}


/* ==========================================================
   ITEMS
========================================================== */

$stmt = $pdo->prepare("
    SELECT orden_items.*, productos.nombre
    FROM orden_items

    INNER JOIN productos
        ON orden_items.producto_id = productos.id

    WHERE orden_items.orden_id = ?
");

$stmt->execute([$id]);

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* ==========================================================
   VALIDAR RESEÑAS
========================================================== */

$reseñasUsuario = [];

foreach ($items as $it) {

    $rStmt = $pdo->prepare("
        SELECT id
        FROM resenas_productos

        WHERE usuario_id = ?
        AND orden_id = ?
        AND producto_id = ?

        LIMIT 1
    ");

    $rStmt->execute([
        $_SESSION['usuario_id'],
        $orden['id'],
        $it['producto_id']
    ]);

    $reseñasUsuario[$it['producto_id']] =
        $rStmt->fetch();
}
?>


<link
    rel="stylesheet"
    href="/bodytech/public/assets/css/ver_compra.css?v=1"
>


<main class="purchase-detail-page">

    <section class="purchase-detail-container">


        <!-- ======================================================
             VOLVER
        ======================================================= -->

        <a
            href="/bodytech/public/mis_compras.php"
            class="purchase-detail-back"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Volver a mis compras

        </a>


        <!-- ======================================================
             ENCABEZADO
        ======================================================= -->

        <header class="purchase-detail-header">

            <div class="purchase-detail-header-icon">

                <i class="fa-solid fa-receipt"></i>

            </div>


            <div>

                <span class="purchase-detail-label">
                    MARKETPLACE FITNESS
                </span>

                <h1>
                    Detalle de Compra
                </h1>

                <p>
                    Consulta toda la información relacionada
                    con tu pedido.
                </p>

            </div>

        </header>


        <!-- ======================================================
             RESUMEN DE LA ORDEN
        ======================================================= -->

        <section class="purchase-detail-summary">


            <div class="purchase-summary-header">

                <div>

                    <span>
                        REFERENCIA DE ORDEN
                    </span>

                    <h2>
                        <?= htmlspecialchars(
                            $orden['referencia']
                        ) ?>
                    </h2>

                </div>


                <div class="
                    purchase-detail-status
                    purchase-detail-status-<?= htmlspecialchars(
                        $orden['estado_orden']
                    ) ?>
                ">

                    <?= strtoupper(
                        htmlspecialchars(
                            $orden['estado_orden']
                        )
                    ) ?>

                </div>

            </div>


            <div class="purchase-detail-info-grid">


                <div class="purchase-detail-info-item">

                    <i class="fa-solid fa-credit-card"></i>

                    <div>

                        <span>
                            Método de pago
                        </span>

                        <strong>
                            <?= strtoupper(
                                htmlspecialchars(
                                    $orden['metodo_pago']
                                )
                            ) ?>
                        </strong>

                    </div>

                </div>


                <div class="purchase-detail-info-item">

                    <i class="fa-solid fa-circle-check"></i>

                    <div>

                        <span>
                            Estado del pago
                        </span>

                        <strong>
                            <?= strtoupper(
                                htmlspecialchars(
                                    $orden['estado_pago']
                                )
                            ) ?>
                        </strong>

                    </div>

                </div>


                <div class="purchase-detail-info-item">

                    <i class="fa-solid fa-wallet"></i>

                    <div>

                        <span>
                            Total
                        </span>

                        <strong>
                            $<?= number_format(
                                $orden['total'],
                                2
                            ) ?>
                        </strong>

                    </div>

                </div>


                <div class="purchase-detail-info-item">

                    <i class="fa-regular fa-calendar"></i>

                    <div>

                        <span>
                            Fecha
                        </span>

                        <strong>
                            <?= htmlspecialchars(
                                $orden['created_at']
                            ) ?>
                        </strong>

                    </div>

                </div>


            </div>

        </section>


        <!-- ======================================================
             PRODUCTOS
        ======================================================= -->

        <section class="purchase-detail-products">


            <div class="purchase-detail-section-header">

                <div>

                    <span>
                        RESUMEN
                    </span>

                    <h2>
                        Productos comprados
                    </h2>

                </div>


                <div class="purchase-products-count">

                    <i class="fa-solid fa-box"></i>

                    <?= count($items) ?>

                    <?= count($items) === 1
                        ? 'producto'
                        : 'productos'
                    ?>

                </div>

            </div>


            <div class="purchase-items-list">


                <?php foreach ($items as $it): ?>


                    <?php

                    /* ==================================================
                       IMAGEN
                    ================================================== */

                    $imgStmt = $pdo->prepare("
                        SELECT media_path
                        FROM producto_media

                        WHERE producto_id = ?
                        AND media_type = 'image'

                        LIMIT 1
                    ");

                    $imgStmt->execute([
                        $it['producto_id']
                    ]);

                    $img = $imgStmt->fetchColumn();

                    ?>


                    <article class="purchase-detail-item">


                        <!-- IMAGEN -->

                        <div class="purchase-detail-item-image">

                            <?php if ($img): ?>

                                <img
                                    src="/bodytech/public/uploads/productos/<?= htmlspecialchars($img) ?>"
                                    alt="<?= htmlspecialchars(
                                        $it['nombre']
                                    ) ?>"
                                >

                            <?php else: ?>

                                <div class="purchase-detail-no-image">

                                    <i class="fa-regular fa-image"></i>

                                </div>

                            <?php endif; ?>

                        </div>


                        <!-- INFORMACIÓN -->

                        <div class="purchase-detail-item-info">


                            <div class="purchase-detail-item-main">

                                <span>
                                    PRODUCTO
                                </span>

                                <h3>
                                    <?= htmlspecialchars(
                                        $it['nombre']
                                    ) ?>
                                </h3>

                            </div>


                            <div class="purchase-detail-pricing">


                                <div>

                                    <span>
                                        Cantidad
                                    </span>

                                    <strong>
                                        <?= $it['cantidad'] ?>
                                    </strong>

                                </div>


                                <div>

                                    <span>
                                        Precio unitario
                                    </span>

                                    <strong>
                                        $<?= number_format(
                                            $it['precio_unitario'],
                                            2
                                        ) ?>
                                    </strong>

                                </div>


                                <div class="purchase-detail-subtotal">

                                    <span>
                                        Subtotal
                                    </span>

                                    <strong>
                                        $<?= number_format(
                                            $it['subtotal'],
                                            2
                                        ) ?>
                                    </strong>

                                </div>


                            </div>


                            <!-- ==================================================
                                 RESEÑA
                            =================================================== -->

                            <div class="purchase-review-area">


                                <?php if (
                                    $orden['estado_orden'] === 'entregado'
                                ): ?>


                                    <?php if (
                                        !$reseñasUsuario[
                                            $it['producto_id']
                                        ]
                                    ): ?>

                                        <a
                                            href="/bodytech/public/dejar_resena.php?orden=<?= $orden['id'] ?>&producto=<?= $it['producto_id'] ?>"
                                            class="purchase-review-button"
                                        >

                                            <i class="fa-solid fa-star"></i>

                                            Dejar reseña

                                        </a>


                                    <?php else: ?>


                                        <div class="purchase-reviewed">

                                            <i class="fa-solid fa-circle-check"></i>

                                            Ya reseñaste este producto

                                        </div>


                                    <?php endif; ?>


                                <?php else: ?>


                                    <div class="purchase-review-pending">

                                        <i class="fa-solid fa-clock"></i>

                                        Podrás reseñar este producto
                                        cuando el pedido sea entregado.

                                    </div>


                                <?php endif; ?>


                            </div>


                        </div>


                    </article>


                <?php endforeach; ?>


            </div>

        </section>


    </section>

</main>


<?php require_once '../app/views/footer.php'; ?>