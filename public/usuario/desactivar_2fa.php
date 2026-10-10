<?php

session_start();

require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/app/config/database.php';

if (!isset($_SESSION['usuario_id'])) {

    header('Location: /bodytech/public/login.php');
    exit;

}

$usuarioId = $_SESSION['usuario_id'];

/*
|--------------------------------------------------------------------------
| Desactivar 2FA
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
UPDATE usuarios
SET
    dos_factores_activo = 0,
    dos_factores_metodo = NULL
WHERE id = ?
");

$stmt->execute([
    $usuarioId
]);

/*
|--------------------------------------------------------------------------
| Invalidar códigos pendientes
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
UPDATE codigos_verificacion
SET usado = 1
WHERE usuario_id = ?
AND usado = 0
");

$stmt->execute([
    $usuarioId
]);

header('Location: /bodytech/public/usuario/dos_factores.php');

exit;