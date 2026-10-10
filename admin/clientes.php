<?php
require_once __DIR__ . '/admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

/* =========================
   BLOQUEAR / ACTIVAR CLIENTE
========================= */
if(isset($_GET['accion']) && isset($_GET['id'])){

    $id = intval($_GET['id']);
    $accion = $_GET['accion'];

    if($accion === 'bloquear'){
        $stmt = $pdo->prepare("UPDATE usuarios SET estado = 'bloqueado' WHERE id = ? AND rol_id = 1");
        $stmt->execute([$id]);
    }

    if($accion === 'activar'){
        $stmt = $pdo->prepare("UPDATE usuarios SET estado = 'activo' WHERE id = ? AND rol_id = 1");
        $stmt->execute([$id]);
    }

    header("Location: clientes.php");
    exit;
}

/* =========================
   CLIENTES REGISTRADOS
========================= */
$stmt = $pdo->query("
    SELECT 
        usuarios.id,
        usuarios.nombre,
        usuarios.email,
        usuarios.email_verificado,
        usuarios.estado,
        COUNT(DISTINCT ordenes.id) AS total_compras,
        IFNULL(SUM(ordenes.total),0) AS gastado_total
    FROM usuarios
    LEFT JOIN ordenes ON usuarios.id = ordenes.usuario_id
    WHERE usuarios.rol_id = 1
    GROUP BY usuarios.id, usuarios.nombre, usuarios.email, usuarios.email_verificado, usuarios.estado
    ORDER BY usuarios.id DESC
");
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gestión de Clientes | BODYTECH</title>

    <link rel="stylesheet"
          href="/bodytech/public/assets/css/admin_clientes.css?v=1">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>

<main class="admin-clientes-page">

    <!-- =====================================================
         HEADER
    ====================================================== -->

    <header class="admin-header">

        <div class="header-left">

            <div class="header-icon">
                <i class="fa-solid fa-users"></i>
            </div>

            <div>

                <span class="header-kicker">
                    BODYTECH ADMIN
                </span>

                <h1>
                    Gestión de clientes
                </h1>

                <p>
                    Administra los clientes registrados en la plataforma.
                </p>

            </div>

        </div>

        <a href="/bodytech/admin/index.php"
           class="back-button">

            <i class="fa-solid fa-arrow-left"></i>

            Volver al panel

        </a>

    </header>


    <!-- =====================================================
         ACCIONES SUPERIORES
    ====================================================== -->

    <section class="top-actions">

        <a href="promo_masiva.php"
           class="action-card action-promo">

            <div class="action-icon">
                <i class="fa-solid fa-envelope"></i>
            </div>

            <div class="action-content">

                <strong>
                    Enviar promoción masiva
                </strong>

                <span>
                    Envía una comunicación promocional a tus clientes.
                </span>

            </div>

            <i class="fa-solid fa-chevron-right action-arrow"></i>

        </a>


        <a href="verificaciones.php"
           class="action-card action-verification">

            <div class="action-icon">
                <i class="fa-solid fa-id-card"></i>
            </div>

            <div class="action-content">

                <strong>
                    Verificaciones pendientes
                </strong>

                <span>
                    Revisa los procesos de verificación de clientes.
                </span>

            </div>

            <i class="fa-solid fa-chevron-right action-arrow"></i>

        </a>

    </section>


    <!-- =====================================================
         RESUMEN
    ====================================================== -->

    <section class="summary-section">

        <div class="section-heading">

            <div>
                <span class="section-kicker">
                    RESUMEN
                </span>

                <h2>
                    Clientes registrados
                </h2>
            </div>

            <span class="client-count">

                <i class="fa-solid fa-users"></i>

                <?= count($clientes) ?> clientes

            </span>

        </div>


        <div class="summary-grid">

            <div class="summary-card">

                <div class="summary-card-icon blue">
                    <i class="fa-solid fa-user-group"></i>
                </div>

                <div>

                    <span>
                        Total clientes
                    </span>

                    <strong>
                        <?= count($clientes) ?>
                    </strong>

                </div>

            </div>


            <div class="summary-card">

                <div class="summary-card-icon green">
                    <i class="fa-solid fa-user-check"></i>
                </div>

                <div>

                    <span>
                        Clientes activos
                    </span>

                    <strong>

                        <?php
                        $activos = 0;

                        foreach($clientes as $cliente){

                            if($cliente['estado'] === 'activo'){
                                $activos++;
                            }

                        }

                        echo $activos;
                        ?>

                    </strong>

                </div>

            </div>


            <div class="summary-card">

                <div class="summary-card-icon red">
                    <i class="fa-solid fa-user-lock"></i>
                </div>

                <div>

                    <span>
                        Clientes bloqueados
                    </span>

                    <strong>

                        <?php
                        $bloqueados = 0;

                        foreach($clientes as $cliente){

                            if($cliente['estado'] !== 'activo'){
                                $bloqueados++;
                            }

                        }

                        echo $bloqueados;
                        ?>

                    </strong>

                </div>

            </div>

        </div>

    </section>


    <!-- =====================================================
         TABLA
    ====================================================== -->

    <section class="clients-section">

        <div class="table-card">

            <?php if(empty($clientes)): ?>

                <div class="empty-state">

                    <div class="empty-icon">
                        <i class="fa-solid fa-users-slash"></i>
                    </div>

                    <h3>
                        No hay clientes registrados
                    </h3>

                    <p>
                        Actualmente no existen clientes registrados en la plataforma.
                    </p>

                </div>

            <?php else: ?>

                <div class="table-wrapper">

                    <table class="clients-table">

                        <thead>

                            <tr>

                                <th>
                                    ID
                                </th>

                                <th>
                                    Cliente
                                </th>

                                <th>
                                    Correo
                                </th>

                                <th>
                                    Verificación
                                </th>

                                <th>
                                    Estado
                                </th>

                                <th>
                                    Compras
                                </th>

                                <th>
                                    Gastado
                                </th>

                                <th>
                                    Acciones
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php foreach($clientes as $c): ?>

                            <tr>

                                <!-- ID -->

                                <td>

                                    <span class="client-id">

                                        #<?= $c['id'] ?>

                                    </span>

                                </td>


                                <!-- CLIENTE -->

                                <td>

                                    <div class="client-info">

                                        <div class="client-avatar">

                                            <i class="fa-solid fa-user"></i>

                                        </div>

                                        <div>

                                            <strong>
                                                <?= htmlspecialchars($c['nombre']) ?>
                                            </strong>

                                            <span>
                                                Cliente registrado
                                            </span>

                                        </div>

                                    </div>

                                </td>


                                <!-- CORREO -->

                                <td>

                                    <div class="email-cell">

                                        <i class="fa-regular fa-envelope"></i>

                                        <span>
                                            <?= htmlspecialchars($c['email']) ?>
                                        </span>

                                    </div>

                                </td>


                                <!-- VERIFICACIÓN -->

                                <td>

                                    <?php if($c['email_verificado']): ?>

                                        <span class="status-badge verified">

                                            <i class="fa-solid fa-circle-check"></i>

                                            Verificado

                                        </span>

                                    <?php else: ?>

                                        <span class="status-badge unverified">

                                            <i class="fa-solid fa-circle-exclamation"></i>

                                            Pendiente

                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- ESTADO -->

                                <td>

                                    <?php if($c['estado'] === 'activo'): ?>

                                        <span class="status-badge active">

                                            <i class="fa-solid fa-circle-check"></i>

                                            Activo

                                        </span>

                                    <?php else: ?>

                                        <span class="status-badge blocked">

                                            <i class="fa-solid fa-circle-xmark"></i>

                                            Bloqueado

                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- COMPRAS -->

                                <td>

                                    <div class="purchase-cell">

                                        <i class="fa-solid fa-bag-shopping"></i>

                                        <strong>
                                            <?= $c['total_compras'] ?>
                                        </strong>

                                    </div>

                                </td>


                                <!-- GASTADO -->

                                <td>

                                    <strong class="money-cell">

                                        $<?= number_format($c['gastado_total'], 2) ?>

                                    </strong>

                                </td>


                                <!-- ACCIONES -->

                                <td>

                                    <div class="table-actions">

                                        <a class="table-action profile"
                                           href="ver_cliente.php?id=<?= $c['id'] ?>"
                                           title="Ver perfil">

                                            <i class="fa-solid fa-eye"></i>

                                            <span>
                                                Perfil
                                            </span>

                                        </a>


                                        <?php if($c['estado'] === 'activo'): ?>

                                            <a class="table-action block"
                                               href="clientes.php?accion=bloquear&id=<?= $c['id'] ?>"
                                               title="Bloquear cliente">

                                                <i class="fa-solid fa-lock"></i>

                                                <span>
                                                    Bloquear
                                                </span>

                                            </a>

                                        <?php else: ?>

                                            <a class="table-action activate"
                                               href="clientes.php?accion=activar&id=<?= $c['id'] ?>"
                                               title="Activar cliente">

                                                <i class="fa-solid fa-lock-open"></i>

                                                <span>
                                                    Activar
                                                </span>

                                            </a>

                                        <?php endif; ?>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>

    </section>


    <!-- =====================================================
         FOOTER
    ====================================================== -->

    <footer class="admin-footer">

        <a href="/bodytech/admin/index.php">

            <i class="fa-solid fa-arrow-left"></i>

            Regresar al panel administrativo

        </a>

    </footer>

</main>

</body>
</html>