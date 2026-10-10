<?php
session_start();

// Seguridad: solo si viene del paso 2
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Acceso no permitido.');
}

// Guardar membresía
$_SESSION['afiliado']['tipo_membresia'] = $_POST['tipo_membresia'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro Afiliado | Paso 3</title>

<link rel="stylesheet"
      href="/bodytech/public/assets/css/register_afiliado_paso3.css">

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body>

<?php require_once '../app/views/header.php'; ?>

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

        Paso 3 de 5 · Completa tus datos personales para continuar con tu afiliación.

    </p>

</header>
<div class="step-progress">

    <div class="step completed">

        <i class="fa-solid fa-check"></i>

    </div>

    <div class="line active"></div>

    <div class="step completed">

        <i class="fa-solid fa-check"></i>

    </div>

    <div class="line active"></div>

    <div class="step active">

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
  

    <form
    class="afiliacion-form"
    method="POST"
    action="register_afiliado_paso4.php"
    id="formPaso3">

       <div class="form-section">

    <div class="section-icon">

        <i class="fa-solid fa-user"></i>

    </div>

    <div>

        <h3>

            Datos personales

        </h3>

        <p>

            Información básica para crear tu afiliación.

        </p>

    </div>

</div>

       <div class="form-group">

    <label class="form-label">

        Nombres

    </label>

    <div class="input-container">

        <i class="fa-solid fa-user input-icon"></i>

        <input
            class="input-field"
            type="text"
            name="nombres"
            placeholder="Ingresa tus nombres"
            pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ ]{2,}"
            required>

    </div>

</div>

      <div class="form-group">

    <label class="form-label">

        Apellidos

    </label>

    <div class="input-container">

        <i class="fa-solid fa-user-group input-icon"></i>

        <input
            class="input-field"
            type="text"
            name="apellidos"
            placeholder="Ingresa tus apellidos"
            pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ ]{2,}"
            required>

    </div>

</div>

        <div class="form-group">

    <label class="form-label">

        Fecha de nacimiento

    </label>

    <div class="input-container">

        <i class="fa-solid fa-calendar-days input-icon"></i>

        <input
            class="input-field"
            type="date"
            name="fecha_nacimiento"
            required>

    </div>

</div>

       <div class="form-group">

    <label class="form-label">

        Género

    </label>

    <div class="input-container select-container">

        <i class="fa-solid fa-venus-mars input-icon"></i>

        <select
            class="input-field"
            name="genero"
            required>

            <option value="">

                Selecciona un género

            </option>

            <option value="masculino">

                Masculino

            </option>

            <option value="femenino">

                Femenino

            </option>

            <option value="no_binario">

                No binario

            </option>

            <option value="otro">

                Otro

            </option>

        </select>

        <i class="fa-solid fa-chevron-down select-arrow"></i>

    </div>

</div>

        <div class="form-group">

    <label class="form-label">

        Dirección

    </label>

    <div class="input-container">

        <i class="fa-solid fa-location-dot input-icon"></i>

        <input
            class="input-field"
            type="text"
            name="direccion"
            placeholder="Calle, Carrera, Avenida..."
            pattern="^(Calle|Carrera|Avenida|Av|Cl|Cra)[A-Za-z0-9 #\-]+"
            required>

    </div>

</div>

        <div class="form-group">

    <label class="form-label">

        Teléfono móvil

    </label>

    <div class="input-container">

        <i class="fa-solid fa-mobile-screen-button input-icon"></i>

        <input
            class="input-field"
            type="text"
            name="telefono"
            placeholder="Número de celular"
            pattern="[0-9]{10}"
            required>

    </div>

</div>

        <div class="form-section">

    <div class="section-icon">

        <i class="fa-solid fa-id-card"></i>

    </div>

    <div>

        <h3>

            Identificación

        </h3>

        <p>

            Documento con el que se registrará tu afiliación.

        </p>

    </div>

