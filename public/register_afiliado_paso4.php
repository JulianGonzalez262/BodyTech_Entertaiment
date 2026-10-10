<?php
session_start();

// Seguridad
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Acceso no permitido.');
}

// Guardar datos del paso 3

$nombres = trim($_POST['nombres']);
$apellidos = trim($_POST['apellidos']);

$_SESSION['afiliado']['datos'] = [

    // Compatibilidad con todo el sistema actual
    'nombre' => trim($nombres . ' ' . $apellidos),

    // Nuevo sistema de identidad
    'nombres' => $nombres,
    'apellidos' => $apellidos,

    'fecha_nacimiento' => $_POST['fecha_nacimiento'],
    'genero' => $_POST['genero'],
    'direccion' => $_POST['direccion'],
    'telefono' => $_POST['telefono'],
    'tipo_id' => $_POST['tipo_id'],
    'numero_id' => $_POST['numero_id'],
    'contacto_emergencia' => $_POST['contacto_emergencia'],
    'telefono_emergencia' => $_POST['telefono_emergencia'],
    'email' => $_POST['email'],
    'password' => $_POST['password'], // hash en store.php
    'consentimiento_marketing' => isset($_POST['consentimiento_marketing']),
];

// 💰 Precios según tipo de membresía
$precios = [
    'anual_promo'        => 500000,
    'debito_automatico'  => 20000,
    'debito_flexible'    => 200000,
    'nocturno'           => 70000,
    'prepago_6m'       => 700000,
    'prepago_3m'       => 400000,
    'mensual'            => 200000,
    'departamental'      => 90000
];

$tipo   = $_SESSION['afiliado']['tipo_membresia'];
$precio = $precios[$tipo] ?? 0;

$_SESSION['afiliado']['precio'] = $precio;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmación Afiliación</title>
    <link rel="stylesheet"
      href="/bodytech/public/assets/css/register_afiliado_paso4.css">

<link
    rel="stylesheet"
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

            <h1>BODYTECH</h1>

            <span class="brand">

                Marketplace Fitness

            </span>

            <p>

                Paso 4 de 5 · Revisa cuidadosamente toda la información antes de continuar al pago.

            </p>

        </header>

        <!-- Indicador -->

        <div class="step-progress">

                <div class="step completed">
            <i class="fa-solid fa-check"></i>
        </div>

        <div class="line active"></div>

        <div class="step completed">
            <i class="fa-solid fa-check"></i>
        </div>

        <div class="line active"></div>

        <div class="step completed">
            <i class="fa-solid fa-check"></i>
        </div>

            <div class="line active"></div>

            <div class="step active">4</div>

            <div class="line"></div>

            <div class="step">5</div>

        </div>
    <h2 class="confirmation-title">
    Confirmación de Afiliación:
    </h2>

            <!-- ==========================================================
                RESUMEN DE AFILIACIÓN
        ========================================================== -->

        <div class="confirmation-grid">

            <!-- Gym -->

            <div class="confirmation-card">

                <div class="confirmation-icon">

                    <i class="fa-solid fa-dumbbell"></i>

                </div>

                <h3>

                    Gimnasio seleccionado

                </h3>

                <p>

                    <strong>

                        <?= htmlspecialchars($_SESSION['afiliado']['gym_nombre']) ?>

                    </strong>

                    <br>

                    <?= htmlspecialchars($_SESSION['afiliado']['ciudad']) ?>

                    ·

                    <?= htmlspecialchars($_SESSION['afiliado']['departamento']) ?>

                </p>

            </div>

            <!-- Membresía -->

            <div class="confirmation-card">

                <div class="confirmation-icon">

                    <i class="fa-solid fa-id-card"></i>

                </div>

                <h3>

                    Membresía

                </h3>

                <p>

                    <?= ucwords(str_replace('_',' ',$tipo)) ?>

                </p>

                <div class="confirmation-price">

                    $<?= number_format($precio,0,',','.') ?>

                    <small>COP</small>

                </div>

            </div>

            <!-- Afiliado -->

            <div class="confirmation-card">

                <div class="confirmation-icon">

                    <i class="fa-solid fa-user"></i>

                </div>

                <h3>

                    Afiliado

                </h3>

                <p>

                    <strong>

                        <?= htmlspecialchars($_SESSION['afiliado']['datos']['nombre']) ?>

                    </strong>

                    <br>

                    <?= htmlspecialchars($_SESSION['afiliado']['datos']['email']) ?>

                    <br>

                    <?= htmlspecialchars($_SESSION['afiliado']['datos']['telefono']) ?>

                </p>

            </div>

        </div>

            <!-- ==========================================================
                CONFIRMACIÓN
        ========================================================== -->

        <div class="confirmation-alert">

            <i class="fa-solid fa-circle-check"></i>

            <div>

                <strong>

                    Todo está listo.

                </strong>

                <span>

                    Revisa la información anterior. Al continuar serás dirigido al proceso de pago para finalizar tu afiliación.

                </span>

            </div>

        </div>

        <form
            class="confirmation-form"
            method="POST"
            action="afiliados/paso5_pago.php">

            <button
                type="submit"
                class="next-btn">

                <i class="fa-solid fa-credit-card"></i>

                Confirmar y continuar al pago

            </button>

        </form>

        <div class="back-home">

            <a href="/bodytech/public/index.php">

                <i class="fa-solid fa-arrow-left"></i>

                Volver al inicio

            </a>

</div>
    </section>
</main>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/views/footer.php'; ?>
</body>
</html>
