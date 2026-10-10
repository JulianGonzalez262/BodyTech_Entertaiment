<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    die('Debes iniciar sesión.');
}

require_once '../../app/config/database.php';
require_once '../../app/libs/dompdf/autoload.inc.php';
require_once '../../app/libs/phpqrcode/qrlib.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$usuario_id = $_SESSION['usuario_id'];

/* ===============================
   OBTENER AFILIADO
=============================== */
$stmt = $pdo->prepare("
    SELECT *
    FROM afiliados
    WHERE usuario_id = :id
    ORDER BY created_at DESC
    LIMIT 1
");
$stmt->execute(['id' => $usuario_id]);
$afiliado = $stmt->fetch();

if (!$afiliado) {
    die('No se encontró afiliación.');
}

/* ===============================
   OBTENER PAGO APROBADO
=============================== */
$stmt = $pdo->prepare("
    SELECT *
    FROM pagos
    WHERE afiliado_id = :id
      AND estado = 'aprobado'
    ORDER BY created_at DESC
    LIMIT 1
");
$stmt->execute(['id' => $afiliado['id']]);
$pago = $stmt->fetch();

if (!$pago || $afiliado['estado'] !== 'activo') {
    die('Recibo no disponible.');
}

/* ===============================
   GENERAR QR EN MEMORIA (BASE64)
=============================== */
function generarQRBase64(string $data, int $size = 4): string {
    ob_start();
    QRcode::png($data, null, QR_ECLEVEL_L, $size);
    $imageString = ob_get_clean();
    return 'data:image/png;base64,' . base64_encode($imageString);
}

$qrValidacion = generarQRBase64(
    "http://localhost/bodytech/public/afiliados/validar_qr.php?token=" . $afiliado['qr_token'],
    5
);

$qrInfo = generarQRBase64(
    "AFILIADO: {$afiliado['numero_id']} | GYM: {$afiliado['gym_nombre']} | ESTADO: {$afiliado['estado']}",
    4
);

/* ===============================
   HTML PDF
=============================== */
$html = "
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
h1, h2 { text-align: center; margin: 0; }
hr { margin: 15px 0; }
table { width: 100%; border-collapse: collapse; }
td { padding: 6px; }
.qr { text-align: center; }
.small { font-size: 10px; color: #555; text-align:center; }
</style>

<h1>BODYTECH</h1>
<h2>Recibo de Afiliación</h2>

<hr>

<table>
<tr><td><strong>Documento:</strong></td><td>{$afiliado['numero_id']}</td></tr>
<tr><td><strong>Gimnasio:</strong></td><td>{$afiliado['gym_nombre']}</td></tr>
<tr><td><strong>Membresía:</strong></td><td>{$afiliado['tipo_membresia']}</td></tr>
<tr><td><strong>Estado:</strong></td><td>{$afiliado['estado']}</td></tr>
<tr><td><strong>Fecha de pago:</strong></td><td>" . date('d/m/Y', strtotime($pago['created_at'])) . "</td></tr>
<tr><td><strong>Valor:</strong></td><td>$" . number_format($afiliado['precio'], 0, ',', '.') . " COP</td></tr>
</table>

<hr>

<table>
<tr>
<td class='qr'>
<strong>QR de Validación</strong><br>
<img src='{$qrValidacion}' width='140'>
</td>

<td class='qr'>
<strong>QR Informativo</strong><br>
<img src='{$qrInfo}' width='120'>
</td>
</tr>
</table>

<p class='small' style='margin-top:20px;'>
Este recibo acredita una afiliación activa y válida.
</p>
";

/* ===============================
   GENERAR PDF
=============================== */
$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream('recibo_afiliacion.pdf', ['Attachment' => true]);
exit;
