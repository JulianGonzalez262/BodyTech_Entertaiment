<?php
session_start();

// Seguridad
if (!isset($_SESSION['usuario_id'])) {
    die('Debes iniciar sesión.');
}

require_once '../../app/config/database.php';

$usuario_id = $_SESSION['usuario_id'];

/*
    Traemos TODOS los pagos del usuario
    y verificamos si la afiliación asociada
    sigue activa o fue cancelada
*/
$stmt = $pdo->prepare("
    SELECT 
    p.monto,
    p.metodo,
    p.referencia,
    p.created_at,
    p.tipo_membresia,
    a.estado AS estado_afiliacion
FROM pagos p
LEFT JOIN afiliados a ON p.afiliado_id = a.id
WHERE p.usuario_id = :usuario_id
ORDER BY p.created_at DESC
");
$stmt->execute(['usuario_id' => $usuario_id]);
$pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Pagos</title>
    <link rel="stylesheet" href="/bodytech/public/assets/css/panel_pagos.css?v=2">
</head>
<body>

<?php require_once '../../app/views/header.php'; ?>

<main class="dashboard-page">

    <!-- Fondo -->
    <div class="dashboard-background">

        <img
            src="/bodytech/public/assets/img/Sino.png"
            alt="Background">

    </div>

    <div class="dashboard-overlay"></div>

    <section class="dashboard-card">

        <!--==========================================================
            HEADER
        ==========================================================-->

        <header class="dashboard-header">

            <div class="logo-circle">

                <i class="fa-solid fa-credit-card"></i>

            </div>

            <h1>

                BODYTECH

            </h1>

            <span class="brand">

                Marketplace Fitness

            </span>

            <h2>

                Historial de Pagos

            </h2>

            <p>

                Consulta todos los pagos realizados, revisa el estado de tus membresías y lleva un control completo de tu historial financiero.

            </p>

        </header>
<?php

$totalPagos = count($pagos);

$totalInvertido = 0;

$ultimoPago = null;

if(!empty($pagos)){

    foreach($pagos as $p){

        $totalInvertido += $p['monto'];

    }

    $ultimoPago = $pagos[0]['created_at'];

}

?>

<div class="dashboard-grid">

    <!-- Total pagos -->

    <div class="dashboard-info-card">

        <div class="confirmation-icon">

            <i class="fa-solid fa-receipt"></i>

        </div>

        <h3>

            Pagos realizados

        </h3>

        <div class="confirmation-price">

            <?= $totalPagos ?>

        </div>

    </div>

    <!-- Total invertido -->

    <div class="dashboard-info-card">

        <div class="confirmation-icon">

            <i class="fa-solid fa-wallet"></i>

        </div>

        <h3>

            Total invertido

        </h3>

        <div class="confirmation-price">

            $<?= number_format($totalInvertido,0,',','.') ?>

            <small>COP</small>

        </div>

    </div>

    <!-- Último pago -->

    <div class="dashboard-info-card">

        <div class="confirmation-icon">

            <i class="fa-solid fa-calendar-check"></i>

        </div>

        <h3>

            Último pago

        </h3>

        <p>

            <?php if($ultimoPago): ?>

                <?= date('d/m/Y',strtotime($ultimoPago)); ?>

            <?php else: ?>

                —

            <?php endif; ?>

        </p>

    </div>

</div>
    <?php if (empty($pagos)): ?>
        <p>No se registran pagos todavía.</p>
    <?php else: ?>

<div class="payments-table-card">

    <div class="table-header">

        <div class="confirmation-icon">

            <i class="fa-solid fa-clock-rotate-left"></i>

        </div>

        <div>

            <h3>

                Historial de transacciones

            </h3>

            <p>

                Todos los pagos registrados de tu cuenta.

            </p>

        </div>

    </div>

    <div class="table-responsive">

        <table class="payments-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Monto</th>
                    <th>Membresía</th>
                    <th>Método</th>
                    <th>Estado</th>
                    <th>Referencia</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pagos as $pago): ?>

                    <?php
                        // Si la afiliación NO existe o está cancelada → pago cancelado visualmente
                        $esCancelada = empty($pago['estado_afiliacion']) 
                                       || $pago['estado_afiliacion'] === 'cancelado';

                        $estadoTexto = $esCancelada ? 'CANCELADA' : 'APROBADO';
                        $estadoClase = $esCancelada ? 'estado-cancelado' : 'estado-aprobado';
                    ?>

                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($pago['created_at'])); ?></td>
                        <td>$<?= number_format($pago['monto'], 0, ',', '.'); ?></td>
                        <td><?= ucfirst(str_replace('_', ' ', $pago['tipo_membresia'])); ?></td>
                        <td><?= ucfirst($pago['metodo']); ?></td>
                        <td class="<?= $pago['estado_afiliacion'] === 'activo'
                                ? 'estado-aprobado'
                                : 'estado-rechazado'; ?>">
                            <?= strtoupper($pago['estado_afiliacion'] ?? 'CANCELADO'); ?>
                        </td>
                        <td><?= $pago['referencia'] ?? '—'; ?></td>
                    </tr>

                <?php endforeach; ?>
            </tbody>
               </table>

    </div>

</div>

<?php endif; ?>

    <div class="acciones">
        <a href="/bodytech/public/afiliados/index.php">
            <button>⬅ Volver a mi afiliación</button>
        </a>
    </div>

    </section>

</main>

<?php require_once '../../app/views/footer.php'; ?>
</body>
</html>