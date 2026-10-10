<?php

/** @var array $usuario */
/** @var array $ordenes */

$tituloReporte = 'Reporte Oficial de Compras';

include __DIR__ . '/components/header.php';

$nombreCompleto = trim(
    ($usuario['nombres'] ?? '') . ' ' .
    ($usuario['apellidos'] ?? '')
);

$totalCompras = count($ordenes);
$totalInvertido = 0;

foreach ($ordenes as $orden) {
    $totalInvertido += (float) ($orden['total'] ?? 0);
}

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

<?php if (empty($ordenes)): ?>

    <p>El usuario aún no registra compras.</p>

<?php else: ?>

    <?php foreach ($ordenes as $orden): ?>

        <div class="orden">

            <h2>
                Orden
                <?= htmlspecialchars($orden['referencia'] ?? '', ENT_QUOTES, 'UTF-8') ?>
            </h2>

            <table>
                <tr>
                    <td style="width: 30%; background-color: #eff6ff;">
                        <strong>Fecha de compra</strong>
                    </td>
                    <td>
                        <?= htmlspecialchars($orden['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </td>
                </tr>

                <tr>
                    <td style="background-color: #eff6ff;">
                        <strong>Estado de la orden</strong>
                    </td>
                    <td>
                        <?= htmlspecialchars($orden['estado_orden'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </td>
                </tr>

                <tr>
                    <td style="background-color: #eff6ff;">
                        <strong>Estado del pago</strong>
                    </td>
                    <td>
                        <?= htmlspecialchars($orden['estado_pago'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </td>
                </tr>

                <tr>
                    <td style="background-color: #eff6ff;">
                        <strong>Método de pago</strong>
                    </td>
                    <td>
                        <?= htmlspecialchars($orden['metodo_pago'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </td>
                </tr>

                <?php if (!empty($orden['transportadora'])): ?>
                    <tr>
                        <td style="background-color: #eff6ff;">
                            <strong>Transportadora</strong>
                        </td>
                        <td>
                            <?= htmlspecialchars($orden['transportadora'], ENT_QUOTES, 'UTF-8') ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php if (!empty($orden['guia_envio'])): ?>
                    <tr>
                        <td style="background-color: #eff6ff;">
                            <strong>Guía de envío</strong>
                        </td>
                        <td>
                            <?= htmlspecialchars($orden['guia_envio'], ENT_QUOTES, 'UTF-8') ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </table>

            <h3>Productos de la compra</h3>

            <?php if (empty($orden['productos'])): ?>

                <p>No hay productos asociados a esta orden.</p>

            <?php else: ?>

                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th style="text-align: center;">Cantidad</th>
                            <th class="importe">Precio unitario</th>
                            <th class="importe">Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($orden['productos'] as $producto): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($producto['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </td>
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

            <table>
                <tr>
                    <td style="width: 70%; background-color: #eff6ff;">
                        <strong>Descuento</strong>
                    </td>
                    <td class="importe">
                        $<?= number_format((float) ($orden['descuento'] ?? 0), 2, ',', '.') ?>
                    </td>
                </tr>

                <tr>
                    <td style="background-color: #eff6ff;">
                        <strong>TOTAL DE LA COMPRA</strong>
                    </td>
                    <td class="importe" style="color: #1d4ed8; font-weight: bold;">
                        $<?= number_format((float) ($orden['total'] ?? 0), 2, ',', '.') ?>
                    </td>
                </tr>
            </table>

        </div>

        <hr>

    <?php endforeach; ?>

    <h2>Resumen General</h2>

    <table>
        <tr>
            <td style="width: 60%; background-color: #eff6ff;">
                <strong>Total de compras</strong>
            </td>
            <td class="importe">
                <?= $totalCompras ?>
            </td>
        </tr>

        <tr>
            <td style="background-color: #eff6ff;">
                <strong>Total invertido</strong>
            </td>
            <td class="importe" style="color: #1d4ed8; font-weight: bold;">
                $<?= number_format($totalInvertido, 2, ',', '.') ?>
            </td>
        </tr>
    </table>

<?php endif; ?>

<?php include __DIR__ . '/components/footer.php'; ?>