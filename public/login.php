<link rel="stylesheet" href="/bodytech/public/assets/css/login.css">
<link rel="stylesheet" href="/bodytech/public/assets/css/components/toast.css">
<link rel="stylesheet" href="/bodytech/public/assets/css/components/forms.css">

<?php require_once '../app/views/header.php'; ?>

<main class="login-page">

    <!-- Video de fondo -->
    <video
        class="background-video"
        autoplay
        muted
        loop
        playsinline
        preload="auto"
        poster="/bodytech/public/assets/images/gym-poster.jpg"
        aria-hidden="true">

        <source src="/bodytech/public/assets/videos/hero.mp4" type="video/mp4">

    </video>

    <!-- Overlay -->
    <div
        class="video-overlay"
        aria-hidden="true">
    </div>

    <!-- Contenedor -->
    <section
        class="login-wrapper"
        aria-labelledby="login-title">

        <div class="login-card">

            <!-- Cabecera -->
            <header class="login-header">

                <div
                    class="logo-circle"
                    aria-hidden="true">

                    <i class="fa-solid fa-dumbbell"></i>

                </div>

                <h1 class="brand-title">

                    BODYTECH

                </h1>

                <span class="brand">

                    Marketplace Fitness

                </span>

                <h2 id="login-title">

                    Bienvenido nuevamente

                </h2>

                <p class="description">

                    Inicia sesión para acceder a tu cuenta.

                </p>

            </header>

            <!-- Formulario -->
            <form
                class="login-form"
                method="POST"
                action="/bodytech/app/auth/login.php"
                novalidate>

                <input
                    type="hidden"
                    name="login_type"
                    value="cliente">

                <!-- =========================
                     EMAIL
                ========================== -->

                <div class="input-group">

                    <label
                        for="email"
                        class="form-label">

                        Correo electrónico

                    </label>

                    <div class="input-container">

                        <i
                            class="fa-solid fa-envelope input-icon"
                            aria-hidden="true">
                        </i>

                        <input

                            type="email"

                            id="email"

                            name="email"

                            class="input-field"

                            placeholder="ㅤㅤㅤcorreo@ejemplo.com"

                            autocomplete="email"

                            autocapitalize="off"

                            spellcheck="false"

                            inputmode="email"

                            required>

                    </div>

                    <small
                        class="input-message"
                        aria-live="polite">
                    </small>

                </div>

                <!-- =========================
                     PASSWORD
                ========================== -->

                <div class="input-group">

                    <label
                        for="password"
                        class="form-label">

                        Contraseña

                    </label>

                    <div class="input-container">

                        <i
                            class="fa-solid fa-lock input-icon"
                            aria-hidden="true">
                        </i>

                        <input

                            type="password"

                            id="password"

                            name="password"

                            class="input-field"

                            placeholder="ㅤㅤㅤ••••••••"

                            autocomplete="current-password"

                            spellcheck="false"

                            required>

                        <button

                            type="button"

                            class="toggle-password"

                            aria-label="Mostrar u ocultar contraseña"

                            aria-controls="password">

                            <i class="fa-solid fa-eye"></i>

                        </button>

                    </div>

                    <small
                        class="caps-warning"
                        aria-live="polite">

                        <i class="fa-solid fa-keyboard"></i>

                        Bloq Mayús activado

                    </small>

                    <small
                        class="input-message"
                        aria-live="polite">
                    </small>

                </div>

                <!-- Opciones -->

                <div class="form-options">

                    <a
                        href="/bodytech/public/forgot_password.php">

                        ¿Olvidaste tu contraseña?

                    </a>

                </div>

                <!-- Botón -->

                <button
                    class="btn btn-primary login-btn"
                    type="submit">

                    <i class="fa-solid fa-right-to-bracket"></i>

                    <span>

                        Iniciar sesión

                    </span>

                </button>

                <div class="login-divider">
                    <span>o</span>
               

                    <a href="/bodytech/app/auth/login_google.php" class="google-login-btn">

                        <img
                            src="/bodytech/public/assets/img/google-logo.png"
                            alt="Google"
                        >

                        <span>Continuar con Google</span>

                    </a>
                </div>
                
            </form>

            <!-- Footer -->

            <footer class="login-footer">

                <a
                    href="/bodytech/public/index.php">

                    <i class="fa-solid fa-arrow-left"></i>

                    <span>

                        Volver al inicio

                    </span>

                </a>

            </footer>

        </div>

    </section>

</main>

<script src="/bodytech/public/assets/js/main.js"></script>
<script src="/bodytech/public/assets/js/components/toast.js"></script>
<script src="/bodytech/public/assets/js/components/forms.js"></script>
<script src="/bodytech/public/assets/js/login.js"></script>

<?php require_once '../app/views/footer.php'; ?>