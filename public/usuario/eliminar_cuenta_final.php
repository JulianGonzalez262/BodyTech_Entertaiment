<?php

session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

/*
|--------------------------------------------------------------------------
| SOLO POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: /bodytech/public/usuario/mi_cuenta.php');
    exit;

}

/*
|--------------------------------------------------------------------------
| VALIDAR AUTORIZACIÓN DEL PROCESO
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['usuario_id']) ||
    !isset($_SESSION['eliminacion_codigo_verificado']) ||
    $_SESSION['eliminacion_codigo_verificado'] !== true ||
    !isset($_SESSION['eliminacion_usuario_id']) ||
    $_SESSION['eliminacion_usuario_id'] !== $_SESSION['usuario_id']
) {

    die('El proceso de eliminación no está autorizado.');

}

$usuario_id = $_SESSION['usuario_id'];

/*
|--------------------------------------------------------------------------
| CONFIRMACIÓN FINAL
|--------------------------------------------------------------------------
*/

if (
    !isset($_POST['confirmar']) ||
    $_POST['confirmar'] !== '1'
) {

    die('La eliminación no fue confirmada.');

}

/*
|--------------------------------------------------------------------------
| OBTENER CONFIGURACIÓN 2FA
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        dos_factores_activo
    FROM usuarios
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([
    $usuario_id
]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {

    die('El usuario no existe.');

}

/*
|--------------------------------------------------------------------------
| SI TIENE 2FA, DEBE HABER SIDO VERIFICADO
|--------------------------------------------------------------------------
*/

if ((int)$usuario['dos_factores_activo'] === 1) {

    if (
        !isset($_SESSION['eliminacion_2fa_verificado']) ||
        $_SESSION['eliminacion_2fa_verificado'] !== true ||
        !isset($_SESSION['eliminacion_2fa_usuario_id']) ||
        $_SESSION['eliminacion_2fa_usuario_id'] !== $usuario_id
    ) {

        die('La segunda verificación de seguridad no ha sido completada.');

    }

}

/*
|--------------------------------------------------------------------------
| REVALIDAR CONDICIONES
|--------------------------------------------------------------------------
*/

$errores = [];

/*
|--------------------------------------------------------------------------
| MEMBRESÍA ACTIVA
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM afiliados
    WHERE usuario_id = ?
    AND estado = 'activo'
");

$stmt->execute([
    $usuario_id
]);

if ($stmt->fetchColumn() > 0) {

    $errores[] = 'Tienes una membresía activa.';

}

/*
|--------------------------------------------------------------------------
| PAGOS PENDIENTES
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM pagos
    WHERE usuario_id = ?
    AND estado = 'pendiente'
");

$stmt->execute([
    $usuario_id
]);

if ($stmt->fetchColumn() > 0) {

    $errores[] = 'Tienes pagos pendientes.';

}

/*
|--------------------------------------------------------------------------
| PEDIDOS PENDIENTES
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM ordenes
    WHERE usuario_id = ?
    AND estado_orden IN ('pendiente', 'procesando')
");

$stmt->execute([
    $usuario_id
]);

if ($stmt->fetchColumn() > 0) {

    $errores[] = 'Tienes pedidos pendientes.';

}

/*
|--------------------------------------------------------------------------
| PQRS ABIERTOS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM pqrs
    WHERE usuario_id = ?
    AND estado IN ('pendiente', 'en_proceso')
");

$stmt->execute([
    $usuario_id
]);

if ($stmt->fetchColumn() > 0) {

    $errores[] = 'Tienes solicitudes PQRS abiertas.';

}

/*
|--------------------------------------------------------------------------
| DETENER PROCESO
|--------------------------------------------------------------------------
*/

if (!empty($errores)) {

    echo '<h1>No es posible eliminar la cuenta.</h1>';

    echo '<ul>';

    foreach ($errores as $error) {

        echo '<li>' .
            htmlspecialchars($error) .
            '</li>';

    }

    echo '</ul>';

    echo '
        <a href="/bodytech/public/usuario/mi_cuenta.php">
            Volver a Mi Cuenta
        </a>
    ';

    exit;

}

/*
|--------------------------------------------------------------------------
| VERIFICAR RELACIONES NO CASCADE
|--------------------------------------------------------------------------
|
| Estas tablas pueden impedir el DELETE:
|
| gimnasios
| pagos
| planes_automaticos
| planes_entrenamiento
| productos
|
| Para esta cuenta ya comprobamos que todas están en 0.
|
|--------------------------------------------------------------------------
*/

$bloqueos = [];

