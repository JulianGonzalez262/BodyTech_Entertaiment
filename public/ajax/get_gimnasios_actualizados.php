
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

header('Content-Type: application/json');

// 🔥 usar ID como entero seguro
$lastUpdate = isset($_GET['last']) ? (int)$_GET['last'] : 0;

// 🔥 IMPORTANTE: NO salir si es 0
// antes cortabas aquí y eso rompía el realtime

$stmt = $pdo->prepare("
    SELECT *
    FROM gimnasios
    WHERE id > ?
       OR updated_at > NOW() - INTERVAL 10 SECOND
    ORDER BY id ASC
");

$stmt->execute([$lastUpdate]);

$gimnasios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 🔽 media (igual que lo tenías)
foreach ($gimnasios as &$gimnasio) {

    $mediaStmt = $pdo->prepare("
        SELECT media_path, media_type
        FROM gimnasio_media
        WHERE gimnasio_id = ?
    ");

    $mediaStmt->execute([$gimnasio['id']]);
    $gimnasio['media'] = $mediaStmt->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode($gimnasios);
exit;