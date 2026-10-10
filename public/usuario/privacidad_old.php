<?php

session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

if (!isset($_SESSION['usuario_id'])) {

    header('Location: /bodytech/public/login.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT *
    FROM preferencias_usuario
    WHERE usuario_id = ?
");

$stmt->execute([
    $_SESSION['usuario_id']
]);

$preferencias = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>

    <meta charset="UTF-8">

    <title>
        Privacidad | BodyTech
    </title>

    <link
        rel="stylesheet"
        href="/bodytech/public/assets/css/mi_cuenta.css"
    >

</head>
<body>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/app/views/header.php'; ?>

<main class="account-container">

    <section class="account-content">

        <h1>
            🔒 Privacidad y preferencias
        </h1>

        <form
            action="actualizar_privacidad.php"
            method="POST"
            class="profile-form"
        >

            <label>

                <input
                    type="checkbox"
                    name="productos"
                    <?= $preferencias['recibir_notificaciones_productos'] ? 'checked' : '' ?>
                >

                Recibir notificaciones sobre productos y compras

            </label>

            <label>

                <input
                    type="checkbox"
                    name="afiliaciones"
                    <?= $preferencias['recibir_notificaciones_afiliaciones'] ? 'checked' : '' ?>
                >

                Recibir notificaciones sobre afiliaciones y membresías

            </label>

            <label>

                <input
                    type="checkbox"
                    name="promociones"
                    <?= $preferencias['recibir_notificaciones_promociones'] ? 'checked' : '' ?>
                >

                Recibir promociones y descuentos

            </label>

            <label>

                <input
                    type="checkbox"
                    name="marketing"
                    <?= $preferencias['recibir_correos_marketing'] ? 'checked' : '' ?>
                >

                Recibir correos de marketing

            </label>

            <button type="submit">

                💾 Guardar preferencias

            </button>

        </form>

    </section>

</main>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/app/views/footer.php'; ?>

</body>
</html>