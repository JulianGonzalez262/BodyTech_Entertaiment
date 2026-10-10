<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: /bodytech/public/login.php");
    exit;
}

$item_id = $_POST['item_id'] ?? null;

if (!$item_id) {
    die("Ítem inválido.");
}

/* =========================
   ELIMINAR SOLO SI PERTENECE AL USUARIO
========================= */
$stmt = $pdo->prepare("
    DELETE carrito_items
    FROM carrito_items
    INNER JOIN carrito ON carrito_items.carrito_id = carrito.id
    WHERE carrito_items.id = ? AND carrito.usuario_id = ?
");
$stmt->execute([$item_id, $_SESSION['usuario_id']]);

header("Location: /bodytech/public/carrito.php");
exit;