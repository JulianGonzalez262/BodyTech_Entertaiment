<?php

require_once '../app/views/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';


/* =========================================================
   VALIDAR ORDEN
========================================================= */

$orden_id = $_GET['orden'] ?? null;

if (!$orden_id) {
    die("Orden inválida.");
}


/* =========================================================
   OBTENER ORDEN
========================================================= */

$stmt = $pdo->prepare("
    SELECT *
    FROM ordenes
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$orden_id]);

$orden = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$orden) {
    die("Orden no encontrada.");
}


/* =========================================================
   OBTENER PAGO
========================================================= */

$stmt = $pdo->prepare("
    SELECT *
    FROM pagos_productos
    WHERE orden_id = ?
    LIMIT 1
");

$stmt->execute([$orden_id]);

$pago = $stmt->fetch(PDO::FETCH_ASSOC);


/* =========================================================
   DATOS VISUALES DEL ESTADO
========================================================= */

$estadoPago = strtolower($orden['estado_pago'] ?? '');

if ($estadoPago === 'aprobado') {

    $estadoClase = 'success';
    $estadoIcono = '✓';
    $estadoTitulo = 'Compra realizada con éxito';

    $estadoDescripcion =
        'Tu pago fue aprobado y tu pedido ya se encuentra en preparación.';

} elseif ($estadoPago === 'pendiente') {

    $estadoClase = 'pending';
    $estadoIcono = '⏳';
    $estadoTitulo = 'Pago pendiente';

    $estadoDescripcion =
        'Tu orden fue generada correctamente.';

} else {

    $estadoClase = 'error';
    $estadoIcono = '!';
    $estadoTitulo = 'Pago rechazado';

    $estadoDescripcion =
        'Tu entidad financiera no aprobó la transacción o ocurrió una validación negativa.';
}

?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Confirmación de compra | BODYTECH</title>

    <link rel="stylesheet" href="/bodytech/public/assets/css/confirmacion_compra.css">
</head>
<main class="confirmacion-page">

    <div class="confirmacion-container">


        <!-- =====================================================
             CABECERA DE ESTADO
        ====================================================== -->

        <section class="confirmacion-status <?= $estadoClase ?>">

            <div class="status-icon">
                <?= $estadoIcono ?>
            </div>

            <div class="status-content">

                <span class="status-label">
                    <?= $estadoPago === 'aprobado'
                        ? 'Pago confirmado'
                        : ($estadoPago === 'pendiente'
                            ? 'Pago pendiente'
                            : 'Transacción no aprobada')
                    ?>
                </span>

                <h1>
                    <?= htmlspecialchars($estadoTitulo) ?>
                </h1>

                <p>
                    <?= htmlspecialchars($estadoDescripcion) ?>
                </p>

                <?php if ($estadoPago === 'pendiente'): ?>

                    <p class="status-extra">
                        Debes acercarte a un punto Efecty o Baloto para completar el pago.
                    </p>

                <?php elseif ($estadoPago === 'rechazado'): ?>

                    <p class="status-extra">
                        Puedes intentarlo nuevamente con otro medio de pago.
                    </p>

                <?php endif; ?>

            </div>

        </section>


        <!-- =====================================================
             INFORMACIÓN DE LA ORDEN
        ====================================================== -->

        <section class="order-card">

            <div class="section-heading">

                <div class="heading-icon">
                    <i class="fa-solid fa-receipt"></i>
                </div>

                <div>
                    <span class="section-kicker">
                        RESUMEN
                    </span>

                    <h2>
                        Información de la orden
                    </h2>
                </div>

            </div>


            <div class="order-details">


                <!-- REFERENCIA ORDEN -->

                <div class="detail-item">

                    <span class="detail-label">
                        Referencia de orden
                    </span>

                    <strong class="detail-value">
                        <?= htmlspecialchars($orden['referencia']) ?>
                    </strong>

                </div>


                <!-- REFERENCIA PAGO -->

                <div class="detail-item">

                    <span class="detail-label">
                        Referencia de pago
                    </span>

                    <strong class="detail-value">

                        <?php if ($pago && !empty($pago['referencia_pago'])): ?>

                            <?= htmlspecialchars($pago['referencia_pago']) ?>

                        <?php else: ?>

                            No disponible

                        <?php endif; ?>

                    </strong>

                </div>


                <!-- MÉTODO -->

                <div class="detail-item">

                    <span class="detail-label">
                        Método de pago
                    </span>

                    <strong class="detail-value">
                        <?= strtoupper(htmlspecialchars($orden['metodo_pago'])) ?>
                    </strong>

                </div>


                <!-- TOTAL -->

                <div class="detail-item total-item">

                    <span class="detail-label">
                        Total pagado
                    </span>

                    <strong class="detail-total">
                        $<?= number_format($orden['total'], 2) ?>
                    </strong>

                </div>


                <!-- ESTADO -->

                <div class="detail-item">

                    <span class="detail-label">
                        Estado del pago
                    </span>

                    <span class="payment-badge <?= $estadoClase ?>">

                        <?= strtoupper(htmlspecialchars($orden['estado_pago'])) ?>

                    </span>

                </div>


            </div>

        </section>


        <!-- =====================================================
             MENSAJE SEGÚN ESTADO
        ====================================================== -->

        <?php if ($estadoPago === 'aprobado'): ?>

            <section class="confirmation-message success-message">

                <div class="message-icon">
                    <i class="fa-solid fa-box"></i>
                </div>

                <div>

                    <strong>
                        ¡Tu pedido está en preparación!
                    </strong>

                    <p>
                        Conserva la referencia de tu orden para consultar
                        el estado de tu pedido posteriormente.
                    </p>

                </div>

            </section>

        <?php elseif ($estadoPago === 'pendiente'): ?>

            <section class="confirmation-message pending-message">

                <div class="message-icon">
                    <i class="fa-solid fa-clock"></i>
                </div>

                <div>

                    <strong>
                        Completa tu pago
                    </strong>

                    <p>
                        Utiliza la referencia de pago indicada anteriormente
                        para realizar el pago presencial.
                    </p>

                </div>

            </section>

        <?php else: ?>

            <section class="confirmation-message error-message">

                <div class="message-icon">
                    <i class="fa-solid fa-circle-exclamation"></i>
                </div>

                <div>

                    <strong>
                        La transacción no fue aprobada
                    </strong>

                    <p>
                        Verifica los datos utilizados e intenta nuevamente
                        con otro medio de pago.
                    </p>

                </div>

            </section>

        <?php endif; ?>


        <!-- =====================================================
             ACCIONES
        ====================================================== -->

        <div class="confirmation-actions">

            <a
                href="/bodytech/public/index.php"
                class="btn-home"
            >

                <i class="fa-solid fa-house"></i>

                <span>
                    Volver al inicio
                </span>

            </a>

        </div>


    </div>

</main>


<?php require_once '../app/views/footer.php'; ?>