<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /bodytech/public/login.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT *
    FROM usuarios
    WHERE id = ?
");

$stmt->execute([$_SESSION['usuario_id']]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    session_destroy();
    header('Location: /bodytech/public/login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar perfil | BodyTech</title>
    <link
    rel="stylesheet"
    href="/bodytech/public/assets/css/editar_perfil.css?v=1"
>
</head>
<body>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/views/header.php'; ?>

<main class="account-container">

    <section class="account-content">

        <section>

                        <h2>📸 Foto de perfil</h2>

            <form
                action="subir_foto.php"
                method="POST"
                enctype="multipart/form-data"
                class="profile-form"
            >

                <input
                    type="file"
                    name="foto"
                    accept=".jpg,.jpeg,.png,.webp"
                    required
                >

                <button type="submit">
                    📤 Subir foto
                </button>

            </form>

            <hr><br>

            <h1>✏️ Editar información personal</h1>

            <form
                action="actualizar_perfil.php"
                method="POST"
                class="profile-form"
            >

                <input
                    type="text"
                    name="nombres"
                    placeholder="Nombres"
                    value="<?= htmlspecialchars($usuario['nombres'] ?? '') ?>"
                    required
                >

                <input
                    type="text"
                    name="apellidos"
                    placeholder="Apellidos"
                    value="<?= htmlspecialchars($usuario['apellidos'] ?? '') ?>"
                    required
                >

                <input
                    type="text"
                    name="telefono_principal"
                    placeholder="Teléfono principal"
                    value="<?= htmlspecialchars($usuario['telefono_principal'] ?? '') ?>"
                >

                <input
                    type="text"
                    name="telefono_secundario"
                    placeholder="Teléfono secundario"
                    value="<?= htmlspecialchars($usuario['telefono_secundario'] ?? '') ?>"
                >

                <input
                    type="text"
                    name="direccion"
                    placeholder="Dirección"
                    value="<?= htmlspecialchars($usuario['direccion'] ?? '') ?>"
                >

                <select name="tipo_documento">

                    <option value="">
                        Selecciona un documento
                    </option>

                    <option value="CC"
                        <?= $usuario['tipo_documento'] === 'CC' ? 'selected' : '' ?>>
                        Cédula
                    </option>

                    <option value="CE"
                        <?= $usuario['tipo_documento'] === 'CE' ? 'selected' : '' ?>>
                        Cédula extranjera
                    </option>

                    <option value="PAS"
                        <?= $usuario['tipo_documento'] === 'PAS' ? 'selected' : '' ?>>
                        Pasaporte
                    </option>

                </select>

                <input
                    type="text"
                    name="numero_documento"
                    placeholder="Número de documento"
                    value="<?= htmlspecialchars($usuario['numero_documento'] ?? '') ?>"
                >

                <button type="submit">
                    💾 Guardar cambios
                </button>

            </form>

        </section>

    </section>

</main>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/views/footer.php'; ?>

</body>
</html>