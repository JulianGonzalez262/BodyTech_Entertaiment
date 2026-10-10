
<?php

session_start();

if (!isset($_SESSION['usuario_id'])) {

    header('Location: /bodytech/public/login.php');
    exit;

}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Confirmar autenticación | BodyTech</title>

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
        href="/bodytech/public/assets/css/confirmar_2fa_email.css?v=1"
    >

</head>

<body>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/app/views/header.php'; ?>

<main class="email2fa-page">

    <section class="email2fa-card">

        <div class="email2fa-icon">
            <i class="fa-solid fa-envelope-circle-check"></i>
        </div>

        <span class="email2fa-eyebrow">
            SEGURIDAD BODYTECH
        </span>

        <h1>Confirmar autenticación</h1>

        <p class="email2fa-description">
            Te hemos enviado un código de verificación a tu correo electrónico.
            Introdúcelo a continuación para continuar.
        </p>

        <div class="email2fa-notice">
            <i class="fa-solid fa-shield-halved"></i>

            <span>
                Esta verificación adicional ayuda a proteger tu cuenta.
            </span>
        </div>

        <form
            action="/bodytech/public/usuario/verificar_2fa_email.php"
            method="POST"
            class="profile-form"
        >

            <label for="codigo">
                Código de verificación
            </label>

            <div class="email2fa-input-wrapper">

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
                    aria-describedby="email2fa-help"
                >

            </div>

            <small id="email2fa-help" class="email2fa-help">
                Introduce los 6 dígitos que recibiste.
            </small>

            <button type="submit">

                <i class="fa-solid fa-circle-check"></i>

                <span>Verificar código</span>

            </button>

        </form>

        <div class="email2fa-footer">
            <i class="fa-solid fa-lock"></i>

            <span>Verificación segura de tu cuenta BodyTech</span>
        </div>

    </section>

</main>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/app/views/footer.php'; ?>

</body>
</html>
