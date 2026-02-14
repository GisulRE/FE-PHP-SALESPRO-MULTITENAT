@extends('layout.main')
@section('content')

@include('layout.partials.session-flash')

<section>
    <div class="container-fluid">
        <a href="{{route('autorizacion.create')}}" class="btn btn-info">
            <i class="dripicons-plus"></i> 
            Agregar Autorización Facturación
        </a>
    </div>

    <div class="table-responsive">
        <table id="item-table" class="table">
            <thead>
                <tr>
                    <th>Fecha Solicitud</th>
                    <th>Ambiente</th>
                    <th>Código Sistema</th>
                    <th>Estado</th>
                    <th>Fecha Vencimiento Token</th>
                    <th>Modalidad</th>
                    <th>Sistema</th>
                    <th class="not-exported">{{trans('file.action')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $key => $item)
                <tr data-id="{{$item['idautorizacion']}}">
                    <td>{{ \Carbon\Carbon::parse($item['fechaSolicitud'])->format($formato_fecha)  }}</td>
                    <td>
                        @if ($item['ambiente'] == 1)
                            PRODUCCIÓN
                        @else
                            PRUEBAS
                        @endif
                    </td>
                    <td>{{($item['codigo_sistema'])}}</td>
                    <td>
                        @if ( $item['estado'] == 'A' )
                            <span class="badge badge-success">Alta</span>                            
                        @else
                            <span class="badge badge-warning text-white">Baja</span>
                        @endif
                    </td>
                    <td>{{ \Carbon\Carbon::parse($item['fecha_vencimiento_token'])->format($formato_fecha)  }}</td>
                    <td>
                        @if ($item['tipo_modalidad'] == 1)
                            ELECTRONICA
                        @else
                            COMPUTARIZADA
                        @endif
                    </td>
                    <td>{{ $item['tipo_sistema'] }}</td>
                    <td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                {{trans('file.action')}}
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                                <li>
                                    <a type="button" class="btn btn-link" 
                                        href="{{ route('autorizacion.edit',$item['idautorizacion']) }}">
                                        <i class="dripicons-document-edit"></i> 
                                        {{trans('file.edit')}}
                                    </a>
                                </li>

                                <li class="divider"></li>
                                
                                @if ( $item['estado'] == 'B' )
                                    <li>
                                        <button type="button" 
                                            class="item-btn btn btn-link" 
                                            data-idautorizacion = "{{ $item['idautorizacion'] }}" 
                                            data-fecha_solicitud = "{{$item['fechaSolicitud'] }}" 
                                            data-tipo_ambiente = "{{ $item['ambiente'] }}" 
                                            data-tipo_modalidad = "{{ $item['tipo_modalidad'] }}" 
                                            data-codigo_sistema = "{{ $item['codigo_sistema'] }}" 
                                            data-fecha_vencimiento_token = "{{ $item['fecha_vencimiento_token'] }}" 
                                            data-tipo_sistema = "{{ $item['tipo_sistema'] }}" 
                                            data-toggle="modal" 
                                            data-target="#modal-informacion"
                                            >
                                            <i class="dripicons-swap"></i> 
                                            Activar
                                        </button>
                                    </li>                                
                                @endif
                            </ul>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<!-- Modal -->
<div id="modal-informacion" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
    class="modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="exampleModalLabel" class="modal-title">Información Credenciales</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i
                            class="dripicons-cross"></i></span></button>
            </div>
            <div class="modal-body">
                @include('layout.partials.spinner-ajax')
                <p class="italic">
                    <small>
                        Información Autorización/Facturación.*
                        <button class="btn_activar btn btn-info ml-3">
                            <i class="fa fa-refresh"></i>
                            Activar
                        </button>
                    </small>
                </p>
                <fieldset disabled>
                    <div class="row">
                        <div class="col form-group">
                            <input type="hidden" name="autorizacion_id" class="form-control"> 
                            <label>Fecha Solicitud *</strong> </label>
                            <input type="date" name="fecha_solicitud" id="fecha_solicitud" class="form-control"> 
                        </div>
                        <div class="col form-group">
                            <label>Ambiente *</strong> </label>
                            <div class="input-group">
                                <select id="tipo_ambiente" name="tipo_ambiente" title="Seleccionar..." class="form-control selectpicker">
                                    <option value="1">Producción </option>
                                    <option value="2">Pruebas</option>
                                </select>                                                
                            </div>
                        </div>
                        <div class="col form-group">
                            <label>Modalidad *</strong> </label>
                            <div class="input-group">
                                <select id="tipo_modalidad" name="tipo_modalidad" title="Seleccionar..." class="form-control selectpicker">
                                    <option value="1">ELECTRONICA </option>
                                    <option value="2">COMPUTARIZADA</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-5 form-group">
                            <label>Código Sistema *</strong> </label>
                            <input type="text" name="codigo_sistema" id="codigo_sistema" class="form-control">
                        </div>
                        <div class="col-4 form-group">
                            <label>Fecha Vencimiento Token *</strong> </label>
                            <input type="date" name="fecha_vencimiento_token" id="fecha_vencimiento_token" class="form-control"> 
                        </div>
                        <div class="col-3 form-group">
                            <label>Tipo Sistema *</strong> </label>
                            <div class="input-group">
                                <select name="tipo_sistema" class="form-control selectpicker">
                                    <option value="PROPIO">Propio</option>
                                    <option value="PROVEEDOR">Proveedor</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <hr>
                <div id="cajaOperaciones">
                    <p class="italic">
                        <small>
                            Se solicita renovar los CUIS y CUFD de los Puntos de Venta.*
                        </small>
                    </p>
                    <div class="row justify-content-center">
                        <div class="col">
                            <button class="btn_renovar_todos btn btn-warning">
                                <i class="fa fa-refresh"></i>
                                Cuis Renovación Masiva
                            </button>
                            
                        </div>
                        <div class="col">
                            <button type="button" class="forzar-renovar-cufd btn btn-warning">Renovar CUFD (Todos)</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer mt-3">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('file.Close')}}</button>
                </div>
            </div>


        </div>
    </div>
</div>

    <script type="text/javascript">
        $("ul#siat").siblings('a').attr('aria-expanded','true');
        $("ul#siat").addClass("show");
        $("ul#siat #siat-menu-autfac").addClass("active");

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.item-btn').on('click', function(){
            $("#cajaOperaciones").hide();
            $("input[name='autorizacion_id']").val($(this).data('idautorizacion'));
            $("input[name='fecha_solicitud']").val($(this).data('fecha_solicitud'));
            $("select[name='tipo_ambiente']").val($(this).data('tipo_ambiente'));
            $("select[name='tipo_modalidad']").val($(this).data('tipo_modalidad'));
            $("input[name='codigo_sistema']").val($(this).data('codigo_sistema'));
            $("input[name='fecha_vencimiento_token']").val($(this).data('fecha_vencimiento_token'));
            $("input[name='tipo_sistema']").val($(this).data('tipo_sistema'));
            $('.selectpicker').selectpicker('refresh');
            $('#exampleModal').modal('hide');
        
        });

        $('#item-table').DataTable( {
            "order": [
                ['0', 'desc']
            ],
            'language': {
                'lengthMenu': '_MENU_ {{trans("file.records per page")}}',
                "info":      '<small>{{trans("file.Showing")}} _START_ - _END_ (_TOTAL_)</small>',
                "search":  '{{trans("file.Search")}}',
                'paginate': {
                        'previous': '<i class="dripicons-chevron-left"></i>',
                        'next': '<i class="dripicons-chevron-right"></i>'
                }
            },
            'columnDefs': [
                {
                    "orderable": false,
                    'targets': [0, 2]
                }
            ],
            'select': { style: 'multi',  selector: 'td:first-child'},
            'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, "All"]],
            dom: '<"row"lfB>rtip',
            buttons: [
                {
                    extend: 'pdf',
                    text: '{{trans("file.PDF")}}',
                    exportOptions: {
                        columns: ':visible:Not(.not-exported)',
                        rows: ':visible'
                    },
                    footer:true
                },
                {
                    extend: 'csv',
                    text: '{{trans("file.CSV")}}',
                    exportOptions: {
                        columns: ':visible:Not(.not-exported)',
                        rows: ':visible'
                    },
                    footer:true
                },
                {
                    extend: 'print',
                    text: '{{trans("file.Print")}}',
                    exportOptions: {
                        columns: ':visible:Not(.not-exported)',
                        rows: ':visible'
                    },
                    footer:true
                },
                {
                    extend: 'colvis',
                    text: '{{trans("file.Column visibility")}}',
                    columns: ':gt(0)'
                },
            ],
        } );


        // Permite activar en la BD la autorización/facturación seleccionada
        $(document).on("click", ".btn_activar", function(event) {
            var id = $('input[name="autorizacion_id"]').val();
            var url = '{{ route('autorizacion.cambiar_estado', ':id') }}';
            url_data = url.replace(':id', id);

            console.log('LA URL es => '+ url_data);
            $("#spinner-div").show();
            $.ajax({
                url: url_data,
                type: "GET",
                success: function(data) {
                    if (data.status) {
                        // mostrar operaciones y ocultar spinner 
                        $("#cajaOperaciones").show();
                    } else {
                        // Mostrar errores 
                        swal("Advertencia",
                        "Nota: " + data.mensaje,
                        "warning"
                        );
                    }
                    
                },
                complete: function () {
                    $("#spinner-div").hide(); //Ocultar icon spinner de cargando
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    $("#spinner-div").hide();
                    swal("Error",
                        "Ocurrio un error del servidor, intente de nuevo ó contacte a soporte \n" +
                        "error: " + errorThrown,
                        "error");
                }
            });
        });

        // Botón ajax para forzar la renovación de los cufd.
        $(document).on("click", ".forzar-renovar-cufd", function(event) {
            var url_data = "{{ route('forzar_renovar_cufd') }}";
            
            $("#spinner-div").show(); //Mostrar icon spinner de cargando
            $.ajax({
                url: url_data, 
                type: "GET",
                success: function (data) {
                    if (data == true) {
                        swal('Renovación Exitosa', 'Cufd renovados para todos los puntos de venta!'); 
                        location.reload();
                    }
                    else{
                        swal('Error', 'no se logró renovar los cufd'); 
                    }
                },
                complete: function () {
                    $("#spinner-div").hide(); //Ocultar icon spinner de cargando
                },
                error: function () {
                    swal('Error', 'error en el servicio'); 
                },
            });
        });

        $(document).on("click", ".btn_renovar_todos", function(event) {
            $("#spinner-div").show();
            $.ajax({
                url: "punto_venta/renovacion-masiva-cuis",
                type: "GET",
                success: function(data) {
                    console.log(data);
                    if (data.status) {
                        swal("Mensaje", data.mensaje, "success");
                        $("#spinner-div").hide();
                    } else{
                        swal("Error", "Error: " + data.mensaje, "error");
                        $("#spinner-div").hide();
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    swal("Error",
                        "Ocurrio un error del servidor, intente de nuevo ó contacte a soporte \n" +
                        "error: " + errorThrown,
                        "error");
                }
            });
        });
    </script>

@include('layout.partials.sweet-alert-siat.sweet-siat')
@endsection