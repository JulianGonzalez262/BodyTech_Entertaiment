<?php

session_start();

/*
|--------------------------------------------------------------------------
| CONFIGURACIÓN BÁSICA
|--------------------------------------------------------------------------
*/

$cssPanel = '/bodytech/public/assets/css/panel_afiliada.css';

/*
|--------------------------------------------------------------------------
| 🔐 SEGURIDAD
|--------------------------------------------------------------------------
| El usuario debe haber iniciado sesión.
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['user_id'])):
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Acceso afiliados | BODYTECH</title>

    <link
        rel="stylesheet"
        href="<?= htmlspecialchars($cssPanel) ?>"
    >

</head>

<body class="afiliado-page afiliado-login-page">

<?php require_once '../../app/views/header.php'; ?>

<main class="afiliado-login-main">

    <section class="afiliado-login-card">

        <div class="afiliado-login-icon">
            <i class="fa-solid fa-lock"></i>
        </div>

        <span class="afiliado-login-label">
            PANEL DE AFILIADOS
        </span>

        <h1>
            Acceso a afiliados
        </h1>

        <p>
            Debes iniciar sesión para consultar tu afiliación
            y acceder a tu panel de BODYTECH.
        </p>

        <a
            href="/bodytech/public/login.php"
            class="afiliado-primary-button"
        >
            <i class="fa-solid fa-right-to-bracket"></i>
            Iniciar sesión
        </a>

    </section>

</main>

<?php require_once '../../app/views/footer.php'; ?>

</body>
</html>

<?php
exit;
endif;


/*
|--------------------------------------------------------------------------
| 🗄️ CONEXIÓN A BASE DE DATOS
|--------------------------------------------------------------------------
*/

require_once '../../app/config/database.php';

$usuario_id = (int) $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| 👤 OBTENER AFILIACIÓN MÁS RECIENTE
|--------------------------------------------------------------------------
*/

