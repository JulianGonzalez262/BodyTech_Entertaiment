<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

header('Content-Type: application/json');

$lastId = isset($_GET['last']) ? (int)$_GET['last'] : 0;

$stmt = $pdo->prepare("
    SELECT *
    FROM productos
    WHERE id > ?
       OR updated_at > NOW() - INTERVAL 10 SECOND
    ORDER BY id ASC
");

$stmt->execute([$lastId]);

$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($productos as &$producto) {

    $mediaStmt = $pdo->prepare("
        SELECT media_path, media_type
        FROM producto_media
        WHERE producto_id = ?
    ");

    $mediaStmt->execute([$producto['id']]);
    $producto['media'] = $mediaStmt->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode($productos);