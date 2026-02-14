@extends('layout.main')
@section('content')
    @include('layout.partials.session-flash')

    <section>
        <div class="container-fluid">
            <form action="{{ route('parametric.siat') }}" class="btn siat-sincronizacion">
                <button class="btn btn-info">
                    <i class="dripicons-plus"></i>
                    {{ trans('file.Import Data SIAT') }}
                </button>
            </form>
        </div>
        <div class="table-responsive">
            <table id="parametric-table" class="table">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
                        <th>ID</th>
                        <th>Tipo Clasificador</th>
                        <th>Cod Clasificador</th>
                        <th>{{ trans('file.Description') }}</th>
                        <th>Sucursal</th>
                        <th>Punto de Venta</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($parametricas as $key => $parametrica)
                        <tr data-id="{{ $parametrica->id }}">
                            <td>{{ $key }}</td>
                            <td>{{ $parametrica->id }}</td>
                            <td>{{ $parametrica->tipo_clasificador }}</td>
                            <td>{{ $parametrica->codigo_clasificador }}</td>
                            <td>{{ $parametrica->descripcion }}</td>
                            <td>{{ $parametrica->sucursal }}</td>
                            <td>{{ $parametrica->codigo_punto_venta }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    {{-- <!-- Create Modal -->
<div id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
    class="modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            {!! Form::open(['route' => 'parametric.store', 'method' => 'post']) !!}
            <div class="modal-header">
                <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Add Parametric')}}</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                    <span aria-hidden="true">
                        <i class="dripicons-cross"></i></span>
                </button>
            </div>
            <div class="modal-body">
                <p class="italic">
                    <small>
                        {{trans('file.The field labels marked with * are required input fields')}}.
                    </small>
                </p>
                <form>
                    <div class="form-group">
                        <label>Código Clasificador *</label>
                        <input type="text" name="codigo_clasificador" required class="form-control" placeholder="1, 2, 3 ...">
                    </div>
                    <div class="form-group">
                        <label>{{__('file.Description')}} *</label>
                        <input type="text" name="descripcion" required class="form-control" placeholder="Punto de ...">

                    </div>
                    
                    <div class="form-group">
                        <input type="submit" value="{{trans('file.submit')}}" class="btn btn-primary">
                    </div>
                </form>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
<!-- Edit Modal -->
<div id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
    class="modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            {{ Form::open(['route' => ['parametric.update', 1], 'method' => 'PUT'] ) }}
            <div class="modal-header">
                <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Update Parametric')}}</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                    <span aria-hidden="true">
                        <i class="dripicons-cross"></i>
                    </span>
                </button>
            </div>
            <div class="modal-body">
                <p class="italic">
                    <small>
                        {{trans('file.The field labels marked with * are required input fields')}}.
                    </small>
                </p>
                <input type="hidden" name="parametrica_id"> 
                <div class="form-group">
                    <label>Código Clasificador *</label>
                    <input type="text" name="codigo_clasificador" required class="form-control" >
                </div>
                <div class="form-group">
                    <label>{{__('file.Description')}} *</label>
                    <input type="text" name="descripcion" required class="form-control" >

                </div>
                <div class="form-group" >
                    <label>Tipo Clasificador *</label>
                    <select name="tipo_clasificador" class="selectpicker form-control">
                        <option selected>Seleccionar</option>
                        <option value="DOCUMENT0_IDENTIDAD">DOCUMENT0_IDENTIDAD</option>
                        <option value="PAIS_ORIGEN">PAIS_ORIGEN</option>
                        <option value="EVENTOS_SIGNIFICATIVOS">EVENTOS_SIGNIFICATIVOS</option>
                        <option value="MENSAJES_SERVICIOS">MENSAJES_SERVICIOS</option>
                        <option value="DOCUMENTO_SECTOR">DOCUMENTO_SECTOR</option>
                        <option value="TIPO_EMISION">TIPO_EMISION</option>
                        <option value="TIPO_HABITACION">TIPO_HABITACION</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="submit" value="{{trans('file.submit')}}" class="btn btn-primary">
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div> --}}

    <script type="text/javascript">
        $("ul#setting #siat").siblings('a').attr('aria-expanded', 'true');
        $("ul#setting #siat").addClass("show");
        $("ul#setting #siat #siat-menu-par").addClass("active");

        var parametrica_id = [];

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function confirmDelete() {
            if (confirm("Are you sure want to delete?")) {
                return true;
            }
            return false;
        }
        $(document).ready(function() {
            $('.edit-btn').on('click', function() {
                $("#editModal input[name='parametrica_id']").val($(this).data('id'));
                $("#editModal input[name='codigo_clasificador']").val($(this).data('codigo_clasificador'));
                $("#editModal input[name='descripcion']").val($(this).data('descripcion'));
                $("#editModal select[name='tipo_clasificador']").val($(this).data('tipo_clasificador'));
                $('.selectpicker').selectpicker('refresh');
            });
        });

        $('#parametric-table').DataTable({
            "order": [],
            'language': {
                'lengthMenu': '_MENU_ {{ trans('file.records per page') }}',
                "info": '<small>{{ trans('file.Showing') }} _START_ - _END_ (_TOTAL_)</small>',
                "search": '{{ trans('file.Search') }}',
                'paginate': {
                    'previous': '<i class="dripicons-chevron-left"></i>',
                    'next': '<i class="dripicons-chevron-right"></i>'
                }
            },
            'columnDefs': [{
                    "orderable": false,
                    'targets': [0, 2]
                },
                {
                    'render': function(data, type, row, meta) {
                        if (type === 'display') {
                            data =
                                '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
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
            'select': {
                style: 'multi',
                selector: 'td:first-child'
            },
            'lengthMenu': [
                [10, 25, 50, -1],
                [10, 25, 50, "All"]
            ],
            dom: '<"row"lfB>rtip',
            buttons: [{
                    extend: 'pdf',
                    text: '{{ trans('file.PDF') }}',
                    exportOptions: {
                        columns: ':visible:Not(.not-exported)',
                        rows: ':visible'
                    },
                    footer: true
                },
                {
                    extend: 'csv',
                    text: '{{ trans('file.CSV') }}',
                    exportOptions: {
                        columns: ':visible:Not(.not-exported)',
                        rows: ':visible'
                    },
                    footer: true
                },
                {
                    extend: 'print',
                    text: '{{ trans('file.Print') }}',
                    exportOptions: {
                        columns: ':visible:Not(.not-exported)',
                        rows: ':visible'
                    },
                    footer: true
                },
                {
                    text: '{{ trans('file.delete') }}',
                    className: 'buttons-delete',
                    action: function(e, dt, node, config) {
                        parametrica_id.length = 0;
                        $(':checkbox:checked').each(function(i) {
                            if (i) {
                                parametrica_id[i - 1] = $(this).closest('tr').data('id');
                            }
                        });
                        if (parametrica_id.length && confirm("Are you sure want to delete?")) {
                            $.ajax({
                                type: 'POST',
                                url: 'parametric/deletebyselection',
                                data: {
                                    departmentIdArray: parametrica_id
                                },
                                success: function(data) {
                                    alert(data);
                                }
                            });
                            dt.rows({
                                page: 'current',
                                selected: true
                            }).remove().draw(false);
                        } else if (!parametrica_id.length)
                            alert('No department is selected!');
                    }
                },
                {
                    extend: 'colvis',
                    text: '{{ trans('file.Column visibility') }}',
                    columns: ':gt(0)'
                },
            ],
        });
    </script>

    @include('layout.partials.sweet-alert-siat.sweet-siat')
@endsection
