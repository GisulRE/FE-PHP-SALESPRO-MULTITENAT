@extends('layout.main') @section('content')
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close"
                data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('message') !!}
        </div>
    @endif
    @if (session()->has('not_permitted'))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert"
                aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}
        </div>
    @endif

    <section>
        <div class="container-fluid row">
            <div class="col-md-10">
                @if (in_array('quotes-add', $all_permission))
                <a href="{{ route('quotations.create') }}" class="btn btn-info"><i class="dripicons-plus"></i>
                    {{ trans('file.Add Quotation') }}</a>
            @endif

            </div>
            <div class="col-md-2">
                <button class="btn btn-info" type="link" onclick="setting_turno()"><i
                        class="dripicons-gear"></i> Ajuste {{ trans('file.Quotation') }}</button>
            </div>
        </div>
        <div class="table-responsive">
            <table id="quotation-table" class="table quotation-list" style="width: 100%">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
                        <th>{{ trans('file.Date') }}</th>
                        <th>{{ trans('file.reference_quotation') }}</th>
                        <th>{{ trans('file.Biller') }}</th>
                        <th>{{ trans('file.customer') }}</th>
                        <th>{{ trans('file.Supplier') }}</th>
                        <th>{{ trans('file.Quotation Status') }}</th>
                        <th>{{ trans('file.grand total') }}</th>
                        <th class="not-exported">{{ trans('file.action') }}</th>
                    </tr>
                </thead>
                <tfoot class="tfoot active">
                    <th></th>
                    <th>{{ trans('file.Total') }}</th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tfoot>
            </table>
        </div>
    </section>

    <div id="quotation-details" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="container mt-3 pb-2 border-bottom">
                    <div class="row">
                        <div class="col-md-3">
                            <button id="print-btn" type="button" class="btn btn-default btn-sm d-print-none"><i
                                    class="dripicons-print"></i> {{ trans('file.Print') }}</button>
                            {{ Form::open(['route' => 'quotation.sendmail', 'method' => 'post', 'class' => 'sendmail-form']) }}
                            <input type="hidden" name="quotation_id">
                            <button class="btn btn-default btn-sm d-print-none"><i class="dripicons-mail"></i>
                                {{ trans('file.Email') }}</button>
                            {{ Form::close() }}
                        </div>
                        <div class="col-md-6">
                            <h3 id="exampleModalLabel" class="modal-title text-center container-fluid">
                                {{ $general_setting->site_title }}</h3>
                        </div>
                        <div class="col-md-3">
                            <button type="button" id="close-btn" data-dismiss="modal" aria-label="Close"
                                class="close d-print-none"><span aria-hidden="true"><i
                                        class="dripicons-cross"></i></span></button>
                        </div>
                        <div class="col-md-12 text-center">
                            <i style="font-size: 15px;">{{ trans('file.Quotation Details') }}</i>
                        </div>
                    </div>
                </div>
                <div id="quotation-content" class="modal-body">
                </div>
                <br>
                <table class="table table-bordered product-quotation-list">
                    <thead>
                        <th>#</th>
                        <th>{{ trans('file.product') }}</th>
                        <th>Qty</th>
                        <th>{{ trans('file.Unit Price') }}</th>
                        <th>{{ trans('file.Tax') }}</th>
                        <th>{{ trans('file.Discount') }}</th>
                        <th>{{ trans('file.Subtotal') }}</th>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <div id="quotation-footer" class="modal-body"></div>
            </div>
        </div>
    </div>
    <div id="setting-pro-modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true"
        class="modal fade bd-example-modal-sm">
        <div role="document" class="modal-dialog modal-dialog-centered modal-sm" style="max-width: 400px;">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">Ajuste Proforma</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <div class="col-md-12 form-group">
                        <label class="d-tc mt-2"><strong>Formato de Impresion *</strong> &nbsp;</label>
                        <select id="quotationprinter" name="quotation_printer" class="selectpicker form-control" title="Seleccione formato..." required>
                        <option value="1">Media Carta (PDF)</option>
                        <option value="2">Impresion Ticket (MTP-3 80mm)</option>
                        <option value="3">Impresion Ticket (MTP-3 80mm - Nativo)</option>
                        </select>
                    </div>
                    <div class="col-md-12 form-group">
                        <button id="btn_updatepos" class="btn btn-success"><i
                                class="dripicons-clockwise"></i>Actualizar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $("ul#quotation").siblings('a').attr('aria-expanded', 'true');
        $("ul#quotation").addClass("show");
        $("ul#quotation #quotation-list-menu").addClass("active");
        var all_permission = <?php echo json_encode($all_permission); ?>;
        var quotation_id = [];

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function confirmDelete() {
            if (confirm("¿Esta seguro de eliminar?")) {
                return true;
            }
            return false;
        }
        $(document).on("click", "tr.quotation-link td:not(:first-child, :last-child)", function() {
            var quotation = $(this).parent().data('quotation');
            quotationDetails(quotation);
        });

        $(document).on("click", ".view", function() {
            var quotation = $(this).parent().parent().parent().parent().parent().data('quotation');
            quotationDetails(quotation);
        });

        $("#print-btn").on("click", function() {
            var id = $('input[name="quotation_id"]').val();
            window.open('quotations/gen_invoice/' + id, '_blank',
                'location=yes,height=950,width=920,scrollbars=yes,status=yes');

        });

        filterdate();

        function filterdate() {
            //$("#quotation-table").empty();
            $('#quotation-table').DataTable({
                destroy: true,
                "processing": true,
                "serverSide": true,
                "ajax": {
                    url: "quotations/list-data",
                    data: {
                        all_permission: all_permission
                    },
                    dataType: "json",
                    type: "post"
                },
                "createdRow": function(row, data, dataIndex) {
                    $(row).addClass('quotation-link');
                    $(row).attr('data-quotation', data['data_view']);
                },
                "columns": [{
                        "data": "key"
                    },
                    {
                        "data": "date"
                    },
                    {
                        "data": "reference_no"
                    },
                    {
                        "data": "biller.name"
                    },
                    {
                        "data": "customer.name"
                    },
                    {
                        "data": "supplier_name"
                    },
                    {
                        "data": "quotation_status"
                    },
                    {
                        "data": "grand_total"
                    },
                    {
                        "data": "options"
                    },
                ],
                'language': {

                    'lengthMenu': '_MENU_ {{ trans('file.records per page') }}',
                    "info": '<small>{{ trans('file.Showing') }} _START_ - _END_ (_TOTAL_)</small>',
                    "search": '{{ trans('file.Search') }}',
                    'paginate': {
                        'previous': '<i class="dripicons-chevron-left"></i>',
                        'next': '<i class="dripicons-chevron-right"></i>'
                    }
                },
                order: [
                    ['1', 'desc']
                ],
                'columnDefs': [{
                        "orderable": false,
                        'targets': [0, 8]
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
                        action: function(e, dt, button, config) {
                            datatable_sum(dt, true);
                            $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
                            datatable_sum(dt, false);
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
                        action: function(e, dt, button, config) {
                            datatable_sum(dt, true);
                            $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
                            datatable_sum(dt, false);
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
                        action: function(e, dt, button, config) {
                            datatable_sum(dt, true);
                            $.fn.dataTable.ext.buttons.print.action.call(this, e, dt, button, config);
                            datatable_sum(dt, false);
                        },
                        footer: true
                    },
                    {
                        text: '{{ trans('file.delete') }}',
                        className: 'buttons-delete',
                        action: function(e, dt, node, config) {
                            quotation_id.length = 0;
                            $(':checkbox:checked').each(function(i) {
                                if (i) {
                                    var quotation = $(this).closest('tr').data('quotation');
                                    quotation_id[i - 1] = quotation[13];
                                }
                            });
                            if (quotation_id.length && confirm("Are you sure want to delete?")) {
                                $.ajax({
                                    type: 'POST',
                                    url: 'quotations/deletebyselection',
                                    data: {
                                        quotationIdArray: quotation_id
                                    },
                                    success: function(data) {
                                        swal("Mensaje", "" + data, "success");
                                    }
                                });
                                dt.rows({
                                    page: 'current',
                                    selected: true
                                }).remove().draw(false);
                            } else if (!quotation_id.length)
                                swal("Mensaje", "No se selecciono nada!", "info");
                        }
                    },
                    {
                        extend: 'colvis',
                        text: '{{ trans('file.Column visibility') }}',
                        columns: ':gt(0)'
                    },
                ],
                drawCallback: function() {
                    var api = this.api();
                    datatable_sum(api, false);
                }
            });
        }

        function datatable_sum(dt_selector, is_calling_first) {
            if (dt_selector.rows('.selected').any() && is_calling_first) {
                var rows = dt_selector.rows('.selected').indexes();

                $(dt_selector.column(7).footer()).html(dt_selector.cells(rows, 7, {
                    page: 'current'
                }).data().sum().toFixed(2));
            } else {
                $(dt_selector.column(7).footer()).html(dt_selector.cells(rows, 7, {
                    page: 'current'
                }).data().sum().toFixed(2));
            }
        }

        if (all_permission.indexOf("quotes-delete") == -1)
            $('.buttons-delete').addClass('d-none');

        function quotationDetails(quotation) {
            $('input[name="quotation_id"]').val(quotation[13]);
            var htmltext = '<strong>{{ trans('file.Date') }}: </strong>' + quotation[0] +
                '<br><strong>{{ trans('file.reference') }}: </strong>' + quotation[1] +
                '<br><strong>{{ trans('file.Status') }}: </strong>' + quotation[2];
            if (quotation[25] != null) {
                htmltext += '<br><strong>{{ trans('file.date_valid') }}: </strong>' + quotation[25];
            }
            htmltext += '<br><br><div class="row"><div class="col-md-6"><strong>{{ trans('file.From') }}:</strong><br>' +
                quotation[3] + '<br>' + quotation[4] + '<br>' + quotation[5] + '<br>' + quotation[6] + '<br>' + quotation[
                    7] + '<br>' + quotation[8] +
                '</div><div class="col-md-6"><div class="float-right"><strong>{{ trans('file.To') }}:</strong><br>' +
                quotation[9] + '<br>' + quotation[10] + '<br>' + quotation[11] + '<br>' + quotation[12] +
                '</div></div></div>';
            $.get('quotations/product_quotation/' + quotation[13], function(data) {
                $(".product-quotation-list tbody").remove();
                var name_code = data[0];
                var qty = data[1];
                var unit_code = data[2];
                var tax = data[3];
                var tax_rate = data[4];
                var discount = data[5];
                var subtotal = data[6];
                var newBody = $("<tbody>");
                $.each(name_code, function(index) {
                    var newRow = $("<tr>");
                    var cols = '';
                    cols += '<td><strong>' + (index + 1) + '</strong></td>';
                    cols += '<td>' + name_code[index] + '</td>';
                    cols += '<td>' + qty[index] + ' ' + unit_code[index] + '</td>';
                    cols += '<td>' + parseFloat(subtotal[index] / qty[index]).toFixed(2) + '</td>';
                    cols += '<td>' + tax[index] + '(' + tax_rate[index] + '%)' + '</td>';
                    cols += '<td>' + discount[index] + '</td>';
                    cols += '<td>' + subtotal[index] + '</td>';
                    newRow.append(cols);
                    newBody.append(newRow);
                });

                var newRow = $("<tr>");
                cols = '';
                cols += '<td colspan=4><strong>{{ trans('file.Total') }}:</strong></td>';
                cols += '<td>' + quotation[14] + '</td>';
                cols += '<td>' + quotation[15] + '</td>';
                cols += '<td>' + quotation[16] + '</td>';
                newRow.append(cols);
                newBody.append(newRow);

                var newRow = $("<tr>");
                cols = '';
                cols += '<td colspan=6><strong>{{ trans('file.Order Tax') }}:</strong></td>';
                cols += '<td>' + quotation[17] + '(' + quotation[18] + '%)' + '</td>';
                newRow.append(cols);
                newBody.append(newRow);

                var newRow = $("<tr>");
                cols = '';
                cols += '<td colspan=6><strong>{{ trans('file.Order Discount') }}:</strong></td>';
                cols += '<td>' + quotation[19] + '</td>';
                newRow.append(cols);
                newBody.append(newRow);

                var newRow = $("<tr>");
                cols = '';
                cols += '<td colspan=6><strong>{{ trans('file.Shipping Cost') }}:</strong></td>';
                cols += '<td>' + quotation[20] + '</td>';
                newRow.append(cols);
                newBody.append(newRow);

                var newRow = $("<tr>");
                cols = '';
                cols += '<td colspan=6><strong>{{ trans('file.grand total') }}:</strong></td>';
                cols += '<td>' + quotation[21] + '</td>';
                newRow.append(cols);
                newBody.append(newRow);

                $("table.product-quotation-list").append(newBody);
            });
            var htmlfooter = '<p><strong>{{ trans('file.Note') }}:</strong> ' + quotation[22] +
                '</p><strong>{{ trans('file.Created By') }}:</strong><br>' + quotation[23] + '<br>' + quotation[24];
            $('#quotation-content').html(htmltext);
            $('#quotation-footer').html(htmlfooter);
            $('#quotation-details').modal('show');
        }

        function setting_turno() {
            $.get('setting/pos_settingjson', function(data) {
                if (data) {
                    $("#quotationprinter").val(data.quotation_printer);
                    $('.selectpicker').selectpicker('refresh');
                } else {

                    $("select[name='quotation_printer']").val(0);
                }
                $('#setting-pro-modal').modal('show');
            });
        }

        $('#btn_updatepos').on('click', function() {
            var print = $("#quotationprinter").val();
            $.ajax({
                type: 'POST',
                url: 'setting/pos_setting_update',
                data: {
                    quotation_printer: print
                },
                success: function(response) {
                    swal("Mensaje",
                        "Se actualizo el formato de Impresión",
                        "success");
                    $('#setting-pro-modal').modal('hide')
                    $('body').removeClass('modal-open');
                    $('.modal-backdrop').remove();
                },
                error: function(response) {
                    //console.log(response);
                    swal("Error",
                        "Error en servidor o datos, Intente nuevamente ó contacte con soporte",
                        "error");
                },
            });
        });
    </script>
@endsection
