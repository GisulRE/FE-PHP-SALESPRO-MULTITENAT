@extends('layout.main')
@section('content')
    @include('layout.partials.session-flash')

    <section>
        <div class="container-fluid">
            <a href="{{ route('url-ws.create') }}" class="btn btn-info">
                <i class="dripicons-plus"></i>
                Agregar URL
            </a>
        </div>
        <div class="table-responsive">
            <table id="item-table" class="table">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
                        <th>Ambiente</th>
                        <th>Nombre Servicio</th>
                        <th>URL</th>
                        <th>Tipo</th>
                        <th>Modalidad</th>
                        <th class="not-exported">{{ trans('file.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $key => $item)
                        <tr data-id="{{ $item->id }}">
                            <td>{{ $key }}</td>
                            <td>{{ $item->getAmbiente() }}</td>
                            <td>{{ $item->nombre_servicio }}</td>
                            <td>{{ $item->ruta_url }}</td>
                            <td>{{ $item->getTipoUrl() }}</td>
                            <td>{{ $item->getModalidad() }}</td>

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
                                            <a type="button" class="btn btn-link"
                                                href="{{ route('url-ws.edit', $item->id) }}">
                                                <i class="dripicons-document-edit"></i>
                                                {{ trans('file.edit') }}
                                            </a>
                                        </li>

                                        <li class="divider"></li>

                                        {{ Form::open(['route' => ['url-ws.destroy', $item->id], 'method' => 'DELETE']) }}
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



    <script type="text/javascript">
        $("ul#siat").siblings('a').attr('aria-expanded', 'true');
        $("ul#siat").addClass("show");
        $("ul#siat #siat-menu-url").addClass("active");

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

        $('#item-table').DataTable({
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
