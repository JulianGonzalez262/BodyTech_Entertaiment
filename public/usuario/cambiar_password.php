<?php

session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

if (!isset($_SESSION['usuario_id'])) {

    header('Location: /bodytech/public/login.php');
    exit;

}

?>

<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">

<title>

Cambiar contraseña | BodyTech

</title>

  <link
        rel="stylesheet"
        href="/bodytech/public/assets/css/cambiar_password.css?v=<?= time() ?>"
    >


</head>

<body>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/app/views/header.php'; ?>

<main class="account-container">

<section class="account-content">

<h1>

🔑 Cambiar contraseña

</h1>

<p>

Por seguridad, primero debes confirmar tu contraseña actual.

</p>

<?php if(isset($_GET['error'])): ?>

<div class="estado-verificacion rechazada">

<?= htmlspecialchars($_GET['error']) ?>

</div>

<?php endif; ?>

<?php if(isset($_GET['ok'])): ?>

<div class="estado-verificacion aprobado">

✅ Contraseña actualizada correctamente.

</div>

<?php endif; ?>

<form
action="actualizar_password.php"
method="POST"
class="profile-form"
>

<label>

Contraseña actual

<input
type="password"
name="password_actual"
required
>

</label>

<label>

Nueva contraseña

<input
type="password"
name="password_nueva"
required
minlength="8"
>

</label>

<label>

Confirmar nueva contraseña

<input
type="password"
name="password_confirmar"
required
minlength="8"
>

</label>

<button type="submit">

💾 Guardar nueva contraseña

</button>

</form>

</section>

</main>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/app/views/footer.php'; ?>

</body>

</html>