/*
|--------------------------------------------------------------------------
| GIMNASIOS CREADOS POR EL USUARIO
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM gimnasios
    WHERE created_by = ?
");

$stmt->execute([
    $usuario_id
]);

if ($stmt->fetchColumn() > 0) {

    $bloqueos[] =
        'El usuario tiene gimnasios asociados como creador.';

}



/*
|--------------------------------------------------------------------------
| PLANES AUTOMÁTICOS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM planes_automaticos
    WHERE usuario_id = ?
");

$stmt->execute([
    $usuario_id
]);

if ($stmt->fetchColumn() > 0) {

    $bloqueos[] =
        'El usuario tiene planes automáticos asociados.';

}

/*
|--------------------------------------------------------------------------
| PLANES DE ENTRENAMIENTO
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM planes_entrenamiento
    WHERE created_by = ?
");

$stmt->execute([
    $usuario_id
]);

if ($stmt->fetchColumn() > 0) {

    $bloqueos[] =
        'El usuario tiene planes de entrenamiento asociados.';

}

/*
|--------------------------------------------------------------------------
| PRODUCTOS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM productos
    WHERE created_by = ?
");

$stmt->execute([
    $usuario_id
]);

if ($stmt->fetchColumn() > 0) {

    $bloqueos[] =
        'El usuario tiene productos asociados como creador.';

}

/*
|--------------------------------------------------------------------------
| SI EXISTE ALGÚN BLOQUEO, NO SE ELIMINA
|--------------------------------------------------------------------------
*/

if (!empty($bloqueos)) {

    echo '<h1>No es posible eliminar la cuenta.</h1>';

    echo '<p>Existen registros que deben conservarse antes de eliminar la cuenta.</p>';

    echo '<ul>';

    foreach ($bloqueos as $bloqueo) {

        echo '<li>' .
            htmlspecialchars($bloqueo) .
            '</li>';

    }

    echo '</ul>';

    echo '
        <a href="/bodytech/public/usuario/mi_cuenta.php">
            Volver a Mi Cuenta
        </a>
    ';

    exit;

}

/*
|--------------------------------------------------------------------------
| OBTENER FOTO DE PERFIL
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT foto_perfil
    FROM usuarios
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([
    $usuario_id
]);

$fotoPerfil = $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| ELIMINACIÓN Y BLOQUEO PERMANENTE DEL CORREO
|--------------------------------------------------------------------------
*/

try {

    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | OBTENER EL CORREO DE LA CUENTA
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT email
        FROM usuarios
        WHERE id = ?
        LIMIT 1
        FOR UPDATE
    ");

    $stmt->execute([
        $usuario_id
    ]);

    $emailUsuario = $stmt->fetchColumn();

    if ($emailUsuario === false || trim($emailUsuario) === '') {
        throw new Exception(
            'No fue posible obtener el correo de la cuenta.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | REGISTRAR EL CORREO PARA BLOQUEARLO PERMANENTEMENTE
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO correos_cuentas_eliminadas (email)
        VALUES (?)
    ");

    $stmt->execute([
        mb_strtolower(trim($emailUsuario), 'UTF-8')
    ]);

    /*
    |--------------------------------------------------------------------------
    | ELIMINAR USUARIO
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        DELETE FROM usuarios
        WHERE id = ?
    ");

    $stmt->execute([
        $usuario_id
    ]);

    /*
    |--------------------------------------------------------------------------
    | VERIFICAR QUE REALMENTE SE ELIMINÓ
    |--------------------------------------------------------------------------
    */

    if ($stmt->rowCount() !== 1) {
        throw new Exception(
            'La cuenta no pudo eliminarse.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIRMAR AMBAS OPERACIONES
    |--------------------------------------------------------------------------
    */

    $pdo->commit();


    /*
    |--------------------------------------------------------------------------
    | ELIMINAR FOTO PERSONAL
    |--------------------------------------------------------------------------
    */

    if (
        !empty($fotoPerfil) &&
        strpos($fotoPerfil, '/bodytech/public/') === 0 &&
        strpos($fotoPerfil, 'default-avatar.png') === false
    ) {

        $rutaFoto =
            $_SERVER['DOCUMENT_ROOT'] . $fotoPerfil;

        if (is_file($rutaFoto)) {

            @unlink($rutaFoto);

        }

    }

    /*
    |--------------------------------------------------------------------------
    | DESTRUIR SESIÓN COMPLETAMENTE
    |--------------------------------------------------------------------------
    */
    $_SESSION['cuenta_eliminada_exitosamente'] = true;
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {

        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );

    }

    session_destroy();

    /*
    |--------------------------------------------------------------------------
    | RESULTADO
    |--------------------------------------------------------------------------
    */

    header(
        'Location: /bodytech/public/usuario/cuenta_eliminada.php'
    );

    exit;

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        'Error al eliminar cuenta ID ' . $usuario_id .
        ': ' . $e->getMessage()
    );

    http_response_code(500);

    echo '<h1>No fue posible eliminar la cuenta</h1>';
    echo '<p>La operación fue cancelada.</p>';
    echo '<p><strong>Error técnico:</strong> ' .
        htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') .
        '</p>';
}
 