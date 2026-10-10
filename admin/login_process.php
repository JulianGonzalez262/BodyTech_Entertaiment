<?php
// admin/login_process.php
session_start();
// Si ya hay sesión de admin, no permitir volver a loguear
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

// Validar envío del formulario
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit;
}

// Conexión a la BD (USA TU ARCHIVO EXISTENTE)
require_once __DIR__ . '/../app/config/database.php';
// ⬆️ ajusta la ruta SOLO si tu archivo está en otro lugar

$email = trim($_POST['email']);
$password = $_POST['password'];

if (empty($email) || empty($password)) {
    header("Location: login.php?error=1");
    exit;
}

try {
    $sql = "
        SELECT u.id, u.nombre, u.password, r.nombre AS rol
        FROM usuarios u
        INNER JOIN roles r ON u.rol_id = r.id
        WHERE u.email = :email
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Validaciones críticas
    if (
        !$usuario ||
        $usuario['rol'] !== 'admin' ||
        !password_verify($password, $usuario['password'])
    ) {
        header("Location: login.php?error=1");
        exit;
    }

    // 🔐 Sesión ADMIN
    $_SESSION['admin_id'] = $usuario['id'];
    $_SESSION['admin_nombre'] = $usuario['nombre'];
    $_SESSION['admin_rol'] = 'admin';

    header("Location: index.php");
    exit;

} catch (PDOException $e) {
    // En producción esto debería ir a logs
    header("Location: login.php?error=1");
    exit;
}