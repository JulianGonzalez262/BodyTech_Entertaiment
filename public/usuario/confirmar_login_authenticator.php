
<?php

session_start();

if (!isset($_SESSION['2fa_usuario_id'])) {

    header('Location: /bodytech/public/login.php');
    exit;

}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Verificación - Google Authenticator | BodyTech</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >

    <link
        rel="stylesheet"
        href="/bodytech/public/assets/css/verificar_login_authenticator.css?v=1"
    >

</head>

<body>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/app/views/header.php'; ?>

<main class="authenticator-page">

    <section class="authenticator-card">

        <div class="authenticator-icon">
            <i class="fa-solid fa-mobile-screen-button"></i>
        </div>

        <span class="authenticator-eyebrow">
            SEGURIDAD BODYTECH
        </span>

        <h1>Google Authenticator</h1>

        <p class="authenticator-description">
            Abre la aplicación Google Authenticator e ingresa el código
            de 6 dígitos generado para continuar con el inicio de sesión.
        </p>

        <div class="authenticator-notice">

            <i class="fa-solid fa-shield-halved"></i>

            <span>
                Utiliza el código temporal que aparece en tu aplicación.
            </span>

        </div>

        <form
            action="/bodytech/public/usuario/verificar_login_authenticator.php"
            method="POST"
            class="profile-form"
        >

            <label for="codigo">
                Código de verificación
            </label>

            <div class="authenticator-input-wrapper">

                <i class="fa-solid fa-key"></i>

                <input
                    type="text"
                    id="codigo"
                    name="codigo"
                    maxlength="6"
                    required
                    autocomplete="one-time-code"
                    inputmode="numeric"
                    pattern="[0-9]{6}"
                    placeholder="••••••"
                    aria-describedby="authenticator-help"
                >

            </div>

            <small id="authenticator-help" class="authenticator-help">
                Introduce los 6 dígitos sin espacios.
            </small>

            <button type="submit">

                <i class="fa-solid fa-right-to-bracket"></i>

                <span>Iniciar sesión</span>

            </button>

        </form>

        <div class="authenticator-footer">

            <i class="fa-solid fa-lock"></i>

            <span>Autenticación segura de tu cuenta BodyTech</span>

        </div>

    </section>

</main>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/app/views/footer.php'; ?>

</body>
</html>
