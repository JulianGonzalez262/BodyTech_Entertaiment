<?php

session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

if (!isset($_SESSION['usuario_id'])) {

    header('Location: /bodytech/public/login.php');
    exit;
}

$stmt = $pdo->prepare("
    UPDATE preferencias_usuario
    SET

        recibir_notificaciones_productos = ?,

        recibir_notificaciones_afiliaciones = ?,

        recibir_notificaciones_promociones = ?,

        recibir_correos_marketing = ?

    WHERE usuario_id = ?
");

$stmt->execute([

    isset($_POST['productos']) ? 1 : 0,

    isset($_POST['afiliaciones']) ? 1 : 0,

    isset($_POST['promociones']) ? 1 : 0,

    isset($_POST['marketing']) ? 1 : 0,

    $_SESSION['usuario_id']
]);

header('Location: privacidad.php');
exit;