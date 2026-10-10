<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/admin/admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int) $_POST['id'];
$admin_id = $_SESSION['admin_id'];

$nombre = trim($_POST['nombre']);
$descripcion = trim($_POST['descripcion']);
$ciudad = trim($_POST['ciudad']);
$telefono = trim($_POST['telefono']);

// Verificar que el gimnasio pertenece al admin
$stmt = $pdo->prepare(
    "SELECT id FROM gimnasios 
     WHERE id = :id AND created_by = :admin_id"
);
$stmt->execute([
    ':id' => $id,
    ':admin_id' => $admin_id
]);

$gimnasio = $stmt->fetch();

if (!$gimnasio) {
    header('Location: index.php');
    exit;
}

/* ============================
   ACTUALIZAR DATOS DEL GIMNASIO
   ============================ */
$update = $pdo->prepare(
    "UPDATE gimnasios 
     SET nombre = :nombre,
         descripcion = :descripcion,
         ciudad = :ciudad,
         telefono = :telefono,
         updated_at = NOW()
     WHERE id = :id"
);

$update->execute([
    ':nombre' => $nombre,
    ':descripcion' => $descripcion,
    ':ciudad' => $ciudad,
    ':telefono' => $telefono,
    ':id' => $id
]);

/* ============================
   ELIMINAR MEDIA SELECCIONADA
   ============================ */
if (!empty($_POST['eliminar_media']) && is_array($_POST['eliminar_media'])) {

    $selectMedia = $pdo->prepare(
        "SELECT id, media_path 
         FROM gimnasio_media 
         WHERE id = :media_id AND gimnasio_id = :gimnasio_id"
    );

    $deleteMedia = $pdo->prepare(
        "DELETE FROM gimnasio_media 
         WHERE id = :media_id"
    );

    foreach ($_POST['eliminar_media'] as $mediaId) {

        $mediaId = (int) $mediaId;

        $selectMedia->execute([
            ':media_id' => $mediaId,
            ':gimnasio_id' => $id
        ]);

        $media = $selectMedia->fetch();

        if ($media) {
            $ruta = $_SERVER['DOCUMENT_ROOT'] . '/bodytech/public/uploads/gimnasios/' . $media['media_path'];

            if (file_exists($ruta)) {
                unlink($ruta);
            }

            $deleteMedia->execute([
                ':media_id' => $mediaId
            ]);
        }
    }
}

/* ============================
   SUBIR NUEVA MEDIA
   ============================ */
if (!empty($_FILES['media']['name'][0])) {

    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/bodytech/public/uploads/gimnasios/';

    foreach ($_FILES['media']['tmp_name'] as $index => $tmpName) {

        if ($_FILES['media']['error'][$index] !== UPLOAD_ERR_OK) {
            continue;
        }

        $originalName = basename($_FILES['media']['name'][$index]);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        $mediaType = in_array($extension, ['mp4', 'webm', 'ogg'])
            ? 'video'
            : 'image';

        $newName = time() . '_' . uniqid() . '.' . $extension;
        $finalPath = $uploadDir . $newName;

        if (move_uploaded_file($tmpName, $finalPath)) {

            $insertMedia = $pdo->prepare(
                "INSERT INTO gimnasio_media 
                 (gimnasio_id, media_path, media_type)
                 VALUES (:gimnasio_id, :media_path, :media_type)"
            );

            $insertMedia->execute([
                ':gimnasio_id' => $id,
                ':media_path' => $newName,
                ':media_type' => $mediaType
            ]);
        }
    }
}

header('Location: index.php');
exit;