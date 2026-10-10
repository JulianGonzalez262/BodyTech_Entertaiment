<?php
require_once __DIR__ . '/../admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

/* =========================
   FILTRO DE ESTADO
========================= */

$estado = $_GET['estado'] ?? '';

/* =========================
   CONSULTA
========================= */

$sql = "
    SELECT
        p.*,
        u.nombre,
        u.email
    FROM pqrs p
    INNER JOIN usuarios u
        ON p.usuario_id = u.id
";

$params = [];

if (!empty($estado)) {

    $sql .= " WHERE p.estado = ? ";
    $params[] = $estado;
}

$sql .= " ORDER BY p.created_at DESC ";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$pqrs = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Administrar PQRS</title>

    <link rel="stylesheet"
          href="/bodytech/public/assets/css/admin_pqrs.css?v=1">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>

<main class="admin-pqrs-page">


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

                    <h1>Gestión de PQRS</h1>

                    <p>
                        Administra las solicitudes, peticiones, quejas,
                        reclamos y sugerencias de los clientes.
                    </p>

                </div>

            </div>


            <a href="/bodytech/admin/index.php"
               class="back-button">

                <i class="fa-solid fa-arrow-left"></i>

                Volver al panel

            </a>

        </div>

    </header>


    <!-- ======================================================
         CONTENIDO
    ======================================================= -->

    <section class="content-wrapper">


        <!-- ==================================================
             CABECERA DE SECCIÓN
        =================================================== -->

        <div class="section-top">

            <div class="section-heading">

                <div class="section-icon">
                    <i class="fa-solid fa-inbox"></i>
                </div>

                <div>

                    <h2>Solicitudes recibidas</h2>

                    <p>
                        <?= count($pqrs) ?>
                        solicitud<?= count($pqrs) === 1 ? '' : 'es' ?>
                        encontradas.
                    </p>

                </div>

            </div>

        </div>


        <!-- ==================================================
             FILTROS
        =================================================== -->

        <div class="filter-card">

            <div class="filter-heading">

                <div class="filter-icon">
                    <i class="fa-solid fa-filter"></i>
                </div>

                <div>

                    <strong>Filtrar solicitudes</strong>

                    <span>
                        Consulta las PQRS según su estado.
                    </span>

                </div>

            </div>


            <form method="GET"
                  class="filter-form">

                <div class="filter-field">

                    <label for="estado">
                        Estado
                    </label>

                    <div class="select-wrapper">

                        <i class="fa-solid fa-list-check"></i>

                        <select
                            id="estado"
                            name="estado"
                        >

                            <option value="">
                                Todas
                            </option>

                            <option
                                value="pendiente"
                                <?= $estado === 'pendiente' ? 'selected' : '' ?>
                            >
                                Pendiente
                            </option>

                            <option
                                value="en_proceso"
                                <?= $estado === 'en_proceso' ? 'selected' : '' ?>
                            >
                                En proceso
                            </option>

                            <option
                                value="respondida"
                                <?= $estado === 'respondida' ? 'selected' : '' ?>
                            >
                                Respondida
                            </option>

                            <option
                                value="cerrada"
                                <?= $estado === 'cerrada' ? 'selected' : '' ?>
                            >
                                Cerrada
                            </option>

                        </select>

                    </div>

                </div>


                <button
                    type="submit"
                    class="filter-button"
                >

                    <i class="fa-solid fa-filter"></i>

                    Filtrar

                </button>


                <?php if(!empty($estado)): ?>

                    <a href="index.php"
                       class="clear-filter-button">

                        <i class="fa-solid fa-xmark"></i>

                        Limpiar

                    </a>

                <?php endif; ?>

            </form>

        </div>


        <!-- ==================================================
             TABLA
        =================================================== -->

        <div class="table-card">

            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Usuario</th>

                            <th>Tipo</th>

                            <th>Asunto</th>

                            <th>Estado</th>

                            <th>Fecha</th>

                            <th>Acción</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if(empty($pqrs)): ?>

                            <tr>

                                <td colspan="7">

                                    <div class="empty-state">

                                        <div class="empty-icon">

                                            <i class="fa-solid fa-inbox"></i>

                                        </div>

                                        <h3>
                                            No hay PQRS para mostrar
                                        </h3>

                                        <p>
                                            No existen solicitudes que
                                            coincidan con el filtro seleccionado.
                                        </p>

                                        <?php if(!empty($estado)): ?>

                                            <a href="index.php"
                                               class="empty-button">

                                                <i class="fa-solid fa-list"></i>

                                                Ver todas las solicitudes

                                            </a>

                                        <?php endif; ?>

                                    </div>

                                </td>

                            </tr>

                        <?php else: ?>

                            <?php foreach($pqrs as $fila): ?>

                                <tr>


                                    <!-- ID -->

                                    <td>

                                        <span class="pqrs-id">

                                            #<?= $fila['id'] ?>

                                        </span>

                                    </td>


                                    <!-- USUARIO -->

                                    <td>

                                        <div class="user-cell">

                                            <div class="user-avatar">

                                                <i class="fa-solid fa-user"></i>

                                            </div>

                                            <div class="user-info">

                                                <strong>
                                                    <?= htmlspecialchars($fila['nombre']) ?>
                                                </strong>

                                                <span>
                                                    <?= htmlspecialchars($fila['email']) ?>
                                                </span>

                                            </div>

                                        </div>

                                    </td>


                                    <!-- TIPO -->

                                    <td>

                                        <span class="type-badge">

                                            <i class="fa-solid fa-tag"></i>

                                            <?= ucfirst($fila['tipo']) ?>

                                        </span>

                                    </td>


                                    <!-- ASUNTO -->

                                    <td>

                                        <div class="subject-cell">

                                            <i class="fa-regular fa-message"></i>

                                            <span>
                                                <?= htmlspecialchars($fila['asunto']) ?>
                                            </span>

                                        </div>

                                    </td>


                                    <!-- ESTADO -->

                                    <td>

                                        <?php

                                        $estadoClass = $fila['estado'];

                                        $estadoIcon = 'fa-circle-question';

                                        $estadoTexto = ucfirst(str_replace('_', ' ', $fila['estado']));

                                        if($fila['estado'] === 'pendiente') {
                                            $estadoIcon = 'fa-clock';
                                        }

                                        if($fila['estado'] === 'en_proceso') {
                                            $estadoIcon = 'fa-spinner';
                                        }

                                        if($fila['estado'] === 'respondida') {
                                            $estadoIcon = 'fa-circle-check';
                                        }

                                        if($fila['estado'] === 'cerrada') {
                                            $estadoIcon = 'fa-circle-xmark';
                                        }

                                        ?>

                                        <span
                                            class="status-badge <?= $estadoClass ?>"
                                        >

                                            <i class="fa-solid <?= $estadoIcon ?>"></i>

                                            <?= $estadoTexto ?>

                                        </span>

                                    </td>


                                    <!-- FECHA -->

                                    <td>

                                        <div class="date-cell">

                                            <i class="fa-regular fa-calendar"></i>

                                            <span>
                                                <?= $fila['created_at'] ?>
                                            </span>

                                        </div>

                                    </td>


                                    <!-- ACCIÓN -->

                                    <td>

                                        <a
                                            href="ver.php?id=<?= $fila['id'] ?>"
                                            class="view-button"
                                        >

                                            <i class="fa-solid fa-eye"></i>

                                            Ver

                                        </a>

                                    </td>


                                </tr>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>


        <!-- ==================================================
             FOOTER
        =================================================== -->

        <div class="page-footer">

            <a href="/bodytech/admin/index.php">

                <i class="fa-solid fa-arrow-left"></i>

                Regresar al panel administrativo

            </a>

        </div>

    </section>

</main>

</body>
</html>