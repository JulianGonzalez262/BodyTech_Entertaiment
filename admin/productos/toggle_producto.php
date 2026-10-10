<?php
require_once __DIR__ . '/../admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

// Validar datos
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$estado = isset($_GET['estado']) ? (int)$_GET['estado'] : null;

if ($id <= 0 || ($estado !== 0 && $estado !== 1)) {
    header("Location: index.php");
    exit;
}

// Actualizar estado
$stmt = $pdo->prepare("
    UPDATE productos
    SET activo = ?
    WHERE id = ?
");

$stmt->execute([$estado, $id]);

// Redirigir de vuelta
header("Location: index.php");
exit;