<?php

require_once __DIR__ . '/../admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;


/* =========================================================
   COLORES
========================================================= */

$colorPrincipal = 'FF0F172A';
$colorSecundario = 'FFE2E8F0';
$colorVerde = 'FFD1FAE5';
$colorVerdeTexto = 'FF166534';
$colorRojo = 'FFFEE2E2';
$colorRojoTexto = 'FF991B1B';
$colorAmarillo = 'FFFEF3C7';
$colorAmarilloTexto = 'FF92400E';
$colorAzulClaro = 'FFE0F2FE';
$colorAzulTexto = 'FF075985';
$colorBlanco = 'FFFFFFFF';
$colorTexto = 'FF1E293B';
$colorBorde = 'FFCBD5E1';


/* =========================================================
   FUNCIONES AUXILIARES
========================================================= */

function configurarHoja($hoja)
{
    $hoja->getDefaultRowDimension()->setRowHeight(20);

    $hoja->getPageSetup()
        ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
        ->setPaperSize(PageSetup::PAPERSIZE_LETTER)
        ->setFitToWidth(1)
        ->setFitToHeight(0);

    $hoja->getPageMargins()
        ->setTop(0.5)
        ->setRight(0.4)
        ->setBottom(0.5)
        ->setLeft(0.4);
}


function colocarTitulo(
    $hoja,
    $rango,
    $titulo,
    $colorPrincipal,
    $colorBlanco
) {
    $hoja->mergeCells($rango);

    $celdaInicial = explode(':', $rango)[0];

    $hoja->setCellValue($celdaInicial, $titulo);

    $hoja->getStyle($rango)->getFont()
        ->setBold(true)
        ->setSize(16)
        ->setColor(new Color($colorBlanco));

    $hoja->getStyle($rango)->getFill()
        ->setFillType(Fill::FILL_SOLID);

    $hoja->getStyle($rango)->getFill()
        ->getStartColor()
        ->setARGB($colorPrincipal);

    $hoja->getStyle($rango)->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setVertical(Alignment::VERTICAL_CENTER);

    $hoja->getRowDimension(1)->setRowHeight(32);
}


function colocarPeriodo($hoja, $fechaInicio, $fechaFin)
{
    $hoja->setCellValue('A3', 'Período del reporte');
    $hoja->setCellValue(
        'B3',
        $fechaInicio . ' al ' . $fechaFin
    );

    $hoja->getStyle('A3')->getFont()->setBold(true);
}


function aplicarEncabezado(
    $hoja,
    $rango,
    $colorPrincipal,
    $colorBlanco
) {
    $hoja->getStyle($rango)->getFont()
        ->setBold(true)
        ->setColor(new Color($colorBlanco));

    $hoja->getStyle($rango)->getFill()
        ->setFillType(Fill::FILL_SOLID);

    $hoja->getStyle($rango)->getFill()
        ->getStartColor()
        ->setARGB($colorPrincipal);

    $hoja->getStyle($rango)->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setVertical(Alignment::VERTICAL_CENTER);

    $hoja->getStyle($rango)->getBorders()
        ->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN)
        ->getColor()
        ->setARGB('FF64748B');
}


function aplicarBordes($hoja, $rango, $colorBorde)
{
    $hoja->getStyle($rango)->getBorders()
        ->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN)
        ->getColor()
        ->setARGB($colorBorde);
}


function aplicarEstado(
    $hoja,
    $celda,
    $estado,
    $colorVerde,
    $colorVerdeTexto,
    $colorRojo,
    $colorRojoTexto,
    $colorAmarillo,
    $colorAmarilloTexto,
    $colorAzulClaro,
    $colorAzulTexto
) {
    $estadoNormalizado = strtolower(trim((string)$estado));

    if (
        $estadoNormalizado === 'aprobado' ||
        $estadoNormalizado === 'activo' ||
        $estadoNormalizado === 'entregado' ||
        $estadoNormalizado === 'completado'
    ) {
        $hoja->getStyle($celda)->getFill()
            ->setFillType(Fill::FILL_SOLID);

        $hoja->getStyle($celda)->getFill()
            ->getStartColor()
            ->setARGB($colorVerde);

        $hoja->getStyle($celda)->getFont()
            ->setBold(true)
            ->setColor(new Color($colorVerdeTexto));
    }

    elseif (
        $estadoNormalizado === 'rechazado' ||
        $estadoNormalizado === 'cancelado' ||
        $estadoNormalizado === 'bloqueado'
    ) {
        $hoja->getStyle($celda)->getFill()
            ->setFillType(Fill::FILL_SOLID);

        $hoja->getStyle($celda)->getFill()
            ->getStartColor()
            ->setARGB($colorRojo);

        $hoja->getStyle($celda)->getFont()
            ->setBold(true)
            ->setColor(new Color($colorRojoTexto));
    }

    elseif (
        $estadoNormalizado === 'pendiente' ||
        $estadoNormalizado === 'en proceso'
    ) {
        $hoja->getStyle($celda)->getFill()
            ->setFillType(Fill::FILL_SOLID);

        $hoja->getStyle($celda)->getFill()
            ->getStartColor()
            ->setARGB($colorAmarillo);

        $hoja->getStyle($celda)->getFont()
            ->setBold(true)
            ->setColor(new Color($colorAmarilloTexto));
    }

    else {
        $hoja->getStyle($celda)->getFill()
            ->setFillType(Fill::FILL_SOLID);

        $hoja->getStyle($celda)->getFill()
            ->getStartColor()
            ->setARGB($colorAzulClaro);

        $hoja->getStyle($celda)->getFont()
            ->setBold(true)
            ->setColor(new Color($colorAzulTexto));
    }
}


