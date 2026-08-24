@extends('layout.main') @section('content')
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert"
                aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('message') }}</div>
    @endif
    @if (session()->has('not_permitted'))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert"
                aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}
        </div>
    @endif

    <section>
        <div class="container-fluid">
            @if (in_array('transfers-add', $all_permission))
                <a href="{{ route('transfers.create') }}" class="btn btn-info"><i class="dripicons-plus"></i>
                    {{ trans('file.add') }} {{ trans('file.Transfer') }}</a>
                <a href="{{ url('transfers/transfer_by_csv') }}" class="btn btn-primary"><i class="dripicons-copy"></i>
                    {{ trans('file.import') }} {{ trans('file.Transfer') }}</a>
            @endif
        </div>
        <div class="table-responsive">
            <table id="transfer-table" class="table transfer-list">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
                        <th>{{ trans('file.Date') }}</th>
                        <th>{{ trans('file.reference') }} No</th>
                        <th>{{ trans('file.Warehouse') }}({{ trans('file.From') }})</th>
                        <th>{{ trans('file.Warehouse') }}({{ trans('file.To') }})</th>
                        <th class="text-right">{{ trans('file.product') }} {{ trans('file.Cost') }}</th>
                        <th class="text-right">{{ trans('file.product') }} {{ trans('file.Tax') }}</th>
                        <th class="text-right">{{ trans('file.grand total') }}</th>
                        <th>{{ trans('file.Status') }}</th>
                        <th class="not-exported">{{ trans('file.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lims_transfer_all as $key => $transfer)
                        <?php
                            if ($transfer->status == 1) {
                                $status = trans('file.Completed');
                            } elseif ($transfer->status == 2) {
                                $status = trans('file.Pending');
                            } elseif ($transfer->status == 3) {
                                $status = trans('file.Sent');
                            } elseif ($transfer->status == 4) {
                                $status = trans('file.Rejected');
                            } else {
                                $status = 'N/A';
                            }

                            $transferData = [
                                date($general_setting->date_format, strtotime($transfer->created_at->toDateString())),
                                (string)$transfer->reference_no,
                                (string)$status,
                                (string)$transfer->id,
                                (string)($transfer->fromWarehouse->name ?? ''),
                                (string)($transfer->fromWarehouse->phone ?? ''),
                                (string)($transfer->fromWarehouse->address ?? ''),
                                (string)($transfer->toWarehouse->name ?? ''),
                                (string)($transfer->toWarehouse->phone ?? ''),
                                (string)($transfer->toWarehouse->address ?? ''),
                                (string)$transfer->total_tax,
                                (string)$transfer->total_cost,
                                (string)$transfer->shipping_cost,
                                (string)$transfer->grand_total,
                                (string)($transfer->note ?? ''),
                                (string)($transfer->user->name ?? ''),
                                (string)($transfer->user->email ?? '')
                            ];
                        ?>
                        <tr class="transfer-link" data-transfer="{{ json_encode($transferData) }}">
                            <td>{{ $key }}</td>
                            <td>{{ date($general_setting->date_format, strtotime($transfer->created_at->toDateString())) . ' ' . $transfer->created_at->toTimeString() }}</td>
                            <td>{{ $transfer->reference_no }}</td>
                            <td>{{ $transfer->fromWarehouse->name ?? 'N/A' }}</td>
                            <td>{{ $transfer->toWarehouse->name ?? 'N/A' }}</td>
                            <td class="total-cost text-right">{{ number_format((float)$transfer->total_cost, 2, '.', ',') }}</td>
                            <td class="total-tax text-right">{{ number_format((float)$transfer->total_tax, 2, '.', ',') }}</td>
                            <td class="grand-total text-right">{{ number_format((float)$transfer->grand_total, 2, '.', ',') }}</td>
                            @if ($transfer->status == 1)
                                <td>
                                    <div class="badge badge-success">{{ $status }}</div>
                                </td>
                            @elseif($transfer->status == 4)
                                <td>
                                    <div class="badge badge-danger">{{ $status }}</div>
                                </td>
                            @else
                                <td>
                                    <div class="badge badge-warning text-white">{{ $status }}</div>
                                </td>
                            @endif
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">{{ trans('file.action') }}<span
                                            class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                                    </button>
                                    <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                                        <li>
                                            <button type="button" class="btn btn-link view"><i class="fa fa-eye"></i>
                                                {{ trans('file.View') }}</button>
                                        </li>
                                        <li>
                                            <a href="{{ route('transfer-logs.show', $transfer->id) }}" class="btn btn-link">
                                                <i class="fa fa-list"></i> {{ trans('file.log') }}
                                            </a>
                                        </li>

                                        @if (in_array('transfers-edit', $all_permission))
                                            @if ($transfer->status == 1 || $transfer->status == 2)
                                                <li>
                                                    <a href="{{ route('transfers.edit', $transfer->id) }}" class="btn btn-link"><i
                                                            class="dripicons-document-edit"></i>
                                                        {{ trans('file.edit') }}</a>
                                                </li>
                                            @endif
                                        @endif
                                        <li class="divider"></li>
                                        @if (in_array('transfers-delete', $all_permission))
                                            {{ Form::open(['route' => ['transfers.destroy', $transfer->id], 'method' => 'DELETE']) }}
                                            <li>
                                                <button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i
                                                        class="dripicons-trash"></i>
                                                    {{ trans('file.delete') }}</button>
                                            </li>
                                            {{ Form::close() }}
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="tfoot active">
                    <th></th>
                    <th>{{ trans('file.Total') }}</th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th class="text-right"></th>
                    <th class="text-right"></th>
                    <th class="text-right"></th>
                    <th></th>
                    <th></th>
                </tfoot>
            </table>
        </div>
    </section>

    <div id="transfer-details" tabindex="-1" role="dialog" aria-labelledby="transferModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-bottom py-2">
                    <h5 id="transferModalLabel" class="modal-title font-weight-bold">
                        <i class="dripicons-swap"></i> {{ trans('file.Transfer Details') }}
                    </h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body p-3">
                    <div id="transfer-content"></div>
                    <div class="table-responsive my-3">
                        <table class="table table-bordered table-striped product-transfer-list mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 40px;">#</th>
                                    <th class="text-left">{{ trans('file.product') }}</th>
                                    <th class="text-center" style="width: 100px;">Qty</th>
                                    <th class="text-right" style="width: 130px;">{{ trans('file.Unit Cost') }}</th>
                                    <th class="text-right" style="width: 130px;">{{ trans('file.Tax') }}</th>
                                    <th class="text-right" style="width: 140px;">{{ trans('file.Subtotal') }}</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div id="transfer-footer"></div>
                </div>
                <div class="modal-footer border-top py-2 d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        <i class="fa fa-times"></i> {{ trans('file.Close') ?? 'Cerrar' }}
                    </button>
                    <button id="print-btn" type="button" class="btn btn-primary btn-sm">
                        <i class="dripicons-print"></i> {{ trans('file.Print') }} Comprobante
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $("ul#transfer").siblings('a').attr('aria-expanded', 'true');
        $("ul#transfer").addClass("show");
        $("ul#transfer #transfer-list-menu").addClass("active");

        var all_permission = <?php echo json_encode($all_permission); ?>;
        var transfer_id = [];
        var currentTransferData = null;

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

        function formatCurrency(val) {
            var num = parseFloat(val) || 0;
            return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function cleanAddress(addr) {
            if (!addr) return '';
            return addr.replace(/Oferta Valida por:.*$/i, '').trim();
        }

        // Delegación de eventos para apertura de modal
        $(document).off('click', 'tr.transfer-link td:not(:first-child, :last-child)').on('click', 'tr.transfer-link td:not(:first-child, :last-child)', function () {
            var $tr = $(this).closest('tr');
            var rawTransfer = $tr.attr('data-transfer') || $tr.data('transfer');
            var transfer = typeof rawTransfer === 'string' ? JSON.parse(rawTransfer) : rawTransfer;
            transferDetails(transfer);
        });

        $(document).off('click', '.view').on('click', '.view', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var $tr = $(this).closest('tr');
            var rawTransfer = $tr.attr('data-transfer') || $tr.data('transfer');
            var transfer = typeof rawTransfer === 'string' ? JSON.parse(rawTransfer) : rawTransfer;
            transferDetails(transfer);
        });

        $(document).off('click', '#print-btn').on('click', '#print-btn', function () {
            printTransferVoucher();
        });

        $('#transfer-table').DataTable({
            destroy: true,
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
                'targets': [0, 9]
            },
            {
                'render': function (data, type, row, meta) {
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
                action: function (e, dt, button, config) {
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
                action: function (e, dt, button, config) {
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
                action: function (e, dt, button, config) {
                    datatable_sum(dt, true);
                    $.fn.dataTable.ext.buttons.print.action.call(this, e, dt, button, config);
                    datatable_sum(dt, false);
                },
                footer: true
            },
            {
                text: '{{ trans('file.delete') }}',
                className: 'buttons-delete',
                action: function (e, dt, node, config) {
                    transfer_id.length = 0;
                    $(':checkbox:checked').each(function (i) {
                        if (i) {
                            var $tr = $(this).closest('tr');
                            var rawTransfer = $tr.attr('data-transfer') || $tr.data('transfer');
                            var transfer = typeof rawTransfer === 'string' ? JSON.parse(rawTransfer) : rawTransfer;
                            transfer_id[i - 1] = transfer[3];
                        }
                    });
                    if (transfer_id.length && confirm("Are you sure want to delete?")) {
                        $.ajax({
                            type: 'POST',
                            url: '{{ url("transfers/deletebyselection") }}',
                            data: {
                                transferIdArray: transfer_id
                            },
                            success: function (data) {
                                alert(data);
                            }
                        });
                        dt.rows({
                            page: 'current',
                            selected: true
                        }).remove().draw(false);
                    } else if (!transfer_id.length)
                        alert('Nothing is selected!');
                }
            },
            {
                extend: 'colvis',
                text: '{{ trans('file.Column visibility') }}',
                columns: ':gt(0)'
            },
            ],
            drawCallback: function () {
                var api = this.api();
                datatable_sum(api, false);
            }
        });

        function datatable_sum(dt_selector, is_calling_first) {
            if (dt_selector.rows('.selected').any() && is_calling_first) {
                var rows = dt_selector.rows('.selected').indexes();

                $(dt_selector.column(5).footer()).html(formatCurrency(dt_selector.cells(rows, 5, { page: 'current' }).data().sum()));
                $(dt_selector.column(6).footer()).html(formatCurrency(dt_selector.cells(rows, 6, { page: 'current' }).data().sum()));
                $(dt_selector.column(7).footer()).html(formatCurrency(dt_selector.cells(rows, 7, { page: 'current' }).data().sum()));
            } else {
                $(dt_selector.column(5).footer()).html(formatCurrency(dt_selector.cells(rows, 5, { page: 'current' }).data().sum()));
                $(dt_selector.column(6).footer()).html(formatCurrency(dt_selector.cells(rows, 6, { page: 'current' }).data().sum()));
                $(dt_selector.column(7).footer()).html(formatCurrency(dt_selector.cells(rows, 7, { page: 'current' }).data().sum()));
            }
        }

        function transferDetails(transfer) {
            if (!transfer) return;

            var fromName = transfer[4] || 'N/A';
            var fromPhone = transfer[5] ? 'Tel: ' + transfer[5] : '';
            var fromAddr = cleanAddress(transfer[6]);

            var toName = transfer[7] || 'N/A';
            var toPhone = transfer[8] ? 'Tel: ' + transfer[8] : '';
            var toAddr = cleanAddress(transfer[9]);

            var statusBadge = '';
            if (transfer[2] === 'Completed' || transfer[2] === 'Completado') {
                statusBadge = '<span class="badge badge-success px-2 py-1">' + transfer[2] + '</span>';
            } else if (transfer[2] === 'Rejected' || transfer[2] === 'Rechazado') {
                statusBadge = '<span class="badge badge-danger px-2 py-1">' + transfer[2] + '</span>';
            } else {
                statusBadge = '<span class="badge badge-warning text-white px-2 py-1">' + transfer[2] + '</span>';
            }

            var htmlHeader = `
                <div class="row mb-2">
                    <div class="col-md-6">
                        <strong>{{ trans('file.Date') }}:</strong> ${transfer[0]}<br>
                        <strong>{{ trans('file.reference') }}:</strong> <span class="badge badge-dark">${transfer[1]}</span>
                    </div>
                    <div class="col-md-6 text-md-right">
                        <strong>{{ trans('file.Status') }}:</strong> ${statusBadge}
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6 mb-2">
                        <div class="p-2 border rounded bg-light">
                            <strong class="text-primary"><i class="dripicons-upload"></i> {{ trans('file.From') }} (Origen):</strong><br>
                            <span class="font-weight-bold">${fromName}</span><br>
                            <small>${fromPhone}</small><br>
                            <small class="text-muted">${fromAddr}</small>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="p-2 border rounded bg-light">
                            <strong class="text-success"><i class="dripicons-download"></i> {{ trans('file.To') }} (Destino):</strong><br>
                            <span class="font-weight-bold">${toName}</span><br>
                            <small>${toPhone}</small><br>
                            <small class="text-muted">${toAddr}</small>
                        </div>
                    </div>
                </div>
            `;

            $('#transfer-content').html(htmlHeader);
            $('.product-transfer-list tbody').html('<tr><td colspan="6" class="text-center py-3 text-muted"><i class="fa fa-spinner fa-spin"></i> Cargando productos...</td></tr>');

            $.get('{{ url("transfers/product_transfer") }}/' + transfer[3], function (data) {
                currentTransferData = {
                    transfer: transfer,
                    items: data
                };

                var name_code = data[0] || [];
                var qty = data[1] || [];
                var unit_code = data[2] || [];
                var tax = data[3] || [];
                var tax_rate = data[4] || [];
                var subtotal = data[5] || [];

                var rowsHtml = '';
                for (var i = 0; i < name_code.length; i++) {
                    var itemQty = parseFloat(qty[i]) || 0;
                    var itemSub = parseFloat(subtotal[i]) || 0;
                    var itemUnitCost = itemQty > 0 ? (itemSub / itemQty) : 0;
                    var itemTax = parseFloat(tax[i]) || 0;
                    var itemTaxRate = parseFloat(tax_rate[i]) || 0;

                    rowsHtml += '<tr>';
                    rowsHtml += '<td class="text-center font-weight-bold">' + (i + 1) + '</td>';
                    rowsHtml += '<td class="text-left">' + name_code[i] + '</td>';
                    rowsHtml += '<td class="text-center font-weight-bold">' + itemQty + ' ' + (unit_code[i] || '') + '</td>';
                    rowsHtml += '<td class="text-right">' + formatCurrency(itemUnitCost) + '</td>';
                    rowsHtml += '<td class="text-right">' + formatCurrency(itemTax) + ' <small class="text-muted">(' + itemTaxRate + '%)</small></td>';
                    rowsHtml += '<td class="text-right font-weight-bold">' + formatCurrency(itemSub) + '</td>';
                    rowsHtml += '</tr>';
                }

                // Fila Total Costo
                rowsHtml += '<tr class="font-weight-bold bg-light">';
                rowsHtml += '<td colspan="4" class="text-right">{{ trans("file.Total") }} / Costo:</td>';
                rowsHtml += '<td class="text-right">' + formatCurrency(transfer[10]) + '</td>';
                rowsHtml += '<td class="text-right">' + formatCurrency(transfer[11]) + '</td>';
                rowsHtml += '</tr>';

                // Fila Shipping
                if (parseFloat(transfer[12]) > 0) {
                    rowsHtml += '<tr class="bg-light">';
                    rowsHtml += '<td colspan="5" class="text-right font-weight-bold">{{ trans("file.Shipping Cost") }}:</td>';
                    rowsHtml += '<td class="text-right">' + formatCurrency(transfer[12]) + '</td>';
                    rowsHtml += '</tr>';
                }

                // Fila Gran Total
                rowsHtml += '<tr class="font-weight-bold table-active" style="font-size: 1.05rem;">';
                rowsHtml += '<td colspan="5" class="text-right text-uppercase">{{ trans("file.grand total") }}:</td>';
                rowsHtml += '<td class="text-right text-primary">' + formatCurrency(transfer[13]) + '</td>';
                rowsHtml += '</tr>';

                $('.product-transfer-list tbody').html(rowsHtml);
            });

            var noteText = transfer[14] ? transfer[14] : '<span class="text-muted italic">Sin observaciones</span>';
            var userText = transfer[15] ? (transfer[15] + (transfer[16] ? ' &lt;' + transfer[16] + '&gt;' : '')) : 'N/A';

            var htmlFooter = `
                <div class="row mt-2 pt-2 border-top">
                    <div class="col-md-7">
                        <strong>{{ trans('file.Note') }}:</strong> ${noteText}
                    </div>
                    <div class="col-md-5 text-md-right">
                        <strong>{{ trans('file.Created By') }}:</strong> ${userText}
                    </div>
                </div>
            `;

            $('#transfer-footer').html(htmlFooter);
            $('#transfer-details').modal('show');
        }

        function printTransferVoucher() {
            if (!currentTransferData || !currentTransferData.transfer) {
                alert('Por favor abra primero el detalle del traspaso');
                return;
            }

            var transfer = currentTransferData.transfer;
            var data = currentTransferData.items || [[], [], [], [], [], []];

            var date = transfer[0] || '';
            var refNo = transfer[1] || '';
            var status = transfer[2] || '';
            var fromName = transfer[4] || 'N/A';
            var fromPhone = transfer[5] || '';
            var fromAddr = cleanAddress(transfer[6]);
            var toName = transfer[7] || 'N/A';
            var toPhone = transfer[8] || '';
            var toAddr = cleanAddress(transfer[9]);
            var totalTax = parseFloat(transfer[10]) || 0;
            var totalCost = parseFloat(transfer[11]) || 0;
            var shippingCost = parseFloat(transfer[12]) || 0;
            var grandTotal = parseFloat(transfer[13]) || 0;
            var note = transfer[14] || '';
            var userName = transfer[15] || '';
            var siteTitle = '{{ $general_setting->site_title ?? "SISTEMA POS" }}';

            var name_code = data[0] || [];
            var qty = data[1] || [];
            var unit_code = data[2] || [];
            var tax = data[3] || [];
            var subtotal = data[5] || [];

            var itemsRows = '';
            for (var i = 0; i < name_code.length; i++) {
                var itemQty = parseFloat(qty[i]) || 0;
                var itemSub = parseFloat(subtotal[i]) || 0;
                var itemUnitCost = itemQty > 0 ? (itemSub / itemQty) : 0;
                var itemTax = parseFloat(tax[i]) || 0;

                itemsRows += `
                    <tr style="background-color: ${i % 2 === 0 ? '#ffffff' : '#f8fafc'};">
                        <td style="padding: 7px 10px; border-bottom: 1px solid #e2e8f0; text-align: center; font-weight: bold; color: #64748b; font-size: 11px;">${i + 1}</td>
                        <td style="padding: 7px 10px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 12px; color: #1e293b; font-weight: 500;">${name_code[i]}</td>
                        <td style="padding: 7px 10px; border-bottom: 1px solid #e2e8f0; text-align: center; font-size: 12px; font-weight: bold; color: #0f172a;">${itemQty} <span style="font-size: 10px; font-weight: normal; color: #64748b;">${unit_code[i] || ''}</span></td>
                        <td style="padding: 7px 10px; border-bottom: 1px solid #e2e8f0; text-align: right; font-size: 12px; color: #334155;">${formatCurrency(itemUnitCost)}</td>
                        <td style="padding: 7px 10px; border-bottom: 1px solid #e2e8f0; text-align: right; font-size: 12px; color: #334155;">${formatCurrency(itemTax)}</td>
                        <td style="padding: 7px 10px; border-bottom: 1px solid #e2e8f0; text-align: right; font-size: 12px; font-weight: bold; color: #0f172a;">${formatCurrency(itemSub)}</td>
                    </tr>
                `;
            }

            var now = new Date();
            var nowFormatted = ('0' + now.getDate()).slice(-2) + '/' + ('0' + (now.getMonth() + 1)).slice(-2) + '/' + now.getFullYear() + ' ' + ('0' + now.getHours()).slice(-2) + ':' + ('0' + now.getMinutes()).slice(-2);

            var voucherHtml = `
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Traspaso - ${refNo}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            background: #ffffff;
            margin: 0;
            padding: 12mm 15mm;
            font-size: 12px;
            line-height: 1.4;
        }
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }
        .brand-title {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
            text-transform: uppercase;
            margin: 0 0 2px 0;
        }
        .doc-subtitle {
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin: 0;
        }
        .ref-badge {
            background: #0f172a;
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 4px;
        }
        .status-tag {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            background: #dcfce7;
            color: #166534;
        }
        .status-tag.pending {
            background: #fef3c7;
            color: #92400e;
        }
        .meta-strip {
            display: flex;
            justify-content: space-between;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 14px;
            margin-bottom: 14px;
            font-size: 11px;
        }
        .warehouses-grid {
            display: flex;
            gap: 14px;
            margin-bottom: 16px;
        }
        .warehouse-card {
            flex: 1;
            border-radius: 6px;
            padding: 10px 12px;
            background: #ffffff;
        }
        .warehouse-card.origin {
            border: 1px solid #fdba74;
            background: #fffaf5;
        }
        .warehouse-card.destination {
            border: 1px solid #93c5fd;
            background: #f8fafc;
        }
        .card-header-label {
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .card-header-label.origin { color: #c2410c; }
        .card-header-label.destination { color: #1d4ed8; }
        .wh-name {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 2px;
        }
        .wh-detail {
            font-size: 11px;
            color: #475569;
        }
        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        table.items-table th {
            background: #0f172a;
            color: #ffffff;
            padding: 8px 10px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .totals-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
        }
        .notes-box {
            width: 55%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 11px;
        }
        .totals-box {
            width: 40%;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 12px;
            font-size: 11px;
            border-bottom: 1px solid #f1f5f9;
        }
        .totals-row.grand-total {
            background: #0f172a;
            color: #ffffff;
            font-weight: 800;
            font-size: 13px;
            padding: 9px 12px;
            border-bottom: none;
        }
        .signatures-grid {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            margin-bottom: 20px;
        }
        .sign-col {
            width: 30%;
            text-align: center;
        }
        .sign-line {
            border-top: 1px solid #64748b;
            margin-bottom: 6px;
        }
        .sign-title {
            font-size: 10px;
            font-weight: 700;
            color: #334155;
            text-transform: uppercase;
        }
        .sign-sub {
            font-size: 9px;
            color: #64748b;
        }
        .footer-note {
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="header-container">
        <div>
            <h1 class="brand-title">${siteTitle}</h1>
            <p class="doc-subtitle">Comprobante de Traspaso de Inventario</p>
        </div>
        <div style="text-align: right;">
            <div class="ref-badge">Nº ${refNo}</div><br>
            <span class="status-tag ${(status === 'Completed' || status === 'Completado') ? '' : 'pending'}">${status}</span>
        </div>
    </div>

    <div class="meta-strip">
        <div><strong>FECHA DE TRASPASO:</strong> ${date}</div>
        <div><strong>USUARIO RESPONSABLE:</strong> ${userName || 'N/A'}</div>
    </div>

    <div class="warehouses-grid">
        <div class="warehouse-card origin">
            <div class="card-header-label origin">▲ Almacén Origen (Entrega)</div>
            <div class="wh-name">${fromName}</div>
            <div class="wh-detail">${fromPhone ? 'Tel: ' + fromPhone + '<br>' : ''}${fromAddr}</div>
        </div>
        <div class="warehouse-card destination">
            <div class="card-header-label destination">▼ Almacén Destino (Recepción)</div>
            <div class="wh-name">${toName}</div>
            <div class="wh-detail">${toPhone ? 'Tel: ' + toPhone + '<br>' : ''}${toAddr}</div>
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 35px; text-align: center;">#</th>
                <th style="text-align: left;">Descripción del Producto / Código</th>
                <th style="width: 100px; text-align: center;">Cantidad</th>
                <th style="width: 110px; text-align: right;">Costo Unit.</th>
                <th style="width: 90px; text-align: right;">Impuesto</th>
                <th style="width: 120px; text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            ${itemsRows}
        </tbody>
    </table>

    <div class="totals-section">
        <div class="notes-box">
            <strong style="color: #0f172a; text-transform: uppercase; font-size: 10px;">Observaciones / Notas:</strong><br>
            <p style="margin: 4px 0 0 0; color: #475569;">${note || 'Traspaso ejecutado conforme a control interno de existencias.'}</p>
        </div>
        <div class="totals-box">
            <div class="totals-row">
                <span style="color: #64748b;">Total Bruto / Costo:</span>
                <span style="font-weight: 600;">${formatCurrency(totalCost)}</span>
            </div>
            <div class="totals-row">
                <span style="color: #64748b;">Impuestos:</span>
                <span style="font-weight: 600;">${formatCurrency(totalTax)}</span>
            </div>
            ${shippingCost > 0 ? `
            <div class="totals-row">
                <span style="color: #64748b;">Flete / Envío:</span>
                <span style="font-weight: 600;">${formatCurrency(shippingCost)}</span>
            </div>` : ''}
            <div class="totals-row grand-total">
                <span>GRAN TOTAL:</span>
                <span>${formatCurrency(grandTotal)}</span>
            </div>
        </div>
    </div>

    <div class="signatures-grid">
        <div class="sign-col">
            <div class="sign-line"></div>
            <div class="sign-title">Entregado Por</div>
            <div class="sign-sub">Almacén Origen</div>
        </div>
        <div class="sign-col">
            <div class="sign-line"></div>
            <div class="sign-title">Recibido Por</div>
            <div class="sign-sub">Almacén Destino</div>
        </div>
        <div class="sign-col">
            <div class="sign-line"></div>
            <div class="sign-title">Control de Inventario</div>
            <div class="sign-sub">Auditoría / Traspasos</div>
        </div>
    </div>

    <div class="footer-note">
        <span>Impreso: ${nowFormatted} | Sistema POS - ${siteTitle}</span>
        <span>Página 1 de 1</span>
    </div>
</body>
</html>
            `;

            var printWindow = window.open('', '_blank', 'width=900,height=700');
            printWindow.document.open();
            printWindow.document.write(voucherHtml);
            printWindow.document.close();
            printWindow.focus();
            setTimeout(function () {
                printWindow.print();
                printWindow.close();
            }, 350);
        }

        if (all_permission.indexOf("transfers-delete") == -1)
            $('.buttons-delete').addClass('d-none');
    </script>
@endsection