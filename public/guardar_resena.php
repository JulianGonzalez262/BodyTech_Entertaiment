<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    die("Debes iniciar sesión.");
}

$usuario_id = $_SESSION['usuario_id'];

$orden_id = $_POST['orden_id'] ?? null;
$producto_id = $_POST['producto_id'] ?? null;
$calificacion = $_POST['calificacion'] ?? null;
$comentario = trim($_POST['comentario'] ?? '');

if (
    !$orden_id ||
    !$producto_id ||
    !$calificacion ||
    !$comentario
) {
    die("Completa todos los campos.");
}

/* =========================
   VALIDAR ORDEN ENTREGADA
========================= */
$stmt = $pdo->prepare("
    SELECT id
    FROM ordenes
    WHERE id = ?
    AND usuario_id = ?
    AND estado_orden = 'entregado'
    LIMIT 1
");

$stmt->execute([
    $orden_id,
    $usuario_id
]);

$orden = $stmt->fetch();

if (!$orden) {
    die("Orden inválida.");
}

/* =========================
   VALIDAR PRODUCTO EN ORDEN
========================= */
$stmt = $pdo->prepare("
    SELECT id
    FROM orden_items
    WHERE orden_id = ?
    AND producto_id = ?
    LIMIT 1
");

$stmt->execute([
    $orden_id,
    $producto_id
]);

$item = $stmt->fetch();

if (!$item) {
    die("Producto inválido.");
}

/* =========================
   EVITAR DUPLICADOS
========================= */
$stmt = $pdo->prepare("
    SELECT id
    FROM resenas_productos
    WHERE usuario_id = ?
    AND orden_id = ?
    AND producto_id = ?
    LIMIT 1
");

$stmt->execute([
    $usuario_id,
    $orden_id,
    $producto_id
]);

if ($stmt->fetch()) {
    die("Ya dejaste una reseña.");
}

/* =========================
   VALIDAR MULTIMEDIA
========================= */
$tieneMedia = false;

if (!empty($_FILES['media']['name'][0])) {
    $tieneMedia = true;
}

$estado = $tieneMedia ? 'pendiente' : 'aprobado';

try {

    $pdo->beginTransaction();

    /* =========================
       INSERT RESEÑA
    ========================== */
    $stmt = $pdo->prepare("
        INSERT INTO resenas_productos (
            producto_id,
            usuario_id,
            orden_id,
            calificacion,
            comentario,
            estado
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $producto_id,
        $usuario_id,
        $orden_id,
        $calificacion,
        $comentario,
        $estado
    ]);

    $resena_id = $pdo->lastInsertId();

    /* =========================
       SUBIR MULTIMEDIA
    ========================== */
    if ($tieneMedia) {

        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/bodytech/public/uploads/resenas/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        foreach ($_FILES['media']['tmp_name'] as $key => $tmpName) {

            if (!$tmpName) {
                continue;
            }

            $originalName = $_FILES['media']['name'][$key];

            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            $permitidos = ['jpg','jpeg','png','webp','mp4'];

            if (!in_array($extension, $permitidos)) {
                continue;
            }

            $nuevoNombre = uniqid('resena_') . '.' . $extension;

            move_uploaded_file(
                $tmpName,
                $uploadDir . $nuevoNombre
            );

            $tipo = ($extension === 'mp4') ? 'video' : 'image';

            $stmt = $pdo->prepare("
                INSERT INTO resenas_media (
                    resena_id,
                    media_path,
                    media_type
                )
                VALUES (?, ?, ?)
            ");

            $stmt->execute([
                $resena_id,
                $nuevoNombre,
                $tipo
            ]);
        }

    }

    $pdo->commit();

    header("Location: /bodytech/public/ver_compra.php?id={$orden_id}&resena=ok");
    exit;

} catch(Exception $e) {

    $pdo->rollBack();

    die("Error al guardar reseña: " . $e->getMessage());
}