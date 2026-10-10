<?php
require_once __DIR__ . '/../admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

$id = $_GET['id'] ?? null;
$producto = $_GET['producto'] ?? null;

if (!$id) die("ID inválido");

// obtener archivo
$stmt = $pdo->prepare("SELECT media_path FROM producto_media WHERE id = ?");
$stmt->execute([$id]);
$media = $stmt->fetch();

if ($media) {
    $ruta = $_SERVER['DOCUMENT_ROOT'] . '/bodytech/public/uploads/productos/' . $media['media_path'];

    if (file_exists($ruta)) {
        unlink($ruta);
    }

    $pdo->prepare("DELETE FROM producto_media WHERE id = ?")->execute([$id]);
}

header("Location: editar_producto.php?id=" . $producto);
exit;