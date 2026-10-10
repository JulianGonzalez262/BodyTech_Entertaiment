<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/admin/admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

// Validar ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$gimnasio_id = (int) $_GET['id'];
$admin_id   = $_SESSION['admin_id'];

// Obtener estado actual (y asegurar que es del admin)
$sql = "SELECT activo FROM gimnasios 
        WHERE id = :id AND created_by = :admin_id";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':id' => $gimnasio_id,
    ':admin_id' => $admin_id
]);

$gimnasio = $stmt->fetch();

if (!$gimnasio) {
    // No existe o no pertenece al admin
    header('Location: index.php');
    exit;
}

// Alternar estado
$nuevo_estado = $gimnasio['activo'] ? 0 : 1;

$update = $pdo->prepare(
    "UPDATE gimnasios 
     SET activo = :activo,
         updated_at = NOW()
     WHERE id = :id"
);
$update->execute([
    ':activo' => $nuevo_estado,
    ':id'     => $gimnasio_id
]);

// Volver al listado
header('Location: index.php');
exit;