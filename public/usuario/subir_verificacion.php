<?php

session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

if (!isset($_SESSION['usuario_id'])) {

    header('Location: /bodytech/public/login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

$stmt = $pdo->prepare("
    SELECT estado
    FROM solicitudes_verificacion
    WHERE usuario_id = ?
    AND estado = 'pendiente'
    LIMIT 1
");

$stmt->execute([$usuario_id]);

$pendiente = $stmt->fetch();

if ($pendiente) {
    die('Ya tienes una solicitud pendiente de revisión.');
}

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_FILES['documento'])) {

        $mensaje = 'Debes seleccionar una imagen.';
    }

    else {

        $archivo = $_FILES['documento'];

        $permitidos = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        if (!in_array($archivo['type'], $permitidos)) {

            $mensaje = 'Formato no permitido.';
        }

        elseif ($archivo['size'] > 5 * 1024 * 1024) {

            $mensaje = 'La imagen no puede superar los 5 MB.';
        }

        else {

            $extension = pathinfo(
                $archivo['name'],
                PATHINFO_EXTENSION
            );

            $nombreArchivo =
                'doc_' .
                $usuario_id .
                '_' .
                time() .
                '.' .
                $extension;

            $rutaCarpeta =
                $_SERVER['DOCUMENT_ROOT']
                . '/bodytech/public/uploads/verificaciones/';

            if (!is_dir($rutaCarpeta)) {

                mkdir(
                    $rutaCarpeta,
                    0777,
                    true
                );
            }

            $rutaFinal = $rutaCarpeta . $nombreArchivo;

            if (
            move_uploaded_file(
                $archivo['tmp_name'],
                $rutaFinal
            )
        ) {

            $stmt = $pdo->prepare("
                INSERT INTO solicitudes_verificacion
                (
                    usuario_id,
                    imagen_documento
                )
                VALUES
                (
                    ?,
                    ?
                )
            ");

            $stmt->execute([
                $usuario_id,
                $nombreArchivo
            ]);

            header(
                'Location: seguridad.php'
            );

            exit;

        } else {

            $mensaje = 'No fue posible guardar la imagen. Inténtalo nuevamente.';

        }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>

    <meta charset="UTF-8">

    <title>
        Verificar identidad
    </title>

    <link
    rel="stylesheet"
    href="/bodytech/public/assets/css/subir_verificacion.css?v=1"
>

</head>
<body>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/app/views/header.php'; ?>

<main class="account-container">

    <section class="account-content">

        <h1>
            🪪 Verificar identidad
        </h1>

        <p>
            Sube una fotografía clara de la parte frontal de tu documento.
        </p>

        <p>
            Una vez revisada por un administrador, la imagen será eliminada
            automáticamente por motivos de privacidad y protección de datos.
        </p>

        <?php if ($mensaje): ?>

            <p>

                <?= htmlspecialchars($mensaje) ?>

            </p>

        <?php endif; ?>

       <form
    method="POST"
    enctype="multipart/form-data"
    class="verification-form"
>

    <div class="verification-upload-box">

        <div class="verification-upload-icon">
            <i class="fa-solid fa-file-circle-question"></i>
        </div>

        <h3>
            Selecciona tu documento
        </h3>

        <p>
            JPG, JPEG, PNG o WEBP · Máximo 5 MB
        </p>

        <label
            for="documento"
            class="verification-file-button"
        >
            <i class="fa-solid fa-folder-open"></i>
            Seleccionar archivo
        </label>

        <input
            type="file"
            id="documento"
            name="documento"
            accept=".jpg,.jpeg,.png,.webp"
            required
        >

        <button
            type="submit"
            class="verification-submit-button"
        >
            <i class="fa-solid fa-paper-plane"></i>
            Enviar documento
        </button>

    </div>

</form>

    </section>

</main>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/app/views/footer.php'; ?>

</body>
</html>