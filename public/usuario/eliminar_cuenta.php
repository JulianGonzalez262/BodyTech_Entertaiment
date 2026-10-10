<?php

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: /bodytech/public/login.php");
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/views/header.php';

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Eliminar cuenta | BodyTech</title>

    <link
        rel="stylesheet"
        href="/bodytech/public/assets/css/eliminar_cuenta.css"
    >

</head>

<body>

<main class="delete-account-page">

    <section class="delete-account-card">

        <!-- =========================
             ENCABEZADO
        ========================== -->

        <div class="delete-account-header">

            <div class="delete-account-icon">
                ⚠️
            </div>

            <div>

                <span class="delete-account-kicker">
                    Seguridad de cuenta
                </span>

                <h1>
                    Eliminar mi cuenta
                </h1>

                <p>
                    Estás a punto de iniciar el proceso de eliminación permanente de tu cuenta de BodyTech.
                </p>

            </div>

        </div>


        <!-- =========================
             ADVERTENCIA
        ========================== -->

        <div class="delete-warning-box">

            <div class="warning-box-header">

                <span class="warning-icon">
                    !
                </span>

                <div>

                    <h2>
                        Antes de continuar
                    </h2>

                    <p>
                        BodyTech realizará las siguientes verificaciones:
                    </p>

                </div>

            </div>

            <ul>

                <li>
                    <span>✓</span>
                    Se verificará que no tengas membresías activas.
                </li>

                <li>
                    <span>✓</span>
                    Se verificará que no existan pedidos pendientes.
                </li>

                <li>
                    <span>✓</span>
                    Se verificará que no existan pagos pendientes.
                </li>

                <li>
                    <span>✓</span>
                    Se verificará que no tengas solicitudes PQRS abiertas.
                </li>

                <li>
                    <span>✓</span>
                    Se solicitará confirmar tu identidad mediante correo electrónico.
                </li>

                <li>
                    <span>✓</span>
                    Si tienes autenticación en dos pasos, también deberás confirmarla.
                </li>

                <li class="permanent-item">
                    <span>!</span>
                    Esta acción será permanente.
                </li>

            </ul>

        </div>


        <!-- =========================
             INFORMACIÓN IMPORTANTE
        ========================== -->

        <div class="important-note">

            <div class="important-note-title">

                <span>
                    🔒
                </span>

                <strong>
                    Importante
                </strong>

            </div>

            <p>
                Una vez eliminada tu cuenta:
            </p>

            <ul>

                <li>
                    No podrás volver a iniciar sesión.
                </li>

                <li>
                    Tu cuenta quedará desactivada permanentemente.
                </li>

                <li>
                    Si deseas volver a BodyTech en el futuro, deberás registrarte con un correo electrónico diferente.
                </li>

            </ul>

        </div>


        <!-- =========================
             ACCIONES
        ========================== -->

        <div class="delete-account-actions">

            <a
                href="/bodytech/public/usuario/seguridad.php"
                class="btn-cancel"
            >
                <span>←</span>
                Cancelar
            </a>

            <a
                href="/bodytech/public/usuario/confirmar_codigo_eliminacion.php"
                class="btn-delete"
            >
                Continuar
                <span>→</span>
            </a>

        </div>


        <div class="delete-account-footer">

            <span>🔐</span>

            <p>
                Por seguridad, necesitarás completar las verificaciones correspondientes antes de eliminar definitivamente tu cuenta.
            </p>

        </div>

    </section>

</main>

</body>

</html>