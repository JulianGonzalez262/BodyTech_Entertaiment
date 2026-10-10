<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: /bodytech/public/login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$metodo = $_POST['metodo_pago'] ?? '';

/* =========================
   CUPÓN
========================= */
$codigo_cupon = trim($_POST['codigo_cupon'] ?? '');

$descuento = 0;
$cupon = null;

/* =========================
   DATOS DE ENVÍO
========================= */
$receptor_nombre = trim($_POST['receptor_nombre'] ?? '');
$receptor_telefono = trim($_POST['receptor_telefono'] ?? '');
$departamento = trim($_POST['departamento'] ?? '');
$ciudad = trim($_POST['ciudad'] ?? '');
$direccion_envio = trim($_POST['direccion_envio'] ?? '');
$referencia_direccion = trim($_POST['referencia_direccion'] ?? '');
$notas_pedido = trim($_POST['notas_pedido'] ?? '');
$direccion_confirmada = isset($_POST['direccion_confirmada']) ? 1 : 0;

if (
    !$metodo ||
    !$receptor_nombre ||
    !$receptor_telefono ||
    !$departamento ||
    !$ciudad ||
    !$direccion_envio ||
    !$direccion_confirmada
) {
    die("Debes completar correctamente todos los datos del pedido.");
}

/* =========================
   DETECTAR FUENTE DE COMPRA
========================= */
$esCompraDirecta = false;

