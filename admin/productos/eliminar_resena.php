<?php
require_once __DIR__ . '/../admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

$id = $_GET['id'] ?? null;
$producto = $_GET['producto'] ?? null;

if(!$id || !$producto){
    die("Solicitud inválida.");
}

try{

    $pdo->beginTransaction();

    /* =========================
       OBTENER MEDIA
    ========================== */
    $mediaStmt = $pdo->prepare("
        SELECT *
        FROM resenas_media
        WHERE resena_id = ?
    ");

    $mediaStmt->execute([$id]);

    $medias = $mediaStmt->fetchAll(PDO::FETCH_ASSOC);

    /* =========================
       ELIMINAR ARCHIVOS
    ========================== */
    foreach($medias as $m){

        $ruta = $_SERVER['DOCUMENT_ROOT']
            . '/bodytech/public/uploads/resenas/'
            . $m['media_path'];

        if(file_exists($ruta)){
            unlink($ruta);
        }

    }

    /* =========================
       ELIMINAR MEDIA BD
    ========================== */
    $deleteMedia = $pdo->prepare("
        DELETE FROM resenas_media
        WHERE resena_id = ?
    ");

    $deleteMedia->execute([$id]);

    /* =========================
       ELIMINAR RESEÑA
    ========================== */
    $deleteResena = $pdo->prepare("
        DELETE FROM resenas_productos
        WHERE id = ?
    ");

    $deleteResena->execute([$id]);

    $pdo->commit();

}catch(Exception $e){

    $pdo->rollBack();

    die("Error: " . $e->getMessage());
}

header("Location: monitorear_productos.php?id=" . $producto);
exit;