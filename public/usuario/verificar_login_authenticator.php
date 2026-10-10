<?php

session_start();

require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/app/config/database.php';

use RobThree\Auth\TwoFactorAuth;

if (!isset($_SESSION['2fa_usuario_id'])) {

    header('Location: /bodytech/public/login.php');
    exit;

}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: /bodytech/public/login.php');
    exit;

}

$usuarioId = $_SESSION['2fa_usuario_id'];

$codigo = trim($_POST['codigo'] ?? '');

if (empty($codigo)) {

    die('Debes ingresar el código.');

}

/*
|--------------------------------------------------------------------------
| Obtener usuario
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
SELECT
id,
nombre,
email,
rol_id,
authenticator_secret
FROM usuarios
WHERE id = ?
LIMIT 1
");

$stmt->execute([
    $usuarioId
]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {

    die('Usuario no encontrado.');

}

$tfa = new TwoFactorAuth('BodyTech');

/*
|--------------------------------------------------------------------------
| Verificar código
|--------------------------------------------------------------------------
*/

if (!$tfa->verifyCode($usuario['authenticator_secret'], $codigo)) {

    die('Código incorrecto.');

}

/*
|--------------------------------------------------------------------------
| Crear sesión
|--------------------------------------------------------------------------
*/

$_SESSION['user_id']   = $usuario['id'];
$_SESSION['user_name'] = $usuario['nombre'];
$_SESSION['usuario_id'] = $usuario['id'];

$stmt = $pdo->prepare("
SELECT nombre
FROM roles
WHERE id = ?
LIMIT 1
");

$stmt->execute([
    $usuario['rol_id']
]);

$rol = $stmt->fetchColumn();

$_SESSION['user_role'] = $rol;

$_SESSION['usuario'] = [

    'id'     => $usuario['id'],
    'email'  => $usuario['email'],
    'nombre' => $usuario['nombre'],
    'rol'    => $rol

];

/*
|--------------------------------------------------------------------------
| Limpiar sesión temporal del 2FA
|--------------------------------------------------------------------------
*/

unset($_SESSION['2fa_usuario_id']);

header('Location: /bodytech/public/index.php');

exit;