<?php
require_once __DIR__ . '/../admin_auth.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Reporte de estadísticas</title>

    <link
        rel="stylesheet"
        href="/bodytech/public/assets/css/admin_reportes.css?v=1"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >

</head>

<body>

<main class="reports-page">

    <!-- =====================================================
         HEADER
    ====================================================== -->

    <header class="reports-header">

        <div class="header-icon">
            <i class="fa-solid fa-file-excel"></i>
        </div>

            <div>

                <span class="header-kicker">
                    CENTRO DE REPORTES
                </span>

                <h1>
                    Reporte de estadísticas
                </h1>

                <p>
                    Genera un reporte detallado del rendimiento
                    financiero y comercial de la plataforma.
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
         SELECTOR DE PERÍODO
    ====================================================== -->

    <section class="report-card">

        <div class="section-heading">

            <div class="section-icon">
                <i class="fa-solid fa-calendar-days"></i>
            </div>

            <div>

                <h2>
                    Seleccionar período
                </h2>

                <p>
                    Indica las fechas que deseas incluir
                    en el reporte.
                </p>

            </div>

        </div>


        <form
            action="exportar_estadisticas.php"
            method="GET"
            class="date-form"
        >

            <div class="date-field">

                <label for="fecha_inicio">
                    Fecha inicial
                </label>

                <input
                    type="date"
                    id="fecha_inicio"
                    name="fecha_inicio"
                    required
                >

            </div>


            <div class="date-field">

                <label for="fecha_fin">
                    Fecha final
                </label>

                <input
                    type="date"
                    id="fecha_fin"
                    name="fecha_fin"
                    required
                >

            </div>


            <div class="form-actions">

                <button
                    type="submit"
                    class="generate-button"
                >

                    <i class="fa-solid fa-file-excel"></i>

                    Generar reporte Excel

                </button>

            </div>

        </form>

    </section>


    <!-- =====================================================
         INFORMACIÓN DEL REPORTE
    ====================================================== -->

    <section class="report-card">

        <div class="section-heading">

            <div class="section-icon">
                <i class="fa-solid fa-chart-pie"></i>
            </div>

            <div>

                <h2>
                    Información incluida
                </h2>

                <p>
                    El reporte consolidará la información del
                    período seleccionado.
                </p>

            </div>

        </div>


        <div class="report-options">

            <article class="report-option">

                <div class="option-icon blue">
                    <i class="fa-solid fa-store"></i>
                </div>

                <div>

                    <strong>
                        Marketplace
                    </strong>

                    <span>
                        Ventas, órdenes y pagos registrados.
                    </span>

                </div>

            </article>


            <article class="report-option">

                <div class="option-icon green">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>

                <div>

                    <strong>
                        Ingresos
                    </strong>

                    <span>
                        Pagos aprobados del Marketplace
                        y afiliaciones.
                    </span>

                </div>

            </article>


            <article class="report-option">

                <div class="option-icon purple">
                    <i class="fa-solid fa-box-open"></i>
                </div>

                <div>

                    <strong>
                        Productos vendidos
                    </strong>

                    <span>
                        Unidades, precios y subtotales
                        registrados.
                    </span>

                </div>

            </article>


            <article class="report-option">

                <div class="option-icon orange">
                    <i class="fa-solid fa-dumbbell"></i>
                </div>

                <div>

                    <strong>
                        Afiliaciones
                    </strong>

                    <span>
                        Membresías e ingresos de afiliación.
                    </span>

                </div>

            </article>

        </div>

    </section>


    <!-- =====================================================
         AVISO
    ====================================================== -->

    <div class="report-notice">

        <i class="fa-solid fa-circle-info"></i>

        <div>

            <strong>
                Reporte por período
            </strong>

            <p>
                La información será calculada únicamente
                con los registros correspondientes a las
                fechas seleccionadas.
            </p>

        </div>

    </div>


    <!-- =====================================================
         FOOTER
    ====================================================== -->

    <footer class="reports-footer">

        <a href="/bodytech/admin/index.php">

            <i class="fa-solid fa-arrow-left"></i>

            Volver al panel administrativo

        </a>

    </footer>

</main>

</body>
</html>