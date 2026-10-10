<?php
require_once __DIR__ . '/../admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    die('PQRS inválida.');
}

/* =========================
   OBTENER PQRS
========================= */

$stmt = $pdo->prepare("
    SELECT
        p.*,
        u.nombre,
        u.email
    FROM pqrs p
    INNER JOIN usuarios u
        ON p.usuario_id = u.id
    WHERE p.id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$pqrs = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pqrs) {
    die('PQRS no encontrada.');
}

/* =========================
   RESPUESTAS
========================= */

$stmt = $pdo->prepare("
    SELECT
        pr.*,
        u.nombre AS admin_nombre
    FROM pqrs_respuestas pr
    INNER JOIN usuarios u
        ON pr.admin_id = u.id
    WHERE pr.pqrs_id = ?
    ORDER BY pr.created_at ASC
");

$stmt->execute([$id]);

$respuestas = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Ver PQRS #<?= $pqrs['id'] ?></title>

    <link rel="stylesheet"
          href="/bodytech/public/assets/css/admin_ver_pqrs.css?v=1">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>

<main class="admin-pqrs-detail-page">


    <!-- ======================================================
         HEADER
    ======================================================= -->

    <header class="page-header">

        <div class="header-content">

            <div class="header-title">

                <div class="header-icon">
                    <i class="fa-solid fa-envelope-open-text"></i>
                </div>

                <div>

                    <h1>PQRS #<?= $pqrs['id'] ?></h1>

                    <p>
                        Consulta, gestiona y responde esta solicitud.
                    </p>

                </div>

            </div>


            <a href="index.php"
               class="back-button">

                <i class="fa-solid fa-arrow-left"></i>

                Volver a PQRS

            </a>

        </div>

    </header>


    <!-- ======================================================
         CONTENIDO
    ======================================================= -->

    <section class="content-wrapper">


        <!-- ==================================================
             INFORMACIÓN DE LA PQRS
        =================================================== -->

        <div class="detail-card">

            <div class="card-heading">

                <div class="heading-icon">
                    <i class="fa-solid fa-file-lines"></i>
                </div>

                <div>

                    <h2>Información de la solicitud</h2>

                    <p>
                        Datos enviados por el cliente.
                    </p>

                </div>

            </div>


            <div class="customer-header">

                <div class="customer-avatar">
                    <i class="fa-solid fa-user"></i>
                </div>

                <div>

                    <strong>
                        <?= htmlspecialchars($pqrs['nombre']) ?>
                    </strong>

                    <span>
                        <?= htmlspecialchars($pqrs['email']) ?>
                    </span>

                </div>

            </div>


            <div class="info-grid">


                <!-- TIPO -->

                <div class="info-item">

                    <span class="info-label">
                        <i class="fa-solid fa-tag"></i>
                        Tipo
                    </span>

                    <strong>
                        <?= ucfirst($pqrs['tipo']) ?>
                    </strong>

                </div>


                <!-- ESTADO -->

                <div class="info-item">

                    <span class="info-label">
                        <i class="fa-solid fa-circle-half-stroke"></i>
                        Estado
                    </span>

                    <?php

                    $estadoClass = $pqrs['estado'];

                    $estadoIcon = 'fa-circle-question';

                    $estadoTexto = ucfirst(
                        str_replace('_', ' ', $pqrs['estado'])
                    );

                    if($pqrs['estado'] === 'pendiente') {
                        $estadoIcon = 'fa-clock';
                    }

                    if($pqrs['estado'] === 'en_proceso') {
                        $estadoIcon = 'fa-spinner';
                    }

                    if($pqrs['estado'] === 'respondida') {
                        $estadoIcon = 'fa-circle-check';
                    }

                    if($pqrs['estado'] === 'cerrada') {
                        $estadoIcon = 'fa-circle-xmark';
                    }

                    ?>

                    <span class="status-badge <?= $estadoClass ?>">

                        <i class="fa-solid <?= $estadoIcon ?>"></i>

                        <?= $estadoTexto ?>

                    </span>

                </div>


                <!-- FECHA -->

                <div class="info-item">

                    <span class="info-label">
                        <i class="fa-regular fa-calendar"></i>
                        Fecha de creación
                    </span>

                    <strong>
                        <?= $pqrs['created_at'] ?>
                    </strong>

                </div>


                <!-- ID -->

                <div class="info-item">

                    <span class="info-label">
                        <i class="fa-solid fa-hashtag"></i>
                        Identificador
                    </span>

                    <strong>
                        #<?= $pqrs['id'] ?>
                    </strong>

                </div>

            </div>


            <!-- ASUNTO -->

            <div class="subject-block">

                <span class="info-label">
                    <i class="fa-regular fa-message"></i>
                    Asunto
                </span>

                <h3>
                    <?= htmlspecialchars($pqrs['asunto']) ?>
                </h3>

            </div>


            <!-- DESCRIPCIÓN -->

            <div class="description-block">

                <span class="info-label">
                    <i class="fa-solid fa-align-left"></i>
                    Descripción
                </span>

                <div class="description-content">

                    <?= nl2br(
                        htmlspecialchars($pqrs['descripcion'])
                    ) ?>

                </div>

            </div>

        </div>


        <!-- ==================================================
             CAMBIAR ESTADO
        =================================================== -->

        <div class="action-card">

            <div class="card-heading">

                <div class="heading-icon">
                    <i class="fa-solid fa-sliders"></i>
                </div>

                <div>

                    <h2>Cambiar estado</h2>

                    <p>
                        Actualiza el estado actual de la solicitud.
                    </p>

                </div>

            </div>


            <form
                action="cambiar_estado.php"
                method="POST"
                class="status-form"
            >

                <input
                    type="hidden"
                    name="pqrs_id"
                    value="<?= $pqrs['id'] ?>"
                >


                <div class="field">

                    <label for="estado">
                        Nuevo estado
                    </label>

                    <div class="select-wrapper">

                        <i class="fa-solid fa-list-check"></i>

                        <select
                            id="estado"
                            name="estado"
                        >

                            <option
                                value="pendiente"
                                <?= $pqrs['estado'] === 'pendiente' ? 'selected' : '' ?>
                            >
                                Pendiente
                            </option>

                            <option
                                value="en_proceso"
                                <?= $pqrs['estado'] === 'en_proceso' ? 'selected' : '' ?>
                            >
                                En proceso
                            </option>

                            <option
                                value="respondida"
                                <?= $pqrs['estado'] === 'respondida' ? 'selected' : '' ?>
                            >
                                Respondida
                            </option>

                            <option
                                value="cerrada"
                                <?= $pqrs['estado'] === 'cerrada' ? 'selected' : '' ?>
                            >
                                Cerrada
                            </option>

                        </select>

                    </div>

                </div>


                <button
                    type="submit"
                    class="primary-button"
                >

                    <i class="fa-solid fa-check"></i>

                    Actualizar estado

                </button>

            </form>

        </div>


        <!-- ==================================================
             RESPONDER
        =================================================== -->

        <div class="action-card">

            <div class="card-heading">

                <div class="heading-icon">
                    <i class="fa-solid fa-reply"></i>
                </div>

                <div>

                    <h2>Responder al cliente</h2>

                    <p>
                        Escribe una respuesta para esta solicitud.
                    </p>

                </div>

            </div>


            <form
                action="responder.php"
                method="POST"
                class="reply-form"
            >

                <input
                    type="hidden"
                    name="pqrs_id"
                    value="<?= $pqrs['id'] ?>"
                >


                <div class="field">

                    <label for="mensaje">
                        Mensaje
                    </label>

                    <div class="textarea-wrapper">

                        <i class="fa-regular fa-comment-dots"></i>

                        <textarea
                            id="mensaje"
                            name="mensaje"
                            rows="7"
                            required
                            placeholder="Escribe aquí la respuesta para el cliente..."
                        ></textarea>

                    </div>

                    <span class="field-help">
                        La respuesta quedará registrada en el historial de esta PQRS.
                    </span>

                </div>


                <div class="form-actions">

                    <button
                        type="submit"
                        class="primary-button"
                    >

                        <i class="fa-solid fa-paper-plane"></i>

                        Enviar respuesta

                    </button>

                </div>

            </form>

        </div>


        <!-- ==================================================
             HISTORIAL
        =================================================== -->

        <div class="history-card">

            <div class="card-heading">

                <div class="heading-icon">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>

                <div>

                    <h2>Historial de respuestas</h2>

                    <p>
                        Respuestas registradas para esta solicitud.
                    </p>

                </div>

            </div>


            <?php if(empty($respuestas)): ?>

                <div class="empty-history">

                    <div class="empty-history-icon">
                        <i class="fa-regular fa-comments"></i>
                    </div>

                    <h3>
                        No hay respuestas todavía
                    </h3>

                    <p>
                        Esta PQRS aún no cuenta con respuestas registradas.
                    </p>

                </div>

            <?php else: ?>

                <div class="response-list">

                    <?php foreach($respuestas as $r): ?>

                        <article class="response-item">

                            <div class="response-header">

                                <div class="response-user">

                                    <div class="response-avatar">

                                        <i class="fa-solid fa-user-shield"></i>

                                    </div>

                                    <div>

                                        <strong>
                                            <?= htmlspecialchars($r['admin_nombre']) ?>
                                        </strong>

                                        <span>
                                            Administrador
                                        </span>

                                    </div>

                                </div>


                                <div class="response-date">

                                    <i class="fa-regular fa-clock"></i>

                                    <?= $r['created_at'] ?>

                                </div>

                            </div>


                            <div class="response-message">

                                <?= nl2br(
                                    htmlspecialchars($r['mensaje'])
                                ) ?>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </div>


        <!-- ==================================================
             FOOTER
        =================================================== -->

        <div class="page-footer">

            <a href="index.php">

                <i class="fa-solid fa-arrow-left"></i>

                Regresar a gestión de PQRS

            </a>

        </div>

    </section>

</main>

</body>
</html>