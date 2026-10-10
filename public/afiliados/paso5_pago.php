<?php
session_start();

// Blindaje básico
if (!isset($_SESSION['afiliado'])) {
    header('Location: register_afiliado.php');
    exit;
}

$afiliado = $_SESSION['afiliado'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pago de Membresía</title>
    <link rel="stylesheet" href="/bodytech/public/assets/css/pago.css">
</head>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/views/header.php'; ?>

<main class="afiliacion-page">

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

<header class="afiliacion-header">

    <div class="logo-circle">

        <i class="fa-solid fa-dumbbell"></i>

    </div>

    <h1>BODYTECH</h1>

    <span class="brand">

        Marketplace Fitness

    </span>

    <h2>

        Pago de Membresía

    </h2>

    <p>

        Paso 5 de 5 · Finaliza tu afiliación realizando el pago de forma segura.

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

    <div class="step completed">
        <i class="fa-solid fa-check"></i>
    </div>

    <div class="line active"></div>

    <div class="step active">

        5

    </div>

</div>

<h2 class="confirmation-title">

    Resumen del pago

</h2>

<div class="confirmation-grid">

    <!-- Gym -->

    <div class="confirmation-card">

        <div class="confirmation-icon">

            <i class="fa-solid fa-dumbbell"></i>

        </div>

        <h3>

            Gimnasio

        </h3>

        <p>

            <strong>

                <?= htmlspecialchars($afiliado['gym_nombre']) ?>

            </strong>

            <br>

            <?= htmlspecialchars($afiliado['ciudad']) ?>

            ·

            <?= htmlspecialchars($afiliado['departamento']) ?>

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

            <?= ucwords(str_replace('_',' ',$afiliado['tipo_membresia'])) ?>

        </p>

    </div>

    <!-- Valor -->

    <div class="confirmation-card">

        <div class="confirmation-icon">

            <i class="fa-solid fa-money-bill-wave"></i>

        </div>

        <h3>

            Valor a pagar

        </h3>

        <div class="confirmation-price">

            $<?= number_format($afiliado['precio'],0,',','.') ?>

            <small>COP</small>

        </div>

    </div>

</div>

<form
    class="afiliacion-form"
    method="POST"
    action="/bodytech/app/afiliados/store.php">

    <h2 class="confirmation-title">

        Datos de pago

    </h2>

    <!-- Tipo de tarjeta -->

    <div class="form-group">

        <label class="form-label">

            Tipo de tarjeta

        </label>

        <div class="input-container">

            <i class="fa-solid fa-credit-card input-icon"></i>

            <select
                name="tipo_tarjeta"
                class="input-field"
                required>

                <option value="">Seleccione</option>
                <option value="credito">Crédito</option>
                <option value="debito">Débito</option>

            </select>

            <i class="fa-solid fa-chevron-down select-arrow"></i>

        </div>

    </div>

    <!-- Número -->

    <div class="form-group">

        <label class="form-label">

            Número de tarjeta

        </label>

        <div class="input-container">

            <i class="fa-solid fa-credit-card input-icon"></i>

            <input
                class="input-field"
                type="text"
                name="numero_tarjeta"
                maxlength="19"
                placeholder="XXXX XXXX XXXX XXXX"
                pattern="[0-9 ]{13,19}"
                required>

        </div>

    </div>

    <!-- Titular -->

    <div class="form-group">

        <label class="form-label">

            Nombre del titular

        </label>

        <div class="input-container">

            <i class="fa-solid fa-user input-icon"></i>

            <input
                class="input-field"
                type="text"
                name="titular"
                pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ ]{5,}"
                required>

        </div>

    </div>

    <!-- Vencimiento -->

    <div class="form-group">

        <label class="form-label">

            Fecha de vencimiento

        </label>

        <div class="input-container">

            <i class="fa-solid fa-calendar input-icon"></i>

            <input
                class="input-field"
                type="month"
                name="vencimiento"
                required>

        </div>

    </div>

    <!-- CVV -->

    <div class="form-group">

        <label class="form-label">

            CVV / CVC

        </label>

        <div class="input-container">

            <i class="fa-solid fa-lock input-icon"></i>

            <input
                class="input-field"
                type="password"
                maxlength="4"
                placeholder="***"
                required>

        </div>

    </div>

    <!-- Monto -->

    <div class="form-group">

        <label class="form-label">

            Monto a pagar

        </label>

        <div class="input-container">

            <i class="fa-solid fa-money-bill-wave input-icon"></i>

            <input
                class="input-field"
                type="text"
                value="$<?= number_format($afiliado['precio'],0,',','.') ?> COP"
                readonly>

        </div>

    </div>

    <div class="confirmation-alert">

        <i class="fa-solid fa-shield-halved"></i>

        <div>

            <strong>

                Pago seguro.

            </strong>

            <span>

                Los datos de tu tarjeta se utilizan únicamente para procesar el pago y no serán almacenados por BodyTech.

            </span>

        </div>

    </div>

    <button
        type="submit"
        class="next-btn">

        <i class="fa-solid fa-circle-check"></i>

        Confirmar pago y finalizar afiliación

    </button>

</form>

<div class="back-home">

    <a href="/bodytech/public/index.php">

        <i class="fa-solid fa-xmark"></i>

        Cancelar afiliación

    </a>

</div>

</section>

</main>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/views/footer.php'; ?>
</html>
