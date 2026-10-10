<?php
require_once __DIR__ . '/../config/database.php';

$token = $_POST['token'] ?? null;
$password = $_POST['password'] ?? null;

if (!$token || !$password) {
    die('Datos incompletos');
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
    die('Token inválido');
}

$hash = password_hash($password, PASSWORD_BCRYPT);

$pdo->beginTransaction();

$pdo->prepare("
    UPDATE usuarios SET password = :password WHERE id = :id
")->execute([
    'password' => $hash,
    'id' => $reset['usuario_id']
]);

$pdo->prepare("
    UPDATE password_resets SET usado = 1 WHERE id = :id
")->execute(['id' => $reset['id']]);

$pdo->commit();

echo "Contraseña actualizada correctamente";
