<div class="col-md-2">
    <div class="form-group">
        <label>Ambiente *</strong> </label>
        <div class="input-group">
            <select id="ambiente_id" name="ambiente" class="form-control selectpicker" required>
                @include('layout.partials.option-prueba-produccion', ['val' => $item->ambiente])
            </select>
        </div>
    </div>
</div>
<div id="pruebas-items" class="col-6">
        <div class="col">
            <div class="form-group">
                <label>URL-Prueba Obtención Códigos*</strong> </label>
                <div class="input-group">
                    <select id="id_url_pruebas_obtencion_codigos" name="id_url_pruebas_obtencion_codigos"
                        class="form-control selectpicker">
                        <option value="">Seleccionar</option>
                        @foreach ($urls as $url)
                        <option value="{{ $url->id }}" {{ ($item->id_url_pruebas_obtencion_codigos == $url->id) ? 'selected'
                            : '' }}>
                            {{ $url->nombre_servicio }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="form-group">
                <label>URL-Prueba Operaciones*</strong> </label>
                <div class="input-group">
                    <select id="id_url_pruebas_operaciones" name="id_url_pruebas_operaciones"
                        class="form-control selectpicker">
                        <option value="">Seleccionar</option>
                        @foreach ($urls as $url)
                        <option value="{{ $url->id }}" {{ ($item->id_url_pruebas_operaciones == $url->id) ? 'selected' : ''
                            }}>
                            {{ $url->nombre_servicio }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        
        <div class="col">
            <div class="form-group">
                <label>URL-Prueba Recepción Compras*</strong> </label>
                <div class="input-group">
                    <select id="id_url_pruebas_recepcion_compras" name="id_url_pruebas_recepcion_compras"
                        class="form-control selectpicker">
                        <option value="">Seleccionar</option>
                        @foreach ($urls as $url)
                        <option value="{{ $url->id }}" {{ ($item->id_url_pruebas_recepcion_compras == $url->id) ? 'selected'
                            : '' }}>
                            {{ $url->nombre_servicio }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="form-group">
                <label>URL-Prueba Sincronización Datos*</strong> </label>
                <div class="input-group">
                    <select id="id_url_pruebas_sincronizacion_datos" name="id_url_pruebas_sincronizacion_datos"
                        class="form-control selectpicker">
                        <option value="">Seleccionar</option>
                        @foreach ($urls as $url)
                        <option value="{{ $url->id }}" {{ ($item->id_url_pruebas_sincronizacion_datos == $url->id) ?
                            'selected' : '' }}>
                            {{ $url->nombre_servicio }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
</div>

<div id="produccion-items" class="col-6">
    <div class="form-group col">
        <label>URL-Produccion Obtención Códigos*</strong> </label>
        <div class="input-group">
            <select id="id_url_produccion_obtencion_codigos" name="id_url_produccion_obtencion_codigos"
                class="form-control selectpicker">
                <option value="">Seleccionar</option>
                @foreach ($urls as $url)
                <option value="{{ $url->id }}" {{ ($item->id_url_produccion_obtencion_codigos == $url->id) ? 'selected'
                    : '' }}>
                    {{ $url->nombre_servicio }}
                </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="form-group col">
        <label>URL-Produccion Operaciones*</strong> </label>
        <div class="input-group">
            <select id="id_url_produccion_operaciones" name="id_url_produccion_operaciones"
                class="form-control selectpicker">
                <option value="">Seleccionar</option>
                @foreach ($urls as $url)
                <option value="{{ $url->id }}" {{ ($item->id_url_produccion_operaciones == $url->id) ? 'selected' : ''
                    }}>
                    {{ $url->nombre_servicio }}
                </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="form-group col">
        <label>URL-Produccion Recepción Compras *</strong> </label>
        <div class="input-group">
            <select id="id_url_produccion_recepcion_compras" name="id_url_produccion_recepcion_compras"
                class="form-control selectpicker">
                <option value="">Seleccionar</option>
                @foreach ($urls as $url)
                <option value="{{ $url->id }}" {{ ($item->id_url_produccion_recepcion_compras == $url->id) ? 'selected'
                    : '' }}>
                    {{ $url->nombre_servicio }}
                </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group col">
        <label>URL-Produccion Sincronización Datos *</strong> </label>
        <div class="input-group">
            <select id="id_url_produccion_sincronizacion_datos" name="id_url_produccion_sincronizacion_datos"
                class="form-control selectpicker">
                <option value="">Seleccionar</option>
                <tr class="ui-selectonemenu-item ui-selectonemenu-row ui-widget-content" data-label="MEDIANO PLAZO" role="option" aria-selected="false" id="frmNuevoProveedor:acpCtasxCobrar:cb_plan_pagos_1"><td>MEDIANO PLAZO</td><td class="text-center">6</td><td class="text-center">30</td><td class="text-center">SI</td></tr>
                @foreach ($urls as $url)
                <option value="{{ $url->id }}" {{ ($item->id_url_produccion_sincronizacion_datos == $url->id) ?
                    'selected' : '' }}>
                    {{ $url->nombre_servicio }}
                </option>
                @endforeach
            </select>
        </div>
    </div>
    
    


</div>



<script>
    if ({{$item->ambiente}}==1) {
        $('#pruebas-items').hide();
    }else{
        $('#produccion-items').hide();
    }

    $(function () {
        $('#tipo_modalidad').on('change', function() {
            $('#ambiente_id').val("");
            $('#pruebas-items').hide();
            $('#produccion-items').hide();
            limpiarTodo();
            $('.selectpicker').selectpicker('refresh');
        });
    });
    $(function () {
        $('#ambiente_id').on('change', onSelectAmbiente);
    });
    var modalidad_id = document.getElementById('tipo_modalidad').value;

    function limpiarTodo() {
        $('select[name="id_url_pruebas_obtencion_codigos"]').empty();
        $('select[name="id_url_pruebas_operaciones"]').empty();
        $('select[name="id_url_pruebas_recepcion_compras"]').empty();
        $('select[name="id_url_pruebas_sincronizacion_datos"]').empty();
        $('select[name="id_url_produccion_obtencion_codigos"]').empty();
        $('select[name="id_url_produccion_operaciones"]').empty();
        $('select[name="id_url_produccion_recepcion_compras"]').empty();
        $('select[name="id_url_produccion_sincronizacion_datos"]').empty();
    }

    function setSeleccionarProd() {
        $('select[name="id_url_produccion_obtencion_codigos"]').append('<option value="">Seleccionar</option>');
        $('select[name="id_url_produccion_operaciones"]').append('<option value="">Seleccionar</option>');
        $('select[name="id_url_produccion_recepcion_compras"]').append('<option value="">Seleccionar</option>');
        $('select[name="id_url_produccion_sincronizacion_datos"]').append('<option value="">Seleccionar</option>');
    }
    function setSeleccionarPrue() {
        $('select[name="id_url_pruebas_obtencion_codigos"]').append('<option value="">Seleccionar</option>');
        $('select[name="id_url_pruebas_operaciones"]').append('<option value="">Seleccionar</option>');
        $('select[name="id_url_pruebas_recepcion_compras"]').append('<option value="">Seleccionar</option>');
        $('select[name="id_url_pruebas_sincronizacion_datos"]').append('<option value="">Seleccionar</option>');
    }

    function onSelectAmbiente() {
        var estatus = $(this).val();
        var modalidad_id = document.getElementById('tipo_modalidad').value;
        if (estatus === "") {
            // vacio
            console.log("Ocultar todo");
            console.log(modalidad_id);
            $('#pruebas-items').hide();
            $('#produccion-items').hide();
            return;
        }
        if (estatus == 1) {
            //1 producción
            console.log("URL Produccion");
            console.log("Estatus "+ estatus+ " | Modalidad "+ modalidad_id);
            $('#pruebas-items').hide();

            $.ajax({
                url: '../'+estatus+'/modalidad/'+modalidad_id,
                type: "GET",
                dataType: "json",
                success:function(data) {
                    limpiarTodo();
                    setSeleccionarProd();
                    console.log(data);
                    for (let i = 0; i < data.length; i++) {
                        $('select[name="id_url_produccion_obtencion_codigos"]').append('<option value="'+ data[i].id +'">'+ data[i].nombre_servicio +' | URL: '+data[i].ruta_url +'</option>');
                        $('select[name="id_url_produccion_operaciones"]').append('<option value="'+ data[i].id +'">'+ data[i].nombre_servicio +' | URL: '+data[i].ruta_url +'</option>');
                        $('select[name="id_url_produccion_recepcion_compras"]').append('<option value="'+ data[i].id +'">'+ data[i].nombre_servicio +' | URL: '+data[i].ruta_url +'</option>');
                        $('select[name="id_url_produccion_sincronizacion_datos"]').append('<option value="'+ data[i].id +'">'+ data[i].nombre_servicio +' | URL: '+data[i].ruta_url +'</option>');
                        console.log(data[i].nombre_servicio +' | URL: '+data[i].ruta_url);
                    };
                    $('.selectpicker').selectpicker('refresh');
                },
            });
            
            $('#produccion-items').show(1000);            
            return;
        }
        if (estatus == 2) {
            //2 prueba
            console.log("URL Pruebas");
            console.log("Estatus "+ estatus+ " | Modalidad "+ modalidad_id);
            $('#produccion-items').hide();
            
            $.ajax({
                url: '../'+estatus+'/modalidad/'+modalidad_id,
                type: "GET",
                dataType: "json",
                success:function(data) {
                    limpiarTodo();
                    setSeleccionarPrue();
                    console.log(data);
                    for (let i = 0; i < data.length; i++) {
                        $('select[name="id_url_pruebas_obtencion_codigos"]').append('<option value="'+ data[i].id +'">'+ data[i].nombre_servicio +' | URL: '+data[i].ruta_url +'</option>');
                        $('select[name="id_url_pruebas_operaciones"]').append('<option value="'+ data[i].id +'">'+ data[i].nombre_servicio +' | URL: '+data[i].ruta_url +'</option>');
                        $('select[name="id_url_pruebas_recepcion_compras"]').append('<option value="'+ data[i].id +'">'+ data[i].nombre_servicio +' | URL: '+data[i].ruta_url +'</option>');
                        $('select[name="id_url_pruebas_sincronizacion_datos"]').append('<option value="'+ data[i].id +'">'+ data[i].nombre_servicio +' | URL: '+data[i].ruta_url +'</option>');
                        console.log(data[i].nombre_servicio +' | URL: '+data[i].ruta_url);
                    };
                    $('.selectpicker').selectpicker('refresh');
                },
            });

            $('#pruebas-items').show(1000);
            return;
        }

    }
</script>