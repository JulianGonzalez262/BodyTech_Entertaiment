<?php

require_once __DIR__ . '/admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

/* =========================
   OBTENER SOLICITUDES
========================= */

$stmt = $pdo->query("
    SELECT
        sv.id,
        sv.usuario_id,
        sv.imagen_documento,
        sv.estado,
        sv.created_at,

        u.nombre,
        u.email,
        u.tipo_documento,
        u.numero_documento,
        u.documento_verificado

    FROM solicitudes_verificacion sv

    INNER JOIN usuarios u
        ON sv.usuario_id = u.id

    ORDER BY sv.created_at DESC
");

$solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>
    Verificaciones de Identidad | BodyTech
</title>

<link
    rel="stylesheet"
    href="/bodytech/public/assets/css/admin_verificaciones.css?v=1"
>

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
>

</head>

<body>

<main class="admin-verificaciones">

    <!-- HEADER -->

    <header class="page-header">

        <div class="header-info">

            <div class="header-icon">
                <i class="fa-solid fa-id-card"></i>
            </div>

            <div>

                <span class="eyebrow">
                    Gestión de clientes
                </span>

                <h1>
                    Verificaciones de identidad
                </h1>

                <p>
                    Revisa las solicitudes de verificación documental de los clientes.
                </p>

            </div>

        </div>

        <a
            href="/bodytech/admin/clientes.php"
            class="back-button"
        >
            <i class="fa-solid fa-arrow-left"></i>
            Volver a clientes
        </a>

    </header>


    <!-- RESUMEN -->

    <section class="summary-card">

        <div class="summary-icon">
            <i class="fa-solid fa-shield-halved"></i>
        </div>

        <div class="summary-content">

            <span class="summary-label">
                Solicitudes recibidas
            </span>

            <strong>
                <?= count($solicitudes) ?>
            </strong>

            <p>
                Solicitudes de identidad registradas en el sistema.
            </p>

        </div>

    </section>


    <!-- TABLA -->

    <section class="table-card">

        <div class="table-header">

            <div>

                <h2>
                    <i class="fa-solid fa-list-check"></i>
                    Solicitudes de verificación
                </h2>

                <p>
                    Consulta la información y revisa el documento enviado por cada cliente.
                </p>

            </div>

        </div>


        <?php if(empty($solicitudes)): ?>

            <div class="empty-state">

                <div class="empty-icon">
                    <i class="fa-solid fa-folder-open"></i>
                </div>

                <h3>
                    No hay solicitudes
                </h3>

                <p>
                    Actualmente no existen solicitudes de verificación de identidad.
                </p>

            </div>

        <?php else: ?>

            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Usuario</th>

                            <th>Correo</th>

                            <th>Documento</th>

                            <th>Estado</th>

                            <th>Solicitud</th>

                            <th>Acciones</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php foreach($solicitudes as $s): ?>

                        <tr>

                            <!-- ID -->

                            <td>

                                <span class="id-badge">

                                    #<?= $s['id'] ?>

                                </span>

                            </td>


                            <!-- USUARIO -->

                            <td>

                                <div class="user-cell">

                                    <div class="user-avatar">

                                        <i class="fa-solid fa-user"></i>

                                    </div>

                                    <span>
                                        <?= htmlspecialchars($s['nombre']) ?>
                                    </span>

                                </div>

                            </td>


                            <!-- CORREO -->

                            <td>

                                <div class="email-cell">

                                    <i class="fa-solid fa-envelope"></i>

                                    <span>
                                        <?= htmlspecialchars($s['email']) ?>
                                    </span>

                                </div>

                            </td>


                            <!-- DOCUMENTO -->

                            <td>

                                <div class="document-cell">

                                    <strong>
                                        <?= htmlspecialchars($s['tipo_documento']) ?>
                                    </strong>

                                    <span>
                                        <?= htmlspecialchars($s['numero_documento']) ?>
                                    </span>

                                </div>

                            </td>


                            <!-- ESTADO -->

                            <td>

                                <span class="status status-<?= $s['estado'] ?>">

                                    <?php if($s['estado'] === 'pendiente'): ?>

                                        <i class="fa-solid fa-clock"></i>

                                    <?php elseif($s['estado'] === 'aprobada'): ?>

                                        <i class="fa-solid fa-circle-check"></i>

                                    <?php elseif($s['estado'] === 'rechazada'): ?>

                                        <i class="fa-solid fa-circle-xmark"></i>

                                    <?php endif; ?>

                                    <?= strtoupper($s['estado']) ?>

                                </span>

                            </td>


                            <!-- FECHA -->

                            <td>

                                <div class="date-cell">

                                    <i class="fa-regular fa-calendar"></i>

                                    <span>
                                        <?= $s['created_at'] ?>
                                    </span>

                                </div>

                            </td>


                            <!-- ACCIONES -->

                            <td>

                                <a
                                    class="action-button view"
                                    href="ver_documento.php?id=<?= $s['id'] ?>"
                                >

                                    <i class="fa-solid fa-eye"></i>

                                    Ver documento

                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </section>


    <!-- FOOTER -->

    <footer class="page-footer">

        <a href="/bodytech/admin/clientes.php">

            <i class="fa-solid fa-arrow-left"></i>

            Regresar a gestión de clientes

        </a>

    </footer>

</main>

</body>

</html>