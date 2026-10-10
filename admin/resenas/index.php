<?php
require_once __DIR__ . '/../admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

/* =========================
   OBTENER RESEÑAS
========================= */
$stmt = $pdo->prepare("
    SELECT 
        resenas_productos.*,
        productos.nombre AS producto_nombre,
        usuarios.nombre AS usuario_nombre
    FROM resenas_productos

    INNER JOIN productos
        ON resenas_productos.producto_id = productos.id

    INNER JOIN usuarios
        ON resenas_productos.usuario_id = usuarios.id

    ORDER BY resenas_productos.id DESC
");

$stmt->execute();

$resenas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Moderación de Reseñas</title>

<style>

body{
    margin:0;
    padding:30px;
    background:#f1f5f9;
    font-family:Arial;
}

h1{
    margin-top:0;
}

.card{
    background:white;
    border-radius:14px;
    padding:25px;
    margin-bottom:25px;
    box-shadow:0 0 10px rgba(0,0,0,0.08);
}

.estado{
    display:inline-block;
    padding:8px 14px;
    border-radius:8px;
    color:white;
    font-size:13px;
    font-weight:bold;
}

.aprobado{
    background:#16a34a;
}

.pendiente{
    background:#eab308;
    color:black;
}

.estrellas{
    color:#f59e0b;
    font-size:22px;
}

.btn{
    display:inline-block;
    padding:10px 16px;
    border-radius:8px;
    text-decoration:none;
    color:white;
    font-weight:bold;
    margin-right:10px;
}

.btn-aprobar{
    background:#16a34a;
}

.btn-eliminar{
    background:#dc2626;
}

.media{
    display:flex;
    gap:15px;
    flex-wrap:wrap;
    margin-top:15px;
}

.media img,
.media video{
    width:140px;
    border-radius:10px;
}

</style>

</head>
<body>

<a href="/bodytech/admin/index.php">
    ⬅️ Volver al panel
</a>

<h1>🛡️ Moderación de Reseñas</h1>

<?php if(!$resenas): ?>

    <p>No existen reseñas.</p>

<?php endif; ?>

<?php foreach($resenas as $resena): ?>

    <?php
    /* =========================
       MULTIMEDIA
    ========================== */
    $mediaStmt = $pdo->prepare("
        SELECT *
        FROM resenas_media
        WHERE resena_id = ?
    ");

    $mediaStmt->execute([$resena['id']]);

    $medias = $mediaStmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div class="card">

        <h2>
            <?= htmlspecialchars($resena['producto_nombre']) ?>
        </h2>

        <p>
            <strong>Cliente:</strong>
            <?= htmlspecialchars($resena['usuario_nombre']) ?>
        </p>

        <div class="estrellas">

            <?php
            for($i=1; $i<=5; $i++){

                if($i <= $resena['calificacion']){
                    echo "★";
                }else{
                    echo "☆";
                }

            }
            ?>

        </div>

        <p style="margin-top:15px;">
            <?= nl2br(htmlspecialchars($resena['comentario'])) ?>
        </p>

        <p>
            <strong>Fecha:</strong>
            <?= $resena['created_at'] ?>
        </p>

        <p>
            <strong>Estado:</strong>

            <span class="estado <?= $resena['estado'] ?>">
                <?= strtoupper($resena['estado']) ?>
            </span>
        </p>

        <?php if($medias): ?>

            <div class="media">

                <?php foreach($medias as $m): ?>

                    <?php if($m['media_type'] === 'image'): ?>

                        <img src="/bodytech/public/uploads/resenas/<?= htmlspecialchars($m['media_path']) ?>">

                    <?php endif; ?>

                    <?php if($m['media_type'] === 'video'): ?>

                        <video controls>
                            <source src="/bodytech/public/uploads/resenas/<?= htmlspecialchars($m['media_path']) ?>" type="video/mp4">
                        </video>

                    <?php endif; ?>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

        <br>

        <?php if($resena['estado'] === 'pendiente'): ?>

            <a 
                href="/bodytech/admin/resenas/aprobar.php?id=<?= $resena['id'] ?>"
                class="btn btn-aprobar"
            >
                ✅ Aprobar
            </a>

        <?php endif; ?>

        <a 
            href="/bodytech/admin/resenas/eliminar.php?id=<?= $resena['id'] ?>"
            class="btn btn-eliminar"
            onclick="return confirm('¿Eliminar reseña?')"
        >
            🗑️ Eliminar
        </a>

    </div>

<?php endforeach; ?>

</body>
</html>