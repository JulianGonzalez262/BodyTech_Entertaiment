<?php

require_once __DIR__ . '/../admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';


$orden_id = $_POST['orden_id'] ?? null;
$estado   = $_POST['estado_orden'] ?? null;

$transportadora = trim(
    $_POST['transportadora'] ?? ''
);

$guia_envio = trim(
    $_POST['guia_envio'] ?? ''
);


if (!$orden_id || !$estado) {
    die("Datos inválidos.");
}


/* =========================
   ESTADOS PERMITIDOS
========================= */

$permitidos = [
    'procesando',
    'enviado',
    'entregado',
    'cancelado'
];

if (!in_array($estado, $permitidos, true)) {
    die("Estado no permitido.");
}


/* =========================
   FECHA DE ENVÍO AUTOMÁTICA
========================= */

$fecha_envio_sql = "";

if ($estado === 'enviado') {
    $fecha_envio_sql = ", fecha_envio = NOW()";
}


/* =========================
   UPDATE PRINCIPAL
========================= */

$sql = "
    UPDATE ordenes
    SET
        estado_orden = ?,
        transportadora = ?,
        guia_envio = ?
        $fecha_envio_sql
    WHERE id = ?
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    $estado,
    $transportadora ?: null,
    $guia_envio ?: null,
    $orden_id
]);


/* =========================
   HISTORIAL DE ESTADOS
========================= */

$observacion = null;

if ($estado === 'procesando') {
    $observacion =
        'Pedido recibido y en preparación.';
}

if ($estado === 'enviado') {
    $observacion =
        'Pedido entregado a la transportadora.';
}

if ($estado === 'entregado') {
    $observacion =
        'Pedido entregado al cliente.';
}

if ($estado === 'cancelado') {
    $observacion =
        'Pedido cancelado por administración.';
}


$stmtHistorial = $pdo->prepare("
    INSERT INTO orden_historial
    (
        orden_id,
        estado,
        observacion
    )
    VALUES
    (
        ?,
        ?,
        ?
    )
");

$stmtHistorial->execute([
    $orden_id,
    $estado,
    $observacion
]);


/* =========================
   DATOS DEL PEDIDO
========================= */

$stmtUsuario = $pdo->prepare("
    SELECT
        usuario_id
    FROM ordenes
    WHERE id = ?
    LIMIT 1
");

$stmtUsuario->execute([
    $orden_id
]);

$orden = $stmtUsuario->fetch(
    PDO::FETCH_ASSOC
);

if (!$orden) {
    die("No se encontró el pedido.");
}


/* =========================
   PRODUCTOS DEL PEDIDO
========================= */

$stmtProductos = $pdo->prepare("
    SELECT
        p.nombre
    FROM orden_items oi
    INNER JOIN productos p
        ON p.id = oi.producto_id
    WHERE oi.orden_id = ?
    ORDER BY oi.id ASC
");

$stmtProductos->execute([
    $orden_id
]);

$productos = $stmtProductos->fetchAll(
    PDO::FETCH_COLUMN
);


/* =========================
   NOMBRE DEL PEDIDO
========================= */

$cantidadProductos = count($productos);

if ($cantidadProductos === 0) {

    /*
     * Caso de seguridad:
     * si por alguna razón el pedido no tiene
     * productos relacionados, no mostramos
     * una referencia técnica.
     */

    $descripcionPedido = 'tu pedido';

} elseif ($cantidadProductos === 1) {

    $descripcionPedido =
        'tu pedido de ' . $productos[0];

} else {

    $primerProducto = $productos[0];

    $productosRestantes =
        $cantidadProductos - 1;

    $descripcionPedido =
        'tu pedido de ' .
        $primerProducto .
        ' y ' .
        $productosRestantes .
        ' productos más';
}


/* =========================
   NOTIFICACIÓN AL CLIENTE
========================= */

$titulo = 'Actualización de pedido';

$mensaje =
    ucfirst($descripcionPedido) .
    ' ahora se encuentra en estado ' .
    strtoupper($estado) .
    '.';


$stmtNotificacion = $pdo->prepare("
    INSERT INTO notificaciones
    (
        usuario_id,
        titulo,
        mensaje,
        tipo
    )
    VALUES
    (
        ?,
        ?,
        ?,
        ?
    )
");

$stmtNotificacion->execute([
    $orden['usuario_id'],
    $titulo,
    $mensaje,
    'pedido'
]);


/* =========================
   REGRESAR AL ADMIN
========================= */

header(
    "Location: /bodytech/admin/ordenes/ver.php?id="
    . $orden_id
);

exit;