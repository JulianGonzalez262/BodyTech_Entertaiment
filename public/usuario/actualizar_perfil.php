<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /bodytech/public/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Acceso no permitido.');
}

$nombres = trim($_POST['nombres'] ?? '');
$apellidos = trim($_POST['apellidos'] ?? '');

$telefono_principal = trim($_POST['telefono_principal'] ?? '');
$telefono_secundario = trim($_POST['telefono_secundario'] ?? '');

$direccion = trim($_POST['direccion'] ?? '');

$tipo_documento = trim($_POST['tipo_documento'] ?? '');
$numero_documento = trim($_POST['numero_documento'] ?? '');

/*
|--------------------------------------------------------------------------
| Compatibilidad con el sistema antiguo
|--------------------------------------------------------------------------
*/
$nombreCompleto = trim($nombres . ' ' . $apellidos);

$stmt = $pdo->prepare("
    UPDATE usuarios
    SET
        nombre = ?,
        nombres = ?,
        apellidos = ?,
        telefono_principal = ?,
        telefono_secundario = ?,
        direccion = ?,
        tipo_documento = ?,
        numero_documento = ?
    WHERE id = ?
");

$stmt->execute([
    $nombreCompleto,
    $nombres,
    $apellidos,
    $telefono_principal,
    $telefono_secundario,
    $direccion,
    $tipo_documento,
    $numero_documento,
    $_SESSION['usuario_id']
]);

/*
|--------------------------------------------------------------------------
| Actualizar sesión si existe el nombre guardado allí
|--------------------------------------------------------------------------
*/
if (isset($_SESSION['usuario']['nombre'])) {
    $_SESSION['usuario']['nombre'] = $nombreCompleto;
}

header('Location: mi_cuenta.php');
exit;