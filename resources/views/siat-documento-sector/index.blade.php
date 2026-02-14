@extends('layout.main')
@section('content')
    @include('layout.partials.session-flash')

    <section>
        <div class="container-fluid">
            <form action="{{ route('documentsector.siat') }}" class="btn siat-sincronizacion">
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
                        <th>Actividad Economica</th>
                        <th>Código Documento Sector</th>
                        <th>{{ __('file.Description') }}</th>
                        <th>Sucursal</th>
                        <th>Codigo Punto Venta</th>
                        <th class="not-exported">{{ __('file.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($documentos as $key => $documento)
                        <tr data-id="{{ $documento->id }}">
                            <td>{{ $key }}</td>
                            <td>{{ $documento->activity->codigo_caeb }}</td>
                            <td>{{ $documento->activity->descripcion }}</td>
                            <td>{{ $documento->codigo_documento_sector }}</td>
                            <td>{{ $documento->tipo_documento_sector }}</td>
                            <td>{{ $documento->sucursal }}</td>
                            <td>{{ $documento->codigo_punto_venta }}</td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default btn-sm dropdown-toggle"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        {{ __('file.action') }}
                                        <span class="caret"></span>
                                        <span class="sr-only">Toggle Dropdown</span>
                                    </button>
                                    <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default"
                                        user="menu">
                                        <li>
                                            <button type="button" data-id="{{ $documento->id }}"
                                                data-codigo_documento_sector="{{ $documento->codigo_documento_sector }}"
                                                data-tipo_documento_sector="{{ $documento->tipo_documento_sector }}"
                                                data-actividad_id="{{ $documento->actividad_id }}"
                                                class="edit-btn btn btn-link" data-toggle="modal" data-target="#editModal">
                                                <i class="dripicons-document-edit"></i>
                                                {{ trans('file.edit') }}
                                            </button>
                                        </li>

                                        <li class="divider"></li>

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
                {!! Form::open(['route' => 'documentsector.store', 'method' => 'post']) !!}
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Add Sector Document') }}</h5>
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
                            <label>Código Documento Sector *</label>
                            <input type="text" name="codigo_documento_sector" required class="form-control"
                                placeholder="1, 24, 28, 34, 35...">
                        </div>
                        <div class="form-group">
                            <label>Tipo Documento Sector *</label>
                            <input type="text" name="tipo_documento_sector" required class="form-control"
                                placeholder="FAC_CVB, NCD, FAC_SEG, FCV, FCOEXS...">
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
                {{ Form::open(['route' => ['documentsector.update', 1], 'method' => 'PUT']) }}
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Update Sector Document') }}</h5>
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
                    <input type="hidden" name="documento_id">
                    <div class="form-group">
                        <label>Código Documento Sector *</label>
                        <input type="text" name="codigo_documento_sector" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Tipo Documento Sector *</label>
                        <input type="text" name="tipo_documento_sector" required class="form-control">
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
        $("ul#setting #siat #siat-menu-doc").addClass("active");

        var documento_id = [];

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
                $("#editModal input[name='documento_id']").val($(this).data('id'));
                $("#editModal input[name='codigo_documento_sector']").val($(this).data(
                    'codigo_documento_sector'));
                $("#editModal input[name='tipo_documento_sector']").val($(this).data(
                    'tipo_documento_sector'));
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
                        documento_id.length = 0;
                        $(':checkbox:checked').each(function(i) {
                            if (i) {
                                documento_id[i - 1] = $(this).closest('tr').data('id');
                            }
                        });
                        if (documento_id.length && confirm("Are you sure want to delete?")) {
                            $.ajax({
                                type: 'POST',
                                url: 'documentsector/deletebyselection',
                                data: {
                                    departmentIdArray: documento_id
                                },
                                success: function(data) {
                                    alert(data);
                                }
                            });
                            dt.rows({
                                page: 'current',
                                selected: true
                            }).remove().draw(false);
                        } else if (!documento_id.length)
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
