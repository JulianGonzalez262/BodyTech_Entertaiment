<?php
require_once __DIR__ . '/../admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

$stmt = $pdo->query("
    SELECT *
    FROM cupones
    ORDER BY id DESC
");

$cupones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gestión de Cupones</title>

    <link rel="stylesheet"
          href="/bodytech/public/assets/css/admin_cupones.css?v=1">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>

<body>

<main class="admin-coupons-page">

    <!-- ======================================================
         ENCABEZADO
    ======================================================= -->

    <header class="page-header">

        <div class="header-content">

            <div class="header-title">

                <div class="header-icon">
                    <i class="fa-solid fa-ticket"></i>
                </div>

                <div>
                    <h1>Gestión de Cupones</h1>

                    <p>
                        Administra los códigos promocionales del marketplace.
                    </p>
                </div>

            </div>

            <a href="/bodytech/admin/index.php" class="back-button">
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
             BARRA SUPERIOR
        =================================================== -->

        <div class="section-top">

            <div class="section-heading">

                <div class="section-icon">
                    <i class="fa-solid fa-tags"></i>
                </div>

                <div>
                    <h2>Cupones registrados</h2>

                    <p>
                        <?= count($cupones) ?>
                        cupón<?= count($cupones) === 1 ? '' : 'es' ?>
                        disponible<?= count($cupones) === 1 ? '' : 's' ?>.
                    </p>
                </div>

            </div>


            <a href="crear.php" class="create-button">
                <i class="fa-solid fa-plus"></i>
                Crear cupón
            </a>

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

                            <th>Código</th>

                            <th>Tipo</th>

                            <th>Valor</th>

                            <th>Monto mínimo</th>

                            <th>Usos</th>

                            <th>Inicio</th>

                            <th>Fin</th>

                            <th>Estado</th>

                            <th>Acción</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if(empty($cupones)): ?>

                            <tr>

                                <td colspan="10">

                                    <div class="empty-state">

                                        <div class="empty-icon">
                                            <i class="fa-solid fa-ticket"></i>
                                        </div>

                                        <h3>No hay cupones registrados</h3>

                                        <p>
                                            Todavía no existen cupones promocionales
                                            creados en el sistema.
                                        </p>

                                        <a href="crear.php" class="empty-button">
                                            <i class="fa-solid fa-plus"></i>
                                            Crear primer cupón
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        <?php else: ?>

                            <?php foreach($cupones as $cupon): ?>

                                <tr>

                                    <!-- ID -->

                                    <td>

                                        <span class="coupon-id">
                                            #<?= $cupon['id'] ?>
                                        </span>

                                    </td>


                                    <!-- CÓDIGO -->

                                    <td>

                                        <div class="coupon-code">

                                            <div class="code-icon">
                                                <i class="fa-solid fa-ticket"></i>
                                            </div>

                                            <strong>
                                                <?= htmlspecialchars($cupon['codigo']) ?>
                                            </strong>

                                        </div>

                                    </td>


                                    <!-- TIPO -->

                                    <td>

                                        <?php if($cupon['tipo_descuento'] === 'porcentaje'): ?>

                                            <span class="type-badge percentage">
                                                <i class="fa-solid fa-percent"></i>
                                                Porcentaje
                                            </span>

                                        <?php else: ?>

                                            <span class="type-badge fixed">
                                                <i class="fa-solid fa-dollar-sign"></i>
                                                Fijo
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- VALOR -->

                                    <td>

                                        <strong class="discount-value">

                                            <?php if($cupon['tipo_descuento'] === 'porcentaje'): ?>

                                                <?= $cupon['valor_descuento'] ?>%

                                            <?php else: ?>

                                                $<?= number_format($cupon['valor_descuento'], 2) ?>

                                            <?php endif; ?>

                                        </strong>

                                    </td>


                                    <!-- MONTO MÍNIMO -->

                                    <td>

                                        <span class="money-value">
                                            $<?= number_format($cupon['monto_minimo'], 2) ?>
                                        </span>

                                    </td>


                                    <!-- USOS -->

                                    <td>

                                        <div class="usage-info">

                                            <span class="usage-current">
                                                <?= (int)$cupon['usos_actuales'] ?>
                                            </span>

                                            <span class="usage-divider">
                                                /
                                            </span>

                                            <span class="usage-max">

                                                <?= $cupon['usos_maximos'] ?? '∞' ?>

                                            </span>

                                        </div>

                                    </td>


                                    <!-- INICIO -->

                                    <td>

                                        <div class="date-cell">

                                            <i class="fa-regular fa-calendar"></i>

                                            <span>
                                                <?= $cupon['fecha_inicio'] ?>
                                            </span>

                                        </div>

                                    </td>


                                    <!-- FIN -->

                                    <td>

                                        <div class="date-cell">

                                            <i class="fa-regular fa-calendar-xmark"></i>

                                            <span>
                                                <?= $cupon['fecha_fin'] ?>
                                            </span>

                                        </div>

                                    </td>


                                    <!-- ESTADO -->

                                    <td>

                                        <?php if($cupon['activo']): ?>

                                            <span class="status-badge active">

                                                <i class="fa-solid fa-circle-check"></i>

                                                Activo

                                            </span>

                                        <?php else: ?>

                                            <span class="status-badge inactive">

                                                <i class="fa-solid fa-circle-pause"></i>

                                                Inactivo

                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- EDITAR -->

                                    <td>

                                        <a
                                            href="editar.php?id=<?= $cupon['id'] ?>"
                                            class="action-button"
                                            title="Editar cupón"
                                        >

                                            <i class="fa-solid fa-pen"></i>

                                            Editar

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
             PIE
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