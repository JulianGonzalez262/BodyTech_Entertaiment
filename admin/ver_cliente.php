<?php
require_once __DIR__ . '/admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    die("Cliente inválido.");
}

/* =========================
   DATOS CLIENTE
========================= */
$stmt = $pdo->prepare("
    SELECT id, nombre, email, email_verificado
    FROM usuarios
    WHERE id = ? AND rol_id = 1
    LIMIT 1
");
$stmt->execute([$id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    die("Cliente no encontrado.");
}

/* =========================
   RESUMEN COMERCIAL
========================= */
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) AS compras,
        IFNULL(SUM(total),0) AS gastado
    FROM ordenes
    WHERE usuario_id = ?
");
$stmt->execute([$id]);
$resumen = $stmt->fetch(PDO::FETCH_ASSOC);

/* =========================
   HISTORIAL DE ÓRDENES
========================= */
$stmt = $pdo->prepare("
    SELECT
        o.id,
        o.referencia,
        o.total,
        o.estado_pago,
        o.estado_orden,
        o.created_at,
        p.nombre AS producto_nombre
    FROM ordenes o

    LEFT JOIN orden_items oi
        ON oi.orden_id = o.id

    LEFT JOIN productos p
        ON p.id = oi.producto_id

    WHERE o.usuario_id = ?
    ORDER BY o.id DESC
");
$stmt->execute([$id]);
$ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Perfil del Cliente | BODYTECH</title>

    <link rel="stylesheet"
          href="/bodytech/public/assets/css/admin_ver_cliente.css?v=1">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>

<main class="admin-client-profile">

    <!-- =====================================================
         HEADER
    ====================================================== -->

    <header class="admin-header">

        <div class="header-left">

            <div class="header-icon">

                <i class="fa-solid fa-user"></i>

            </div>

            <div>

                <span class="header-kicker">
                    BODYTECH ADMIN
                </span>

                <h1>
                    Perfil del cliente
                </h1>

                <p>
                    Consulta la información comercial y el historial de compras.
                </p>

            </div>

        </div>


        <a href="/bodytech/admin/clientes.php"
           class="back-button">

            <i class="fa-solid fa-arrow-left"></i>

            Volver a clientes

        </a>

    </header>


    <!-- =====================================================
         PERFIL
    ====================================================== -->

    <section class="profile-card">

        <div class="profile-main">

            <div class="profile-avatar">

                <i class="fa-solid fa-user"></i>

            </div>


            <div class="profile-info">

                <span class="profile-label">
                    CLIENTE REGISTRADO
                </span>

                <h2>
                    <?= htmlspecialchars($cliente['nombre']) ?>
                </h2>

                <div class="profile-email">

                    <i class="fa-regular fa-envelope"></i>

                    <?= htmlspecialchars($cliente['email']) ?>

                </div>

            </div>

        </div>


        <div class="profile-status">

            <?php if($cliente['email_verificado']): ?>

                <span class="status-badge verified">

                    <i class="fa-solid fa-circle-check"></i>

                    Correo verificado

                </span>

            <?php else: ?>

                <span class="status-badge pending">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    Correo pendiente

                </span>

            <?php endif; ?>

        </div>

    </section>


    <!-- =====================================================
         DATOS DEL CLIENTE
    ====================================================== -->

    <section class="information-section">

        <div class="section-title">

            <span class="section-kicker">
                INFORMACIÓN
            </span>

            <h2>
                Datos del cliente
            </h2>

        </div>


        <div class="information-grid">

            <div class="info-card">

                <div class="info-icon blue">

                    <i class="fa-solid fa-hashtag"></i>

                </div>

                <div>

                    <span>
                        ID del cliente
                    </span>

                    <strong>
                        #<?= $cliente['id'] ?>
                    </strong>

                </div>

            </div>


            <div class="info-card">

                <div class="info-icon blue">

                    <i class="fa-solid fa-user"></i>

                </div>

                <div>

                    <span>
                        Nombre
                    </span>

                    <strong>
                        <?= htmlspecialchars($cliente['nombre']) ?>
                    </strong>

                </div>

            </div>


            <div class="info-card">

                <div class="info-icon blue">

                    <i class="fa-regular fa-envelope"></i>

                </div>

                <div>

                    <span>
                        Correo electrónico
                    </span>

                    <strong>
                        <?= htmlspecialchars($cliente['email']) ?>
                    </strong>

                </div>

            </div>


            <div class="info-card">

                <div class="info-icon <?= $cliente['email_verificado'] ? 'green' : 'yellow' ?>">

                    <?php if($cliente['email_verificado']): ?>

                        <i class="fa-solid fa-circle-check"></i>

                    <?php else: ?>

                        <i class="fa-solid fa-clock"></i>

                    <?php endif; ?>

                </div>

                <div>

                    <span>
                        Estado del correo
                    </span>

                    <strong>

                        <?= $cliente['email_verificado']
                            ? 'Verificado'
                            : 'Pendiente'
                        ?>

                    </strong>

                </div>

            </div>

        </div>

    </section>


    <!-- =====================================================
         RESUMEN COMERCIAL
    ====================================================== -->

    <section class="commercial-section">

        <div class="section-title">

            <span class="section-kicker">
                ACTIVIDAD COMERCIAL
            </span>

            <h2>
                Resumen de compras
            </h2>

        </div>


        <div class="commercial-grid">

            <div class="commercial-card">

                <div class="commercial-icon blue">

                    <i class="fa-solid fa-bag-shopping"></i>

                </div>

                <div>

                    <span>
                        Total de compras
                    </span>

                    <strong>
                        <?= $resumen['compras'] ?>
                    </strong>

                    <small>
                        <?= $resumen['compras'] == 1
                            ? 'compra registrada'
                            : 'compras registradas'
                        ?>
                    </small>

                </div>

            </div>


            <div class="commercial-card">

                <div class="commercial-icon green">

                    <i class="fa-solid fa-dollar-sign"></i>

                </div>

                <div>

                    <span>
                        Dinero gastado
                    </span>

                    <strong>
                        $<?= number_format($resumen['gastado'], 2) ?>
                    </strong>

                    <small>
                        Total acumulado
                    </small>

                </div>

            </div>

        </div>

    </section>


    <!-- =====================================================
         HISTORIAL DE COMPRAS
    ====================================================== -->

    <section class="orders-section">

        <div class="section-title orders-title">

            <div>

                <span class="section-kicker">
                    HISTORIAL
                </span>

                <h2>
                    Historial de compras
                </h2>

            </div>

            <span class="orders-count">

                <i class="fa-solid fa-receipt"></i>

                <?= count($ordenes) ?> pedidos

            </span>

        </div>


        <div class="orders-card">

            <?php if(empty($ordenes)): ?>

                <div class="empty-state">

                    <div class="empty-icon">

                        <i class="fa-solid fa-cart-shopping"></i>

                    </div>

                    <h3>
                        Sin compras registradas
                    </h3>

                    <p>
                        Este cliente todavía no tiene pedidos registrados.
                    </p>

                </div>

            <?php else: ?>

                <div class="table-wrapper">

                    <table class="orders-table">

                        <thead>

                            <tr>

                                <th>
                                    Nombre y Referencia
                                </th>

                                <th>
                                    Total
                                </th>

                                <th>
                                    Estado del pago
                                </th>

                                <th>
                                    Estado del pedido
                                </th>

                                <th>
                                    Fecha
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php foreach($ordenes as $o): ?>

                            <tr>

                                <td>

                                    <div class="reference-cell">

                                        <div class="reference-icon">

                                            <i class="fa-solid fa-receipt"></i>

                                        </div>

                                        <strong>
                                                <?= htmlspecialchars($o['referencia']) ?>
                                            </strong>

                                            <?php if (!empty($o['producto_nombre'])): ?>

                                                <span class="product-name">
                                                    <?= htmlspecialchars($o['producto_nombre']) ?>
                                                </span>

                                            <?php endif; ?>

                                    </div>

                                </td>


                                <td>

                                    <strong class="order-total">

                                        $<?= number_format($o['total'], 2) ?>

                                    </strong>

                                </td>


                                <td>

                                    <span class="order-status payment">

                                        <i class="fa-solid fa-credit-card"></i>

                                        <?= strtoupper($o['estado_pago']) ?>

                                    </span>

                                </td>


                                <td>

                                    <span class="order-status order">

                                        <i class="fa-solid fa-box"></i>

                                        <?= strtoupper($o['estado_orden']) ?>

                                    </span>

                                </td>


                                <td>

                                    <div class="date-cell">

                                        <i class="fa-regular fa-calendar"></i>

                                        <?= $o['created_at'] ?>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>

    </section>


    <!-- =====================================================
         FOOTER
    ====================================================== -->

    <footer class="admin-footer">

        <a href="/bodytech/admin/clientes.php">

            <i class="fa-solid fa-arrow-left"></i>

            Regresar a gestión de clientes

        </a>

    </footer>

</main>

</body>
</html>