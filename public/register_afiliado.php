<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/views/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/sedes_oficiales.php';

/** @var array<string, array<string, array<int, string>>> $SEDES_OFICIALES */

$gymNombrePreseleccionado = null;

if (isset($_GET['gym']) && is_numeric($_GET['gym'])) {

    $stmt = $pdo->prepare("
        SELECT nombre
        FROM gimnasios
        WHERE id = ?
        AND activo = 1
    ");

    $stmt->execute([$_GET['gym']]);
    $gym = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($gym) {
        $gymNombrePreseleccionado = $gym['nombre'];
    }
}

/* =========================
   CONFIGURACIÓN PARA JAVASCRIPT
========================= */

$afiliadoConfig = [
    'sedes' => $SEDES_OFICIALES,
    'gym'   => $gymNombrePreseleccionado
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro Afiliado - Paso 1</title>

    <link
        rel="stylesheet"
        href="/bodytech/public/assets/css/register_afiliado_paso1.css"
    >
</head>

<body>

<main class="afiliacion-page">

    <!-- Video de fondo -->

    <video
        class="background-video"
        autoplay
        muted
        loop
        playsinline
    >

        <source
            src="/bodytech/public/assets/videos/hero.mp4"
            type="video/mp4"
        >

    </video>

    <div class="video-overlay"></div>

    <section class="afiliacion-card">

        <!-- Cabecera -->

        <header class="afiliacion-header">

            <div class="logo-circle">

                <i class="fa-solid fa-dumbbell"></i>

            </div>

            <h1>BODYTECH</h1>

            <span class="brand">
                Marketplace Fitness
            </span>

            <h2>
                Registro de Afiliación
            </h2>

            <p>
                Paso 1 de 5 · Selecciona el gimnasio donde deseas iniciar tu afiliación.
            </p>

        </header>

        <!-- Indicador -->

        <div class="step-progress">

            <div class="step active">1</div>

            <div class="line"></div>

            <div class="step">2</div>

            <div class="line"></div>

            <div class="step">3</div>

            <div class="line"></div>

            <div class="step">4</div>

            <div class="line"></div>

            <div class="step">5</div>

        </div>

        <!-- Formulario -->

        <form
            class="afiliacion-form"
            method="POST"
            action="register_afiliado_paso2.php"
        >

            <!-- Departamento -->

            <div class="form-group">

                <label
                    class="form-label"
                    for="departamento"
                >
                    Departamento
                </label>

                <div class="input-container">

                    <i class="fa-solid fa-map-location-dot input-icon"></i>

                    <select
                        id="departamento"
                        name="departamento"
                        class="input-field"
                        required
                    >

                        <option value="">
                            -- Seleccione un departamento --
                        </option>

                    </select>

                    <i class="fa-solid fa-chevron-down select-arrow"></i>

                </div>

            </div>

            <!-- Ciudad -->

            <div class="form-group">

                <label
                    class="form-label"
                    for="ciudad"
                >
                    Ciudad
                </label>

                <div class="input-container">

                    <i class="fa-solid fa-city input-icon"></i>

                    <select
                        id="ciudad"
                        name="ciudad"
                        class="input-field"
                        required
                        disabled
                    >

                        <option value="">
                            -- Seleccione una ciudad --
                        </option>

                    </select>

                    <i class="fa-solid fa-chevron-down select-arrow"></i>

                </div>

            </div>

            <!-- Gym -->

            <div class="form-group">

                <label
                    class="form-label"
                    for="gym_nombre"
                >
                    Gym BodyTech
                </label>

                <div class="input-container">

                    <i class="fa-solid fa-dumbbell input-icon"></i>

                    <select
                        id="gym_nombre"
                        name="gym_nombre"
                        class="input-field"
                        required
                        disabled
                    >

                        <option value="">
                            -- Seleccione un gym --
                        </option>

                    </select>

                    <i class="fa-solid fa-chevron-down select-arrow"></i>

                </div>

            </div>

            <button
                type="submit"
                class="next-btn"
            >

                <i class="fa-solid fa-arrow-right"></i>

                Continuar al paso 2

            </button>

        </form>

        <div class="back-home">

            <a href="/bodytech/public/index.php">

                <i class="fa-solid fa-arrow-left"></i>

                Volver al inicio

            </a>

        </div>

    </section>

</main>

<!--
    Datos PHP disponibles para el JavaScript externo.
    El JS sigue siendo completamente independiente.
-->
<script
    id="afiliado-config"
    type="application/json"
>
<?= json_encode(
    $afiliadoConfig,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
) ?>
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/views/footer.php'; ?>

<script src="/bodytech/public/assets/js/registro_afiliado22.js"></script>

</body>
</html>