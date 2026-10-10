<?php

session_start();

/*
|--------------------------------------------------------------------------
| Esta página solo debe mostrarse después de una eliminación exitosa.
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['cuenta_eliminada_exitosamente'])) {

    header('Location: /bodytech/public/login.php');
    exit;

}

unset($_SESSION['cuenta_eliminada_exitosamente']);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cuenta eliminada | BodyTech</title>

    <link
        rel="stylesheet"
        href="/bodytech/public/assets/css/mi_cuenta.css"
    >

</head>

<body>

<main class="account-container">

    <section class="account-content">

        <h1>Cuenta eliminada</h1>

        <p>
            Tu cuenta de BodyTech ha sido eliminada correctamente.
        </p>

        <p>
            Todos los procesos asociados a tu cuenta que podían
            eliminarse fueron procesados correctamente.
        </p>

        <p>
            Gracias por haber formado parte de BodyTech.
        </p>

        <a
            href="/bodytech/public/login.php"
            class="btn-primary"
        >
            Volver al inicio de sesión
        </a>

    </section>

</main>

</body>

</html>