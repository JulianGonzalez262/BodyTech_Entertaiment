<?php

session_start();

require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/app/config/database.php';

require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/app/libs/PHPMailer-master/src/Exception.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/app/libs/PHPMailer-master/src/PHPMailer.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/app/libs/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

if (!isset($_SESSION['usuario_id'])) {

    header('Location: /bodytech/public/login.php');
    exit;

}

$usuarioId = $_SESSION['usuario_id'];

/*
|--------------------------------------------------------------------------
| Obtener correo del usuario
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT email
    FROM usuarios
    WHERE id = ?
");

$stmt->execute([$usuarioId]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {

    die('Usuario no encontrado.');

}

/*
|--------------------------------------------------------------------------
| Generar código
|--------------------------------------------------------------------------
*/

$codigo = random_int(100000, 999999);

$expires = date(
    'Y-m-d H:i:s',
    strtotime('+10 minutes')
);

/*
|--------------------------------------------------------------------------
| Invalidar códigos anteriores
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    UPDATE codigos_verificacion
    SET usado = 1
    WHERE usuario_id = ?
      AND tipo = '2fa_email'
      AND usado = 0
");

$stmt->execute([$usuarioId]);

/*
|--------------------------------------------------------------------------
| Guardar nuevo código
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    INSERT INTO codigos_verificacion
    (
        usuario_id,
        codigo,
        tipo,
        expires_at
    )
    VALUES
    (
        ?,
        ?,
        '2fa_email',
        ?
    )
");

$stmt->execute([
    $usuarioId,
    $codigo,
    $expires
]);

/*
|--------------------------------------------------------------------------
| Enviar correo
|--------------------------------------------------------------------------
*/

$mail = new PHPMailer(true);

$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'mirtaleskilo@gmail.com';
$mail->Password = 'lhcb vmrk ogbg vpww';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

$mail->setFrom(
    'mirtaleskilo@gmail.com',
    'BodyTech'
);

$mail->addAddress($usuario['email']);

$mail->isHTML(true);

$mail->Subject = 'Codigo de verificacion - BodyTech';

$mail->Body = "

<h2>Autenticación en dos pasos</h2>

<p>

Tu código de verificación es:

</p>

<h1 style='letter-spacing:5px;'>

$codigo

</h1>

<p>

Este código expirará en 10 minutos.

</p>

<p>

Si no solicitaste esta acción puedes ignorar este correo.

</p>

";

$mail->send();

/*
|--------------------------------------------------------------------------
| Redirigir
|--------------------------------------------------------------------------
*/

header(
    'Location: /bodytech/public/usuario/confirmar_2fa_email.php'
);

exit;