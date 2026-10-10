<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/admin/admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

// Traer gimnasios creados por el admin actual
$sql = "SELECT * FROM gimnasios WHERE created_by = :admin_id ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':admin_id' => $_SESSION['admin_id']
]);
$gimnasios = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Panel - Gimnasios</title>

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >

    <!-- CSS -->
    <link
        rel="stylesheet"
        href="/bodytech/public/assets/css/admin_gimnasios.css?v=1"
    >

</head>

<body>

<div class="panel">

    <!-- =========================
         HEADER
    ========================== -->

    <header class="page-header">

        <div class="page-heading">

            <div class="heading-icon">

                <i class="fa-solid fa-dumbbell"></i>

            </div>

            <div>

                <h1>
                    Gimnasios creados
                </h1>

                <p>
                    Administra las sedes creadas desde este panel.
                </p>

            </div>

        </div>


        <a
            href="crear.php"
            class="create-button"
        >

            <i class="fa-solid fa-plus"></i>

            Crear gimnasio

        </a>

    </header>


    <!-- =========================
         CONTENIDO
    ========================== -->

    <?php if (empty($gimnasios)): ?>

        <section class="empty-state">

            <div class="empty-icon">

                <i class="fa-solid fa-dumbbell"></i>

            </div>

            <h2>
                No hay gimnasios todavía
            </h2>

            <p>
                Aún no has creado ningún gimnasio desde este panel.
            </p>

            <a
                href="crear.php"
                class="empty-button"
            >

                <i class="fa-solid fa-plus"></i>

                Crear primer gimnasio

            </a>

        </section>

    <?php else: ?>

        <section class="table-card">

            <div class="table-header">

                <div>

                    <h2>
                        Mis gimnasios
                    </h2>

                    <p>
                        Gestiona la información y publicación de cada sede.
                    </p>

                </div>

                <div class="gym-count">

                    <i class="fa-solid fa-building"></i>

                    <?= count($gimnasios) ?>

                    <?= count($gimnasios) === 1 ? 'gimnasio' : 'gimnasios' ?>

                </div>

            </div>


            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>
                                Gimnasio
                            </th>

                            <th>
                                Ciudad
                            </th>

                            <th>
                                Estado
                            </th>

                            <th>
                                Acciones
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php foreach ($gimnasios as $g): ?>

                        <tr>

                            <!-- NOMBRE -->

                            <td>

                                <div class="gym-name">

                                    <div class="gym-icon">

                                        <i class="fa-solid fa-dumbbell"></i>

                                    </div>

                                    <div>

                                        <strong>
                                            <?= htmlspecialchars($g['nombre']) ?>
                                        </strong>

                                        <span>
                                            ID #<?= $g['id'] ?>
                                        </span>

                                    </div>

                                </div>

                            </td>


                            <!-- CIUDAD -->

                            <td>

                                <div class="city">

                                    <i class="fa-solid fa-location-dot"></i>

                                    <?= htmlspecialchars($g['ciudad']) ?>

                                </div>

                            </td>


                            <!-- ESTADO -->

                            <td>

                                <?php if ($g['activo']): ?>

                                    <span class="status-badge active">

                                        <i class="fa-solid fa-circle-check"></i>

                                        Publicado

                                    </span>

                                <?php else: ?>

                                    <span class="status-badge inactive">

                                        <i class="fa-solid fa-circle-pause"></i>

                                        Desactivado

                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- ACCIONES -->

                            <td>

                                <div class="actions">

                                    <a
                                        class="action-button edit"
                                        href="editar.php?id=<?= $g['id'] ?>"
                                        title="Editar gimnasio"
                                    >

                                        <i class="fa-solid fa-pen"></i>

                                        <span>
                                            Editar
                                        </span>

                                    </a>


                                    <a
                                        class="action-button monitor"
                                        href="monitorear_gimnasio.php?id=<?= $g['id'] ?>"
                                        title="Monitorear publicación"
                                    >

                                        <i class="fa-solid fa-eye"></i>

                                        <span>
                                            Monitorear
                                        </span>

                                    </a>


                                    <a
                                        class="action-button toggle"
                                        href="toggle.php?id=<?= $g['id'] ?>"
                                        onclick="return confirm('¿Cambiar estado del gimnasio?')"
                                        title="<?= $g['activo'] ? 'Desactivar gimnasio' : 'Activar gimnasio' ?>"
                                    >

                                        <?php if ($g['activo']): ?>

                                            <i class="fa-solid fa-toggle-off"></i>

                                            <span>
                                                Desactivar
                                            </span>

                                        <?php else: ?>

                                            <i class="fa-solid fa-toggle-on"></i>

                                            <span>
                                                Activar
                                            </span>

                                        <?php endif; ?>

                                    </a>


                                    <a
                                        class="action-button delete"
                                        href="eliminar.php?id=<?= $g['id'] ?>"
                                        onclick="return confirm('¿Eliminar este gimnasio? Esta acción no se puede deshacer')"
                                        title="Eliminar gimnasio"
                                    >

                                        <i class="fa-solid fa-trash"></i>

                                        <span>
                                            Eliminar
                                        </span>

                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </section>

    <?php endif; ?>


    <!-- =========================
         FOOTER
    ========================== -->

    <div class="page-footer">

        <a
            href="/bodytech/admin/index.php"
            class="back-panel-button"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Volver al panel principal

        </a>

    </div>

</div>

</body>
</html>