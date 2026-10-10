<?php
require_once __DIR__ . '/../admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    die("Orden inválida.");
}

/* =========================
   ORDEN + USUARIO
========================= */
$stmt = $pdo->prepare("
    SELECT 
        ordenes.*,
        usuarios.nombre,
        usuarios.email
    FROM ordenes
    INNER JOIN usuarios ON ordenes.usuario_id = usuarios.id
    WHERE ordenes.id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$orden = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$orden) {
    die("Orden no encontrada.");
}

/* =========================
   ITEMS
========================= */
$stmt = $pdo->prepare("
    SELECT 
        orden_items.*,
        productos.nombre
    FROM orden_items
    INNER JOIN productos ON orden_items.producto_id = productos.id
    WHERE orden_items.orden_id = ?
");
$stmt->execute([$id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   PAGO
========================= */
$stmt = $pdo->prepare("
    SELECT *
    FROM pagos_productos
    WHERE orden_id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$pago = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Detalle de Orden #<?= $orden['id'] ?> | BodyTech
    </title>

    <link
        rel="stylesheet"
        href="/bodytech/public/assets/css/admin_ver_orden.css?v=1"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >

</head>

<body>

<main class="admin-page">

    <!-- =========================
         HEADER
    ========================== -->

    <header class="page-header">

        <div class="header-content">

            <div class="header-title">

                <div class="header-icon">
                    <i class="fa-solid fa-file-invoice"></i>
                </div>

                <div>

                    <span class="header-label">
                        Marketplace
                    </span>

                    <h1>
                        Detalle de Orden
                    </h1>

                    <p>
                        Orden #<?= $orden['id'] ?>
                    </p>

                </div>

            </div>


            <a
                href="/bodytech/admin/ordenes/index.php"
                class="back-button"
            >
                <i class="fa-solid fa-arrow-left"></i>
                Volver a órdenes
            </a>

        </div>

    </header>


    <!-- =========================
         RESUMEN SUPERIOR
    ========================== -->

    <section class="order-summary">

        <div class="summary-main">

            <div class="summary-icon">
                <i class="fa-solid fa-receipt"></i>
            </div>

            <div>

                <span class="summary-label">
                    Orden
                </span>

                <strong>
                    #<?= $orden['id'] ?>
                </strong>

                <span class="summary-reference">
                    <?= htmlspecialchars($orden['referencia']) ?>
                </span>

            </div>

        </div>


        <div class="summary-total">

            <span>
                Total de la orden
            </span>

            <strong>
                $<?= number_format($orden['total'], 2) ?>
            </strong>

        </div>

    </section>


    <!-- =========================
         CLIENTE + PAGO
    ========================== -->

    <div class="two-column-grid">


        <!-- CLIENTE -->

        <section class="info-card">

            <div class="card-heading">

                <div class="card-icon">
                    <i class="fa-solid fa-user"></i>
                </div>

                <div>
                    <h2>Cliente</h2>
                    <p>Información del comprador</p>
                </div>

            </div>


            <div class="info-list">

                <div class="info-row">

                    <span class="info-label">
                        <i class="fa-solid fa-user"></i>
                        Nombre
                    </span>

                    <strong>
                        <?= htmlspecialchars($orden['nombre']) ?>
                    </strong>

                </div>


                <div class="info-row">

                    <span class="info-label">
                        <i class="fa-solid fa-envelope"></i>
                        Correo
                    </span>

                    <strong>
                        <?= htmlspecialchars($orden['email']) ?>
                    </strong>

                </div>

            </div>

        </section>


        <!-- PAGO -->

        <section class="info-card">

            <div class="card-heading">

                <div class="card-icon">
                    <i class="fa-solid fa-credit-card"></i>
                </div>

                <div>
                    <h2>Información de Pago</h2>
                    <p>Datos asociados a la transacción</p>
                </div>

            </div>


            <div class="info-list">

                <div class="info-row">

                    <span class="info-label">
                        <i class="fa-solid fa-hashtag"></i>
                        Referencia orden
                    </span>

                    <strong>
                        <?= htmlspecialchars($orden['referencia']) ?>
                    </strong>

                </div>


                <div class="info-row">

                    <span class="info-label">
                        <i class="fa-solid fa-receipt"></i>
                        Referencia pago
                    </span>

                    <strong>
                        <?= htmlspecialchars($pago['referencia_pago'] ?? 'No registrada') ?>
                    </strong>

                </div>


                <div class="info-row">

                    <span class="info-label">
                        <i class="fa-solid fa-wallet"></i>
                        Método
                    </span>

                    <strong>
                        <?= strtoupper($orden['metodo_pago']) ?>
                    </strong>

                </div>


                <div class="info-row">

                    <span class="info-label">
                        <i class="fa-solid fa-circle-check"></i>
                        Estado
                    </span>

                    <span class="payment-status <?= htmlspecialchars($orden['estado_pago']) ?>">

                        <?php if($orden['estado_pago'] === 'aprobado'): ?>

                            <i class="fa-solid fa-circle-check"></i>

                        <?php elseif($orden['estado_pago'] === 'rechazado'): ?>

                            <i class="fa-solid fa-circle-xmark"></i>

                        <?php else: ?>

                            <i class="fa-solid fa-clock"></i>

                        <?php endif; ?>

                        <?= strtoupper($orden['estado_pago']) ?>

                    </span>

                </div>


                <div class="info-row">

                    <span class="info-label">
                        <i class="fa-solid fa-dollar-sign"></i>
                        Total
                    </span>

                    <strong class="payment-total">
                        $<?= number_format($orden['total'], 2) ?>
                    </strong>

                </div>

            </div>

        </section>

    </div>


    <!-- =========================
         LOGÍSTICA
    ========================== -->

    <section class="info-card">

        <div class="card-heading">

            <div class="card-icon">
                <i class="fa-solid fa-truck"></i>
            </div>

            <div>
                <h2>Información Logística</h2>
                <p>Datos actuales del despacho</p>
            </div>

        </div>


        <div class="logistics-grid">

            <div class="logistics-item">

                <span>
                    <i class="fa-solid fa-building"></i>
                    Transportadora
                </span>

                <strong>
                    <?= htmlspecialchars($orden['transportadora'] ?? 'No registrada') ?>
                </strong>

            </div>


            <div class="logistics-item">

                <span>
                    <i class="fa-solid fa-barcode"></i>
                    Guía de envío
                </span>

                <strong>
                    <?= htmlspecialchars($orden['guia_envio'] ?? 'No registrada') ?>
                </strong>

            </div>


            <div class="logistics-item">

                <span>
                    <i class="fa-solid fa-calendar"></i>
                    Fecha de envío
                </span>

                <strong>
                    <?= htmlspecialchars($orden['fecha_envio'] ?? 'Pendiente') ?>
                </strong>

            </div>

        </div>

    </section>


    <!-- =========================
         INFORMACIÓN DE ENVÍO
    ========================== -->

    <section class="info-card">

        <div class="card-heading">

            <div class="card-icon">
                <i class="fa-solid fa-location-dot"></i>
            </div>

            <div>
                <h2>Información de Envío</h2>
                <p>Datos proporcionados por el cliente</p>
            </div>

        </div>


        <div class="shipping-grid">

            <div class="shipping-item">

                <span>
                    <i class="fa-solid fa-user"></i>
                    Receptor
                </span>

                <strong>
                    <?= htmlspecialchars($orden['receptor_nombre'] ?? 'No registrado') ?>
                </strong>

            </div>


            <div class="shipping-item">

                <span>
                    <i class="fa-solid fa-phone"></i>
                    Teléfono
                </span>

                <strong>
                    <?= htmlspecialchars($orden['receptor_telefono'] ?? 'No registrado') ?>
                </strong>

            </div>


            <div class="shipping-item">

                <span>
                    <i class="fa-solid fa-map"></i>
                    Departamento
                </span>

                <strong>
                    <?= htmlspecialchars($orden['departamento'] ?? 'No registrado') ?>
                </strong>

            </div>


            <div class="shipping-item">

                <span>
                    <i class="fa-solid fa-city"></i>
                    Ciudad
                </span>

                <strong>
                    <?= htmlspecialchars($orden['ciudad'] ?? 'No registrada') ?>
                </strong>

            </div>


            <div class="shipping-item shipping-wide">

                <span>
                    <i class="fa-solid fa-location-dot"></i>
                    Dirección
                </span>

                <strong>
                    <?= htmlspecialchars($orden['direccion_envio'] ?? 'No registrada') ?>
                </strong>

            </div>


            <div class="shipping-item shipping-wide">

                <span>
                    <i class="fa-solid fa-location-crosshairs"></i>
                    Referencia
                </span>

                <strong>
                    <?= htmlspecialchars($orden['referencia_direccion'] ?? 'Sin referencia') ?>
                </strong>

            </div>


            <div class="shipping-notes">

                <span>
                    <i class="fa-solid fa-note-sticky"></i>
                    Notas del pedido
                </span>

                <p>
                    <?= nl2br(htmlspecialchars($orden['notas_pedido'] ?? 'Sin notas')) ?>
                </p>

            </div>

        </div>

    </section>


    <!-- =========================
         PRODUCTOS
    ========================== -->

    <section class="info-card products-card">

        <div class="card-heading">

            <div class="card-icon">
                <i class="fa-solid fa-box"></i>
            </div>

            <div>
                <h2>Productos Comprados</h2>
                <p>Detalle de los artículos incluidos en la orden</p>
            </div>

        </div>


        <?php if(empty($items)): ?>

            <div class="empty-items">

                <i class="fa-solid fa-box-open"></i>

                <p>
                    No hay productos registrados en esta orden.
                </p>

            </div>

        <?php else: ?>

            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Subtotal</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php foreach($items as $item): ?>

                        <tr>

                            <td>

                                <div class="product-name">

                                    <div class="product-icon">
                                        <i class="fa-solid fa-box"></i>
                                    </div>

                                    <strong>
                                        <?= htmlspecialchars($item['nombre']) ?>
                                    </strong>

                                </div>

                            </td>


                            <td>

                                <span class="quantity-badge">
                                    <?= $item['cantidad'] ?>
                                </span>

                            </td>


                            <td>

                                $<?= number_format($item['precio_unitario'], 2) ?>

                            </td>


                            <td>

                                <strong class="subtotal">
                                    $<?= number_format($item['subtotal'], 2) ?>
                                </strong>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </section>


    <!-- =========================
         ACTUALIZAR ESTADO
    ========================== -->

    <section class="update-card">

        <div class="card-heading">

            <div class="card-icon">
                <i class="fa-solid fa-truck-fast"></i>
            </div>

            <div>
                <h2>Estado de la Orden</h2>
                <p>Actualiza el estado y la información logística.</p>
            </div>

        </div>


        <form
            action="actualizar_estado.php"
            method="POST"
            class="update-form"
        >

            <input
                type="hidden"
                name="orden_id"
                value="<?= $orden['id'] ?>"
            >


            <div class="form-grid">

                <div class="form-group">

                    <label for="estado_orden">
                        <i class="fa-solid fa-list-check"></i>
                        Estado
                    </label>

                    <div class="input-wrapper">

                        <i class="fa-solid fa-box input-icon"></i>

                        <select
                            id="estado_orden"
                            name="estado_orden"
                        >

                            <option
                                value="procesando"
                                <?= $orden['estado_orden']=='procesando'?'selected':''; ?>
                            >
                                Procesando
                            </option>

                            <option
                                value="enviado"
                                <?= $orden['estado_orden']=='enviado'?'selected':''; ?>
                            >
                                Enviado
                            </option>

                            <option
                                value="entregado"
                                <?= $orden['estado_orden']=='entregado'?'selected':''; ?>
                            >
                                Entregado
                            </option>

                            <option
                                value="cancelado"
                                <?= $orden['estado_orden']=='cancelado'?'selected':''; ?>
                            >
                                Cancelado
                            </option>

                        </select>

                    </div>

                </div>


                <div class="form-group">

                    <label for="transportadora">
                        <i class="fa-solid fa-building"></i>
                        Transportadora
                    </label>

                    <div class="input-wrapper">

                        <i class="fa-solid fa-truck input-icon"></i>

                        <input
                            type="text"
                            id="transportadora"
                            name="transportadora"
                            value="<?= htmlspecialchars($orden['transportadora'] ?? '') ?>"
                        >

                    </div>

                </div>


                <div class="form-group">

                    <label for="guia_envio">
                        <i class="fa-solid fa-barcode"></i>
                        Guía de envío
                    </label>

                    <div class="input-wrapper">

                        <i class="fa-solid fa-barcode input-icon"></i>

                        <input
                            type="text"
                            id="guia_envio"
                            name="guia_envio"
                            value="<?= htmlspecialchars($orden['guia_envio'] ?? '') ?>"
                        >

                    </div>

                </div>

            </div>


            <div class="form-actions">

                <a
                    href="/bodytech/admin/ordenes/index.php"
                    class="cancel-button"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    Cancelar
                </a>

                <button
                    type="submit"
                    class="update-button"
                >
                    <i class="fa-solid fa-floppy-disk"></i>
                    Actualizar orden
                </button>

            </div>

        </form>

    </section>


    <footer class="page-footer">

        <a href="/bodytech/admin/ordenes/index.php">

            <i class="fa-solid fa-arrow-left"></i>

            Volver a gestión de órdenes

        </a>

    </footer>

</main>

</body>
</html>