<?php

session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

/*
|--------------------------------------------------------------------------
| VALIDAR SESIÓN DEL PROCESO
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
        'Location: /bodytech/public/usuario/confirmar_eliminacion_2fa_email.php'
    );

    exit;

}

$usuario_id = $_SESSION['usuario_id'];

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
| BUSCAR CÓDIGO
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        codigo,
        usado,
        expires_at
    FROM codigos_verificacion
    WHERE usuario_id = ?
    AND tipo = 'eliminar_cuenta_2fa'
    ORDER BY id DESC
    LIMIT 1
");

$stmt->execute([
    $usuario_id
]);

$registro = $stmt->fetch(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| VALIDAR EXISTENCIA
|--------------------------------------------------------------------------
*/

if (!$registro) {

    die('No existe un código de segunda verificación válido.');

}

/*
|--------------------------------------------------------------------------
| VALIDAR SI YA FUE UTILIZADO
|--------------------------------------------------------------------------
*/

if ((int)$registro['usado'] === 1) {

    die('El código ya fue utilizado.');

}

/*
|--------------------------------------------------------------------------
| VALIDAR EXPIRACIÓN
|--------------------------------------------------------------------------
*/

if (strtotime($registro['expires_at']) < time()) {

    die('El código ha expirado. Solicita uno nuevo.');

}

/*
|--------------------------------------------------------------------------
| VALIDAR CÓDIGO
|--------------------------------------------------------------------------
*/

if (!hash_equals(
    (string)$registro['codigo'],
    (string)$codigo
)) {

    die('El código de segunda verificación es incorrecto.');

}

/*
|--------------------------------------------------------------------------
| MARCAR CÓDIGO COMO UTILIZADO
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

/*
|--------------------------------------------------------------------------
| SEGUNDO FACTOR VERIFICADO
|--------------------------------------------------------------------------
*/

$_SESSION['eliminacion_2fa_verificado'] = true;

$_SESSION['eliminacion_2fa_usuario_id'] = $usuario_id;

/*
|--------------------------------------------------------------------------
| CONTINUAR
|--------------------------------------------------------------------------
*/

header(
    'Location: /bodytech/public/usuario/confirmacion_final_eliminacion.php'
);

exit;