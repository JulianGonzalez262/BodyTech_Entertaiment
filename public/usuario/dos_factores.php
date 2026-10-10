<?php

session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

if (!isset($_SESSION['usuario_id'])) {

    header('Location: /bodytech/public/login.php');
    exit;

}

$stmt = $pdo->prepare("
    SELECT
        dos_factores_activo,
        dos_factores_metodo
    FROM usuarios
    WHERE id = ?
");

$stmt->execute([
    $_SESSION['usuario_id']
]);

$config = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">

<title>

Autenticación en dos pasos | BodyTech

</title>

<link
    rel="stylesheet"
    href="/bodytech/public/assets/css/dos_factores.css?v=1"
>

</head>

<body>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/app/views/header.php'; ?>

<main class="account-container">

<section class="account-content">

<h1>

🛡️ Autenticación en dos pasos

</h1>

<p>

Protege tu cuenta añadiendo una segunda capa de seguridad al iniciar sesión.

</p>

<hr>

<h3>

Estado actual

</h3>

<?php if($config['dos_factores_activo']): ?>

<p>

<strong>

✅ Activada

</strong>

</p>

<p>

Método:

<strong>

<?= ucfirst($config['dos_factores_metodo']) ?>

</strong>

</p>

<a
href="/bodytech/public/usuario/desactivar_2fa.php"
class="edit-profile-btn"
>

❌ Desactivar

</a>

<?php else: ?>

<p>

<strong>

⚠️ Actualmente desactivada

</strong>

</p>

<p>

Puedes elegir uno de los siguientes métodos.

</p>

<div class="account-options">

<a

href="/bodytech/public/usuario/activar_2fa.php?metodo=email"

class="account-option"

>

📧 Activar mediante correo electrónico

</a>

<a

href="/bodytech/public/usuario/activar_2fa.php?metodo=authenticator"

class="account-option"

>

📲 Activar Google Authenticator

</a>

</div>

<?php endif; ?>

</section>

</main>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/app/views/footer.php'; ?>

</body>

</html>