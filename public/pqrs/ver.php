<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

/* =========================
   VALIDAR SESIÓN
========================= */

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /bodytech/public/login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$id = $_GET['id'] ?? null;

if (!$id) {
    die('PQRS inválida.');
}

/* =========================
   OBTENER PQRS
========================= */

$stmt = $pdo->prepare("
    SELECT *
    FROM pqrs
    WHERE id = ?
    AND usuario_id = ?
    LIMIT 1
");

$stmt->execute([$id, $usuario_id]);

$pqrs = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pqrs) {
    die('PQRS no encontrada.');
}

/* =========================
   RESPUESTAS ADMIN
========================= */

$stmt = $pdo->prepare("
    SELECT
        pr.*,
        u.nombre AS admin_nombre
    FROM pqrs_respuestas pr
    INNER JOIN usuarios u
        ON pr.admin_id = u.id
    WHERE pr.pqrs_id = ?
    ORDER BY pr.created_at ASC
");

$stmt->execute([$id]);

$respuestas = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Detalle PQRS</title>

<style>

body{
    font-family:Arial;
    margin:30px;
}

.box{
    background:#f8fafc;
    padding:20px;
    border-radius:12px;
    margin-bottom:20px;
}

.respuesta{
    background:#e2e8f0;
    padding:15px;
    border-radius:10px;
    margin-top:15px;
}

.badge{
    color:white;
    padding:6px 12px;
    border-radius:20px;
    font-weight:bold;
}

.pendiente{
    background:#f59e0b;
}

.en_proceso{
    background:#2563eb;
}

.respondida{
    background:#16a34a;
}

.cerrada{
    background:#6b7280;
}

</style>

</head>
<body>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/views/header.php'; ?>

<a href="mis_pqrs.php">⬅️ Volver</a>

<h1>📨 Detalle de PQRS #<?= $pqrs['id'] ?></h1>

<div class="box">

    <p>
        <strong>Tipo:</strong>
        <?= ucfirst($pqrs['tipo']) ?>
    </p>

    <p>
        <strong>Estado:</strong>

        <span class="badge <?= $pqrs['estado'] ?>">
            <?= strtoupper($pqrs['estado']) ?>
        </span>
    </p>

    <p>
        <strong>Asunto:</strong>
        <?= htmlspecialchars($pqrs['asunto']) ?>
    </p>

    <p>
        <strong>Descripción:</strong>
    </p>

    <p>
        <?= nl2br(htmlspecialchars($pqrs['descripcion'])) ?>
    </p>

    <p>
        <strong>Creada:</strong>
        <?= $pqrs['created_at'] ?>
    </p>

</div>


<h2>💬 Respuestas del administrador</h2>

<?php if(empty($respuestas)): ?>

    <p>No hay respuestas todavía.</p>

<?php else: ?>

    <?php foreach($respuestas as $r): ?>

        <div class="respuesta">

            <strong>
                <?= htmlspecialchars($r['admin_nombre']) ?>
            </strong>

            <br><br>

            <?= nl2br(htmlspecialchars($r['mensaje'])) ?>

            <br><br>

            <small>
                <?= $r['created_at'] ?>
            </small>

        </div>

    <?php endforeach; ?>

<?php endif; ?>


<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/views/footer.php'; ?>

</body>
</html>