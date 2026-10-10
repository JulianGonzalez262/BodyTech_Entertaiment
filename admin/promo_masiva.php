<?php
require_once __DIR__ . '/admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/libs/PHPMailer-master/src/Exception.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/libs/PHPMailer-master/src/PHPMailer.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/libs/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $asunto = trim($_POST['asunto']);
    $contenido = trim($_POST['contenido']);

    $imagenTmp = $_FILES['imagen']['tmp_name'] ?? '';
    $imagenNombre = $_FILES['imagen']['name'] ?? '';

    $permitidas = ['jpg','jpeg','png','webp','gif'];
    $extension = strtolower(pathinfo($imagenNombre, PATHINFO_EXTENSION));

    $usarImagen = false;

    if (!empty($imagenTmp) && in_array($extension, $permitidas)) {
        $usarImagen = true;
    }

    if ($asunto && $contenido) {

        $stmt = $pdo->query("
            SELECT email, nombre 
            FROM usuarios 
            WHERE rol_id = 1 AND email_verificado = 1
        ");
        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $enviados = 0;

        foreach($clientes as $c){

            $mail = new PHPMailer(true);

            try{
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'mirtaleskilo@gmail.com';
                $mail->Password = 'lhcb vmrk ogbg vpww';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('mirtaleskilo@gmail.com', 'Bodytech Promociones');
                $mail->addAddress($c['email'], $c['nombre']);

                $mail->isHTML(true);

                if($usarImagen){
                    $mail->AddEmbeddedImage($imagenTmp, 'promoimg', $imagenNombre);
                }

                $mail->Subject = $asunto;

                $imgHtml = '';

                if($usarImagen){
                    $imgHtml = "<img src='cid:promoimg' style='width:100%;max-height:260px;object-fit:cover;border-radius:10px;'>";
                }

                $mail->Body = "
                <div style='max-width:700px;margin:auto;font-family:Arial;background:#f8fafc;border:1px solid #ddd;'>

                    <div style='background:#0f172a;padding:25px;text-align:center;color:white;'>
                        <h1 style='margin:0;'>🏋️ BODYTECH</h1>
                        <p style='margin:5px 0 0;'>Promociones Exclusivas para Nuestros Clientes</p>
                    </div>

                    <div style='padding:30px;text-align:center;'>

                        {$imgHtml}

                        <h2 style='color:#0f172a;'>Hola {$c['nombre']}</h2>

                        <p style='font-size:16px;color:#334155;line-height:1.6;'>
                            {$contenido}
                        </p>

                        <br>

                        <a href='http://localhost/bodytech/public/index.php'
                           style='background:#16a34a;color:white;padding:14px 22px;text-decoration:none;border-radius:8px;font-weight:bold;'>
                           🔥 Visitar Bodytech Ahora
                        </a>

                    </div>

                    <div style='background:#e2e8f0;padding:15px;text-align:center;font-size:12px;color:#475569;'>
                        Este mensaje promocional fue enviado automáticamente por Bodytech Marketplace & Fitness.
                    </div>

                </div>
                ";

                $mail->send();
                $enviados++;

            }catch(Exception $e){
                continue;
            }
        }

        $mensaje = "Promoción enviada correctamente a {$enviados} clientes.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Promoción Masiva | BODYTECH</title>

    <link rel="stylesheet"
          href="/bodytech/public/assets/css/admin_promo_masiva.css?v=1">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>

<main class="admin-promo-page">

    <!-- =====================================================
         HEADER
    ====================================================== -->

    <header class="admin-header">

        <div class="header-left">

            <div class="header-icon">

                <i class="fa-solid fa-envelope"></i>

            </div>

            <div>

                <span class="header-kicker">
                    BODYTECH ADMIN
                </span>

                <h1>
                    Promoción masiva
                </h1>

                <p>
                    Envía comunicaciones promocionales a los clientes verificados.
                </p>

            </div>

        </div>


        <a href="/bodytech/admin/clientes.php"
           class="back-button">

            <i class="fa-solid fa-arrow-left"></i>

            Volver a clientes

        </a>

    </header>


    <!-- =====================================================
         RESULTADO
    ====================================================== -->

    <?php if($mensaje): ?>

        <div class="success-message">

            <div class="success-icon">

                <i class="fa-solid fa-circle-check"></i>

            </div>

            <div>

                <strong>
                    Envío completado
                </strong>

                <span>
                    <?= htmlspecialchars($mensaje) ?>
                </span>

            </div>

        </div>

    <?php endif; ?>


    <!-- =====================================================
         CONTENT
    ====================================================== -->

    <section class="promo-layout">


        <!-- =================================================
             FORMULARIO
        ================================================== -->

        <div class="promo-card">

            <div class="card-heading">

                <div class="heading-icon">

                    <i class="fa-solid fa-paper-plane"></i>

                </div>

                <div>

                    <span>
                        NUEVA CAMPAÑA
                    </span>

                    <h2>
                        Crear promoción
                    </h2>

                </div>

            </div>


            <form method="POST"
                  enctype="multipart/form-data"
                  class="promo-form">


                <!-- ASUNTO -->

                <div class="form-group">

                    <label for="asunto">

                        <i class="fa-solid fa-heading"></i>

                        Asunto del correo

                    </label>

                    <div class="input-wrapper">

                        <i class="fa-regular fa-envelope input-icon"></i>

                        <input
                            type="text"
                            id="asunto"
                            name="asunto"
                            placeholder="Ej: Nueva promoción BODYTECH"
                            required
                            autocomplete="off"
                        >

                    </div>

                </div>


                <!-- CONTENIDO -->

                <div class="form-group">

                    <label for="contenido">

                        <i class="fa-solid fa-align-left"></i>

                        Contenido de la promoción

                    </label>

                    <div class="textarea-wrapper">

                        <textarea
                            id="contenido"
                            name="contenido"
                            rows="10"
                            placeholder="Escribe aquí el mensaje que recibirán tus clientes..."
                            required
                        ></textarea>

                    </div>

                    <span class="field-help">

                        <i class="fa-solid fa-circle-info"></i>

                        El contenido se enviará dentro del correo promocional.

                    </span>

                </div>


                <!-- IMAGEN -->

                <div class="form-group">

                    <label for="imagen">

                        <i class="fa-regular fa-image"></i>

                        Imagen promocional

                    </label>


                    <label class="upload-area"
                           for="imagen">

                        <div class="upload-icon">

                            <i class="fa-solid fa-cloud-arrow-up"></i>

                        </div>

                        <div class="upload-content">

                            <strong>
                                Seleccionar imagen
                            </strong>

                            <span id="file-name">
                                JPG, JPEG, PNG, WEBP o GIF
                            </span>

                        </div>

                        <div class="upload-button">

                            <i class="fa-solid fa-folder-open"></i>

                            Examinar

                        </div>

                    </label>


                    <input
                        type="file"
                        id="imagen"
                        name="imagen"
                        accept=".jpg,.jpeg,.png,.webp,.gif"
                        hidden
                    >

                </div>


                <!-- INFO -->

                <div class="recipient-info">

                    <div class="recipient-icon">

                        <i class="fa-solid fa-users"></i>

                    </div>

                    <div>

                        <strong>
                            Destinatarios
                        </strong>

                        <span>
                            La promoción se enviará a los clientes registrados que tengan su correo verificado.
                        </span>

                    </div>

                </div>


                <!-- ACTIONS -->

                <div class="form-actions">

                    <a href="/bodytech/admin/clientes.php"
                       class="cancel-button">

                        <i class="fa-solid fa-xmark"></i>

                        Cancelar

                    </a>


                    <button type="submit"
                            class="send-button">

                        <i class="fa-solid fa-paper-plane"></i>

                        Enviar promoción

                    </button>

                </div>

            </form>

        </div>


        <!-- =================================================
             SIDE INFO
        ================================================== -->

        <aside class="promo-sidebar">


            <div class="side-card">

                <div class="side-icon">

                    <i class="fa-solid fa-bullhorn"></i>

                </div>

                <h3>
                    Comunicación directa
                </h3>

                <p>
                    Utiliza este espacio para informar a tus clientes sobre promociones, novedades y campañas especiales.
                </p>

            </div>


            <div class="side-card">

                <div class="side-icon green">

                    <i class="fa-solid fa-shield-check"></i>

                </div>

                <h3>
                    Clientes verificados
                </h3>

                <p>
                    Solo los clientes con correo electrónico verificado reciben la promoción.
                </p>

            </div>


            <div class="side-card">

                <div class="side-icon blue">

                    <i class="fa-solid fa-image"></i>

                </div>

                <h3>
                    Imagen opcional
                </h3>

                <p>
                    Puedes adjuntar una imagen para hacer la promoción más atractiva.
                </p>

            </div>


        </aside>

    </section>


    <!-- =====================================================
         FOOTER
    ====================================================== -->

    <footer class="admin-footer">

        <a href="/bodytech/admin/clientes.php">

            <i class="fa-solid fa-arrow-left"></i>

            Regresar a gestión de clientes

        </a>

    </footer>

</main>


<script src="/bodytech/public/assets/js/promo_masiva.js"></script>

</body>
</html>