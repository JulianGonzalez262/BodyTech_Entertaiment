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

    <title>
        Mis reportes | BodyTech
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
            📄 Centro de reportes
        </h1>

        <p>
            Descarga y consulta toda la información asociada a tu cuenta.
        </p>

        <div class="account-options">

            <a
                href="generar_reporte_perfil.php"
                class="account-option"
            >
                👤 Descargar mi perfil
            </a>

            <a
                href="#"
                class="account-option"
            >
                🏋️ Mis afiliaciones (Próximamente)
            </a>

            <a
                href="#"
                class="account-option"
            >
                🛒 Mis compras (Próximamente)
            </a>

            <a
                href="#"
                class="account-option"
            >
                📨 Mis PQRS (Próximamente)
            </a>

            <a
                href="#"
                class="account-option"
            >
                🌎 Reporte completo (Próximamente)
            </a>

        </div>

    </section>

</main>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/app/views/footer.php'; ?>

</body>
</html>