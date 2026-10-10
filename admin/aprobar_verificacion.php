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
=       OBTENER LA SOLICITUD               =
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

if(!$solicitud){

    die('La solicitud no existe.');

}

if($solicitud['estado'] !== 'pendiente'){

    die('Esta solicitud ya fue procesada.');

}

/*==========================================
=          INICIAR TRANSACCIÓN             =
==========================================*/

$pdo->beginTransaction();

try{/*==========================================
    =      APROBAR LA SOLICITUD              =
    ==========================================*/

    $stmt = $pdo->prepare("
        UPDATE solicitudes_verificacion
        SET
            estado = 'aprobada',
            observaciones = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $observaciones,
        $idSolicitud
    ]);

    /*==========================================
    =      MARCAR USUARIO VERIFICADO         =
    ==========================================*/

    $stmt = $pdo->prepare("
        UPDATE usuarios
        SET documento_verificado = 1
        WHERE id = ?
    ");

    $stmt->execute([
        $solicitud['usuario_id']
    ]);

    /*==========================================
    =         CREAR NOTIFICACIÓN             =
    ==========================================*/

    $titulo = 'Identidad verificada';

    $mensaje = 'Tu documento fue revisado y aprobado correctamente. Tu cuenta ahora cuenta con identidad verificada.';

    $stmt = $pdo->prepare("
        INSERT INTO notificaciones
        (
            usuario_id,
            titulo,
            mensaje
        )
        VALUES
        (
            ?,
            ?,
            ?
        )
    ");

    $stmt->execute([
        $solicitud['usuario_id'],
        $titulo,
        $mensaje
    ]);

    /*==========================================
    =      ELIMINAR DOCUMENTO FÍSICO         =
    ==========================================*/

    $rutaArchivo =
        $_SERVER['DOCUMENT_ROOT']
        .
        '/bodytech/public/uploads/verificaciones/'
        .
        $solicitud['imagen_documento'];

    if(
        file_exists($rutaArchivo)
    ){

        unlink($rutaArchivo);

    }

    /*==========================================
    =          CONFIRMAR CAMBIOS             =
    ==========================================*/

    $pdo->commit();

    header(
        'Location: verificaciones.php?ok=1'
    );

    exit;

}catch(Exception $e){

    $pdo->rollBack();

    die(
        'Error al aprobar la solicitud: '
        .
        $e->getMessage()
    );

}