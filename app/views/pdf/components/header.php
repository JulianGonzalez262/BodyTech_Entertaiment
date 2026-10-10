<?php

$tituloReporte = $tituloReporte ?? 'Reporte Oficial';

$codigoDocumento =
    'BT-'
    . date('Ymd')
    . '-'
    . str_pad(
        (string) random_int(1, 999999),
        6,
        '0',
        STR_PAD_LEFT
    );

/*
 * Ruta del logo.
 * Se conserva la ubicación que indicaste.
 */
$rutaLogo = 'C:/xampp/htdocs/bodytech/public/assets/img/logs.png';

/*
 * Algunas bibliotecas PDF necesitan una ruta absoluta
 * del sistema de archivos para cargar las imágenes.
 */
$logoDisponible = is_file($rutaLogo) && is_readable($rutaLogo);
error_log('Ruta del logo: ' . $rutaLogo);
error_log('Logo disponible: ' . ($logoDisponible ? 'SI' : 'NO'));
?>

<table
    style="
        width: 100%;
        border-collapse: collapse;
        border-bottom: 3px solid #2563eb;
        margin-bottom: 25px;
        padding-bottom: 12px;
    "
>
    <tr>
        <td
            style="
                width: 170px;
                vertical-align: middle;
                padding: 10px 15px 15px 0;
            "
        >
            <?php if ($logoDisponible): ?>
                <img
                    src="data:image/png;base64,<?= base64_encode(file_get_contents($rutaLogo)) ?>"
                    alt="Logo BodyTech"
                    style="
                        width: 145px;
                        height: auto;
                        display: block;
                    "
                >
            <?php else: ?>
                <div
                    style="
                        font-size: 22px;
                        font-weight: bold;
                        color: #1d4ed8;
                    "
                >
                   
                </div>
            <?php endif; ?>
        </td>

        <td
            style="
                text-align: right;
                vertical-align: middle;
                padding: 10px 0 15px 15px;
            "
        >
            <div
                style="
                    margin: 0;
                    color: #1d4ed8;
                    font-size: 23px;
                    font-weight: bold;
                "
            >
            
            </div>

            <div
                style="
                    margin-top: 7px;
                    color: #334155;
                    font-size: 13px;
                    font-weight: bold;
                "
            >
                <?= htmlspecialchars(
                    $tituloReporte,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </div>

            <div
                style="
                    margin-top: 15px;
                    font-size: 10px;
                    line-height: 1.8;
                    color: #475569;
                "
            >
                <strong>Fecha:</strong>
                <?= date('d/m/Y') ?>

                <br>

                <strong>Hora:</strong>
                <?= date('H:i') ?>

                <br>

                <strong>Código:</strong>
                <?= htmlspecialchars(
                    $codigoDocumento,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </div>
        </td>
    </tr>
</table>