<?php

require_once __DIR__ . '/admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    die("Orden no válida.");
}

/* =========================
   OBTENER ORDEN
========================= */

$stmt = $pdo->prepare("
    SELECT
        ordenes.*,
        usuarios.nombre,
        usuarios.email
    FROM ordenes
    INNER JOIN usuarios
        ON ordenes.usuario_id = usuarios.id
    WHERE ordenes.id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$orden = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$orden) {
    die("Orden no encontrada.");
}


/* =========================
   PRODUCTOS DE LA ORDEN
========================= */

$itemsStmt = $pdo->prepare("
    SELECT
        orden_items.*,
        productos.nombre AS producto_nombre
    FROM orden_items
    INNER JOIN productos
        ON orden_items.producto_id = productos.id
    WHERE orden_items.orden_id = ?
");

$itemsStmt->execute([$id]);

$items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================
   PAGO RELACIONADO
========================= */

$pagoStmt = $pdo->prepare("
    SELECT *
    FROM pagos_productos
    WHERE orden_id = ?
    LIMIT 1
");

$pagoStmt->execute([$id]);

$pago = $pagoStmt->fetch(PDO::FETCH_ASSOC);


/* =========================
   CLASE ESTADO PAGO
========================= */

$clasePago = 'pendiente';

if ($orden['estado_pago'] === 'aprobado') {
    $clasePago = 'aprobado';
} elseif ($orden['estado_pago'] === 'rechazado') {
    $clasePago = 'rechazado';
}


/* =========================
   CLASE ESTADO ORDEN
========================= */

$claseOrden = 'pendiente';

if (in_array($orden['estado_orden'], [
    'procesando',
    'preparando',
    'enviado'
])) {
    $claseOrden = 'aprobado';
} elseif ($orden['estado_orden'] === 'cancelado') {
    $claseOrden = 'rechazado';
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

    <title>
        Orden #<?= htmlspecialchars($orden['referencia']) ?>
    </title>

    <link
        rel="stylesheet"
        href="/bodytech/public/assets/css/admin-ver-orden.css?v=1"
    >

</head>

<body>

<main class="order-page">

    <!-- =========================
         CABECERA
    ========================== -->

    <header class="order-header">

        <div class="order-header-left">

            <a
                href="/bodytech/admin/ordenes_marketplace.php"
                class="back-button"
            >
                ← Volver a órdenes
            </a>

            <div class="order-heading">

                <span class="order-eyebrow">
                    Marketplace
                </span>

                <h1>
                    Detalle de la orden
                </h1>

                <p>
                    Referencia:
                    <strong>
                        <?= htmlspecialchars($orden['referencia']) ?>
                    </strong>
                </p>

            </div>

        </div>


        <div class="order-header-status">

            <span class="status-label">
                Estado actual
            </span>

            <span class="status-badge <?= $claseOrden ?>">
                <?= strtoupper(htmlspecialchars($orden['estado_orden'])) ?>
            </span>

        </div>

    </header>


    <!-- =========================
         INFORMACIÓN GENERAL
    ========================== -->

    <section class="admin-card">

        <div class="card-header">

            <div>
                <span class="card-eyebrow">
                    Orden
                </span>

                <h2>
                    📦 Información general
                </h2>
            </div>

        </div>


        <div class="info-grid">

            <div class="info-item">

                <span class="info-label">
                    Referencia
                </span>

                <span class="info-value">
                    <?= htmlspecialchars($orden['referencia']) ?>
                </span>

            </div>


            <div class="info-item">

                <span class="info-label">
                    Cliente
                </span>

                <span class="info-value">
                    <?= htmlspecialchars($orden['nombre']) ?>
                </span>

            </div>


            <div class="info-item">

                <span class="info-label">
                    Correo electrónico
                </span>

                <span class="info-value">
                    <?= htmlspecialchars($orden['email']) ?>
                </span>

            </div>


            <div class="info-item">

                <span class="info-label">
                    Fecha de creación
                </span>

                <span class="info-value">
                    <?= htmlspecialchars($orden['created_at']) ?>
                </span>

            </div>


            <div class="info-item">

                <span class="info-label">
                    Método de pago
                </span>

                <span class="info-value">
                    <?= strtoupper(htmlspecialchars($orden['metodo_pago'])) ?>
                </span>

            </div>


            <div class="info-item">

                <span class="info-label">
                    Estado del pago
                </span>

                <span class="status-badge <?= $clasePago ?>">
                    <?= strtoupper(htmlspecialchars($orden['estado_pago'])) ?>
                </span>

            </div>


            <div class="info-item">

                <span class="info-label">
                    Estado de la orden
                </span>

                <span class="status-badge <?= $claseOrden ?>">
                    <?= strtoupper(htmlspecialchars($orden['estado_orden'])) ?>
                </span>

            </div>

        </div>

    </section>


    <!-- =========================
         GESTIÓN LOGÍSTICA
    ========================== -->

    <section class="admin-card logistics-card">

        <div class="card-header">

            <div>

                <span class="card-eyebrow">
                    Administración
                </span>

                <h2>
                    🚛 Gestión logística
                </h2>

                <p>
                    Actualiza el estado del pedido según su proceso de entrega.
                </p>

            </div>

        </div>


        <form
            method="POST"
            action="/bodytech/admin/ordenes/actualizar_estado.php"
            class="status-form"
        >

            <input
                type="hidden"
                name="orden_id"
                value="<?= (int) $id ?>"
            >

            <input
                type="hidden"
                name="transportadora"
                value=""
            >

            <input
                type="hidden"
                name="guia_envio"
                value=""
            >


            <div class="status-form-content">

                <div class="form-group">

                    <label for="estado_orden">
                        Estado del pedido
                    </label>

                    <select
                        id="estado_orden"
                        name="estado_orden"
                        class="status-select"
                    >

                        <option
                            value="pendiente"
                            <?= $orden['estado_orden'] === 'pendiente' ? 'selected' : '' ?>
                        >
                            Pendiente
                        </option>

                        <option
                            value="procesando"
                            <?= $orden['estado_orden'] === 'procesando' ? 'selected' : '' ?>
                        >
                            Procesando pedido
                        </option>

                        

                        <option
                            value="enviado"
                            <?= $orden['estado_orden'] === 'enviado' ? 'selected' : '' ?>
                        >
                            Enviado
                        </option>

                        <option
                            value="entregado"
                            <?= $orden['estado_orden'] === 'entregado' ? 'selected' : '' ?>
                        >
                            Entregado
                        </option>

                        <option
                            value="cancelado"
                            <?= $orden['estado_orden'] === 'cancelado' ? 'selected' : '' ?>
                        >
                            Cancelado
                        </option>

                    </select>

                </div>


                <button
                    type="submit"
                    class="update-status-button"
                >
                    ✓ Actualizar estado
                </button>

            </div>

        </form>

    </section>


    <!-- =========================
         INFORMACIÓN DE ENVÍO
    ========================== -->

    <section class="admin-card">

        <div class="card-header">

            <div>

                <span class="card-eyebrow">
                    Entrega
                </span>

                <h2>
                    🚚 Información de envío
                </h2>

            </div>

        </div>


        <div class="info-grid">

            <div class="info-item">

                <span class="info-label">
                    Receptor
                </span>

                <span class="info-value">
                    <?= htmlspecialchars($orden['receptor_nombre']) ?>
                </span>

            </div>


            <div class="info-item">

                <span class="info-label">
                    Teléfono
                </span>

                <span class="info-value">
                    <?= htmlspecialchars($orden['receptor_telefono']) ?>
                </span>

            </div>


            <div class="info-item">

                <span class="info-label">
                    Ciudad
                </span>

                <span class="info-value">
                    <?= htmlspecialchars($orden['ciudad']) ?>
                </span>

            </div>


            <div class="info-item">

                <span class="info-label">
                    Departamento
                </span>

                <span class="info-value">
                    <?= htmlspecialchars($orden['departamento']) ?>
                </span>

            </div>

        </div>


        <div class="detail-block">

            <span class="info-label">
                Dirección de envío
            </span>

            <span class="info-value">
                <?= htmlspecialchars($orden['direccion_envio']) ?>
            </span>

        </div>


        <?php if (!empty($orden['referencia_direccion'])): ?>

            <div class="detail-block">

                <span class="info-label">
                    Referencia de dirección
                </span>

                <span class="info-value">
                    <?= htmlspecialchars($orden['referencia_direccion']) ?>
                </span>

            </div>

        <?php endif; ?>


        <?php if (!empty($orden['notas_pedido'])): ?>

            <div class="detail-block">

                <span class="info-label">
                    Notas del pedido
                </span>

                <span class="order-notes">
                    <?= nl2br(htmlspecialchars($orden['notas_pedido'])) ?>
                </span>

            </div>

        <?php endif; ?>

    </section>


    <!-- =========================
         PRODUCTOS
    ========================== -->

    <section class="admin-card">

        <div class="card-header">

            <div>

                <span class="card-eyebrow">
                    Compra
                </span>

                <h2>
                    🛒 Productos comprados
                </h2>

            </div>

            <span class="items-count">
                <?= count($items) ?>
                <?= count($items) === 1 ? 'producto' : 'productos' ?>
            </span>

        </div>


        <?php if (!empty($items)): ?>

            <div class="table-wrapper">

                <table class="orders-table">

                    <thead>

                        <tr>

                            <th>
                                Producto
                            </th>

                            <th>
                                Cantidad
                            </th>

                            <th>
                                Precio unitario
                            </th>

                            <th>
                                Subtotal
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php foreach ($items as $item): ?>

                            <tr>

                                <td>

                                    <span class="product-name">
                                        <?= htmlspecialchars($item['producto_nombre']) ?>
                                    </span>

                                </td>


                                <td>

                                    <span class="quantity-badge">
                                        <?= (int) $item['cantidad'] ?>
                                    </span>

                                </td>


                                <td>
                                    $<?= number_format($item['precio_unitario'], 2) ?>
                                </td>


                                <td class="subtotal">
                                    $<?= number_format($item['subtotal'], 2) ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php else: ?>

            <div class="empty-state">
                No hay productos registrados en esta orden.
            </div>

        <?php endif; ?>


        <div class="order-total">

            <span>
                Total de la orden
            </span>

            <strong>
                $<?= number_format($orden['total'], 2) ?>
            </strong>

        </div>

    </section>


    <!-- =========================
         INFORMACIÓN DEL PAGO
    ========================== -->

    <?php if ($pago): ?>

        <section class="admin-card">

            <div class="card-header">

                <div>

                    <span class="card-eyebrow">
                        Transacción
                    </span>

                    <h2>
                        💳 Información del pago
                    </h2>

                </div>

            </div>


            <div class="info-grid">

                <div class="info-item">

                    <span class="info-label">
                        Referencia del pago
                    </span>

                    <span class="info-value">
                        <?= htmlspecialchars($pago['referencia_pago']) ?>
                    </span>

                </div>


                <div class="info-item">

                    <span class="info-label">
                        Gateway
                    </span>

                    <span class="info-value">
                        <?= htmlspecialchars($pago['gateway']) ?>
                    </span>

                </div>


                <div class="info-item">

                    <span class="info-label">
                        Estado
                    </span>

                    <span class="status-text">
                        <?= strtoupper(htmlspecialchars($pago['estado'])) ?>
                    </span>

                </div>


                <div class="info-item">

                    <span class="info-label">
                        Fecha
                    </span>

                    <span class="info-value">
                        <?= htmlspecialchars($pago['created_at']) ?>
                    </span>

                </div>

            </div>

        </section>

    <?php endif; ?>


</main>

</body>
</html>