<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_GET['token'])) {
    die('Token inválido.');
}

$token = $_GET['token'];

$stmt = $pdo->prepare("
    SELECT id, nombre, email
    FROM usuarios
    WHERE email_token = :token
    LIMIT 1
");
$stmt->execute(['token' => $token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die('Token no válido.');
}

// marcar correo verificado
$stmt = $pdo->prepare("
    UPDATE usuarios
    SET email_verificado = 1, email_token = NULL
    WHERE id = :id
");
$stmt->execute(['id' => $user['id']]);

// iniciar sesión automática
$_SESSION['usuario_id'] = $user['id'];
$_SESSION['usuario'] = [
    'id' => $user['id'],
    'nombre' => $user['nombre'],
    'email' => $user['email'],
    'rol' => 'cliente'
];

// revisar si tiene afiliación
$stmt = $pdo->prepare("
    SELECT id
    FROM afiliados
    WHERE usuario_id = :id
    AND estado = 'activo'
    LIMIT 1
");
$stmt->execute(['id' => $user['id']]);
$afiliado = $stmt->fetch(PDO::FETCH_ASSOC);

// redirección inteligente
if ($afiliado) {
    header('Location: /bodytech/public/afiliacion_exitosa.php?verificado=1');
} else {
    header('Location: /bodytech/public/afiliacion_exitosa.php');
}
exit;