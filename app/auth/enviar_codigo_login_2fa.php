<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/services/MailService.php';

function enviarCodigoLogin2FA($usuarioId)
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
        AND tipo = '2fa_email'
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
            '2fa_email',
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

    <h2>Inicio de sesión protegido</h2>

    <p>

    Hola {$usuario['nombre']}.

    </p>

    <p>

    Tu código para iniciar sesión es:

    </p>

    <h1 style='letter-spacing:5px;'>

    {$codigo}

    </h1>

    <p>

    Este código expirará en 10 minutos.

    </p>

    <p>

    Si no intentaste iniciar sesión, ignora este correo.

    </p>

    ";

    enviarCorreo(

        $usuario['email'],

        'Código de acceso - BodyTech',

        $html

    );

    return true;
}