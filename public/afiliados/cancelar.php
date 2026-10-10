<?php
session_start();
require_once '../../app/config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    die('Acceso denegado.');
}

$usuario_id = $_SESSION['usuario_id'];

try {
    // 1️⃣ Obtener el afiliado_id del usuario
    $stmt = $pdo->prepare("
        SELECT id 
        FROM afiliados 
        WHERE usuario_id = :usuario_id
    ");
    $stmt->execute(['usuario_id' => $usuario_id]);
    $afiliado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($afiliado) {

        // 2️⃣ BORRAR planes automáticos asociados (SOLO AUTOMÁTICOS)
        $stmt = $pdo->prepare("
            DELETE FROM planes_automaticos
            WHERE afiliado_id = :afiliado_id
        ");
        $stmt->execute([
            'afiliado_id' => $afiliado['id']
        ]);
    }

    // 3️⃣ TU CÓDIGO ORIGINAL — INTACTO
    $stmt = $pdo->prepare("
        DELETE FROM afiliados
        WHERE usuario_id = :usuario_id
    ");
    $stmt->execute(['usuario_id' => $usuario_id]);

    header('Location: /bodytech/public/afiliados/index.php?cancelada=1');
    exit;

} catch (Exception $e) {
    die('Error al cancelar afiliación: ' . $e->getMessage());
}
