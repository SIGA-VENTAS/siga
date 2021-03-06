<?php $ruta = base_url(); ?>

<ul class="breadcrumb breadcrumb-top">
    <li>Reportes</li>
    <li><a href="">Entradas y Salidas</a></li>
</ul>
<link rel="stylesheet" href="<?= $ruta ?>recursos/css/plugins.css">
<link rel="stylesheet" href="<?= $ruta ?>recursos/js/datepicker-range/daterangepicker.css">
<div class="row-fluid">
    <div class="span12">
        <div class="block">

            <!-- Progress Bars Wizard Title -->
            <div class="row">
                <div class="form-group">
                    <div class="col-md-3">
                        <?php if (isset($locales)): ?>
                            <label class="control-label panel-admin-text">Ubicaci&oacute;n</label>
                            <select id="venta_local" class="form-control filter-input">
                                <?php foreach ($locales as $local): ?>
                                    <option <?php if ($this->session->userdata('id_local') == $local['int_local_id']) echo "selected"; ?>
                                            value="<?= $local['int_local_id']; ?>"> <?= $local['local_nombre'] ?> </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>

                    </div>

                    <div class="col-md-3">
                        <label class="control-label panel-admin-text">Fecha</label>
                        <input type="text" id="date_range" class="form-control" readonly style="cursor: pointer;"
                               name="daterange" value="<?= date('01/m/Y') ?> - <?= date('d/m/Y') ?>"/>

                    </div>

                    <div class="col-md-2">
                        <label class="control-label panel-admin-text">Movimiento</label>
                        <select name="io" id="io" class='cho form-control'>
                           <option value="0">TODOS</option>
                           <option value="1">Entrada</option>
                           <option value="2">Salida</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="control-label panel-admin-text">Moneda</label>
                        <select name="moneda_id" id="moneda_id" class='cho form-control'>
                            <?php foreach ($monedas as $moneda): ?>
                                <option value="<?= $moneda->id_moneda ?>"
                                        data-simbolo="<?= $moneda->simbolo ?>"
                                    <?= $moneda->id_moneda == MONEDA_DEFECTO ? 'selected' : '' ?>><?= $moneda->nombre ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-1"></div>

                    <div class="col-md-1">
                        <label class="control-label panel-admin-text" style="color: #FFFFFF;">.</label><br>
                        <button id="btn_buscar" class="btn btn-default">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>

                </div>
            </div>
            <br>


            <div class="row-fluid">
                <div class="span12">
                    <div id="historial_list">

                    </div>

                </div>
            </div>

            <div class="row" id="loading" style="display: none;">
                <div class="col-md-12 text-center">
                    <div class="loading-icon"></div>
                </div>
            </div>


            <script src="<?php echo $ruta; ?>recursos/js/datepicker-range/moment.min.js"></script>
            <script src="<?php echo $ruta; ?>recursos/js/datepicker-range/daterangepicker.js"></script>
            <script src="<?php echo $ruta; ?>recursos/js/Validacion.js"></script>
            <!-- /.modal-dialog -->
            <script type="text/javascript">

                $(function () {

                    $('input[name="daterange"]').daterangepicker({
                        "locale": {
                            "format": "DD/MM/YYYY",
                            "separator": " - ",
                            "applyLabel": "Aplicar",
                            "cancelLabel": "Cancelar",
                            "fromLabel": "De",
                            "toLabel": "A",
                            "customRangeLabel": "Personalizado",
                            "daysOfWeek": [
                                "Do",
                                "Lu",
                                "Ma",
                                "Mi",
                                "Ju",
                                "Vi",
                                "Sa"
                            ],
                            "monthNames": [
                                "Enero",
                                "Febrero",
                                "Marzo",
                                "Abril",
                                "Mayo",
                                "Junio",
                                "Julio",
                                "Agosto",
                                "Septiembre",
                                "Octubre",
                                "Noviembre",
                                "Diciembre"
                            ],
                            "firstDay": 1
                        }
                    });

                    $('select').chosen();

                    getReporte();

                    $("#btn_buscar").on("click", function () {
                        getReporte();
                    });


                    $('.chosen-container').css('width', '100%');

                });

                function getReporte() {
                    $("#historial_list").html($("#loading").html());

                    var data = {
                        local_id: $("#venta_local").val(),
                        fecha: $("#date_range").val(),
                        moneda_id: $("#moneda_id").val(),
                        io: $("#io").val()
                    };

                    $.ajax({
                        url: '<?= base_url()?>ajuste/historial/filter',
                        data: data,
                        type: 'POST',
                        success: function (data) {
                            $("#historial_list").html(data);
                        },
                        error: function () {
                            $.bootstrapGrowl('<h4>Error.</h4> <p>Ha ocurrido un error en la operaci&oacute;n</p>', {
                                type: 'danger',
                                delay: 5000,
                                allow_dismiss: true
                            });
                            $("#historial_list").html('');
                        }
                    });

                }


            </script>
