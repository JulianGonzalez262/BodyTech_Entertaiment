
<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../libs/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

$email = $_POST['email'] ?? null;

if (!$email) {
    die('Correo requerido');
}

$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
$stmt->execute(['email' => $email]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    die('Si el correo existe, se enviará un enlace');
}

$token = bin2hex(random_bytes(32));
$expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

$stmt = $pdo->prepare("
    INSERT INTO password_resets (usuario_id, token, expires_at)
    VALUES (:usuario_id, :token, :expires)
");
$stmt->execute([
    'usuario_id' => $usuario['id'],
    'token' => $token,
    'expires' => $expires
]);

$enlace = "http://localhost/bodytech/public/reset_password.php?token=$token";

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'mirtaleskilo@gmail.com';
$mail->Password = 'lhcb vmrk ogbg vpww';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

$mail->setFrom('mirtaleskilo@gmail.com', 'Bodytech');
$mail->addAddress($email);
$mail->isHTML(true);
$mail->Subject = 'Recupera tu contraseña';
$mail->Body = "
    <h3>Recuperación de contraseña</h3>
    <p>Haz clic aquí para cambiar tu contraseña:</p>
    <a href='$enlace'>Cambiar contraseña</a>
";

$mail->send();

echo "Correo enviado";
