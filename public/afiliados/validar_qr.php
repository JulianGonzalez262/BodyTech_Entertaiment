<?php
require_once '../../app/config/database.php';

if (empty($_GET['token'])) {
    die('Token no proporcionado.');
}

$token = trim($_GET['token']);

/* =========================
   BUSCAR AFILIADO
========================= */
$stmt = $pdo->prepare("
    SELECT 
        a.*, 
        u.nombre
    FROM afiliados a
    JOIN usuarios u ON u.id = a.usuario_id
    WHERE a.qr_token = :token
    LIMIT 1
");
$stmt->execute(['token' => $token]);
$afiliado = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$afiliado) {
    mostrarResultado(false, 'Afiliación no encontrada.');
}

/* =========================
   VALIDAR ESTADO
========================= */
if ($afiliado['estado'] !== 'activo') {
    mostrarResultado(false, 'Afiliación no activa.');
}

/* =========================
   VALIDAR VIGENCIA
========================= */
$hoy = new DateTime();
$fechaFinTexto = 'SIN VENCIMIENTO';

if (
    !empty($afiliado['fecha_fin']) &&
    $afiliado['fecha_fin'] !== '0000-00-00'
) {
    try {
        $fechaFin = new DateTime($afiliado['fecha_fin']);

        if ($hoy > $fechaFin) {
            mostrarResultado(false, 'Membresía vencida.');
        }

        $fechaFinTexto = $fechaFin->format('d/m/Y');

    } catch (Exception $e) {
        $fechaFinTexto = 'SIN VENCIMIENTO';
    }
}

/* =========================
   VALIDAR HORARIO PLAN NOCTURNO 🔥
========================= */
$plan = strtolower($afiliado['tipo_membresia']);

if (str_contains($plan, 'nocturno')) {

    $horaActual = (int) date('H'); // 0 a 23

    // Solo permitido entre 18:00 y 06:00
    $horarioValido = ($horaActual >= 18 || $horaActual < 6);

    if (!$horarioValido) {
        mostrarResultado(
            false,
            'Esta membresía nocturna solo es válida entre las 6:00 pm y las 6:00 am.'
        );
    }
}

/* =========================
   TODO OK
========================= */
mostrarResultado(true, 'Acceso permitido.', $afiliado, $fechaFinTexto);


/* ======================================================
   FUNCIÓN DE RESULTADO
====================================================== */
function mostrarResultado($ok, $mensaje, $afiliado = null, $fechaFinTexto = null)
{
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Validación de Afiliación</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: <?= $ok ? '#e8f8f0' : '#fdecea' ?>;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .card {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            text-align: center;
            width: 90%;
            max-width: 420px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        h1 {
            color: <?= $ok ? '#2ecc71' : '#e74c3c' ?>;
        }
        .info {
            margin-top: 15px;
            font-size: 1rem;
        }
    </style>
</head>
<body>

<div class="card">
    <h1><?= $ok ? '✔ ACCESO PERMITIDO' : '✖ ACCESO DENEGADO' ?></h1>
    <p><?= htmlspecialchars($mensaje) ?></p>

    <?php if ($ok && $afiliado): ?>
        <div class="info">
            <p><strong>Nombre:</strong> <?= htmlspecialchars($afiliado['nombre']) ?></p>
            <p><strong>Gimnasio:</strong> <?= htmlspecialchars($afiliado['gym_nombre']) ?></p>
            <p><strong>Membresía:</strong> <?= htmlspecialchars($afiliado['tipo_membresia']) ?></p>
            <p><strong>Válido hasta:</strong> <?= $fechaFinTexto ?></p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
<?php
exit;
}
