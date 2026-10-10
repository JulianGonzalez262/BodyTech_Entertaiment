<?php
session_start();

// Seguridad básica
if (!isset($_SESSION['usuario_id'])) {
    die('Debes iniciar sesión.');
}

require_once '../../app/config/database.php';
require_once '../../app/libs/phpqrcode/qrlib.php';

$usuario_id = $_SESSION['usuario_id'];

// Buscar afiliación más reciente
$stmt = $pdo->prepare("
    SELECT *
    FROM afiliados
    WHERE usuario_id = :usuario_id
    ORDER BY created_at DESC
    LIMIT 1
");
$stmt->execute(['usuario_id' => $usuario_id]);
$afiliado = $stmt->fetch();

if (!$afiliado) {
    die('No se encontró afiliación registrada.');
}

// 🔒 Validación automática de vencimiento
if (!empty($afiliado['fecha_fin'])) {
    $hoy = new DateTime();
    $fechaFin = new DateTime($afiliado['fecha_fin']);

    if ($hoy > $fechaFin && $afiliado['estado'] === 'activo') {
        $stmt = $pdo->prepare("
            UPDATE afiliados
            SET estado = 'vencido'
            WHERE id = :id
        ");
        $stmt->execute(['id' => $afiliado['id']]);
        $afiliado['estado'] = 'vencido';
    }
}

// ✅ Verificar pago aprobado
$stmt = $pdo->prepare("
    SELECT *
    FROM pagos
    WHERE afiliado_id = :afiliado_id
      AND estado = 'aprobado'
    ORDER BY created_at DESC
    LIMIT 1
");
$stmt->execute(['afiliado_id' => $afiliado['id']]);
$pago = $stmt->fetch();

$puede_imprimir = ($afiliado['estado'] === 'activo' && $pago);

// 🔐 Validar token
if (empty($afiliado['qr_token'])) {
    die('Error de seguridad: token no generado.');
}

/* ======================================================
   📁 RUTA DE QRs (LOCAL)
====================================================== */
$qrDir = $_SERVER['DOCUMENT_ROOT'] . '/bodytech/public/qrs/';
if (!is_dir($qrDir)) {
    mkdir($qrDir, 0755, true);
}

/* ======================================================
   QR 1️⃣ VALIDACIÓN (PC)
====================================================== */
$baseUrl = "http://localhost/bodytech/public/afiliados/validar_qr.php";
$qr_validacion_data = $baseUrl . "?token=" . $afiliado['qr_token'];
$qr_validacion_file = $qrDir . "qr_validacion_{$afiliado['id']}.png";

QRcode::png($qr_validacion_data, $qr_validacion_file, QR_ECLEVEL_H, 6);

/* ======================================================
   QR 2️⃣ INFORMATIVO (CELULAR)
====================================================== */
$qr_info_data =
    "AFILIADO ID: {$afiliado['id']} | " .
    "GYM: {$afiliado['gym_nombre']} | " .
    "CIUDAD: {$afiliado['ciudad']} | " .
    "MEMBRESÍA: {$afiliado['tipo_membresia']} | " .
    "ESTADO: {$afiliado['estado']} | " .
    "ÚLTIMO PAGO: " . date('d/m/Y', strtotime($pago['created_at']));

$qr_info_file = $qrDir . "qr_info_{$afiliado['id']}.png";

QRcode::png($qr_info_data, $qr_info_file, QR_ECLEVEL_M, 5);

// URLs públicas
$qr_pc = "/bodytech/public/qrs/qr_validacion_{$afiliado['id']}.png";
$qr_movil = "/bodytech/public/qrs/qr_info_{$afiliado['id']}.png";
?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Recibo de Afiliación | BODYTECH</title>

    <link
        rel="stylesheet"
        href="/bodytech/public/assets/css/recibo.css?v=1"
    >

</head>

<body>

<?php require_once '../../app/views/header.php'; ?>


<main class="receipt-page">


    <!-- ==========================================================
         FONDO
    =========================================================== -->

    <div class="receipt-background">

        <img
            src="/bodytech/public/assets/img/Sino.png"
            alt="Background"
        >

    </div>

    <div class="receipt-overlay"></div>


    <!-- ==========================================================
         CONTENEDOR DEL RECIBO
    =========================================================== -->

    <section class="receipt-card">


        <!-- ======================================================
             ENCABEZADO
        ======================================================= -->

        <header class="receipt-header">

            <div class="receipt-logo">

                <i class="fa-solid fa-dumbbell"></i>

            </div>

            <div class="receipt-brand">

                <h1>
                    BODYTECH
                </h1>

                <span>
                    Marketplace Fitness
                </span>

            </div>

            <div class="receipt-title">

                <h2>
                    Recibo de Afiliación
                </h2>

                <p>
                    Comprobante oficial de tu afiliación
                </p>

            </div>

        </header>


        <!-- ======================================================
             IDENTIFICACIÓN DEL RECIBO
        ======================================================= -->

        <div class="receipt-meta">

            <div class="receipt-meta-item">

                <span>
                    Recibo #
                </span>

                <strong>
                    <?= $afiliado['id']; ?>
                </strong>

            </div>


            <div class="receipt-meta-divider"></div>


            <div class="receipt-meta-item">

                <span>
                    Fecha de emisión
                </span>

                <strong>
                    <?= date(
                        'd/m/Y',
                        strtotime($afiliado['created_at'])
                    ); ?>
                </strong>

            </div>

        </div>


        <!-- ======================================================
             INFORMACIÓN PRINCIPAL
        ======================================================= -->

        <div class="receipt-information">


            <!-- ==================================================
                 AFILIADO
            =================================================== -->

            <section class="receipt-section">

                <div class="receipt-section-heading">

                    <div class="receipt-section-icon">

                        <i class="fa-solid fa-user"></i>

                    </div>

                    <div>

                        <span>
                            Información
                        </span>

                        <h3>
                            Afiliado
                        </h3>

                    </div>

                </div>


                <div class="receipt-details">

                    <div class="receipt-detail">

                        <span>
                            Documento
                        </span>

                        <strong>
                            <?= htmlspecialchars($afiliado['tipo_id']); ?>

                            <?= htmlspecialchars($afiliado['numero_id']); ?>
                        </strong>

                    </div>


                    <div class="receipt-detail">

                        <span>
                            Teléfono
                        </span>

                        <strong>
                            <?= htmlspecialchars($afiliado['telefono']); ?>
                        </strong>

                    </div>

                </div>

            </section>



            <!-- ==================================================
                 GIMNASIO
            =================================================== -->

            <section class="receipt-section">

                <div class="receipt-section-heading">

                    <div class="receipt-section-icon">

                        <i class="fa-solid fa-dumbbell"></i>

                    </div>

                    <div>

                        <span>
                            Centro deportivo
                        </span>

                        <h3>
                            Gimnasio afiliado
                        </h3>

                    </div>

                </div>


                <div class="receipt-gym">

                    <strong>

                        <?= htmlspecialchars(
                            $afiliado['gym_nombre']
                        ); ?>

                    </strong>

                    <span>

                        <i class="fa-solid fa-location-dot"></i>

                        <?= htmlspecialchars(
                            $afiliado['ciudad']
                        ); ?>

                        ·

                        <?= htmlspecialchars(
                            $afiliado['departamento']
                        ); ?>

                    </span>

                </div>

            </section>



            <!-- ==================================================
                 MEMBRESÍA
            =================================================== -->

            <section class="receipt-section">

                <div class="receipt-section-heading">

                    <div class="receipt-section-icon">

                        <i class="fa-solid fa-id-card"></i>

                    </div>

                    <div>

                        <span>
                            Plan contratado
                        </span>

                        <h3>
                            Membresía
                        </h3>

                    </div>

                </div>


                <div class="receipt-membership">

                    <div class="receipt-membership-type">

                        <span>
                            Tipo de membresía
                        </span>

                        <strong>

                            <?= htmlspecialchars(
                                ucfirst(
                                    $afiliado['tipo_membresia']
                                )
                            ); ?>

                        </strong>

                    </div>


                    <div class="receipt-price">

                        <span>
                            Valor pagado
                        </span>

                        <strong>

                            $<?= number_format(
                                $afiliado['precio'],
                                0,
                                ',',
                                '.'
                            ); ?>

                            <small>
                                COP
                            </small>

                        </strong>

                    </div>

                </div>

            </section>


        </div>


        <!-- ======================================================
             ESTADO
        ======================================================= -->

        <div class="receipt-status-section">

            <span class="receipt-status-label">
                Estado de la afiliación
            </span>

            <div
                class="
                    receipt-status
                    status-<?= strtolower(
                        htmlspecialchars(
                            $afiliado['estado']
                        )
                    ); ?>
                "
            >

                <span class="receipt-status-dot"></span>

                <strong>
                    <?= strtoupper(
                        htmlspecialchars(
                            $afiliado['estado']
                        )
                    ); ?>
                </strong>

            </div>

        </div>



        <!-- ======================================================
             QR
        ======================================================= -->

        <?php if ($puede_imprimir): ?>

            <section class="receipt-qr-section">


                <div class="receipt-qr-header">

                    <div class="receipt-section-icon">

                        <i class="fa-solid fa-qrcode"></i>

                    </div>

                    <div>

                        <span>
                            Verificación digital
                        </span>

                        <h3>
                            Códigos QR
                        </h3>

                    </div>

                </div>


                <div class="receipt-qr-grid">


                    <!-- ==========================================
                         QR VALIDACIÓN
                    =========================================== -->

                    <div class="receipt-qr-card">

                        <div class="receipt-qr-card-icon">

                            <i class="fa-solid fa-shield-halved"></i>

                        </div>

                        <h4>
                            QR de Validación
                        </h4>

                        <p>
                            Escanea este código para verificar
                            oficialmente tu afiliación.
                        </p>

                        <div class="receipt-qr-image">

                            <img
                                src="<?= $qr_pc ?>"
                                alt="QR de validación"
                            >

                        </div>

                        <span class="receipt-qr-label">

                            Validación oficial

                        </span>

                    </div>



                    <!-- ==========================================
                         QR INFORMATIVO
                    =========================================== -->

                    <div class="receipt-qr-card">

                        <div class="receipt-qr-card-icon">

                            <i class="fa-solid fa-mobile-screen-button"></i>

                        </div>

                        <h4>
                            QR Informativo
                        </h4>

                        <p>
                            Consulta rápidamente la información
                            de tu afiliación desde tu celular.
                        </p>

                        <div class="receipt-qr-image">

                            <img
                                src="<?= $qr_movil ?>"
                                alt="QR informativo"
                            >

                        </div>

                        <span class="receipt-qr-label">

                            Información de afiliación

                        </span>

                    </div>


                </div>

            </section>


        <?php else: ?>


            <!-- ==================================================
                 RECIBO NO DISPONIBLE
            =================================================== -->

            <div class="receipt-unavailable">

                <div class="receipt-unavailable-icon">

                    <i class="fa-solid fa-triangle-exclamation"></i>

                </div>

                <div>

                    <h3>
                        Recibo no disponible
                    </h3>

                    <p>
                        Este recibo no está disponible porque
                        no existe un pago aprobado o la afiliación
                        no se encuentra activa.
                    </p>

                </div>

            </div>


        <?php endif; ?>



        <!-- ======================================================
             ACCIONES
        ======================================================= -->

        <div class="receipt-actions">

            <?php if ($puede_imprimir): ?>

                <button
                    type="button"
                    class="receipt-btn receipt-print-btn"
                    onclick="window.print()"
                >

                    <i class="fa-solid fa-print"></i>

                    <span>
                        Imprimir recibo
                    </span>

                </button>

            <?php endif; ?>


            <a
                href="recibo_pdf.php"
                target="_blank"
                class="receipt-btn receipt-pdf-btn"
            >

                <i class="fa-solid fa-file-pdf"></i>

                <span>
                    Descargar PDF
                </span>

            </a>

        </div>



        <!-- ======================================================
             PIE DEL RECIBO
        ======================================================= -->

        <footer class="receipt-footer">

            <div class="receipt-footer-line"></div>

            <div class="receipt-footer-content">

                <i class="fa-solid fa-lock"></i>

                <span>
                    Comprobante generado por BODYTECH
                </span>

            </div>

            <p>
                Conserva este recibo como comprobante de tu afiliación.
            </p>

        </footer>


    </section>

</main>


<?php require_once '../../app/views/footer.php'; ?>


</body>
</html>