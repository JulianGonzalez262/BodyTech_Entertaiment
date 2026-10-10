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

$usuario_id = $_SESSION['usuario_id'];

$segundoFactorVerificado =
    isset($_SESSION['eliminacion_2fa_verificado']) &&
    $_SESSION['eliminacion_2fa_verificado'] === true &&
    isset($_SESSION['eliminacion_2fa_usuario_id']) &&
    $_SESSION['eliminacion_2fa_usuario_id'] === $usuario_id;

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>
        Confirmación final | BodyTech
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
            ⚠️ Confirmación final
        </h1>

        <h2>
            Tu identidad ha sido verificada.
        </h2>

        <p>
            Has completado correctamente las verificaciones de seguridad
            necesarias para solicitar la eliminación de tu cuenta.
        </p>

        <div class="warning-box">

            <h3>
                Esta acción es permanente
            </h3>

            <p>
                Al confirmar la eliminación:
            </p>

            <ul>

                <li>
                    Tu cuenta dejará de estar disponible.
                </li>

                <li>
                    Ya no podrás iniciar sesión con este correo.
                </li>

                <li>
                    Se eliminarán los datos personales que puedan ser
                    eliminados de acuerdo con las reglas de conservación
                    de información de BodyTech.
                </li>

                <li>
                    Si deseas volver a utilizar BodyTech en el futuro,
                    deberás registrarte nuevamente con otro correo.
                </li>

            </ul>

        </div>

        <div class="important-note">

            <strong>
                Última oportunidad
            </strong>

            <p>
                Si no deseas eliminar tu cuenta, puedes cancelar ahora
                y conservarla normalmente.
            </p>

        </div>

        <div class="buttons">

            <a
                href="/bodytech/public/usuario/mi_cuenta.php"
                class="btn-cancel"
            >
                Cancelar
            </a>

            <form
                action="/bodytech/public/usuario/eliminar_cuenta_final.php"
                method="POST"
                style="display:inline;"
            >

                <input
                    type="hidden"
                    name="confirmar"
                    value="1"
                >

                <button
                    type="submit"
                    class="btn-delete"
                >
                    🗑️ Eliminar definitivamente mi cuenta
                </button>

            </form>

        </div>

    </section>

</main>

<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/views/footer.php';
?>

</body>

</html>