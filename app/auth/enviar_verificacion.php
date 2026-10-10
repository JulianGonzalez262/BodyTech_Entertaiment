<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// PHPMailer
require_once __DIR__ . '/../libs/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ✅ Validar sesión
if (!isset($_SESSION['usuario']['id'])) {
    die('Acceso inválido.');
}

$usuario_id = $_SESSION['usuario']['id'];

// Obtener usuario
$stmt = $pdo->prepare("SELECT email, email_verificado FROM usuarios WHERE id = :id");
$stmt->execute(['id' => $usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    die('Usuario no encontrado.');
}

if ($usuario['email_verificado']) {
    header('Location: /bodytech/public/afiliacion_exitosa.php');
    exit;
}

// 🔑 Token
$token = bin2hex(random_bytes(32));

// Guardar token
$stmt = $pdo->prepare("
    UPDATE usuarios 
    SET email_token = :token 
    WHERE id = :id
");
$stmt->execute([
    'token' => $token,
    'id'    => $usuario_id
]);

// 🔗 Enlace
$enlace = "http://localhost/bodytech/app/auth/verificar_correo.php?token=" . $token;

// ✉️ PHPMailer
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'mirtaleskilo@gmail.com'; // 🔴 TU GMAIL
    $mail->Password   = 'lhcb vmrk ogbg vpww';   // 🔴 APP PASSWORD
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('mirtaleskilo@gmail.com', 'Bodytech');
    $mail->addAddress($usuario['email']);

    $mail->isHTML(true);
    $mail->Subject = 'Verifica tu correo electronico';
    $mail->Body = "
        <h2>Bienvenido a Bodytech</h2>
        <p>Para verificar tu correo, haz clic aquí:</p>
        <a href='$enlace'>Verificar correo</a>
    ";

    $mail->send();

    header('Location: /bodytech/public/afiliacion_exitosa.php?email=enviado');
    exit;

} catch (Exception $e) {
    die("Error al enviar correo: {$mail->ErrorInfo}");
}
