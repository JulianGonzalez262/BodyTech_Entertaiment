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
$mensaje = trim($_POST['mensaje'] ?? '');

if (!$pqrs_id || empty($mensaje)) {
    die('Datos inválidos.');
}

/* =========================
   OBTENER ADMIN
========================= */

$admin_id = $_SESSION['admin_id'];

/* =========================
   GUARDAR RESPUESTA
========================= */

$stmt = $pdo->prepare("
    INSERT INTO pqrs_respuestas (
        pqrs_id,
        admin_id,
        mensaje
    )
    VALUES (?, ?, ?)
");

$stmt->execute([
    $pqrs_id,
    $admin_id,
    $mensaje
]);

/* =========================
   CAMBIAR ESTADO
========================= */

$stmt = $pdo->prepare("
    UPDATE pqrs
    SET estado = 'respondida'
    WHERE id = ?
");

$stmt->execute([$pqrs_id]);

/* =========================
   FUTURO CORREO ELECTRÓNICO
========================= */

/*
Aquí más adelante enviaremos:

- Correo al cliente indicando que su PQRS fue respondida.
- Enlace directo para verla.
- Copia del mensaje del administrador.
*/

/* =========================
   REDIRECCIÓN
========================= */

header("Location: ver.php?id=" . $pqrs_id);
exit;