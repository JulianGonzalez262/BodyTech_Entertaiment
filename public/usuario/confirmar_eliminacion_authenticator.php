<?php

session_start();

if (
    !isset($_SESSION['usuario_id']) ||
    !isset($_SESSION['eliminacion_codigo_verificado']) ||
    $_SESSION['eliminacion_codigo_verificado'] !== true ||
    !isset($_SESSION['eliminacion_usuario_id']) ||
    $_SESSION['eliminacion_usuario_id'] !== $_SESSION['usuario_id']
) {

    header('Location: /bodytech/public/login.php');
    exit;

}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>
        Verificación de seguridad | BodyTech
    </title>

    <link
        rel="stylesheet"
        href="/bodytech/public/assets/css/mi_cuenta.css"
    >

</head>

<body>

<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/views/header.php';
?>

<main class="account-container">

    <section class="account-content">

        <h1>
            📱 Verificación adicional
        </h1>

        <p>
            Tu cuenta tiene activada la autenticación en dos pasos.
        </p>

        <p>
            Para continuar con la eliminación permanente de tu cuenta,
            abre Google Authenticator e introduce el código de 6 dígitos.
        </p>

        <form
            action="/bodytech/public/usuario/verificar_eliminacion_authenticator.php"
            method="POST"
            class="profile-form"
        >

            <label for="codigo">
                Código de autenticación
            </label>

            <input
                type="text"
                id="codigo"
                name="codigo"
                maxlength="6"
                pattern="[0-9]{6}"
                inputmode="numeric"
                autocomplete="one-time-code"
                placeholder="Código de 6 dígitos"
                required
            >

            <button type="submit">
                🔐 Verificar y continuar
            </button>

        </form>

    </section>

</main>

<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/views/footer.php';
?>

</body>

</html>