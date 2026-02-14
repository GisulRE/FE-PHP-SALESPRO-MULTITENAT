@extends('layout.main')
@section('content')
    @include('layout.partials.session-flash')

    <section>
        <div class="container-fluid">
            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#siatModal">
                <i class="dripicons-plus"></i>
                {{ trans('file.Import Data SIAT') }}
            </button>
            <form action="{{ route('activities.siat') }}" class="btn siat-sincronizacion">
                <button class="btn btn-info">
                    <i class="dripicons-plus"></i>
                    {{ trans('file.Import Data SIAT') }}
                </button>
            </form>
        </div>
        <div class="table-responsive">
            <table id="activity-table" class="table">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
                        <th>Cod CAEB</th>
                        <th>{{ trans('file.Description') }}</th>
                        <th>Tipo Actividad</th>
                        <th>Sucursal</th>
                        <th>Codigo Punto Venta</th>
                        <th class="not-exported">{{ trans('file.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($actividades as $key => $actividad)
                        <tr data-id="{{ $actividad->id }}">
                            <td>{{ $key }}</td>
                            <td>{{ $actividad->codigo_caeb }}</td>
                            <td>{{ $actividad->descripcion }}</td>
                            <td>{{ $actividad->tipo_actividad }}</td>
                            <td>{{ $actividad->sucursal }}</td>
                            <td>{{ $actividad->codigo_punto_venta }}</td>
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
                                            <button type="button" data-id="{{ $actividad->id }}"
                                                data-codigo_caeb="{{ $actividad->codigo_caeb }}"
                                                data-descripcion="{{ $actividad->descripcion }}"
                                                data-tipo_actividad="{{ $actividad->tipo_actividad }}"
                                                class="edit-btn btn btn-link" data-toggle="modal" data-target="#editModal">
                                                <i class="dripicons-document-edit"></i>
                                                {{ trans('file.edit') }}
                                            </button>
                                        </li>

                                        <li class="divider"></li>

                                        {{ Form::open(['route' => ['activities.destroy', $actividad->id], 'method' => 'DELETE']) }}
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
    <!-- SIAT Modal -->
    <div id="siatModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div class="modal-dialog ">
            <div class="modal-content">
                {!! Form::open(['route' => 'activities.siat', 'method' => 'get']) !!}
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{ __('file.Import Data SIAT') }}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                        <span aria-hidden="true">
                            <i class="dripicons-cross"></i></span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="italic">
                        <small>
                            {{ __('file.This process aims to delete the data, and then insert data from SIAT') }}.
                        </small>
                    </p>
                    <div class="modal-footer">
                        <div>
                            <b>
                                ¿Está seguro?
                            </b>
                        </div>
                        <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">{{ __('file.Close') }}</button>
                        <div class="">
                            <input type="submit" value="{{ __('file.submit') }}" class="btn btn-primary">
                        </div>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
    <!-- Create Modal -->
    <div id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                {!! Form::open(['route' => 'activities.store', 'method' => 'post']) !!}
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Add Economic Activity') }}</h5>
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
                            {{ Form::text('descripcion', null, [
                                'required' => 'required',
                                'class' => 'form-control',
                                'placeholder' => 'Nombre de Actividad económica...',
                            ]) }}
                        </div>
                        <div class="form-group">
                            <label>Tipo Actividad *</label>
                            <select name="tipo_actividad" class="selectpicker form-control">
                                <option selected>Seleccionar</option>
                                <option value="P">P</option>
                                <option value="S">S</option>
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
                {{ Form::open(['route' => ['activities.update', 1], 'method' => 'PUT']) }}
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Update Economic Activity') }}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                        <span aria-hidden="true">
                            <i class="dripicons-cross"></i>
                        </span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="italic">
                        <small>
                            {{ trans('file.The field labels marked with * are required input fields') }}.
                        </small>
                    </p>
                    <input type="hidden" name="actividad_id">
                    <div class="form-group">
                        <label>Código CAEB *</label>
                        <input type="text" name="codigo_caeb" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{ trans('file.Description') }} *</label>
                        <input type="text" name="descripcion" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Tipo Actividad *</label>
                        <select name="tipo_actividad" class="selectpicker form-control">
                            <option value="P">P</option>
                            <option value="S">S</option>
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
        $("ul#setting #siat #siat-menu-act").addClass("active");

        var actividad_id = [];
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
        };
        $(document).ready(function() {
            $('.edit-btn').on('click', function() {
                $("#editModal input[name='actividad_id']").val($(this).data('id'));
                $("#editModal input[name='codigo_caeb']").val($(this).data('codigo_caeb'));
                $("#editModal input[name='descripcion']").val($(this).data('descripcion'));
                $("#editModal select[name='tipo_actividad']").val($(this).data('tipo_actividad'));
                $('.selectpicker').selectpicker('refresh');
            });
        });

        $('#activity-table').DataTable({
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
                        actividad_id.length = 0;
                        $(':checkbox:checked').each(function(i) {
                            if (i) {
                                actividad_id[i - 1] = $(this).closest('tr').data('id');
                            }
                        });
                        if (actividad_id.length && confirm("Are you sure want to delete?")) {
                            $.ajax({
                                type: 'POST',
                                url: 'activities/deletebyselection',
                                data: {
                                    departmentIdArray: actividad_id
                                },
                                success: function(data) {
                                    alert(data);
                                }
                            });
                            dt.rows({
                                page: 'current',
                                selected: true
                            }).remove().draw(false);
                        } else if (!actividad_id.length)
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
