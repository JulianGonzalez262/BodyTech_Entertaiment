<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: /bodytech/public/login.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT *
    FROM usuarios
    WHERE id = ?
");

$stmt->execute([$_SESSION['usuario_id']]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    session_destroy();
    header("Location: /bodytech/public/login.php");
    exit;
}

$fotoPerfil = !empty($usuario['foto_perfil'])
    ? $usuario['foto_perfil']
    : '/bodytech/public/assets/img/default-avatar.png';

if ($fotoPerfil && !str_starts_with($fotoPerfil, '/')) {
    $fotoPerfil = '/bodytech/public/' . ltrim($fotoPerfil, '/');
}
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Mi Cuenta | BodyTech</title>

    <link
        rel="stylesheet"
        href="/bodytech/public/assets/css/mi_cuenta.css?v=<?= time() ?>"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >

</head>

<body>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/views/header.php'; ?>

<main class="account-container">

    <!-- =========================
         SIDEBAR
    ========================== -->

    <aside class="account-sidebar">

        <div class="profile-preview">

            <div class="profile-avatar-wrapper">

                <img
                    src="<?= htmlspecialchars($fotoPerfil) ?>"
                    alt="Foto de perfil"
                    class="profile-avatar"
                >

            </div>

            <h2>
                <?= htmlspecialchars(
                    trim(
                        ($usuario['nombres'] ?? '') .
                        ' ' .
                        ($usuario['apellidos'] ?? '')
                    )
                ) ?>
            </h2>

            <p>
                <?= htmlspecialchars($usuario['email']) ?>
            </p>

        </div>

        <nav class="account-menu">

            <a href="#perfil" class="menu-item">

                <span class="menu-icon">
                    <i class="fa-solid fa-user"></i>
                </span>

                <span>Mi perfil</span>

            </a>

            <a href="#membresias" class="menu-item">

                <span class="menu-icon">
                    <i class="fa-solid fa-dumbbell"></i>
                </span>

                <span>Mi membresía</span>

            </a>

            <a href="#compras" class="menu-item">

                <span class="menu-icon">
                    <i class="fa-solid fa-cart-shopping"></i>
                </span>

                <span>Mis compras</span>

            </a>

            <a href="#reportes" class="menu-item">

                <span class="menu-icon">
                    <i class="fa-solid fa-file-lines"></i>
                </span>

                <span>Centro de Reportes</span>

            </a>

            <a href="#privacidad" class="menu-item">

                <span class="menu-icon">
                    <i class="fa-solid fa-shield-halved"></i>
                </span>

                <span>Privacidad</span>

            </a>

        </nav>

    </aside>


    <!-- =========================
         CONTENIDO
    ========================== -->

    <section class="account-content">


        <!-- PERFIL -->

        <section
            id="perfil"
            class="account-section"
        >

            <div class="section-heading">

                <div class="section-icon">
                    <i class="fa-solid fa-user"></i>
                </div>

                <div>
                    <h1>Mi perfil</h1>

                    <p>
                        Información personal de tu cuenta BodyTech.
                    </p>
                </div>

            </div>


            <div class="profile-info">

                <div class="profile-item">

                    <span>Nombres</span>

                    <strong>
                        <?= htmlspecialchars(
                            $usuario['nombres'] ?: 'No registrado'
                        ) ?>
                    </strong>

                </div>

                <div class="profile-item">

                    <span>Apellidos</span>

                    <strong>
                        <?= htmlspecialchars(
                            $usuario['apellidos'] ?: 'No registrado'
                        ) ?>
                    </strong>

                </div>

                <div class="profile-item">

                    <span>Correo electrónico</span>

                    <strong>
                        <?= htmlspecialchars($usuario['email']) ?>
                    </strong>

                </div>

                <div class="profile-item">

                    <span>Teléfono principal</span>

                    <strong>
                        <?= htmlspecialchars(
                            $usuario['telefono_principal'] ?: 'No registrado'
                        ) ?>
                    </strong>

                </div>

                <div class="profile-item">

                    <span>Teléfono secundario</span>

                    <strong>
                        <?= htmlspecialchars(
                            $usuario['telefono_secundario'] ?: 'No registrado'
                        ) ?>
                    </strong>

                </div>

                <div class="profile-item">

                    <span>Dirección</span>

                    <strong>
                        <?= htmlspecialchars(
                            $usuario['direccion'] ?: 'No registrada'
                        ) ?>
                    </strong>

                </div>

                <div class="profile-item">

                    <span>Tipo de documento</span>

                    <strong>
                        <?= htmlspecialchars(
                            $usuario['tipo_documento'] ?: 'No registrado'
                        ) ?>
                    </strong>

                </div>

                <div class="profile-item">

                    <span>Número de documento</span>

                    <strong>
                        <?= htmlspecialchars(
                            $usuario['numero_documento'] ?: 'No registrado'
                        ) ?>
                    </strong>

                </div>

                <div class="profile-item">

                    <span>Correo verificado</span>

                    <strong class="<?= $usuario['email_verificado'] ? 'status-success' : 'status-warning' ?>">

                        <?=
                            $usuario['email_verificado']
                                ? '✓ Verificado'
                                : 'Pendiente'
                        ?>

                    </strong>

                </div>

                <div class="profile-item">

                    <span>Documento verificado</span>

                    <strong class="<?= $usuario['documento_verificado'] ? 'status-success' : 'status-warning' ?>">

                        <?=
                            $usuario['documento_verificado']
                                ? '✓ Verificado'
                                : 'Pendiente'
                        ?>

                    </strong>

                </div>

            </div>


            <a
                href="editar_perfil.php"
                class="account-button primary-button"
            >

                <i class="fa-solid fa-pen"></i>

                Editar información personal

            </a>

        </section>


        <!-- MEMBRESÍAS -->

        <section
            id="membresias"
            class="account-section"
        >

            <div class="section-heading">

                <div class="section-icon">
                    <i class="fa-solid fa-dumbbell"></i>
                </div>

                <div>

                    <h1>Mi membresía</h1>

                    <p>
                        Consulta y administra la información de tus afiliaciones.
                    </p>

                </div>

            </div>


            <div class="action-card">

                <div class="action-card-icon">
                    <i class="fa-solid fa-id-card"></i>
                </div>

                <div class="action-card-content">

                    <h3>
                        Panel de membresías
                    </h3>

                    <p>
                        Accede al sistema de afiliaciones de BodyTech.
                    </p>

                </div>

                <a
                    href="/bodytech/public/afiliados/index.php"
                    class="account-button"
                >

                    Abrir panel

                    <i class="fa-solid fa-arrow-right"></i>

                </a>

            </div>

        </section>


        <!-- COMPRAS -->

        <section
            id="compras"
            class="account-section"
        >

            <div class="section-heading">

                <div class="section-icon">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>

                <div>

                    <h1>Mis compras</h1>

                    <p>
                        Consulta el historial de compras realizadas en BodyTech.
                    </p>

                </div>

            </div>


            <div class="action-card">

                <div class="action-card-icon">
                    <i class="fa-solid fa-receipt"></i>
                </div>

                <div class="action-card-content">

                    <h3>
                        Historial de compras
                    </h3>

                    <p>
                        Consulta y genera el historial de tus compras.
                    </p>

                </div>

                <a
                    href="/bodytech/public/usuario/reportes/generar_compras.php"
                    class="account-button"
                >

                    Ver historial

                    <i class="fa-solid fa-arrow-right"></i>

                </a>

            </div>

        </section>


        <!-- REPORTES -->

        <section
            id="reportes"
            class="account-section"
        >

            <div class="section-heading">

                <div class="section-icon">
                    <i class="fa-solid fa-file-lines"></i>
                </div>

                <div>

                    <h1>Centro de Reportes</h1>

                    <p>
                        Descarga documentos oficiales de tu cuenta BodyTech.
                    </p>

                </div>

            </div>


            <div class="reports-grid">

                <a
                    href="/bodytech/public/usuario/reportes/generar_personal.php"
                    class="report-card"
                >

                    <span class="report-icon">
                        <i class="fa-solid fa-user"></i>
                    </span>

                    <span class="report-content">

                        <strong>
                            Información personal
                        </strong>

                        <small>
                            Generar reporte personal
                        </small>

                    </span>

                    <i class="fa-solid fa-download report-arrow"></i>

                </a>


                <a
                    href="/bodytech/public/usuario/reportes/generar_pedidos.php"
                    class="report-card"
                >

                    <span class="report-icon">
                        <i class="fa-solid fa-box"></i>
                    </span>

                    <span class="report-content">

                        <strong>
                            Historial de pedidos
                        </strong>

                        <small>
                            Descargar historial
                        </small>

                    </span>

                    <i class="fa-solid fa-download report-arrow"></i>

                </a>


                <a
                    href="/bodytech/public/usuario/reportes/generar_pqrs.php"
                    class="report-card"
                >

                    <span class="report-icon">
                        <i class="fa-solid fa-headset"></i>
                    </span>

                    <span class="report-content">

                        <strong>
                            Historial PQRS
                        </strong>

                        <small>
                            Descargar historial
                        </small>

                    </span>

                    <i class="fa-solid fa-download report-arrow"></i>

                </a>


                <a
                    href="/bodytech/public/usuario/reportes/generar_completo.php"
                    class="report-card report-card-highlight"
                >

                    <span class="report-icon">
                        <i class="fa-solid fa-file-export"></i>
                    </span>

                    <span class="report-content">

                        <strong>
                            Reporte completo
                        </strong>

                        <small>
                            Descargar toda la información
                        </small>

                    </span>

                    <i class="fa-solid fa-download report-arrow"></i>

                </a>

            </div>

        </section>


        <!-- PRIVACIDAD -->

        <section
            id="privacidad"
            class="account-section"
        >

            <div class="section-heading">

                <div class="section-icon">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>

                <div>

                    <h1>Privacidad</h1>

                    <p>
                        Administra la seguridad y las preferencias de tu cuenta.
                    </p>

                </div>

            </div>


            <div class="privacy-grid">

                <a
                    href="/bodytech/public/usuario/notificaciones.php"
                    class="privacy-card"
                >

                    <span class="privacy-icon">
                        <i class="fa-solid fa-bell"></i>
                    </span>

                    <span>

                        <strong>
                            Gestionar notificaciones
                        </strong>

                        <small>
                            Configura las notificaciones de tu cuenta.
                        </small>

                    </span>

                    <i class="fa-solid fa-chevron-right"></i>

                </a>


                <a
                    href="/bodytech/public/usuario/seguridad.php"
                    class="privacy-card"
                >

                    <span class="privacy-icon">
                        <i class="fa-solid fa-lock"></i>
                    </span>

                    <span>

                        <strong>
                            Seguridad de la cuenta
                        </strong>

                        <small>
                            Gestiona las opciones de seguridad.
                        </small>

                    </span>

                    <i class="fa-solid fa-chevron-right"></i>

                </a>

            </div>

        </section>

    </section>

</main>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/views/footer.php'; ?>

</body>
</html>