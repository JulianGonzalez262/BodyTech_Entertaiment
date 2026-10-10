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
| DATOS DEL USUARIO
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
| OBTENER PQRS
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

/*
|--------------------------------------------------------------------------
| RESPUESTAS Y ARCHIVOS
|--------------------------------------------------------------------------
*/

foreach ($pqrs as &$caso) {

    /*
    |----------------------------------------
    | RESPUESTAS
    |----------------------------------------
    */

    $stmtResp = $pdo->prepare("

        SELECT *

        FROM pqrs_respuestas

        WHERE pqrs_id = ?

        ORDER BY created_at ASC

    ");

    $stmtResp->execute([

        $caso['id']

    ]);

    $caso['respuestas'] =

        $stmtResp->fetchAll(PDO::FETCH_ASSOC);

    /*
    |----------------------------------------
    | ARCHIVOS
    |----------------------------------------
    */

    $stmtArchivos = $pdo->prepare("

        SELECT *

        FROM pqrs_archivos

        WHERE pqrs_id = ?

    ");

    $stmtArchivos->execute([

        $caso['id']

    ]);

    $caso['archivos'] =

        $stmtArchivos->fetchAll(PDO::FETCH_ASSOC);

}

unset($caso);

/*
|--------------------------------------------------------------------------
| HTML
|--------------------------------------------------------------------------
*/

ob_start();

include $_SERVER['DOCUMENT_ROOT']
    . '/bodytech/app/views/pdf/pqrs.php';

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

    'Reporte_PQRS_BodyTech_' .
    date('Ymd_His') .
    '.pdf',

    [
        "Attachment" => true
    ]

);

exit;