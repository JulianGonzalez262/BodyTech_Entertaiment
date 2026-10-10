<?php

require_once __DIR__ . '/admin_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/database.php';

/*==========================================
=            VALIDAR SOLICITUD             =
==========================================*/

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {

    die('Solicitud inválida.');

}

$idSolicitud = (int) $_GET['id'];

/*==========================================
=        OBTENER INFORMACIÓN               =
==========================================*/

$stmt = $pdo->prepare("
SELECT

    sv.id,
    sv.usuario_id,
    sv.imagen_documento,
    sv.estado,
    sv.observaciones,
    sv.created_at,

    u.nombre,
    u.nombres,
    u.apellidos,
    u.email,
    u.foto_perfil,
    u.tipo_documento,
    u.numero_documento,
    u.documento_verificado

FROM solicitudes_verificacion sv

INNER JOIN usuarios u

ON u.id = sv.usuario_id

WHERE sv.id = ?

LIMIT 1
");

$stmt->execute([
    $idSolicitud
]);

$solicitud = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$solicitud) {

    die('La solicitud no existe.');

}

/*==========================================
=          RUTA DE LA IMAGEN              =
==========================================*/

$rutaImagen =
'/bodytech/public/uploads/verificaciones/'
.
$solicitud['imagen_documento'];

/*==========================================
=          ESTADO VISUAL                  =
==========================================*/

$claseEstado = '';

switch($solicitud['estado']){

    case 'pendiente':

        $claseEstado = 'estado-pendiente';

    break;

    case 'aprobada':

        $claseEstado = 'estado-aprobada';

    break;

    case 'rechazada':

        $claseEstado = 'estado-rechazada';

    break;

}

/*==========================================
=              HTML                       =
==========================================*/

?>

<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">

<title>

Ver Documento

</title>

<style>

body{

    margin:0;
    padding:35px;
    background:#eef2f7;
    font-family:Arial, Helvetica, sans-serif;

}

.contenedor{

    max-width:1200px;
    margin:auto;

}

.tarjeta{

    background:white;

    border-radius:12px;

    padding:30px;

    box-shadow:0 6px 18px rgba(0,0,0,.08);

}

h1{

    margin-top:0;

}

.info{

    display:grid;

    grid-template-columns:220px 1fr;

    gap:15px;

    margin-top:25px;

}

.label{

    font-weight:bold;

    color:#334155;

}

.valor{

    color:#111827;

}

.estado-pendiente{

    color:#d97706;

    font-weight:bold;

}

.estado-aprobada{

    color:#15803d;

    font-weight:bold;

}

.estado-rechazada{

    color:#dc2626;

    font-weight:bold;

}

hr{

    margin:35px 0;

}

.imagen-documento{

    width:100%;

    max-width:750px;

    border-radius:10px;

    border:2px solid #d1d5db;

    display:block;

    margin:auto;

}

.foto-perfil{

    width:90px;

    height:90px;

    border-radius:50%;

    object-fit:cover;

    border:3px solid #2563eb;

}

textarea{

    width:100%;

    min-height:130px;

    resize:vertical;

    padding:12px;

    border-radius:8px;

    border:1px solid #cbd5e1;

    font-size:15px;

    box-sizing:border-box;

}

.botones{

    display:flex;

    gap:15px;

    margin-top:25px;

}

.btn{

    padding:12px 24px;

    border:none;

    border-radius:8px;

    color:white;

    cursor:pointer;

    font-size:15px;

    font-weight:bold;

}

.aprobar{

    background:#16a34a;

}

.rechazar{

    background:#dc2626;

}

.volver{

    display:inline-block;

    margin-bottom:20px;

    text-decoration:none;

    color:#2563eb;

    font-weight:bold;

}

</style>

</head>

<body>

<div class="contenedor">

<a
class="volver"
href="verificaciones.php">

⬅️ Volver al listado

</a>

<div class="tarjeta">

<h1>

🪪 Revisión de documento

</h1>

<div class="info">

<div class="label">

Foto de perfil

</div>

<div class="valor">

<?php if(!empty($solicitud['foto_perfil'])): ?>

<img
class="foto-perfil"
src="/bodytech/public/uploads/perfiles/<?= htmlspecialchars($solicitud['foto_perfil']) ?>"
alt="Foto">

<?php else: ?>

Sin fotografía

<?php endif; ?>

</div>

<div class="label">

Nombre

</div>

<div class="valor">

<?= htmlspecialchars($solicitud['nombre']) ?>

</div>

<div class="label">

Nombres

</div>

<div class="valor">

<?= htmlspecialchars($solicitud['nombres']) ?>

</div>

<div class="label">

Apellidos

</div>

<div class="valor">

<?= htmlspecialchars($solicitud['apellidos']) ?>

</div>

<div class="label">

Correo

</div>

<div class="valor">

<?= htmlspecialchars($solicitud['email']) ?>

</div>

<div class="label">

Tipo documento

</div>

<div class="valor">

<?= htmlspecialchars($solicitud['tipo_documento']) ?>

</div>

<div class="label">

Número documento

</div>

<div class="valor">

<?= htmlspecialchars($solicitud['numero_documento']) ?>

</div>

<div class="label">

Estado

</div>

<div class="valor">

<span class="<?= $claseEstado ?>">

<?= strtoupper($solicitud['estado']) ?>

</span>

</div>

<div class="label">

Fecha solicitud

</div>

<div class="valor">

<?= $solicitud['created_at'] ?>

</div>

</div>

<hr>

<h2>

Documento enviado

</h2>

<img
class="imagen-documento"
src="<?= $rutaImagen ?>"
alt="Documento">

<hr>

<form
method="POST">

<h2>

Observaciones del administrador

</h2>

<textarea
name="observaciones"><?= htmlspecialchars($solicitud['observaciones']) ?></textarea>

<br><br>

<?php if($solicitud['estado'] === 'pendiente'): ?>

<div class="botones">

    <button
        type="submit"
        class="btn aprobar"
        formaction="aprobar_verificacion.php?id=<?= $solicitud['id'] ?>"
    >
        ✅ Aprobar documento
    </button>

    <button
        type="submit"
        class="btn rechazar"
        formaction="rechazar_verificacion.php?id=<?= $solicitud['id'] ?>"
    >
        ❌ Rechazar documento
    </button>

</div>

<?php elseif($solicitud['estado'] === 'aprobada'): ?>

    <div
        style="
            margin-top:25px;
            padding:18px;
            background:#dcfce7;
            border:1px solid #16a34a;
            border-radius:8px;
            color:#166534;
            font-weight:bold;
            font-size:16px;
        "
    >

        ✔️ Este documento ya fue aprobado.

    </div>

<?php else: ?>

    <div
        style="
            margin-top:25px;
            padding:18px;
            background:#fee2e2;
            border:1px solid #dc2626;
            border-radius:8px;
            color:#991b1b;
            font-weight:bold;
            font-size:16px;
        "
    >

        ❌ Este documento fue rechazado.

    </div>

<?php endif; ?>

</form>

</div>

</div>

</body>

</html>