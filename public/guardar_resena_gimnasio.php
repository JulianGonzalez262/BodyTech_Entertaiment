<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    die("Debes iniciar sesión.");
}

/* =========================
   DATOS
========================= */
$usuario_id = $_SESSION['usuario_id'];

$gimnasio_id = $_POST['gimnasio_id'] ?? null;
$calificacion = $_POST['calificacion'] ?? null;
$comentario = trim($_POST['comentario'] ?? '');

/* =========================
   VALIDACIONES
========================= */
if (
    !$gimnasio_id ||
    !$calificacion ||
    !$comentario
) {
    die("Completa todos los campos.");
}

/* =========================
   VALIDAR GIMNASIO
========================= */
$stmt = $pdo->prepare("
    SELECT id
    FROM gimnasios
    WHERE id = ?
    AND activo = 1
    LIMIT 1
");

$stmt->execute([$gimnasio_id]);

$gym = $stmt->fetch();

if (!$gym) {
    die("Gimnasio inválido.");
}

/* =========================
   EVITAR DUPLICADOS
========================= */
$stmt = $pdo->prepare("
    SELECT id
    FROM resenas_gimnasios
    WHERE usuario_id = ?
    AND gimnasio_id = ?
    LIMIT 1
");

$stmt->execute([
    $usuario_id,
    $gimnasio_id
]);

if ($stmt->fetch()) {
    die("Ya comentaste este gimnasio.");
}

/* =========================
   INSERTAR RESEÑA
========================= */
$stmt = $pdo->prepare("
    INSERT INTO resenas_gimnasios (
        gimnasio_id,
        usuario_id,
        calificacion,
        comentario
    )
    VALUES (?, ?, ?, ?)
");

$stmt->execute([
    $gimnasio_id,
    $usuario_id,
    $calificacion,
    $comentario
]);

header("Location: /bodytech/public/detalle_gimnasio.php?id={$gimnasio_id}&resena=ok");
exit;