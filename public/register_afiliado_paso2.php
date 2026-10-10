<?php
session_start();

// Validar que venga del paso 1
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Acceso no permitido.');
}

// Guardar selección del paso 1 en sesión
$_SESSION['afiliado'] = [
    'departamento' => $_POST['departamento'],
    'ciudad'       => $_POST['ciudad'],
    'gym_nombre'   => $_POST['gym_nombre']
];
?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>

        Registro Afiliado - Paso 2

    </title>

    <link rel="stylesheet"
          href="/bodytech/public/assets/css/register_afiliado_paso2.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>

<?php require_once '../app/views/header.php'; ?>

<main class="afiliacion-page">

    <!-- Video de fondo -->

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

                Registro de Afiliación

            </h2>

            <p>

                Paso 2 de 5 · Selecciona la membresía ideal para continuar con tu afiliación.

            </p>

        </header>

        <!-- Indicador -->

        <div class="step-progress">

            <div class="step completed">

                <i class="fa-solid fa-check"></i>

            </div>

            <div class="line active"></div>

            <div class="step active">

                2

            </div>

            <div class="line"></div>

            <div class="step">

                3

            </div>

            <div class="line"></div>

            <div class="step">

                4

            </div>

            <div class="line"></div>

            <div class="step">

                5

            </div>

        </div>

        <!-- Gym seleccionado -->

        <div class="gym-selected">

            <div class="gym-selected-icon">

                <i class="fa-solid fa-location-dot"></i>

            </div>

            <div class="gym-selected-info">

                <strong>

                    <?= htmlspecialchars($_SESSION['afiliado']['gym_nombre']) ?>

                </strong>

                <span>

                    <?= htmlspecialchars($_SESSION['afiliado']['ciudad']) ?>

                    ·

                    <?= htmlspecialchars($_SESSION['afiliado']['departamento']) ?>

                </span>

            </div>

        </div>

        <!-- Formulario -->

        <form
            class="afiliacion-form"
            method="POST"
            action="register_afiliado_paso3.php">

        <!-- ==========================================================
     MEMBRESÍAS
========================================================== -->

<div class="membership-list">

    <!-- PLAN ANUAL -->

    <label class="membership-card featured">

        <input
            type="radio"
            name="tipo_membresia"
            value="anual_promo"
            required>

        <div class="membership-body">

            <span class="membership-badge">

                MÁS POPULAR

            </span>

            <h3>

                Plan Prepagado Anual

            </h3>

            <div class="membership-price">

                $500.000 COP

            </div>

            <p>

                Acceso ilimitado durante 12 meses a todas las sedes BodyTech.

            </p>

            <ul>

                <li><i class="fa-solid fa-check"></i> Acceso 24/7</li>

                <li><i class="fa-solid fa-check"></i> Todas las sedes</li>

                <li><i class="fa-solid fa-check"></i> Pago único</li>

            </ul>

        </div>

    </label>

    <!-- DÉBITO AUTOMÁTICO -->

    <label class="membership-card">

        <input
            type="radio"
            name="tipo_membresia"
            value="debito_automatico">

        <div class="membership-body">

            <h3>

                Débito Automático

            </h3>

            <div class="membership-price">

                $20.000 <small>/ mes</small>

            </div>

            <p>

                Cobro automático mensual con permanencia mínima.

            </p>

            <ul>

                <li><i class="fa-solid fa-check"></i> Permanencia 6 meses</li>

                <li><i class="fa-solid fa-check"></i> Acceso 24 horas</li>

                <li><i class="fa-solid fa-check"></i> Renovación automática</li>

            </ul>

        </div>

    </label>

    <!-- DÉBITO FLEXIBLE -->

    <label class="membership-card">

        <input
            type="radio"
            name="tipo_membresia"
            value="debito_flexible">

        <div class="membership-body">

            <h3>

                Débito Automático Flexible

            </h3>

            <div class="membership-price">

                $200.000 <small>/ mes</small>

            </div>

            <p>

                Cancela cuando quieras sin permanencia.

            </p>

            <ul>

                <li><i class="fa-solid fa-check"></i> Sin cláusula</li>

                <li><i class="fa-solid fa-check"></i> Acceso nacional</li>

                <li><i class="fa-solid fa-check"></i> Disponible 24/7</li>

            </ul>

        </div>

    </label>

    <!-- PLAN NOCTURNO -->

    <label class="membership-card">

        <input
            type="radio"
            name="tipo_membresia"
            value="nocturno">

        <div class="membership-body">

            <h3>

                Plan Nocturno

            </h3>

            <div class="membership-price">

                $70.000 <small>/ mes</small>

            </div>

            <p>

                Entrena exclusivamente en horario nocturno.

            </p>

            <ul>

                <li><i class="fa-solid fa-check"></i> Horario nocturno</li>

                <li><i class="fa-solid fa-check"></i> Menor costo</li>

                <li><i class="fa-solid fa-check"></i> Acceso seguro</li>

            </ul>

        </div>

    </label>

    <!-- PREPAGO 6 MESES -->

    <label class="membership-card">

        <input
            type="radio"
            name="tipo_membresia"
            value="prepago_6m">

        <div class="membership-body">

            <h3>

                Prepagado 6 Meses

            </h3>

            <div class="membership-price">

                $700.000 COP

            </div>

            <p>

                Pago único por seis meses consecutivos.

            </p>

        </div>

    </label>

    <!-- PREPAGO 3 MESES -->

    <label class="membership-card">

        <input
            type="radio"
            name="tipo_membresia"
            value="prepago_3m">

        <div class="membership-body">

            <h3>

                Prepagado 3 Meses

            </h3>

            <div class="membership-price">

                $400.000 COP

            </div>

            <p>

                Ideal para comenzar tu entrenamiento.

            </p>

        </div>

    </label>

    <!-- MENSUAL -->

    <label class="membership-card">

        <input
            type="radio"
            name="tipo_membresia"
            value="mensual">

        <div class="membership-body">

            <h3>

                Mensual Estándar

            </h3>

            <div class="membership-price">

                $200.000 COP

            </div>

            <p>

                Acceso completo durante 30 días.

            </p>

        </div>

    </label>

    <!-- DEPARTAMENTAL -->

    <label class="membership-card">

        <input
            type="radio"
            name="tipo_membresia"
            value="departamental">

        <div class="membership-body">

            <h3>

                Exclusivo por Departamento

            </h3>

            <div class="membership-price">

                $90.000 <small>/ mes</small>

            </div>

            <p>

                Acceso únicamente a las sedes del departamento seleccionado.

            </p>

        </div>

    </label>

</div>

<button
    type="submit"
    class="next-btn">

    Continuar al Paso 3

    <i class="fa-solid fa-arrow-right"></i>

</button>

</form>

<div class="back-home">


</div>

</section>

</main>

<?php require_once '../app/views/footer.php'; ?>

</body>
</html>