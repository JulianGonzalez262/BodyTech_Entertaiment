<?php

session_start();

require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/app/config/database.php';

if (!isset($_SESSION['2fa_usuario_id'])) {

    header('Location: /bodytech/public/login.php');
    exit;

}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: /bodytech/public/usuario/confirmar_login_2fa.php');
    exit;

}

$usuarioId = $_SESSION['2fa_usuario_id'];

$codigo = trim($_POST['codigo'] ?? '');

if (empty($codigo)) {

    die('Debes ingresar el código.');

}

/*
|--------------------------------------------------------------------------
| Buscar código
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

/*
|--------------------------------------------------------------------------
| Obtener usuario
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
SELECT
usuarios.id,
usuarios.nombre,
usuarios.email,
roles.nombre AS rol
FROM usuarios
INNER JOIN roles
ON usuarios.rol_id = roles.id
WHERE usuarios.id = ?
LIMIT 1
");

$stmt->execute([
    $usuarioId
]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {

    session_destroy();

    die('Usuario no encontrado.');

}

/*
|--------------------------------------------------------------------------
| Crear sesión definitiva
|--------------------------------------------------------------------------
*/

$_SESSION['user_id'] = $usuario['id'];

$_SESSION['user_name'] = $usuario['nombre'];

$_SESSION['user_role'] = $usuario['rol'];

$_SESSION['usuario_id'] = $usuario['id'];

$_SESSION['usuario'] = [

    'id' => $usuario['id'],

    'email' => $usuario['email'],

    'nombre' => $usuario['nombre'],

    'rol' => $usuario['rol']

];

/*
|--------------------------------------------------------------------------
| Limpiar sesión temporal
|--------------------------------------------------------------------------
*/

unset($_SESSION['2fa_usuario_id']);

header('Location: /bodytech/public/index.php');

exit;