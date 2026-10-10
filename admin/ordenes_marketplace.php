<?php

require_once __DIR__ . '/admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

/* =========================
   OBTENER ÓRDENES
========================= */

$stmt = $pdo->query("
    SELECT 
        ordenes.*,
        usuarios.nombre,
        usuarios.email
    FROM ordenes
    INNER JOIN usuarios 
        ON ordenes.usuario_id = usuarios.id
    ORDER BY ordenes.id DESC
");

$ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        Órdenes Marketplace | BodyTech
    </title>

    <link
        rel="stylesheet"
        href="/bodytech/public/assets/css/admin_ordenes.css?v=1"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >

</head>

<body>

<main class="admin-ordenes">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <header class="page-header">

        <div class="header-info">

            <div class="header-icon">

                <i class="fa-solid fa-box-open"></i>

            </div>

            <div>

                <span class="eyebrow">
                    Marketplace
                </span>

                <h1>
                    Gestión de órdenes
                </h1>

                <p>
                    Consulta pedidos, pagos, envíos y detalles de las compras realizadas.
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

            <i class="fa-solid fa-cart-shopping"></i>

        </div>

        <div class="summary-content">

            <span class="summary-label">
                Órdenes registradas
            </span>

            <strong>
                <?= count($ordenes) ?>
            </strong>

            <p>
                Pedidos disponibles para consulta administrativa.
            </p>

        </div>

    </section>


    <!-- =====================================================
         CONTENIDO
    ====================================================== -->

    <section class="table-card">


        <div class="table-header">

            <div>

                <h2>

                    <i class="fa-solid fa-clipboard-list"></i>

                    Órdenes del Marketplace

                </h2>

                <p>
                    Revisa el estado de cada compra y consulta su información detallada.
                </p>

            </div>

        </div>


        <?php if(!$ordenes): ?>


            <!-- =================================================
                 ESTADO VACÍO
            ================================================== -->

            <div class="empty-state">

                <div class="empty-icon">

                    <i class="fa-solid fa-box-open"></i>

                </div>

                <h3>
                    No existen órdenes registradas
                </h3>

                <p>
                    Actualmente no hay pedidos disponibles en el Marketplace.
                </p>

            </div>


        <?php else: ?>


            <!-- =================================================
                 TABLA
            ================================================== -->

            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Referencia</th>

                            <th>Cliente</th>

                            <th>Total</th>

                            <th>Método</th>

                            <th>Pago</th>

                            <th>Pedido</th>

                            <th>Envío</th>

                            <th>Fecha</th>

                            <th>Acción</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php foreach($ordenes as $o): ?>

                        <tr>


                            <!-- ID -->

                            <td>

                                <span class="id-badge">

                                    #<?= $o['id'] ?>

                                </span>

                            </td>


                            <!-- REFERENCIA -->

                            <td>

                                <div class="reference-cell">

                                    <i class="fa-solid fa-hashtag"></i>

                                    <span>

                                        <?= htmlspecialchars($o['referencia']) ?>

                                    </span>

                                </div>

                            </td>


                            <!-- CLIENTE -->

                            <td>

                                <div class="user-cell">

                                    <div class="user-avatar">

                                        <i class="fa-solid fa-user"></i>

                                    </div>

                                    <div>

                                        <strong>

                                            <?= htmlspecialchars($o['nombre']) ?>

                                        </strong>

                                        <span>

                                            <?= htmlspecialchars($o['email']) ?>

                                        </span>

                                    </div>

                                </div>

                            </td>


                            <!-- TOTAL -->

                            <td>

                                <strong class="price">

                                    $<?= number_format($o['total'],2) ?>

                                </strong>

                            </td>


                            <!-- MÉTODO -->

                            <td>

                                <div class="method-cell">

                                    <i class="fa-solid fa-credit-card"></i>

                                    <span>

                                        <?= strtoupper($o['metodo_pago']) ?>

                                    </span>

                                </div>

                            </td>


                            <!-- PAGO -->

                            <td>

                                <?php

                                $clasePago = 'pendiente';

                                if($o['estado_pago'] === 'aprobado'){
                                    $clasePago = 'aprobado';
                                }

                                if($o['estado_pago'] === 'rechazado'){
                                    $clasePago = 'rechazado';
                                }

                                ?>

                                <span class="status <?= $clasePago ?>">

                                    <?php if($clasePago === 'aprobado'): ?>

                                        <i class="fa-solid fa-circle-check"></i>

                                    <?php elseif($clasePago === 'rechazado'): ?>

                                        <i class="fa-solid fa-circle-xmark"></i>

                                    <?php else: ?>

                                        <i class="fa-solid fa-clock"></i>

                                    <?php endif; ?>

                                    <?= strtoupper($o['estado_pago']) ?>

                                </span>

                            </td>


                            <!-- PEDIDO -->

                            <td>

                                <?php

                                $claseOrden = 'pendiente';

                                if($o['estado_orden'] === 'procesando'){
                                    $claseOrden = 'aprobado';
                                }

                                if($o['estado_orden'] === 'cancelado'){
                                    $claseOrden = 'rechazado';
                                }

                                ?>

                                <span class="status <?= $claseOrden ?>">

                                    <?php if($claseOrden === 'aprobado'): ?>

                                        <i class="fa-solid fa-gears"></i>

                                    <?php elseif($claseOrden === 'rechazado'): ?>

                                        <i class="fa-solid fa-circle-xmark"></i>

                                    <?php else: ?>

                                        <i class="fa-solid fa-clock"></i>

                                    <?php endif; ?>

                                    <?= strtoupper($o['estado_orden']) ?>

                                </span>

                            </td>


                            <!-- ENVÍO -->

                            <td>

                                <div class="shipping-cell">

                                    <div class="shipping-recipient">

                                        <i class="fa-solid fa-user-tag"></i>

                                        <strong>

                                            <?= htmlspecialchars($o['receptor_nombre']) ?>

                                        </strong>

                                    </div>

                                    <span class="shipping-address">

                                        <i class="fa-solid fa-location-dot"></i>

                                        <?= htmlspecialchars($o['direccion_envio']) ?>

                                    </span>

                                    <span class="shipping-location">

                                        <?= htmlspecialchars($o['ciudad']) ?>

                                        <span>/</span>

                                        <?= htmlspecialchars($o['departamento']) ?>

                                    </span>

                                </div>

                            </td>


                            <!-- FECHA -->

                            <td>

                                <div class="date-cell">

                                    <i class="fa-regular fa-calendar"></i>

                                    <span>

                                        <?= $o['created_at'] ?>

                                    </span>

                                </div>

                            </td>


                            <!-- ACCIÓN -->

                            <td>

                                <a
                                    class="action-button"
                                    href="/bodytech/admin/ver_orden.php?id=<?= $o['id'] ?>"
                                >

                                    <i class="fa-solid fa-eye"></i>

                                    Ver detalle

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