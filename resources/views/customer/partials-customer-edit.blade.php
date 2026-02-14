<div class="form-group col-md-6">
    <label>Razón Social</label>
    <input type="text" name="razon_social" class="form-control" value="{{$lims_customer_data->razon_social}}">
</div>
<div class="form-group col-md-6">
    <input type="hidden" name="tipo_documento_id" value="{{$lims_customer_data->tipo_documento}}">
    <label>Tipo de Documento</label>
    <select name="tipo_documento" id="tipo_documento" class="selectpicker form-control" title="Seleccione documento...">
        @include('customer.partials-tipo-documentos')
    </select>
</div>
<div class="form-group col-md-4" id="codigos">
    <label for="valor_documento">Valor Documento</label>
    <div class="input-group">
        <input type="text" name="valor_documento" class="form-control" value="{{ $lims_customer_data->valor_documento }}">
        <div class="input-group-append">
            <button class="btn btn-outline-secondary" type="button" onclick="consultarNIT()" id="verificarNIT"><i class="dripicons-search"></i></button>
        </div>
        <div class="invalid-feedback">
        </div>
        <div class="valid-feedback">
        </div>    
    </div> 
</div>
<div class="form-group col" id="complemento">
    <label for="complemento_documento">Complemento</label>
    <input type="text" name="complemento_documento" class="form-control" value="{{ $lims_customer_data->complemento_documento }}">
</div>

<script>
    $('#codigos').hide();
    $('#verificarNIT').hide();
    $('select[name=tipo_documento]').val($("input[name='tipo_documento_id']").val());

    var status = $("input[name='tipo_documento_id']").val();
    if(status){
        $('#codigos').show(300);
        $('#complemento').show(300);
        esNIT(status);
        esCarnetIdentidad(status);
    }
    $(function () {
        $('#tipo_documento').on('change', mostrarInput);
    });
    function mostrarInput() {
        $("input[name='valor_documento']").prop('required',true);
        $('#codigos').show(300);
        $('#complemento').hide();
        $('#verificarNIT').hide();
        $('.invalid-feedback').hide();
        $('.valid-feedback').hide();
        var status = $(this).val();
        esNIT(status);
        esCarnetIdentidad(status);
    }
    //mostrar complemento solo cuando es Carnet de Identidad
    function esCarnetIdentidad(status) {
        if ( status== 1) {
            $('#complemento').show(300);
        }
    }
    //mostrar botón para verificarNIT y oculta el complemento
    function esNIT(status) {
        if ( status== 5) {
            $('#complemento').hide();
            $('#verificarNIT').show(300);
        }
    }
    function consultarNIT(){
        var url_edit = "verificar_nit/"
        var nit = $("input[name='valor_documento']").val();
        url_edit = url_edit.concat(nit);
        console.log(url_edit);        
        
        $.ajax({
            url: '../'+url_edit,
            type: "GET",
            dataType: "json",
            success:function(data) {
                console.log(data);        
                if (data['codigo'] == '994') {
                    $('.invalid-feedback').text('Respuesta Siat: '+data['descripcion']);
                    $('.invalid-feedback').show();
                    $('.valid-feedback').hide();
                }else{
                    $('.invalid-feedback').hide();
                    $('.valid-feedback').text('Respuesta Siat: '+data['descripcion']);
                    $('.valid-feedback').show();
                }
            },
        });
    }

</script>
