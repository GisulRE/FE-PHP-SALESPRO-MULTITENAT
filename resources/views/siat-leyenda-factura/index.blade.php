@extends('layout.main')
@section('content')
    @include('layout.partials.session-flash')

    <section>
        <div class="container-fluid">
            <form action="{{ route('legends.siat') }}" class="btn siat-sincronizacion">
                <button class="btn btn-info">
                    <i class="dripicons-plus"></i>
                    {{ trans('file.Import Data SIAT') }}
                </button>
            </form>
        </div>
        <div class="table-responsive">
            <table id="legend-table" class="table">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
                        <th>COD</th>
                        <th>Actividad Económica</th>
                        <th>{{ trans('file.Description') }}</th>
                        <th>Sucursal</th>
                        <th>Punto de Venta</th>
                        <th class="not-exported">{{ trans('file.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($leyendas as $key => $leyenda)
                        <tr data-id="{{ $leyenda->id }}">
                            <td>{{ $key }}</td>
                            <td>{{ $leyenda->activity->codigo_caeb }}</td>
                            <td>{{ $leyenda->activity->descripcion }}</td>
                            <td>{{ $leyenda->descripcion_leyenda }}</td>
                            <td>{{ $leyenda->sucursal }}</td>
                            <td>{{ $leyenda->codigo_punto_venta }}</td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default btn-sm dropdown-toggle"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        {{ trans('file.action') }}
                                        <span class="caret"></span>
                                        <span class="sr-only">Toggle Dropdown</span>
                                    </button>
                                    <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default"
                                        user="menu">
                                        <li>
                                            <button type="button" data-id="{{ $leyenda->id }}"
                                                data-descripcion_leyenda="{{ $leyenda->descripcion_leyenda }}"
                                                data-actividad_id="{{ $leyenda->actividad_id }}"
                                                class="edit-btn btn btn-link" data-toggle="modal" data-target="#editModal">
                                                <i class="dripicons-document-edit"></i>
                                                {{ trans('file.edit') }}
                                            </button>
                                        </li>

                                        <li class="divider"></li>

                                        {{ Form::open(['route' => ['legends.destroy', $leyenda->id], 'method' => 'DELETE']) }}
                                        <li>
                                            <button type="submit" class="btn btn-link" onclick="return confirmDelete()">
                                                <i class="dripicons-trash"></i>
                                                {{ trans('file.delete') }}
                                            </button>
                                        </li>
                                        {{ Form::close() }}
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <!-- Create Modal -->
    <div id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                {!! Form::open(['route' => 'legends.store', 'method' => 'post']) !!}
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Add Invoice Legends') }}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                        <span aria-hidden="true">
                            <i class="dripicons-cross"></i></span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="italic">
                        <small>
                            {{ trans('file.The field labels marked with * are required input fields') }}.
                        </small>
                    </p>
                    <form>
                        <div class="form-group">
                            <label>{{ trans('file.Description') }} *</label>
                            <input type="text" name="descripcion_leyenda" required class="form-control"
                                placeholder="Descripción Leyenda de Factura...">
                        </div>
                        <div class="form-group">
                            <label>Tipo Actividad *</label>
                            <select name="actividad_id" class="selectpicker form-control" required>
                                <option selected>Seleccionar</option>
                                @foreach ($actividades as $actividad)
                                    <option value="{{ $actividad->id }}">{{ $actividad->codigo_caeb }} |
                                        {{ $actividad->descripcion }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <input type="submit" value="{{ trans('file.submit') }}" class="btn btn-primary">
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
                {{ Form::open(['route' => ['legends.update', 1], 'method' => 'PUT']) }}
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Update Invoice Legends') }}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                        <span aria-hidden="true">
                            <i class="dripicons-cross"></i>
                        </span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="italic">
                        <small>
                        </small>
                    </p>
                    <input type="hidden" name="leyenda_id">
                    <div class="form-group">
                        <label>{{ trans('file.Description') }} *</label>
                        <input type="text" name="descripcion_leyenda" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Tipo Actividad *</label>
                        <select name="actividad_id" class="selectpicker form-control" required>
                            <option selected>Seleccionar</option>
                            @foreach ($actividades as $actividad)
                                <option value="{{ $actividad->id }}">{{ $actividad->codigo_caeb }} |
                                    {{ $actividad->descripcion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="submit" value="{{ trans('file.submit') }}" class="btn btn-primary">
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $("ul#setting #siat").siblings('a').attr('aria-expanded', 'true');
        $("ul#setting #siat").addClass("show");
        $("ul#setting #siat #siat-menu-leg").addClass("active");

        var leyenda_id = [];

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
                $("#editModal input[name='leyenda_id']").val($(this).data('id'));
                $("#editModal input[name='descripcion_leyenda']").val($(this).data('descripcion_leyenda'));
                $("#editModal select[name='actividad_id']").val($(this).data('actividad_id'));
                $('.selectpicker').selectpicker('refresh');
            });
        });

        $('#legend-table').DataTable({
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
                        leyenda_id.length = 0;
                        $(':checkbox:checked').each(function(i) {
                            if (i) {
                                leyenda_id[i - 1] = $(this).closest('tr').data('id');
                            }
                        });
                        if (leyenda_id.length && confirm("Are you sure want to delete?")) {
                            $.ajax({
                                type: 'POST',
                                url: 'legends/deletebyselection',
                                data: {
                                    departmentIdArray: leyenda_id
                                },
                                success: function(data) {
                                    alert(data);
                                }
                            });
                            dt.rows({
                                page: 'current',
                                selected: true
                            }).remove().draw(false);
                        } else if (!leyenda_id.length)
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
