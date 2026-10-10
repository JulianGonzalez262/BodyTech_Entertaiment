<?php

require_once '../app/views/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: /bodytech/public/login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

/* =========================
   OBTENER CARRITO
========================= */

$stmt = $pdo->prepare("
    SELECT 
        carrito_items.id AS item_id,
        carrito_items.cantidad,
        productos.id AS producto_id,
        productos.nombre,
        productos.precio
    FROM carrito
    INNER JOIN carrito_items 
        ON carrito.id = carrito_items.carrito_id
    INNER JOIN productos 
        ON carrito_items.producto_id = productos.id
    WHERE carrito.usuario_id = ?
");

$stmt->execute([$usuario_id]);

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$items) {
    die("Tu carrito está vacío.");
}

$total = 0;

foreach ($items as $i) {
    $total += ($i['precio'] * $i['cantidad']);
}

?>

<link rel="stylesheet" href="/bodytech/public/assets/css/checkout.css">

<main class="checkout-page">

    <div class="checkout-container">

        <!-- =========================================
             CABECERA
        ========================================== -->

        <header class="checkout-header">

            <a href="/bodytech/public/carrito.php" class="checkout-back">
                <span>←</span>
                Volver al carrito
            </a>

            <div class="checkout-title-row">

                <div class="checkout-title-icon">
                    🧾
                </div>

                <div>
                    <h1>Finalizar compra</h1>

                    <p>
                        Completa tus datos de entrega y selecciona el medio de pago.
                    </p>
                </div>

            </div>

        </header>


        <!-- =========================================
             CONTENIDO
        ========================================== -->

        <div class="checkout-layout">


            <!-- =====================================
                 COLUMNA FORMULARIO
            ====================================== -->

            <section class="checkout-form-column">

                <form
                    action="/bodytech/public/procesar_compra.php"
                    method="POST"
                    class="checkout-form"
                >


                    <!-- =================================
                         CUPÓN
                    ================================== -->

                    <section class="checkout-card coupon-section">

                        <div class="section-heading">

                            <div class="section-icon">
                                🏷️
                            </div>

                            <div>

                                <span class="section-eyebrow">
                                    AHORRA EN TU COMPRA
                                </span>

                                <h2>
                                    Cupón de descuento
                                </h2>

                                <p>
                                    ¿Tienes un código promocional?
                                </p>

                            </div>

                        </div>


                        <div class="coupon-form">

                            <input
                                type="text"
                                id="codigo_cupon"
                                name="codigo_cupon"
                                placeholder="Ej: BODYTECH10"
                                autocomplete="off"
                            >

                            <button
                                type="button"
                                onclick="aplicarCupon()"
                                class="coupon-button"
                            >
                                Aplicar cupón
                            </button>

                        </div>

                        <p
                            id="mensaje_cupon"
                            class="coupon-message"
                        ></p>

                    </section>



                    <!-- =================================
                         DATOS DE ENVÍO
                    ================================== -->

                    <section class="checkout-card">

                        <div class="section-heading">

                            <div class="section-icon">
                                📦
                            </div>

                            <div>

                                <span class="section-eyebrow">
                                    ENTREGA
                                </span>

                                <h2>
                                    Datos de envío
                                </h2>

                                <p>
                                    Indica dónde debemos entregar tu pedido.
                                </p>

                            </div>

                        </div>


                        <div class="form-grid">


                            <!-- NOMBRE -->

                            <div class="form-group">

                                <label for="receptor_nombre">
                                    Nombre de quien recibe
                                </label>

                                <div class="input-wrapper">

                                    <span class="input-icon">
                                        👤
                                    </span>

                                    <input
                                        type="text"
                                        id="receptor_nombre"
                                        name="receptor_nombre"
                                        placeholder="Nombre completo"
                                        required
                                    >

                                </div>

                            </div>


                            <!-- TELÉFONO -->

                            <div class="form-group">

                                <label for="receptor_telefono">
                                    Teléfono
                                </label>

                                <div class="input-wrapper">

                                    <span class="input-icon">
                                        📞
                                    </span>

                                    <input
                                        type="text"
                                        id="receptor_telefono"
                                        name="receptor_telefono"
                                        placeholder="Número de contacto"
                                        required
                                    >

                                </div>

                            </div>


                            <!-- DEPARTAMENTO -->

                            <div class="form-group">

                                <label for="departamento">
                                    Departamento
                                </label>

                                <div class="input-wrapper">

                                    <span class="input-icon">
                                        🗺️
                                    </span>

                                    <input
                                        type="text"
                                        id="departamento"
                                        name="departamento"
                                        placeholder="Departamento"
                                        required
                                    >

                                </div>

                            </div>


                            <!-- CIUDAD -->

                            <div class="form-group">

                                <label for="ciudad">
                                    Ciudad
                                </label>

                                <div class="input-wrapper">

                                    <span class="input-icon">
                                        📍
                                    </span>

                                    <input
                                        type="text"
                                        id="ciudad"
                                        name="ciudad"
                                        placeholder="Ciudad"
                                        required
                                    >

                                </div>

                            </div>


                            <!-- DIRECCIÓN -->

                            <div class="form-group form-group-full">

                                <label for="direccion_envio">
                                    Dirección
                                </label>

                                <div class="input-wrapper">

                                    <span class="input-icon">
                                        🏠
                                    </span>

                                    <input
                                        type="text"
                                        id="direccion_envio"
                                        name="direccion_envio"
                                        placeholder="Dirección exacta de entrega"
                                        required
                                    >

                                </div>

                            </div>


                            <!-- REFERENCIA -->

                            <div class="form-group form-group-full">

                                <label for="referencia_direccion">
                                    Referencia adicional
                                    <span>(opcional)</span>
                                </label>

                                <div class="input-wrapper">

                                    <span class="input-icon">
                                        ◈
                                    </span>

                                    <input
                                        type="text"
                                        id="referencia_direccion"
                                        name="referencia_direccion"
                                        placeholder="Referencia (casa azul, apto 302...)"
                                    >

                                </div>

                            </div>


                            <!-- NOTAS -->

                            <div class="form-group form-group-full">

                                <label for="notas_pedido">
                                    Notas para el domiciliario
                                    <span>(opcional)</span>
                                </label>

                                <div class="textarea-wrapper">

                                    <span class="textarea-icon">
                                        📝
                                    </span>

                                    <textarea
                                        id="notas_pedido"
                                        name="notas_pedido"
                                        placeholder="Notas adicionales..."
                                    ></textarea>

                                </div>

                            </div>

                        </div>


                        <!-- CONFIRMACIÓN -->

                        <label class="checkbox-row">

                            <input
                                type="checkbox"
                                name="direccion_confirmada"
                                value="1"
                                required
                            >

                            <span class="custom-checkbox"></span>

                            <span class="checkbox-text">
                                Confirmo que la dirección ingresada es correcta.
                            </span>

                        </label>

                    </section>



                    <!-- =================================
                         MÉTODO DE PAGO
                    ================================== -->

                    <section class="checkout-card">

                        <div class="section-heading">

                            <div class="section-icon">
                                💳
                            </div>

                            <div>

                                <span class="section-eyebrow">
                                    PAGO SEGURO
                                </span>

                                <h2>
                                    Método de pago
                                </h2>

                                <p>
                                    Selecciona cómo deseas pagar tu pedido.
                                </p>

                            </div>

                        </div>


                        <div class="form-group">

                            <label for="metodo_pago">
                                Medio de pago
                            </label>

                            <div class="select-wrapper">

                                <span class="select-icon">
                                    💳
                                </span>

                                <select
                                    name="metodo_pago"
                                    id="metodo_pago"
                                    required
                                    onchange="mostrarCamposPago()"
                                >

                                    <option value="">
                                        Seleccione
                                    </option>

                                    <option value="tarjeta">
                                        💳 Tarjeta Crédito / Débito
                                    </option>

                                    <option value="pse">
                                        🏦 PSE Transferencia Bancaria
                                    </option>

                                    <option value="efectivo">
                                        💵 Pago Presencial
                                    </option>

                                </select>

                            </div>

                        </div>



                        <!-- =================================
                             TARJETA
                        ================================== -->

                        <div
                            id="campos_tarjeta"
                            class="dynamic-payment-box"
                            style="display:none;"
                        >

                            <div class="payment-box-header">

                                <div class="payment-box-icon">
                                    💳
                                </div>

                                <div>

                                    <h3>
                                        Datos de tarjeta
                                    </h3>

                                    <p>
                                        Ingresa los datos de tu tarjeta.
                                    </p>

                                </div>

                            </div>


                            <div class="form-grid">

                                <div class="form-group form-group-full">

                                    <label for="numero_tarjeta">
                                        Número de tarjeta
                                    </label>

                                    <input
                                        type="text"
                                        id="numero_tarjeta"
                                        name="numero_tarjeta"
                                        placeholder="Número de tarjeta"
                                    >

                                </div>


                                <div class="form-group">

                                    <label for="titular">
                                        Nombre del titular
                                    </label>

                                    <input
                                        type="text"
                                        id="titular"
                                        name="titular"
                                        placeholder="Nombre del titular"
                                    >

                                </div>


                                <div class="form-group">

                                    <label for="vencimiento">
                                        Fecha de vencimiento
                                    </label>

                                    <input
                                        type="month"
                                        id="vencimiento"
                                        name="vencimiento"
                                    >

                                </div>


                                <div class="form-group">

                                    <label for="cvv">
                                        CVV
                                    </label>

                                    <input
                                        type="password"
                                        id="cvv"
                                        name="cvv"
                                        placeholder="CVV"
                                    >

                                </div>

                            </div>

                        </div>



                        <!-- =================================
                             PSE
                        ================================== -->

                        <div
                            id="campos_pse"
                            class="dynamic-payment-box"
                            style="display:none;"
                        >

                            <div class="payment-box-header">

                                <div class="payment-box-icon">
                                    🏦
                                </div>

                                <div>

                                    <h3>
                                        Pago por PSE
                                    </h3>

                                    <p>
                                        Completa los datos para realizar la transferencia.
                                    </p>

                                </div>

                            </div>


                            <div class="form-grid">

                                <div class="form-group">

                                    <label for="titular_pse">
                                        Nombre completo
                                    </label>

                                    <input
                                        type="text"
                                        id="titular_pse"
                                        name="titular_pse"
                                        placeholder="Nombre completo"
                                    >

                                </div>


                                <div class="form-group">

                                    <label for="documento_pse">
                                        Documento
                                    </label>

                                    <input
                                        type="text"
                                        id="documento_pse"
                                        name="documento_pse"
                                        placeholder="Número de documento"
                                    >

                                </div>


                                <div class="form-group form-group-full">

                                    <label for="banco_pse">
                                        Banco
                                    </label>

                                    <select
                                        name="banco_pse"
                                        id="banco_pse"
                                    >

                                        <option value="">
                                            Seleccione banco
                                        </option>

                                        <option>
                                            Bancolombia
                                        </option>

                                        <option>
                                            BBVA
                                        </option>

                                        <option>
                                            Banco de Bogotá
                                        </option>

                                    </select>

                                </div>

                            </div>

                        </div>



                        <!-- =================================
                             EFECTIVO
                        ================================== -->

                        <div
                            id="campos_efectivo"
                            class="dynamic-payment-box cash-payment-box"
                            style="display:none;"
                        >

                            <div class="payment-box-header">

                                <div class="payment-box-icon">
                                    💵
                                </div>

                                <div>

                                    <h3>
                                        Pago presencial
                                    </h3>

                                    <p>
                                        Se generará una referencia de pago presencial.
                                    </p>

                                </div>

                            </div>

                        </div>

                    </section>



                    <!-- =================================
                         BOTÓN
                    ================================== -->

                    <button
                        type="submit"
                        class="checkout-submit"
                    >

                        <span class="submit-icon">
                            ✓
                        </span>

                        <span>
                            Confirmar Compra
                        </span>

                        <span class="submit-arrow">
                            →
                        </span>

                    </button>


                    <div class="checkout-protection">

                        <span>
                            🔒
                        </span>

                        <span>
                            Tu información se procesa de forma segura.
                        </span>

                    </div>

                </form>

            </section>



            <!-- =====================================
                 RESUMEN DEL PEDIDO
            ====================================== -->

            <aside class="order-summary">

                <div class="summary-header">

                    <span class="summary-eyebrow">
                        TU PEDIDO
                    </span>

                    <h2>
                        Resumen
                    </h2>

                </div>


                <div class="summary-items">

                    <?php foreach ($items as $item): ?>

                        <div class="summary-item">

                            <div class="summary-product-icon">
                                🛍️
                            </div>

                            <div class="summary-product-info">

                                <strong>
                                    <?= htmlspecialchars($item['nombre']) ?>
                                </strong>

                                <span>
                                    Cantidad: <?= $item['cantidad'] ?>
                                </span>

                            </div>

                            <div class="summary-product-price">

                                $
                                <?= number_format(
                                    $item['precio'] * $item['cantidad'],
                                    2
                                ) ?>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>


                <div class="summary-divider"></div>


                <div class="summary-totals">

                    <div class="summary-row">

                        <span>
                            Subtotal
                        </span>

                        <strong>
                            $
                            <span id="subtotal">
                                <?= number_format($total, 2) ?>
                            </span>
                        </strong>

                    </div>


                    <div class="summary-row discount-row">

                        <span>
                            Descuento
                        </span>

                        <strong>
                            -$
                            <span id="descuento">
                                0.00
                            </span>
                        </strong>

                    </div>

                </div>


                <div class="summary-total">

                    <span>
                        Total final
                    </span>

                    <strong>
                        $
                        <span id="total_final">
                            <?= number_format($total, 2) ?>
                        </span>
                    </strong>

                </div>


                <div class="summary-security">

                    <span class="security-icon">
                        🛡️
                    </span>

                    <div>

                        <strong>
                            Compra protegida
                        </strong>

                        <span>
                            Pago y datos procesados de forma segura.
                        </span>

                    </div>

                </div>

            </aside>

        </div>

    </div>

</main>


<script src="/bodytech/public/assets/js/compraar_todo.js"></script>

<?php require_once '../app/views/footer.php'; ?>