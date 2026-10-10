<?php

session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

if (!isset($_SESSION['usuario_id'])) {

    header("Location: /bodytech/public/login.php");
    exit;

}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: cambiar_password.php");
    exit;

}

$passwordActual = trim($_POST['password_actual'] ?? '');
$passwordNueva = trim($_POST['password_nueva'] ?? '');
$passwordConfirmar = trim($_POST['password_confirmar'] ?? '');

if (

    empty($passwordActual) ||
    empty($passwordNueva) ||
    empty($passwordConfirmar)

) {

    header("Location: cambiar_password.php?error=Todos los campos son obligatorios.");
    exit;

}

if ($passwordNueva !== $passwordConfirmar) {

    header("Location: cambiar_password.php?error=Las nuevas contraseñas no coinciden.");
    exit;

}

if (strlen($passwordNueva) < 8) {

    header("Location: cambiar_password.php?error=La nueva contraseña debe tener mínimo 8 caracteres.");
    exit;

}

$stmt = $pdo->prepare("
    SELECT password
    FROM usuarios
    WHERE id = ?
");

$stmt->execute([
    $_SESSION['usuario_id']
]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {

    session_destroy();

    header("Location: /bodytech/public/login.php");
    exit;

}

if (!password_verify($passwordActual, $usuario['password'])) {

    header("Location: cambiar_password.php?error=La contraseña actual es incorrecta.");
    exit;

}

if (password_verify($passwordNueva, $usuario['password'])) {

    header("Location: cambiar_password.php?error=La nueva contraseña debe ser diferente a la actual.");
    exit;

}

$nuevoHash = password_hash($passwordNueva, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("
    UPDATE usuarios
    SET password = ?
    WHERE id = ?
");

$stmt->execute([
    $nuevoHash,
    $_SESSION['usuario_id']
]);

header("Location: cambiar_password.php?ok=1");
exit;