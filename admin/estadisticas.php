<?php
require_once __DIR__ . '/admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

/* =========================
   RESÚMENES
========================= */
$totalOrdenes = $pdo->query("SELECT COUNT(*) FROM ordenes")->fetchColumn();

$totalVendido = $pdo->query("
    SELECT IFNULL(SUM(total),0)
    FROM ordenes
    WHERE estado_pago = 'aprobado'
")->fetchColumn();

$pagosPendientes = $pdo->query("
    SELECT COUNT(*) FROM ordenes
    WHERE estado_pago = 'pendiente'
")->fetchColumn();

$pagosRechazados = $pdo->query("
    SELECT COUNT(*) FROM ordenes
    WHERE estado_pago = 'rechazado'
")->fetchColumn();

$pagosAprobados = $pdo->query("
    SELECT COUNT(*) FROM ordenes
    WHERE estado_pago = 'aprobado'
")->fetchColumn();

$porcAprobados  = $totalOrdenes > 0 ? ($pagosAprobados / $totalOrdenes) * 100 : 0;
$porcRechazados = $totalOrdenes > 0 ? ($pagosRechazados / $totalOrdenes) * 100 : 0;
$porcPendientes = $totalOrdenes > 0 ? ($pagosPendientes / $totalOrdenes) * 100 : 0;

/* =========================
   RENDIMIENTO DE TODOS LOS PRODUCTOS
========================= */
$stmt = $pdo->query("
    SELECT 
        productos.id,
        productos.nombre,
        IFNULL(SUM(orden_items.cantidad),0) AS vendidos
    FROM productos
    LEFT JOIN orden_items 
        ON productos.id = orden_items.producto_id
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
   USUARIOS Y AFILIACIONES
========================= */
$totalUsuarios = $pdo->query("
    SELECT COUNT(*) FROM usuarios WHERE rol_id = 1
")->fetchColumn();

$afiliadosActivos = $pdo->query("
    SELECT COUNT(*) FROM afiliados WHERE estado = 'activo'
")->fetchColumn();

$ingresosAfiliacion = $pdo->query("
    SELECT IFNULL(SUM(monto),0)
    FROM pagos
    WHERE estado = 'aprobado'
")->fetchColumn();

/* =========================
   GRÁFICO CIRCULAR
========================= */
$gradosAprob = round(($porcAprobados / 100) * 360);
$gradosRech  = round(($porcRechazados / 100) * 360);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Estadístico Premium</title>
    <style>
        body{
            margin:0;
            padding:30px;
            font-family:Arial;
            background:#e2e8f0;
        }
        .grid{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
            gap:20px;
            margin-bottom:30px;
        }
        .card,.section{
            background:white;
            padding:25px;
            border-radius:12px;
            box-shadow:0 0 10px rgba(0,0,0,0.08);
            margin-bottom:25px;
        }
        .chart-wrap{
            display:flex;
            gap:40px;
            flex-wrap:wrap;
            align-items:center;
        }
        .pie{
            width:180px;
            height:180px;
            border-radius:50%;
            background:
            conic-gradient(
                #22c55e 0deg <?= $gradosAprob ?>deg,
                #ef4444 <?= $gradosAprob ?>deg <?= $gradosAprob + $gradosRech ?>deg,
                #facc15 <?= $gradosAprob + $gradosRech ?>deg 360deg
            );
        }
        .bar-container{
            margin:15px 0;
        }
        .bar{
            height:24px;
            background:#2563eb;
            border-radius:8px;
        }
    </style>
</head>
<body>

<a href="/bodytech/admin/index.php">⬅️ Volver al panel</a>

<h1>📊 Dashboard Estadístico Premium</h1>

<div class="grid">
    <div class="card"><h3>🧾 Total Órdenes</h3><p><?= $totalOrdenes ?></p></div>
    <div class="card"><h3>💰 Ingresos Marketplace</h3><p>$<?= number_format($totalVendido,2) ?></p></div>
    <div class="card"><h3>👥 Usuarios Registrados</h3><p><?= $totalUsuarios ?></p></div>
    <div class="card"><h3>💪 Afiliados Activos</h3><p><?= $afiliadosActivos ?></p></div>
    <div class="card"><h3>🏦 Ingresos Afiliaciones</h3><p>$<?= number_format($ingresosAfiliacion,2) ?></p></div>
</div>

<div class="section">
    <h3>🔵 Distribución de Pagos</h3>

    <div class="chart-wrap">
        <div class="pie"></div>
        <div>
            <p>🟢 Aprobados: <?= round($porcAprobados) ?>%</p>
            <p>🔴 Rechazados: <?= round($porcRechazados) ?>%</p>
            <p>🟡 Pendientes: <?= round($porcPendientes) ?>%</p>
        </div>
    </div>
</div>

<div class="section">
    <h3>📊 Rendimiento Comercial de Todos los Productos</h3>

    <?php foreach($topProductos as $tp): 
        $ancho = $maxVendidos > 0 ? ($tp['vendidos'] / $maxVendidos) * 100 : 0;
    ?>
        <p><?= htmlspecialchars($tp['nombre']) ?> (<?= $tp['vendidos'] ?> ventas)</p>
        <div class="bar-container">
            <div class="bar" style="width:<?= $ancho ?>%;"></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="section">
    <h3>🛒 Últimos Compradores</h3>

    <?php foreach($ultimasCompras as $u): ?>
        <p>
            <?= htmlspecialchars($u['nombre']) ?> compró por 
            <strong>$<?= number_format($u['total'],2) ?></strong> 
            el <?= $u['created_at'] ?>
        </p>
    <?php endforeach; ?>
</div>

<div class="section">
    <h3>💹 Resumen Financiero Total</h3>
    <p><strong>Marketplace:</strong> $<?= number_format($totalVendido,2) ?></p>
    <p><strong>Afiliaciones:</strong> $<?= number_format($ingresosAfiliacion,2) ?></p>
    <p><strong>Total General:</strong> $<?= number_format($totalVendido + $ingresosAfiliacion,2) ?></p>
</div>

</body>
</html>