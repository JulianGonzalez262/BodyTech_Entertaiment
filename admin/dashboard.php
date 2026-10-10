<?php
require_once __DIR__ . '/admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

/* =========================
   MÉTRICAS MARKETPLACE
========================= */
$totalOrdenes = $pdo->query("SELECT COUNT(*) FROM ordenes")->fetchColumn();

$totalVendido = $pdo->query("
    SELECT IFNULL(SUM(monto),0)
    FROM pagos_productos
    WHERE estado = 'aprobado'
")->fetchColumn();

$pagosPendientes = $pdo->query("
    SELECT COUNT(*) FROM pagos_productos
    WHERE estado = 'pendiente'
")->fetchColumn();

$pagosRechazados = $pdo->query("
    SELECT COUNT(*) FROM pagos_productos
    WHERE estado = 'rechazado'
")->fetchColumn();

$pagosAprobados = $pdo->query("
    SELECT COUNT(*) FROM pagos_productos
    WHERE estado = 'aprobado'
")->fetchColumn();

$porcAprobados = $totalOrdenes > 0 ? ($pagosAprobados / $totalOrdenes) * 100 : 0;
$porcRechazados = $totalOrdenes > 0 ? ($pagosRechazados / $totalOrdenes) * 100 : 0;
$porcPendientes = $totalOrdenes > 0 ? ($pagosPendientes / $totalOrdenes) * 100 : 0;

/* =========================
   PRODUCTOS MÁS VENDIDOS
========================= */
$stmt = $pdo->query("
    SELECT 
        productos.nombre,
        IFNULL(SUM(orden_items.cantidad),0) AS vendidos
    FROM productos
    LEFT JOIN orden_items ON productos.id = orden_items.producto_id
    GROUP BY productos.id, productos.nombre
    ORDER BY vendidos DESC
");

$topProductos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$maxVendidos = 1;

foreach($topProductos as $tp){

    if($tp['vendidos'] > $maxVendidos){
        $maxVendidos = $tp['vendidos'];
    }

}

/* =========================
   ÚLTIMAS COMPRAS
========================= */
$stmt = $pdo->query("
    SELECT usuarios.nombre, ordenes.total, ordenes.created_at
    FROM ordenes
    INNER JOIN usuarios ON ordenes.usuario_id = usuarios.id
    ORDER BY ordenes.id DESC
    LIMIT 5
");

$ultimasCompras = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   USUARIOS Y AFILIADOS
========================= */
$totalUsuarios = $pdo->query("
    SELECT COUNT(*)
    FROM usuarios
    WHERE rol_id = 1
")->fetchColumn();

$afiliadosActivos = $pdo->query("
    SELECT COUNT(*)
    FROM afiliados
    WHERE estado = 'activo'
")->fetchColumn();

$ingresosAfiliacion = $pdo->query("
    SELECT IFNULL(SUM(monto),0)
    FROM pagos
    WHERE estado = 'aprobado'
")->fetchColumn();

/* =========================
   GRADOS GRÁFICO
========================= */
$gradosAprob = round(($porcAprobados / 100) * 360);
$gradosRech = round(($porcRechazados / 100) * 360);
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Dashboard estadístico</title>

    <link rel="stylesheet"
          href="/bodytech/public/assets/css/admin_estadisticas.css?v=1">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>

<main class="statistics-page">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <header class="statistics-header">

        <div class="header-title">

            <div class="header-icon">
                <i class="fa-solid fa-chart-line"></i>
            </div>

            <div>

                <span class="header-kicker">
                    ADMINISTRACIÓN
                </span>

                <h1>
                    Dashboard estadístico
                </h1>

                <p>
                    Resumen general del rendimiento de la plataforma.
                </p>

            </div>

        </div>


        <a href="/bodytech/admin/index.php"
           class="back-button">

            <i class="fa-solid fa-arrow-left"></i>

            Volver al panel

        </a>

    </header>


    <!-- =====================================================
         MÉTRICAS PRINCIPALES
    ====================================================== -->

    <section class="metrics-grid">


        <!-- Órdenes -->

        <article class="metric-card">

            <div class="metric-icon blue">
                <i class="fa-solid fa-receipt"></i>
            </div>

            <div class="metric-content">

                <span class="metric-label">
                    TOTAL ÓRDENES
                </span>

                <strong>
                    <?= $totalOrdenes ?>
                </strong>

                <small>
                    Órdenes registradas
                </small>

            </div>

        </article>


        <!-- Marketplace -->

        <article class="metric-card">

            <div class="metric-icon green">
                <i class="fa-solid fa-dollar-sign"></i>
            </div>

            <div class="metric-content">

                <span class="metric-label">
                    INGRESOS MARKETPLACE
                </span>

                <strong>
                    $<?= number_format($totalVendido, 2) ?>
                </strong>

                <small>
                    Pagos aprobados
                </small>

            </div>

        </article>


        <!-- Usuarios -->

        <article class="metric-card">

            <div class="metric-icon purple">
                <i class="fa-solid fa-users"></i>
            </div>

            <div class="metric-content">

                <span class="metric-label">
                    USUARIOS REGISTRADOS
                </span>

                <strong>
                    <?= $totalUsuarios ?>
                </strong>

                <small>
                    Clientes de la plataforma
                </small>

            </div>

        </article>


        <!-- Afiliados -->

        <article class="metric-card">

            <div class="metric-icon orange">
                <i class="fa-solid fa-dumbbell"></i>
            </div>

            <div class="metric-content">

                <span class="metric-label">
                    AFILIADOS ACTIVOS
                </span>

                <strong>
                    <?= $afiliadosActivos ?>
                </strong>

                <small>
                    Afiliados actualmente activos
                </small>

            </div>

        </article>


        <!-- Afiliaciones -->

        <article class="metric-card">

            <div class="metric-icon cyan">
                <i class="fa-solid fa-building"></i>
            </div>

            <div class="metric-content">

                <span class="metric-label">
                    INGRESOS AFILIACIONES
                </span>

                <strong>
                    $<?= number_format($ingresosAfiliacion, 2) ?>
                </strong>

                <small>
                    Pagos de afiliaciones aprobados
                </small>

            </div>

        </article>

    </section>


    <!-- =====================================================
         DISTRIBUCIÓN DE PAGOS
    ====================================================== -->

    <section class="dashboard-section">

        <div class="section-heading">

            <div class="section-icon">
                <i class="fa-solid fa-chart-pie"></i>
            </div>

            <div>

                <h2>
                    Distribución de pagos Marketplace
                </h2>

                <p>
                    Estado general de los pagos registrados.
                </p>

            </div>

        </div>


        <div class="payments-layout">


            <!-- GRÁFICO -->

            <div class="pie-wrapper">

                <div
                    class="pie"
                    style="
                        --approved: <?= $gradosAprob ?>deg;
                        --rejected: <?= $gradosAprob + $gradosRech ?>deg;
                    "
                ></div>

                <div class="pie-center">

                    <strong>
                        <?= round($porcAprobados) ?>%
                    </strong>

                    <span>
                        Aprobados
                    </span>

                </div>

            </div>


            <!-- LEYENDA -->

            <div class="payment-legend">


                <div class="legend-item">

                    <div class="legend-color approved"></div>

                    <div>

                        <strong>
                            <?= round($porcAprobados) ?>%
                        </strong>

                        <span>
                            Pagos aprobados
                        </span>

                    </div>

                </div>


                <div class="legend-item">

                    <div class="legend-color rejected"></div>

                    <div>

                        <strong>
                            <?= round($porcRechazados) ?>%
                        </strong>

                        <span>
                            Pagos rechazados
                        </span>

                    </div>

                </div>


                <div class="legend-item">

                    <div class="legend-color pending"></div>

                    <div>

                        <strong>
                            <?= round($porcPendientes) ?>%
                        </strong>

                        <span>
                            Pagos pendientes
                        </span>

                    </div>

                </div>

            </div>

        </div>

    </section>


    <!-- =====================================================
         PRODUCTOS MÁS VENDIDOS
    ====================================================== -->

    <section class="dashboard-section">

        <div class="section-heading">

            <div class="section-icon">
                <i class="fa-solid fa-box-open"></i>
            </div>

            <div>

                <h2>
                    Ranking de productos vendidos
                </h2>

                <p>
                    Cantidad de unidades vendidas por producto.
                </p>

            </div>

        </div>


        <?php if (!empty($topProductos)): ?>

            <div class="products-ranking">

                <?php
                $posicion = 1;

                foreach($topProductos as $tp):

                    $ancho = ($tp['vendidos'] / $maxVendidos) * 100;
                ?>

                    <div class="product-ranking-item">

                        <div class="product-ranking-header">

                            <div class="product-name">

                                <span class="product-position">
                                    <?= $posicion ?>
                                </span>

                                <span>
                                    <?= htmlspecialchars($tp['nombre']) ?>
                                </span>

                            </div>

                            <strong>
                                <?= $tp['vendidos'] ?> vendidos
                            </strong>

                        </div>


                        <div class="bar-container">

                            <div
                                class="bar"
                                style="width:<?= $ancho ?>%;"
                            ></div>

                        </div>

                    </div>

                <?php
                    $posicion++;

                endforeach;
                ?>

            </div>

        <?php else: ?>

            <div class="empty-state">

                <i class="fa-solid fa-box-open"></i>

                <p>
                    No hay productos registrados.
                </p>

            </div>

        <?php endif; ?>

    </section>


    <!-- =====================================================
         ÚLTIMAS COMPRAS
    ====================================================== -->

    <section class="dashboard-section">

        <div class="section-heading">

            <div class="section-icon">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>

            <div>

                <h2>
                    Últimas compras
                </h2>

                <p>
                    Las cinco órdenes más recientes.
                </p>

            </div>

        </div>


        <?php if (!empty($ultimasCompras)): ?>

            <div class="purchases-list">

                <?php foreach($ultimasCompras as $u): ?>

                    <div class="purchase-item">

                        <div class="purchase-user">

                            <div class="purchase-avatar">

                                <i class="fa-solid fa-user"></i>

                            </div>

                            <div>

                                <strong>
                                    <?= htmlspecialchars($u['nombre']) ?>
                                </strong>

                                <span>
                                    <i class="fa-regular fa-calendar"></i>

                                    <?= htmlspecialchars($u['created_at']) ?>
                                </span>

                            </div>

                        </div>


                        <div class="purchase-total">

                            <span>
                                Total
                            </span>

                            <strong>
                                $<?= number_format($u['total'], 2) ?>
                            </strong>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="empty-state">

                <i class="fa-solid fa-cart-shopping"></i>

                <p>
                    No existen compras registradas.
                </p>

            </div>

        <?php endif; ?>

    </section>


    <!-- =====================================================
         RESUMEN FINANCIERO
    ====================================================== -->

    <section class="dashboard-section financial-section">

        <div class="section-heading">

            <div class="section-icon">
                <i class="fa-solid fa-coins"></i>
            </div>

            <div>

                <h2>
                    Resumen financiero global
                </h2>

                <p>
                    Consolidado de los ingresos de la plataforma.
                </p>

            </div>

        </div>


        <div class="financial-grid">


            <div class="financial-item">

                <span>
                    <i class="fa-solid fa-store"></i>
                    Marketplace
                </span>

                <strong>
                    $<?= number_format($totalVendido, 2) ?>
                </strong>

            </div>


            <div class="financial-item">

                <span>
                    <i class="fa-solid fa-handshake"></i>
                    Afiliaciones
                </span>

                <strong>
                    $<?= number_format($ingresosAfiliacion, 2) ?>
                </strong>

            </div>


            <div class="financial-item total">

                <span>
                    <i class="fa-solid fa-chart-line"></i>
                    Total plataforma
                </span>

                <strong>
                    $<?= number_format(
                        $totalVendido + $ingresosAfiliacion,
                        2
                    ) ?>
                </strong>

            </div>

        </div>

    </section>


    <!-- =====================================================
         FOOTER
    ====================================================== -->

    <footer class="statistics-footer">

        <a href="/bodytech/admin/index.php">

            <i class="fa-solid fa-arrow-left"></i>

            Volver al panel administrativo

        </a>
            <a href="/bodytech/admin/reportes/estadisticas.php"
    class="back-button">

        <i class="fa-solid fa-file-excel"></i>

        Generar reportes

    </a>

    </footer>

</main>

</body>
</html>