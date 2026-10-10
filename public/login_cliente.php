<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        die("Completa todos los campos");
    }

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario || !password_verify($password, $usuario['password'])) {
        die("Credenciales incorrectas");
    }

    // Guardar sesión
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['rol_id'] = $usuario['rol_id'];

    // Redirigir al index público
    header("Location: /bodytech/public/index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Cliente</title>
</head>
<body>

<h2>Iniciar sesión</h2>

<form method="POST">

    <input type="email" name="email" placeholder="Correo" required><br><br>

    <input type="password" name="password" placeholder="Contraseña" required><br><br>

    <button type="submit">Ingresar</button>

</form>

<a href="registro_cliente.php">Crear cuenta</a>

</body>
</html>