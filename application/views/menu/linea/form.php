<form name="formagregar" id="formagregarlinea"  action="<?= base_url() ?>linea/guardar" method="post" >
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Nueva L&iacute;nea</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <div class="col-md-2">
                        Nombre
                    </div>
                    <div class="col-md-10"><input type="text" name="nombre" id="nombre_linea" required="true" class="form-control"
                                                  value="<?php if (isset($lineas['nombre_linea'])) echo $lineas['nombre_linea']; ?>">
                    </div>
                    <input type="hidden" name="id" id="" required="true"
                           value="<?php if (isset($lineas['id_linea'])) echo $lineas['id_linea']; ?>">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button"  class="btn btn-primary" id="confirmar_boton_linea" onclick="guardar_linea('linea')">Confirmar</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>

            </div>
        </div>
        <!-- /.modal-content -->
    </div>
</form>

<script>

    function guardar_linea(retorno){
        if ($("#nombre_linea").val() == '') {
            var growlType = 'warning';

            $.bootstrapGrowl('<h4>Debe seleccionar el nombre</h4>', {
                type: growlType,
                delay: 2500,
                allow_dismiss: true
            });

            $(this).prop('disabled', true);

            return false;
        }
    $('#load_div').show()

        $.ajax({
            url: '<?= base_url()?>linea/guardar',
            type: 'POST',
            headers: {
                Accept: 'application/json'
            },
            dataType:'json',
            data: $("#formagregarlinea").serialize(),
            success: function (data) {

                if (data.error == undefined) {
                    /*si retorno es producto, quiere decir que esta vista fue llamada desde el modulo de producto
                     * por lo tanto va allamar a update, y update esta en la vista de producto_form.
                     * Sino quiere decir que esta en su modulo normal, y retornara la vista nuevamente*/
                    if(retorno=='producto') {
                        update_linea(data.id,data.nombre);
                    }

                    var growlType = 'success';

                    $.bootstrapGrowl('<h4>'+data.success+'</h4>', {
                        type: growlType,
                        delay: 2500,
                        allow_dismiss: true
                    });

                    retornarlinea(retorno)
                }else{
                    var growlType = 'warning';
                    $.bootstrapGrowl('<h4>' + data.error + '</h4>', {
                        type: growlType,
                        delay: 2500,
                        allow_dismiss: true
                    });
                    $(this).prop('disabled', true);
                }
                    setTimeout(function () {
                        //$(".alert-danger").css('display','none');
                        $('#load_div').hide()
                    }, 2000)
            },
            error: function(data){

                var growlType = 'warning';

                $.bootstrapGrowl('<h4>' + data.error + '</h4>', {
                    type: growlType,
                    delay: 2500,
                    allow_dismiss: true
                });

                $(this).prop('disabled', true);
                    setTimeout(function () {
                        //$(".alert-danger").css('display','none');
                        $('#load_div').hide()
                    }, 2000)

            }
        })



    }

    function retornarlinea (retorno){

        $("#agregarlinea").modal('hide');

        if(retorno=="linea") {
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
            return $.ajax({
                url: '<?= base_url()?>linea',
                success: function (data2) {
                    $('#page-content').html(data2);
                }

            })
        }

    }
    $(function(){



    });</script>
