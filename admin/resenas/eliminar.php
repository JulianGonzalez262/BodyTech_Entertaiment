<?php
require_once __DIR__ . '/../admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

$id = $_GET['id'] ?? null;

if(!$id){
    die("Reseña inválida.");
}

/* =========================
   ELIMINAR MULTIMEDIA
========================= */
$stmt = $pdo->prepare("
    SELECT *
    FROM resenas_media
    WHERE resena_id = ?
");

$stmt->execute([$id]);

$medias = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($medias as $media){

    $ruta = $_SERVER['DOCUMENT_ROOT'] . '/bodytech/public/uploads/resenas/' . $media['media_path'];

    if(file_exists($ruta)){
        unlink($ruta);
    }

}

/* =========================
   BORRAR MEDIA DB
========================= */
$stmt = $pdo->prepare("
    DELETE FROM resenas_media
    WHERE resena_id = ?
");

$stmt->execute([$id]);

/* =========================
   BORRAR RESEÑA
========================= */
$stmt = $pdo->prepare("
    DELETE FROM resenas_productos
    WHERE id = ?
");

$stmt->execute([$id]);

header("Location: /bodytech/admin/resenas/index.php");
exit;