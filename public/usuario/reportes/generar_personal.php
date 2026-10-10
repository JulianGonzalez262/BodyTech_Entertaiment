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
| GENERAR HTML
|--------------------------------------------------------------------------
*/

ob_start();

include $_SERVER['DOCUMENT_ROOT']
    . '/bodytech/app/views/pdf/personal.php';

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
    'Reporte_Personal_BodyTech_' .
    date('Ymd_His') .
    '.pdf';

$dompdf->stream(
    $nombreArchivo,
    [
        "Attachment" => true
    ]
);

exit;