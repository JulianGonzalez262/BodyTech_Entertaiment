<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

$total_notificaciones = 0;
$max_notificacion_id = 0;

if (isset($_SESSION['usuario_id'])) {

    $stmtNotif = $pdo->prepare("
        SELECT COUNT(*)
        FROM notificaciones
        WHERE usuario_id = ?
        AND leida = 0
    ");

    $stmtNotif->execute([
        $_SESSION['usuario_id']
    ]);

    $total_notificaciones = $stmtNotif->fetchColumn();

    // ID más reciente que ya existe (para que el polling solo traiga lo nuevo)
    $stmtMaxId = $pdo->prepare("
        SELECT MAX(id)
        FROM notificaciones
        WHERE usuario_id = ?
    ");

    $stmtMaxId->execute([
        $_SESSION['usuario_id']
    ]);

    $max_notificacion_id = (int) $stmtMaxId->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="es">
<head <link
    rel="preconnect"
    href="https://fonts.googleapis.com"
>

<link
    rel="preconnect"
    href="https://fonts.gstatic.com"
    crossorigin
>

<link
    href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700;800;900&display=swap"
    rel="stylesheet"
>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/bodytech/public/assets/img/bodybt.png">

    <title>BodyTech Entertaiment</title>

    <link rel="stylesheet" href="/bodytech/public/assets/css/index.css?v=3">
<link rel="preconnect" href="https://fonts.googleapis.com">

<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>
<body>

<?php if (isset($_SESSION['usuario_id'])): ?>
    <script>
        window.BODYTECH_USER_ID = <?= (int) $_SESSION['usuario_id'] ?>;
        window.BODYTECH_LAST_NOTIF_ID = <?= (int) $max_notificacion_id ?>;
    </script>
<?php endif; ?>

<header class="site-header">

    <div class="logo-container">

    <a href="/bodytech/public/index.php" class="logo-link">

        <img
            src="/bodytech/public/assets/img/loga.png"
            alt="BodyTech"
            class="logo-image"
        >

    </a>

</div>

    <nav class="main-nav">

    <a href="/bodytech/public/index.php" class="nav-link">
        Inicio
    </a>

    <a href="/bodytech/public/register_afiliado.php" class="nav-link">
        Afíliate Ya
    </a>

    <a href="/bodytech/public/afiliados/index.php" class="nav-link nav-button">
        Consulta tu Afiliación
    </a>


    <?php if(!isset($_SESSION['usuario'])): ?>

        <a href="/bodytech/public/registro_cliente.php" class="nav-link">
            Registrarse
        </a>

        <a href="/bodytech/public/login.php" class="nav-link">
            Iniciar Sesión
        </a>

    <?php else: ?>

       


        <a
            href="/bodytech/public/carrito.php"
            class="nav-link cart-link"
        >
            🛒
        </a>


        <a
            href="/bodytech/public/mis_compras.php"
            class="nav-link purchases-link"
        >
            Mis Compras
        </a>


        <a
            href="/bodytech/public/notificaciones/notificaciones.php"
            class="nav-link notifications-link"
        >

            🔔

            <?php if($total_notificaciones > 0): ?>

                <span class="notification-badge">

                    <?= $total_notificaciones ?>

                </span>

            <?php endif; ?>

        </a>


  <?php

$fotoUsuario = '/bodytech/public/assets/img/default-avatar.png';

if (isset($_SESSION['usuario_id'])) {

    $stmtUser = $pdo->prepare("
        SELECT foto_perfil
        FROM usuarios
        WHERE id = ?
    ");

    $stmtUser->execute([
        $_SESSION['usuario_id']
    ]);

    $fotoUsuario = $stmtUser->fetchColumn();

    $fotoUsuario = $fotoUsuario
        ?: '/bodytech/public/assets/img/default-avatar.png';

    if (!str_starts_with($fotoUsuario, '/')) {
        $fotoUsuario = '/bodytech/public/' . ltrim($fotoUsuario, '/');
    }
}

?>

    <a
        href="/bodytech/public/usuario/mi_cuenta.php"
        class="user-profile-link"
    >

        <img
            src="<?= htmlspecialchars($fotoUsuario) ?>"
            alt="Perfil"
            class="header-profile-image"
        >

        <span class="user-name">

            <?= htmlspecialchars($_SESSION['usuario']['nombre']) ?>

        </span>

    </a>
         <a
            href="/bodytech/app/auth/logout.php"
            class="nav-link logout-link"
        >
             Cerrar Sesión
        </a>

    <?php endif; ?>

</nav>

        </header>

<!-- 🔄 Script de notificaciones (polling, sin Node) -->
<?php if (isset($_SESSION['usuario_id'])): ?>
    <script src="/bodytech/public/assets/js/notificaciones-polling.js"></script>
<?php endif; ?>
<!-- ============================
     CHAT DE IA — BODYBOT (NUTRICIÓN Y ENTRENAMIENTO)
============================= -->

<link rel="stylesheet" href="/bodytech/public/assets/css/chat-ia.css">

<div id="chatIaWrap" class="chat-ia-bubble-wrap">

    <span class="chat-ia-tooltip">
        ¡Consulta aquí con BodyBot!
    </span>

    <button id="chatIaBubble" class="chat-ia-bubble" aria-label="Abrir chat de nutrición y entrenamiento">
        <img src="/bodytech/public/assets/img/bodybot.png" alt="BodyBot">
    </button>

    <div id="chatIaWindow" class="chat-ia-window">

        <div class="chat-ia-header">
            <div>
                BodyBot
                <small>Nutrición · Entrenamiento · Calistenia</small>
            </div>
            <button id="chatIaClose" class="chat-ia-close" aria-label="Cerrar chat">✕</button>
        </div>

        <div id="chatIaMessages" class="chat-ia-messages">
            <div class="chat-ia-msg chat-ia-msg-bot">
                ¡Hola! Soy <strong>BodyBot</strong>, tu asistente de nutrición y entrenamiento.
                Pregúntame sobre ejercicios, rutinas, calistenia o alimentación. 💪
            </div>
        </div>

        <form id="chatIaForm" class="chat-ia-form">
            <input
                id="chatIaInput"
                class="chat-ia-input"
                type="text"
                placeholder="Escribe tu pregunta..."
                autocomplete="off"
            >
            <button type="submit" class="chat-ia-send">➤</button>
        </form>

    </div>

</div>

<script src="/bodytech/public/assets/js/chat-ia.js"></script>

</body>
</html>
