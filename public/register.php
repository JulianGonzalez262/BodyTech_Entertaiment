<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro Cliente</title>
    <link rel="stylesheet" href="/bodytech/public/assets/css/logun.css">
</head>
<body>

<?php require_once '../app/views/header.php'; ?>

<main>
    <h2>Registrar Cliente</h2>

    <form method="POST" action="../app/auth/register.php">
        <div>
            <label for="nombre">Nombre completo</label><br>
            <input type="text" name="nombre" id="nombre" required>
        </div>

        <br>

        <div>
            <label for="email">Correo electrónico</label><br>
            <input type="email" name="email" id="email" required>
        </div>

        <br>

        <div>
            <label for="password">Contraseña</label><br>
            <input type="password" name="password" id="password" required>
        </div>

        <br>

        <button type="submit">Registrarse</button>
    </form>
</main>

<?php require_once '../app/views/footer.php'; ?>
</body>
</html>