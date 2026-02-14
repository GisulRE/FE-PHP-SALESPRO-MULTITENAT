<div class="col-md-12">
    <div class="row" id="datos_siat">
        <div class="col-md-6 mb-3">
            <div class="form-group">
                <label>Actividad Económica</label>
                <input type="hidden" name="codigo_actividad_hidden" value="{{ $lims_product_data->codigo_actividad }}">
                <select name="codigo_actividad" id="actividad_id" class="form-control selectpicker" title="Seleccionar...">
                    @foreach ($actividades as $item)
                        <option value="{{ $item->codigo_caeb }}">{{ $item->descripcion }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-6 mb-3" id="cod_productos_servicios">
            <div class="form-group">
                <label>Producto-Servicio</label>
                <input type="hidden" name="codigo_producto_servicio_hidden" value="{{ $lims_product_data->codigo_producto_servicio }}">
                <select name="codigo_producto_servicio" class="form-control selectpicker" data-live-search="true" title="Seleccionar...">
                </select>
            </div>
        </div>
    </div>
</div>   

<script>
    $(document).ready(function() {
        var cat_id = $('select[name=category_id]').val();
        getDatosCategoria(cat_id);
        console.log("Ready Product!");
    });
    // se oculta los input de actividad y productos servicios
    $("#datos_siat").hide(); 
    //cada que se seleccione una Categoría, debe mostrar datos_siat 'si solo si' tiene datos.
    $("select[name='category_id']").on('change', onSelectCategoria);

    function limpiar() {
        $("select[name='codigo_producto_servicio']").empty();
    }

    function onSelectCategoria() {
        $("#datos_siat").hide();
        $("select[name='codigo_actividad']").val(null);
        $("select[name='codigo_producto_servicio']").val(null);
        var categoria_id = $(this).val();

        getDatosCategoria(categoria_id);
    }

    // funcion que permite comprobar y además mostrar si existe ActividadEconomica 
    //y Producto/servicios de la determinada Categoría
    function getDatosCategoria(categoria_id) {
        var id = categoria_id;
        var url = '{{ route("category.edit", ":id") }}';
        url = url.replace(':id', id);

        $.ajax({
            url: url,
            type: "GET",
            dataType: "json",
            async: false,
            success:function(data) {
                
                if (data['codigo_actividad'] != null) {
                    //llama a un funcion para rellenar datos productos/servicios de acuerdo a la ActividadEconómica
                    getProductosServicios (data['codigo_actividad']);

                    $("select[name='codigo_actividad']").val(data['codigo_actividad']);
                    $("#datos_siat").show(350);
                    $('.selectpicker').selectpicker('refresh');
                }
                if (data['codigo_producto_servicio'] != null) {
                    $("select[name='codigo_producto_servicio']").val(data['codigo_producto_servicio']);
                    $('.selectpicker').selectpicker('refresh');
                }
            },
        });
    }
    function getProductosServicios (cod_actividad) {
        var id = cod_actividad;
        var url = '{{ route("category-get", ":id") }}';
        url = url.replace(':id', id);

        $.ajax({
            url: url,
            type: "GET",
            dataType: "json",
            async: false,
            success:function(data) {
                limpiar();
                for (let i = 0; i < data.length; i++) {
                    $("select[name='codigo_producto_servicio']").append('<option value="'+ data[i].codigo_producto +'">'+ data[i].codigo_producto +' - '+data[i].descripcion_producto +'</option>');
                };
            },
        });
    }
</script>
<script>
    // si tiene datos de actividad
    // se procede a setear los datos del producto con los combobox
    if ($("input[name='codigo_actividad_hidden']").val() != "") {
        var actividad = $("input[name='codigo_actividad_hidden']").val();
        getProductosServicios(actividad);
        $("select[name='codigo_actividad']").val($("input[name='codigo_actividad_hidden']").val());
        $("#datos_siat").show();
    }
    if ($("input[name='codigo_producto_servicio_hidden']").val() != "") {
        var producto = $("input[name='codigo_producto_servicio_hidden']").val();
        $("select[name='codigo_producto_servicio']").val(producto);
        $('.selectpicker').selectpicker('refresh');
    }
</script>