/* =========================================================
   VALIDACIÓN DE FECHAS
========================================================= */

$fechaInicio = $_GET['fecha_inicio'] ?? '';
$fechaFin = $_GET['fecha_fin'] ?? '';

if (!$fechaInicio || !$fechaFin) {
    exit('Debes seleccionar una fecha de inicio y una fecha de fin.');
}

$inicio = DateTime::createFromFormat('Y-m-d', $fechaInicio);
$fin = DateTime::createFromFormat('Y-m-d', $fechaFin);

if (!$inicio || !$fin) {
    exit('El formato de las fechas no es válido.');
}

if (
    $inicio->format('Y-m-d') !== $fechaInicio ||
    $fin->format('Y-m-d') !== $fechaFin
) {
    exit('Una de las fechas no tiene un formato válido.');
}

if ($inicio > $fin) {
    exit('La fecha de inicio no puede ser mayor que la fecha final.');
}

$inicioSQL = $fechaInicio . ' 00:00:00';
$finSQL = $fechaFin . ' 23:59:59';


/* =========================================================
   RESUMEN GENERAL
========================================================= */

/* ÓRDENES */

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM ordenes
    WHERE created_at BETWEEN ? AND ?
");

$stmt->execute([$inicioSQL, $finSQL]);

$totalOrdenes = (int)$stmt->fetchColumn();


/* PAGOS MARKETPLACE */

$stmt = $pdo->prepare("
    SELECT estado, COUNT(*) AS total
    FROM pagos_productos
    WHERE created_at BETWEEN ? AND ?
    GROUP BY estado
");

$stmt->execute([$inicioSQL, $finSQL]);

$pagosPorEstado = [];

while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $pagosPorEstado[$fila['estado']] = (int)$fila['total'];
}

$pagosAprobados = $pagosPorEstado['aprobado'] ?? 0;
$pagosPendientes = $pagosPorEstado['pendiente'] ?? 0;
$pagosRechazados = $pagosPorEstado['rechazado'] ?? 0;


/* INGRESOS MARKETPLACE */

