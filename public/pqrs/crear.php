<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /bodytech/public/login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear PQRS</title>
</head>
<body>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/views/header.php'; ?>

<h1>📩 Crear PQRS</h1>

<form action="guardar.php" method="POST">

    <label>Tipo de solicitud</label>
    <br>

    <select name="tipo" required>
        <option value="">Seleccione...</option>
        <option value="peticion">Petición</option>
        <option value="queja">Queja</option>
        <option value="reclamo">Reclamo</option>
        <option value="sugerencia">Sugerencia</option>
    </select>

    <br><br>

    <label>Asunto</label>
    <br>

    <input
        type="text"
        name="asunto"
        maxlength="150"
        required
    >

    <br><br>

    <label>Descripción</label>
    <br>

    <textarea
        name="descripcion"
        rows="8"
        cols="60"
        required
    ></textarea>

    <br><br>

    <button type="submit">
        Enviar PQRS
    </button>

</form>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/views/footer.php'; ?>

</body>
</html>