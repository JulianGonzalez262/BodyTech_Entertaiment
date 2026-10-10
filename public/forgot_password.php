<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar contraseña</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .box {
            background: #fff;
            padding: 35px;
            width: 100%;
            max-width: 420px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,.15);
            text-align: center;
        }
        input, button {
            width: 100%;
            padding: 12px;
            margin-top: 15px;
            font-size: 1rem;
        }
        button {
            background: #2ecc71;
            border: none;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background: #27ae60;
        }
        .msg {
            margin-top: 15px;
            color: #e74c3c;
        }
    </style>
</head>
<body>

<div class="box">
    <h2>🔐 Recuperar contraseña</h2>

    <form method="POST" action="../app/auth/send_reset_link.php">
        <input 
            type="email" 
            name="email" 
            placeholder="Correo electrónico" 
            required
        >
        <button type="submit">Enviar enlace</button>
    </form>

    <?php if (!empty($_SESSION['msg'])): ?>
        <div class="msg">
            <?= $_SESSION['msg']; unset($_SESSION['msg']); ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
