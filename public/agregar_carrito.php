<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

/* =========================
   VALIDAR LOGIN
========================= */

if (!isset($_SESSION['usuario_id'])) {
    header("Location: /bodytech/public/login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

$producto_id = $_POST['producto_id'] ?? null;
$cantidad = isset($_POST['cantidad'])
    ? (int) $_POST['cantidad']
    : 1;


/* =========================
   VALIDAR DATOS
========================= */

if (!$producto_id) {
    die("Producto inválido.");
}

if ($cantidad < 1) {
    die("Cantidad inválida.");
}


/* =========================
   BUSCAR PRODUCTO Y STOCK
========================= */

$stmt = $pdo->prepare("
    SELECT
        id,
        nombre,
        stock
    FROM productos
    WHERE id = ?
    AND activo = 1
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

$stock = (int) $producto['stock'];

if ($stock <= 0) {
    die("Producto sin stock.");
}

if ($cantidad > $stock) {
    die(
        "No puedes agregar {$cantidad} unidades. " .
        "Solo hay {$stock} disponibles."
    );
}


/* =========================
   BUSCAR O CREAR CARRITO
========================= */

$stmt = $pdo->prepare("
    SELECT id
    FROM carrito
    WHERE usuario_id = ?
    LIMIT 1
");

$stmt->execute([$usuario_id]);

$carrito = $stmt->fetch(PDO::FETCH_ASSOC);

if ($carrito) {

    $carrito_id = $carrito['id'];

} else {

    $stmt = $pdo->prepare("
        INSERT INTO carrito (usuario_id)
        VALUES (?)
    ");

    $stmt->execute([$usuario_id]);

    $carrito_id = $pdo->lastInsertId();
}


/* =========================
   BUSCAR PRODUCTO EN CARRITO
========================= */

$stmt = $pdo->prepare("
    SELECT
        id,
        cantidad
    FROM carrito_items
    WHERE carrito_id = ?
    AND producto_id = ?
    LIMIT 1
");

$stmt->execute([
    $carrito_id,
    $producto_id
]);

$item = $stmt->fetch(PDO::FETCH_ASSOC);


/* =========================
   ACTUALIZAR O INSERTAR
========================= */

if ($item) {

    /*
     * El producto ya está en el carrito.
     * Sumamos la cantidad seleccionada.
     */

    $nuevaCantidad = (int) $item['cantidad'] + $cantidad;


    /* =========================
       VALIDAR STOCK TOTAL
    ========================= */

    if ($nuevaCantidad > $stock) {

        die(
            "No puedes agregar esa cantidad. " .
            "Ya tienes {$item['cantidad']} unidad(es) " .
            "de este producto en tu carrito y solo hay " .
            "{$stock} disponibles."
        );
    }


    /* =========================
       ACTUALIZAR CARRITO
    ========================= */

    $stmt = $pdo->prepare("
        UPDATE carrito_items
        SET cantidad = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $nuevaCantidad,
        $item['id']
    ]);

} else {

    /*
     * El producto todavía no está
     * en el carrito.
     */

    $stmt = $pdo->prepare("
        INSERT INTO carrito_items (
            carrito_id,
            producto_id,
            cantidad
        )
        VALUES (?, ?, ?)
    ");

    $stmt->execute([
        $carrito_id,
        $producto_id,
        $cantidad
    ]);
}


/* =========================
   VOLVER AL PRODUCTO
========================= */

header(
    "Location: /bodytech/public/producto.php?id="
    . $producto_id
    . "&carrito=ok"
);

exit;