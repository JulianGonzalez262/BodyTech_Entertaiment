
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

    <title>Verificación de inicio de sesión | BodyTech</title>

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
        href="/bodytech/public/assets/css/verificar_login_2fa.css?v=1"
    >

</head>

<body>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/app/views/header.php'; ?>

<main class="twofa-page">

    <section class="twofa-card">

        <div class="twofa-icon">
            <i class="fa-solid fa-shield-halved"></i>
        </div>

        <span class="twofa-eyebrow">SEGURIDAD BODYTECH</span>

        <h1>Verificación en dos pasos</h1>

        <p class="twofa-description">
            Para completar el inicio de sesión, ingresa el código de
            6 dígitos que acabamos de enviar a tu correo electrónico.
        </p>

        <div class="twofa-notice">
            <i class="fa-solid fa-envelope-circle-check"></i>
            <span>Revisa tu correo electrónico para encontrar el código.</span>
        </div>

        <form
            action="/bodytech/public/usuario/verificar_login_2fa.php"
            method="POST"
            class="profile-form"
        >

            <label for="codigo">
                Código de verificación
            </label>

            <div class="code-input-wrapper">
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
                    aria-describedby="codigo-ayuda"
                >
            </div>

            <small id="codigo-ayuda" class="input-help">
                Introduce los 6 dígitos recibidos.
            </small>

            <button type="submit">
                <i class="fa-solid fa-lock-open"></i>
                <span>Verificar e iniciar sesión</span>
            </button>

        </form>

        <div class="twofa-footer-note">
            <i class="fa-solid fa-shield-check"></i>
            Tu cuenta está protegida con verificación adicional.
        </div>

    </section>

</main>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/app/views/footer.php'; ?>

</body>
</html>
