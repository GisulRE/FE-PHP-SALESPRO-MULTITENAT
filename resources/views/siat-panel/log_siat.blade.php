@extends('layout.main')
@section('content')

@include('layout.partials.session-flash')

<section>
    {{-- Navs siat --}}
    @include('layout.partials.navs-siat')
    {{-- Navs siat --}}

    <div class="container fluid mt-4">
        <div class="form-row form-group">
            <div class="input-group col-3">
                <label>Sucursal</label>
                <select id="sucursales_id" name="sucursal" class="form-control selectpicker" data-live-search="true" data-live-search-style="begins" title="Seleccionar sucursal...">
                    @foreach ($sucursales as $sucursal)
                        <option value="{{ $sucursal->sucursal }}">{{ $sucursal->sucursal}}.- {{ $sucursal->nombre}} | {{ $sucursal->domicilio_tributario }}</option>
                    @endforeach
                </select>
            </div>
            <div class="input-group col-3">
                <label>Punto de Venta</label>
                <select id="punto_venta" name="punto_venta" class="form-control selectpicker" title="Punto Venta...">
                    
                </select>
            </div>
            <div class="form-group col-2">
                <label >CUIS </label>
                <input type="text" name="cuis" class="form-control" disabled>
            </div>
            <div class="form-group col-2">
                <label >NIT </label>
                <input type="text" name="nit" class="form-control" disabled value="{{ $nit[0]}}">
            </div>
            <div class=" col-2">
                <a id="btnBuscar" class="btn btn-info " >Buscar</a>
            </div>
        </div>

        <div class="table-responsive">
            <table id="product-table" class="table">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th class="not-exported">{{__('file.action')}}</th>
                        <th>Fecha Modificación</th>
                        <th>Usuario</th>
                    </tr>
                </thead>
                <tbody id="tblInsumos">
                    @foreach($datos as $key=>$dato)
                    <tr data-id="{{$dato->id}}">
                        <td>{{$key}}</td>
                        <td>{{ $dato->descripcion}}</td>
                        <td>
                            @if ( $dato->estado == 1 )
                                <div class="badge badge-success">Sincronizado</div>                            
                            @else
                                <div class="badge badge-warning">No Sincronizado</div>
                            @endif
                        </td>
                        <td>
                            <button type="button" 
                                data-id="{{$dato->id}}"
                                data-operacion="{{$dato->operacion}}" 
                                data-sucursal="{{$dato->sucursal}}" 
                                data-codigo_punto_venta="{{$dato->codigo_punto_venta}}" 
                                class="edit-btn btn btn-link"
                                data-toggle="modal" data-target="#siatModal">
                                <i class="dripicons-document-edit"></i> 
                                Sincronizar
                            </button>
                        </td>
                        <td>{{ $dato->updated_at }}</td>
                        <td>{{ $dato->getUsuario->name }}</td>  
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</section>
<div id="siatModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
    class="modal fade text-left">
    <div class="modal-dialog ">
        <div class="modal-content">
            {{ Form::open(['route' => ['siat_panel.update', 1], 'method' => 'PUT'] ) }}
            <div class="modal-header">
                <h5 id="exampleModalLabel" class="modal-title">{{__('file.Import Data SIAT')}}</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                    <span aria-hidden="true">
                        <i class="dripicons-cross"></i></span>
                </button>
            </div>
            <div class="modal-body">
                <p class="italic">
                    <small>
                        Este proceso importará información del SIAT.* (Servicio de Impuestos)
                    </small>
                </p>
                <input type="hidden" name="registro_id">
                <input type="hidden" name="operacion">
                <input type="hidden" name="sucursal">
                <input type="hidden" name="codigo_punto_venta">
                <input type="hidden" name="nit">
                <div class="modal-footer">
                    <div>
                        <b>
                            ¿Está seguro?
                        </b> 
                    </div>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('file.Close')}}</button>
                    <div class="">
                        <input type="submit" value="{{__('file.submit')}}" class="btn btn-primary">
                    </div>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>

    <script type="text/javascript">
        $("ul#siat").siblings('a').attr('aria-expanded','true');
        $("ul#siat").addClass("show");
        $("ul#siat #siat-menu-panel").addClass("active");

        //llenar de valores el modal, cuando se dé click en editar
        $('.edit-btn').on('click', function(){
            $("#siatModal input[name='registro_id']").val($(this).data('id'));
            $("#siatModal input[name='operacion']").val($(this).data('operacion'));
            $("#siatModal input[name='sucursal']").val($(this).data('sucursal'));
            $("#siatModal input[name='codigo_punto_venta']").val($(this).data('codigo_punto_venta'));
            
            $("#siatModal input[name='nit']").val($("input[name='nit']").val());
            $('.selectpicker').selectpicker('refresh');
        });
        

        $('#product-table').DataTable( {
            
            "order": [],
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
                },
                {
                    'render': function(data, type, row, meta){
                        if(type === 'display'){
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                    return data;
                    },
                    'checkboxes': {
                    'selectRow': true,
                    'selectAllRender': '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>'
                    },
                    'targets': [0]
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
    </script>

    <script>
        $("ul#nav-siat #log").addClass("active");
        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#sucursales_id').on('change', function() {
            sucursal_ID = $(this).val();
            console.log(sucursal_ID);
            if(sucursal_ID){
                getPuntoVenta(sucursal_ID)
            }else{    
                $('select[name="punto_venta"]').empty()
            }
        });
        function getPuntoVenta(sucursal_ID){
            $.ajax({
                url: 'p_venta/'+sucursal_ID,
                type: "GET",
                dataType: "json",
                success:function(data) {
                    console.log(data);
                    $('select[name="punto_venta"]').empty()
                    $('input[name="cuis"]').val('');
                    $('select[name="punto_venta"]').append('<option value="'+ data.codigo_punto_venta +'">'+ data.nombre_punto_venta +'</option>');
                    $('.selectpicker').selectpicker('refresh');
                },
            });
        }
        //obtener CUIS y NIT
        $('select[name="punto_venta"]').on('change', function() {
            p_ventaID = $(this).val();
            if(p_ventaID){
                getCuis(p_ventaID);
                onSiatSincronizacion(); 
            }else{    
                $('select[name="punto_venta"]').empty()
            }
        });
        function getCuis(p_ventaID){
            var sucursal = $('#sucursales_id').val();
            $.ajax({
                url: 'cuis/'+sucursal+'/'+p_ventaID,
                type: "GET",
                dataType: "json",
                success:function(data) {
                    $('input[name="cuis"]').val('');
                    $.each(data, function(key, value) {
                        $('input[name="cuis"]').val(value);
                    });
                    $('.selectpicker').selectpicker('refresh');
                },
            });
        }


        

        //funciona para llamar vista con los parámetros Sucursal-PuntoVenta
        function onSiatSincronizacion() {
            var sucursal_id = $("select[name='sucursal']").val();
            var p_venta_id  = $("select[name='punto_venta']").val();
            var cuis        = $("input[name='cuis']").val();
            var nit         = $("input[name='nit']").val();
            //Petición AJAX
            $('#btnBuscar').prop('href','{{ url("siat_panel/registros-siat")}}' + '/suc/' + sucursal_id+ '/pv/' + p_venta_id );
            console.log("funcionando botón")
        }

        
    </script>

@include('layout.partials.sweet-alert-siat.sweet-siat')
@endsection