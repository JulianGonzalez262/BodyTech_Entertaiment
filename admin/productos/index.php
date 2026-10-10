<?php
require_once __DIR__ . '/../admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gestión de Productos | BodyTech</title>

    <!-- CSS -->
    <link rel="stylesheet" href="/bodytech/public/assets/css/admin_productos.css?v=1">

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >
</head>

<body>

<div class="panel">

    <!-- ======================================================
         ENCABEZADO
    ======================================================= -->

    <div class="header">

        <div class="header-title">
            <h1>
                <i class="fa-solid fa-box"></i>
                Gestión de Productos
            </h1>

            <p>Administra los productos disponibles en el marketplace.</p>
        </div>

        <a href="../index.php" class="back-button">
            <i class="fa-solid fa-arrow-left"></i>
            Volver al panel
        </a>

    </div>


    <!-- ======================================================
         ACCIONES SUPERIORES
    ======================================================= -->

    <div class="toolbar">

        <a href="crear_producto.php" class="create-button">
            <i class="fa-solid fa-plus"></i>
            Crear producto
        </a>

    </div>


    <?php
    $stmt = $pdo->query("
        SELECT *
        FROM productos
        ORDER BY id DESC
    ");

    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>


    <!-- ======================================================
         SIN PRODUCTOS
    ======================================================= -->

    <?php if(count($productos) === 0): ?>

        <div class="empty-state">

            <div class="empty-icon">
                <i class="fa-solid fa-box-open"></i>
            </div>

            <h2>No hay productos aún</h2>

            <p>
                Todavía no existen productos registrados
                en el marketplace.
            </p>

        </div>


    <?php else: ?>


        <!-- ==================================================
             TABLA DE PRODUCTOS
        =================================================== -->

        <div class="table-container">

            <table>

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Categoría</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>

                </thead>


                <tbody>

                    <?php foreach($productos as $p): ?>

                        <tr>

                            <td class="product-id">
                                #<?= $p['id'] ?>
                            </td>


                            <td class="product-name">
                                <?= htmlspecialchars($p['nombre']) ?>
                            </td>


                            <td class="product-price">
                                $<?= number_format($p['precio'], 2) ?>
                            </td>


                            <td>
                                <?= $p['stock'] ?>
                            </td>


                            <td>
                                <span class="category">
                                    <?= htmlspecialchars($p['categoria']) ?>
                                </span>
                            </td>


                            <td>

                                <?php if($p['activo']): ?>

                                    <span class="status active">
                                        <i class="fa-solid fa-circle"></i>
                                        Activo
                                    </span>

                                <?php else: ?>

                                    <span class="status inactive">
                                        <i class="fa-solid fa-circle"></i>
                                        Inactivo
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- ==================================================
                                 ACCIONES
                            =================================================== -->

                            <td>

                                <div class="actions">


                                    <!-- EDITAR -->

                                    <a
                                        href="editar_producto.php?id=<?= $p['id'] ?>"
                                        class="action-button edit"
                                        title="Editar producto"
                                    >
                                        <i class="fa-solid fa-pen"></i>
                                        <span>Editar</span>
                                    </a>


                                    <!-- MONITOREAR -->

                                    <a
                                        href="monitorear_productos.php?id=<?= $p['id'] ?>"
                                        class="action-button monitor"
                                        title="Monitorear producto"
                                    >
                                        <i class="fa-solid fa-eye"></i>
                                        <span>Monitorear</span>
                                    </a>


                                    <!-- ACTIVAR / DESACTIVAR -->

                                    <?php if($p['activo']): ?>

                                        <a
                                            href="toggle_producto.php?id=<?= $p['id'] ?>&estado=0"
                                            class="action-button deactivate"
                                            title="Desactivar producto"
                                        >
                                            <i class="fa-solid fa-toggle-off"></i>
                                            <span>Desactivar</span>
                                        </a>

                                    <?php else: ?>

                                        <a
                                            href="toggle_producto.php?id=<?= $p['id'] ?>&estado=1"
                                            class="action-button activate"
                                            title="Activar producto"
                                        >
                                            <i class="fa-solid fa-toggle-on"></i>
                                            <span>Activar</span>
                                        </a>

                                    <?php endif; ?>


                                    <!-- ELIMINAR -->

                                    <a
                                        href="eliminar_producto.php?id=<?= $p['id'] ?>"
                                        class="action-button delete"
                                        title="Eliminar producto"
                                        onclick="return confirm('¿Eliminar este producto definitivamente?');"
                                    >
                                        <i class="fa-solid fa-trash"></i>
                                        <span>Eliminar</span>
                                    </a>


                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</div>

</body>
</html>