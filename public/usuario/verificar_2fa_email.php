<?php

session_start();

require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/app/config/database.php';

if (!isset($_SESSION['usuario_id'])) {

    header('Location: /bodytech/public/login.php');
    exit;

}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: /bodytech/public/usuario/dos_factores.php');
    exit;

}

$usuarioId = $_SESSION['usuario_id'];

$codigo = trim($_POST['codigo'] ?? '');

if (empty($codigo)) {

    die('Debes ingresar el código.');

}

/*
|--------------------------------------------------------------------------
| Buscar código válido
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
SELECT *
FROM codigos_verificacion
WHERE usuario_id = ?
AND codigo = ?
AND tipo = '2fa_email'
AND usado = 0
LIMIT 1
");

$stmt->execute([
    $usuarioId,
    $codigo
]);

$registro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$registro) {

    die('Código incorrecto.');

}

/*
|--------------------------------------------------------------------------
| Verificar expiración
|--------------------------------------------------------------------------
*/

if (strtotime($registro['expires_at']) < time()) {

    die('El código ha expirado.');

}

/*
|--------------------------------------------------------------------------
| Activar 2FA
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
UPDATE usuarios
SET
dos_factores_activo = 1,
dos_factores_metodo = 'email'
WHERE id = ?
");

$stmt->execute([
    $usuarioId
]);

/*
|--------------------------------------------------------------------------
| Marcar código como usado
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
UPDATE codigos_verificacion
SET usado = 1
WHERE id = ?
");

$stmt->execute([
    $registro['id']
]);

header('Location: /bodytech/public/usuario/dos_factores.php');

exit;