<?php
require_once '../app/views/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: /bodytech/public/login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

$stmt = $pdo->prepare("
    SELECT *
    FROM ordenes
    WHERE usuario_id = ?
    ORDER BY id DESC
");

$stmt->execute([$usuario_id]);

$ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* ==========================================================
   HISTORIAL DE LA ORDEN
========================================================== */

function obtenerHistorial($pdo, $orden_id)
{
    $stmt = $pdo->prepare("
        SELECT estado, created_at, observacion
        FROM orden_historial
        WHERE orden_id = ?
        ORDER BY created_at DESC
    ");

    $stmt->execute([$orden_id]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>


<link
    rel="stylesheet"
    href="/bodytech/public/assets/css/mis_compras.css?v=1"
>


<main class="purchases-page">

    <section class="purchases-container">


        <!-- ======================================================
             ENCABEZADO
        ======================================================= -->

        <header class="purchases-header">

            <div class="purchases-header-icon">

                <i class="fa-solid fa-box-open"></i>

            </div>

            <div>

                <span class="purchases-label">
                    MARKETPLACE FITNESS
                </span>

                <h1>
                    Mis Compras
                </h1>

                <p>
                    Consulta tus pedidos, realiza seguimiento a tus
                    envíos y revisa el historial de cada compra.
                </p>

            </div>

        </header>


        <!-- ======================================================
             LISTA
        ======================================================= -->

        <section class="purchases-list">


            <?php if (!$ordenes): ?>

                <div class="purchases-empty">

                    <div class="purchases-empty-icon">

                        <i class="fa-solid fa-bag-shopping"></i>

                    </div>

                    <h2>
                        Aún no tienes compras
                    </h2>

                    <p>
                        Cuando realices una compra en BODYTECH,
                        aparecerá aquí todo su historial.
                    </p>

                    <a
                        href="/bodytech/public/productos.php"
                        class="purchases-shop-button"
                    >

                        <i class="fa-solid fa-store"></i>

                        Explorar productos

                    </a>

                </div>

            <?php else: ?>


                <?php foreach ($ordenes as $orden): ?>


                    <?php

                    /* ==================================================
                       PRODUCTO PRINCIPAL
                    ================================================== */

                    $itemStmt = $pdo->prepare("
                        SELECT orden_items.*, productos.nombre
                        FROM orden_items
                        INNER JOIN productos
                            ON orden_items.producto_id = productos.id
                        WHERE orden_items.orden_id = ?
                        LIMIT 1
                    ");

                    $itemStmt->execute([$orden['id']]);

                    $item = $itemStmt->fetch(PDO::FETCH_ASSOC);


                    /* ==================================================
                       IMAGEN
                    ================================================== */

                    $img = null;

                    if ($item) {

                        $imgStmt = $pdo->prepare("
                            SELECT media_path
                            FROM producto_media
                            WHERE producto_id = ?
                            AND media_type = 'image'
                            LIMIT 1
                        ");

                        $imgStmt->execute([
                            $item['producto_id']
                        ]);

                        $img = $imgStmt->fetchColumn();
                    }


                    /* ==================================================
                       CANTIDAD DE ARTÍCULOS
                    ================================================== */

                    $countStmt = $pdo->prepare("
                        SELECT COUNT(*)
                        FROM orden_items
                        WHERE orden_id = ?
                    ");

                    $countStmt->execute([
                        $orden['id']
                    ]);

                    $cantidadItems = $countStmt->fetchColumn();


                    /* ==================================================
                       HISTORIAL
                    ================================================== */

                    $historial = obtenerHistorial(
                        $pdo,
                        $orden['id']
                    );

                    ?>


                    <!-- ==================================================
                         ORDEN
                    =================================================== -->

                    <article class="purchase-card">


                        <!-- IMAGEN -->

                        <div class="purchase-product-image">

                            <?php if ($img): ?>

                                <img
                                    src="/bodytech/public/uploads/productos/<?= htmlspecialchars($img) ?>"
                                    alt="<?= htmlspecialchars($item['nombre'] ?? 'Producto') ?>"
                                >

                            <?php else: ?>

                                <div class="purchase-no-image">

                                    <i class="fa-regular fa-image"></i>

                                </div>

                            <?php endif; ?>

                        </div>


                        <!-- INFORMACIÓN -->

                        <div class="purchase-main">


                            <div class="purchase-top">

                                <div>

                                    <span class="purchase-order-label">
                                        ORDEN
                                    </span>

                                    <h2>
                                        <?= htmlspecialchars($orden['referencia']) ?>
                                    </h2>

                                </div>


                                <span class="
                                    purchase-status
                                    purchase-status-<?= htmlspecialchars($orden['estado_orden']) ?>
                                ">

                                    <?= strtoupper(
                                        htmlspecialchars(
                                            $orden['estado_orden']
                                        )
                                    ) ?>

                                </span>

                            </div>


                            <div class="purchase-product">

                                <span>
                                    Producto
                                </span>

                                <strong>
                                    <?= htmlspecialchars(
                                        $item['nombre'] ?? 'Producto'
                                    ) ?>
                                </strong>

                            </div>


                            <!-- RESUMEN -->

                            <div class="purchase-summary">


                                <div class="purchase-summary-item">

                                    <i class="fa-solid fa-box"></i>

                                    <div>

                                        <span>
                                            Artículos
                                        </span>

                                        <strong>
                                            <?= $cantidadItems ?>
                                        </strong>

                                    </div>

                                </div>


                                <div class="purchase-summary-item">

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


                                <div class="purchase-summary-item">

                                    <i class="fa-solid fa-credit-card"></i>

                                    <div>

                                        <span>
                                            Método
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


                            </div>


                            <!-- SEGUIMIENTO -->

                            <?php if (
                                !empty($orden['transportadora']) ||
                                !empty($orden['guia_envio'])
                            ): ?>

                                <div class="purchase-tracking">

                                    <div class="purchase-section-title">

                                        <i class="fa-solid fa-truck"></i>

                                        <strong>
                                            Seguimiento del envío
                                        </strong>

                                    </div>


                                    <div class="tracking-grid">


                                        <div>

                                            <span>
                                                Transportadora
                                            </span>

                                            <strong>
                                                <?= htmlspecialchars(
                                                    $orden['transportadora']
                                                    ?? 'Pendiente'
                                                ) ?>
                                            </strong>

                                        </div>


                                        <div>

                                            <span>
                                                Guía
                                            </span>

                                            <strong>

                                                <?php if (
                                                    !empty($orden['guia_envio'])
                                                ): ?>

                                                    <a
                                                        href="<?= htmlspecialchars(
                                                            $orden['tracking_url']
                                                            ?? '#'
                                                        ) ?>"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                    >

                                                        <?= htmlspecialchars(
                                                            $orden['guia_envio']
                                                        ) ?>

                                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>

                                                    </a>

                                                <?php else: ?>

                                                    Pendiente

                                                <?php endif; ?>

                                            </strong>

                                        </div>


                                        <div>

                                            <span>
                                                Fecha de envío
                                            </span>

                                            <strong>
                                                <?= htmlspecialchars(
                                                    $orden['fecha_envio']
                                                    ?? 'Pendiente'
                                                ) ?>
                                            </strong>

                                        </div>


                                    </div>

                                </div>

                            <?php endif; ?>


                            <!-- HISTORIAL -->

                            <?php if ($historial): ?>

                                <div class="purchase-history">

                                    <div class="purchase-section-title">

                                        <i class="fa-solid fa-clock-rotate-left"></i>

                                        <strong>
                                            Historial de estado
                                        </strong>

                                    </div>


                                    <div class="history-list">

                                        <?php foreach ($historial as $h): ?>

                                            <div class="history-item">

                                                <div class="history-dot"></div>

                                                <div class="history-content">

                                                    <strong>
                                                        <?= strtoupper(
                                                            htmlspecialchars(
                                                                $h['estado']
                                                            )
                                                        ) ?>
                                                    </strong>

                                                    <small>
                                                        <?= htmlspecialchars(
                                                            $h['created_at']
                                                        ) ?>
                                                    </small>


                                                    <?php if (
                                                        !empty($h['observacion'])
                                                    ): ?>

                                                        <p>
                                                            <?= htmlspecialchars(
                                                                $h['observacion']
                                                            ) ?>
                                                        </p>

                                                    <?php endif; ?>

                                                </div>

                                            </div>

                                        <?php endforeach; ?>

                                    </div>

                                </div>

                            <?php endif; ?>


                            <!-- ENTREGA -->

                            <div class="purchase-delivery">

                                <div class="purchase-section-title">

                                    <i class="fa-solid fa-location-dot"></i>

                                    <strong>
                                        Información de entrega
                                    </strong>

                                </div>


                                <div class="delivery-grid">


                                    <div>

                                        <span>
                                            Receptor
                                        </span>

                                        <strong>
                                            <?= htmlspecialchars(
                                                $orden['receptor_nombre']
                                            ) ?>
                                        </strong>

                                    </div>


                                    <div>

                                        <span>
                                            Teléfono
                                        </span>

                                        <strong>
                                            <?= htmlspecialchars(
                                                $orden['receptor_telefono']
                                            ) ?>
                                        </strong>

                                    </div>


                                    <div class="delivery-address">

                                        <span>
                                            Dirección
                                        </span>

                                        <strong>

                                            <?= htmlspecialchars(
                                                $orden['direccion_envio']
                                            ) ?>

                                            <br>

                                            <?= htmlspecialchars(
                                                $orden['ciudad']
                                            ) ?>

                                            /

                                            <?= htmlspecialchars(
                                                $orden['departamento']
                                            ) ?>

                                        </strong>

                                    </div>


                                    <?php if (
                                        !empty(
                                            $orden['referencia_direccion']
                                        )
                                    ): ?>

                                        <div>

                                            <span>
                                                Referencia
                                            </span>

                                            <strong>
                                                <?= htmlspecialchars(
                                                    $orden['referencia_direccion']
                                                ) ?>
                                            </strong>

                                        </div>

                                    <?php endif; ?>


                                </div>

                            </div>


                            <!-- PIE -->

                            <div class="purchase-footer">

                                <div class="purchase-date">

                                    <i class="fa-regular fa-calendar"></i>

                                    <span>
                                        <?= htmlspecialchars(
                                            $orden['created_at']
                                        ) ?>
                                    </span>

                                </div>


                                <a
                                    href="/bodytech/public/ver_compra.php?id=<?= $orden['id'] ?>"
                                    class="purchase-detail-button"
                                >

                                    Ver detalle

                                    <i class="fa-solid fa-arrow-right"></i>

                                </a>

                            </div>


                        </div>

                    </article>


                <?php endforeach; ?>


            <?php endif; ?>


        </section>

    </section>

</main>


<?php require_once '../app/views/footer.php'; ?>