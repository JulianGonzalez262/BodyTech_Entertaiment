<?php 
require_once '../app/views/header.php'; 
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';
?>

<main class="hero-section">

    <!-- Video principal -->
    <video class="hero-video" autoplay muted loop playsinline>

        <source
            src="/bodytech/public/assets/videos/hero.mp4"
            type="video/mp4"
        >

    </video>

    <!-- Capa oscura para mejorar la lectura -->
    <div class="hero-overlay"></div>

    <div class="hero-content">

        <span class="hero-badge">
           <a href="/bodytech/public/index.php" class="logo-link">

        <img
            src="/bodytech/public/assets/img/logi.png"
            alt="BodyTech"
            class="logo-image"
        >

    </a>
        </span>

        <h1 class="hero-title">

        El Arte

        <span class="hero-title-middle">
         ― De ―
        </span>

        Superarte.

    </h1>
        <p class="hero-description">
            Descubre suplementos, equipamiento y las mejores sedes deportivas
            en un solo lugar.
        </p>

        <form
            action="/bodytech/public/productos.php"
            method="GET"
            class="hero-search-form"
        >

            <input
                type="text"
                name="buscar"
                class="hero-search-input"
                placeholder="Buscar productos o gimnasios..."
            >

            <button
                type="submit"
                class="hero-search-button"
            >
                🔍 Buscar
            </button>

        </form>

    </div>

</main>

<!-- 🛒 PRODUCTOS -->
<section class="products-section">

    <div class="section-header reveal-section">

    <h2 class="section-title reveal-item">
        Productos destacados
    </h2>

    <p class="section-subtitle reveal-item">
        Descubre los suplementos, accesorios y equipamiento más recientes de la comunidad BodyTech.
    </p>

