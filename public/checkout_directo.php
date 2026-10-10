<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: /bodytech/public/login.php");
    exit;
}

if (!isset($_SESSION['compra_directa']) || empty($_SESSION['compra_directa'])) {
    die("No hay compra directa activa.");
}

$items = $_SESSION['compra_directa'];

$total = 0;

foreach ($items as $i) {
    $total += ($i['precio'] * $i['cantidad']);
}

require_once '../app/views/header.php';

?>

<link rel="stylesheet" href="/bodytech/public/assets/css/checkout_directo.css">

<main class="checkout-page">

    <div class="checkout-container">

        <!-- =========================
             ENCABEZADO
        ========================== -->

        <div class="checkout-header">

            <a
                href="/bodytech/public/index.php"
                class="checkout-back"
            >
                <i class="fa-solid fa-arrow-left"></i>
                Volver a la tienda
            </a>

            <div class="checkout-title-icon">
                <i class="fa-solid fa-bolt"></i>
            </div>

            <h1>Compra rápida</h1>

            <p>
                Completa tus datos para finalizar tu compra.
            </p>

        </div>


        <div class="checkout-layout">

            <!-- =========================
                 COLUMNA PRINCIPAL
            ========================== -->

            <div class="checkout-main">


                <!-- =========================
                     CUPÓN
                ========================== -->

                <section class="checkout-card coupon-card">

                    <div class="section-icon">
                        <i class="fa-solid fa-tag"></i>
                    </div>

                    <div class="section-heading">

                        <h2>Aplicar cupón</h2>

                        <p>
                            ¿Tienes un código promocional?
                        </p>

                    </div>

                    <div class="coupon-form">

                        <div class="input-wrapper">

                            <input
                                type="text"
                                id="codigo_cupon"
                                name="codigo_cupon"
                                placeholder="Ingresa tu código"
                                autocomplete="off"
                            >

                        </div>

                        <button
                            type="button"
                            class="coupon-button"
                            onclick="aplicarCupon()"
                        >
                            Aplicar
                        </button>

                    </div>

                    <p
                        id="mensaje_cupon"
                        class="coupon-message"
                    ></p>

                </section>


                <!-- =========================
                     DATOS DE ENTREGA
                ========================== -->

                <section class="checkout-card">

                    <div class="section-icon">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>

                    <div class="section-heading">

                        <h2>Datos de entrega</h2>

                        <p>
                            Indica dónde debemos entregar tu pedido.
                        </p>

                    </div>


                    <form
                        action="/bodytech/public/procesar_compra.php"
                        method="POST"
                        id="checkout-form"
                    >

                        <!-- =========================
                             DATOS PERSONALES / ENVÍO
                        ========================== -->

                        <div class="form-grid">

                            <div class="form-group form-group-full">

                                <label for="receptor_nombre">
                                    <i class="fa-solid fa-user"></i>
                                    Nombre de quien recibe
                                </label>

                                <input
                                    type="text"
                                    id="receptor_nombre"
                                    name="receptor_nombre"
                                    placeholder="Nombre completo"
                                    required
                                >

                            </div>


                            <div class="form-group">

                                <label for="receptor_telefono">
                                    <i class="fa-solid fa-phone"></i>
                                    Teléfono
                                </label>

                                <input
                                    type="text"
                                    id="receptor_telefono"
                                    name="receptor_telefono"
                                    placeholder="Número de contacto"
                                    required
                                >

                            </div>


                            <div class="form-group">

                                <label for="departamento">
                                    <i class="fa-solid fa-map"></i>
                                    Departamento
                                </label>

                                <input
                                    type="text"
                                    id="departamento"
                                    name="departamento"
                                    placeholder="Departamento"
                                    required
                                >

                            </div>


                            <div class="form-group">

                                <label for="ciudad">
                                    <i class="fa-solid fa-city"></i>
                                    Ciudad
                                </label>

                                <input
                                    type="text"
                                    id="ciudad"
                                    name="ciudad"
                                    placeholder="Ciudad"
                                    required
                                >

                            </div>


                            <div class="form-group form-group-full">

                                <label for="direccion_envio">
                                    <i class="fa-solid fa-house"></i>
                                    Dirección
                                </label>

                                <input
                                    type="text"
                                    id="direccion_envio"
                                    name="direccion_envio"
                                    placeholder="Dirección exacta de entrega"
                                    required
                                >

                            </div>


                            <div class="form-group form-group-full">

                                <label for="referencia_direccion">
                                    <i class="fa-solid fa-location-crosshairs"></i>
                                    Referencia adicional
                                    <span>(opcional)</span>
                                </label>

                                <input
                                    type="text"
                                    id="referencia_direccion"
                                    name="referencia_direccion"
                                    placeholder="Ej. cerca al parque..."
                                >

                            </div>


                            <div class="form-group form-group-full">

                                <label for="notas_pedido">
                                    <i class="fa-solid fa-note-sticky"></i>
                                    Notas para el domiciliario
                                    <span>(opcional)</span>
                                </label>

                                <textarea
                                    id="notas_pedido"
                                    name="notas_pedido"
                                    placeholder="Escribe alguna indicación adicional..."
                                ></textarea>

                            </div>

                        </div>


                        <label class="confirmation-check">

                            <input
                                type="checkbox"
                                name="direccion_confirmada"
                                required
                            >

                            <span>
                                Confirmo que los datos y la dirección de entrega son correctos.
                            </span>

                        </label>


                        <!-- =========================
                             MÉTODO DE PAGO
                        ========================== -->

                        <section class="payment-section">

                            <div class="section-icon">
                                <i class="fa-solid fa-credit-card"></i>
                            </div>

                            <div class="section-heading">

                                <h2>Método de pago</h2>

                                <p>
                                    Selecciona cómo deseas pagar tu pedido.
                                </p>

                            </div>


                            <div class="form-group form-group-full">

                                <label for="metodo_pago">
                                    Medio de pago
                                </label>

                                <select
                                    name="metodo_pago"
                                    id="metodo_pago"
                                    required
                                    onchange="mostrarCamposPago()"
                                >

                                    <option value="">
                                        Selecciona un método
                                    </option>

                                    <option value="tarjeta">
                                        💳 Tarjeta Crédito / Débito
                                    </option>

                                    <option value="pse">
                                        🏦 PSE Transferencia Bancaria
                                    </option>

                                    <option value="efectivo">
                                        💵 Pago Presencial (Efecty / Baloto)
                                    </option>

                                </select>

                            </div>


                            <!-- =========================
                                 TARJETA
                            ========================== -->

                            <div
                                id="campos_tarjeta"
                                class="payment-fields"
                                style="display:none;"
                            >

                                <div class="payment-fields-header">

                                    <div class="payment-fields-icon">
                                        <i class="fa-solid fa-credit-card"></i>
                                    </div>

                                    <div>
                                        <h3>Datos de tarjeta</h3>
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


                                    <div class="form-group form-group-full">

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
                                            Vencimiento
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


                            <!-- =========================
                                 PSE
                            ========================== -->

                            <div
                                id="campos_pse"
                                class="payment-fields"
                                style="display:none;"
                            >

                                <div class="payment-fields-header">

                                    <div class="payment-fields-icon">
                                        <i class="fa-solid fa-building-columns"></i>
                                    </div>

                                    <div>
                                        <h3>Pago por PSE</h3>
                                        <p>
                                            Completa los datos para continuar.
                                        </p>
                                    </div>

                                </div>


                                <div class="form-grid">

                                    <div class="form-group form-group-full">

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


                                    <div class="form-group form-group-full">

                                        <label for="documento_pse">
                                            Número de documento
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


                            <!-- =========================
                                 EFECTIVO
                            ========================== -->

                            <div
                                id="campos_efectivo"
                                class="payment-fields cash-payment"
                                style="display:none;"
                            >

                                <div class="payment-fields-icon">
                                    <i class="fa-solid fa-money-bill-wave"></i>
                                </div>

                                <div>

                                    <h3>Pago presencial</h3>

                                    <p>
                                        Se generará una referencia para pago presencial.
                                    </p>

                                </div>

                            </div>


                            <!-- =========================
                                 BOTÓN
                            ========================== -->

                            <button
                                type="submit"
                                class="checkout-submit"
                            >
                                <span>
                                    <i class="fa-solid fa-lock"></i>
                                </span>

                                <span>
                                    Confirmar compra rápida
                                </span>

                                <i class="fa-solid fa-arrow-right"></i>
                            </button>

                        </section>

                    </form>

                </section>

            </div>


            <!-- =========================
                 RESUMEN LATERAL
            ========================== -->

            <aside class="checkout-summary">

                <div class="summary-card">

                    <div class="summary-label">
                        TU PEDIDO
                    </div>

                    <h2>
                        Resumen
                    </h2>


                    <div class="summary-products">

                        <?php foreach ($items as $item): ?>

                            <div class="summary-product">

                                <div class="summary-product-info">

                                    <span class="summary-product-name">
                                        <?= htmlspecialchars($item['nombre']) ?>
                                    </span>

                                    <span class="summary-product-quantity">
                                        Cantidad: <?= (int) $item['cantidad'] ?>
                                    </span>

                                </div>

                                <strong class="summary-product-price">
                                    $<?= number_format(
                                        $item['precio'] * $item['cantidad'],
                                        2
                                    ) ?>
                                </strong>

                            </div>

                        <?php endforeach; ?>

                    </div>


                    <div class="summary-divider"></div>


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


                    <div class="summary-row summary-discount">

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


                    <div class="summary-divider"></div>


                    <div class="summary-total">

                        <span>
                            Total
                        </span>

                        <strong>
                            $
                            <span id="total_final">
                                <?= number_format($total, 2) ?>
                            </span>
                        </strong>

                    </div>


                    <div class="summary-security">

                        <i class="fa-solid fa-shield-halved"></i>

                        <span>
                            Compra protegida y segura
                        </span>

                    </div>

                </div>

            </aside>

        </div>

    </div>

</main>


<script src="/bodytech/public/assets/js/cheecks.js"></script>

<?php require_once '../app/views/footer.php'; ?>