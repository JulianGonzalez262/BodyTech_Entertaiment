<?php

session_start();

require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/app/config/database.php';

use RobThree\Auth\TwoFactorAuth;

if (!isset($_SESSION['usuario_id'])) {

    header('Location: /bodytech/public/login.php');
    exit;

}

$usuarioId = $_SESSION['usuario_id'];

$tfa = new TwoFactorAuth('BodyTech');

/*
|--------------------------------------------------------------------------
| Obtener usuario
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        nombre,
        email,
        authenticator_secret
    FROM usuarios
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([
    $usuarioId
]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {

    die('Usuario no encontrado.');

}

/*
|--------------------------------------------------------------------------
| Generar secreto solo una vez
|--------------------------------------------------------------------------
*/

if (empty($usuario['authenticator_secret'])) {

    $secret = $tfa->createSecret();

    $stmt = $pdo->prepare("
        UPDATE usuarios
        SET authenticator_secret = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $secret,
        $usuarioId
    ]);

} else {

    $secret = $usuario['authenticator_secret'];

}

$label = $usuario['email'];

$qrCode = $tfa->getQRCodeImageAsDataUri(
    $label,
    $secret
);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Google Authenticator | BodyTech</title>

    <link
    rel="stylesheet"
    href="/bodytech/public/assets/css/activar_2fa_authenticator.css?v=1"
>

</head>

<body>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/app/views/header.php'; ?>

<main class="account-container">

    <section class="account-content">

        <div class="security-icon">
            📱
        </div>

        <h1>
            Google Authenticator
        </h1>

        <p class="account-description">
            Configura la autenticación de dos factores para proteger tu cuenta de BodyTech.
        </p>

        <div class="security-step">

            <span class="step-number">1</span>

            <div>

                <h3>
                    Escanea el código QR
                </h3>

                <p>
                    Abre Google Authenticator en tu dispositivo y escanea el siguiente código.
                </p>

            </div>

        </div>

        <div class="authenticator-qr">

            <img
                src="<?= htmlspecialchars($qrCode); ?>"
                alt="Código QR para Google Authenticator"
            >

        </div>

        <div class="security-step">

            <span class="step-number">2</span>

            <div>

                <h3>
                    ¿No puedes escanearlo?
                </h3>

                <p>
                    Introduce manualmente esta clave en Google Authenticator:
                </p>

            </div>

        </div>

        <div class="authenticator-secret">

            <?= htmlspecialchars($secret); ?>

        </div>

        <div class="security-divider"></div>

        <div class="security-step">

            <span class="step-number">3</span>

            <div>

                <h3>
                    Confirma la configuración
                </h3>

                <p>
                    Introduce el código generado por Google Authenticator para activar la protección.
                </p>

            </div>

        </div>

        <form
            action="/bodytech/public/usuario/confirmar_activacion_2fa_authenticator.php"
            method="POST"
            class="profile-form"
        >

            <label for="codigo">
                Código generado por Google Authenticator
            </label>

            <input
                type="text"
                id="codigo"
                name="codigo"
                maxlength="16"
                required
                autocomplete="one-time-code"
                inputmode="numeric"
                placeholder="Ingresa tu código"
            >

            <button type="submit">
                ✅ Activar Google Authenticator
            </button>

        </form>

    </section>

</main>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/app/views/footer.php'; ?>

</body>

</html>