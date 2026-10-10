<?php
require_once __DIR__ . '/../admin/admin_auth.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Panel Administrador</title>

    <!-- CSS del panel -->
    <link rel="stylesheet" href="/bodytech/public/assets/css/admin.css?v=1">

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >
</head>

<body>

<div class="panel">

    <!-- ENCABEZADO -->
    <div class="header">

        <h1>Panel de Administración</h1>

        <a href="logout.php">
            <i class="fa-solid fa-right-from-bracket"></i>
            Cerrar sesión
        </a>

    </div>


    <!-- BIENVENIDA -->
    <p>
        Bienvenido,
        <strong><?php echo $_SESSION['admin_nombre']; ?></strong>
    </p>


    <hr>


    <!-- OPCIONES ADMINISTRATIVAS -->
    <ul>

        <li>
            <a href="productos/index.php">
                <i class="fa-solid fa-box"></i>
                <span>Gestionar productos</span>
            </a>
        </li>


        <li>
            <a href="gimnasios/crear.php">
                <i class="fa-solid fa-plus"></i>
                <span>Crear gimnasio</span>
            </a>
        </li>


        <li>
            <a href="dashboard.php">
                <i class="fa-solid fa-chart-line"></i>
                <span>Ver estadísticas</span>
            </a>
        </li>


        <li>
            <a href="gimnasios/index.php">
                <i class="fa-solid fa-building"></i>
                <span>Panel de publicaciones</span>
            </a>
        </li>


        <li>
            <a href="clientes.php">
                <i class="fa-solid fa-users"></i>
                <span>Gestión de clientes</span>
            </a>
        </li>


        <li>
            <a href="afiliados.php">
                <i class="fa-solid fa-dumbbell"></i>
                <span>Gestión de afiliados</span>
            </a>
        </li>


        <li>
            <a href="ordenes_marketplace.php">
                <i class="fa-solid fa-truck"></i>
                <span>Gestionar órdenes marketplace</span>
            </a>
        </li>


        <li>
            <a href="cupones/index.php">
                <i class="fa-solid fa-ticket"></i>
                <span>Gestión de cupones y promociones</span>
            </a>
        </li>


        <li>
            <a href="/bodytech/admin/pqrs/index.php">
                <i class="fa-solid fa-envelope"></i>
                <span>Gestionar PQRS</span>
            </a>
        </li>

    </ul>

</div>

</body>
</html>