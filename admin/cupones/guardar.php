<?php
require_once __DIR__ . '/../admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

/* =========================
   RECIBIR DATOS
========================= */

$codigo = trim($_POST['codigo'] ?? '');
$tipo_descuento = $_POST['tipo_descuento'] ?? '';
$valor_descuento = $_POST['valor_descuento'] ?? 0;
$monto_minimo = $_POST['monto_minimo'] ?? 0;
$fecha_inicio = $_POST['fecha_inicio'] ?? '';
$fecha_fin = $_POST['fecha_fin'] ?? '';
$usos_maximos = $_POST['usos_maximos'] ?? null;

/* =========================
   VALIDACIONES
========================= */

if (
    empty($codigo) ||
    empty($tipo_descuento) ||
    empty($valor_descuento) ||
    empty($fecha_inicio) ||
    empty($fecha_fin)
) {
    die("Todos los campos obligatorios deben completarse.");
}

$permitidos = ['porcentaje', 'fijo'];

if (!in_array($tipo_descuento, $permitidos)) {
    die("Tipo de descuento inválido.");
}

/* =========================
   VALIDAR DUPLICADO
========================= */

$stmt = $pdo->prepare("
    SELECT id
    FROM cupones
    WHERE codigo = ?
    LIMIT 1
");

$stmt->execute([$codigo]);

if ($stmt->fetch()) {
    die("Ya existe un cupón con ese código.");
}

/* =========================
   INSERTAR CUPÓN
========================= */

$stmt = $pdo->prepare("
    INSERT INTO cupones (
        codigo,
        tipo_descuento,
        valor_descuento,
        monto_minimo,
        fecha_inicio,
        fecha_fin,
        usos_maximos
    )
    VALUES (
        ?, ?, ?, ?, ?, ?, ?
    )
");

$stmt->execute([
    strtoupper($codigo),
    $tipo_descuento,
    $valor_descuento,
    $monto_minimo,
    $fecha_inicio,
    $fecha_fin,
    !empty($usos_maximos) ? $usos_maximos : null
]);


/* =========================
   NOTIFICAR NUEVO CUPÓN
========================= */

$stmtUsuarios = $pdo->query("
    SELECT id
    FROM usuarios
");

$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

foreach ($usuarios as $usuario) {

    $stmtNotificacion = $pdo->prepare("
        INSERT INTO notificaciones
        (
            usuario_id,
            titulo,
            mensaje,
            tipo
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?
        )
    ");

    $stmtNotificacion->execute([
        $usuario['id'],
        'Nuevo cupón disponible',
        'Se ha publicado un nuevo cupón: ' . strtoupper($codigo) . '. ¡Aprovecha esta promoción antes de que finalice!',
        'cupon'
    ]);
}
/* =========================
   REDIRECCIÓN
========================= */

header("Location: index.php");
exit;