<?php

session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

if (!isset($_SESSION['usuario_id'])) {

    header('Location: /bodytech/public/login.php');
    exit;

}

$usuario_id = $_SESSION['usuario_id'];

/*
|--------------------------------------------------------------------------
| Crear registro si aún no existe
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM preferencias_usuario
    WHERE usuario_id = ?
");

$stmt->execute([$usuario_id]);

$preferencias = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$preferencias) {

    $stmt = $pdo->prepare("
        INSERT INTO preferencias_usuario (
            usuario_id
        )
        VALUES (?)
    ");

    $stmt->execute([$usuario_id]);

    $stmt = $pdo->prepare("
        SELECT *
        FROM preferencias_usuario
        WHERE usuario_id = ?
    ");

    $stmt->execute([$usuario_id]);

    $preferencias = $stmt->fetch(PDO::FETCH_ASSOC);

}

/*
|--------------------------------------------------------------------------
| Guardar cambios
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $stmt = $pdo->prepare("

        UPDATE preferencias_usuario

        SET

            recibir_notificaciones_productos = ?,

            recibir_notificaciones_afiliaciones = ?,

            recibir_notificaciones_promociones = ?,

            recibir_correos_marketing = ?

        WHERE usuario_id = ?

    ");

    $stmt->execute([

        isset($_POST['productos']) ? 1 : 0,

        isset($_POST['afiliaciones']) ? 1 : 0,

        isset($_POST['promociones']) ? 1 : 0,

        isset($_POST['marketing']) ? 1 : 0,

        $usuario_id

    ]);

    header("Location: notificaciones.php?ok=1");

    exit;

}

?>

<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">

<title>

Notificaciones | BodyTech

</title>

<link
    rel="stylesheet"
    href="/bodytech/public/assets/css/notificacioness.css?v=1"
>

</head>

<body>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/app/views/header.php'; ?>

<main class="account-container">

<section class="account-content">

<h1>

🔔 Gestionar notificaciones

</h1>

<p>

Personaliza qué tipo de información deseas recibir.

</p>

<form
method="POST"
class="notification-form"
>

<div class="notification-item">

    <div>

        <h3>🛒 Productos nuevos</h3>

        <p>
            Recibir avisos cuando se publiquen nuevos productos.
        </p>

    </div>

    <label class="switch">

        <input
            type="checkbox"
            name="productos"
            <?= $preferencias['recibir_notificaciones_productos'] ? 'checked' : '' ?>
        >

        <span class="slider"></span>

    </label>

</div>

<div class="notification-item">

    <div>

        <h3>🏋️ Afiliaciones</h3>

        <p>
            Recibir novedades relacionadas con tus afiliaciones.
        </p>

    </div>

    <label class="switch">

        <input
            type="checkbox"
            name="afiliaciones"
            <?= $preferencias['recibir_notificaciones_afiliaciones'] ? 'checked' : '' ?>
        >

        <span class="slider"></span>

    </label>

</div>

<div class="notification-item">

    <div>

        <h3>🎁 Promociones</h3>

        <p>
            Recibir promociones y descuentos exclusivos.
        </p>

    </div>

    <label class="switch">

        <input
            type="checkbox"
            name="promociones"
            <?= $preferencias['recibir_notificaciones_promociones'] ? 'checked' : '' ?>
        >

        <span class="slider"></span>

    </label>

</div>

<div class="notification-item">

    <div>

        <h3>📧 Correos de marketing</h3>

        <p>
            Recibir información comercial y campañas de BodyTech.
        </p>

    </div>

    <label class="switch">

        <input
            type="checkbox"
            name="marketing"
            <?= $preferencias['recibir_correos_marketing'] ? 'checked' : '' ?>
        >

        <span class="slider"></span>

    </label>

</div>

<br>

<button
    type="submit"
    class="edit-profile-btn"
>

    💾 Guardar cambios

</button>

<?php if(isset($_GET['ok'])): ?>

    <p style="color:green;margin-top:20px;">

        ✅ Preferencias actualizadas correctamente.

    </p>

<?php endif; ?>

</form>

</section>

</main>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/app/views/footer.php'; ?>

</body>

</html>