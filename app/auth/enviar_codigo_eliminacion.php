<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/services/MailService.php';

function enviarCodigoEliminacion($usuarioId)
{
    global $pdo;

    /*
    |--------------------------------------------------------------------------
    | Obtener usuario
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT email, nombre
        FROM usuarios
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$usuarioId]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {

        return false;

    }

    /*
    |--------------------------------------------------------------------------
    | Invalidar códigos anteriores
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE codigos_verificacion
        SET usado = 1
        WHERE usuario_id = ?
        AND tipo = 'eliminar_cuenta'
        AND usado = 0
    ");

    $stmt->execute([$usuarioId]);

    /*
    |--------------------------------------------------------------------------
    | Generar nuevo código
    |--------------------------------------------------------------------------
    */

    $codigo = random_int(100000, 999999);

    $expires = date(
        'Y-m-d H:i:s',
        strtotime('+10 minutes')
    );

    $stmt = $pdo->prepare("
        INSERT INTO codigos_verificacion
        (
            usuario_id,
            tipo,
            codigo,
            expires_at
        )
        VALUES
        (
            ?,
            'eliminar_cuenta',
            ?,
            ?
        )
    ");

    $stmt->execute([
        $usuarioId,
        $codigo,
        $expires
    ]);




    /*
    |--------------------------------------------------------------------------
    | Correo
    |--------------------------------------------------------------------------
    */

    $html = "

    <h2>Confirmación de eliminación de cuenta</h2>

    <p>

    Hola {$usuario['nombre']}.

    </p>

    <p>

    Hemos recibido una solicitud para eliminar permanentemente tu cuenta de BodyTech.

    </p>

    <p>

    Tu código de confirmación es:

    </p>

    <h1 style='letter-spacing:5px;'>

    {$codigo}

    </h1>

    <p>

    Este código expirará en 10 minutos.

    </p>

    <p>

    Si NO solicitaste eliminar tu cuenta, ignora este correo.
    Nadie podrá eliminarla sin este código.

    </p>

    ";

    enviarCorreo(

        $usuario['email'],

        'Confirmación de eliminación de cuenta - BodyTech',

        $html

    );

    return true;
}
/*
|--------------------------------------------------------------------------
| ENVIAR SEGUNDO CÓDIGO PARA ELIMINACIÓN DE CUENTA
|--------------------------------------------------------------------------
*/

function enviarCodigoEliminacion2FA($usuarioId)
{
    global $pdo;

    /*
    |--------------------------------------------------------------------------
    | Obtener usuario
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT email, nombre
        FROM usuarios
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([
        $usuarioId
    ]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {

        return false;

    }

    /*
    |--------------------------------------------------------------------------
    | Invalidar códigos anteriores del segundo factor
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE codigos_verificacion
        SET usado = 1
        WHERE usuario_id = ?
        AND tipo = 'eliminar_cuenta_2fa'
        AND usado = 0
    ");

    $stmt->execute([
        $usuarioId
    ]);

    /*
    |--------------------------------------------------------------------------
    | Generar código
    |--------------------------------------------------------------------------
    */

    $codigo = random_int(100000, 999999);

    $expires = date(
        'Y-m-d H:i:s',
        strtotime('+10 minutes')
    );

    /*
    |--------------------------------------------------------------------------
    | Guardar código
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO codigos_verificacion
        (
            usuario_id,
            tipo,
            codigo,
            expires_at
        )
        VALUES
        (
            ?,
            'eliminar_cuenta_2fa',
            ?,
            ?
        )
    ");

    $stmt->execute([
        $usuarioId,
        $codigo,
        $expires
    ]);

    /*
    |--------------------------------------------------------------------------
    | Correo
    |--------------------------------------------------------------------------
    */

    $html = "

    <h2>Segunda verificación de seguridad</h2>

    <p>
        Hola {$usuario['nombre']}.
    </p>

    <p>
        Se ha solicitado la eliminación permanente de tu cuenta de BodyTech.
    </p>

    <p>
        Como tienes activada la autenticación en dos pasos mediante correo,
        necesitamos una segunda confirmación.
    </p>

    <p>
        Tu segundo código de verificación es:
    </p>

    <h1 style='letter-spacing:5px;'>
        {$codigo}
    </h1>

    <p>
        Este código expirará en 10 minutos.
    </p>

    <p>
        Si no solicitaste la eliminación de tu cuenta,
        ignora este mensaje y revisa la seguridad de tu cuenta.
    </p>

    ";

    $resultado = enviarCorreo(
        $usuario['email'],
        'Segunda verificación - Eliminación de cuenta | BodyTech',
        $html
    );

    return $resultado !== false;
}