$stmtAfiliado = $pdo->prepare("
    SELECT *
    FROM afiliados
    WHERE usuario_id = :usuario_id
    ORDER BY created_at DESC
    LIMIT 1
");

$stmtAfiliado->execute([
    'usuario_id' => $usuario_id
]);

$afiliado = $stmtAfiliado->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| ❌ VERIFICAR SI EXISTE AFILIACIÓN
|--------------------------------------------------------------------------
| Esta comprobación debe hacerse ANTES de utilizar datos de $afiliado.
|--------------------------------------------------------------------------
*/

if (!$afiliado):
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Sin afiliación | BODYTECH</title>

    <link
        rel="stylesheet"
        href="<?= htmlspecialchars($cssPanel) ?>"
    >

</head>

<body class="afiliado-page afiliado-empty-page">

<?php require_once '../../app/views/header.php'; ?>

<main class="afiliado-empty-main">

    <section class="afiliado-empty-card">

        <div class="afiliado-empty-icon">
            <i class="fa-solid fa-dumbbell"></i>
        </div>

        <span class="afiliado-empty-label">
            BODYTECH
        </span>

        <h1>
            No tienes una afiliación registrada
        </h1>

        <p>
            Actualmente no tienes una membresía activa en BODYTECH.
            Afíliate para comenzar a disfrutar de nuestros servicios.
        </p>

        <a
            href="/bodytech/public/register_afiliado.php"
            class="afiliado-primary-button"
        >
            <i class="fa-solid fa-plus"></i>
            Afiliarme ahora
        </a>

    </section>

</main>

<?php require_once '../../app/views/footer.php'; ?>

</body>
</html>

<?php
exit;
endif;


/*
|--------------------------------------------------------------------------
| 🏋️ OBTENER GIMNASIO AFILIADO
|--------------------------------------------------------------------------
*/

$stmtGimnasio = $pdo->prepare("
    SELECT
        id,
        nombre,
        ciudad
    FROM gimnasios
    WHERE nombre = :nombre
      AND ciudad = :ciudad
      AND activo = 1
    LIMIT 1
");

$stmtGimnasio->execute([
    'nombre' => $afiliado['gym_nombre'],
    'ciudad' => $afiliado['ciudad']
]);

$gimnasio = $stmtGimnasio->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| 🖼️ OBTENER IMÁGENES DEL GIMNASIO
|--------------------------------------------------------------------------
*/

$gymImagenes = [];

if ($gimnasio) {

    $stmtImagenes = $pdo->prepare("
        SELECT gm.media_path
        FROM gimnasio_media gm
        WHERE gm.gimnasio_id = :gimnasio_id
          AND gm.media_type = 'image'
        ORDER BY gm.created_at ASC
    ");

    $stmtImagenes->execute([
        'gimnasio_id' => $gimnasio['id']
    ]);

    $gymImagenes = $stmtImagenes->fetchAll(PDO::FETCH_COLUMN);
}


/*
|--------------------------------------------------------------------------
| 📅 ESTADO Y TIEMPO RESTANTE DE LA MEMBRESÍA
|--------------------------------------------------------------------------
*/

$diasRestantes = 999;

if (empty($afiliado['fecha_fin'])) {

    /*
    | Afiliación nueva sin fecha de finalización.
    | Se mantiene como activa.
    */
    $afiliado['estado'] = 'activo';

} else {

    $hoy = new DateTime();
    $fechaFin = new DateTime($afiliado['fecha_fin']);

    $diasRestantes = (int) $hoy->diff($fechaFin)->format('%r%a');

    /*
    | Si ya venció, actualizamos la afiliación.
    */
    if (
        $hoy > $fechaFin &&
        $afiliado['estado'] === 'activo'
    ) {

        $stmtVencimiento = $pdo->prepare("
            UPDATE afiliados
            SET estado = 'vencido'
            WHERE id = :id
        ");

        $stmtVencimiento->execute([
            'id' => $afiliado['id']
        ]);

        $afiliado['estado'] = 'vencido';
    }
}


/*
|--------------------------------------------------------------------------
| ⚠️ AFILIACIÓN NO ACTIVA
|--------------------------------------------------------------------------
*/

if ($afiliado['estado'] !== 'activo'):
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Membresía inactiva | BODYTECH</title>

    <link
        rel="stylesheet"
        href="<?= htmlspecialchars($cssPanel) ?>"
    >

</head>

<body class="afiliado-page afiliado-inactive-page">

<?php require_once '../../app/views/header.php'; ?>

<main class="afiliado-inactive-main">

    <section class="afiliado-inactive-card">

        <div class="afiliado-inactive-icon">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>

        <span class="afiliado-inactive-label">
            ESTADO DE MEMBRESÍA
        </span>

        <h1>
            Membresía inactiva
        </h1>

        <?php if ($afiliado['estado'] === 'vencido'): ?>

            <p>
                Tu membresía venció el
                <strong>
                    <?= htmlspecialchars(
                        date(
                            'd/m/Y',
                            strtotime($afiliado['fecha_fin'])
                        )
                    ) ?>
                </strong>.
            </p>

            <p>
                Para continuar disfrutando de BODYTECH debes renovarla.
            </p>

        <?php elseif ($afiliado['estado'] === 'pendiente_pago'): ?>

            <p>
                Tienes un pago pendiente para activar tu membresía.
            </p>

        <?php else: ?>

            <p>
                Tu afiliación actualmente no se encuentra activa.
            </p>

        <?php endif; ?>

        <a
            href="/bodytech/app/afiliados/volver_pago.php"
            class="afiliado-primary-button"
        >
            <i class="fa-solid fa-rotate"></i>
            Renovar ahora
        </a>

    </section>

</main>

<?php require_once '../../app/views/footer.php'; ?>

</body>
</html>

<?php
exit;
endif;


/*
|--------------------------------------------------------------------------
| 🎨 INFORMACIÓN VISUAL DEL ESTADO
|--------------------------------------------------------------------------
*/

$estadoColor = 'green';
$estadoMensaje = 'Tu membresía está activa';

if ($diasRestantes <= 3) {

    $estadoColor = 'red';
    $estadoMensaje = 'Últimos días de tu membresía';

} elseif ($diasRestantes <= 10) {

    $estadoColor = 'orange';
    $estadoMensaje = 'Tu membresía está por vencer';
}


/*
|--------------------------------------------------------------------------
| 🏷️ NOMBRE LEGIBLE DE LA MEMBRESÍA
|--------------------------------------------------------------------------
*/

$tipoMembresia = ucwords(
    str_replace(
        '_',
        ' ',
        $afiliado['tipo_membresia']
    )
);


/*
|--------------------------------------------------------------------------
| 💰 PRECIO FORMATEADO
|--------------------------------------------------------------------------
*/

$precioMembresia = number_format(
    (float) $afiliado['precio'],
    0,
    ',',
    '.'
);


/*
|--------------------------------------------------------------------------
| 🖼️ RUTA DEL FONDO
|--------------------------------------------------------------------------
*/

$dashboardBackground = '/bodytech/public/assets/img/Sino.png';

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Mi Panel de Afiliado | BODYTECH</title>

    <link
        rel="stylesheet"
        href="<?= htmlspecialchars($cssPanel) ?>"
    >

</head>

<body class="afiliado-page afiliado-dashboard-page">

<?php require_once '../../app/views/header.php'; ?>


<main class="afiliado-dashboard">

    <!-- ======================================================
         FONDO
    ======================================================= -->

    <div class="afiliado-dashboard-background">

        <img
            src="<?= htmlspecialchars($dashboardBackground) ?>"
            alt=""
            aria-hidden="true"
        >

    </div>

    <div class="afiliado-dashboard-overlay"></div>


    <!-- ======================================================
         CONTENIDO PRINCIPAL
    ======================================================= -->

    <section class="afiliado-dashboard-card">


        <!-- ==================================================
             ESTADO DE MEMBRESÍA
        =================================================== -->

        <?php if ($diasRestantes < 999): ?>

            <section class="afiliado-membership-status">

                <div class="afiliado-status-icon">

                    <i class="fa-solid fa-calendar-check"></i>

                </div>

                <div class="afiliado-status-content">

                    <span class="afiliado-status-label">
                        ESTADO DE LA MEMBRESÍA
                    </span>

                    <h3>
                        <?= htmlspecialchars($estadoMensaje) ?>
                    </h3>

                    <p>
                        Te quedan
                        <strong>
                            <?= $diasRestantes ?>
                        </strong>
                        día(s) de acceso.
                    </p>

                </div>

            </section>

        <?php endif; ?>


        <!-- ==================================================
             ENCABEZADO
        =================================================== -->

        <header class="afiliado-dashboard-header">

            <div class="afiliado-brand-icon">

                <i class="fa-solid fa-dumbbell"></i>

            </div>

            <span class="afiliado-brand-name">
                BODYTECH
            </span>

            <span class="afiliado-brand-subtitle">
                Marketplace Fitness
            </span>

            <h1>
                Mi Panel de Afiliado
            </h1>

            <p>
                Bienvenido nuevamente. Aquí podrás consultar tu
                membresía, revisar tus pagos y acceder a la
                información de tu afiliación.
            </p>

        </header>


        <!-- ==================================================
             RESUMEN PRINCIPAL
        =================================================== -->

        <section class="afiliado-summary-grid">


            <!-- ==============================================
                 GIMNASIO AFILIADO
            =============================================== -->

            <a
                href="<?= $gimnasio
                    ? '/bodytech/public/detalle_gimnasio.php?id=' . (int) $gimnasio['id']
                    : '#' ?>"
                class="afiliado-gym-card"
            >

                <div class="afiliado-card-heading">

                    <div class="afiliado-card-icon">

                        <i class="fa-solid fa-dumbbell"></i>

                    </div>

                    <div>

                        <span class="afiliado-card-label">
                            MI GIMNASIO
                        </span>

                        <h2>
                            Gimnasio afiliado
                        </h2>

                    </div>

                </div>


                <div class="afiliado-gym-layout">


                    <div class="afiliado-gym-information">

                        <h3>
                            <?= htmlspecialchars(
                                $afiliado['gym_nombre']
                            ) ?>
                        </h3>

                        <p>

                            <i class="fa-solid fa-location-dot"></i>

                            <span>
                                <?= htmlspecialchars(
                                    $afiliado['ciudad']
                                ) ?>

                                

                               
                            </span>

                        </p>

                        <?php if ($gimnasio): ?>

                            <span class="afiliado-gym-link">

                                Ver gimnasio

                                <i class="fa-solid fa-arrow-right"></i>

                            </span>

                        <?php endif; ?>

                    </div>


                    <?php if (!empty($gymImagenes)): ?>

                        <div
                            class="afiliado-gym-carousel"
                            aria-label="Imágenes del gimnasio afiliado"
                        >

                            <?php foreach (
                                $gymImagenes
                                as $index => $imagen
                            ): ?>

                                <img
                                    src="/bodytech/public/uploads/gimnasios/<?= htmlspecialchars($imagen) ?>"
                                    alt="<?= htmlspecialchars($afiliado['gym_nombre']) ?>"
                                    class="afiliado-gym-image <?= $index === 0 ? 'active' : '' ?>"
                                >

                            <?php endforeach; ?>

                            <?php if (count($gymImagenes) > 1): ?>

                                <div class="afiliado-carousel-indicator">
                                    <?= count($gymImagenes) ?> imágenes
                                </div>

                            <?php endif; ?>

                        </div>

                    <?php else: ?>

                        <div class="afiliado-gym-placeholder">

                            <i class="fa-solid fa-dumbbell"></i>

                            <span>
                                BODYTECH
                            </span>

                        </div>

                    <?php endif; ?>

                </div>

            </a>


            <!-- ==============================================
                 MEMBRESÍA
            =============================================== -->

            <article class="afiliado-membership-card">

                <div class="afiliado-card-heading">

                    <div class="afiliado-card-icon">

                        <i class="fa-solid fa-id-card"></i>

                    </div>

                    <div>

                        <span class="afiliado-card-label">
                            MI PLAN
                        </span>

                        <h2>
                            Mi membresía
                        </h2>

                    </div>

                </div>


                <div class="afiliado-membership-details">

                    <span class="afiliado-membership-type">
                        <?= htmlspecialchars($tipoMembresia) ?>
                    </span>

                    <div class="afiliado-membership-price">

                        <span>
                            $
                        </span>

                        <?= $precioMembresia ?>

                        <small>
                            COP
                        </small>

                    </div>

                </div>

            </article>

        </section>


        <!-- ==================================================
             ACCIONES RÁPIDAS
        =================================================== -->

        <section class="afiliado-actions-section">

            <div class="afiliado-section-heading">

                <span>
                    GESTIÓN
                </span>

                <h2>
                    Acciones rápidas
                </h2>

            </div>


            <div class="afiliado-actions-grid">


                <!-- RECIBO -->

                <a
                    href="/bodytech/public/afiliados/recibo.php"
                    class="afiliado-action-card"
                >

                    <div class="afiliado-action-icon">

                        <i class="fa-solid fa-file-invoice"></i>

                    </div>

                    <div class="afiliado-action-text">

                        <strong>
                            Imprimir recibo
                        </strong>

                        <span>
                            Consulta e imprime tu comprobante.
                        </span>

                    </div>

                    <i class="fa-solid fa-arrow-right afiliado-action-arrow"></i>

                </a>


                <!-- PAGOS -->

                <a
                    href="/bodytech/public/afiliados/pagos.php"
                    class="afiliado-action-card"
                >

                    <div class="afiliado-action-icon">

                        <i class="fa-solid fa-credit-card"></i>

                    </div>

                    <div class="afiliado-action-text">

                        <strong>
                            Ver mis pagos
                        </strong>

                        <span>
                            Consulta tu historial de pagos.
                        </span>

                    </div>

                    <i class="fa-solid fa-arrow-right afiliado-action-arrow"></i>

                </a>


                <!-- CANCELAR -->

                <?php if ($afiliado['estado'] === 'activo'): ?>

                    <form
                        action="/bodytech/public/afiliados/cancelar.php"
                        method="POST"
                        class="afiliado-cancel-form"
                        onsubmit="return confirm('¿Estás seguro de cancelar tu membresía? Esta acción no se puede deshacer.');"
                    >

                        <button
                            type="submit"
                            class="afiliado-danger-card"
                        >

                            <div class="afiliado-action-icon">

                                <i class="fa-solid fa-ban"></i>

                            </div>

                            <div class="afiliado-action-text">

                                <strong>
                                    Cancelar membresía
                                </strong>

                                <span>
                                    Cancelar tu afiliación actual.
                                </span>

                            </div>

                            <i class="fa-solid fa-arrow-right afiliado-action-arrow"></i>

                        </button>

                    </form>

                <?php endif; ?>


            </div>

        </section>


    </section>

</main>


<?php require_once '../../app/views/footer.php'; ?>

<script src="/bodytech/public/assets/js/panel_afiliado.js"></script>

</body>
</html> 