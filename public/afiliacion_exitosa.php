<?php
session_start();

require_once __DIR__ . '/../app/config/database.php';

// ✅ Validación correcta post-afiliación
if (!isset($_SESSION['usuario']['id'])) {
    die('Acceso no permitido.');
}

$usuario_id = $_SESSION['usuario']['id'];

// Obtener datos del usuario
$stmt = $pdo->prepare("
    SELECT email, email_verificado 
    FROM usuarios 
    WHERE id = :id
    LIMIT 1
");
$stmt->execute(['id' => $usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    die('Usuario no encontrado.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Afiliación Exitosa</title>
    <link rel="stylesheet" href="/bodytech/public/assets/css/afiliacion_exitosa.css">
</head>
<body>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/views/header.php'; ?>

<main class="afiliacion-page">

    <!-- Video -->

    <video
        class="background-video"
        autoplay
        muted
        loop
        playsinline>

        <source
            src="/bodytech/public/assets/videos/hero.mp4"
            type="video/mp4">

    </video>

    <div class="video-overlay"></div>

    <section class="afiliacion-card">

        <!-- Cabecera -->

        <header class="afiliacion-header">

            <div class="logo-circle">

                <i class="fa-solid fa-dumbbell"></i>

            </div>

            <h1>

                BODYTECH

            </h1>

            <span class="brand">

                Marketplace Fitness

            </span>

            <h2>

                ¡Afiliación completada!

            </h2>

            <p>

                Tu registro se completó correctamente. Ya haces parte de la comunidad BodyTech.

            </p>

        </header>

        <!-- ==========================================================
    ÉXITO
========================================================== -->

<div class="success-icon">

    <i class="fa-solid fa-circle-check"></i>

</div>

<div class="success-card">

    <h2>

        ¡Registro realizado con éxito!

    </h2>

    <p>

        Tu afiliación fue registrada correctamente. A partir de este momento podrás acceder a nuestros gimnasios oficiales, comprar productos y disfrutar de todos los beneficios de la comunidad <strong>BODYTECH</strong>.

    </p>

</div>

<!-- ==========================================================
    CORREO
========================================================== -->

<div class="email-card">

    <div class="confirmation-icon">

        <i class="fa-solid fa-envelope"></i>

    </div>

    <div class="email-info">

        <strong>

            Correo registrado

        </strong>

        <span>

            <?= htmlspecialchars($usuario['email']) ?>

        </span>

    </div>

</div>

<?php if (!$usuario['email_verificado']): ?>

<div class="confirmation-alert">

    <i class="fa-solid fa-triangle-exclamation"></i>

    <div>

        <strong>

            Correo pendiente de verificación

        </strong>

        <span>

            Antes de iniciar sesión, verifica tu dirección de correo electrónico. y si ya verificaste tu correo puedes  iniciar el recorrido!!

        </span>

    </div>

</div>

<form
    method="POST"
    action="/bodytech/app/auth/enviar_verificacion.php">

    <button
        type="submit"
        class="next-btn">

        <i class="fa-solid fa-paper-plane"></i>

        Verificar correo electrónico

    </button>

</form>

<?php else: ?>

<div class="confirmation-alert">

    <i class="fa-solid fa-circle-check"></i>

    <div>

        <strong>

            Correo verificado correctamente

        </strong>

        <span>

            Tu cuenta ya está lista para iniciar sesión.

        </span>

    </div>

</div>

<?php endif; ?>

       <!-- ==========================================================
    ACCIONES
========================================================== -->

<div class="success-actions">

    <a
        href="/bodytech/public/login.php"
        class="next-btn">

        <i class="fa-solid fa-right-to-bracket"></i>

        Iniciar Recorrido
    </a>

</div>

<div class="back-home">

    <a href="/bodytech/public/index.php">

        <i class="fa-solid fa-house"></i>

        Volver al inicio

    </a>

</div>


    </section>

</main>


<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/views/footer.php'; ?>

</body>
</html>