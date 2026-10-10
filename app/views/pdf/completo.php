<?php

/** @var array $usuario */
/** @var array $compras */
/** @var array $pedidos */
/** @var array $pqrs */

$tituloReporte = 'Reporte Completo del Usuario';

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

<h1>Reporte Completo del Usuario</h1>

<p class="introduccion">
    Documento generado automáticamente por BodyTech.
</p>

<hr>

<h2>1. Información Personal</h2>

<table>
    <tr>
        <td style="width: 32%; background-color: #eff6ff;">
            <strong>Nombre completo</strong>
        </td>
        <td><?= htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8') ?></td>
    </tr>
    <tr>
        <td style="background-color: #eff6ff;"><strong>Correo electrónico</strong></td>
        <td><?= htmlspecialchars($usuario['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
    </tr>
    <tr>
        <td style="background-color: #eff6ff;"><strong>Teléfono principal</strong></td>
        <td><?= htmlspecialchars($usuario['telefono_principal'] ?? '-', ENT_QUOTES, 'UTF-8') ?: '-' ?></td>
    </tr>
    <tr>
        <td style="background-color: #eff6ff;"><strong>Teléfono secundario</strong></td>
        <td><?= htmlspecialchars($usuario['telefono_secundario'] ?? '-', ENT_QUOTES, 'UTF-8') ?: '-' ?></td>
    </tr>
    <tr>
        <td style="background-color: #eff6ff;"><strong>Dirección</strong></td>
        <td><?= htmlspecialchars($usuario['direccion'] ?? '-', ENT_QUOTES, 'UTF-8') ?: '-' ?></td>
    </tr>
    <tr>
        <td style="background-color: #eff6ff;"><strong>Tipo de documento</strong></td>
        <td><?= htmlspecialchars($usuario['tipo_documento'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
    </tr>
    <tr>
        <td style="background-color: #eff6ff;"><strong>Número de documento</strong></td>
        <td><?= htmlspecialchars($usuario['numero_documento'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
    </tr>
</table>

<hr>

<h2>2. Historial de Compras</h2>

<?php if (empty($compras)): ?>

    <p>No existen compras registradas.</p>

<?php else: ?>

    <?php foreach ($compras as $compra): ?>

        <h3>
            Compra:
            <?= htmlspecialchars($compra['referencia'] ?? '', ENT_QUOTES, 'UTF-8') ?>
        </h3>

        <table>
            <thead>
                <tr>
                    <th>Referencia</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Estado del pago</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= htmlspecialchars($compra['referencia'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($compra['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="importe">
                        $<?= number_format((float) ($compra['total'] ?? 0), 2, ',', '.') ?>
                    </td>
                    <td><?= htmlspecialchars($compra['estado_pago'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            </tbody>
        </table>

        <h3>Productos comprados</h3>

        <?php if (empty($compra['productos'])): ?>

            <p>No hay productos asociados a esta compra.</p>

        <?php else: ?>

            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio unitario</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($compra['productos'] as $producto): ?>
                        <tr>
                            <td><?= htmlspecialchars($producto['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td style="text-align: center;">
                                <?= (int) ($producto['cantidad'] ?? 0) ?>
                            </td>
                            <td class="importe">
                                $<?= number_format((float) ($producto['precio_unitario'] ?? 0), 2, ',', '.') ?>
                            </td>
                            <td class="importe">
                                $<?= number_format((float) ($producto['subtotal'] ?? 0), 2, ',', '.') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php endif; ?>

        <hr>

    <?php endforeach; ?>

<?php endif; ?>

<hr>

<h2>3. Historial de Pedidos</h2>

<?php if (empty($pedidos)): ?>

    <p>No existen pedidos registrados.</p>

<?php else: ?>

    <?php foreach ($pedidos as $pedido): ?>

        <h3>
            Pedido:
            <?= htmlspecialchars($pedido['referencia'] ?? '', ENT_QUOTES, 'UTF-8') ?>
        </h3>

        <table>
            <tr>
                <td style="width: 32%; background-color: #eff6ff;">
                    <strong>Estado del pedido</strong>
                </td>
                <td><?= htmlspecialchars($pedido['estado_orden'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
        </table>

        <h3>Productos del pedido</h3>

        <?php if (empty($pedido['productos'])): ?>

            <p>No hay productos asociados a este pedido.</p>

        <?php else: ?>

            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio unitario</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedido['productos'] as $producto): ?>
                        <tr>
                            <td><?= htmlspecialchars($producto['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td style="text-align: center;">
                                <?= (int) ($producto['cantidad'] ?? 0) ?>
                            </td>
                            <td class="importe">
                                $<?= number_format((float) ($producto['precio_unitario'] ?? 0), 2, ',', '.') ?>
                            </td>
                            <td class="importe">
                                $<?= number_format((float) ($producto['subtotal'] ?? 0), 2, ',', '.') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php endif; ?>

        <p class="etiqueta">Historial de estados</p>

        <?php if (empty($pedido['historial'])): ?>

            <p>No existe historial registrado para este pedido.</p>

        <?php else: ?>

            <table>
                <thead>
                    <tr>
                        <th style="width: 35%;">Fecha</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedido['historial'] as $evento): ?>
                        <tr>
                            <td><?= htmlspecialchars($evento['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($evento['estado'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php endif; ?>

        <hr>

    <?php endforeach; ?>

<?php endif; ?>

<hr>

<h2>4. Historial de PQRS</h2>

<?php if (empty($pqrs)): ?>

    <p>No existen casos PQRS registrados.</p>

<?php else: ?>

    <?php foreach ($pqrs as $caso): ?>

        <div class="bloque-pqrs">

            <h3>
                <?= htmlspecialchars(ucfirst($caso['tipo'] ?? 'Solicitud'), ENT_QUOTES, 'UTF-8') ?>
                -
                <?= htmlspecialchars($caso['asunto'] ?? '', ENT_QUOTES, 'UTF-8') ?>
            </h3>

            <table>
                <tr>
                    <td style="width: 32%; background-color: #eff6ff;">
                        <strong>Estado del caso</strong>
                    </td>
                    <td><?= htmlspecialchars($caso['estado'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            </table>

            <p class="etiqueta">Descripción de la solicitud</p>

            <p>
                <?= nl2br(htmlspecialchars($caso['descripcion'] ?? '', ENT_QUOTES, 'UTF-8')) ?>
            </p>

            <h3>Respuestas</h3>

            <?php if (empty($caso['respuestas'])): ?>

                <p>No existen respuestas registradas.</p>

            <?php else: ?>

                <table>
                    <thead>
                        <tr>
                            <th style="width: 35%;">Fecha</th>
                            <th>Respuesta</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($caso['respuestas'] as $respuesta): ?>
                            <tr>
                                <td><?= htmlspecialchars($respuesta['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= nl2br(htmlspecialchars($respuesta['mensaje'] ?? '', ENT_QUOTES, 'UTF-8')) ?></td>
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
                            <?= htmlspecialchars(basename($archivo['archivo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

            <?php endif; ?>

            <hr>

        </div>

    <?php endforeach; ?>

<?php endif; ?>

<p class="introduccion">
    Este documento fue generado automáticamente por el sistema BodyTech.
</p>

<?php include __DIR__ . '/components/footer.php'; ?>