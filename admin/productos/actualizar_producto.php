<?php
require_once __DIR__ . '/../admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

$id = $_POST['id'] ?? null;
$nombre = $_POST['nombre'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';
$precio = $_POST['precio'] ?? 0;
$stock = $_POST['stock'] ?? 0;
$categoria = $_POST['categoria'] ?? '';

if (!$id) {
    die("ID inválido");
}

/* ================================
   0️⃣ OBTENER STOCK ANTERIOR
================================ */

$stmtAnterior = $pdo->prepare("
    SELECT nombre, stock
    FROM productos
    WHERE id = ?
");

$stmtAnterior->execute([$id]);

$productoAnterior = $stmtAnterior->fetch(PDO::FETCH_ASSOC);
$stmt = $pdo->prepare("
    UPDATE productos
    SET nombre = ?, descripcion = ?, precio = ?, stock = ?, categoria = ?
    WHERE id = ?
");

$stmt->execute([
    $nombre,
    $descripcion,
    $precio,
    $stock,
    $categoria,
    $id
]);

/* ================================
   1.1️⃣ PRODUCTO NUEVAMENTE DISPONIBLE
================================ */

if (
    $productoAnterior &&
    $productoAnterior['stock'] <= 0 &&
    $stock > 0
) {

    $usuarios = $pdo->query("
        SELECT id
        FROM usuarios
        WHERE rol_id = 1
    ");

    foreach ($usuarios as $usuario) {

        $stmtNotif = $pdo->prepare("
            INSERT INTO notificaciones
            (
                usuario_id,
                titulo,
                mensaje,
                tipo
            )
            VALUES
            (
                ?, ?, ?, ?
            )
        ");

        $stmtNotif->execute([
            $usuario['id'],
            'Producto nuevamente disponible',
            'El producto "' . $nombre . '" volvió a tener stock disponible.',
            'producto'
        ]);
    }
}

if (!empty($_FILES['media']['name'][0])) {

    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/bodytech/public/uploads/productos/';

    // Crear carpeta si no existe
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    foreach ($_FILES['media']['tmp_name'] as $key => $tmpName) {

        if ($_FILES['media']['error'][$key] !== UPLOAD_ERR_OK) {
            continue;
        }

        $nombreOriginal = $_FILES['media']['name'][$key];
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

        // 🔥 detectar tipo correctamente
        $media_type = in_array($extension, ['mp4','webm','ogg']) ? 'video' : 'image';

        // 🔥 nombre único más seguro
        $nombreFinal = uniqid('prod_', true) . '.' . $extension;
        $rutaFinal = $uploadDir . $nombreFinal;

        if (move_uploaded_file($tmpName, $rutaFinal)) {

            $mediaStmt = $pdo->prepare("
                INSERT INTO producto_media (producto_id, media_path, media_type)
                VALUES (?, ?, ?)
            ");

            $mediaStmt->execute([
                $id,
                $nombreFinal,
                $media_type
            ]);
        }
    }
}

/* ================================
   3️⃣ REDIRIGIR
================================ */
header("Location: editar_producto.php?id=" . $id);
exit;