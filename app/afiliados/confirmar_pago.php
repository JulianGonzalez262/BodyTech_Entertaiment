<?php
session_start();
require_once '../config/database.php';

// Seguridad
if (!isset($_SESSION['user_id'])) {
    die('Debes iniciar sesión.');
}

$usuarioId = $_SESSION['user_id'];

// 1. Buscar afiliación del usuario
$stmt = $pdo->prepare("
    SELECT id, tipo_membresia
    FROM afiliados
    WHERE usuario_id = :usuario_id
    ORDER BY created_at DESC
    LIMIT 1
");
$stmt->execute(['usuario_id' => $usuarioId]);
$afiliado = $stmt->fetch();

if (!$afiliado) {
    die('Afiliación no encontrada.');
}

// 2. Calcular fechas
$fechaInicio = date('Y-m-d');

if ($afiliado['tipo_membresia'] === 'mensual') {
    $fechaFin = date('Y-m-d', strtotime('+30 days'));
} else {
    $fechaFin = date('Y-m-d', strtotime('+365 days'));
}

// 3. Generar token único para QR
$qrToken = bin2hex(random_bytes(32)); // 64 caracteres seguros

// 4. Actualizar afiliación (activar + fechas + token)
$stmt = $pdo->prepare("
    UPDATE afiliados
    SET 
        estado = 'activo',
        fecha_inicio = :fecha_inicio,
        fecha_fin = :fecha_fin,
        qr_token = :qr_token
    WHERE id = :afiliado_id
");
$stmt->execute([
    'fecha_inicio' => $fechaInicio,
    'fecha_fin' => $fechaFin,
    'qr_token' => $qrToken,
    'afiliado_id' => $afiliado['id']
]);

// 5. Registrar pago (simulado)
$stmt = $pdo->prepare("
    INSERT INTO pagos (afiliado_id, monto, metodo, estado, referencia)
    VALUES (:afiliado_id, :monto, 'tarjeta', 'aprobado', :referencia)
");
$stmt->execute([
    'afiliado_id' => $afiliado['id'],
    'monto' => 0, // luego lo conectas al valor real
    'referencia' => 'SIM-' . strtoupper(bin2hex(random_bytes(4)))
]);

// 6. Redirigir al recibo
header('Location: /bodytech/public/afiliados/recibo.php');
exit;

