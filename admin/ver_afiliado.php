<?php
require_once __DIR__ . '/admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

$id = $_GET['id'] ?? null;

if(!$id){
    die("Afiliado no válido");
}

/* =========================
   ACCIONES ADMIN
========================= */
if(isset($_GET['accion'])){

    $accion = $_GET['accion'];

    if($accion === 'cancelar'){
        $stmt = $pdo->prepare("
            UPDATE afiliados
            SET estado = 'cancelado',
                fecha_cancelacion = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$id]);

        $stmtUser = $pdo->prepare("
            SELECT usuario_id
            FROM afiliados
            WHERE id = ?
        ");

        $stmtUser->execute([$id]);

        $usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);

        $stmtNotif = $pdo->prepare("
            INSERT INTO notificaciones
            (
                usuario_id,
                titulo,
                mensaje,
                tipo
            )
            VALUES
            (
                ?, ?, ?, ?
            )
        ");

        $stmtNotif->execute([
            $usuario['usuario_id'],
            'Afiliación cancelada',
            'Tu afiliación ha sido cancelada por la administración.',
            'afiliacion'
        ]);

        header("Location: ver_afiliado.php?id=".$id);
        exit;
    }

    if($accion === 'renovar'){

        $q = $pdo->prepare("
            SELECT tipo_membresia, precio, usuario_id
            FROM afiliados
            WHERE id = ?
        ");

        $q->execute([$id]);
        $info = $q->fetch(PDO::FETCH_ASSOC);

        $fechaInicio = date('Y-m-d');

        if($info['tipo_membresia'] === 'mensual'){
            $fechaFin = date('Y-m-d', strtotime('+30 days'));
        }else{
            $fechaFin = date('Y-m-d', strtotime('+365 days'));
        }

        $nuevoQR = bin2hex(random_bytes(32));

        $stmt = $pdo->prepare("
            UPDATE afiliados
            SET estado = 'activo',
                fecha_inicio = ?,
                fecha_fin = ?,
                qr_token = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $fechaInicio,
            $fechaFin,
            $nuevoQR,
            $id
        ]);

        $ref = 'REN-' . strtoupper(bin2hex(random_bytes(4)));

        $pago = $pdo->prepare("
            INSERT INTO pagos
            (
                afiliado_id,
                usuario_id,
                tipo_membresia,
                monto,
                metodo,
                estado,
                referencia
            )
            VALUES
            (
                ?, ?, ?, ?, 'renovacion_admin', 'aprobado', ?
            )
        ");

        $pago->execute([
            $id,
            $info['usuario_id'],
            $info['tipo_membresia'],
            $info['precio'],
            $ref
        ]);

        $stmtNotif = $pdo->prepare("
            INSERT INTO notificaciones
            (
                usuario_id,
                titulo,
                mensaje,
                tipo
            )
            VALUES
            (
                ?, ?, ?, ?
            )
        ");

        $stmtNotif->execute([
            $info['usuario_id'],
            'Membresía renovada',
            'Tu membresía ha sido renovada exitosamente. Tu acceso sigue activo.',
            'afiliacion'
        ]);

        header("Location: ver_afiliado.php?id=".$id);
        exit;
    }
}

/* =========================
   PERFIL AFILIADO
========================= */
$stmt = $pdo->prepare("
    SELECT 
        afiliados.*,
        usuarios.nombre,
        usuarios.email
    FROM afiliados
    INNER JOIN usuarios ON afiliados.usuario_id = usuarios.id
    WHERE afiliados.id = ?
    LIMIT 1
");

$stmt->execute([$id]);
$afiliado = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$afiliado){
    die("Afiliado no encontrado");
}

/* =========================
   HISTORIAL DE PAGOS
========================= */
$p = $pdo->prepare("
    SELECT *
    FROM pagos
    WHERE afiliado_id = ?
    ORDER BY id DESC
");

$p->execute([$id]);
$pagos = $p->fetchAll(PDO::FETCH_ASSOC);

$totalPagado = 0;

foreach($pagos as $pg){
    if($pg['estado'] === 'aprobado'){
        $totalPagado += $pg['monto'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Perfil Premium del Afiliado</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >

    <link
        rel="stylesheet"
        href="/bodytech/public/assets/css/ver_afiliado.css?v=1"
    >

</head>

<body>

<main class="affiliate-page">

    <div class="page-container">

        <a
            href="/bodytech/admin/afiliados.php"
            class="back-link"
        >
            <i class="fa-solid fa-arrow-left"></i>
            <span>Volver a afiliados</span>
        </a>


        <header class="page-header">

            <div class="header-icon">
                <i class="fa-solid fa-id-card"></i>
            </div>

            <div>
                <span class="eyebrow">ADMINISTRACIÓN BODYTECH</span>

                <h1>Perfil del Afiliado</h1>

                <p>
                    Consulta y administra toda la información de esta afiliación.
                </p>
            </div>

        </header>


        <section class="top-actions">

            <a
                class="action-button cancel"
                href="ver_afiliado.php?id=<?= $id ?>&accion=cancelar"
            >
                <i class="fa-solid fa-circle-xmark"></i>

                <span>Cancelar Membresía</span>
            </a>


            <a
                class="action-button renew"
                href="ver_afiliado.php?id=<?= $id ?>&accion=renovar"
            >
                <i class="fa-solid fa-rotate"></i>

                <span>Renovar Membresía</span>
            </a>

        </section>


        <section class="summary-grid">

            <article class="summary-card">

                <div class="summary-icon blue">
                    <i class="fa-solid fa-chart-line"></i>
                </div>

                <div>

                    <span class="summary-label">
                        Total pagos aprobados
                    </span>

                    <strong class="summary-value">
                        $<?= number_format($totalPagado,2) ?>
                    </strong>

                </div>

            </article>


            <article class="summary-card">

                <div class="summary-icon status">
                    <i class="fa-solid fa-circle-check"></i>
                </div>

                <div>

                    <span class="summary-label">
                        Estado actual
                    </span>

                    <span class="badge <?= $afiliado['estado'] ?>">
                        <?= strtoupper($afiliado['estado']) ?>
                    </span>

                </div>

            </article>


            <article class="summary-card">

                <div class="summary-icon orange">
                    <i class="fa-solid fa-calendar-xmark"></i>
                </div>

                <div>

                    <span class="summary-label">
                        Fecha cancelación
                    </span>

                    <strong class="summary-value small">
                        <?= $afiliado['fecha_cancelacion'] ?: '---' ?>
                    </strong>

                </div>

            </article>

        </section>


        <section class="info-card">

            <div class="card-heading">

                <div class="section-icon">
                    <i class="fa-solid fa-user"></i>
                </div>

                <div>
                    <h2>Información Personal</h2>
                    <p>Datos registrados del afiliado.</p>
                </div>

            </div>


            <div class="info-grid">

                <div class="info-item">
                    <span>Nombre</span>
                    <strong><?= htmlspecialchars($afiliado['nombre']) ?></strong>
                </div>

                <div class="info-item">
                    <span>Correo electrónico</span>
                    <strong><?= htmlspecialchars($afiliado['email']) ?></strong>
                </div>

                <div class="info-item">
                    <span>Fecha de nacimiento</span>
                    <strong><?= $afiliado['fecha_nacimiento'] ?></strong>
                </div>

                <div class="info-item">
                    <span>Género</span>
                    <strong><?= strtoupper($afiliado['genero']) ?></strong>
                </div>

                <div class="info-item full">
                    <span>Dirección</span>
                    <strong><?= htmlspecialchars($afiliado['direccion']) ?></strong>
                </div>

                <div class="info-item">
                    <span>Teléfono</span>
                    <strong><?= htmlspecialchars($afiliado['telefono']) ?></strong>
                </div>

                <div class="info-item">
                    <span>Tipo de identificación</span>
                    <strong><?= htmlspecialchars($afiliado['tipo_id']) ?></strong>
                </div>

                <div class="info-item">
                    <span>Número de identificación</span>
                    <strong><?= htmlspecialchars($afiliado['numero_id']) ?></strong>
                </div>

            </div>

        </section>


        <section class="info-card emergency-card">

            <div class="card-heading">

                <div class="section-icon emergency">
                    <i class="fa-solid fa-truck-medical"></i>
                </div>

                <div>
                    <h2>Contacto de Emergencia</h2>
                    <p>Información para situaciones de emergencia.</p>
                </div>

            </div>


            <div class="info-grid two-columns">

                <div class="info-item">
                    <span>Nombre</span>
                    <strong>
                        <?= htmlspecialchars($afiliado['contacto_emergencia']) ?>
                    </strong>
                </div>

                <div class="info-item">
                    <span>Teléfono</span>
                    <strong>
                        <?= htmlspecialchars($afiliado['telefono_emergencia']) ?>
                    </strong>
                </div>

            </div>

        </section>


        <section class="info-card">

            <div class="card-heading">

                <div class="section-icon membership">
                    <i class="fa-solid fa-dumbbell"></i>
                </div>

                <div>
                    <h2>Información de Membresía</h2>
                    <p>Estado y características del plan contratado.</p>
                </div>

            </div>


            <div class="membership-highlight">

                <div>
                    <span>Plan contratado</span>

                    <strong>
                        <?= htmlspecialchars($afiliado['tipo_membresia']) ?>
                    </strong>
                </div>

                <div>
                    <span>Valor</span>

                    <strong>
                        $<?= number_format($afiliado['precio'],2) ?>
                    </strong>
                </div>

            </div>


            <div class="info-grid">

                <div class="info-item">
                    <span>Gym</span>
                    <strong>
                        <?= htmlspecialchars($afiliado['gym_nombre']) ?>
                    </strong>
                </div>

                <div class="info-item">
                    <span>Ubicación</span>
                    <strong>
                        <?= htmlspecialchars($afiliado['ciudad']) ?>
                        /
                        <?= htmlspecialchars($afiliado['departamento']) ?>
                    </strong>
                </div>

                <div class="info-item">
                    <span>Fecha de inicio</span>
                    <strong>
                        <?= $afiliado['fecha_inicio'] ?: '---' ?>
                    </strong>
                </div>

                <div class="info-item">
                    <span>Fecha de finalización</span>
                    <strong>
                        <?= $afiliado['fecha_fin'] ?: '---' ?>
                    </strong>
                </div>

                <div class="info-item">
                    <span>Email verificado</span>

                    <strong class="<?= $afiliado['email_verificado'] ? 'positive' : 'negative' ?>">
                        <?= $afiliado['email_verificado'] ? 'Sí' : 'No' ?>
                    </strong>
                </div>

                <div class="info-item">
                    <span>Acepta marketing</span>

                    <strong class="<?= $afiliado['consentimiento_marketing'] ? 'positive' : 'negative' ?>">
                        <?= $afiliado['consentimiento_marketing'] ? 'Sí' : 'No' ?>
                    </strong>
                </div>

            </div>

        </section>


        <section class="info-card qr-card">

            <div class="card-heading">

                <div class="section-icon qr">
                    <i class="fa-solid fa-qrcode"></i>
                </div>

                <div>
                    <h2>QR de Acceso</h2>
                    <p>Código utilizado para el acceso del afiliado.</p>
                </div>

            </div>


            <div class="qr-content">

                <?php if($afiliado['qr_token']): ?>

                    <div class="qr-image-wrapper">

                        <img
                            src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=<?= urlencode($afiliado['qr_token']) ?>"
                            alt="QR de acceso del afiliado"
                        >

                    </div>


                    <div class="qr-token-box">

                        <span>Token de acceso</span>

                        <code>
                            <?= htmlspecialchars($afiliado['qr_token']) ?>
                        </code>

                    </div>

                <?php else: ?>

                    <div class="empty-state">

                        <i class="fa-solid fa-qrcode"></i>

                        <p>No hay QR generado aún.</p>

                    </div>

                <?php endif; ?>

            </div>

        </section>


        <section class="info-card payments-card">

            <div class="card-heading">

                <div class="section-icon payment">
                    <i class="fa-solid fa-credit-card"></i>
                </div>

                <div>
                    <h2>Historial de Pagos</h2>
                    <p>Registro completo de las transacciones del afiliado.</p>
                </div>

            </div>


            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>ID Pago</th>
                            <th>Monto</th>
                            <th>Método</th>
                            <th>Estado</th>
                            <th>Referencia</th>
                            <th>Fecha</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php foreach($pagos as $pg): ?>

                            <tr>

                                <td data-label="ID Pago">
                                    #<?= $pg['id'] ?>
                                </td>

                                <td data-label="Monto" class="amount">
                                    $<?= number_format($pg['monto'],2) ?>
                                </td>

                                <td data-label="Método">
                                    <?= strtoupper($pg['metodo']) ?>
                                </td>

                                <td data-label="Estado">

                                    <span class="payment-status <?= $pg['estado'] ?>">
                                        <?= strtoupper($pg['estado']) ?>
                                    </span>

                                </td>

                                <td data-label="Referencia">
                                    <?= htmlspecialchars($pg['referencia'] ?? '---') ?>
                                </td>

                                <td data-label="Fecha">
                                    <?= $pg['created_at'] ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </div>

</main>

</body>
</html>