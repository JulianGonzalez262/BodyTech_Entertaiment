<?php
// admin/login.php
session_start();

// Si ya hay sesión de admin, redirigir al panel
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso Administrador</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            height: 100vh;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
        }

        .login-box {
            background: rgba(0, 0, 0, 0.75);
            padding: 40px;
            border-radius: 12px;
            width: 100%;
            max-width: 360px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.6);
        }

        .login-box h2 {
            color: #fff;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .login-box input {
            width: 100%;
            padding: 12px;
            margin-bottom: 18px;
            border: none;
            border-radius: 6px;
            outline: none;
            font-size: 15px;
        }

        .login-box button {
            width: 100%;
            padding: 12px;
            background: #f39c12;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .login-box button:hover {
            background: #e67e22;
        }

        .error {
            color: #ff6b6b;
            text-align: center;
            margin-bottom: 15px;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Administrador</h2>

    <?php if (isset($_GET['error'])): ?>
        <div class="error">
            Credenciales inválidas
        </div>
    <?php endif; ?>

    <form action="login_process.php" method="POST">
        <input type="email" name="email" placeholder="Correo administrador" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Acceder</button>
    </form>
</div>

</body>
</html>