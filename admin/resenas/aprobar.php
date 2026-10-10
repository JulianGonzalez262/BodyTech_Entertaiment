<?php
require_once __DIR__ . '/../admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

$id = $_GET['id'] ?? null;

if(!$id){
    die("Reseña inválida.");
}

/* =========================
   APROBAR RESEÑA
========================= */
$stmt = $pdo->prepare("
    UPDATE resenas_productos
    SET estado = 'aprobado'
    WHERE id = ?
");

$stmt->execute([$id]);

header("Location: /bodytech/admin/resenas/index.php");
exit;