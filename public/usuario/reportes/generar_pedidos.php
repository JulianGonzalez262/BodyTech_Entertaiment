<?php

session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/libs/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

/*
|--------------------------------------------------------------------------
| VALIDAR SESIÓN
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['usuario_id'])) {

    header('Location: /bodytech/public/login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

/*
|--------------------------------------------------------------------------
| OBTENER USUARIO
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
| OBTENER PEDIDOS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("

SELECT

    id,
    referencia,
    total,
    metodo_pago,
    estado_pago,
    estado_orden,
    transportadora,
    guia_envio,
    tracking_url,
    fecha_envio,
    created_at

FROM ordenes

WHERE usuario_id = ?

ORDER BY created_at DESC

");

$stmt->execute([$usuario_id]);

$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($pedidos as &$pedido) {

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

    $stmtProductos->execute([$pedido['id']]);

    $pedido['productos'] = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);
}

unset($pedido);
/*
|--------------------------------------------------------------------------
| OBTENER HISTORIAL DE CADA PEDIDO
|--------------------------------------------------------------------------
*/

foreach ($pedidos as &$pedido) {

    $stmtHistorial = $pdo->prepare("

        SELECT *

        FROM orden_historial

        WHERE orden_id = ?

        ORDER BY created_at ASC

    ");

    $stmtHistorial->execute([

        $pedido['id']

    ]);

    $pedido['historial'] =

        $stmtHistorial->fetchAll(PDO::FETCH_ASSOC);

}

unset($pedido);

/*
|--------------------------------------------------------------------------
| HTML
|--------------------------------------------------------------------------
*/

ob_start();

include $_SERVER['DOCUMENT_ROOT']
    . '/bodytech/app/views/pdf/pedidos.php';

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

$nombreArchivo =
    'Reporte_Pedidos_BodyTech_' .
    date('Ymd_His') .
    '.pdf';

$dompdf->stream(
    $nombreArchivo,
    [
        "Attachment" => true
    ]
);

exit;