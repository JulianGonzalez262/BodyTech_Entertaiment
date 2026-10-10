<?php
require_once __DIR__ . '/../admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

/* =========================
   VALIDAR MÉTODO
========================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Acceso no permitido.');
}

$pqrs_id = $_POST['pqrs_id'] ?? null;
$estado = $_POST['estado'] ?? null;

/* =========================
   ESTADOS PERMITIDOS
========================= */

$estadosValidos = [
    'pendiente',
    'en_proceso',
    'respondida',
    'cerrada'
];

if (
    !$pqrs_id ||
    !$estado ||
    !in_array($estado, $estadosValidos)
) {
    die('Datos inválidos.');
}

/* =========================
   ACTUALIZAR ESTADO
========================= */

$stmt = $pdo->prepare("
    UPDATE pqrs
    SET estado = ?
    WHERE id = ?
");

$stmt->execute([
    $estado,
    $pqrs_id
]);

/* =========================
   FUTURAS INTEGRACIONES
========================= */

/*
Más adelante podremos agregar:

- Correo al cliente cuando la PQRS cambie de estado.
- Notificación interna en el centro de notificaciones.
- Registro histórico de cambios administrativos.
*/

/* =========================
   REDIRECCIÓN
========================= */

header("Location: ver.php?id=" . $pqrs_id);
exit;