<?php
session_start();

// Seguridad: usuario logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: /bodytech/public/login.php');
    exit;
}

require_once '../config/database.php';

$usuario_id = $_SESSION['user_id'];

// Buscar afiliación del usuario
$stmt = $pdo->prepare("
    SELECT *
    FROM afiliados
    WHERE usuario_id = :usuario_id
    ORDER BY created_at DESC
    LIMIT 1
");
$stmt->execute(['usuario_id' => $usuario_id]);
$afiliado = $stmt->fetch();

// Validaciones fuertes
if (!$afiliado) {
    die('No se encontró una afiliación.');
}

if ($afiliado['estado'] !== 'pendiente_pago') {
    die('Esta afiliación no requiere pago.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Completar pago de membresía</title>
    <link rel="stylesheet" href="/bodytech/public/assets/css/pago.css">
</head>
<body>

<h2>Completar pago de membresía</h2>

<section>
    <h3>Resumen</h3>
    <p><strong>Gym:</strong> <?= htmlspecialchars($afiliado['gym_nombre']) ?></p>
    <p><strong>Membresía:</strong> <?= htmlspecialchars($afiliado['tipo_membresia']) ?></p>
    <p><strong>Precio:</strong> $<?= number_format($afiliado['precio'], 0, ',', '.') ?> COP</p>
</section>

<hr>

<form method="POST" action="/bodytech/app/afiliados/confirmar_pago.php">

    <!-- Identificador CLAVE -->
    <input type="hidden" name="afiliado_id" value="<?= $afiliado['id'] ?>">

    <h3>Datos de pago</h3>

    <label>Tipo de tarjeta</label><br>
    <select name="tipo_tarjeta" required>
        <option value="">Seleccione</option>
        <option value="credito">Crédito</option>
        <option value="debito">Débito</option>
    </select><br><br>

    <label>Número de tarjeta</label><br>
    <input type="text" name="numero_tarjeta" maxlength="19" required><br><br>

    <label>Nombre del titular</label><br>
    <input type="text" name="titular" required><br><br>

    <label>Fecha de vencimiento</label><br>
    <input type="month" name="vencimiento" required><br><br>

    <label>CVV / CVC</label><br>
    <input type="password" name="cvv" maxlength="4" required><br><br>

    <label>Monto a pagar</label><br>
    <input type="text"
           value="$<?= number_format($afiliado['precio'], 0, ',', '.') ?> COP"
           readonly><br><br>

    <button type="submit">Confirmar pago</button>
</form>

<div style="margin-top:15px;">
    <a href="/bodytech/public/afiliados/index.php">⬅ Volver</a>
</div>

</body>
</html>
