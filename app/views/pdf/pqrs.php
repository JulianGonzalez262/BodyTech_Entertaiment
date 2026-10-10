
<?php

/** @var array $usuario */
/** @var array $pqrs */

$tituloReporte = 'Reporte Oficial de PQRS';

include __DIR__ . '/components/header.php';

$nombreCompleto = trim(
    ($usuario['nombres'] ?? '') . ' ' .
    ($usuario['apellidos'] ?? '')
);

?>

<style>
        <?php
        $archivoCSS = __DIR__ . '/css/personal.css';

        if (file_exists($archivoCSS)) {
            echo file_get_contents($archivoCSS);
        }
        ?>
    </style>

<h2>Información del Cliente</h2>

<table>
    <tr>
        <td style="width: 30%; background-color: #eff6ff;">
            <strong>Nombre completo</strong>
        </td>
        <td>
            <?= htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8') ?>
        </td>
    </tr>

    <tr>
        <td style="background-color: #eff6ff;">
            <strong>Correo electrónico</strong>
        </td>
        <td>
            <?= htmlspecialchars($usuario['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>
        </td>
    </tr>

    <tr>
        <td style="background-color: #eff6ff;">
            <strong>Fecha del reporte</strong>
        </td>
        <td>
            <?= date('d/m/Y H:i') ?>
        </td>
    </tr>
</table>

<hr>

<?php if (empty($pqrs)): ?>

    <p>No existen casos PQRS registrados.</p>

<?php else: ?>

    <?php foreach ($pqrs as $caso): ?>

        <div class="caso-pqrs">

            <h2>
                Caso #<?= htmlspecialchars((string) ($caso['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
            </h2>

            <table>
                <tr>
                    <td style="width: 30%; background-color: #eff6ff;">
                        <strong>Tipo de solicitud</strong>
                    </td>
                    <td>
                        <?= htmlspecialchars($caso['tipo'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </td>
                </tr>

                <tr>
                    <td style="background-color: #eff6ff;">
                        <strong>Estado</strong>
                    </td>
                    <td>
                        <?= htmlspecialchars($caso['estado'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </td>
                </tr>

                <tr>
                    <td style="background-color: #eff6ff;">
                        <strong>Fecha de registro</strong>
                    </td>
                    <td>
                        <?= htmlspecialchars($caso['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </td>
                </tr>

                <tr>
                    <td style="background-color: #eff6ff;">
                        <strong>Asunto</strong>
                    </td>
                    <td>
                        <?= htmlspecialchars($caso['asunto'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </td>
                </tr>

                <tr>
                    <td style="background-color: #eff6ff;">
                        <strong>Descripción</strong>
                    </td>
                    <td>
                        <?= nl2br(htmlspecialchars($caso['descripcion'] ?? '', ENT_QUOTES, 'UTF-8')) ?>
                    </td>
                </tr>
            </table>

            <h3>Respuestas</h3>

            <?php if (empty($caso['respuestas'])): ?>

                <p>No existen respuestas registradas.</p>

            <?php else: ?>

                <table>
                    <thead>
                        <tr>
                            <th style="width: 30%;">Fecha</th>
                            <th>Respuesta</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($caso['respuestas'] as $respuesta): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($respuesta['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td>
                                    <?= nl2br(htmlspecialchars($respuesta['mensaje'] ?? '', ENT_QUOTES, 'UTF-8')) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php endif; ?>

            <h3>Archivos adjuntos</h3>

            <?php if (empty($caso['archivos'])): ?>

                <p>No existen archivos adjuntos.</p>

            <?php else: ?>

                <ul>
                    <?php foreach ($caso['archivos'] as $archivo): ?>
                        <li>
                            <?= htmlspecialchars(
                                basename($archivo['archivo'] ?? ''),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

            <?php endif; ?>

        </div>

        <hr>

    <?php endforeach; ?>

<?php endif; ?>

<?php include __DIR__ . '/components/footer.php'; ?>
