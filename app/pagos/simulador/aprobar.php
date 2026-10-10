<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'], $_SESSION['pago_referencia'])) {
    die('Acceso inválido');
}

$usuario_id = $_SESSION['user_id'];
$referencia = $_SESSION['pago_referencia'];

// Obtener pago
$stmt = $pdo->prepare("
    SELECT p.*, a.id AS afiliado_id
    FROM pagos p
    JOIN afiliados a ON p.afiliado_id = a.id
    WHERE p.referencia = :ref
    LIMIT 1
");
$stmt->execute(['ref' => $referencia]);
$pago = $stmt->fetch();

if (!$pago) {
    die('Pago no encontrado');
}

// Marcar pago aprobado
$stmt = $pdo->prepare("
    UPDATE pagos 
    SET estado = 'aprobado'
    WHERE id = :id
");
$stmt->execute(['id' => $pago['id']]);

// Activar afiliación
$stmt = $pdo->prepare("
    UPDATE afiliados
    SET estado = 'activo'
    WHERE id = :afiliado
");
$stmt->execute(['afiliado' => $pago['afiliado_id']]);

/* =========================
   NOTIFICACIÓN AFILIACIÓN ACTIVADA
========================= */

$stmt = $pdo->prepare("
    INSERT INTO notificaciones
    (
        usuario_id,
        titulo,
        mensaje,
        tipo
    )
    VALUES
    (
        ?,
        ?,
        ?,
        ?
    )
");

$stmt->execute([
    $usuario_id,
    'Afiliación activada',
    'Tu afiliación ha sido activada exitosamente. Ya puedes disfrutar de todos los beneficios BodyTech.',
    'afiliacion'
]);

unset($_SESSION['pago_referencia']);

// Redirigir al recibo
header('Location: /bodytech/public/afiliados/recibo.php');
exit;
