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

if (!isset($_FILES['foto'])) {
    die('No se recibió ninguna imagen.');
}

$archivo = $_FILES['foto'];

if ($archivo['error'] !== UPLOAD_ERR_OK) {
    die('Error al subir la imagen.');
}

$permitidos = ['jpg', 'jpeg', 'png', 'webp'];

$extension = strtolower(
    pathinfo(
        $archivo['name'],
        PATHINFO_EXTENSION
    )
);

if (!in_array($extension, $permitidos)) {
    die('Formato no permitido.');
}

if ($archivo['size'] > 3 * 1024 * 1024) {
    die('La imagen supera los 3 MB.');
}

/*
|--------------------------------------------------------------------------
| Generar nombre único
|--------------------------------------------------------------------------
*/

$nombreArchivo =
    'perfil_' .
    $_SESSION['usuario_id'] .
    '_' .
    time() .
    '.' .
    $extension;

$rutaFisica =
    $_SERVER['DOCUMENT_ROOT'] .
    '/bodytech/public/uploads/perfiles/' .
    $nombreArchivo;

$rutaBD =
    '/bodytech/public/uploads/perfiles/' .
    $nombreArchivo;

if (!move_uploaded_file($archivo['tmp_name'], $rutaFisica)) {
    die('No se pudo guardar la imagen.');
}

/*
|--------------------------------------------------------------------------
| Guardar en BD
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    UPDATE usuarios
    SET foto_perfil = ?
    WHERE id = ?
");

$stmt->execute([
    $rutaBD,
    $_SESSION['usuario_id']
]);

header('Location: mi_cuenta.php');
exit;