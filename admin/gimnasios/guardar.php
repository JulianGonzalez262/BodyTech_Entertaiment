<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/admin/admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/sedes_oficiales.php';

/* ================================
   1️⃣ Validar método
================================ */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Acceso no permitido.');
}

/* ================================
   2️⃣ Sanitizar datos
================================ */
$departamento = trim($_POST['departamento'] ?? '');
$ciudad       = trim($_POST['ciudad'] ?? '');
$nombre       = trim($_POST['nombre'] ?? '');
$descripcion  = trim($_POST['descripcion'] ?? '');
$direccion    = trim($_POST['direccion'] ?? '');
$telefono     = trim($_POST['telefono'] ?? '');
$admin_id     = $_SESSION['admin_id'];
$activo       = 1;

/* ================================
   3️⃣ Validación contra catálogo
================================ */
if (
    !isset($SEDES_OFICIALES[$departamento]) ||
    !isset($SEDES_OFICIALES[$departamento][$ciudad]) ||
    !in_array($nombre, $SEDES_OFICIALES[$departamento][$ciudad])
) {
    die('Sede no válida.');
}

/* ================================
   4️⃣ Verificar duplicados
================================ */
$check = $pdo->prepare("
    SELECT id FROM gimnasios
    WHERE nombre = ?
    AND ciudad = ?
    AND activo = 1
    LIMIT 1
");

$check->execute([$nombre, $ciudad]);

if ($check->fetch()) {
    die('Este gimnasio ya está publicado en esta ciudad.');
}

/* ================================
   5️⃣ Insertar gimnasio
================================ */
$sql = "INSERT INTO gimnasios 
        (nombre, descripcion, direccion, ciudad, telefono, created_by, activo)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    $nombre,
    $descripcion,
    $direccion,
    $ciudad,
    $telefono,
    $admin_id,
    $activo
]);

$gimnasio_id = $pdo->lastInsertId();

/* ================================
   NOTIFICAR NUEVO GIMNASIO
================================ */

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
    'Nuevo gimnasio disponible',
    'Se ha agregado un nuevo gimnasio: ' . $nombre . '. ¡Visítalo y conoce sus instalaciones!',
    'gimnasio',
    $gimnasio_id
]);
}
/* ================================
   6️⃣ Procesar múltiples archivos
================================ */
if (!empty($_FILES['media']['name'][0])) {

    $rutaBase = $_SERVER['DOCUMENT_ROOT'] . '/bodytech/public/uploads/gimnasios/';

    foreach ($_FILES['media']['tmp_name'] as $index => $tmpName) {

        if ($_FILES['media']['error'][$index] !== UPLOAD_ERR_OK) {
            continue;
        }

        $nombreOriginal = $_FILES['media']['name'][$index];
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

        $media_type = in_array($extension, ['mp4','webm','ogg']) ? 'video' : 'image';

        $nombreFinal = uniqid('gym_') . '.' . $extension;
        $rutaFinal   = $rutaBase . $nombreFinal;

        if (move_uploaded_file($tmpName, $rutaFinal)) {

            $stmt = $pdo->prepare(
                "INSERT INTO gimnasio_media 
                (gimnasio_id, media_path, media_type)
                VALUES (?, ?, ?)"
            );

            $stmt->execute([
                $gimnasio_id,
                $nombreFinal,
                $media_type
            ]);
        }
    }
}

/* ================================
   7️⃣ Redirigir
================================ */
header("Location: index.php");
exit;