<?php
require_once __DIR__ . '/../admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    die("ID inválido");
}

/* ================================
   1️⃣ OBTENER MEDIA
================================ */
$stmt = $pdo->prepare("
    SELECT media_path FROM producto_media WHERE producto_id = ?
");
$stmt->execute([$id]);
$medias = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================================
   2️⃣ ELIMINAR ARCHIVOS FÍSICOS
================================ */
$basePath = $_SERVER['DOCUMENT_ROOT'] . '/bodytech/public/uploads/productos/';

foreach ($medias as $m) {
    $ruta = $basePath . $m['media_path'];

    if (file_exists($ruta)) {
        unlink($ruta);
    }
}

/* ================================
   3️⃣ ELIMINAR MEDIA BD
================================ */
$pdo->prepare("DELETE FROM producto_media WHERE producto_id = ?")
    ->execute([$id]);

/* ================================
   4️⃣ ELIMINAR PRODUCTO
================================ */
$pdo->prepare("DELETE FROM productos WHERE id = ?")
    ->execute([$id]);

/* ================================
   5️⃣ REDIRIGIR
================================ */
header("Location: index.php");
exit;