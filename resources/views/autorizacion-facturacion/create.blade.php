@extends('layout.main')

@section('content')
<section class="forms">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h4>Añadir Autorización Facturación</h4>
                    </div>
                    
                    <div class="card-body">
                        <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
                        <form action="{{ route('autorizacion.store') }}" method="POST">
                            @csrf 
                            <div class="row">
                                <div class="row col-12">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="nit_empresa">NIT</label>
                                            <input name="nit_empresa" type="text" readonly class="form-control" value="{{$pos_setting->nit_emisor}}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Modalidad *</strong> </label>
                                            <div class="input-group">
                                                <select id="tipo_modalidad" name="tipo_modalidad" title="Seleccionar..." onchange="buscarDocumentoSector();" class="form-control selectpicker" required>
                                                    <option value="1">ELECTRONICA </option>
                                                    <option value="2">COMPUTARIZADA</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Ambiente *</strong> </label>
                                            <div class="input-group">
                                                <select id="tipo_ambiente" name="tipo_ambiente" title="Seleccionar..." onchange="buscarDocumentoSector();" class="form-control selectpicker" required>
                                                    <option value="1">Producción </option>
                                                    <option value="2">Pruebas</option>
                                                </select>                                                
                                            </div>
                                        </div>
                                    </div>                                    
                                </div>
                                <div class="row col-12">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Documento Sector</label>
                                            <div id="list_documento_sector" class="d-flex flex-column">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="documento_sector" value="1">
                                                    <label class="form-check-label">Compra Venta</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="documento_sector" value="2">
                                                    <label class="form-check-label">Alquiler</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="documento_sector" value="24">
                                                    <label class="form-check-label">Nota Credito</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="form-group">
                                            <label>URLs</label>
                                            <div id="list_urls" class="d-flex flex-column">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row col-12">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Fecha Solicitud *</strong> </label>
                                            <input type="date" name="fecha_solicitud" id="fecha_solicitud" class="form-control" required> 
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Tipo Sistema *</strong> </label>
                                            <div class="input-group">
                                                <select name="tipo_sistema" class="form-control selectpicker" required>
                                                    <option value="PROPIO">Propio</option>
                                                    <option value="PROVEEDOR">Proveedor</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Código Sistema *</strong> </label>
                                            <input type="text" name="codigo_sistema" id="codigo_sistema" class="form-control" required>
                                        </div>
                                    </div>
                                
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Token *</strong> </label>
                                            <textarea class="form-control" name="token" id="token"  rows="10" required></textarea>
                                        </div>
                                    </div>
                                
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Fecha Vencimiento Token *</strong> </label>
                                            <input type="date" name="fecha_vencimiento_token" id="fecha_vencimiento_token" class="form-control" required> 
                                        </div>
                                    </div>                            

                                </div>
                                <div class="col-12 mt-5">
                                    <div class="form-group">
                                        <input type="submit" value="{{trans('file.submit')}}" id="submit-btn" class="btn btn-primary">
                                    </div>
                                </div>
                            
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">

    $("ul#siat").siblings('a').attr('aria-expanded','true');
    $("ul#siat").addClass("show");
    $("ul#siat #siat-menu-autfac").addClass("active");

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var list_checked = [];
    $(document).on('click', 'input:checkbox', getCheckedBox);    
    function getCheckedBox() {
        list_checked = $.map($('input:checkbox:checked'), function(val, i) {
            return val.value;
        });
        console.clear();
        console.log(list_checked);
        // swal("info", "Lista seleccionada => " + list_checked);
    }

    function buscarDocumentoSector() {
        var modalidad_id = $("#tipo_modalidad").val();
        var ambiente_id = $("#tipo_ambiente").val();
        
        if (modalidad_id == "") {
            swal("Mensaje", "Seleccione una Modalidad", "info");
            return;
        }
        if (ambiente_id == "") {
            swal("Mensaje", "Seleccione una Ambiente", "info");
            return;
        }
        var url_data = '{{ route('buscar_documento_sector') }}';

        $.ajax({
            url: url_data,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                modalidad: modalidad_id,
                ambiente: ambiente_id
            },
            success: function(data) {
                $("#list_documento_sector").empty();
                $("#list_urls").empty();
                console.clear();
                console.log(data);
                var count = data.length;
                if (count > 0) {
                    $.each(data, function(key, value) {
                        var htmlDocumentoSector = '<div class="form-check form-check-inline"> ' +
                                        '<input class="form-check-input" type="checkbox" name="documento_sector[]" value="' + 
                                            value['codigo_sin'] + '">' +
                                        '<label class="form-check-label">' + value['descripcion_sin'] +'</label> </div>'
                        var htmlURLs = '<div class="form-check form-check-inline"> ' +
                                        '<label class="form-check-label">' + value['urlServices']['ruta_url'] +'</label> </div>'

                        $('#list_documento_sector').append(htmlDocumentoSector);
                        $('#list_urls').append(htmlURLs);
                    });
                } else {                        
                    swal('Información: ', 'No existen registros.');
                }
            },
            error: function() {
                swal('Error', 'Error en los servicios');
            },
        });
    }


</script>
@endsection
