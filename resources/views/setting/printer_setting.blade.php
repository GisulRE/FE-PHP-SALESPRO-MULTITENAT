@extends('layout.main') @section('content')
    @if ($errors->has('title'))
        <div class="alert alert-danger alert-dismissible text-center">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{{ $errors->first('title') }}
        </div>
    @endif
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close"
                data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{{ session()->get('message') }}</div>
    @endif
    @if (session()->has('not_permitted'))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close"
                data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
    @endif

    <section>
        <div class="container-fluid">
            <button class="btn btn-info" data-toggle="modal" data-target="#createModal"><i class="dripicons-plus"></i>
                {{ trans('file.Add Printer') }} </button>&nbsp;
        </div>
        <div class="table-responsive">
            <table id="printer-table" class="table">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
                        <th>{{ trans('file.Name Tag') }}</th>
                        <th>{{ trans('file.Host') }}</th>
                        <th>{{ trans('file.Protocole') }}</th>
                        <th>{{ trans('file.Categorie Print') }}</th>
                        <th>{{ trans('file.Status') }}</th>
                        <th class="not-exported">{{ trans('file.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lims_printers_list as $key => $printer)
                        <tr data-id="{{ $printer->id }}">
                            <td>{{ $key }}</td>
                            <td>{{ $printer->name }}</td>
                            <td>{{ $printer->host_address }}</td>
                            @if ($printer->type == 'shared')
                                <td>Modo Compartido</td>
                            @else
                                <td>Modo TCP/IP</td>
                            @endif
                            @if ($printer->category_id == 0)
                                <td>Sin Area Especifica</td>
                            @else
                                <?php
                                $category = App\Category::select('name')->find($printer->category_id);
                                ?>
                                <td>{{ $category->name }}</td>
                            @endif
                            @if ($printer->status)
                                <td>
                                    <div class="badge badge-success">Activo</div>
                                </td>
                            @else
                                <td>
                                    <div class="badge badge-danger">Inactivo</div>
                                </td>
                            @endif
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default btn-sm dropdown-toggle"
                                        data-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false">{{ trans('file.action') }}
                                        <span class="caret"></span>
                                        <span class="sr-only">Toggle Dropdown</span>
                                    </button>
                                    <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default"
                                        user="menu">
                                        <li><button type="button" data-id="{{ $printer->id }}" class="btn btn-link"
                                                onclick="openDialog('{{ $printer->id }}')" data-toggle="modal"
                                                data-target="#editModal"><i class="dripicons-document-edit"></i>
                                                {{ trans('file.edit') }}</button></li>
                                        <li class="divider"></li>
                                        {{ Form::open(['route' => ['printer.destroy', $printer->id], 'method' => 'DELETE']) }}
                                        <li>
                                            <button type="submit" class="btn btn-link"
                                                onclick="return confirm('Esta seguro de eliminar?')"><i
                                                    class="dripicons-trash"></i> {{ trans('file.delete') }}</button>
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

    <div id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                {!! Form::open(['route' => 'printer.store', 'method' => 'post', 'files' => true]) !!}
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Add Printer') }}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <p class="italic">
                        <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small></p>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ trans('file.Name Tag') }} *</label>
                                <input type="text" name="name" class="form-control" placeholder="Nombre Impresora..."
                                    required />
                            </div>
                            <div class="form-group">
                                <label>{{ trans('file.Printer') }} *</label>
                                <input type="text" name="printer" class="form-control"
                                    placeholder="Nombre Identificador en red..." required />
                            </div>
                            <div class="form-group">
                                <label>{{ trans('file.Categorie Print') }} *</label>
                                <select required name="category_id" class="selectpicker form-control"
                                    title="Seleccione Area...">
                                    <option value="0">Sin Area Especifica</option>
                                    @foreach ($lims_categories_list as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <input type="submit" value="{{ trans('file.submit') }}" class="btn btn-primary">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ trans('file.Host') }} *</label>
                                <input type="text" name="host_address" class="form-control"
                                    placeholder="Nombre equipo ó Direccion IP de Equipo..." required />
                            </div>
                            <div class="form-group">
                                <label>{{ trans('file.Protocole') }} *</label>
                                <select id="type" name="type" class="selectpicker form-control"
                                    title="Seleccione Protocolo..." required>
                                    <option value="shared">Compartido</option>
                                    <option value="tcp/ip">TCP/IP</option>
                                </select>
                            </div>

                        </div>
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
                {{ Form::open(['route' => ['printer.update', 1], 'method' => 'PUT', 'files' => true]) }}
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title"> {{ trans('file.Update Brand') }}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <p class="italic">
                        <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small></p>
                    <div class="row">
                        <div class="col-md-6">
                            <input type="hidden" name="printer_id" />
                            <div class="form-group">
                                <label>{{ trans('file.Name Tag') }} *</label>
                                <input type="text" name="name" class="form-control"
                                    placeholder="Nombre Impresora..." required />
                            </div>
                            <div class="form-group">
                                <label>{{ trans('file.Printer') }} *</label>
                                <input type="text" name="printer" class="form-control"
                                    placeholder="Nombre Identificador en red..." required />
                            </div>
                            <div class="form-group">
                                <label>{{ trans('file.Categorie Print') }} *</label>
                                <select required name="category_id" class="selectpicker form-control"
                                    title="Seleccione Area...">
                                    <option value="0">Sin Area Especifica</option>
                                    @foreach ($lims_categories_list as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <input type="submit" value="{{ trans('file.submit') }}" class="btn btn-primary">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ trans('file.Host') }} *</label>
                                <input type="text" name="host_address" class="form-control"
                                    placeholder="Nombre equipo ó Direccion IP de Equipo..." required />
                            </div>
                            <div class="form-group">
                                <label>{{ trans('file.Protocole') }} *</label>
                                <select id="type" name="type" class="selectpicker form-control"
                                    title="Seleccione Protocolo..." required>
                                    <option value="shared">Compartido</option>
                                    <option value="tcp/ip">TCP/IP</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>{{ trans('file.Status') }}</label>
                                <select id="type" name="status" class="selectpicker form-control"
                                    title="Seleccione Estado Impresora...">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>


    <script type="text/javascript">
        $("ul#setting").siblings('a').attr('aria-expanded', 'true');
        $("ul#setting").addClass("show");
        $("ul#setting #printer-menu").addClass("active");

        var brand_id = [];

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#select_all").on("change", function() {
            if ($(this).is(':checked')) {
                $("tbody input[type='checkbox']").prop('checked', true);
            } else {
                $("tbody input[type='checkbox']").prop('checked', false);
            }
        });


        function openDialog(idp) {
            var url = "printer/"
            var id = idp;
            url = url.concat(id).concat("/edit");

            $.get(url, function(data) {
                $("input[name='name']").val(data['name']);
                $("input[name='printer_id']").val(data['id']);
                $("input[name='host_address']").val(data['host_address']);
                $("input[name='printer']").val(data['printer']);
                $('select[name="category_id"]').val(data['category_id']);
                $('select[name="type"]').val(data['type']);
                $('select[name="status"]').val(data['status']);
                $('.selectpicker').selectpicker('refresh');
            });
        }

        $('#printer-table').DataTable({
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
                    'targets': [0, 1, 2, 6]
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
                    exportOptions: {
                        columns: ':visible:Not(.not-exported-quotation)',
                        rows: ':visible'
                    },
                    action: function(e, dt, button, config) {
                        datatable_sum_return(dt, true);
                        $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
                        datatable_sum_return(dt, false);
                    },
                },
                {
                    extend: 'csv',
                    exportOptions: {
                        columns: ':visible:Not(.not-exported)',
                        rows: ':visible'
                    },
                    action: function(e, dt, button, config) {
                        datatable_sum_return(dt, true);
                        $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
                        datatable_sum_return(dt, false);
                    },
                },
                {
                    extend: 'print',
                    text: '{{ trans('file.Print') }}',
                    exportOptions: {
                        columns: ':visible:Not(.not-exported)',
                        rows: ':visible',
                        stripHtml: false
                    },
                },
                {
                    text: '{{ trans('file.delete') }}',
                    className: 'buttons-delete',
                    action: function(e, dt, node, config) {
                        printer_id.length = 0;
                        $(':checkbox:checked').each(function(i) {
                            if (i) {
                                printer_id[i - 1] = $(this).closest('tr').data('id');
                            }
                        });
                        if (printer_id.length && confirm("Esta seguro de querer eliminar?")) {
                            $.ajax({
                                type: 'POST',
                                url: 'printer/deletebyselection',
                                data: {
                                    printerIdArray: printer_id
                                },
                                success: function(data) {
                                    alert(data);
                                }
                            });
                            dt.rows({
                                page: 'current',
                                selected: true
                            }).remove().draw(false);
                        } else if (!printer_id.length)
                            alert('Impresora no seleccionado!');
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
