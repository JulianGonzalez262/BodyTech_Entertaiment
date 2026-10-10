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
| OBTENER ÓRDENES
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("

SELECT

    o.id,
    o.referencia,
    o.total,
    o.descuento,
    o.metodo_pago,
    o.estado_pago,
    o.estado_orden,
    o.transportadora,
    o.guia_envio,
    o.tracking_url,
    o.fecha_envio,
    o.created_at,

    p.referencia_pago,
    p.gateway

FROM ordenes o

LEFT JOIN pagos_productos p

    ON p.orden_id = o.id

WHERE o.usuario_id = ?

ORDER BY o.created_at DESC

");

$stmt->execute([$usuario_id]);

$ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| OBTENER PRODUCTOS DE CADA ORDEN
|--------------------------------------------------------------------------
*/

foreach ($ordenes as &$orden) {

    $stmtItems = $pdo->prepare("

        SELECT

            pr.nombre,

            oi.cantidad,

            oi.precio_unitario,

            oi.subtotal

        FROM orden_items oi

        INNER JOIN productos pr

            ON pr.id = oi.producto_id

        WHERE oi.orden_id = ?

    ");

    $stmtItems->execute([

        $orden['id']

    ]);

    $orden['productos'] =

        $stmtItems->fetchAll(PDO::FETCH_ASSOC);

}

unset($orden);

/*
|--------------------------------------------------------------------------
| GENERAR HTML
|--------------------------------------------------------------------------
*/

ob_start();

include $_SERVER['DOCUMENT_ROOT']
    . '/bodytech/app/views/pdf/compras.php';

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
    'Reporte_Compras_BodyTech_' .
    date('Ymd_His') .
    '.pdf';

$dompdf->stream(
    $nombreArchivo,
    [
        "Attachment" => true
    ]
);

exit;