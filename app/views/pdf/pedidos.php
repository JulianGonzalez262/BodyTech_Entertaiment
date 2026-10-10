<?php

/** @var array $usuario */
/** @var array $pedidos */

$tituloReporte = 'Reporte Oficial de Pedidos';

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

<?php if (empty($pedidos)): ?>

    <p>No existen pedidos registrados.</p>

<?php else: ?>

    <?php foreach ($pedidos as $pedido): ?>

        <div class="pedido">

            
<h2>
    Pedido
    <?= htmlspecialchars($pedido['referencia'] ?? '', ENT_QUOTES, 'UTF-8') ?>
</h2>

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
                    <td>
                        <?= htmlspecialchars($producto['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </td>
                    <td>
                        <?= (int) ($producto['cantidad'] ?? 0) ?>
                    </td>
                    <td>
                        $<?= number_format((float) ($producto['precio_unitario'] ?? 0), 2, ',', '.') ?>
                    </td>
                    <td>
                        $<?= number_format((float) ($producto['subtotal'] ?? 0), 2, ',', '.') ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php endif; ?>

            <table>
                <tr>
                    <td style="width: 30%; background-color: #eff6ff;">
                        <strong>Fecha del pedido</strong>
                    </td>
                    <td>
                        <?= htmlspecialchars($pedido['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </td>
                </tr>

                <tr>
                    <td style="background-color: #eff6ff;">
                        <strong>Estado del pedido</strong>
                    </td>
                    <td>
                        <?= htmlspecialchars($pedido['estado_orden'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </td>
                </tr>

                <tr>
                    <td style="background-color: #eff6ff;">
                        <strong>Estado del pago</strong>
                    </td>
                    <td>
                        <?= htmlspecialchars($pedido['estado_pago'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </td>
                </tr>

                <tr>
                    <td style="background-color: #eff6ff;">
                        <strong>Método de pago</strong>
                    </td>
                    <td>
                        <?= htmlspecialchars($pedido['metodo_pago'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </td>
                </tr>

                <tr>
                    <td style="background-color: #eff6ff;">
                        <strong>Total</strong>
                    </td>
                    <td class="pedido-total">
                        $<?= number_format((float) ($pedido['total'] ?? 0), 2, ',', '.') ?>
                    </td>
                </tr>

                <?php if (!empty($pedido['transportadora'])): ?>
                    <tr>
                        <td style="background-color: #eff6ff;">
                            <strong>Transportadora</strong>
                        </td>
                        <td>
                            <?= htmlspecialchars($pedido['transportadora'], ENT_QUOTES, 'UTF-8') ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php if (!empty($pedido['guia_envio'])): ?>
                    <tr>
                        <td style="background-color: #eff6ff;">
                            <strong>Guía de envío</strong>
                        </td>
                        <td>
                            <?= htmlspecialchars($pedido['guia_envio'], ENT_QUOTES, 'UTF-8') ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php if (!empty($pedido['tracking_url'])): ?>
                    <tr>
                        <td style="background-color: #eff6ff;">
                            <strong>Seguimiento del envío</strong>
                        </td>
                        <td>
                            <?= htmlspecialchars($pedido['tracking_url'], ENT_QUOTES, 'UTF-8') ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php if (!empty($pedido['fecha_envio'])): ?>
                    <tr>
                        <td style="background-color: #eff6ff;">
                            <strong>Fecha de envío</strong>
                        </td>
                        <td>
                            <?= htmlspecialchars($pedido['fecha_envio'], ENT_QUOTES, 'UTF-8') ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </table>

            <h3>Historial del Pedido</h3>

            <?php if (empty($pedido['historial'])): ?>

                <p>No existe historial registrado.</p>

            <?php else: ?>

                <table>
                    <thead>
                        <tr>
                            <th style="width: 25%;">Fecha</th>
                            <th style="width: 25%;">Estado</th>
                            <th>Observación</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($pedido['historial'] as $evento): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($evento['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($evento['estado'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($evento['observacion'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php endif; ?>

        </div>

        <hr>

    <?php endforeach; ?>

<?php endif; ?>

<?php include __DIR__ . '/components/footer.php'; ?>