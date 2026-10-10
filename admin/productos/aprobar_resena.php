<?php
require_once __DIR__ . '/../admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

/* =========================
   VALIDAR ID
========================= */
$id = $_GET['id'] ?? null;
$producto = $_GET['producto'] ?? null;

if (!$id || !$producto) {
    die("Solicitud inválida.");
}

/* =========================
   APROBAR RESEÑA
========================= */
$stmt = $pdo->prepare("
    UPDATE resenas_productos
    SET estado = 'aprobada'
    WHERE id = ?
");

$stmt->execute([$id]);

/* =========================
   OBTENER DATOS DE LA RESEÑA
========================= */
$stmtResena = $pdo->prepare("
    SELECT
        usuario_id,
        producto_id
    FROM resenas_productos
    WHERE id = ?
    LIMIT 1
");

$stmtResena->execute([$id]);

$resena = $stmtResena->fetch(PDO::FETCH_ASSOC);

if (!$resena) {
    die("No se encontró la reseña.");
}

/* =========================
   OBTENER PRODUCTO
========================= */
$stmtProducto = $pdo->prepare("
    SELECT nombre
    FROM productos
    WHERE id = ?
    LIMIT 1
");

$stmtProducto->execute([$resena['producto_id']]);

$productoData = $stmtProducto->fetch(PDO::FETCH_ASSOC);

if (!$productoData) {
    die("No se encontró el producto asociado a la reseña.");
}

$nombreProducto = $productoData['nombre'];

/* =========================
   NOTIFICAR APROBACIÓN
========================= */

$titulo = 'Reseña aprobada';

$mensaje =
    'Tu reseña del producto "' .
    $nombreProducto .
    '" ha sido aprobada y ya es visible para otros usuarios.';

$stmtNotificacion = $pdo->prepare("
    INSERT INTO notificaciones
    (
        usuario_id,
        titulo,
        mensaje,
        tipo,
        referencia_id
    )
    VALUES
    (
        ?,
        ?,
        ?,
        ?,
        ?
    )
");

$stmtNotificacion->execute([
    $resena['usuario_id'],
    $titulo,
    $mensaje,
    'resena_producto',
    $resena['producto_id']
]);

/* =========================
   REDIRECT
========================= */
header("Location: monitorear_productos.php?id=" . $producto);
exit;
?>