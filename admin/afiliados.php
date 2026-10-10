<?php

require_once __DIR__ . '/admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

/* =========================
   LISTADO AFILIADOS + PAGOS
========================= */

$stmt = $pdo->query("
    SELECT 
        afiliados.id,
        usuarios.nombre,
        usuarios.email,
        afiliados.gym_nombre,
        afiliados.ciudad,
        afiliados.departamento,
        afiliados.tipo_membresia,
        afiliados.precio,
        afiliados.estado AS estado_afiliacion,
        afiliados.fecha_inicio,
        afiliados.fecha_fin,
        pagos.metodo,
        pagos.estado AS estado_pago,
        pagos.monto

    FROM afiliados

    INNER JOIN usuarios
        ON afiliados.usuario_id = usuarios.id

    LEFT JOIN pagos
        ON afiliados.id = pagos.afiliado_id

    ORDER BY afiliados.id DESC
");

$afiliados = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Gestión de Afiliados | BodyTech
    </title>

    <link
        rel="stylesheet"
        href="/bodytech/public/assets/css/admin_afiliados.css?v=1"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >

</head>

<body>

<main class="admin-afiliados">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <header class="page-header">

        <div class="header-info">

            <div class="header-icon">
                <i class="fa-solid fa-users"></i>
            </div>

            <div>

                <span class="eyebrow">
                    Gestión administrativa
                </span>

                <h1>
                    Gestión general de afiliados
                </h1>

                <p>
                    Consulta afiliaciones, membresías, pagos y fechas de vigencia.
                </p>

            </div>

        </div>


        <a
            href="/bodytech/admin/index.php"
            class="back-button"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Volver al panel

        </a>

    </header>


    <!-- =====================================================
         RESUMEN
    ====================================================== -->

    <section class="summary-card">

        <div class="summary-icon">

            <i class="fa-solid fa-id-card"></i>

        </div>

        <div class="summary-content">

            <span class="summary-label">
                Afiliaciones registradas
            </span>

            <strong>
                <?= count($afiliados) ?>
            </strong>

            <p>
                Registros de afiliación disponibles para consulta administrativa.
            </p>

        </div>

    </section>


    <!-- =====================================================
         TABLA
    ====================================================== -->

    <section class="table-card">


        <div class="table-header">

            <div>

                <h2>

                    <i class="fa-solid fa-clipboard-list"></i>

                    Listado de afiliados

                </h2>

                <p>
                    Información general de clientes, membresías y pagos.
                </p>

            </div>

        </div>


        <?php if(empty($afiliados)): ?>

            <div class="empty-state">

                <div class="empty-icon">

                    <i class="fa-solid fa-users-slash"></i>

                </div>

                <h3>
                    No hay afiliados registrados
                </h3>

                <p>
                    Actualmente no existen afiliaciones disponibles para mostrar.
                </p>

            </div>

        <?php else: ?>


            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Cliente</th>

                            <th>Correo</th>

                            <th>Gym</th>

                            <th>Ubicación</th>

                            <th>Membresía</th>

                            <th>Valor</th>

                            <th>Afiliación</th>

                            <th>Inicio</th>

                            <th>Fin</th>

                            <th>Pago</th>

                            <th>Método</th>

                            <th>Acción</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php foreach($afiliados as $a): ?>

                        <tr>


                            <!-- ID -->

                            <td>

                                <span class="id-badge">

                                    #<?= $a['id'] ?>

                                </span>

                            </td>


                            <!-- CLIENTE -->

                            <td>

                                <div class="user-cell">

                                    <div class="user-avatar">

                                        <i class="fa-solid fa-user"></i>

                                    </div>

                                    <span>

                                        <?= htmlspecialchars($a['nombre']) ?>

                                    </span>

                                </div>

                            </td>


                            <!-- CORREO -->

                            <td>

                                <div class="email-cell">

                                    <i class="fa-solid fa-envelope"></i>

                                    <span>

                                        <?= htmlspecialchars($a['email']) ?>

                                    </span>

                                </div>

                            </td>


                            <!-- GYM -->

                            <td>

                                <div class="gym-cell">

                                    <i class="fa-solid fa-dumbbell"></i>

                                    <span>

                                        <?= htmlspecialchars($a['gym_nombre']) ?>

                                    </span>

                                </div>

                            </td>


                            <!-- UBICACIÓN -->

                            <td>

                                <div class="location-cell">

                                    <i class="fa-solid fa-location-dot"></i>

                                    <span>

                                        <?= htmlspecialchars($a['ciudad']) ?>

                                        <small>
                                            <?= htmlspecialchars($a['departamento']) ?>
                                        </small>

                                    </span>

                                </div>

                            </td>


                            <!-- MEMBRESÍA -->

                            <td>

                                <span class="membership-badge">

                                    <i class="fa-solid fa-layer-group"></i>

                                    <?= htmlspecialchars($a['tipo_membresia']) ?>

                                </span>

                            </td>


                            <!-- VALOR -->

                            <td>

                                <strong class="price">

                                    $<?= number_format($a['precio'],2) ?>

                                </strong>

                            </td>


                            <!-- ESTADO AFILIACIÓN -->

                            <td>

                                <span class="status status-affiliation">

                                    <?php if($a['estado_afiliacion'] === 'activo'): ?>

                                        <i class="fa-solid fa-circle-check"></i>

                                    <?php else: ?>

                                        <i class="fa-solid fa-circle-pause"></i>

                                    <?php endif; ?>

                                    <?= strtoupper($a['estado_afiliacion']) ?>

                                </span>

                            </td>


                            <!-- INICIO -->

                            <td>

                                <div class="date-cell">

                                    <i class="fa-regular fa-calendar-check"></i>

                                    <span>
                                        <?= $a['fecha_inicio'] ?: '---' ?>
                                    </span>

                                </div>

                            </td>


                            <!-- FIN -->

                            <td>

                                <div class="date-cell">

                                    <i class="fa-regular fa-calendar-xmark"></i>

                                    <span>
                                        <?= $a['fecha_fin'] ?: '---' ?>
                                    </span>

                                </div>

                            </td>


                            <!-- PAGO -->

                            <td>

                                <?php if(!empty($a['estado_pago'])): ?>

                                    <span class="status status-payment status-<?= $a['estado_pago'] ?>">

                                        <?php if($a['estado_pago'] === 'aprobado'): ?>

                                            <i class="fa-solid fa-circle-check"></i>

                                        <?php elseif($a['estado_pago'] === 'pendiente'): ?>

                                            <i class="fa-solid fa-clock"></i>

                                        <?php elseif($a['estado_pago'] === 'rechazado'): ?>

                                            <i class="fa-solid fa-circle-xmark"></i>

                                        <?php else: ?>

                                            <i class="fa-solid fa-circle-info"></i>

                                        <?php endif; ?>

                                        <?= strtoupper($a['estado_pago']) ?>

                                    </span>

                                <?php else: ?>

                                    <span class="status status-no-payment">

                                        <i class="fa-solid fa-ban"></i>

                                        SIN PAGO

                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- MÉTODO -->

                            <td>

                                <div class="method-cell">

                                    <i class="fa-solid fa-credit-card"></i>

                                    <span>

                                        <?= strtoupper($a['metodo'] ?? '---') ?>

                                    </span>

                                </div>

                            </td>


                            <!-- ACCIÓN -->

                            <td>

                                <a
                                    class="action-button view"
                                    href="ver_afiliado.php?id=<?= $a['id'] ?>"
                                >

                                    <i class="fa-solid fa-eye"></i>

                                    Ver perfil

                                </a>

                            </td>


                        </tr>

                    <?php endforeach; ?>


                    </tbody>

                </table>

            </div>

        <?php endif; ?>


    </section>


    <!-- =====================================================
         FOOTER
    ====================================================== -->

    <footer class="page-footer">

        <a href="/bodytech/admin/index.php">

            <i class="fa-solid fa-arrow-left"></i>

            Regresar al panel administrativo

        </a>

    </footer>


</main>

</body>

</html>