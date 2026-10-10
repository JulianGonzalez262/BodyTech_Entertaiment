<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

/* =========================
   VALIDAR SESIÓN
========================= */

if (!isset($_SESSION['usuario_id'])) {
    die('Debes iniciar sesión.');
}

/* =========================
   VALIDAR MÉTODO
========================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Acceso no permitido.');
}

/* =========================
   RECIBIR DATOS
========================= */

$usuario_id = $_SESSION['usuario_id'];

$tipo = trim($_POST['tipo'] ?? '');
$asunto = trim($_POST['asunto'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');

/* =========================
   VALIDACIONES
========================= */

$tiposPermitidos = [
    'peticion',
    'queja',
    'reclamo',
    'sugerencia'
];

if (
    empty($tipo) ||
    empty($asunto) ||
    empty($descripcion)
) {
    die('Todos los campos son obligatorios.');
}

if (!in_array($tipo, $tiposPermitidos)) {
    die('Tipo de PQRS inválido.');
}

/* =========================
   GUARDAR PQRS
========================= */

$stmt = $pdo->prepare("
    INSERT INTO pqrs (
        usuario_id,
        tipo,
        asunto,
        descripcion
    )
    VALUES (?, ?, ?, ?)
");

$stmt->execute([
    $usuario_id,
    $tipo,
    $asunto,
    $descripcion
]);

$pqrs_id = $pdo->lastInsertId();

/* =========================
   NOTIFICACIÓN ADMIN
   (LA HAREMOS EN EL SIGUIENTE PASO)
========================= */

/* =========================
   REDIRECCIÓN
========================= */

header("Location: mis_pqrs.php");
exit;