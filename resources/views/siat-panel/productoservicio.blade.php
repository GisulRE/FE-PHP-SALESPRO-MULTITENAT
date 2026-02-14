@extends('layout.main')
@section('content')

@include('layout.partials.session-flash')

<section>
    {{-- Navs siat --}}
    @include('layout.partials.navs-siat')
    {{-- Navs siat --}}

    <div class="container fluid mt-3">
        <div class="form-row form-group col-8">
            <div class="input-group col-6">
                <select id="id_select" name="id_select" class="form-control selectpicker">
                    @include('layout.partials.list-billers')
                </select>
            </div>
            <div class="input-group col-2">
                <a id="btnBuscar" class="btn btn-info " >Buscar</a>
            </div>
        </div>

        <div class="table-responsive">
            <table id="product-table" class="table">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
                        <th>COD</th>
                        <th>Actividad Económica</th>
                        <th>Código Producto</th>
                        <th>{{__('file.Description')}}</th>
                        <th>Sucursal</th>
                        <th>Punto de Venta</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productos as $key=>$prod)
                    <tr data-id="{{$prod->id}}">
                        <td>{{$key}}</td>
                        <td>{{ $prod->activity->codigo_caeb}}</td>
                        <td>{{ $prod->activity->descripcion}}</td>
                        <td>{{ $prod->codigo_producto }}</td>
                        <td>{{ $prod->descripcion_producto }}</td>
                        <td>{{ $prod->getSucursal->nombre }}</td>
                        <td>{{ $prod->getPuntoVenta->nombre_punto_venta }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</section>

    <script type="text/javascript">
        $("ul#siat").siblings('a').attr('aria-expanded','true');
        $("ul#siat").addClass("show");
        $("ul#siat #siat-menu-panel").addClass("active");

        

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
        $("ul#nav-siat #prod").addClass("active");

        $(function () {
            $('#id_select').on('change', onSelectBiller);
        });
        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        function onSelectBiller() {
            var biller_id = $(this).val();
            
            //Petición AJAX
            $('#btnBuscar').prop('href','{{ url("siat_panel/prod")}}' + '/' + biller_id );
            

        }
    </script>

@include('layout.partials.sweet-alert-siat.sweet-siat')
@endsection