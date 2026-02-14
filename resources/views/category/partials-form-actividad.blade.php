<div class="dropdown-divider mt-2"></div>
<p class="italic"><small>Información SIAT.</small></p>
<div class="row">
    <div class="form-group col-md-12">
        <label>Actividad Económica</label>
        <select name="actividad_id" id="actividad_id" class="form-control selectpicker" title="Seleccionar...">
            @foreach ($actividades as $item)
                <option value="{{ $item->codigo_caeb }}">{{ $item->descripcion }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-12" id="codigos">
        <label for="codigo_pro_ser">Producto-Servicio</label>
        <select name="codigo_pro_ser" id="codigo_pro_ser" class="form-control selectpicker">
        </select>
    </div>
</div>


<script>
    $(document).ready(function() {
        $('#codigos').hide();
        $('#actividad_id').on('change', onSelectProductoServicio);

        function limpiar() {
            $("#createModal select[name='codigo_pro_ser']").empty();
        }

        function setSeleccionar() {
            $("#createModal select[name='codigo_pro_ser']").append('<option value="">Seleccionar</option>');
        }

        function onSelectProductoServicio() {
            var estatus = $(this).val();
            if (estatus === "") {
                $('#codigos').hide();
                limpiar();
                return;
            }
            if (estatus) {

                $.ajax({
                    url: 'category/get/' + estatus,
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        limpiar();
                        setSeleccionar();
                        console.log(estatus);
                        console.log(data);
                        for (let i = 0; i < data.length; i++) {
                            $("#createModal select[name='codigo_pro_ser']").append(
                                '<option value="' + data[i].codigo_producto + '">' + data[i]
                                .codigo_producto + ' - ' + data[i].descripcion_producto +
                                '</option>');
                        };
                        $('.selectpicker').selectpicker('refresh');
                    },
                });

                $('#codigos').show(1000);
                return;
            }
        }
    });
</script>
