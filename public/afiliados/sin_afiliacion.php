<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    die('Acceso no autorizado.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sin afiliación</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .card {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            text-align: center;
            max-width: 420px;
            box-shadow: 0 10px 25px rgba(0,0,0,.15);
        }
        a button {
            margin-top: 20px;
            padding: 12px 20px;
            font-size: 1rem;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>⚠️ No tienes una membresía activa</h2>
    <p>Tu afiliación fue cancelada correctamente.</p>
    <p>Puedes afiliarte nuevamente cuando lo desees.</p>

    <a href="/bodytech/public/register_afiliado.php">
        <button>➕ Afiliarme de nuevo</button>
    </a>
</div>

</body>
</html>
