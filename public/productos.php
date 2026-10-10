<?php

require_once '../app/views/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';


/* ==========================================================
   MODO DE BÚSQUEDA
========================================================== */

$modo = $_GET['modo'] ?? 'productos';

if (!in_array($modo, ['productos', 'gimnasios'], true)) {
    $modo = 'productos';
}


/* ==========================================================
   BUSCADOR GENERAL
========================================================== */

$buscar = trim($_GET['buscar'] ?? '');


/* ==========================================================
   FILTROS
========================================================== */

$precios = $_GET['precio'] ?? [];
$categorias = $_GET['categoria'] ?? [];
$marcas = $_GET['marca'] ?? [];
$stocks = $_GET['stock'] ?? [];
$estados = $_GET['estado'] ?? [];
$ciudades = $_GET['ciudad'] ?? [];
$gymEstados = $_GET['gym_estado'] ?? [];



/* ==========================================================
   NORMALIZAR FILTROS
========================================================== */

if (!is_array($precios)) {
    $precios = [$precios];
}

if (!is_array($categorias)) {
    $categorias = [$categorias];
}

if (!is_array($marcas)) {
    $marcas = [$marcas];
}

if (!is_array($stocks)) {
    $stocks = [$stocks];
}

if (!is_array($estados)) {
    $estados = [$estados];
}

if (!is_array($ciudades)) {
    $ciudades = [$ciudades];
}

if (!is_array($gymEstados)) {
    $gymEstados = [$gymEstados];
}




/* ==========================================================
   LIMPIAR VALORES VACÍOS
========================================================== */

$precios = array_values(array_filter($precios, 'strlen'));
$categorias = array_values(array_filter($categorias, 'strlen'));
$marcas = array_values(array_filter($marcas, 'strlen'));
$stocks = array_values(array_filter($stocks, 'strlen'));
$estados = array_values(array_filter($estados, 'strlen'));
$ciudades = array_values(array_filter($ciudades, 'strlen'));
$gymEstados = array_values(array_filter($gymEstados, 'strlen'));



/* ==========================================================
   PRODUCTOS
========================================================== */

$productos = [];