$stmt = $pdo->prepare("
    SELECT IFNULL(SUM(monto), 0)
    FROM pagos_productos
    WHERE estado = 'aprobado'
      AND created_at BETWEEN ? AND ?
");

$stmt->execute([$inicioSQL, $finSQL]);

$ingresosMarketplace = (float)$stmt->fetchColumn();


/* UNIDADES VENDIDAS */

$stmt = $pdo->prepare("
    SELECT IFNULL(SUM(oi.cantidad), 0)
    FROM orden_items oi
    INNER JOIN ordenes o
        ON oi.orden_id = o.id
    WHERE o.created_at BETWEEN ? AND ?
");

$stmt->execute([$inicioSQL, $finSQL]);

$productosVendidos = (int)$stmt->fetchColumn();


/* AFILIACIONES APROBADAS */

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM pagos
    WHERE estado = 'aprobado'
      AND created_at BETWEEN ? AND ?
");

$stmt->execute([$inicioSQL, $finSQL]);

$afiliacionesAprobadas = (int)$stmt->fetchColumn();


/* INGRESOS AFILIACIONES */

$stmt = $pdo->prepare("
    SELECT IFNULL(SUM(monto), 0)
    FROM pagos
    WHERE estado = 'aprobado'
      AND created_at BETWEEN ? AND ?
");

$stmt->execute([$inicioSQL, $finSQL]);

$ingresosAfiliaciones = (float)$stmt->fetchColumn();


/* INGRESOS TOTALES */

$ingresosTotales =
    $ingresosMarketplace +
    $ingresosAfiliaciones;


/* TICKET PROMEDIO */

$ticketPromedio = $totalOrdenes > 0
    ? $ingresosMarketplace / $totalOrdenes
    : 0;


/* PORCENTAJES */

$porcentajeAprobados = $totalOrdenes > 0
    ? ($pagosAprobados / $totalOrdenes) * 100
    : 0;

$porcentajePendientes = $totalOrdenes > 0
    ? ($pagosPendientes / $totalOrdenes) * 100
    : 0;

$porcentajeRechazados = $totalOrdenes > 0
    ? ($pagosRechazados / $totalOrdenes) * 100
    : 0;


/* =========================================================
   VENTAS
========================================================= */

$stmt = $pdo->prepare("
    SELECT
        o.id,
        o.referencia,
        u.nombre AS cliente,
        o.total,
        o.metodo_pago,
        o.estado_pago,
        o.estado_orden,
        o.transportadora,
        o.guia_envio,
        o.fecha_envio,
        o.receptor_nombre,
        o.receptor_telefono,
        o.departamento,
        o.ciudad,
        o.direccion_envio,
        o.referencia_direccion,
        o.notas_pedido,
        o.direccion_confirmada,
        o.tracking_url,
        o.descuento,
        o.created_at
    FROM ordenes o
    INNER JOIN usuarios u
        ON o.usuario_id = u.id
    WHERE o.created_at BETWEEN ? AND ?
    ORDER BY o.created_at DESC
");

$stmt->execute([$inicioSQL, $finSQL]);

$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================================================
   DETALLE DE PRODUCTOS POR ORDEN
========================================================= */

$stmt = $pdo->prepare("
    SELECT
        oi.id,
        oi.orden_id,
        o.referencia AS referencia_orden,
        u.nombre AS cliente,
        p.nombre AS producto,
        p.categoria,
        oi.cantidad,
        oi.precio_unitario,
        oi.subtotal,
        o.created_at
    FROM orden_items oi
    INNER JOIN ordenes o
        ON oi.orden_id = o.id
    INNER JOIN usuarios u
        ON o.usuario_id = u.id
    INNER JOIN productos p
        ON oi.producto_id = p.id
    WHERE o.created_at BETWEEN ? AND ?
    ORDER BY o.created_at DESC, oi.id ASC
");

$stmt->execute([$inicioSQL, $finSQL]);

$detalleProductos = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================================================
   PAGOS MARKETPLACE
========================================================= */

$stmt = $pdo->prepare("
    SELECT
        pp.id,
        pp.orden_id,
        u.nombre AS cliente,
        pp.monto,
        pp.metodo_pago,
        pp.referencia_pago,
        pp.estado,
        pp.gateway,
        pp.created_at
    FROM pagos_productos pp
    INNER JOIN usuarios u
        ON pp.usuario_id = u.id
    WHERE pp.created_at BETWEEN ? AND ?
    ORDER BY pp.created_at DESC
");

$stmt->execute([$inicioSQL, $finSQL]);

$pagosMarketplace = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================================================
   PRODUCTOS MÁS VENDIDOS
========================================================= */

$stmt = $pdo->prepare("
    SELECT
        p.id,
        p.nombre,
        p.categoria,
        SUM(oi.cantidad) AS unidades,
        SUM(oi.subtotal) AS ingresos
    FROM orden_items oi
    INNER JOIN ordenes o
        ON oi.orden_id = o.id
    INNER JOIN productos p
        ON oi.producto_id = p.id
    WHERE o.created_at BETWEEN ? AND ?
    GROUP BY
        p.id,
        p.nombre,
        p.categoria
    ORDER BY
        unidades DESC,
        ingresos DESC
");

$stmt->execute([$inicioSQL, $finSQL]);

$productosMasVendidos = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================================================
   AFILIACIONES
========================================================= */

$stmt = $pdo->prepare("
    SELECT
        id,
        afiliado_id,
        usuario_id,
        tipo_membresia,
        monto,
        metodo,
        estado,
        gateway,
        gateway_id,
        referencia,
        created_at
    FROM pagos
    WHERE created_at BETWEEN ? AND ?
    ORDER BY created_at DESC
");

$stmt->execute([$inicioSQL, $finSQL]);

$pagosAfiliaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================================================
   CREAR EXCEL
========================================================= */

$spreadsheet = new Spreadsheet();


/* =========================================================
   HOJA 1 — RESUMEN
========================================================= */

$hoja = $spreadsheet->getActiveSheet();

$hoja->setTitle('Resumen');

configurarHoja($hoja);

colocarTitulo(
    $hoja,
    'A1:D1',
    'BODYTECH — CENTRO DE REPORTES ADMINISTRATIVOS',
    $colorPrincipal,
    $colorBlanco
);

colocarPeriodo(
    $hoja,
    $fechaInicio,
    $fechaFin
);


/* INDICADORES */

$hoja->setCellValue('A5', 'INDICADOR');
$hoja->setCellValue('B5', 'VALOR');
$hoja->setCellValue('C5', 'TIPO');
$hoja->setCellValue('D5', 'OBSERVACIÓN');

aplicarEncabezado(
    $hoja,
    'A5:D5',
    $colorPrincipal,
    $colorBlanco
);

$resumen = [
    ['Órdenes', $totalOrdenes, 'Cantidad', 'Órdenes creadas en el período'],
    ['Pagos Marketplace aprobados', $pagosAprobados, 'Cantidad', 'Pagos con estado aprobado'],
    ['Pagos Marketplace pendientes', $pagosPendientes, 'Cantidad', 'Pagos pendientes'],
    ['Pagos Marketplace rechazados', $pagosRechazados, 'Cantidad', 'Pagos rechazados'],
    ['Ingresos Marketplace', $ingresosMarketplace, 'Dinero', 'Pagos Marketplace aprobados'],
    ['Unidades vendidas', $productosVendidos, 'Cantidad', 'Unidades registradas en órdenes'],
    ['Afiliaciones aprobadas', $afiliacionesAprobadas, 'Cantidad', 'Pagos de afiliación aprobados'],
    ['Ingresos por afiliaciones', $ingresosAfiliaciones, 'Dinero', 'Pagos de afiliación aprobados'],
    ['Ingresos totales', $ingresosTotales, 'Dinero', 'Marketplace + afiliaciones'],
    ['Ticket promedio', $ticketPromedio, 'Dinero', 'Ingreso Marketplace / órdenes'],
];

$fila = 6;

foreach ($resumen as $dato) {

    $hoja->setCellValue('A' . $fila, $dato[0]);
    $hoja->setCellValue('B' . $fila, $dato[1]);
    $hoja->setCellValue('C' . $fila, $dato[2]);
    $hoja->setCellValue('D' . $fila, $dato[3]);

    $fila++;
}

$hoja->getStyle('B10')->getNumberFormat()
    ->setFormatCode('$#,##0.00');

$hoja->getStyle('B13:B15')->getNumberFormat()
    ->setFormatCode('$#,##0.00');


/* ESTADOS */

aplicarEstado(
    $hoja,
    'B7',
    'aprobado',
    $colorVerde,
    $colorVerdeTexto,
    $colorRojo,
    $colorRojoTexto,
    $colorAmarillo,
    $colorAmarilloTexto,
    $colorAzulClaro,
    $colorAzulTexto
);


/* PORCENTAJES */

$hoja->setCellValue('A17', 'ANÁLISIS DE PAGOS');
$hoja->setCellValue('A18', 'Aprobados');
$hoja->setCellValue('B18', $porcentajeAprobados / 100);

$hoja->setCellValue('A19', 'Pendientes');
$hoja->setCellValue('B19', $porcentajePendientes / 100);

$hoja->setCellValue('A20', 'Rechazados');
$hoja->setCellValue('B20', $porcentajeRechazados / 100);

$hoja->getStyle('B18:B20')->getNumberFormat()
    ->setFormatCode('0.00%');

$hoja->getStyle('A17:B17')->getFont()
    ->setBold(true);

$hoja->getStyle('A17:B17')->getFill()
    ->setFillType(Fill::FILL_SOLID);

$hoja->getStyle('A17:B17')->getFill()
    ->getStartColor()
    ->setARGB($colorSecundario);


/* RESALTAR INGRESOS TOTALES */

$hoja->getStyle('A14:D14')->getFont()
    ->setBold(true);

$hoja->getStyle('A14:D14')->getFill()
    ->setFillType(Fill::FILL_SOLID);

$hoja->getStyle('A14:D14')->getFill()
    ->getStartColor()
    ->setARGB($colorVerde);


/* BORDES */

aplicarBordes(
    $hoja,
    'A5:D15',
    $colorBorde
);

aplicarBordes(
    $hoja,
    'A18:B20',
    $colorBorde
);


/* ANCHOS */

$hoja->getColumnDimension('A')->setWidth(38);
$hoja->getColumnDimension('B')->setWidth(22);
$hoja->getColumnDimension('C')->setWidth(16);
$hoja->getColumnDimension('D')->setWidth(42);


/* =========================================================
   HOJA 2 — VENTAS
========================================================= */

$ventasSheet = $spreadsheet->createSheet();

$ventasSheet->setTitle('Ventas');

configurarHoja($ventasSheet);

colocarTitulo(
    $ventasSheet,
    'A1:U1',
    'BODYTECH — DETALLE COMPLETO DE VENTAS',
    $colorPrincipal,
    $colorBlanco
);

colocarPeriodo(
    $ventasSheet,
    $fechaInicio,
    $fechaFin
);

$encabezadosVentas = [
    'ID',
    'Referencia',
    'Cliente',
    'Total',
    'Método de pago',
    'Estado de pago',
    'Estado de orden',
    'Transportadora',
    'Guía de envío',
    'Fecha de envío',
    'Receptor',
    'Teléfono receptor',
    'Departamento',
    'Ciudad',
    'Dirección',
    'Referencia dirección',
    'Notas',
    'Dirección confirmada',
    'Tracking URL',
    'Descuento',
    'Fecha de creación'
];

$columna = 'A';

foreach ($encabezadosVentas as $encabezado) {

    $ventasSheet->setCellValue(
        $columna . '5',
        $encabezado
    );

    $columna++;
}

aplicarEncabezado(
    $ventasSheet,
    'A5:U5',
    $colorPrincipal,
    $colorBlanco
);

$fila = 6;

foreach ($ventas as $venta) {

    $valores = [
        $venta['id'],
        $venta['referencia'],
        $venta['cliente'],
        (float)$venta['total'],
        $venta['metodo_pago'],
        $venta['estado_pago'],
        $venta['estado_orden'],
        $venta['transportadora'],
        $venta['guia_envio'],
        $venta['fecha_envio'],
        $venta['receptor_nombre'],
        $venta['receptor_telefono'],
        $venta['departamento'],
        $venta['ciudad'],
        $venta['direccion_envio'],
        $venta['referencia_direccion'],
        $venta['notas_pedido'],
        ((int)$venta['direccion_confirmada'] === 1 ? 'Sí' : 'No'),
        $venta['tracking_url'],
        (float)$venta['descuento'],
        $venta['created_at']
    ];

    $columna = 'A';

    foreach ($valores as $valor) {

        $ventasSheet->setCellValue(
            $columna . $fila,
            $valor
        );

        $columna++;
    }

    $fila++;
}

$ultimaFilaVentas = max(5, $fila - 1);

if ($ultimaFilaVentas >= 6) {

    $ventasSheet->getStyle(
        'D6:D' . $ultimaFilaVentas
    )->getNumberFormat()
        ->setFormatCode('$#,##0.00');

    $ventasSheet->getStyle(
        'T6:T' . $ultimaFilaVentas
    )->getNumberFormat()
        ->setFormatCode('$#,##0.00');

    $ventasSheet->getStyle(
        'J6:J' . $ultimaFilaVentas
    )->getNumberFormat()
        ->setFormatCode('yyyy-mm-dd hh:mm');

    $ventasSheet->getStyle(
        'U6:U' . $ultimaFilaVentas
    )->getNumberFormat()
        ->setFormatCode('yyyy-mm-dd hh:mm');

    aplicarBordes(
        $ventasSheet,
        'A5:U' . $ultimaFilaVentas,
        $colorBorde
    );

    $ventasSheet->setAutoFilter(
        'A5:U' . $ultimaFilaVentas
    );

    for ($i = 6; $i <= $ultimaFilaVentas; $i++) {

        aplicarEstado(
            $ventasSheet,
            'F' . $i,
            $ventasSheet->getCell('F' . $i)->getValue(),
            $colorVerde,
            $colorVerdeTexto,
            $colorRojo,
            $colorRojoTexto,
            $colorAmarillo,
            $colorAmarilloTexto,
            $colorAzulClaro,
            $colorAzulTexto
        );

        aplicarEstado(
            $ventasSheet,
            'G' . $i,
            $ventasSheet->getCell('G' . $i)->getValue(),
            $colorVerde,
            $colorVerdeTexto,
            $colorRojo,
            $colorRojoTexto,
            $colorAmarillo,
            $colorAmarilloTexto,
            $colorAzulClaro,
            $colorAzulTexto
        );
    }
}

$ventasSheet->freezePane('A6');

$anchosVentas = [
    'A' => 9,
    'B' => 20,
    'C' => 28,
    'D' => 16,
    'E' => 20,
    'F' => 18,
    'G' => 20,
    'H' => 20,
    'I' => 20,
    'J' => 21,
    'K' => 24,
    'L' => 20,
    'M' => 18,
    'N' => 20,
    'O' => 38,
    'P' => 30,
    'Q' => 35,
    'R' => 20,
    'S' => 40,
    'T' => 16,
    'U' => 22
];

foreach ($anchosVentas as $col => $ancho) {
    $ventasSheet->getColumnDimension($col)->setWidth($ancho);
}


/* =========================================================
   HOJA 3 — DETALLE PRODUCTOS
========================================================= */

$detalleSheet = $spreadsheet->createSheet();

$detalleSheet->setTitle('Detalle productos');

configurarHoja($detalleSheet);

colocarTitulo(
    $detalleSheet,
    'A1:J1',
    'BODYTECH — DETALLE DE PRODUCTOS VENDIDOS',
    $colorPrincipal,
    $colorBlanco
);

colocarPeriodo(
    $detalleSheet,
    $fechaInicio,
    $fechaFin
);

$encabezadosDetalle = [
    'ID',
    'Orden',
    'Referencia',
    'Cliente',
    'Producto',
    'Categoría',
    'Cantidad',
    'Precio unitario',
    'Subtotal',
    'Fecha'
];

$columna = 'A';

foreach ($encabezadosDetalle as $encabezado) {

    $detalleSheet->setCellValue(
        $columna . '5',
        $encabezado
    );

    $columna++;
}

aplicarEncabezado(
    $detalleSheet,
    'A5:J5',
    $colorPrincipal,
    $colorBlanco
);

$fila = 6;

foreach ($detalleProductos as $detalle) {

    $detalleSheet->setCellValue(
        'A' . $fila,
        $detalle['id']
    );

    $detalleSheet->setCellValue(
        'B' . $fila,
        $detalle['orden_id']
    );

    $detalleSheet->setCellValue(
        'C' . $fila,
        $detalle['referencia_orden']
    );

    $detalleSheet->setCellValue(
        'D' . $fila,
        $detalle['cliente']
    );

    $detalleSheet->setCellValue(
        'E' . $fila,
        $detalle['producto']
    );

    $detalleSheet->setCellValue(
        'F' . $fila,
        $detalle['categoria']
    );

    $detalleSheet->setCellValue(
        'G' . $fila,
        (int)$detalle['cantidad']
    );

    $detalleSheet->setCellValue(
        'H' . $fila,
        (float)$detalle['precio_unitario']
    );

    $detalleSheet->setCellValue(
        'I' . $fila,
        (float)$detalle['subtotal']
    );

    $detalleSheet->setCellValue(
        'J' . $fila,
        $detalle['created_at']
    );

    $fila++;
}

$ultimaFilaDetalle = max(5, $fila - 1);

if ($ultimaFilaDetalle >= 6) {

    $detalleSheet->getStyle(
        'H6:I' . $ultimaFilaDetalle
    )->getNumberFormat()
        ->setFormatCode('$#,##0.00');

    $detalleSheet->getStyle(
        'J6:J' . $ultimaFilaDetalle
    )->getNumberFormat()
        ->setFormatCode('yyyy-mm-dd hh:mm');

    aplicarBordes(
        $detalleSheet,
        'A5:J' . $ultimaFilaDetalle,
        $colorBorde
    );

    $detalleSheet->setAutoFilter(
        'A5:J' . $ultimaFilaDetalle
    );
}

$detalleSheet->freezePane('A6');

$detalleSheet->getColumnDimension('A')->setWidth(9);
$detalleSheet->getColumnDimension('B')->setWidth(11);
$detalleSheet->getColumnDimension('C')->setWidth(20);
$detalleSheet->getColumnDimension('D')->setWidth(28);
$detalleSheet->getColumnDimension('E')->setWidth(38);
$detalleSheet->getColumnDimension('F')->setWidth(22);
$detalleSheet->getColumnDimension('G')->setWidth(14);
$detalleSheet->getColumnDimension('H')->setWidth(18);
$detalleSheet->getColumnDimension('I')->setWidth(18);
$detalleSheet->getColumnDimension('J')->setWidth(22);


/* =========================================================
   HOJA 4 — PAGOS MARKETPLACE
========================================================= */

$pagosSheet = $spreadsheet->createSheet();

$pagosSheet->setTitle('Pagos Marketplace');

configurarHoja($pagosSheet);

colocarTitulo(
    $pagosSheet,
    'A1:I1',
    'BODYTECH — PAGOS DEL MARKETPLACE',
    $colorPrincipal,
    $colorBlanco
);

colocarPeriodo(
    $pagosSheet,
    $fechaInicio,
    $fechaFin
);

$encabezadosPagos = [
    'ID',
    'Orden',
    'Cliente',
    'Monto',
    'Método de pago',
    'Referencia',
    'Estado',
    'Gateway',
    'Fecha'
];

$columna = 'A';

foreach ($encabezadosPagos as $encabezado) {

    $pagosSheet->setCellValue(
        $columna . '5',
        $encabezado
    );

    $columna++;
}

aplicarEncabezado(
    $pagosSheet,
    'A5:I5',
    $colorPrincipal,
    $colorBlanco
);

$fila = 6;

foreach ($pagosMarketplace as $pago) {

    $pagosSheet->setCellValue(
        'A' . $fila,
        $pago['id']
    );

    $pagosSheet->setCellValue(
        'B' . $fila,
        $pago['orden_id']
    );

    $pagosSheet->setCellValue(
        'C' . $fila,
        $pago['cliente']
    );

    $pagosSheet->setCellValue(
        'D' . $fila,
        (float)$pago['monto']
    );

    $pagosSheet->setCellValue(
        'E' . $fila,
        $pago['metodo_pago']
    );

    $pagosSheet->setCellValue(
        'F' . $fila,
        $pago['referencia_pago']
    );

    $pagosSheet->setCellValue(
        'G' . $fila,
        $pago['estado']
    );

    $pagosSheet->setCellValue(
        'H' . $fila,
        $pago['gateway']
    );

    $pagosSheet->setCellValue(
        'I' . $fila,
        $pago['created_at']
    );

    $fila++;
}

$ultimaFilaPagos = max(5, $fila - 1);

if ($ultimaFilaPagos >= 6) {

    $pagosSheet->getStyle(
        'D6:D' . $ultimaFilaPagos
    )->getNumberFormat()
        ->setFormatCode('$#,##0.00');

    $pagosSheet->getStyle(
        'I6:I' . $ultimaFilaPagos
    )->getNumberFormat()
        ->setFormatCode('yyyy-mm-dd hh:mm');

    aplicarBordes(
        $pagosSheet,
        'A5:I' . $ultimaFilaPagos,
        $colorBorde
    );

    $pagosSheet->setAutoFilter(
        'A5:I' . $ultimaFilaPagos
    );

    for ($i = 6; $i <= $ultimaFilaPagos; $i++) {

        aplicarEstado(
            $pagosSheet,
            'G' . $i,
            $pagosSheet->getCell('G' . $i)->getValue(),
            $colorVerde,
            $colorVerdeTexto,
            $colorRojo,
            $colorRojoTexto,
            $colorAmarillo,
            $colorAmarilloTexto,
            $colorAzulClaro,
            $colorAzulTexto
        );
    }
}

$pagosSheet->freezePane('A6');

$pagosSheet->getColumnDimension('A')->setWidth(9);
$pagosSheet->getColumnDimension('B')->setWidth(12);
$pagosSheet->getColumnDimension('C')->setWidth(28);
$pagosSheet->getColumnDimension('D')->setWidth(18);
$pagosSheet->getColumnDimension('E')->setWidth(20);
$pagosSheet->getColumnDimension('F')->setWidth(25);
$pagosSheet->getColumnDimension('G')->setWidth(17);
$pagosSheet->getColumnDimension('H')->setWidth(17);
$pagosSheet->getColumnDimension('I')->setWidth(22);


/* =========================================================
   HOJA 5 — PRODUCTOS VENDIDOS
========================================================= */

$productosSheet = $spreadsheet->createSheet();

$productosSheet->setTitle('Productos vendidos');

configurarHoja($productosSheet);

colocarTitulo(
    $productosSheet,
    'A1:E1',
    'BODYTECH — RANKING DE PRODUCTOS',
    $colorPrincipal,
    $colorBlanco
);

colocarPeriodo(
    $productosSheet,
    $fechaInicio,
    $fechaFin
);

$encabezadosProductos = [
    'ID',
    'Producto',
    'Categoría',
    'Unidades vendidas',
    'Ingresos'
];

$columna = 'A';

foreach ($encabezadosProductos as $encabezado) {

    $productosSheet->setCellValue(
        $columna . '5',
        $encabezado
    );

    $columna++;
}

aplicarEncabezado(
    $productosSheet,
    'A5:E5',
    $colorPrincipal,
    $colorBlanco
);

$fila = 6;

foreach ($productosMasVendidos as $producto) {

    $productosSheet->setCellValue(
        'A' . $fila,
        $producto['id']
    );

    $productosSheet->setCellValue(
        'B' . $fila,
        $producto['nombre']
    );

    $productosSheet->setCellValue(
        'C' . $fila,
        $producto['categoria']
    );

    $productosSheet->setCellValue(
        'D' . $fila,
        (int)$producto['unidades']
    );

    $productosSheet->setCellValue(
        'E' . $fila,
        (float)$producto['ingresos']
    );

    $fila++;
}

$ultimaFilaProductos = max(5, $fila - 1);

if ($ultimaFilaProductos >= 6) {

    $productosSheet->getStyle(
        'E6:E' . $ultimaFilaProductos
    )->getNumberFormat()
        ->setFormatCode('$#,##0.00');

    aplicarBordes(
        $productosSheet,
        'A5:E' . $ultimaFilaProductos,
        $colorBorde
    );

    $productosSheet->setAutoFilter(
        'A5:E' . $ultimaFilaProductos
    );
}

$productosSheet->freezePane('A6');

$productosSheet->getColumnDimension('A')->setWidth(9);
$productosSheet->getColumnDimension('B')->setWidth(40);
$productosSheet->getColumnDimension('C')->setWidth(24);
$productosSheet->getColumnDimension('D')->setWidth(20);
$productosSheet->getColumnDimension('E')->setWidth(20);


/* =========================================================
   HOJA 6 — AFILIACIONES
========================================================= */

$afiliacionesSheet = $spreadsheet->createSheet();

$afiliacionesSheet->setTitle('Afiliaciones');

configurarHoja($afiliacionesSheet);

colocarTitulo(
    $afiliacionesSheet,
    'A1:K1',
    'BODYTECH — PAGOS Y INGRESOS DE AFILIACIONES',
    $colorPrincipal,
    $colorBlanco
);

colocarPeriodo(
    $afiliacionesSheet,
    $fechaInicio,
    $fechaFin
);


/* RESUMEN */

$afiliacionesSheet->setCellValue(
    'A5',
    'RESUMEN FINANCIERO'
);

$afiliacionesSheet->getStyle('A5:B5')
    ->getFill()
    ->setFillType(Fill::FILL_SOLID);

$afiliacionesSheet->getStyle('A5:B5')
    ->getFill()
    ->getStartColor()
    ->setARGB($colorSecundario);

$afiliacionesSheet->getStyle('A5:B5')
    ->getFont()
    ->setBold(true);

$afiliacionesSheet->setCellValue(
    'A6',
    'Afiliaciones aprobadas'
);

$afiliacionesSheet->setCellValue(
    'B6',
    $afiliacionesAprobadas
);

$afiliacionesSheet->setCellValue(
    'A7',
    'Ingresos por afiliaciones'
);

$afiliacionesSheet->setCellValue(
    'B7',
    $ingresosAfiliaciones
);

$afiliacionesSheet->getStyle('B7')
    ->getNumberFormat()
    ->setFormatCode('$#,##0.00');

$afiliacionesSheet->getStyle('A7:B7')
    ->getFont()
    ->setBold(true);

$afiliacionesSheet->getStyle('A7:B7')
    ->getFill()
    ->setFillType(Fill::FILL_SOLID);

$afiliacionesSheet->getStyle('A7:B7')
    ->getFill()
    ->getStartColor()
    ->setARGB($colorVerde);


/* DETALLE */

$afiliacionesSheet->setCellValue(
    'A10',
    'ID'
);

$afiliacionesSheet->setCellValue(
    'B10',
    'Afiliado ID'
);

$afiliacionesSheet->setCellValue(
    'C10',
    'Usuario ID'
);

$afiliacionesSheet->setCellValue(
    'D10',
    'Tipo de membresía'
);

$afiliacionesSheet->setCellValue(
    'E10',
    'Monto'
);

$afiliacionesSheet->setCellValue(
    'F10',
    'Método'
);

$afiliacionesSheet->setCellValue(
    'G10',
    'Estado'
);

$afiliacionesSheet->setCellValue(
    'H10',
    'Gateway'
);

$afiliacionesSheet->setCellValue(
    'I10',
    'Gateway ID'
);

$afiliacionesSheet->setCellValue(
    'J10',
    'Referencia'
);

$afiliacionesSheet->setCellValue(
    'K10',
    'Fecha'
);

aplicarEncabezado(
    $afiliacionesSheet,
    'A10:K10',
    $colorPrincipal,
    $colorBlanco
);

$fila = 11;

foreach ($pagosAfiliaciones as $pago) {

    $afiliacionesSheet->setCellValue(
        'A' . $fila,
        $pago['id']
    );

    $afiliacionesSheet->setCellValue(
        'B' . $fila,
        $pago['afiliado_id']
    );

    $afiliacionesSheet->setCellValue(
        'C' . $fila,
        $pago['usuario_id']
    );

    $afiliacionesSheet->setCellValue(
        'D' . $fila,
        $pago['tipo_membresia']
    );

    $afiliacionesSheet->setCellValue(
        'E' . $fila,
        (float)$pago['monto']
    );

    $afiliacionesSheet->setCellValue(
        'F' . $fila,
        $pago['metodo']
    );

    $afiliacionesSheet->setCellValue(
        'G' . $fila,
        $pago['estado']
    );

    $afiliacionesSheet->setCellValue(
        'H' . $fila,
        $pago['gateway']
    );

    $afiliacionesSheet->setCellValue(
        'I' . $fila,
        $pago['gateway_id']
    );

    $afiliacionesSheet->setCellValue(
        'J' . $fila,
        $pago['referencia']
    );

    $afiliacionesSheet->setCellValue(
        'K' . $fila,
        $pago['created_at']
    );

    $fila++;
}

$ultimaFilaAfiliaciones = max(10, $fila - 1);

if ($ultimaFilaAfiliaciones >= 11) {

    $afiliacionesSheet->getStyle(
        'E11:E' . $ultimaFilaAfiliaciones
    )->getNumberFormat()
        ->setFormatCode('$#,##0.00');

    $afiliacionesSheet->getStyle(
        'K11:K' . $ultimaFilaAfiliaciones
    )->getNumberFormat()
        ->setFormatCode('yyyy-mm-dd hh:mm');

    aplicarBordes(
        $afiliacionesSheet,
        'A10:K' . $ultimaFilaAfiliaciones,
        $colorBorde
    );

    $afiliacionesSheet->setAutoFilter(
        'A10:K' . $ultimaFilaAfiliaciones
    );

    for ($i = 11; $i <= $ultimaFilaAfiliaciones; $i++) {

        aplicarEstado(
            $afiliacionesSheet,
            'G' . $i,
            $afiliacionesSheet->getCell('G' . $i)->getValue(),
            $colorVerde,
            $colorVerdeTexto,
            $colorRojo,
            $colorRojoTexto,
            $colorAmarillo,
            $colorAmarilloTexto,
            $colorAzulClaro,
            $colorAzulTexto
        );
    }
}

$afiliacionesSheet->freezePane('A11');

$anchosAfiliaciones = [
    'A' => 9,
    'B' => 13,
    'C' => 13,
    'D' => 28,
    'E' => 18,
    'F' => 18,
    'G' => 16,
    'H' => 17,
    'I' => 22,
    'J' => 24,
    'K' => 22
];

foreach ($anchosAfiliaciones as $col => $ancho) {
    $afiliacionesSheet->getColumnDimension($col)->setWidth($ancho);
}


/* =========================================================
   ESTILO GENERAL
========================================================= */

foreach ($spreadsheet->getWorksheetIterator() as $hojaActual) {

    $dimension = $hojaActual->calculateWorksheetDimension();

    $hojaActual->getStyle($dimension)
        ->getFont()
        ->setName('Arial')
        ->setSize(10);

    $hojaActual->getStyle($dimension)
        ->getAlignment()
        ->setVertical(Alignment::VERTICAL_CENTER);
}


/* =========================================================
   HOJA RESUMEN ACTIVA
========================================================= */

$spreadsheet->setActiveSheetIndex(0);


/* =========================================================
   DESCARGA
========================================================= */

$nombreArchivo =
    'Reporte_BodyTech_' .
    $fechaInicio .
    '_al_' .
    $fechaFin .
    '.xlsx';

header(
    'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
);

header(
    'Content-Disposition: attachment; filename="' .
    $nombreArchivo .
    '"'
);

header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);

$writer->save('php://output');

exit;