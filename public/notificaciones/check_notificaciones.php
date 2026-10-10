<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['count' => 0, 'nuevas' => []]);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$last_id = isset($_GET['last_id']) ? (int) $_GET['last_id'] : 0;

/* =========================
   CONTADOR DE NO LEÍDAS
   (siempre la verdad desde la BD)
========================= */
$stmtCount = $pdo->prepare("
    SELECT COUNT(*)
    FROM notificaciones
    WHERE usuario_id = ?
    AND leida = 0
");
$stmtCount->execute([$usuario_id]);
$count = (int) $stmtCount->fetchColumn();

/* =========================
   NOTIFICACIONES NUEVAS
   (con id mayor al último que ya vio el navegador)
========================= */
$stmt = $pdo->prepare("
    SELECT
        notificaciones.*,
        producto_media.media_path AS producto_imagen,
        gimnasio_media.media_path AS gimnasio_imagen
    FROM notificaciones

    LEFT JOIN producto_media
        ON producto_media.producto_id = notificaciones.referencia_id
        AND notificaciones.tipo = 'producto'
        AND producto_media.id = (
            SELECT MIN(id) FROM producto_media
            WHERE producto_id = notificaciones.referencia_id
        )

    LEFT JOIN gimnasio_media
        ON gimnasio_media.gimnasio_id = notificaciones.referencia_id
        AND notificaciones.tipo = 'gimnasio'
        AND gimnasio_media.id = (
            SELECT MIN(id) FROM gimnasio_media
            WHERE gimnasio_id = notificaciones.referencia_id
        )

    WHERE notificaciones.usuario_id = ?
    AND notificaciones.id > ?
    ORDER BY notificaciones.id ASC
");

$stmt->execute([$usuario_id, $last_id]);

$filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$nuevas = [];

foreach ($filas as $n) {

    $link = '#';
    $imagen = null;

    if ($n['tipo'] === 'producto' && $n['referencia_id']) {

        $link = '/bodytech/public/producto.php?id=' . $n['referencia_id'];

        if (!empty($n['producto_imagen'])) {
            $imagen = '/bodytech/public/uploads/productos/' . $n['producto_imagen'];
        }

    } elseif ($n['tipo'] === 'gimnasio' && $n['referencia_id']) {

        $link = '/bodytech/public/detalle_gimnasio.php?id=' . $n['referencia_id'];

        if (!empty($n['gimnasio_imagen'])) {
            $imagen = '/bodytech/public/uploads/gimnasios/' . $n['gimnasio_imagen'];
        }

    }

    $nuevas[] = [
        'id'         => (int) $n['id'],
        'titulo'     => htmlspecialchars($n['titulo']),
        'mensaje'    => htmlspecialchars($n['mensaje']),
        'link'       => $link,
        'imagen'     => $imagen,
        'created_at' => $n['created_at']
    ];

}

echo json_encode([
    'count'  => $count,
    'nuevas' => $nuevas
]);