<?php

session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

if (!isset($_SESSION['usuario_id'])) {

    header('Location: /bodytech/public/login.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT
        email,
        email_verificado,
        documento_verificado,
        tipo_documento,
        numero_documento
    FROM usuarios
    WHERE id = ?
");

$stmt->execute([
    $_SESSION['usuario_id']
]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

$stmtSolicitud = $pdo->prepare("
    SELECT
        estado,
        observaciones,
        created_at,
        updated_at
    FROM solicitudes_verificacion
    WHERE usuario_id = ?
    ORDER BY id DESC
    LIMIT 1
");

$stmtSolicitud->execute([
    $_SESSION['usuario_id']
]);

$solicitud = $stmtSolicitud->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>

    <meta charset="UTF-8">

    <title>
        Seguridad | BodyTech
    </title>

    <link
    rel="stylesheet"
    href="/bodytech/public/assets/css/seguridad.css?v=1"
>

</head>
<body>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/app/views/header.php'; ?>

<main class="account-container">

    <section class="account-content">

        <h1>
            Seguridad de la cuenta
        </h1>

        <h3>
             ㅤCorreo electrónico
        </h3>

        <p>

            <?= htmlspecialchars($usuario['email']) ?>

            <?php if ($usuario['email_verificado']): ?>

                <strong>✅ Verificado</strong>

            <?php else: ?>

                <strong>⚠️ Pendiente</strong>

            <?php endif; ?>

        </p>

        <hr>

        <h3>
         ㅤIdentidad
        </h3>

        <?php if ($usuario['documento_verificado']): ?>

<div class="estado-verificacion aprobado">

    <h4>✅ Identidad verificada</h4>

    <p>

        Tu documento fue aprobado correctamente.

    </p>

    <p>

        Tu cuenta ahora posee identidad verificada.

    </p>

    <?php if($solicitud): ?>

        <small>

            Fecha de aprobación:

            <?= htmlspecialchars($solicitud['updated_at']) ?>

        </small>

    <?php endif; ?>

</div>

<?php elseif(

    $solicitud &&
    $solicitud['estado'] === 'pendiente'

): ?>

<div class="estado-verificacion pendiente">

    <h4> Documento en revisión</h4>

    <p>

        Tu documento fue recibido correctamente.

    </p>

    <p>

        Actualmente está siendo revisado por un administrador.

    </p>

    <small>

        Enviado el:

        <?= htmlspecialchars($solicitud['created_at']) ?>

    </small>

</div>

<?php elseif(

    $solicitud &&
    $solicitud['estado'] === 'rechazada'

): ?>

<div class="estado-verificacion rechazada">

    <h4>❌ Documento rechazado</h4>

    <p>

        El administrador encontró un inconveniente con el documento enviado.

    </p>

    <?php if(!empty($solicitud['observaciones'])): ?>

        <strong>

            Motivo:

        </strong>

        <p>

            <?= nl2br(htmlspecialchars($solicitud['observaciones'])) ?>

        </p>

    <?php endif; ?>

    <a

        href="/bodytech/public/usuario/subir_verificacion.php"

        class="account-option"

    >

        🔄 Volver a enviar documento

    </a>

</div>

<?php else: ?>

<div class="estado-verificacion">

    <h4> Identidad no verificada</h4>

    <p>

        Aún no has enviado ningún documento.

    </p>

    <p>

        La verificación es opcional.

    </p>

    <p>

        Una vez aprobada obtendrás la insignia de identidad verificada.

    </p>

    <a

        href="/bodytech/public/usuario/subir_verificacion.php"

        class="account-option"

    >

        Verificar identidad

    </a>

</div>

<?php endif; ?>

        <hr>
        <hr>

<h3>
     ㅤCambiar contraseña
</h3>

<p>

    Mantén tu cuenta protegida actualizando periódicamente tu contraseña.

</p>

<a

    href="/bodytech/public/usuario/cambiar_password.php"

    class="edit-profile-btn"

>

     Cambiar contraseña

</a>

<hr>

<h3>
    ㅤAutenticación en dos pasos
</h3>

<p>

    Añade una capa adicional de seguridad a tu cuenta.
    Cada vez que inicies sesión se solicitará un código de verificación.

</p>

<a

    href="/bodytech/public/usuario/dos_factores.php"

    class="account-option"

>

     Administrar autenticación en dos pasos

</a>

       

        <h2>Eliminar Cuenta</h2>

    <p>
        Si eliminas tu cuenta perderás permanentemente el acceso a BodyTech.
        Esta acción no podrá deshacerse.
    </p>

    <a
        href="/bodytech/public/usuario/eliminar_cuenta.php"
        class="btn-danger"
    >
        Eliminar mi cuenta
    </a>


    </section>

</main>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/bodytech/app/views/footer.php'; ?>

</body>
</html>