<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

// PHPMailer
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/libs/PHPMailer-master/src/Exception.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/libs/PHPMailer-master/src/PHPMailer.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/libs/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombres = trim($_POST['nombres'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $nombre = trim($nombres . ' ' . $apellidos);
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $captcha = $_POST['g-recaptcha-response'] ?? '';

    if (
        !$nombres ||
        !$apellidos ||
        !$email ||
        !$password ||
        !$confirm_password
    ) {
        $mensaje = "Todos los campos son obligatorios.";
    }

    elseif ($password !== $confirm_password) {
        $mensaje = "Las contraseñas no coinciden.";
    }

   elseif (
    strlen($password) < 8 ||
    !preg_match('/[A-Z]/', $password) ||
    !preg_match('/[a-z]/', $password) ||
    !preg_match('/[0-9]/', $password) ||
    !preg_match('/[^A-Za-z0-9]/', $password)
    ) {
        $mensaje = "La contraseña debe tener mínimo 8 caracteres, mayúscula, minúscula, número y carácter especial.";
    }

    elseif (empty($captcha)) {
        $mensaje = "Debes verificar que no eres un robot.";
    }

    else {

        // VALIDAR CAPTCHA CON GOOGLE
        $secretKey = "6LeEuNQsAAAAAPCjsikuK9tulzyJ0y1q2AE25TWQ";
        $ip = $_SERVER['REMOTE_ADDR'];

        $url = "https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$captcha&remoteip=$ip";
        $respuesta = file_get_contents($url);
        $atributos = json_decode($respuesta, true);

        if (!$atributos["success"]) {
            $mensaje = "Captcha inválido. Intenta nuevamente.";
        }

        else {

            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);

            if ($stmt->fetch()) {
                $mensaje = "Este correo ya está registrado.";
            } else {

                $hash = password_hash($password, PASSWORD_DEFAULT);
                $token = bin2hex(random_bytes(32));

                $stmt = $pdo->prepare("
                    INSERT INTO usuarios
                    (
                        nombre,
                        nombres,
                        apellidos,
                        email,
                        password,
                        rol_id,
                        email_verificado,
                        email_token
                    )
                    VALUES
                    (
                        :nombre,
                        :nombres,
                        :apellidos,
                        :email,
                        :password,
                        1,
                        0,
                        :token
                    )
                ");

                $stmt->execute([
                    'nombre'     => $nombre,
                    'nombres'    => $nombres,
                    'apellidos'  => $apellidos,
                    'email'      => $email,
                    'password'   => $hash,
                    'token'      => $token
                ]);

                $usuario_id = $pdo->lastInsertId();
                $_SESSION['usuario'] = [
                    'id'        => $usuario_id,
                    'email'     => $email,
                    'nombre'    => $nombre,
                    'nombres'   => $nombres,
                    'apellidos' => $apellidos
                ];

                    $stmtPrefs = $pdo->prepare("
                        INSERT INTO preferencias_usuario
                        (
                            usuario_id
                        )
                        VALUES
                        (
                            ?
                        )
                    ");

                    $stmtPrefs->execute([
                        $usuario_id
                    ]);

                $enlace = "http://localhost/bodytech/app/auth/verificar_correo.php?token=" . $token;

                $mail = new PHPMailer(true);

                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'mirtaleskilo@gmail.com';
                    $mail->Password   = 'lhcb vmrk ogbg vpww';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port       = 587;

                    $mail->setFrom('mirtaleskilo@gmail.com', 'Bodytech');
                    $mail->addAddress($email);

                    $mail->isHTML(true);
                    $mail->Subject = 'Verifica tu correo electronico';
                    $mail->Body = "
                        <h2>Bienvenido a Bodytech</h2>
                        <p>Tu cuenta fue creada correctamente.</p>
                        <p>Haz clic aquí para verificar tu correo:</p>
                        <a href='$enlace'>Verificar correo</a>
                    ";

                    $mail->send();

                    header("Location: /bodytech/public/afiliacion_exitosa.php");
                    exit;

                } catch (Exception $e) {
                    $mensaje = "Cuenta creada, pero no se pudo enviar el correo: " . $mail->ErrorInfo;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Crear cuenta | BodyTech</title>

    <!-- CSS -->

    <link rel="stylesheet" href="/bodytech/public/assets/css/variables.css">

    <link rel="stylesheet" href="/bodytech/public/assets/css/animations.css">

    <link rel="stylesheet" href="/bodytech/public/assets/css/components/forms.css">

    <link rel="stylesheet" href="/bodytech/public/assets/css/components/toast.css">

    <link rel="stylesheet" href="/bodytech/public/assets/css/register.css">

    <!-- Font Awesome -->

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <!-- reCAPTCHA -->

    <script src="https://www.google.com/recaptcha/api.js"
            async
            defer></script>

</head>

<body>

<main class="register-page">

    <!-- Video -->

    <video class="background-video"
           autoplay
           muted
           loop
           playsinline>

        <source
            src="/bodytech/public/assets/videos/hero.mp4"
            type="video/mp4">

    </video>

    <div class="video-overlay"></div>

    <section class="register-card">

        <header class="register-header">

            <div class="logo-circle">

                <i class="fa-solid fa-dumbbell"></i>

            </div>

            <h1>BODYTECH</h1>

            <span class="brand">

                Marketplace Fitness

            </span>

            <h2>

                Crear cuenta

            </h2>

            <p>

                Completa los datos para comenzar tu transformación.

            </p>

        </header>

        <?php if($mensaje): ?>

            <div class="server-message"
                 data-message="<?= htmlspecialchars($mensaje) ?>">
            </div>

        <?php endif; ?>

        <form class="register-form"
              method="POST"
              autocomplete="on"
              novalidate>
        <!-- =========================
     NOMBRES
========================= -->

<div class="form-group">

    <label for="nombres" class="form-label">

        Nombres

    </label>

    <div class="input-container">

        <i class="fa-solid fa-user input-icon"></i>

        <input
            class="input-field"
            type="text"
            id="nombres"
            name="nombres"
            placeholder="Ingresa tus nombres"
            autocomplete="given-name"
            spellcheck="false"
            required>

    </div>

    <small class="input-message"></small>

</div>

<!-- =========================
     APELLIDOS
========================= -->

<div class="form-group">

    <label for="apellidos" class="form-label">

        Apellidos

    </label>

    <div class="input-container">

        <i class="fa-solid fa-id-card input-icon"></i>

        <input
            class="input-field"
            type="text"
            id="apellidos"
            name="apellidos"
            placeholder="Ingresa tus apellidos"
            autocomplete="family-name"
            spellcheck="false"
            required>

    </div>

    <small class="input-message"></small>

</div>

<!-- =========================
     EMAIL
========================= -->

<div class="form-group">

    <label for="email" class="form-label">

        Correo electrónico

    </label>

    <div class="input-container">

        <i class="fa-solid fa-envelope input-icon"></i>

        <input
            class="input-field"
            type="email"
            id="email"
            name="email"
            placeholder="correo@ejemplo.com"
            autocomplete="email"
            autocapitalize="off"
            spellcheck="false"
            inputmode="email"
            required>

    </div>

    <small class="input-message"></small>

</div>

<!-- =========================
     CONTRASEÑA
========================= -->

<div class="form-group">

    <label for="password" class="form-label">

        Contraseña

    </label>

    <div class="input-container">

        <i class="fa-solid fa-lock input-icon"></i>

        <input
            class="input-field"
            type="password"
            id="password"
            name="password"
            placeholder="Crea una contraseña"
            autocomplete="new-password"
            minlength="8"
            required>

        <button
            type="button"
            class="toggle-password"
            aria-label="Mostrar contraseña">

            <i class="fa-solid fa-eye"></i>

        </button>

    </div>

    <small class="caps-warning">

        <i class="fa-solid fa-keyboard"></i>

        Bloq Mayús activado

    </small>

    <small class="input-message"></small>

</div>

<!-- =========================
     FUERZA
========================= -->

<div class="password-strength">

    <div class="strength-bar">

        <div class="strength-fill"></div>

    </div>

    <small class="strength-text">

        Usa mínimo 8 caracteres, una mayúscula, una minúscula, un número y un símbolo.

    </small>

</div>

<!-- =========================
     CONFIRMAR
========================= -->

<div class="form-group">

    <label for="confirm_password" class="form-label">

        Confirmar contraseña

    </label>

    <div class="input-container">

        <i class="fa-solid fa-shield-halved input-icon"></i>

        <input
            class="input-field"
            type="password"
            id="confirm_password"
            name="confirm_password"
            placeholder="Repite la contraseña"
            autocomplete="new-password"
            minlength="8"
            required>

        <button
            type="button"
            class="toggle-password"
            aria-label="Mostrar contraseña">

            <i class="fa-solid fa-eye"></i>

        </button>

    </div>

    <small class="input-message"></small>

</div>
<!-- =========================
     CAPTCHA
========================= -->

<div class="captcha-container">

    <div
        class="g-recaptcha"
        data-sitekey="6LeEuNQsAAAAAHwONjHpoMqYXTPvfuXWBIOoIcLs">

    </div>

</div>

<!-- =========================
     BOTÓN
========================= -->

<button
    type="submit"
    class="register-btn">

    <i class="fa-solid fa-user-plus"></i>

    <span>

        Crear cuenta

    </span>

</button>

</form>

<!-- =========================
     FOOTER
========================= -->

<footer class="register-footer">

    <p>

        ¿Ya tienes una cuenta?

    </p>

    <a href="/bodytech/public/login.php">

        <i class="fa-solid fa-right-to-bracket"></i>

        Iniciar sesión

    </a>

</footer>

</section>

</main>

<!-- =========================
     JAVASCRIPT
========================= -->

<script src="/bodytech/public/assets/js/main.js"></script>

<script src="/bodytech/public/assets/js/components/forms.js"></script>

<script src="/bodytech/public/assets/js/components/toast.js"></script>

<script src="/bodytech/public/assets/js/register.js"></script>

</body>

</html>