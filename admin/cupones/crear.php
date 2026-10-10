<?php
require_once __DIR__ . '/../admin_auth.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Crear Cupón</title>

    <link rel="stylesheet"
          href="/bodytech/public/assets/css/admin_crear_cupon.css?v=1">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>

<body>

<main class="admin-coupon-create-page">

    <!-- ======================================================
         HEADER
    ======================================================= -->

    <header class="page-header">

        <div class="header-content">

            <div class="header-title">

                <div class="header-icon">
                    <i class="fa-solid fa-ticket"></i>
                </div>

                <div>
                    <h1>Crear Cupón</h1>

                    <p>
                        Configura un nuevo código promocional para el marketplace.
                    </p>
                </div>

            </div>

            <a href="index.php" class="back-button">
                <i class="fa-solid fa-arrow-left"></i>
                Volver a cupones
            </a>

        </div>

    </header>


    <!-- ======================================================
         CONTENIDO
    ======================================================= -->

    <section class="content-wrapper">

        <div class="form-layout">


            <!-- ==================================================
                 FORMULARIO
            =================================================== -->

            <div class="form-card">

                <div class="card-heading">

                    <div class="heading-icon">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>

                    <div>
                        <h2>Información del cupón</h2>

                        <p>
                            Completa los datos que tendrá la promoción.
                        </p>
                    </div>

                </div>


                <form action="guardar.php" method="POST" class="coupon-form">


                    <!-- ==================================================
                         CÓDIGO
                    =================================================== -->

                    <div class="form-section">

                        <div class="section-label">

                            <i class="fa-solid fa-barcode"></i>

                            <span>Código promocional</span>

                        </div>


                        <div class="field">

                            <label for="codigo">
                                Código del cupón
                            </label>

                            <div class="input-wrapper">

                                <i class="fa-solid fa-ticket input-icon"></i>

                                <input
                                    type="text"
                                    id="codigo"
                                    name="codigo"
                                    required
                                    placeholder="Ej: BODYTECH10"
                                    autocomplete="off"
                                >

                            </div>

                            <span class="field-help">
                                Este será el código que utilizarán los clientes.
                            </span>

                        </div>

                    </div>


                    <!-- ==================================================
                         DESCUENTO
                    =================================================== -->

                    <div class="form-section">

                        <div class="section-label">

                            <i class="fa-solid fa-percent"></i>

                            <span>Configuración del descuento</span>

                        </div>


                        <div class="field-grid">


                            <div class="field">

                                <label for="tipo_descuento">
                                    Tipo de descuento
                                </label>

                                <div class="input-wrapper">

                                    <i class="fa-solid fa-tags input-icon"></i>

                                    <select
                                        id="tipo_descuento"
                                        name="tipo_descuento"
                                        required
                                    >

                                        <option value="porcentaje">
                                            Porcentaje (%)
                                        </option>

                                        <option value="fijo">
                                            Valor fijo ($)
                                        </option>

                                    </select>

                                </div>

                            </div>


                            <div class="field">

                                <label for="valor_descuento">
                                    Valor del descuento
                                </label>

                                <div class="input-wrapper">

                                    <i class="fa-solid fa-dollar-sign input-icon"></i>

                                    <input
                                        type="number"
                                        id="valor_descuento"
                                        step="0.01"
                                        name="valor_descuento"
                                        required
                                        placeholder="0.00"
                                    >

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- ==================================================
                         CONDICIÓN DE COMPRA
                    =================================================== -->

                    <div class="form-section">

                        <div class="section-label">

                            <i class="fa-solid fa-cart-shopping"></i>

                            <span>Condición de compra</span>

                        </div>


                        <div class="field">

                            <label for="monto_minimo">
                                Monto mínimo de compra
                            </label>

                            <div class="input-wrapper">

                                <i class="fa-solid fa-dollar-sign input-icon"></i>

                                <input
                                    type="number"
                                    id="monto_minimo"
                                    step="0.01"
                                    name="monto_minimo"
                                    value="0"
                                    placeholder="0.00"
                                >

                            </div>

                            <span class="field-help">
                                Déjalo en $0 si el cupón no requiere un mínimo de compra.
                            </span>

                        </div>

                    </div>


                    <!-- ==================================================
                         VIGENCIA
                    =================================================== -->

                    <div class="form-section">

                        <div class="section-label">

                            <i class="fa-regular fa-calendar"></i>

                            <span>Vigencia del cupón</span>

                        </div>


                        <div class="field-grid">


                            <div class="field">

                                <label for="fecha_inicio">
                                    Fecha de inicio
                                </label>

                                <div class="input-wrapper">

                                    <i class="fa-regular fa-calendar-check input-icon"></i>

                                    <input
                                        type="datetime-local"
                                        id="fecha_inicio"
                                        name="fecha_inicio"
                                        required
                                    >

                                </div>

                            </div>


                            <div class="field">

                                <label for="fecha_fin">
                                    Fecha de vencimiento
                                </label>

                                <div class="input-wrapper">

                                    <i class="fa-regular fa-calendar-xmark input-icon"></i>

                                    <input
                                        type="datetime-local"
                                        id="fecha_fin"
                                        name="fecha_fin"
                                        required
                                    >

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- ==================================================
                         USOS
                    =================================================== -->

                    <div class="form-section">

                        <div class="section-label">

                            <i class="fa-solid fa-rotate"></i>

                            <span>Límite de uso</span>

                        </div>


                        <div class="field">

                            <label for="usos_maximos">
                                Máximo de usos
                            </label>

                            <div class="input-wrapper">

                                <i class="fa-solid fa-hashtag input-icon"></i>

                                <input
                                    type="number"
                                    id="usos_maximos"
                                    name="usos_maximos"
                                    placeholder="Vacío = ilimitado"
                                >

                            </div>

                            <span class="field-help">
                                Puedes establecer un límite o dejarlo vacío para permitir usos ilimitados.
                            </span>

                        </div>

                    </div>


                    <!-- ==================================================
                         ACCIONES
                    =================================================== -->

                    <div class="form-actions">

                        <a href="index.php" class="cancel-button">
                            <i class="fa-solid fa-xmark"></i>
                            Cancelar
                        </a>

                        <button type="submit" class="save-button">

                            <i class="fa-solid fa-check"></i>

                            Guardar cupón

                        </button>

                    </div>


                </form>

            </div>


            <!-- ==================================================
                 PANEL INFORMATIVO
            =================================================== -->

            <aside class="info-card">

                <div class="info-icon">
                    <i class="fa-solid fa-circle-info"></i>
                </div>

                <h3>Configuración del cupón</h3>

                <p>
                    Revisa estos puntos antes de guardar la promoción.
                </p>


                <div class="info-item">

                    <div class="info-item-icon">
                        <i class="fa-solid fa-percent"></i>
                    </div>

                    <div>
                        <strong>Porcentaje o valor fijo</strong>

                        <span>
                            Define si el descuento será porcentual o tendrá un valor monetario.
                        </span>
                    </div>

                </div>


                <div class="info-item">

                    <div class="info-item-icon">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </div>

                    <div>
                        <strong>Monto mínimo</strong>

                        <span>
                            Establece el valor mínimo necesario para poder utilizar el cupón.
                        </span>
                    </div>

                </div>


                <div class="info-item">

                    <div class="info-item-icon">
                        <i class="fa-regular fa-calendar"></i>
                    </div>

                    <div>
                        <strong>Vigencia</strong>

                        <span>
                            El cupón estará disponible dentro del periodo configurado.
                        </span>
                    </div>

                </div>


                <div class="info-item">

                    <div class="info-item-icon">
                        <i class="fa-solid fa-hashtag"></i>
                    </div>

                    <div>
                        <strong>Límite de usos</strong>

                        <span>
                            Déjalo vacío si quieres permitir usos ilimitados.
                        </span>
                    </div>

                </div>

            </aside>


        </div>


        <!-- ==================================================
             FOOTER
        =================================================== -->

        <div class="page-footer">

            <a href="index.php">
                <i class="fa-solid fa-arrow-left"></i>
                Regresar a gestión de cupones
            </a>

        </div>

    </section>

</main>

</body>
</html>