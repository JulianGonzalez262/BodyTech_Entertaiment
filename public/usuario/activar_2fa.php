<?php

session_start();

if (!isset($_SESSION['usuario_id'])) {

    header('Location: /bodytech/public/login.php');
    exit;

}

if (!isset($_GET['metodo'])) {

    header('Location: /bodytech/public/usuario/dos_factores.php');
    exit;

}

$metodo = $_GET['metodo'];

switch ($metodo) {

    case 'email':

        header('Location: /bodytech/public/usuario/activar_2fa_email.php');
        exit;

    case 'authenticator':

        header('Location: /bodytech/public/usuario/activar_2fa_authenticator.php');
        exit;

    default:

        header('Location: /bodytech/public/usuario/dos_factores.php');
        exit;

}