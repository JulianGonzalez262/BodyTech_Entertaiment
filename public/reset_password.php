<?php
require_once __DIR__ . '/../app/config/database.php';

$token = $_GET['token'] ?? null;

if (!$token) {
    die('Token inválido');
}

$stmt = $pdo->prepare("
    SELECT * FROM password_resets
    WHERE token = :token
    AND usado = 0
    AND expires_at > NOW()
");
$stmt->execute(['token' => $token]);
$reset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reset) {
    die('Token inválido o expirado');
}
?>

<form method="POST" action="/bodytech/app/auth/reset_password.php">
    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
    <input type="password" name="password" placeholder="Nueva contraseña" required>
    <button type="submit">Cambiar contraseña</button>
</form>