if (isset($_SESSION['compra_directa']) && !empty($_SESSION['compra_directa'])) {

    $items = $_SESSION['compra_directa'];
    $esCompraDirecta = true;

} else {

    $stmt = $pdo->prepare("
        SELECT 
            carrito_items.id AS item_id,
            carrito_items.cantidad,
            productos.id AS producto_id,
            productos.nombre,
            productos.precio,
            productos.stock
        FROM carrito
        INNER JOIN carrito_items 
            ON carrito.id = carrito_items.carrito_id
        INNER JOIN productos 
            ON carrito_items.producto_id = productos.id
        WHERE carrito.usuario_id = ?
    ");

    $stmt->execute([$usuario_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (!$items) {
    die("No hay productos para procesar.");
}

/* =========================
   VALIDAR STOCK Y TOTAL
========================= */
$total = 0;

foreach ($items as $i) {

    if ($i['cantidad'] > $i['stock']) {
        die("No hay suficiente stock para " . $i['nombre']);
    }

    $total += ($i['precio'] * $i['cantidad']);
}

$subtotal_original = $total;

/* =========================
   VALIDAR CUPÓN
========================= */

if (!empty($codigo_cupon)) {

    $stmt = $pdo->prepare("
        SELECT *
        FROM cupones
        WHERE codigo = ?
        AND activo = 1
        AND fecha_inicio <= NOW()
        AND fecha_fin >= NOW()
        LIMIT 1
    ");

    $stmt->execute([
        strtoupper($codigo_cupon)
    ]);

    $cupon = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cupon) {
        die("El cupón no es válido o ya expiró.");
    }

    if (
        $cupon['usos_maximos'] !== null &&
        $cupon['usos_actuales'] >= $cupon['usos_maximos']
    ) {
        die("El cupón alcanzó el límite de usos.");
    }

    if ($subtotal_original < $cupon['monto_minimo']) {
        die("No cumples el monto mínimo para usar este cupón.");
    }

    /* =========================
       CALCULAR DESCUENTO
    ========================== */

    if ($cupon['tipo_descuento'] === 'porcentaje') {

        $descuento = (
            $subtotal_original *
            $cupon['valor_descuento']
        ) / 100;

    } else {

        $descuento = $cupon['valor_descuento'];
    }

    if ($descuento > $subtotal_original) {
        $descuento = $subtotal_original;
    }

    $total = $subtotal_original - $descuento;
}

/* =========================
   SIMULACIÓN PAGO
========================= */
$estado_pago = 'aprobado';

if ($metodo === 'efectivo') {
    $estado_pago = 'pendiente';
}

if (($metodo === 'tarjeta' || $metodo === 'pse') && rand(1,10) <= 2) {
    $estado_pago = 'rechazado';
}

$estado_orden = ($estado_pago === 'aprobado')
    ? 'procesando'
    : 'pendiente';

$referencia = 'ORD-' . date('YmdHis') . '-' . strtoupper(substr(bin2hex(random_bytes(3)),0,6));

$ref_pago = 'PAY-' . date('YmdHis') . '-' . strtoupper(substr(bin2hex(random_bytes(3)),0,6));

try {

    $pdo->beginTransaction();

    /* =========================
       INSERT ORDEN
    ========================== */
    $stmt = $pdo->prepare("
        INSERT INTO ordenes (
            usuario_id,
            referencia,
            total,
            descuento,
            metodo_pago,
            estado_pago,
            estado_orden,
            receptor_nombre,
            receptor_telefono,
            departamento,
            ciudad,
            direccion_envio,
            referencia_direccion,
            notas_pedido,
            direccion_confirmada
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $usuario_id,
        $referencia,
        $total,
        $descuento,
        $metodo,
        $estado_pago,
        $estado_orden,
        $receptor_nombre,
        $receptor_telefono,
        $departamento,
        $ciudad,
        $direccion_envio,
        $referencia_direccion,
        $notas_pedido,
        $direccion_confirmada
    ]);

    $orden_id = $pdo->lastInsertId();

    /* =========================
   NOTIFICACIÓN AUTOMÁTICA
========================= */

    $stmt = $pdo->prepare("
        INSERT INTO notificaciones (
            usuario_id,
            titulo,
            mensaje,
            tipo
        )
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
        $usuario_id,
        'Pedido creado',
        'Tu pedido ' . ' fue creado correctamente y se encuentra en estado ' . strtoupper($estado_orden) . '.',
        'pedido'
    ]);

    /* =========================
       INSERT ITEMS + STOCK
    ========================== */
    foreach ($items as $item) {

        $subtotal = $item['precio'] * $item['cantidad'];

        $stmt = $pdo->prepare("
            INSERT INTO orden_items (
                orden_id,
                producto_id,
                cantidad,
                precio_unitario,
                subtotal
            )
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $orden_id,
            $item['producto_id'],
            $item['cantidad'],
            $item['precio'],
            $subtotal
        ]);

        if ($estado_pago !== 'rechazado') {

            $stmt = $pdo->prepare("
                UPDATE productos
                SET stock = stock - ?
                WHERE id = ?
            ");

            $stmt->execute([
                $item['cantidad'],
                $item['producto_id']
            ]);
        }
    }

    /* =========================
       INSERT PAGO
    ========================== */
    $stmt = $pdo->prepare("
        INSERT INTO pagos_productos (
            orden_id,
            usuario_id,
            monto,
            metodo_pago,
            referencia_pago,
            estado
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $orden_id,
        $usuario_id,
        $total,
        $metodo,
        $ref_pago,
        $estado_pago
    ]);

    /* =========================
       SUMAR USO DEL CUPÓN
    ========================== */

    if ($cupon) {

        $stmt = $pdo->prepare("
            UPDATE cupones
            SET usos_actuales = usos_actuales + 1
            WHERE id = ?
        ");

        $stmt->execute([
            $cupon['id']
        ]);
    }

    /* =========================
       LIMPIEZA SEGÚN ORIGEN
    ========================== */
    if ($estado_pago !== 'rechazado') {

        if ($esCompraDirecta) {

            foreach ($items as $item) {

                $stmt = $pdo->prepare("
                    DELETE carrito_items
                    FROM carrito_items
                    INNER JOIN carrito 
                        ON carrito_items.carrito_id = carrito.id
                    WHERE carrito.usuario_id = ?
                    AND carrito_items.producto_id = ?
                ");

                $stmt->execute([
                    $usuario_id,
                    $item['producto_id']
                ]);
            }

            unset($_SESSION['compra_directa']);

        } else {

            $stmt = $pdo->prepare("
                DELETE carrito_items
                FROM carrito_items
                INNER JOIN carrito 
                    ON carrito_items.carrito_id = carrito.id
                WHERE carrito.usuario_id = ?
            ");

            $stmt->execute([$usuario_id]);
        }
    }

    $pdo->commit();

    header("Location: /bodytech/public/resultado_compra.php?orden={$orden_id}");
    exit;

} catch (Exception $e) {

    $pdo->rollBack();

    die("Error al procesar compra: " . $e->getMessage());
}