<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/libs/PHPMailer-master/src/Exception.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/libs/PHPMailer-master/src/PHPMailer.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/libs/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

function enviarCorreo($destinatario, $asunto, $contenido)
{
    $mail = new PHPMailer(true);

    $mail->isSMTP();

    $mail->Host = 'smtp.gmail.com';

    $mail->SMTPAuth = true;

    $mail->Username = 'mirtaleskilo@gmail.com';

    $mail->Password = 'lhcb vmrk ogbg vpww';

    $mail->SMTPSecure = 'tls';

    $mail->Port = 587;

    $mail->CharSet = 'UTF-8';

    $mail->setFrom(
        'mirtaleskilo@gmail.com',
        'BodyTech'
    );

    $mail->addAddress($destinatario);

    $mail->isHTML(true);

    $mail->Subject = $asunto;

    $mail->Body = $contenido;

    return $mail->send();
}