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

/* =========================
   OBTENER PQRS DEL USUARIO
========================= */

$stmt = $pdo->prepare("
    SELECT *
    FROM pqrs
    WHERE usuario_id = ?
    ORDER BY created_at DESC
");

$stmt->execute([$usuario_id]);

$pqrs = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis PQRS</title>

    <style>
        body{
            font-family: Arial, sans-serif;
            margin:30px;
        }

        table{
            width:100%;
            border-collapse:collapse;
            margin-top:20px;
        }

        th, td{
            border:1px solid #ddd;
            padding:12px;
            text-align:left;
        }

        th{
            background:#111827;
            color:white;
        }

        .badge{
            color:white;
            padding:6px 12px;
            border-radius:20px;
            font-size:14px;
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

<h1>📋 Mis PQRS</h1>

<a href="crear.php">
    ➕ Crear nueva PQRS
</a>

<br><br>

<?php if(empty($pqrs)): ?>

    <p>No has creado ninguna PQRS todavía.</p>

<?php else: ?>

<table>

    <tr>
        <th>ID</th>
        <th>Tipo</th>
        <th>Asunto</th>
        <th>Estado</th>
        <th>Fecha</th>
        <th>Acciones</th>
    </tr>

    <?php foreach($pqrs as $fila): ?>

        <tr>

            <td><?= $fila['id'] ?></td>

            <td><?= ucfirst($fila['tipo']) ?></td>

            <td><?= htmlspecialchars($fila['asunto']) ?></td>

            <td>
                <span class="badge <?= $fila['estado'] ?>">
                    <?= strtoupper($fila['estado']) ?>
                </span>
            </td>

            <td><?= $fila['created_at'] ?></td>

            <td>
                <a href="ver.php?id=<?= $fila['id'] ?>">
                    Ver detalle
                </a>
            </td>

        </tr>

    <?php endforeach; ?>

</table>

<?php endif; ?>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/views/footer.php'; ?>

</body>
</html>