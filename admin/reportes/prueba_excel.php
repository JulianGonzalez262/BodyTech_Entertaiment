<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;

$spreadsheet = new Spreadsheet();

$hoja = $spreadsheet->getActiveSheet();
$hoja->setCellValue('A1', 'BodyTech');
$hoja->setCellValue('A2', 'PhpSpreadsheet funciona correctamente');

echo '<h1>Prueba exitosa</h1>';
echo '<p>PhpSpreadsheet está instalado y PHP puede cargarlo correctamente.</p>';