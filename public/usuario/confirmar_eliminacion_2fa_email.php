<?php

session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

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

$usuario_id = $_SESSION['usuario_id'];

/*
|--------------------------------------------------------------------------
| COMPROBAR QUE EL 2FA POR EMAIL SIGUE ACTIVO
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        dos_factores_activo,
        dos_factores_metodo
    FROM usuarios
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([
    $usuario_id
]);

$config2FA = $stmt->fetch(PDO::FETCH_ASSOC);

if (
    !$config2FA ||
    (int)$config2FA['dos_factores_activo'] !== 1 ||
    $config2FA['dos_factores_metodo'] !== 'email'
) {

    die('La autenticación en dos pasos por correo no está activa para esta cuenta.');

}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>
        Segunda verificación | BodyTech
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
            📧 Segunda verificación
        </h1>

        <p>
            Tu cuenta tiene activada la autenticación en dos pasos mediante correo electrónico.
        </p>

        <p>
            Hemos enviado un segundo código de seguridad a tu correo registrado.
        </p>

        <p>
            Introduce el código para continuar con el proceso de eliminación.
        </p>

        <form
            action="/bodytech/public/usuario/verificar_eliminacion_2fa_email.php"
            method="POST"
            class="profile-form"
        >

            <label for="codigo">
                Segundo código de verificación
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