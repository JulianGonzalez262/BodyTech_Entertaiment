<?php

/** @var array $usuario */

$nombreCompleto = trim(
    ($usuario['nombres'] ?? '')
    . ' ' .
    ($usuario['apellidos'] ?? '')
);

$tituloReporte =
    'Reporte Oficial de Informacion Personal';

include __DIR__ . '/components/header.php';

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <style>
        <?php
        $archivoCSS = __DIR__ . '/css/personal.css';

        if (file_exists($archivoCSS)) {
            echo file_get_contents($archivoCSS);
        }
        ?>
    </style>
</head>

<body>

    <div class="header">
        <h1>BODYTECH ENTERTAIMENT</h1>
        <p>Reporte Oficial de Informacion Personal</p>
    </div>

    <h2>Informacion del Usuario</h2>

    <table>
        <tr>
            <td class="label">Nombre completo</td>
            <td><?= htmlspecialchars($nombreCompleto) ?></td>
        </tr>

        <tr>
            <td class="label">Correo electronico</td>
            <td><?= htmlspecialchars($usuario['email'] ?? '') ?></td>
        </tr>

        <tr>
            <td class="label">Telefono principal</td>
            <td><?= htmlspecialchars($usuario['telefono_principal'] ?? 'No registrado') ?></td>
        </tr>

        <tr>
            <td class="label">Telefono secundario</td>
            <td><?= htmlspecialchars($usuario['telefono_secundario'] ?? 'No registrado') ?></td>
        </tr>

        <tr>
            <td class="label">Direccion</td>
            <td><?= htmlspecialchars($usuario['direccion'] ?? 'No registrada') ?></td>
        </tr>

        <tr>
            <td class="label">Tipo de documento</td>
            <td><?= htmlspecialchars($usuario['tipo_documento'] ?? 'No registrado') ?></td>
        </tr>

        <tr>
            <td class="label">Numero de documento</td>
            <td><?= htmlspecialchars($usuario['numero_documento'] ?? 'No registrado') ?></td>
        </tr>

        <tr>
            <td class="label">Correo verificado</td>
            <td><?= !empty($usuario['email_verificado']) ? 'Si' : 'No' ?></td>
        </tr>

        <tr>
            <td class="label">Documento verificado</td>
            <td><?= !empty($usuario['documento_verificado']) ? 'Si' : 'No' ?></td>
        </tr>

        <tr>
            <td class="label">Fecha de registro</td>
            <td><?= htmlspecialchars($usuario['created_at'] ?? '') ?></td>
        </tr>
    </table>

    <?php
    include __DIR__ . '/components/footer.php';
    ?>

</body>

</html>