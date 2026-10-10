<?php

session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

use RobThree\Auth\TwoFactorAuth;

/*
|--------------------------------------------------------------------------
| VALIDAR SESIÓN DEL PROCESO DE ELIMINACIÓN
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['usuario_id']) ||
    !isset($_SESSION['eliminacion_codigo_verificado']) ||
    $_SESSION['eliminacion_codigo_verificado'] !== true ||
    !isset($_SESSION['eliminacion_usuario_id']) ||
    $_SESSION['eliminacion_usuario_id'] !== $_SESSION['usuario_id']
) {

    header('Location: /bodytech/public/login.php');
    exit;

}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header(
        'Location: /bodytech/public/usuario/confirmar_eliminacion_authenticator.php'
    );

    exit;

}

$usuarioId = $_SESSION['usuario_id'];

$codigo = trim($_POST['codigo'] ?? '');

/*
|--------------------------------------------------------------------------
| VALIDAR FORMATO
|--------------------------------------------------------------------------
*/

if (!preg_match('/^\d{6}$/', $codigo)) {

    die('El código debe contener exactamente 6 dígitos.');

}

/*
|--------------------------------------------------------------------------
| OBTENER SECRETO DEL AUTHENTICATOR
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        authenticator_secret,
        dos_factores_activo,
        dos_factores_metodo
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

/*
|--------------------------------------------------------------------------
| VERIFICAR QUE EL AUTHENTICATOR ESTÉ ACTIVO
|--------------------------------------------------------------------------
*/

if (
    (int)$usuario['dos_factores_activo'] !== 1 ||
    $usuario['dos_factores_metodo'] !== 'authenticator'
) {

    die('La autenticación mediante Authenticator no está activa para esta cuenta.');

}

if (empty($usuario['authenticator_secret'])) {

    die('La configuración del Authenticator no está disponible.');

}

/*
|--------------------------------------------------------------------------
| VALIDAR TOTP
|--------------------------------------------------------------------------
*/

$tfa = new TwoFactorAuth('BodyTech');

if (
    !$tfa->verifyCode(
        $usuario['authenticator_secret'],
        $codigo
    )
) {

    die('El código de Google Authenticator es incorrecto.');

}

/*
|--------------------------------------------------------------------------
| AUTHENTICATOR VERIFICADO
|--------------------------------------------------------------------------
*/

$_SESSION['eliminacion_2fa_verificado'] = true;

$_SESSION['eliminacion_2fa_usuario_id'] = $usuarioId;

/*
|--------------------------------------------------------------------------
| CONTINUAR AL SIGUIENTE PASO
|--------------------------------------------------------------------------
*/

header(
    'Location: /bodytech/public/usuario/confirmacion_final_eliminacion.php'
);

exit;