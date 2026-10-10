<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/admin/admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

/* =========================
   VALIDAR DATOS
========================= */
$id = $_GET['id'] ?? null;
$gimnasio = $_GET['gimnasio'] ?? null;

if (!$id || !$gimnasio) {
    die("Solicitud inválida.");
}

/* =========================
   ELIMINAR RESEÑA
========================= */
$stmt = $pdo->prepare("
    DELETE FROM resenas_gimnasios
    WHERE id = ?
");

$stmt->execute([$id]);

/* =========================
   REDIRECCIÓN
========================= */
header("Location: monitorear_gimnasio.php?id=" . $gimnasio);
exit;
?>