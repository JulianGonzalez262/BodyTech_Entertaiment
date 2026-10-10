<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// 1. Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Acceso no permitido.');
}

// 2. Verificar tipo de login
if (!isset($_POST['login_type'])) {
    die('Tipo de login no especificado.');
}

$loginType = $_POST['login_type'];

// 3. Solo login cliente
if ($loginType !== 'cliente') {
    die('Acceso no autorizado.');
}

// 4. Validar campos
if (empty($_POST['email']) || empty($_POST['password'])) {
    die('Correo y contraseña son obligatorios.');
}

$email = trim($_POST['email']);
$password = $_POST['password'];

// 5. Buscar usuario
$stmt = $pdo->prepare("
    SELECT 
        usuarios.id,
        usuarios.nombre,
        usuarios.email,
        usuarios.password,
        usuarios.estado,
        usuarios.dos_factores_activo,
        usuarios.dos_factores_metodo,
        roles.nombre AS rol
    FROM usuarios
    INNER JOIN roles ON usuarios.rol_id = roles.id
    WHERE usuarios.email = :email
    LIMIT 1
");
$stmt->execute(['email' => $email]);
$usuario = $stmt->fetch();

// 6. Validar credenciales
if ($usuario && password_verify($password, $usuario['password'])) {

    // VALIDAR BLOQUEO ADMIN
    if ($usuario['estado'] !== 'activo') {
        die('Tu cuenta ha sido bloqueada por el administrador.');
    }

    if ($usuario['rol'] !== 'cliente') {
        die('Acceso denegado.');
    }

/*
/*
|--------------------------------------------------------------------------
| Login con autenticación en dos pasos
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Login con autenticación en dos pasos
|--------------------------------------------------------------------------
*/

if ($usuario['dos_factores_activo']) {

    $_SESSION['2fa_usuario_id'] = $usuario['id'];

    switch ($usuario['dos_factores_metodo']) {

        case 'email':

            require_once __DIR__.'/enviar_codigo_login_2fa.php';

            if (!enviarCodigoLogin2FA($usuario['id'])) {

                die('No fue posible enviar el código de verificación.');

            }

            header('Location: /bodytech/public/usuario/confirmar_login_2fa.php');

            exit;

        case 'authenticator':

            header('Location: /bodytech/public/usuario/confirmar_login_authenticator.php');

            exit;

        default:

            die('Método de autenticación no válido.');

    }

}

    /* =====================================================
       ✅ SESIÓN COMPLETA
    ===================================================== */

    $_SESSION['user_id']   = $usuario['id'];
    $_SESSION['user_name'] = $usuario['nombre'];
    $_SESSION['user_role'] = $usuario['rol'];

    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario'] = [
        'id'     => $usuario['id'],
        'email'  => $usuario['email'],
        'nombre' => $usuario['nombre'],
        'rol'    => 'cliente'
    ];

    header('Location: /bodytech/public/index.php');
    exit;

} else {
    die('Correo o contraseña incorrectos.');
}