if ($modo === 'productos') {

    $where = [];
    $params = [];


    /* ------------------------------------------------------
       ESTADO BASE
    ------------------------------------------------------ */

    $where[] = "activo = 1";


    /* ------------------------------------------------------
       BUSCADOR GENERAL
       
       Busca texto en:
       - nombre
       - descripción
       - categoría
    ------------------------------------------------------ */

    if ($buscar !== '') {

        $where[] = "
            (
                nombre LIKE ?
                OR descripcion LIKE ?
                OR categoria LIKE ?
            )
        ";

        $termino = "%{$buscar}%";

        $params[] = $termino;
        $params[] = $termino;
        $params[] = $termino;
    }


    /* ------------------------------------------------------
       FILTRO PRECIO
    ------------------------------------------------------ */

    if (!empty($precios)) {

        $precioConditions = [];

        foreach ($precios as $precio) {

            switch ($precio) {

                case '0-50000':

                    $precioConditions[] = "precio < 50000";

                    break;


                case '50000-100000':

                    $precioConditions[] = "
                        precio >= 50000
                        AND precio <= 100000
                    ";

                    break;


                case '100000-200000':

                    $precioConditions[] = "
                        precio > 100000
                        AND precio <= 200000
                    ";

                    break;


                case '200000':

                    $precioConditions[] = "precio > 200000";

                    break;
            }
        }

        if (!empty($precioConditions)) {

            $where[] = '(' .
                implode(' OR ', $precioConditions) .
            ')';
        }
    }


    /* ------------------------------------------------------
       FILTRO CATEGORÍA
       
       Las opciones funcionan como búsqueda de texto.
       No se obliga a que coincidan exactamente.
    ------------------------------------------------------ */

    if (!empty($categorias)) {

        $categoriaConditions = [];

        foreach ($categorias as $categoria) {

            $categoriaConditions[] = "
                (
                    nombre LIKE ?
                    OR descripcion LIKE ?
                    OR categoria LIKE ?
                )
            ";

            $termino = "%{$categoria}%";

            $params[] = $termino;
            $params[] = $termino;
            $params[] = $termino;
        }

        $where[] = '(' .
            implode(' OR ', $categoriaConditions) .
        ')';
    }


    /* ------------------------------------------------------
       FILTRO MARCA
       
       La tabla productos NO tiene columna marca.
       
       Por eso NO inventamos una columna.
       
       El nombre seleccionado funciona como búsqueda
       general dentro de:
       - nombre
       - descripción
       - categoría
    ------------------------------------------------------ */

    if (!empty($marcas)) {

        $marcaConditions = [];

        foreach ($marcas as $marca) {

            $marcaConditions[] = "
                (
                    nombre LIKE ?
                    OR descripcion LIKE ?
                    OR categoria LIKE ?
                )
            ";

            $termino = "%{$marca}%";

            $params[] = $termino;
            $params[] = $termino;
            $params[] = $termino;
        }

        $where[] = '(' .
            implode(' OR ', $marcaConditions) .
        ')';
    }


    /* ------------------------------------------------------
       FILTRO STOCK
    ------------------------------------------------------ */

    if (!empty($stocks)) {

        $stockConditions = [];

        foreach ($stocks as $stock) {

            if ($stock === 'disponible') {

                $stockConditions[] = "stock > 0";
            }

            if ($stock === 'agotado') {

                $stockConditions[] = "stock <= 0";
            }
        }

        if (!empty($stockConditions)) {

            $where[] = '(' .
                implode(' OR ', $stockConditions) .
            ')';
        }
    }


    /* ------------------------------------------------------
       FILTRO ESTADO
    ------------------------------------------------------ */

    if (!empty($estados)) {

        $estadoConditions = [];

        foreach ($estados as $estado) {

            if ($estado === 'activo') {

                $estadoConditions[] = "activo = 1";
            }

            if ($estado === 'inactivo') {

                $estadoConditions[] = "activo = 0";
            }
        }

        if (!empty($estadoConditions)) {

            $where[] = '(' .
                implode(' OR ', $estadoConditions) .
            ')';
        }
    }


    /* ------------------------------------------------------
       CONSULTA FINAL
    ------------------------------------------------------ */

    $sql = "
        SELECT *
        FROM productos
        WHERE " . implode(' AND ', $where) . "
        ORDER BY id DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* ==========================================================
   GIMNASIOS
   SOLO SE CONSULTAN CUANDO EL MODO ES GIMNASIOS
========================================================== */

$gimnasios = [];


if ($modo === 'gimnasios') {

    $gymWhere = [];
    $gymParams = [];


    /* ======================================================
       ESTADO BASE
    ====================================================== */

    $gymWhere[] = "activo = 1";


    /* ======================================================
       BUSCADOR GENERAL
       
       Busca el texto en todos los campos de texto
       relevantes del gimnasio.
    ====================================================== */

    if ($buscar !== '') {

        $gymWhere[] = "
            (
                nombre LIKE ?
                OR descripcion LIKE ?
                OR direccion LIKE ?
                OR ciudad LIKE ?
                OR telefono LIKE ?
            )
        ";

        $terminoGym = "%{$buscar}%";

        $gymParams[] = $terminoGym;
        $gymParams[] = $terminoGym;
        $gymParams[] = $terminoGym;
        $gymParams[] = $terminoGym;
        $gymParams[] = $terminoGym;
    }


   /* ------------------------------------------------------
   FILTRO CIUDAD
   Cada ciudad funciona como búsqueda textual general.
------------------------------------------------------ */

        $ciudades = $_GET['ciudad'] ?? [];

        if (!is_array($ciudades)) {
            $ciudades = [$ciudades];
        }

        $ciudades = array_values(
            array_filter($ciudades, 'strlen')
        );


        if (!empty($ciudades)) {

            $ciudadConditions = [];

            foreach ($ciudades as $ciudad) {

                $ciudadConditions[] = "
                    (
                        nombre LIKE ?
                        OR descripcion LIKE ?
                        OR direccion LIKE ?
                        OR ciudad LIKE ?
                    )
                ";

                $terminoCiudad = "%{$ciudad}%";

                $gymParams[] = $terminoCiudad;
                $gymParams[] = $terminoCiudad;
                $gymParams[] = $terminoCiudad;
                $gymParams[] = $terminoCiudad;
            }

            $gymWhere[] = '(' .
                implode(' OR ', $ciudadConditions) .
            ')';
}


    /* ======================================================
       FILTRO ESTADO
       
       Mantiene la misma filosofía del sistema de productos.
    ====================================================== */

    if (!empty($gymEstados)) {

        $gymEstadoConditions = [];


        foreach ($gymEstados as $estado) {

            if ($estado === 'activo') {

                $gymEstadoConditions[] =
                    "activo = 1";
            }


            if ($estado === 'inactivo') {

                $gymEstadoConditions[] =
                    "activo = 0";
            }
        }


        if (!empty($gymEstadoConditions)) {

            $gymWhere[] =
                '(' .
                implode(' OR ', $gymEstadoConditions) .
                ')';
        }
    }


    /* ======================================================
       CONSULTA FINAL
    ====================================================== */

    $gymSql = "
        SELECT *
        FROM gimnasios
        WHERE " .
        implode(' AND ', $gymWhere) . "
        ORDER BY id DESC
    ";


    $gymStmt =
        $pdo->prepare($gymSql);


    $gymStmt->execute(
        $gymParams
    );


    $gimnasios =
        $gymStmt->fetchAll(
            PDO::FETCH_ASSOC
        );
}

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
        <?= $modo === 'gimnasios'
            ? 'Gimnasios'
            : 'Productos'
        ?>
        | BODYTECH
    </title>

    <link
        rel="stylesheet"
        href="/bodytech/public/assets/css/productos.css?v=2"
    >

</head>


<body>


<main class="products-page">


    <div class="products-container">


        <!-- ==================================================
             ENCABEZADO
        =================================================== -->

        <header class="products-header">

            <div class="products-header-content">

                <span class="products-label">

                    MARKETPLACE FITNESS

                </span>


                <h1>

                    Encuentra lo que necesitas

                </h1>


                <p>

                    Explora nuestra selección de productos
                    fitness y suplementos.

                </p>

            </div>


            <div class="products-header-icon">

                <i class="fa-solid fa-store"></i>

            </div>

        </header>



        <!-- ==================================================
             BUSCADOR
        =================================================== -->

        <section class="products-search-section">


            <form
                method="GET"
                class="products-search-form"
            >

                <input
                    type="hidden"
                    name="modo"
                    value="<?= htmlspecialchars($modo) ?>"
                >


                <div class="products-search-box">

                    <i class="fa-solid fa-magnifying-glass"></i>


                    <input
                        type="text"
                        name="buscar"
                        value="<?= htmlspecialchars($buscar) ?>"
                        placeholder="<?= $modo === 'gimnasios'
                            ? 'Buscar gimnasios...'
                            : 'Buscar productos...'
                        ?>"
                        autocomplete="off"
                    >


                    <?php if ($buscar !== ''): ?>

                        <a
                            href="/bodytech/public/productos.php?modo=<?= htmlspecialchars($modo) ?>"
                            class="products-search-clear"
                            aria-label="Limpiar búsqueda"
                        >

                            <i class="fa-solid fa-xmark"></i>

                        </a>

                    <?php endif; ?>


                </div>


                <button
                    type="submit"
                    class="products-search-button"
                >

                    Buscar

                    <i class="fa-solid fa-arrow-right"></i>

                </button>

            </form>



            <!-- ==================================================
                 MODO
            =================================================== -->

            <div class="products-search-mode">


                <span class="products-mode-label">

                    Buscar en:

                </span>


                <a
                    href="/bodytech/public/productos.php?modo=productos<?= $buscar !== '' ? '&buscar=' . urlencode($buscar) : '' ?>"
                    class="products-mode <?= $modo === 'productos' ? 'active' : '' ?>"
                >

                    <i class="fa-solid fa-box"></i>

                    Productos

                </a>


                <a
                    href="/bodytech/public/productos.php?modo=gimnasios<?= $buscar !== '' ? '&buscar=' . urlencode($buscar) : '' ?>"
                    class="products-mode <?= $modo === 'gimnasios' ? 'active' : '' ?>"
                >

                    <i class="fa-solid fa-dumbbell"></i>

                    Gimnasios

                </a>

            </div>


        </section>



        <?php if ($modo === 'productos'): ?>


        <!-- ==================================================
             RESULTADOS PRODUCTOS
        =================================================== -->

        <div class="products-results-layout">


            <!-- ==================================================
                 FILTROS
            =================================================== -->

            <aside class="products-filters">


                <div class="products-filters-header">

                    <div>

                        <span>
                            FILTRAR
                        </span>

                        <h2>
                            Productos
                        </h2>

                    </div>

                    <i class="fa-solid fa-sliders"></i>

                </div>



                <!-- ==================================================
                     PRECIO
                =================================================== -->

                <div class="filter-group">

                    <h3>

                        Precio

                        <i class="fa-solid fa-chevron-down"></i>

                    </h3>


                    <label class="filter-option">

                        <input
                            type="checkbox"
                            name="precio[]"
                            value="0-50000"
                            <?= in_array('0-50000', $precios, true) ? 'checked' : '' ?>
                        >

                        <span class="filter-check"></span>

                        Menos de $50.000

                    </label>


                    <label class="filter-option">

                        <input
                            type="checkbox"
                            name="precio[]"
                            value="50000-100000"
                            <?= in_array('50000-100000', $precios, true) ? 'checked' : '' ?>
                        >

                        <span class="filter-check"></span>

                        $50.000 – $100.000

                    </label>


                    <label class="filter-option">

                        <input
                            type="checkbox"
                            name="precio[]"
                            value="100000-200000"
                            <?= in_array('100000-200000', $precios, true) ? 'checked' : '' ?>
                        >

                        <span class="filter-check"></span>

                        $100.000 – $200.000

                    </label>


                    <label class="filter-option">

                        <input
                            type="checkbox"
                            name="precio[]"
                            value="200000"
                            <?= in_array('200000', $precios, true) ? 'checked' : '' ?>
                        >

                        <span class="filter-check"></span>

                        Más de $200.000

                    </label>

                </div>



                <!-- ==================================================
                     CATEGORÍA
                =================================================== -->

                <div class="filter-group">

                    <h3>

                        Categoría

                        <i class="fa-solid fa-chevron-down"></i>

                    </h3>


                    <label class="filter-option">

                        <input
                            type="checkbox"
                            name="categoria[]"
                            value="proteina"
                            <?= in_array('proteina', $categorias, true) ? 'checked' : '' ?>
                        >

                        <span class="filter-check"></span>

                        Proteínas

                    </label>


                    <label class="filter-option">

                        <input
                            type="checkbox"
                            name="categoria[]"
                            value="creatina"
                            <?= in_array('creatina', $categorias, true) ? 'checked' : '' ?>
                        >

                        <span class="filter-check"></span>

                        Creatina

                    </label>


                    <label class="filter-option">

                        <input
                            type="checkbox"
                            name="categoria[]"
                            value="pre-entreno"
                            <?= in_array('pre-entreno', $categorias, true) ? 'checked' : '' ?>
                        >

                        <span class="filter-check"></span>

                        Pre-entreno

                    </label>


                    <label class="filter-option">

                        <input
                            type="checkbox"
                            name="categoria[]"
                            value="aminoacidos"
                            <?= in_array('aminoacidos', $categorias, true) ? 'checked' : '' ?>
                        >

                        <span class="filter-check"></span>

                        Aminoácidos

                    </label>


                    <label class="filter-option">

                        <input
                            type="checkbox"
                            name="categoria[]"
                            value="vitaminas"
                            <?= in_array('vitaminas', $categorias, true) ? 'checked' : '' ?>
                        >

                        <span class="filter-check"></span>

                        Vitaminas

                    </label>

                </div>



                <!-- ==================================================
                     MARCA
                =================================================== -->

                <div class="filter-group">

                    <h3>

                        Marca

                        <i class="fa-solid fa-chevron-down"></i>

                    </h3>


                    <!--
                        IMPORTANTE:
                        Estos valores funcionan como búsquedas
                        de texto generales.

                        No se consulta una columna "marca"
                        porque actualmente NO existe en productos.
                    -->


                    <label class="filter-option">

                        <input
                            type="checkbox"
                            name="marca[]"
                            value="Optimum Nutrition"
                            <?= in_array('Optimum Nutrition', $marcas, true) ? 'checked' : '' ?>
                        >

                        <span class="filter-check"></span>

                        Optimum Nutrition

                    </label>


                    <label class="filter-option">

                        <input
                            type="checkbox"
                            name="marca[]"
                            value="MuscleTech"
                            <?= in_array('MuscleTech', $marcas, true) ? 'checked' : '' ?>
                        >

                        <span class="filter-check"></span>

                        MuscleTech

                    </label>


                    <label class="filter-option">

                        <input
                            type="checkbox"
                            name="marca[]"
                            value="Universal Nutrition"
                            <?= in_array('Universal Nutrition', $marcas, true) ? 'checked' : '' ?>
                        >

                        <span class="filter-check"></span>

                        Universal Nutrition

                    </label>


                    <label class="filter-option">

                        <input
                            type="checkbox"
                            name="marca[]"
                            value="MusclePharm"
                            <?= in_array('MusclePharm', $marcas, true) ? 'checked' : '' ?>
                        >

                        <span class="filter-check"></span>

                        MusclePharm

                    </label>

                </div>



                <!-- ==================================================
                     DISPONIBILIDAD
                =================================================== -->

                <div class="filter-group">

                    <h3>

                        Disponibilidad

                        <i class="fa-solid fa-chevron-down"></i>

                    </h3>


                    <label class="filter-option">

                        <input
                            type="checkbox"
                            name="stock[]"
                            value="disponible"
                            <?= in_array('disponible', $stocks, true) ? 'checked' : '' ?>
                        >

                        <span class="filter-check"></span>

                        Disponible

                    </label>


                    <label class="filter-option">

                        <input
                            type="checkbox"
                            name="stock[]"
                            value="agotado"
                            <?= in_array('agotado', $stocks, true) ? 'checked' : '' ?>
                        >

                        <span class="filter-check"></span>

                        Agotado

                    </label>

                </div>



                <!-- ==================================================
                     ESTADO
                =================================================== -->

                <div class="filter-group">

                    <h3>

                        Estado

                        <i class="fa-solid fa-chevron-down"></i>

                    </h3>


                    <label class="filter-option">

                        <input
                            type="checkbox"
                            name="estado[]"
                            value="activo"
                            <?= in_array('activo', $estados, true) ? 'checked' : '' ?>
                        >

                        <span class="filter-check"></span>

                        Activos

                    </label>

                </div>


                <button
                    type="button"
                    class="clear-filters-button"
                >

                    <i class="fa-solid fa-rotate-left"></i>

                    Limpiar filtros

                </button>


            </aside>



            <!-- ==================================================
                 CONTENIDO
            =================================================== -->

            <section class="products-content">


                <div class="products-toolbar">


                    <div class="products-result-info">


                        <?php if ($buscar !== ''): ?>

                            <span class="products-result-label">

                                RESULTADOS PARA

                            </span>


                            <h2>

                                "<?= htmlspecialchars($buscar) ?>"

                            </h2>

                        <?php else: ?>

                            <span class="products-result-label">

                                CATÁLOGO BODYTECH

                            </span>


                            <h2>

                                Todos los productos

                            </h2>

                        <?php endif; ?>


                        <p>

                            <?= count($productos) ?>

                            producto(s) encontrado(s)

                        </p>

                    </div>



                    <div class="products-sort">

                        <label for="sortProducts">

                            Ordenar por

                        </label>


                        <select id="sortProducts">

                            <option value="recent">
                                Más recientes
                            </option>

                            <option value="price-low">
                                Precio: menor a mayor
                            </option>

                            <option value="price-high">
                                Precio: mayor a menor
                            </option>

                            <option value="rating">
                                Mejor valorados
                            </option>

                        </select>

                    </div>


                </div>



                <!-- ==================================================
                     GRID PRODUCTOS
                =================================================== -->

                <div class="products-grid">


                    <?php if (!$productos): ?>


                        <div class="products-empty">

                            <div class="products-empty-icon">

                                <i class="fa-solid fa-box-open"></i>

                            </div>


                            <h2>

                                No encontramos productos

                            </h2>


                            <p>

                                Intenta realizar otra búsqueda
                                o modifica los filtros.

                            </p>


                            <a
                                href="/bodytech/public/productos.php"
                                class="products-empty-button"
                            >

                                <i class="fa-solid fa-arrow-rotate-left"></i>

                                Ver todos los productos

                            </a>

                        </div>


                    <?php endif; ?>



                    <?php foreach ($productos as $producto): ?>


                        <?php

                        /* ======================================
                           IMAGEN
                        ======================================= */

                        $imgStmt = $pdo->prepare("
                            SELECT media_path
                            FROM producto_media
                            WHERE producto_id = ?
                            AND media_type = 'image'
                            LIMIT 1
                        ");

                        $imgStmt->execute([
                            $producto['id']
                        ]);

                        $img = $imgStmt->fetchColumn();


                        /* ======================================
                           RATING
                        ======================================= */

                        $ratingStmt = $pdo->prepare("
                            SELECT
                                AVG(calificacion) AS promedio,
                                COUNT(*) AS total
                            FROM resenas_productos
                            WHERE producto_id = ?
                            AND estado = 'aprobado'
                        ");

                        $ratingStmt->execute([
                            $producto['id']
                        ]);

                        $rating =
                            $ratingStmt->fetch(PDO::FETCH_ASSOC);


                        $promedio =
                            round(
                                $rating['promedio'] ?? 0,
                                1
                            );


                        $totalResenas =
                            $rating['total'] ?? 0;

                        ?>


                        <article class="product-card">


                            <a
                                href="/bodytech/public/producto.php?id=<?= (int)$producto['id'] ?>"
                                class="product-card-image"
                            >


                                <?php if ($img): ?>

                                    <img
                                        src="/bodytech/public/uploads/productos/<?= htmlspecialchars($img) ?>"
                                        alt="<?= htmlspecialchars($producto['nombre']) ?>"
                                    >

                                <?php else: ?>

                                    <div class="product-card-no-image">

                                        <i class="fa-regular fa-image"></i>

                                    </div>

                                <?php endif; ?>


                                <span class="product-card-category">

                                    <?= htmlspecialchars(
                                        $producto['categoria']
                                    ) ?>

                                </span>


                            </a>



                            <div class="product-card-body">


                                <div class="product-rating">


                                    <div class="product-stars">

                                        <?php

                                        $fullStars =
                                            floor($promedio);

                                        for (
                                            $i = 1;
                                            $i <= 5;
                                            $i++
                                        ):

                                        ?>

                                            <?php if (
                                                $i <= $fullStars
                                            ): ?>

                                                <i class="fa-solid fa-star"></i>

                                            <?php else: ?>

                                                <i class="fa-regular fa-star"></i>

                                            <?php endif; ?>

                                        <?php endfor; ?>

                                    </div>


                                    <span>

                                        <?= $promedio ?>

                                        ·

                                        <?= $totalResenas ?>

                                        reseña(s)

                                    </span>


                                </div>



                                <h3 class="product-card-title">

                                    <a
                                        href="/bodytech/public/producto.php?id=<?= (int)$producto['id'] ?>"
                                    >

                                        <?= htmlspecialchars(
                                            $producto['nombre']
                                        ) ?>

                                    </a>

                                </h3>



                                <?php if (
                                    !empty($producto['descripcion'])
                                ): ?>

                                    <p class="product-card-description">

                                        <?= htmlspecialchars(
                                            $producto['descripcion']
                                        ) ?>

                                    </p>

                                <?php endif; ?>



                                <div class="product-card-price">

                                    $

                                    <?= number_format(
                                        $producto['precio'],
                                        2
                                    ) ?>

                                </div>



                                <?php if (
                                    (int)$producto['stock'] > 0
                                ): ?>

                                    <div class="product-stock available">

                                        <i class="fa-solid fa-circle-check"></i>

                                        Disponible

                                        <span>

                                            <?= (int)$producto['stock'] ?>

                                            unidades

                                        </span>

                                    </div>

                                <?php else: ?>

                                    <div class="product-stock unavailable">

                                        <i class="fa-solid fa-circle-xmark"></i>

                                        Agotado

                                    </div>

                                <?php endif; ?>



                                <a
                                    href="/bodytech/public/producto.php?id=<?= (int)$producto['id'] ?>"
                                    class="product-view-button"
                                >

                                    Ver producto

                                    <i class="fa-solid fa-arrow-right"></i>

                                </a>


                            </div>


                        </article>


                    <?php endforeach; ?>


                </div>


            </section>


        </div>


      <?php else: ?>


<!-- ==================================================
     MODO GIMNASIOS
=================================================== -->

<div class="products-results-layout">


    <!-- ==================================================
         FILTROS GIMNASIOS
    =================================================== -->

    <aside class="products-filters">


        <div class="products-filters-header">

            <div>

                <span>
                    FILTRAR
                </span>

                <h2>
                    Gimnasios
                </h2>

            </div>


            <i class="fa-solid fa-sliders"></i>

        </div>



        <!-- ==================================================
             CIUDAD
        =================================================== -->

        <div class="filter-group">

                <h3>

                    Ciudad

                    <i class="fa-solid fa-chevron-down"></i>

                </h3>


                <label class="filter-option">

                    <input
                        type="checkbox"
                        name="ciudad[]"
                        value="Bogotá"
                        <?= in_array('Bogotá', $ciudades, true) ? 'checked' : '' ?>
                    >

                    <span class="filter-check"></span>

                    Bogotá

                </label>


                <label class="filter-option">

                    <input
                        type="checkbox"
                        name="ciudad[]"
                        value="Medellín"
                        <?= in_array('Medellín', $ciudades, true) ? 'checked' : '' ?>
                    >

                    <span class="filter-check"></span>

                    Medellín

                </label>


                <label class="filter-option">

                    <input
                        type="checkbox"
                        name="ciudad[]"
                        value="Rionegro"
                        <?= in_array('Rionegro', $ciudades, true) ? 'checked' : '' ?>
                    >

                    <span class="filter-check"></span>

                    Rionegro

                </label>


                <label class="filter-option">

                    <input
                        type="checkbox"
                        name="ciudad[]"
                        value="Bello"
                        <?= in_array('Bello', $ciudades, true) ? 'checked' : '' ?>
                    >

                    <span class="filter-check"></span>

                    Bello

                </label>


                <label class="filter-option">

                    <input
                        type="checkbox"
                        name="ciudad[]"
                        value="Soacha"
                        <?= in_array('Soacha', $ciudades, true) ? 'checked' : '' ?>
                    >

                    <span class="filter-check"></span>

                    Soacha

                </label>


                <label class="filter-option">

                    <input
                        type="checkbox"
                        name="ciudad[]"
                        value="Zipaquirá"
                        <?= in_array('Zipaquirá', $ciudades, true) ? 'checked' : '' ?>
                    >

                    <span class="filter-check"></span>

                    Zipaquirá

                </label>


                <label class="filter-option">

                    <input
                        type="checkbox"
                        name="ciudad[]"
                        value="Girardot"
                        <?= in_array('Girardot', $ciudades, true) ? 'checked' : '' ?>
                    >

                    <span class="filter-check"></span>

                    Girardot

                </label>


                <label class="filter-option">

                    <input
                        type="checkbox"
                        name="ciudad[]"
                        value="Tunja"
                        <?= in_array('Tunja', $ciudades, true) ? 'checked' : '' ?>
                    >

                    <span class="filter-check"></span>

                    Tunja

                </label>


                <label class="filter-option">

                    <input
                        type="checkbox"
                        name="ciudad[]"
                        value="Duitama"
                        <?= in_array('Duitama', $ciudades, true) ? 'checked' : '' ?>
                    >

                    <span class="filter-check"></span>

                    Duitama

                </label>


                <label class="filter-option">

                    <input
                        type="checkbox"
                        name="ciudad[]"
                        value="Sogamoso"
                        <?= in_array('Sogamoso', $ciudades, true) ? 'checked' : '' ?>
                    >

                    <span class="filter-check"></span>

                    Sogamoso

                </label>


                <label class="filter-option">

                    <input
                        type="checkbox"
                        name="ciudad[]"
                        value="Manizales"
                        <?= in_array('Manizales', $ciudades, true) ? 'checked' : '' ?>
                    >

                    <span class="filter-check"></span>

                    Manizales

                </label>


                <label class="filter-option">

                    <input
                        type="checkbox"
                        name="ciudad[]"
                        value="Pereira"
                        <?= in_array('Pereira', $ciudades, true) ? 'checked' : '' ?>
                    >

                    <span class="filter-check"></span>

                    Pereira

                </label>


                <label class="filter-option">

                    <input
                        type="checkbox"
                        name="ciudad[]"
                        value="Armenia"
                        <?= in_array('Armenia', $ciudades, true) ? 'checked' : '' ?>
                    >

                    <span class="filter-check"></span>

                    Armenia

                </label>


                <label class="filter-option">

                    <input
                        type="checkbox"
                        name="ciudad[]"
                        value="Ibagué"
                        <?= in_array('Ibagué', $ciudades, true) ? 'checked' : '' ?>
                    >

                    <span class="filter-check"></span>

                    Ibagué

                </label>


                <label class="filter-option">

                    <input
                        type="checkbox"
                        name="ciudad[]"
                        value="Neiva"
                        <?= in_array('Neiva', $ciudades, true) ? 'checked' : '' ?>
                    >

                    <span class="filter-check"></span>

                    Neiva

                </label>


                <label class="filter-option">

                    <input
                        type="checkbox"
                        name="ciudad[]"
                        value="Bucaramanga"
                        <?= in_array('Bucaramanga', $ciudades, true) ? 'checked' : '' ?>
                    >

                    <span class="filter-check"></span>

                    Bucaramanga

                </label>


                <label class="filter-option">

                    <input
                        type="checkbox"
                        name="ciudad[]"
                        value="Floridablanca"
                        <?= in_array('Floridablanca', $ciudades, true) ? 'checked' : '' ?>
                    >

                    <span class="filter-check"></span>

                    Floridablanca

                </label>


                <label class="filter-option">

                    <input
                        type="checkbox"
                        name="ciudad[]"
                        value="Barrancabermeja"
                        <?= in_array('Barrancabermeja', $ciudades, true) ? 'checked' : '' ?>
                    >

                    <span class="filter-check"></span>

                    Barrancabermeja

                </label>


                <label class="filter-option">

                    <input
                        type="checkbox"
                        name="ciudad[]"
                        value="Cúcuta"
                        <?= in_array('Cúcuta', $ciudades, true) ? 'checked' : '' ?>
                    >

                    <span class="filter-check"></span>

                    Cúcuta

                </label>


                <label class="filter-option">

                    <input
                        type="checkbox"
                        name="ciudad[]"
                        value="Popayán"
                        <?= in_array('Popayán', $ciudades, true) ? 'checked' : '' ?>
                    >

                    <span class="filter-check"></span>

                    Popayán

                </label>


                <label class="filter-option">

                    <input
                        type="checkbox"
                        name="ciudad[]"
                        value="Pasto"
                        <?= in_array('Pasto', $ciudades, true) ? 'checked' : '' ?>
                    >

                    <span class="filter-check"></span>

                    Pasto

                </label>

            </div>


        <!-- ==================================================
             ESTADO
        =================================================== -->

        <div class="filter-group">

            <h3>

                Estado

                <i class="fa-solid fa-chevron-down"></i>

            </h3>


            <label class="filter-option">


                <input
                    type="checkbox"
                    name="gym_estado[]"
                    value="activo"
                    <?= in_array(
                        'activo',
                        $gymEstados,
                        true
                    ) ? 'checked' : '' ?>
                >


                <span class="filter-check"></span>


                Activos


            </label>


        </div>



        <!-- ==================================================
             LIMPIAR FILTROS
        =================================================== -->

        <button
            type="button"
            class="clear-filters-button"
        >

            <i class="fa-solid fa-rotate-left"></i>

            Limpiar filtros

        </button>


    </aside>



    <!-- ==================================================
         CONTENIDO GIMNASIOS
    =================================================== -->

    <section class="products-content">


            <div class="products-toolbar">

                <div class="products-result-info">

                    <?php if ($buscar !== ''): ?>

                        <span class="products-result-label">

                            RESULTADOS PARA

                        </span>

                        <h2>

                            "<?= htmlspecialchars($buscar) ?>"

                        </h2>

                    <?php else: ?>

                        <span class="products-result-label">

                            BODYTECH

                        </span>

                        <h2>

                            Gimnasios disponibles

                        </h2>

                    <?php endif; ?>


                    <p>

                        <?= count($gimnasios) ?>

                        gimnasio(s) encontrado(s)

                    </p>

                </div>

            </div>



            <div class="products-grid">


                <?php if (!$gimnasios): ?>

                    <div class="products-empty">

                        <div class="products-empty-icon">

                            <i class="fa-solid fa-dumbbell"></i>

                        </div>


                        <h2>

                            No encontramos gimnasios

                        </h2>


                        <p>

                            Intenta realizar otra búsqueda.

                        </p>


                        <a
                            href="/bodytech/public/productos.php?modo=gimnasios"
                            class="products-empty-button"
                        >

                            <i class="fa-solid fa-arrow-rotate-left"></i>

                            Ver todos los gimnasios

                        </a>

                    </div>

                <?php endif; ?>



                <?php foreach ($gimnasios as $gym): ?>


                    <?php

                    $gymImgStmt = $pdo->prepare("
                        SELECT media_path
                        FROM gimnasio_media
                        WHERE gimnasio_id = ?
                        AND media_type = 'image'
                        LIMIT 1
                    ");

                    $gymImgStmt->execute([
                        $gym['id']
                    ]);

                    $gymImg = $gymImgStmt->fetchColumn();


                    $gymRatingStmt = $pdo->prepare("
                        SELECT
                            AVG(calificacion) AS promedio,
                            COUNT(*) AS total
                        FROM resenas_gimnasios
                        WHERE gimnasio_id = ?
                    ");

                    $gymRatingStmt->execute([
                        $gym['id']
                    ]);

                    $gymRating =
                        $gymRatingStmt->fetch(PDO::FETCH_ASSOC);


                    $gymPromedio =
                        round(
                            $gymRating['promedio'] ?? 0,
                            1
                        );


                    $gymTotal =
                        $gymRating['total'] ?? 0;

                    ?>


                    <article class="product-card">


                        <a
                            href="/bodytech/public/detalle_gimnasio.php?id=<?= (int)$gym['id'] ?>"
                            class="product-card-image"
                        >


                            <?php if ($gymImg): ?>

                                <img
                                    src="/bodytech/public/uploads/gimnasios/<?= htmlspecialchars($gymImg) ?>"
                                    alt="<?= htmlspecialchars($gym['nombre']) ?>"
                                >

                            <?php else: ?>

                                <div class="product-card-no-image">

                                    <i class="fa-solid fa-dumbbell"></i>

                                </div>

                            <?php endif; ?>


                            <span class="product-card-category">

                                Gimnasio

                            </span>


                        </a>



                        <div class="product-card-body">


                            <div class="product-rating">

                                <div class="product-stars">

                                    <?php

                                    $gymStars =
                                        floor($gymPromedio);

                                    for (
                                        $i = 1;
                                        $i <= 5;
                                        $i++
                                    ):

                                    ?>

                                        <?php if (
                                            $i <= $gymStars
                                        ): ?>

                                            <i class="fa-solid fa-star"></i>

                                        <?php else: ?>

                                            <i class="fa-regular fa-star"></i>

                                        <?php endif; ?>

                                    <?php endfor; ?>

                                </div>


                                <span>

                                    <?= $gymPromedio ?>

                                    ·

                                    <?= $gymTotal ?>

                                    opinión(es)

                                </span>

                            </div>



                            <h3 class="product-card-title">

                                <a
                                    href="/bodytech/public/detalle_gimnasio.php?id=<?= (int)$gym['id'] ?>"
                                >

                                    <?= htmlspecialchars(
                                        $gym['nombre']
                                    ) ?>

                                </a>

                            </h3>



                            <p class="product-card-description">

                                <?= htmlspecialchars(
                                    $gym['ciudad']
                                ) ?>

                                <?php if (!empty($gym['direccion'])): ?>

                                    ·

                                    <?= htmlspecialchars(
                                        $gym['direccion']
                                    ) ?>

                                <?php endif; ?>

                            </p>



                            <a
                                href="/bodytech/public/detalle_gimnasio.php?id=<?= (int)$gym['id'] ?>"
                                class="product-view-button"
                            >

                                Ver gimnasio

                                <i class="fa-solid fa-arrow-right"></i>

                            </a>


                        </div>


                    </article>


                <?php endforeach; ?>


            </div>


        </section>

    </div>

        <?php endif; ?>


    </div>


</main>


<script src="/bodytech/public/assets/js/productos.js"></script>

<?php require_once '../app/views/footer.php'; ?>


</body>

</html>