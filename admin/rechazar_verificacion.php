<?php

require_once __DIR__ . '/admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

/*==========================================
=            VALIDACIONES                  =
==========================================*/

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {
    die('Solicitud inválida.');
}

$idSolicitud = (int) $_GET['id'];

$observaciones = trim($_POST['observaciones'] ?? '');

/*==========================================
=      OBTENER LA SOLICITUD                =
==========================================*/

$stmt = $pdo->prepare("
    SELECT
        id,
        usuario_id,
        imagen_documento,
        estado
    FROM solicitudes_verificacion
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([
    $idSolicitud
]);

$solicitud = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$solicitud) {
    die('La solicitud no existe.');
}

if ($solicitud['estado'] !== 'pendiente') {
    die('Esta solicitud ya fue procesada.');
}

$pdo->beginTransaction();

try {
    /*==========================================
    =        RECHAZAR LA SOLICITUD            =
    ==========================================*/

    $stmt = $pdo->prepare("
        UPDATE solicitudes_verificacion
        SET
            estado = 'rechazada',
            observaciones = ?,
            imagen_documento = NULL
        WHERE id = ?
    ");

    $stmt->execute([
        $observaciones,
        $idSolicitud
    ]);

    /*==========================================
    =        CREAR NOTIFICACIÓN               =
    ==========================================*/

    $titulo = 'Verificación de identidad rechazada';

    $mensaje = "La verificación de tu documento no pudo ser aprobada.

Motivo del administrador:

" . $observaciones . "

Puedes ingresar nuevamente al apartado de seguridad de tu cuenta y enviar una nueva fotografía de tu documento.";

    $stmt = $pdo->prepare("
        INSERT INTO notificaciones
        (
            usuario_id,
            titulo,
            mensaje,
            tipo
        )
        VALUES
        (
            ?,
            ?,
            ?,
            'verificacion'
        )
    ");

    $stmt->execute([
        $solicitud['usuario_id'],
        $titulo,
        $mensaje
    ]);

    /*==========================================
    =       ELIMINAR DOCUMENTO FÍSICO         =
    ==========================================*/

    $rutaArchivo =
        $_SERVER['DOCUMENT_ROOT']
        . '/bodytech/public/uploads/verificaciones/'
        . $solicitud['imagen_documento'];

    if (
        is_file($rutaArchivo)
    ) {

        @unlink($rutaArchivo);

    }

    /*==========================================
    =       CONFIRMAR TRANSACCIÓN             =
    ==========================================*/

    $pdo->commit();

    header(
        'Location: verificaciones.php?rechazada=1'
    );

    exit;

}
catch(Exception $e){

    $pdo->rollBack();

    die(
        'Error al rechazar la solicitud: '
        . $e->getMessage()
    );

}