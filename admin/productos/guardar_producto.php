<?php
require_once __DIR__ . '/../admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

// Validar datos básicos
$nombre = $_POST['nombre'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';
$precio = $_POST['precio'] ?? 0;
$stock = $_POST['stock'] ?? 0;
$categoria = $_POST['categoria'] ?? '';
$activo = $_POST['activo'] ?? 1;

if (!$nombre || !$descripcion) {
    die("Datos incompletos");
}

$created_by = $_SESSION['admin_id'];

$stmt = $pdo->prepare("
    INSERT INTO productos (nombre, descripcion, precio, stock, categoria, activo, created_by)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $nombre,
    $descripcion,
    $precio,
    $stock,
    $categoria,
    $activo,
    $created_by
]);

// Obtener ID del producto recién creado
$producto_id = $pdo->lastInsertId();

/* =========================
   NOTIFICAR NUEVO PRODUCTO
========================= */

$stmtUsuarios = $pdo->query("
    SELECT id
    FROM usuarios
");

$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

foreach ($usuarios as $usuario) {

    $stmtNotificacion = $pdo->prepare("
    INSERT INTO notificaciones
    (
        usuario_id,
        titulo,
        mensaje,
        tipo,
        referencia_id
    )
    VALUES
    (
        ?,
        ?,
        ?,
        ?,
        ?
    )
");

$stmtNotificacion->execute([
    $usuario['id'],
    'Nuevo producto disponible',
    'Se ha agregado un nuevo producto: ' . $nombre . '. ¡Descúbrelo ahora en el marketplace!',
    'producto',
    $producto_id
]);
}
// ================================
// 📸📹 GUARDAR MEDIA (IMAGEN + VIDEO)
// ================================
if (!empty($_FILES['media']['name'][0])) {

    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/bodytech/public/uploads/productos/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    foreach ($_FILES['media']['tmp_name'] as $key => $tmpName) {

        if ($_FILES['media']['error'][$key] !== 0) {
            continue;
        }

        $nombreOriginal = $_FILES['media']['name'][$key];
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

        // 🔥 detectar tipo
        if (in_array($extension, ['mp4','webm','ogg'])) {
            $media_type = 'video';
        } else {
            $media_type = 'image';
        }

        // nombre único
        $nombreArchivo = uniqid('prod_') . '.' . $extension;
        $rutaFinal = $uploadDir . $nombreArchivo;

        if (move_uploaded_file($tmpName, $rutaFinal)) {

            $mediaStmt = $pdo->prepare("
                INSERT INTO producto_media (producto_id, media_path, media_type)
                VALUES (?, ?, ?)
            ");

            $mediaStmt->execute([
                $producto_id,
                $nombreArchivo,
                $media_type
            ]);
        }
    }
}

// Redirigir
header("Location: index.php");
exit;