<?php

session_start();

require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/app/config/database.php';

use RobThree\Auth\TwoFactorAuth;

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
| Obtener secreto del usuario
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
SELECT authenticator_secret
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

if (empty($usuario['authenticator_secret'])) {

    die('No existe una clave de Google Authenticator.');

}

/*
|--------------------------------------------------------------------------
| Verificar código
|--------------------------------------------------------------------------
*/

$tfa = new TwoFactorAuth('BodyTech');

echo "<pre>";

echo "Secreto BD: " . $usuario['authenticator_secret'] . PHP_EOL;

echo "Código escrito: " . $codigo . PHP_EOL;

echo "Código esperado AHORA: " . $tfa->getCode($usuario['authenticator_secret']) . PHP_EOL;

echo "</pre>";


if (!$tfa->verifyCode($usuario['authenticator_secret'], $codigo)) {

    die('El código es incorrecto.');

}

/*
|--------------------------------------------------------------------------
| Activar Google Authenticator
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
UPDATE usuarios
SET

    dos_factores_activo = 1,

    dos_factores_metodo = 'authenticator'

WHERE id = ?
");

$stmt->execute([
    $usuarioId
]);

header('Location: /bodytech/public/usuario/dos_factores.php');

exit;