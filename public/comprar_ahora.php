<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: /bodytech/public/login.php");
    exit;
}

$producto_id = $_POST['producto_id'] ?? null;
$cantidad = (int)($_POST['cantidad'] ?? 1);

if (!$producto_id) {
    die("Producto inválido.");
}

/* =========================
   VALIDAR CANTIDAD
========================= */

if ($cantidad < 1) {
    $cantidad = 1;
}

/* =========================
   BUSCAR PRODUCTO
========================= */

$stmt = $pdo->prepare("
    SELECT id, nombre, precio, stock
    FROM productos
    WHERE id = ? AND activo = 1
    LIMIT 1
");

$stmt->execute([$producto_id]);

$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    die("Producto no encontrado.");
}

/* =========================
   VALIDAR STOCK
========================= */

$stock = (int)$producto['stock'];

if ($stock <= 0) {
    die("Producto sin stock.");
}

if ($cantidad > $stock) {
    $cantidad = $stock;
}

/* =========================
   CREAR SESIÓN DE COMPRA DIRECTA
========================= */

$_SESSION['compra_directa'] = [
    [
        'producto_id' => $producto['id'],
        'nombre'      => $producto['nombre'],
        'precio'      => $producto['precio'],
        'cantidad'    => $cantidad,
        'stock'       => $stock
    ]
];

/* =========================
   IR AL CHECKOUT
========================= */

header("Location: /bodytech/public/checkout_directo.php");
exit;