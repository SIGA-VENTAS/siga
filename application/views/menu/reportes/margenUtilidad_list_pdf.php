<?php $ruta = base_url(); ?>
<?php $md = get_moneda_defecto() ?>
<style type="text/css">
    table td {
        width: 100%;
        border: #e1e1e1 1px solid;
        font-size: 9px;
    }

    thead, th {
        background: #585858;
        border: #111 1px solid;
        color: #fff;
        font-size: 10px;
    }

    h4, h5 {
        margin: 0px;
    }

    table tfoot tr td {
        font-weight: bold;
    }
</style>
<h4 style="text-align: center;">Reporte de m&aacute;rgen de utilidad</h4>
<h4 style="text-align: center;">Desde <?= date('d/m/Y', strtotime($fecha_ini)) ?>
    al <?= date('d/m/Y', strtotime($fecha_fin)) ?></h4>

<h5>EMPRESA: <?= valueOption('EMPRESA_NOMBRE') ?></h5>
<h5>DIRECCI&Oacute;N: <?= $local_direccion ?></h5>
<h5>SUCURSAL: <?= $local_nombre ?></h5>
<table>
    <thead>
    <tr>
        <th><?= getCodigoNombre() ?></th>
        <th>Nombre</th>
        <th>Unidad</th>
        <th>Costo unitario</th>
        <th>Impuesto</th>
        <th>Costo + Impuesto</th>
        <th>Impuesto</th>
        <th>Precio unitario</th>
        <th>Precio + Impuesto</th>
        <th>Costo Total</th>
        <th>Cantidad vendida</th>
        <th>Subtotal</th>
        <th>Impuesto</th>
        <th>Venta total</th>
        <th>Utilidad x unidad</th>
        <th>Utilidad total</th>
        <th>% rentabilidad</th>
    </tr>
    </thead>
    <tbody>
    <?php
        $totalCostoImpuesto = 0;
        $totalPrecioImpuesto = 0;
        $totalCostoTotal = 0;
        $totalSubTotal = 0;
        $totalImpuestoV = 0;
        $totalVentaTotal = 0;
        $totalUtilidadTotal = 0;
    ?>
    <?php foreach ($lists as $list): ?>
        <?php
            $porcImpuesto = $list->porcentaje_impuesto;
            $cantidadVendida = $list->cantidad;
            $igv = (100 + $porcImpuesto) / 100;
            $costoImpuesto = $list->compra;
            $costoUnitario = $costoImpuesto / $igv;
            $impuesto = $costoImpuesto - $costoUnitario;
            $precioImpuesto = $list->precioUnitario;
            $precioUnitario = $precioImpuesto / $igv;
            $costoTotal = $cantidadVendida * $costoImpuesto;
            $subtotal = $cantidadVendida * $precioUnitario;
            $ventaTotal = $subtotal * $igv;
            $impuestoV = $ventaTotal - $subtotal;
            $utilidadUnidad = $precioUnitario - $costoUnitario;
            $utilidadTotal = $utilidadUnidad * $cantidadVendida;
            if($list->compra==0){
                $porcRentabilidad = 0;
            }else{
                $porcRentabilidad = ($utilidadUnidad / $costoUnitario) * 100; 
            }
            //Totales
            $totalCostoImpuesto += $costoImpuesto;
            $totalPrecioImpuesto += $precioImpuesto;
            $totalCostoTotal += $costoTotal;
            $totalSubTotal += $subtotal;
            $totalImpuestoV += $impuestoV;
            $totalVentaTotal += $ventaTotal;
            $totalUtilidadTotal += $utilidadTotal;
        ?>
        <tr>
            <td><?= getCodigoValue($list->id_producto, $list->producto_codigo_interno) ?></td>
            <td><?= $list->producto_nombre ?></td>
            <td><?= $list->nombre_unidad ?></td>
            <td style="text-align: right;"><?= number_format($costoUnitario, 2) ?></td>
            <td style="text-align: right;"><?= number_format($impuesto, 2) ?></td>
            <td style="text-align: right;"><?= number_format($costoImpuesto, 2) ?></td>
            <td style="text-align: right;"><?= number_format($porcImpuesto, 2) ?> %</td>
            <td style="text-align: right;"><?= number_format($precioUnitario, 2) ?></td>
            <td style="text-align: right;"><?= number_format($precioImpuesto, 2) ?></td>
            <td style="text-align: right;"><?= number_format($costoTotal, 2) ?></td>
            <td style="text-align: right;"><?= number_format($cantidadVendida, 0) ?></td>
            <td style="text-align: right;"><?= number_format($subtotal, 2) ?></td>
            <td style="text-align: right;"><?= number_format($impuestoV, 2) ?></td>
            <td style="text-align: right;"><?= number_format($ventaTotal, 2) ?></td>
            <td style="text-align: right;"><?= number_format($utilidadUnidad, 2) ?></td>
            <td style="text-align: right;"><?= number_format($utilidadTotal, 2) ?></td>
            <td style="text-align: right;"><?= number_format($porcRentabilidad, 2) ?> %</td>
        </tr>
    <?php endforeach ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3">TOTALES</td>
            <td></td>
            <td></td>
            <td style="text-align: right;"><?= number_format($totalCostoImpuesto, 2) ?></td>
            <td></td>
            <td></td>
            <td style="text-align: right;"><?= number_format($totalPrecioImpuesto, 2) ?></td>
            <td style="text-align: right;"><?= number_format($totalCostoTotal, 2) ?></td>
            <td></td>
            <td style="text-align: right;"><?= number_format($totalSubTotal, 2) ?></td>
            <td style="text-align: right;"><?= number_format($totalImpuestoV, 2) ?></td>
            <td style="text-align: right;"><?= number_format($totalVentaTotal, 2) ?></td>
            <td></td>
            <td style="text-align: right;"><?= number_format($totalUtilidadTotal, 2) ?></td>
            <td></td>
        </tr>
    </tfoot>
</table>
