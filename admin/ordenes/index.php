<?php
require_once __DIR__ . '/../admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

$busqueda = trim($_GET['buscar'] ?? '');
$estadoFiltro = trim($_GET['estado'] ?? '');

$sql = "
    SELECT
        ordenes.*,
        usuarios.nombre,
        usuarios.email
    FROM ordenes
    INNER JOIN usuarios
        ON ordenes.usuario_id = usuarios.id
    WHERE 1=1
";

$params = [];

/* =========================
   BUSCADOR
========================= */
if (!empty($busqueda)) {

    $sql .= "
        AND (
            usuarios.nombre LIKE ?
            OR usuarios.email LIKE ?
            OR ordenes.referencia LIKE ?
        )
    ";

    $like = "%{$busqueda}%";

    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

/* =========================
   FILTRO ESTADO
========================= */
$estadosPermitidos = [
    'procesando',
    'enviado',
    'entregado',
    'cancelado'
];

if (
    !empty($estadoFiltro)
    && in_array($estadoFiltro, $estadosPermitidos)
) {
    $sql .= " AND ordenes.estado_orden = ?";
    $params[] = $estadoFiltro;
}

$sql .= " ORDER BY ordenes.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

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

<title>Buscar Órdenes | BodyTech</title>

<link
    rel="stylesheet"
    href="/bodytech/public/assets/css/admin_buscar_ordenes.css?v=1"
>

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
>

</head>

<body>

<main class="admin-page">

    <!-- =========================
         ENCABEZADO
    ========================== -->

    <header class="page-header">

        <div class="header-content">

            <div class="header-title">

                <div class="header-icon">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>

                <div>
                    <h1>Buscar órdenes</h1>

                    <p>
                        Consulta y filtra las órdenes del Marketplace.
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

        </div>

    </header>


    <!-- =========================
         BUSCADOR
    ========================== -->

    <section class="search-card">

        <div class="section-heading">

            <div class="section-icon">
                <i class="fa-solid fa-filter"></i>
            </div>

            <div>
                <h2>Buscar y filtrar</h2>

                <p>
                    Encuentra una orden por cliente, correo o referencia.
                </p>
            </div>

        </div>


        <form
            method="GET"
            class="search-form"
        >

            <div class="form-group search-input-group">

                <label for="buscar">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    Búsqueda
                </label>

                <div class="input-wrapper">

                    <i class="fa-solid fa-search input-icon"></i>

                    <input
                        type="text"
                        id="buscar"
                        name="buscar"
                        placeholder="Cliente, correo o referencia..."
                        value="<?= htmlspecialchars($busqueda) ?>"
                    >

                </div>

            </div>


            <div class="form-group">

                <label for="estado">
                    <i class="fa-solid fa-list-check"></i>
                    Estado de la orden
                </label>

                <div class="input-wrapper">

                    <i class="fa-solid fa-box input-icon"></i>

                    <select
                        id="estado"
                        name="estado"
                    >

                        <option value="">
                            Todas las órdenes
                        </option>

                        <option
                            value="procesando"
                            <?= $estadoFiltro === 'procesando' ? 'selected' : '' ?>
                        >
                            Procesando
                        </option>

                        <option
                            value="enviado"
                            <?= $estadoFiltro === 'enviado' ? 'selected' : '' ?>
                        >
                            Enviado
                        </option>

                        <option
                            value="entregado"
                            <?= $estadoFiltro === 'entregado' ? 'selected' : '' ?>
                        >
                            Entregado
                        </option>

                        <option
                            value="cancelado"
                            <?= $estadoFiltro === 'cancelado' ? 'selected' : '' ?>
                        >
                            Cancelado
                        </option>

                    </select>

                </div>

            </div>


            <div class="search-actions">

                <button
                    type="submit"
                    class="search-button"
                >
                    <i class="fa-solid fa-magnifying-glass"></i>
                    Buscar
                </button>

                <a
                    href="index.php"
                    class="clear-button"
                >
                    <i class="fa-solid fa-rotate-left"></i>
                    Limpiar
                </a>

            </div>

        </form>

    </section>


    <!-- =========================
         RESULTADOS
    ========================== -->

    <section class="results-section">

        <div class="results-header">

            <div>

                <span class="results-label">
                    Resultados
                </span>

                <h2>
                    Órdenes encontradas
                </h2>

            </div>

            <div class="results-count">

                <i class="fa-solid fa-receipt"></i>

                <?= count($ordenes) ?>

                <?= count($ordenes) === 1 ? 'orden' : 'órdenes' ?>

            </div>

        </div>


        <?php if(empty($ordenes)): ?>

            <div class="empty-state">

                <div class="empty-icon">
                    <i class="fa-solid fa-box-open"></i>
                </div>

                <h3>
                    No se encontraron órdenes
                </h3>

                <p>
                    No hay órdenes que coincidan con los criterios de búsqueda.
                </p>

                <a
                    href="index.php"
                    class="empty-button"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    Ver todas las órdenes
                </a>

            </div>

        <?php else: ?>

            <div class="table-card">

                <div class="table-wrapper">

                    <table>

                        <thead>

                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Correo</th>
                                <th>Referencia</th>
                                <th>Total</th>
                                <th>Método</th>
                                <th>Pago</th>
                                <th>Orden</th>
                                <th>Fecha</th>
                                <th>Ver</th>
                            </tr>

                        </thead>

                        <tbody>

                        <?php foreach($ordenes as $o): ?>

                            <tr>

                                <td>

                                    <span class="order-id">
                                        #<?= $o['id'] ?>
                                    </span>

                                </td>


                                <td>

                                    <div class="client-cell">

                                        <div class="client-avatar">
                                            <i class="fa-solid fa-user"></i>
                                        </div>

                                        <span>
                                            <?= htmlspecialchars($o['nombre']) ?>
                                        </span>

                                    </div>

                                </td>


                                <td>

                                    <span class="email-cell">
                                        <?= htmlspecialchars($o['email']) ?>
                                    </span>

                                </td>


                                <td>

                                    <span class="reference-cell">
                                        <?= htmlspecialchars($o['referencia']) ?>
                                    </span>

                                </td>


                                <td>

                                    <strong class="total-cell">
                                        $<?= number_format($o['total'], 2) ?>
                                    </strong>

                                </td>


                                <td>

                                    <span class="method-cell">

                                        <i class="fa-solid fa-credit-card"></i>

                                        <?= strtoupper($o['metodo_pago']) ?>

                                    </span>

                                </td>


                                <td>

                                    <span class="payment-status">

                                        <?php if($o['estado_pago'] === 'aprobado'): ?>

                                            <i class="fa-solid fa-circle-check"></i>

                                        <?php elseif($o['estado_pago'] === 'rechazado'): ?>

                                            <i class="fa-solid fa-circle-xmark"></i>

                                        <?php else: ?>

                                            <i class="fa-solid fa-clock"></i>

                                        <?php endif; ?>

                                        <?= strtoupper($o['estado_pago']) ?>

                                    </span>

                                </td>


                                <td>

                                    <?php
                                    switch($o['estado_orden']) {

                                        case 'procesando':
                                            $iconoEstado = 'fa-gears';
                                            $textoEstado = 'PROCESANDO';
                                            break;

                                        case 'enviado':
                                            $iconoEstado = 'fa-truck';
                                            $textoEstado = 'ENVIADO';
                                            break;

                                        case 'entregado':
                                            $iconoEstado = 'fa-circle-check';
                                            $textoEstado = 'ENTREGADO';
                                            break;

                                        case 'cancelado':
                                            $iconoEstado = 'fa-circle-xmark';
                                            $textoEstado = 'CANCELADO';
                                            break;

                                        default:
                                            $iconoEstado = 'fa-circle';
                                            $textoEstado = strtoupper($o['estado_orden']);
                                    }
                                    ?>

                                    <span class="order-status <?= htmlspecialchars($o['estado_orden']) ?>">

                                        <i class="fa-solid <?= $iconoEstado ?>"></i>

                                        <?= $textoEstado ?>

                                    </span>

                                </td>


                                <td>

                                    <span class="date-cell">

                                        <i class="fa-regular fa-calendar"></i>

                                        <?= $o['created_at'] ?>

                                    </span>

                                </td>


                                <td>

                                    <a
                                        href="ver.php?id=<?= $o['id'] ?>"
                                        class="view-button"
                                    >
                                        <i class="fa-solid fa-eye"></i>
                                        Abrir
                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        <?php endif; ?>

    </section>


    <footer class="page-footer">

        <a href="/bodytech/admin/index.php">

            <i class="fa-solid fa-arrow-left"></i>

            Volver al panel administrativo

        </a>

    </footer>

</main>

</body>
</html>