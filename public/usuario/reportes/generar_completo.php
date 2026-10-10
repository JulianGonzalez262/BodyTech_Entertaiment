
<?php

session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/libs/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /bodytech/public/login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

/*
|--------------------------------------------------------------------------
| USUARIO
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM usuarios
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    exit('Usuario no encontrado.');
}

/*
|--------------------------------------------------------------------------
| FUNCIÓN PARA OBTENER LOS PRODUCTOS DE UNA ORDEN
|--------------------------------------------------------------------------
*/

function obtenerProductosOrden(PDO $pdo, int $orden_id): array
{
    $stmtProductos = $pdo->prepare("
        SELECT
            p.nombre,
            oi.cantidad,
            oi.precio_unitario,
            oi.subtotal
        FROM orden_items oi
        INNER JOIN productos p
            ON p.id = oi.producto_id
        WHERE oi.orden_id = ?
        ORDER BY oi.id ASC
    ");

    $stmtProductos->execute([$orden_id]);

    return $stmtProductos->fetchAll(PDO::FETCH_ASSOC);
}

/*
|--------------------------------------------------------------------------
| COMPRAS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        o.id,
        o.referencia,
        o.total,
        o.descuento,
        o.estado_pago,
        o.created_at
    FROM ordenes o
    WHERE o.usuario_id = ?
    ORDER BY o.created_at DESC
");

$stmt->execute([$usuario_id]);
$compras = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($compras as &$compra) {
    $compra['productos'] = obtenerProductosOrden(
        $pdo,
        (int) $compra['id']
    );
}

unset($compra);

/*
|--------------------------------------------------------------------------
| PEDIDOS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM ordenes
    WHERE usuario_id = ?
    ORDER BY created_at DESC
");

$stmt->execute([$usuario_id]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($pedidos as &$pedido) {

    $pedido['productos'] = obtenerProductosOrden(
        $pdo,
        (int) $pedido['id']
    );

    $stmtHistorial = $pdo->prepare("
        SELECT *
        FROM orden_historial
        WHERE orden_id = ?
        ORDER BY created_at ASC
    ");

    $stmtHistorial->execute([$pedido['id']]);

    $pedido['historial'] = $stmtHistorial->fetchAll(PDO::FETCH_ASSOC);
}

unset($pedido);

/*
|--------------------------------------------------------------------------
| PQRS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM pqrs
    WHERE usuario_id = ?
    ORDER BY created_at DESC
");

$stmt->execute([$usuario_id]);
$pqrs = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($pqrs as &$caso) {

    $stmtResp = $pdo->prepare("
        SELECT *
        FROM pqrs_respuestas
        WHERE pqrs_id = ?
        ORDER BY created_at ASC
    ");

    $stmtResp->execute([$caso['id']]);
    $caso['respuestas'] = $stmtResp->fetchAll(PDO::FETCH_ASSOC);

    $stmtArchivos = $pdo->prepare("
        SELECT *
        FROM pqrs_archivos
        WHERE pqrs_id = ?
    ");

    $stmtArchivos->execute([$caso['id']]);
    $caso['archivos'] = $stmtArchivos->fetchAll(PDO::FETCH_ASSOC);
}

unset($caso);

/*
|--------------------------------------------------------------------------
| HTML
|--------------------------------------------------------------------------
*/

ob_start();

include $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/views/pdf/completo.php';

$html = ob_get_clean();

/*
|--------------------------------------------------------------------------
| DOMPDF
|--------------------------------------------------------------------------
*/

$options = new Options();

$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

/*
|--------------------------------------------------------------------------
| DESCARGAR
|--------------------------------------------------------------------------
*/

$dompdf->stream(
    'Reporte_Completo_BodyTech_' . date('Ymd_His') . '.pdf',
    [
        'Attachment' => true
    ]
);

exit;