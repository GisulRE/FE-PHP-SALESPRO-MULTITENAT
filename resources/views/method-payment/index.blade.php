@extends('layout.main')
@section('content')
    @include('layout.partials.session-flash')

    <section>
        {{-- Navs siat --}}
        @include('layout.partials.navs-siat')
        {{-- Navs siat --}}

        <div class="container fluid mt-3">
            <button class="btn btn-info" data-toggle="modal" data-target="#createModal"><i
                    class="dripicons-plus"></i>{{ __('file.Add Method Payment') }} </button>

            <div class="table-responsive mt-3">
                <table id="product-table" class="table">
                    <thead>
                        <tr>
                            <th class="not-exported"></th>
                            <th>id </th>
                            <th>Nombre</th>
                            <th>{{ trans('file.Description') }}</th>
                            <th>Código Siat</th>
                            <th>Detalle Siat</th>
                            <th class="not-exported">{{ trans('file.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lista_metodo_pago as $key => $method_payment)
                            <tr data-id="{{ $method_payment->id }}">
                                <td>{{ $key }}</td>
                                <td>{{ $method_payment->id }}</td>
                                <td>{{ $method_payment->name }}</td>
                                <td>{{ $method_payment->description }}</td>
                                <td>{{ $method_payment->codigo_clasificador_siat }}</td>
                                <td>{{ $method_payment->getDescripcionCodigoClasificador() }}</td>
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
                                                <button type="button" data-id="{{ $method_payment->id }}"
                                                    class="btn btn-link" onclick="openDialog('{{ $method_payment->id }}')"
                                                    data-toggle="modal" data-target="#editModal">
                                                    <i class="dripicons-document-edit"></i> {{ trans('file.edit') }}
                                                </button>
                                            </li>
                                            <li class="divider"></li>
                                            {{ Form::open(['route' => ['method_payment.destroy', $method_payment->id], 'method' => 'DELETE']) }}
                                            <li>
                                                <button type="submit" class="btn btn-link"
                                                    onclick="return confirmEstado()">
                                                    <i class="dripicons-swap"></i>
                                                    Cambiar Estado
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
        </div>

    </section>

    <div id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                {!! Form::open(['route' => 'method_payment.store', 'method' => 'post']) !!}
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{ __('file.Add Method Payment') }}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                        <span aria-hidden="true"><i class="dripicons-cross"></i></span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="italic">
                        <small>{{ __('file.The field labels marked with * are required input fields') }}.</small></p>
                    <div class="form-group">
                        <label>Nombre *</label>
                        <input type="text" name="name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Descripción *</label>
                        <input type="text" name="description" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Clasificación Siat *</label>
                        <select name="codigo_clasificador_siat" class="form-control" data-live-search="true"
                            data-live-search-style="begins" title="Seleccionar...">
                            @include('method-payment.partials-method-payment')
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="submit" value="{{ __('file.submit') }}" class="btn btn-primary">
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
    <div id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                {{ Form::open(['route' => ['method_payment.update', 1], 'method' => 'PUT']) }}
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title"> {{ __('file.Update Method Payment') }}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <p class="italic">
                        <small>{{ __('file.The field labels marked with * are required input fields') }}.</small></p>
                    <div class="form-group">
                        <input type="hidden" name="method_payment_id">
                        <label>Nombre *</label>
                        <input type="text" name="name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Descripción *</label>
                        <input type="text" name="description" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Clasificación Siat *</label>
                        <select name="codigo_clasificador_siat" class="selectpicker form-control" data-live-search="true"
                            data-live-search-style="begins" title="Seleccionar...">
                            @include('method-payment.partials-method-payment')
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="submit" value="{{ __('file.submit') }}" class="btn btn-primary">
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $("ul#siat").siblings('a').attr('aria-expanded', 'true');
        $("ul#siat").addClass("show");
        $("ul#siat #siat-menu-panel").addClass("active");

        $("ul#nav-siat #pago").addClass("active");

        function openDialog(idp) {
            var url = "method_payment/"
            var id = idp;
            url = url.concat(id).concat("/edit");

            $.get(url, function(data) {
                $("#editModal input[name='method_payment_id']").val(data['id']);
                $("#editModal input[name='name']").val(data['name']);
                $("#editModal input[name='description']").val(data['description']);
                $("#editModal select[name='codigo_clasificador_siat']").val(data['codigo_clasificador_siat']);
                $('.selectpicker').selectpicker('refresh');
            });
        }

        $('#product-table').DataTable({
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
@endsection
