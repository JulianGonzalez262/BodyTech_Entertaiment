<?php
require_once '../app/views/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: /bodytech/public/login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

/* =========================
   OBTENER PRODUCTOS DEL CARRITO
========================= */
$stmt = $pdo->prepare("
    SELECT 
        carrito_items.id AS item_id,
        carrito_items.cantidad,
        productos.id AS producto_id,
        productos.nombre,
        productos.precio,
        productos.stock,
        (
            SELECT media_path 
            FROM producto_media 
            WHERE producto_id = productos.id 
            AND media_type = 'image'
            LIMIT 1
        ) AS imagen
    FROM carrito
    INNER JOIN carrito_items ON carrito.id = carrito_items.carrito_id
    INNER JOIN productos ON carrito_items.producto_id = productos.id
    WHERE carrito.usuario_id = ?
");
$stmt->execute([$usuario_id]);

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/bodytech/public/assets/css/carrito.css">
</head>
<body>
<main class="cart-page">

    <!-- ==========================================================
         FONDO
    =========================================================== -->

    <div class="cart-background">

        <img
            src="/bodytech/public/assets/img/Sino.png"
            alt="Background">

    </div>

    <div class="cart-overlay"></div>


    <!-- ==========================================================
         CONTENEDOR PRINCIPAL
    =========================================================== -->

    <section class="cart-card">

        <!-- ======================================================
             HEADER
        ======================================================= -->

        <header class="cart-header">

            <div class="cart-logo">

                <i class="fa-solid fa-cart-shopping"></i>

            </div>

            <h1>
                BODYTECH
            </h1>

            <span class="cart-brand">
                Marketplace Fitness
            </span>

            <h2>
                Mi Carrito de Compras
            </h2>

            <p>
                Revisa tus productos, verifica las cantidades y
                confirma tu compra cuando estés listo.
            </p>

        </header>


        <?php if (!$items): ?>

            <!-- ==================================================
                 CARRITO VACÍO
            =================================================== -->

            <section class="cart-empty">

                <div class="cart-empty-icon">

                    <i class="fa-solid fa-cart-shopping"></i>

                </div>

                <h3>
                    Tu carrito está vacío
                </h3>

                <p>
                    Aún no has agregado productos a tu carrito.
                    Explora nuestro marketplace y encuentra lo que necesitas.
                </p>

                <a
                    href="/bodytech/public/productos.php"
                    class="cart-primary-btn">

                    <i class="fa-solid fa-store"></i>

                    Explorar productos

                </a>

            </section>


        <?php else: ?>


            <!-- ==================================================
                 CONTENIDO DEL CARRITO
            =================================================== -->

            <div class="cart-layout">


                <!-- =================================================
                     PRODUCTOS
                ================================================== -->

                <section class="cart-products">

                    <div class="cart-section-header">

                        <div>

                            <h3>
                                Productos seleccionados
                            </h3>

                            <p>
                                <?= count($items) ?>
                                producto(s) en tu carrito.
                            </p>

                        </div>

                    </div>


                    <?php foreach ($items as $item):

                        $subtotal = $item['precio'] * $item['cantidad'];

                        $total += $subtotal;

                    ?>

                       <article class="cart-product">

                            <!-- ======================================================
                                PRODUCTO CLICKEABLE
                            ======================================================= -->

                            <a
                                href="/bodytech/public/producto.php?id=<?= (int)$item['producto_id'] ?>"
                                class="cart-product-link">

                                <!-- Imagen -->

                                <div class="cart-product-image">

                                    <img
                                        src="/bodytech/public/uploads/productos/<?= htmlspecialchars($item['imagen']) ?>"
                                        alt="<?= htmlspecialchars($item['nombre']) ?>">

                                </div>


                                <!-- Información -->

                                <div class="cart-product-info">

                                    <h3>
                                        <?= htmlspecialchars($item['nombre']) ?>
                                    </h3>

                                    <span class="cart-product-price">

                                        $<?= number_format($item['precio'], 2) ?>

                                    </span>

                                    <div class="cart-product-details">

                                        <span>

                                            Cantidad:

                                            <strong>
                                                <?= $item['cantidad'] ?>
                                            </strong>

                                        </span>

                                        <span>

                                            Subtotal:

                                            <strong>
                                                $<?= number_format($subtotal, 2) ?>
                                            </strong>

                                        </span>

                                    </div>

                                </div>

                            </a>


                            <!-- ======================================================
                                ELIMINAR
                            ======================================================= -->

                            <form
                                action="/bodytech/public/eliminar_carrito_item.php"
                                method="POST"
                                class="cart-remove-form">

                                <input
                                    type="hidden"
                                    name="item_id"
                                    value="<?= $item['item_id'] ?>">

                                <button
                                    type="submit"
                                    class="cart-remove-btn">

                                    <i class="fa-solid fa-trash"></i>

                                    <span>
                                        Eliminar
                                    </span>

                                </button>

                            </form>

                        </article>

                    <?php endforeach; ?>

                </section>


                <!-- =================================================
                     RESUMEN
                ================================================== -->

                <aside class="cart-summary">

                    <div class="cart-summary-icon">

                        <i class="fa-solid fa-receipt"></i>

                    </div>

                    <h3>
                        Resumen de compra
                    </h3>

                    <div class="cart-summary-row">

                        <span>
                            Productos
                        </span>

                        <strong>
                            <?= count($items) ?>
                        </strong>

                    </div>

                    <div class="cart-summary-divider"></div>

                    <div class="cart-summary-total">

                        <span>
                            Total
                        </span>

                        <strong>
                            $<?= number_format($total, 2) ?>
                        </strong>

                    </div>

                    <a
                        href="/bodytech/public/comprar_todo.php"
                        class="cart-buy-btn">

                        <i class="fa-solid fa-credit-card"></i>

                        Comprar todo

                    </a>

                    <p class="cart-summary-security">

                        <i class="fa-solid fa-lock"></i>

                        Compra segura y protegida

                    </p>

                </aside>

            </div>


        <?php endif; ?>


        <!-- ==========================================================
             VOLVER
        =========================================================== -->

        <div class="cart-back">

            <a href="/bodytech/public/index.php">

                <i class="fa-solid fa-arrow-left"></i>

                Seguir comprando

            </a>

        </div>


    </section>

</main>
<script src="/bodytech/public/assets/js/carrillo.js"></script>
</body>
</html>

<?php require_once '../app/views/footer.php'; ?>