</div>
    <?php
    $stmt = $pdo->query("
        SELECT *
        FROM productos
        WHERE activo = 1
        ORDER BY created_at DESC
        LIMIT 10
    ");

    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $ultimoProductoId = $productos[0]['id'] ?? 0;

    echo '
    <div
        id="lista-productos"
        class="products-carousel"
        data-last-update="'.$ultimoProductoId.'"
    >';

    if(count($productos) === 0){
        echo '
        <p class="empty-products">
            No hay productos disponibles.
        </p>';
    }

    foreach($productos as $producto){

        $mediaStmt = $pdo->prepare("
            SELECT media_path, media_type
            FROM producto_media
            WHERE producto_id = ?
        ");

        $mediaStmt->execute([$producto['id']]);
        $medias = $mediaStmt->fetchAll();

        ?>

        <a
            href="/bodytech/public/producto.php?id=<?= $producto['id'] ?>"
            class="product-link"
        >

            <article
                id="prod-<?= $producto['id'] ?>"
                class="product-card"
            >
               <?php if ($medias): ?>

    <div class="product-media-container">

        <?php foreach ($medias as $index => $media): ?>

            <div class="product-slide <?= $index === 0 ? 'active' : '' ?>">

                <?php if ($media['media_type'] === 'image'): ?>

                    <img
                        src="/bodytech/public/uploads/productos/<?= htmlspecialchars($media['media_path']) ?>"
                        class="product-image"
                        alt="<?= htmlspecialchars($producto['nombre']) ?>"
                    >

                <?php endif; ?>


                <?php if ($media['media_type'] === 'video'): ?>

                    <video
                        class="product-video"
                        muted
                        loop
                        playsinline
                    >
                        <source
                            src="/bodytech/public/uploads/productos/<?= htmlspecialchars($media['media_path']) ?>"
                            type="video/mp4"
                        >
                    </video>

                <?php endif; ?>

            </div>

        <?php endforeach; ?>

    </div>

<?php endif; ?>

                <div class="product-content">

                    <h3 class="product-title">
                        <?= htmlspecialchars($producto['nombre']) ?>
                    </h3>

                    <p class="product-description">
                        <?= nl2br(htmlspecialchars($producto['descripcion'])) ?>
                    </p>

                    <p class="product-price">
                        $<?= number_format($producto['precio'], 2) ?>
                    </p>

                    <p class="product-category">
                        <?= htmlspecialchars($producto['categoria']) ?>
                    </p>

                    <p class="product-stock">
                        Stock disponible: <?= $producto['stock'] ?>
                    </p>

                    <?php
                    /* =========================
                    REPUTACIÓN PRODUCTO
                    ========================= */
                   $statsStmt = $pdo->prepare("
                        SELECT
                            AVG(calificacion) AS promedio,
                            COUNT(*) AS total_resenas
                        FROM resenas_productos
                        WHERE producto_id = ?
                        AND estado = 'aprobada'
                    ");

                    $statsStmt->execute([$producto['id']]);

                    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

                    $promedio = round($stats['promedio'] ?? 0, 1);
                    $totalResenas = $stats['total_resenas'] ?? 0;

                    $rounded = round($promedio);
                    ?>

                    <div class="product-rating">

                        <div class="product-stars">

                            <?php
                            for($i=1; $i<=5; $i++){

                                if($i <= $rounded){
                                    echo "★";
                                }else{
                                    echo "☆";
                                }

                            }
                            ?>

                        </div>

                        <div class="product-rating-info">

                            <?php if($totalResenas > 0): ?>

                                <strong><?= $promedio ?>/5</strong>
                                (<?= $totalResenas ?> reseñas)

                            <?php else: ?>

                                Sin calificaciones todavía

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            </article>

        </a>

        <?php
    }

    echo '</div>';
    ?>

</section>

<!-- 🏋️ GIMNASIOS -->
<section class="gyms-section">

    <div class="section-header reveal-section">

    <h2 class="section-title reveal-item">
        🏋️ Gimnasios disponibles
    </h2>

    <p class="section-subtitle reveal-item">
        Encuentra centros deportivos, sedes y espacios de entrenamiento respaldados por la comunidad BodyTech.
    </p>

</div>

    <?php
    $stmt = $pdo->query("
        SELECT *
        FROM gimnasios
        WHERE activo = 1
        ORDER BY id DESC
    ");

    $gimnasios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $ultimoId = $gimnasios[0]['id'] ?? 0;

    echo '
<div class="gyms-carousel-wrapper">

    <button
        type="button"
        class="gyms-carousel-arrow gyms-carousel-prev"
        aria-label="Gimnasio anterior"
    >
        <i class="fa-solid fa-chevron-left"></i>
    </button>

    <div
        id="lista-gimnasios"
        class="gyms-grid"
        data-last-update="'.$ultimoId.'"
    >';

    if (count($gimnasios) === 0) {

        echo '
        <p class="empty-gyms">
            No hay gimnasios publicados todavía.
        </p>';

    }

    foreach ($gimnasios as $gimnasio) {

        $mediaStmt = $pdo->prepare("
            SELECT media_path, media_type
            FROM gimnasio_media
            WHERE gimnasio_id = ?
        ");

        $mediaStmt->execute([$gimnasio['id']]);
        $medias = $mediaStmt->fetchAll();
    ?>
        <article
            id="gym-<?= $gimnasio['id'] ?>"
            class="gym-card"
        >

          <?php if ($medias): ?>

    <div class="gym-media-container">

    <?php foreach ($medias as $index => $media): ?>

        <div class="gym-slide <?= $index === 0 ? 'active' : '' ?>">

            <?php if ($media['media_type'] === 'image'): ?>

                <img
                    src="/bodytech/public/uploads/gimnasios/<?= htmlspecialchars($media['media_path']) ?>"
                    class="gym-image"
                    alt="<?= htmlspecialchars($gimnasio['nombre']) ?>"
                >

            <?php endif; ?>

            <?php if ($media['media_type'] === 'video'): ?>

                <video
                    class="gym-video"
                    muted
                    loop
                    playsinline
                >
                    <source
                        src="/bodytech/public/uploads/gimnasios/<?= htmlspecialchars($media['media_path']) ?>"
                        type="video/mp4"
                    >
                </video>

            <?php endif; ?>

        </div>

    <?php endforeach; ?>


</div>

<?php endif; ?>

            <div class="gym-content">

                <h3 class="gym-title">

             <a
                    href="/bodytech/public/detalle_gimnasio.php?id=<?= $gimnasio['id'] ?>"
                    class="gym-title-link"
                >
                    <?= htmlspecialchars($gimnasio['nombre']) ?>
                </a>

            </h3>

                <p class="gym-description">
                    <?= nl2br(htmlspecialchars($gimnasio['descripcion'])) ?>
                </p>

                <p class="gym-city">
                    📍 <?= htmlspecialchars($gimnasio['ciudad']) ?>
                </p>

                <p class="gym-address">
                    <?= htmlspecialchars($gimnasio['direccion']) ?>
                </p>

                <?php
                /* =========================
                REPUTACIÓN GIMNASIO
                ========================= */
                $statsGymStmt = $pdo->prepare("
                    SELECT
                        AVG(calificacion) AS promedio,
                        COUNT(*) AS total_resenas
                    FROM resenas_gimnasios
                    WHERE gimnasio_id = ?
                ");

                $statsGymStmt->execute([$gimnasio['id']]);

                $statsGym = $statsGymStmt->fetch(PDO::FETCH_ASSOC);

                $promedioGym = round($statsGym['promedio'] ?? 0, 1);
                $totalGym = $statsGym['total_resenas'] ?? 0;

                $roundedGym = round($promedioGym);
                ?>

                <div class="gym-rating">

                    <div class="gym-stars">

                        <?php
                        for($i=1; $i<=5; $i++){

                            if($i <= $roundedGym){
                                echo "★";
                            }else{
                                echo "☆";
                            }

                        }
                        ?>

                    </div>

                    <div class="gym-rating-info">

                        <?php if($totalGym > 0): ?>

                            <strong><?= $promedioGym ?>/5</strong>
                            (<?= $totalGym ?> opiniones)

                        <?php else: ?>

                            Sin opiniones todavía

                        <?php endif; ?>

                    </div>

                </div>

                <a
                    href="/bodytech/public/register_afiliado.php?gym=<?= $gimnasio['id'] ?>"
                    class="gym-button"
                >
                    Afiliarme a esta sede
                </a>

            </div>

        </article>
    
    <?php
    }

    echo '
    </div>

    <button
        type="button"
        class="gyms-carousel-arrow gyms-carousel-next"
        aria-label="Siguientes gimnasios"
    >
        &#10095;
    </button>

</div>';
    ?>

</section>

<section class="bodytech-info-section bodytech-transition">

    <div class="section-header reveal-section">

        <h2 class="section-title reveal-item">
            ¿Por qué elegir BodyTech?
        </h2>

        <p class="section-subtitle reveal-item">
            Más que una plataforma fitness, una comunidad diseñada para transformar vidas.
        </p>

    </div>


    <!-- BLOQUE 1 -->
    <div class="info-block reveal-section">

        <div class="info-image reveal-item">

            <img
                src="/bodytech/public/assets/img/12.png"
                alt="BodyTech Entertainment"
            >

        </div>

        <div class="info-content reveal-item">

            <h3>
               Somos BodyTech Entertainment
            </h3>

            <p>
              
Vive el fitness de una manera diferente
En BodyTech Entertainment, entendemos que el ejercicio no debe ser una obligación diaria, sino el momento más emocionante de tu jornada. No somos solo un espacio con máquinas; somos un ecosistema diseñado para transformar tu estilo de vida. Nuestra vibrante comunidad fusiona la ciencia del entrenamiento, la psicología de la motivación y la vanguardia de la tecnología para convertir cada una de tus sesiones en una experiencia inspiradora, adictiva y llena de energía.
Aquí no entrenas en aislamiento; te integras a un movimiento colectivo donde cada repetición cuenta y cada logro se celebra en equipo.
Los Pilares de Nuestra Experiencia:
el Entrenamiento de Élite y Prescripción Inteligente:
Llevamos tus capacidades al siguiente nivel a través de programas diseñados por profesionales de la salud y el acondicionamiento físico. Ya sea que busques dominar zonas de peso libre, desafiar tu resistencia en circuitos de entrenamiento funcional, o liberar energía en nuestras exclusivas zonas de baile, contamos con espacios personalizados para cada disciplina. A través del acompañamiento de un entrenador tutor y opciones de Entrenamiento Personalizado de Bodytech, aprenderás la ejecución técnica perfecta, mejorarás tu flexibilidad y fuerza, y maximizarás tu adherencia al ejercicio físico.
y la Motivación y Comunidad Vibrante
La fuerza del grupo es nuestro motor principal. 

        </div>

    </div>


    <!-- BLOQUE 2 -->
<div class="info-block reverse reveal-section">

    <div class="info-image reveal-item">

        <img
            src="/bodytech/public/assets/img/2.png"
            alt="Garantía de productos"
        >

    </div>

    <div class="info-content reveal-item">

        <h3>
            Garantía y confianza en cada producto
        </h3>

        <p>
            Seleccionamos productos respaldados por valoraciones reales y una
            comunidad activa que prioriza la calidad, la seguridad y los
            resultados a largo plazo. Tu tranquilidad es nuestro estándar de calidad
En un mercado saturado de opciones temporales y promesas vacías, nosotros elegimos el camino de la transparencia y la excelencia. Entendemos que cuando compras un producto, no solo estás adquiriendo un objeto o un servicio; estás depositando tu confianza en nosotros y en el impacto que eso tendrá en tu vida diaria. Por ello, nuestra filosofía se aleja del comercio masivo e impersonal para enfocarse en una curaduría estricta, donde cada artículo de nuestro catálogo pasa por un riguroso proceso de validación antes de llegar a tus manos. • Contamos con el apoyo de una comunidad vibrante y exigente que comparte nuestros mismos valores. Este ecosistema de usuarios actúa como nuestro panel de expertos más severo. Gracias a su retroalimentación constante, logramos evolucionar nuestro catálogo, optimizar la selección de marcas y priorizar la calidad y la seguridad por encima de cualquier otra variable. Si un producto no cumple con los estándares de nuestra comunidad, simplemente no forma parte de nuestra oferta.
• Resultados e impacto a largo plazo:
Creemos firmemente en el consumo responsable y duradero. No diseñamos nuestra oferta para soluciones rápidas que fallan al poco tiempo. Nos enfocamos en la durabilidad, la eficiencia y el valor sostenido, seleccionando componentes y materiales que resistan el paso del tiempo. Nuestro objetivo es que tu inversión inicial se traduzca en satisfacción continua y en resultados medibles meses y años después de tu compra.

        </p>

    </div>

</div>
    </div>


    <!-- BLOQUE 3 -->
    <div class="info-block reveal-section">

        <div class="info-image reveal-item">

            <img
                src="/bodytech/public/assets/img/33.png"
                alt="Entrenamiento en casa"
            >

        </div>

        <div class="info-content reveal-item">

            <h3>
                Resultados dentro y fuera del gimnasio
            </h3>

            <p>
                Ya sea desde una sede afiliada o desde tu hogar, nuestros planes y
                recomendaciones están pensados para acompañarte en cada etapa de tu
                evolución física y mental. Entendemos que el bienestar y la transformación no son eventos aislados que ocurren únicamente durante una hora al día entre cuatro paredes. Tu vida se mueve, cambia de entorno y presenta diferentes retos cotidianos; por eso, tu entrenamiento y tu mentalidad deben ser igual de dinámicos. Nuestro compromiso contigo va mucho más allá del espacio físico de nuestras instalaciones. Nos convertimos en tu aliado de estilo de vida las 24 horas del día, los 7 días de la semana, asegurándonos de que cada esfuerzo se traduzca en cambios sostenibles y visibles en todos los aspectos de tu realidad.
Flexibilidad Total para tu Estilo de Vida
• Desde una sede afiliada:
Cuando cruzas las puertas de nuestros centros de acondicionamiento, accedes a un entorno de alto rendimiento diseñado para potenciar tus capacidades. Ponemos a tu disposición equipamiento de última generación, zonas especializadas de entrenamiento de fuerza, áreas cardiovasculares y salones de clases grupales con dinámicas llenas de energía. Todo esto, bajo la supervisión directa de profesionales certificados que corrigen tu técnica en tiempo real, optimizan tus cargas de trabajo y te brindan la motivación necesaria para romper tus propios récords personales.
• Desde la comodidad de tu hogar:
Sabemos que el tiempo es tu recurso más valioso y que los viajes, el trabajo o la rutina familiar a veces te impiden desplazarte. Por ello, hemos derribado las barreras geográficas. Llevamos la esencia de nuestra metodología directamente a tu sala, habitación o lugar de viaje. A través de nuestras plataformas digitales, guías estructuradas y rutinas adaptadas para realizar con poco o ningún equipo, transformamos cualquier espacio en tu propio centro de bienestar, garantizando que nunca pierdas el impulso ni la constancia.
            </p>

        </div>

    </div>

</section>

<?php

$topStmt = $pdo->query("
    SELECT
        p.id,
        p.nombre,
        p.precio,
        SUM(oi.cantidad) AS total_vendido
    FROM orden_items oi

    INNER JOIN ordenes o
        ON oi.orden_id = o.id

    INNER JOIN productos p
        ON oi.producto_id = p.id

    WHERE
        o.estado_pago = 'aprobado'
        AND o.created_at >= NOW() - INTERVAL 7 DAY

    GROUP BY
        p.id,
        p.nombre,
        p.precio

    ORDER BY total_vendido DESC

    LIMIT 3
");

$topProductos = $topStmt->fetchAll(PDO::FETCH_ASSOC);

/* ==========================================
   ORDEN VISUAL DEL PODIO
========================================== */

$productosPodio = [];

if(count($topProductos) === 3){

    $productosPodio = [

        [
            'producto' => $topProductos[1],
            'medalla'  => '🥈',
            'clase'    => 'silver'
        ],

        [
            'producto' => $topProductos[0],
            'medalla'  => '🥇',
            'clase'    => 'gold champion'
        ],

        [
            'producto' => $topProductos[2],
            'medalla'  => '🥉',
            'clase'    => 'bronze'
        ]

    ];

}else{

    foreach($topProductos as $index => $producto){

        $medallas = ['🥇','🥈','🥉'];
        $clases   = ['gold','silver','bronze'];

        $productosPodio[] = [

            'producto' => $producto,
            'medalla'  => $medallas[$index] ?? '🏅',
            'clase'    => $clases[$index] ?? ''

        ];

    }

}
?>

<section class="weekly-top-section">

    <div class="section-header reveal-section">

        <h2 class="section-title reveal-item">
            🏆 Productos más vendidos esta semana
        </h2>

        <p class="section-subtitle reveal-item">
            Lo que la comunidad BodyTech está comprando y utilizando actualmente.
        </p>

    </div>

    <?php if(empty($topProductos)): ?>

        <div class="empty-top-products">

            Todavía no hay ventas registradas esta semana.

        </div>

    <?php else: ?>

        <<div class="top-podium">

    <?php

    foreach($productosPodio as $item):

        $producto = $item['producto'];

        $mediaStmt = $pdo->prepare("
            SELECT media_path
            FROM producto_media
            WHERE producto_id = ?
            AND media_type = 'image'
            ORDER BY id ASC
            LIMIT 1
        ");

        $mediaStmt->execute([$producto['id']]);

        $imagen = $mediaStmt->fetchColumn();

    ?>

    <a
        href="/bodytech/public/producto.php?id=<?= $producto['id'] ?>"
        class="podium-link"
    >

        <article class="podium-card <?= $item['clase'] ?>">

            <div class="podium-medal">

                <?= $item['medalla'] ?>

            </div>

            <?php if($imagen): ?>

                <img
                    src="/bodytech/public/uploads/productos/<?= htmlspecialchars($imagen) ?>"
                    alt="<?= htmlspecialchars($producto['nombre']) ?>"
                    class="podium-image"
                >

            <?php endif; ?>

            <h3>

                <?= htmlspecialchars($producto['nombre']) ?>

            </h3>

            <span class="podium-sales">

                🔥 <?= $producto['total_vendido'] ?> ventas

            </span>

        </article>

    </a>

    <?php endforeach; ?>

</div>

    <?php endif; ?>

</section>

<section class="beginner-section">

    <div class="section-header reveal-section">

        <h2 class="section-title reveal-item">
            🚀 Empieza desde cero
        </h2>

        <p class="section-subtitle reveal-item">
            Todo gran cambio comienza con un primer paso. Aprende a entrenar correctamente desde el inicio.
        </p>

    </div>

    <div class="beginner-container reveal-section">

        <div class="beginner-video reveal-item">

            <iframe
                src="https://www.youtube.com/embed/UItWltVZZmE"
                title="Guía para principiantes"
                allowfullscreen
            ></iframe>

        </div>

        <div class="beginner-content reveal-item">

            <h3>
                Construye una base sólida
            </h3>

            <p>
                La disciplina, la alimentación y la técnica correcta son los pilares fundamentales de cualquier transformación física.
            </p>

            <div class="beginner-tips">

                <div class="tip-card">
                    ✅ Aprende la técnica antes del peso.
                </div>

                <div class="tip-card">
                    🥗 La alimentación es el 70% del progreso.
                </div>

                <div class="tip-card">
                    ⏰ La constancia supera a la motivación.
                </div>

                <div class="tip-card">
                    🧠 Descansa y permite que tu cuerpo evolucione.
                </div>

            </div>

            <a href="#" class="beginner-button">

                Explora mas de nuestros suplementos nutricionales!

            </a>

        </div>

    </div>

</section>

<section class="bodytech-experience-section">

    <div class="experience-main">

        <div class="experience-image">

            <img
                src="/bodytech/public/assets/img/8.png"
                alt="BodyTech Experience"
            >

        </div>

        <div class="experience-content reveal-section">

            <h2 class="reveal-item">
                Más que un gimnasio.
            </h2>

            <p class="reveal-item">

                BodyTech Entertaiment representa una comunidad enfocada en la disciplina,
                el crecimiento personal y la excelencia física. Nuestro objetivo
                no es únicamente transformar cuerpos, sino construir hábitos y
                estilos de vida sostenibles.

            </p>

            <ul class="experience-list reveal-item">

                <li>🏋️ Entrenadores certificados y seguimiento profesional.</li>

                <li>⚙️ Equipamiento moderno y espacios especializados.</li>

                <li>🌎 Comunidad fitness presente en múltiples ciudades.</li>

            </ul>

            <a
                href="#lista-gimnasios"
                class="experience-button reveal-item"
            >

                Explorar nuestras sedes

            </a>

        </div>

    </div>


<script src="/bodytech/public/assets/js/principal.js"></script>

<script src="/bodytech/public/assets/js/home.js"></script>

<?php require_once '../app/views/footer.php'; ?>
</body>
</html>