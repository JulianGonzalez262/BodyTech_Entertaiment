<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    die('Acceso no autorizado');
}

$usuario_id = $_SESSION['user_id'];

// Buscar afiliado activo o pendiente
$stmt = $pdo->prepare("
    SELECT * FROM afiliados 
    WHERE usuario_id = :uid 
    ORDER BY created_at DESC 
    LIMIT 1
");
$stmt->execute(['uid' => $usuario_id]);
$afiliado = $stmt->fetch();

if (!$afiliado || $afiliado['estado'] !== 'pendiente_pago') {
    die('No hay pagos pendientes');
}

// Crear referencia única (estilo Wompi)
$referencia = 'BT-' . time() . '-' . $afiliado['id'];

// Registrar intento de pago
$stmt = $pdo->prepare("
    INSERT INTO pagos (afiliado_id, monto, metodo, estado, referencia)
    VALUES (:afiliado, :monto, 'tarjeta', 'pendiente', :ref)
");
$stmt->execute([
    'afiliado' => $afiliado['id'],
    'monto' => $afiliado['precio'],
    'ref' => $referencia
]);

// Guardamos referencia en sesión (como Wompi)
$_SESSION['pago_referencia'] = $referencia;

// Redirigir al “checkout” (simulado por ahora)
header('Location: /bodytech/app/pagos/simulador/aprobar.php');
exit;
