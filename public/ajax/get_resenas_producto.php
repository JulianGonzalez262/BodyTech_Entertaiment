<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

header('Content-Type: application/json');

$producto_id = $_GET['producto_id'] ?? 0;
$last = $_GET['last'] ?? 0;

if(!$producto_id){
    echo json_encode([]);
    exit;
}

/* =========================
   NUEVAS RESEÑAS
========================= */
$stmt = $pdo->prepare("
    SELECT
        resenas_productos.*,
        usuarios.nombre AS usuario_nombre
    FROM resenas_productos

    INNER JOIN usuarios
        ON resenas_productos.usuario_id = usuarios.id

    WHERE resenas_productos.producto_id = ?
    AND resenas_productos.id > ?

    ORDER BY resenas_productos.id ASC
");

$stmt->execute([
    $producto_id,
    $last
]);

$resenas = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   MEDIA
========================= */
foreach($resenas as &$r){

    $mediaStmt = $pdo->prepare("
        SELECT *
        FROM resenas_media
        WHERE resena_id = ?
    ");

    $mediaStmt->execute([$r['id']]);

    $r['media'] = $mediaStmt->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode($resenas);