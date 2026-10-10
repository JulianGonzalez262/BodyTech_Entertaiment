<?php

session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/auth/enviar_codigo_eliminacion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: /bodytech/public/login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

$errores = [];
$codigoVerificado = false;

/* ==========================================
   VALIDAR CÓDIGO DE ELIMINACIÓN
========================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $codigo = trim($_POST['codigo'] ?? '');

    if (!preg_match('/^\d{6}$/', $codigo)) {

        $errores[] = "El código debe tener exactamente 6 dígitos.";

    } else {

        $stmt = $pdo->prepare("
            SELECT
                id,
                codigo,
                usado,
                expires_at
            FROM codigos_verificacion
            WHERE usuario_id = ?
            AND tipo = 'eliminar_cuenta'
            AND codigo = ?
            AND usado = 0
            AND expires_at > NOW()
            ORDER BY id DESC
            LIMIT 1
        ");

        $stmt->execute([
            $usuario_id,
            $codigo
        ]);

        $codigoRegistro = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$codigoRegistro) {

            $errores[] =
                "El código es incorrecto, ya fue utilizado o ha expirado.";

        } else {

            /* ==========================================
               MARCAR CÓDIGO COMO UTILIZADO
            ========================================== */

            $stmt = $pdo->prepare("
                UPDATE codigos_verificacion
                SET usado = 1
                WHERE id = ?
            ");

            $stmt->execute([
                $codigoRegistro['id']
            ]);

            /* ==========================================
               CÓDIGO DE CORREO VERIFICADO
            ========================================== */

            $codigoVerificado = true;

            $_SESSION['eliminacion_codigo_verificado'] = true;
            $_SESSION['eliminacion_usuario_id'] = $usuario_id;

            /* ==========================================
               COMPROBAR CONFIGURACIÓN 2FA
            ========================================== */

            $stmt = $pdo->prepare("
                SELECT
                    dos_factores_activo,
                    dos_factores_metodo
                FROM usuarios
                WHERE id = ?
                LIMIT 1
            ");

            $stmt->execute([
                $usuario_id
            ]);

            $config2FA = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$config2FA) {

                $errores[] =
                    "No fue posible verificar la configuración de seguridad de tu cuenta.";

            } elseif ((int)$config2FA['dos_factores_activo'] === 1) {

                /* ==========================================
                   2FA ACTIVO
                ========================================== */

                switch ($config2FA['dos_factores_metodo']) {

                    case 'authenticator':

                        header(
                            "Location: /bodytech/public/usuario/confirmar_eliminacion_authenticator.php"
                        );

                        exit;

                    case 'email':

                        require_once $_SERVER['DOCUMENT_ROOT']
                            . '/bodytech/app/auth/enviar_codigo_eliminacion.php';

                        if (!enviarCodigoEliminacion2FA($usuario_id)) {

                            $errores[] =
                                "No fue posible enviar el segundo código de verificación.";

                            break;
                        }

                        header(
                            "Location: /bodytech/public/usuario/confirmar_eliminacion_2fa_email.php"
                        );

                        exit;

                    
                    default:

                        $errores[] =
                            "El método de autenticación de dos factores no es válido.";

                        break;
                }

            } elseif ((int)$config2FA['dos_factores_activo'] === 0) {

                /*
                 * USUARIO SIN 2FA ACTIVADO
                 * El código de correo ya fue verificado.
                 */
                header(
                    "Location: /bodytech/public/usuario/confirmacion_final_eliminacion.php"
                );

                exit;

            } else {

                $errores[] =
                    "La configuración de seguridad de tu cuenta no es válida.";
            }
        }
    }
}


/* ==========================================
   VALIDAR MEMBRESÍA ACTIVA
========================================== */

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

    $errores[] = "Tienes una membresía activa.";

}


/* ==========================================
   VALIDAR PAGOS PENDIENTES
========================================== */

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

    $errores[] = "Tienes pagos pendientes.";

}


/* ==========================================
   VALIDAR PEDIDOS PENDIENTES
========================================== */

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM ordenes
    WHERE usuario_id = ?
    AND (
        estado_orden = 'procesando'
        OR (
            estado_orden = 'pendiente'
            AND estado_pago = 'pendiente'
        )
    )
");

$stmt->execute([
    $usuario_id
]);

if ($stmt->fetchColumn() > 0) {

    $errores[] =
        "Tienes pedidos pendientes de completar.";

}


/* ==========================================
   VALIDAR PQRS ABIERTOS
========================================== */

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

    $errores[] =
        "Tienes solicitudes PQRS abiertas.";

}


/* ==========================================
   ENVIAR CÓDIGO SOLAMENTE AL INICIAR
   EL PROCESO
========================================== */

$codigoEnviado = false;

if (
    $_SERVER['REQUEST_METHOD'] === 'GET'
    && empty($errores)
) {

    if (enviarCodigoEliminacion($usuario_id)) {

        $codigoEnviado = true;

    } else {

        $errores[] =
            "No fue posible enviar el código de verificación.";

    }
}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Validación previa | BodyTech</title>

    <link
        rel="stylesheet"
        href="/bodytech/public/assets/css/confirmar_codigo_eliminacion.css"
    >

</head>

<body>

<main class="verification-container">

    <section class="verification-card">

        <?php if (!empty($errores)): ?>

            <div class="status-icon error-icon">
                ⚠️
            </div>

            <h1>
                No puedes eliminar tu cuenta
            </h1>

            <p class="verification-description">
                Antes de continuar con la eliminación,
                BodyTech necesita que cumplas las condiciones
                de seguridad indicadas.
            </p>

            <div class="error-box">

                <h2>
                    Se encontraron los siguientes inconvenientes:
                </h2>

                <ul>

                    <?php foreach ($errores as $error): ?>

                        <li>
                            <?= htmlspecialchars($error) ?>
                        </li>

                    <?php endforeach; ?>

                </ul>

            </div>

            <a
                href="/bodytech/public/usuario/eliminar_cuenta.php"
                class="btn-secondary"
            >
                ← Volver
            </a>

        <?php else: ?>

            <?php if (!$codigoVerificado): ?>

                <div class="status-icon success-icon">
                    ✉️
                </div>

                <h1>
                    Validación de seguridad
                </h1>

                <p class="verification-description">
                    Todo está correcto para iniciar el proceso
                    de eliminación de tu cuenta.
                </p>

                <div class="info-box">

                    <span class="info-icon">
                        🔐
                    </span>

                    <div>

                        <strong>
                            Código de verificación enviado
                        </strong>

                        <p>
                            Hemos enviado un código de 6 dígitos
                            al correo electrónico asociado a tu cuenta.
                        </p>

                    </div>

                </div>

                <form
                    method="POST"
                    class="verification-form"
                >

                    <label for="codigo">
                        Código de verificación
                    </label>

                    <input
                        type="text"
                        id="codigo"
                        name="codigo"
                        maxlength="6"
                        pattern="[0-9]{6}"
                        inputmode="numeric"
                        placeholder="000000"
                        required
                        autocomplete="one-time-code"
                    >

                    <p class="input-help">
                        Ingresa los 6 dígitos recibidos por correo.
                    </p>

                    <button
                        type="submit"
                        class="btn-primary"
                    >
                        Verificar código
                    </button>

                </form>

                <a
                    href="/bodytech/public/usuario/eliminar_cuenta.php"
                    class="back-link"
                >
                    ← Cancelar proceso
                </a>

            <?php endif; ?>

        <?php endif; ?>

    </section>

</main>

</body>

</html>