</div>

        <div class="form-group">

    <label class="form-label">

        Tipo de identificación

    </label>

   <div class="input-container select-container">

        <i class="fa-solid fa-address-card input-icon"></i>

        <select
            class="input-field"
            name="tipo_id"
            required>

            <option value="">Seleccione una opción</option>

            <option value="CC">Cédula</option>

            <option value="CE">Cédula extranjera</option>

            <option value="PAS">Pasaporte</option>

        </select>

        <i class="fa-solid fa-chevron-down select-arrow"></i>

    </div>

</div>

        <div class="form-group">

    <label class="form-label">

        Número de identificación

    </label>

    <div class="input-container">

        <i class="fa-solid fa-hashtag input-icon"></i>

        <input
            class="input-field"
            type="text"
            name="numero_id"
            placeholder="Ingresa tu número de documento"
            pattern="[0-9]{6,15}"
            required>

    </div>

</div>

       <div class="form-section">

    <div class="section-icon">

        <i class="fa-solid fa-truck-medical"></i>

    </div>

    <div>

        <h3>

            Contacto de emergencia

        </h3>

        <p>

            Persona a contactar en caso de una emergencia.

        </p>

    </div>

</div>

       <div class="form-group">

    <label class="form-label">

        Nombre del contacto

    </label>

    <div class="input-container">

        <i class="fa-solid fa-user-shield input-icon"></i>

        <input
            class="input-field"
            type="text"
            name="contacto_emergencia"
            placeholder="Nombre completo"
            pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ ]{5,}"
            required>

    </div>

</div>

        <div class="form-group">

    <label class="form-label">

        Teléfono del contacto

    </label>

    <div class="input-container">

        <i class="fa-solid fa-phone input-icon"></i>

        <input
            class="input-field"
            type="text"
            name="telefono_emergencia"
            placeholder="Número celular"
            pattern="[0-9]{10}"
            required>

    </div>

</div>

        <div class="form-section">

    <div class="section-icon">

        <i class="fa-solid fa-lock"></i>

    </div>

    <div>

        <h3>

            Datos de acceso

        </h3>

        <p>

            Crea las credenciales que utilizarás para ingresar a tu cuenta BodyTech.

        </p>

    </div>

</div>

       <div class="form-group">

    <label class="form-label">

        Correo electrónico

    </label>

    <div class="input-container">

        <i class="fa-solid fa-envelope input-icon"></i>

        <input
            class="input-field"
            type="email"
            name="email"
            placeholder="correo@ejemplo.com"
            required>

    </div>

</div>

       <div class="form-group">

    <label class="form-label">

        Contraseña

    </label>

    <div class="input-container">

        <i class="fa-solid fa-lock input-icon"></i>

        <input
            class="input-field"
            type="password"
            name="password"
            id="password"
            placeholder="Crea una contraseña segura"
            required>
        <button
            type="button"
            class="password-toggle"
            id="togglePassword">

            <i class="fa-solid fa-eye"></i>

        </button>

    </div>

</div>



      <div class="form-section">

    <div class="section-icon">

        <i class="fa-solid fa-file-signature"></i>

    </div>

    <div>

        <h3>

            Consentimientos

        </h3>

        <p>

            Confirma las autorizaciones necesarias para finalizar tu afiliación.

        </p>

    </div>

</div>

        <label class="checkbox-card">

    <input
        type="checkbox"
        name="consentimiento_marketing">

    <span class="checkmark"></span>

    <div>

        <strong>

            Comunicaciones comerciales

        </strong>

        <p>

            Deseo recibir promociones, novedades y ofertas de BodyTech.

        </p>

    </div>

</label>

        <label class="checkbox-card required">

    <input
        type="checkbox"
        name="politicas"
        required>

    <span class="checkmark"></span>

    <div>

        <strong>

            Políticas de privacidad

        </strong>

        <p>

            He leído y acepto las políticas de tratamiento de datos personales.

        </p>

    </div>

</label>

       <div class="affiliate-actions">

    <button
        type="submit"
        class="btn-primary">

        Continuar al paso 4

        <i class="fa-solid fa-arrow-right"></i>

    </button>

</div>
    </form>
</section>

</main>

<?php require_once '../app/views/footer.php'; ?>

<script src="/bodytech/public/assets/js/registro_afiliado.js"></script>

</body>
</html>
