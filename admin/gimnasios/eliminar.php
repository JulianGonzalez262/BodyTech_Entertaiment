<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/admin/admin_auth.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int) $_GET['id'];

# 1️⃣ Obtener info del gimnasio (para borrar el archivo)
$stmt = $pdo->prepare("SELECT media_path FROM gimnasios WHERE id = ?");
$stmt->execute([$id]);
$gimnasio = $stmt->fetch();

if ($gimnasio && !empty($gimnasio['media_path'])) {
    $rutaArchivo = $_SERVER['DOCUMENT_ROOT'] . '/bodytech/public/uploads/gimnasios/' . $gimnasio['media_path'];

    if (file_exists($rutaArchivo)) {
        unlink($rutaArchivo); // 🔥 borra imagen/video
    }
}

# 2️⃣ Eliminar registro de la BD
$stmt = $pdo->prepare("DELETE FROM gimnasios WHERE id = ?");
$stmt->execute([$id]);

# 3️⃣ Volver al panel
header("Location: index.php");
exit;