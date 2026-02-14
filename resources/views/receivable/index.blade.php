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
        <div class="container-fluid">
        </div>
        {!! Form::open(['route' => 'receivable.filter', 'method' => 'post']) !!}
        <div class="col-md-8 offset-md-2 mt-4">
            <div class="form-group row">
                <label class="d-tc mt-2"><strong>{{ trans('file.Choose Your Date') }}</strong> &nbsp;</label>
                <div class="d-tc">
                    <div class="input-group">
                        <input id="start_date" name="start_date" class="form-control" placeholder="DD/MM/YYYY"
                            type="date" value="{{ $start_date }}" onchange="filterdate()" required>
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <i class="dripicons-calendar tx-10 lh-0 op-4"></i>
                            </div>
                        </div>
                        <label class="d-tc mt-2" style="margin-left: 5px"><strong> A </strong> &nbsp;</label>
                        <input id="end_date" name="end_date" class="form-control" placeholder="DD/MM/YYYY" type="date"
                            value="{{ $end_date }}" onchange="filterdate()" required>
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <i class="dripicons-calendar tx-10 lh-0 op-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <button class="btn btn-primary" type="submit">{{ trans('file.submit') }}</button>
                </div>
            </div>
        </div>
        {!! Form::close() !!}

        <div class="col-md-10 offset-md-2 mt-4">
            <div class="form-group row">
                <label class="d-tc mt-3"><strong>Elija Metodo de Pago : </strong> &nbsp;</label>
                <div class="d-tc">
                    <input type="hidden" name="paymentmethod_id_hidden" value="{{ $payment_id }}" />
                    <select id="paymentmethod_id" name="paymentmethod_id" class="selectpicker form-control"
                        data-live-search="true" data-live-search-style="begins">
                        @foreach ($lims_methodpay_list as $payment)
                            <option value="{{ $payment->id }}">{{ $payment->name }}</option>
                        @endforeach
                    </select>
                </div>
                <label class="d-tc mt-3"><strong>Total a Pagar : </strong> &nbsp;</label>
                <div class="d-tc">
                    <input type="number" name="total_pay" class="form-control" value="0" readonly />
                </div>
                <div class="form-group">
                    <button id="btn_pro" class="btn btn-primary" type="" onclick="processpay()">Procesar</button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <div id="info-alert" class="alert-info alert-dismissible fade show" role="alert">
                <strong>Guia: </strong> si quiere editar un monto a pagar de una venta, haga click sobre el monto en la
                columna <strong> cobrado </strong>

            </div>
            <table id="sale-table" class="table sale-list" style="width: 100%">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
                        <th>{{ trans('file.Date') }}</th>
                        <th>{{ trans('file.reference') }}</th>
                        <th>{{ trans('file.Biller') }}</th>
                        <th>{{ trans('file.customer') }}</th>
                        <th>{{ trans('file.Sale Status') }}</th>
                        <th>{{ trans('file.Payment Status') }}</th>
                        <th>{{ trans('file.grand total') }}</th>
                        <th>Cobrado</th>
                        <th>Por Cobrar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lims_sale_all as $key => $sale)
                        <?php
                        $biller = DB::table('billers')
                            ->select('name')
                            ->find($sale->biller_id);
                        $customer = DB::table('customers')
                            ->select('name')
                            ->find($sale->customer_id);
                        if ($biller) {
                            $biller_name = $biller->name;
                        } else {
                            $biller_name = 'Desconocido';
                        }
                        if ($customer) {
                            $customer_name = $customer->name;
                        } else {
                            $customer_name = 'Desconocido';
                        }
                        
                        ?>
                        <tr class="sale-link" data-id="{{ $sale->id }}">
                            <td>{{ $sale->id }}</td>
                            <td>{{ date($general_setting->date_format, strtotime($sale->date_sell)) }}</td>
                            <td>{{ $sale->reference_no }}</td>
                            <td>{{ $biller_name }}</td>
                            <td>{{ $customer_name }}</td>
                            @if ($sale->sale_status == 1)
                                <td>
                                    <div class="badge badge-success">{{ trans('file.Completed') }}</div>
                                </td>
                            @elseif($sale->sale_status == 4)
                                <td>
                                    <div class="badge badge-info">{{ trans('file.Receivable') }}</div>
                                </td>
                            @else
                                <td>
                                    <div class="badge badge-danger">{{ trans('file.Pending') }}</div>
                                </td>
                            @endif
                            @if ($sale->payment_status == 1)
                                <td>
                                    <div class="badge badge-danger">{{ trans('file.Pending') }}</div>
                                </td>
                            @elseif($sale->payment_status == 2)
                                <td>
                                    <div class="badge badge-danger">{{ trans('file.Due') }}</div>
                                </td>
                            @elseif($sale->payment_status == 3)
                                <td>
                                    <div class="badge badge-warning">{{ trans('file.Partial') }}</div>
                                </td>
                            @else
                                <td>
                                    <div class="badge badge-success">{{ trans('file.Paid') }}</div>
                                </td>
                            @endif
                            <td class="total-{{ $sale->id }}">{{ number_format($sale->grand_total, 2) }}</td>
                            <td class="paying-{{ $sale->id }}" onclick="editAmount({{ $sale->id }})">
                                {{ number_format($sale->paid_amount, 2) }}</td>
                            <td class="receivable-{{ $sale->id }}">
                                {{ number_format($sale->grand_total - $sale->paid_amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <script type="text/javascript">
        $("ul#sale").siblings('a').attr('aria-expanded', 'true');
        $("ul#sale").addClass("show");
        $("ul#sale #salerec-list-menu").addClass("active");
        $('select[name=paymentmethod_id]').val($("input[name='paymentmethod_id_hidden']").val());
        $('.selectpicker').selectpicker('refresh');
        $(".buttons-print").css({
            "display": "none"
        });
        $('#info-alert').css({
            "display": "inherit",
            "text-align": "-moz-center"
        });
        var sale_id = [];
        var sale_cobrado = [];
        var sale_porcobrar = [];
        var total = parseFloat(0);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        let $dt = $('#sale-table');
        let dt = $dt.DataTable({
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
                    'targets': [0, 8]
                },
                {
                    'render': function(data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input id="check_' + data +
                                '" type="checkbox" class="dt-checkboxes" onclick="getSelectOnly(this)"><label></label></div>';
                        }

                        return data;
                    },
                    'checkboxes': {
                        'selectRow': true,
                        'selectAllRender': '<div class="checkbox"><input id="checkall" type="checkbox" class="dt-checkboxes" onclick="getSelected()"><label></label></div>'
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
            buttons: [],
            drawCallback: function() {
                var api = this.api();
                //datatable_sum(api, false);
            }
        });

        function getSelectOnly(id) {
            if ($(id).is(":checked")) {
                var sale = $(id).closest('tr').data('id');
                sale_id.push('' + sale);
                console.log("receivable-" + sale);
                var cobrar = parseFloat($(`.receivable-${sale}`).text().replace(",", ""));
                $(`.paying-${sale}`).text(cobrar.toFixed(2));
                $(`.receivable-${sale}`).text("0.00");
                sale_cobrado.push('' + cobrar);
                sale_porcobrar.push('' + 0.00);
                total = total + cobrar;
            } else {
                var sale = $(id).closest('tr').data('id');
                console.log("receivable-" + sale);
                var pagado = parseFloat($(`.paying-${sale}`).text().replace(",", ""));
                $(`.paying-${sale}`).text("0.00");
                $(`.receivable-${sale}`).text(pagado.toFixed(2));
                total = total - pagado;
                var index = sale_id.indexOf('' + sale);
                if (index > -1) {
                    sale_id.splice(index, 1);
                    sale_cobrado.splice(index, 1);
                    sale_porcobrar.splice(index, 1);
                }
            }
            $("input[name='total_pay']").val(total.toFixed(2));
        }

        function getSelected() {
            var i = 0;
            total = 0;
            sale_id = [];
            sale_cobrado = [];
            sale_porcobrar = [];
            var selectedIds = dt.columns().data()[0];
            var selectedCobrado = dt.columns().data()[8];
            var selectedPorCobrar = dt.columns().data()[9];
            if ($('#checkall').prop('checked')) {
                sale_id = selectedIds;
                sale_cobrado = selectedPorCobrar;
                sale_porcobrar = selectedCobrado;
                selectedIds.forEach(function(selectedId) {
                    var cobrar = parseFloat(selectedPorCobrar[i].replace(",", ""));
                    $(`.paying-${selectedId}`).text(cobrar.toFixed(2));
                    $(`.receivable-${selectedId}`).text("0.00");
                    total = total + cobrar;
                    i = i + 1;
                });
            } else {
                selectedIds.forEach(function(selectedId) {
                    var pagado = parseFloat(selectedPorCobrar[i].replace(",", ""));
                    $(`.paying-${selectedId}`).text("0.00");
                    $(`.receivable-${selectedId}`).text(pagado.toFixed(2));
                    total = total - pagado;
                    i = i + 1;
                });
                total = 0;
                sale_id = [];
                sale_cobrado = [];
                sale_porcobrar = [];
            }
            $("input[name='total_pay']").val(total.toFixed(2));

        }

        function processpay() {
            var method = $('select[name="paymentmethod_id"]').val();
            var totales = $("input[name='total_pay']").val();
            console.log(sale_id);
            console.log(sale_cobrado);
            console.log(sale_porcobrar);
            if (sale_id.length) {
                swal({
                        title: "Está Seguro?",
                        text: "Procesar Todas las ventas Seleccionadas?!",
                        icon: "warning",
                        buttons: {
                            cancel: "Cancelar!",
                            process: {
                                text: "Procesar",
                                value: true,
                            },
                        },
                    })
                    .then((process) => {
                        if (process) {
                            $.ajax({
                                type: 'POST',
                                url: 'receivable/payment',
                                data: {
                                    saleIdArray: sale_id,
                                    salePayArray: sale_cobrado,
                                    saleDueArray: sale_porcobrar,
                                    methodpay: method,
                                    totalpay: totales
                                },
                                success: function(data) {
                                    result = JSON.parse(data);
                                    swal({
                                            title: "Pagado con éxito!",
                                            text: "Mensaje : " + result.message +
                                                " - Total Procesados : " + result.totalprocess,
                                            icon: "success",
                                            buttons: {
                                                cancel: "Cerrar!",
                                                printer: {
                                                    text: "Imprimir",
                                                    value: true,
                                                },
                                            },
                                        })
                                        .then((printer) => {
                                            if (printer) {
                                                var win = window.open('receivable/report/' + result
                                                    .report_id, '_blank');
                                                win.focus();
                                                location.reload(true);
                                            } else {
                                                location.reload(true);
                                            }
                                        });
                                }
                            });
                        } else {
                            swal("Intente nuevamente seleccionando un registro!");
                        }
                    });

                //dt.rows({ page: 'current', selected: true }).remove().draw(false);
            } else if (!sale_id.length)
                swal('Ninguna venta Seleccionado!', "Seleccione uno o mas para esta operacion");

        }

        function print() {
            window.open('receivable/report/9', '_blank', 'location=yes,height=950,width=920,scrollbars=yes,status=yes');
            //var win = window.open('receivable/report/9', '_blank');
            //win.focus();
        }

        function editAmount(id) {
            var oldamount = parseFloat($(`.paying-${id}`).text().replace(",", ""));
            var dueamount = parseFloat($(`.receivable-${id}`).text().replace(",", ""));
            var totalamount = parseFloat($(`.total-${id}`).text().replace(",", ""));
            swal({
                    title: 'Editar',
                    text: 'Ingresa monto a pagar:',
                    content: {
                        element: "input",
                        attributes: {
                            defaultValue: 0,
                        }
                    },
                })
                .then((amount) => {
                    if (amount && amount <= totalamount) {
                        var index = sale_id.indexOf('' + id);
                        if (index > -1) {
                            total = total - oldamount;
                            var cobrado = parseFloat(amount);
                            var deuda = parseFloat($(`.receivable-${id}`).text().replace(",", ""));
                            if (cobrado > deuda)
                                deuda = cobrado - deuda;
                            else
                                deuda = deuda - cobrado;

                            $(`.paying-${id}`).text(cobrado.toFixed(2));
                            $(`.receivable-${id}`).text(deuda.toFixed(2));
                            total = total + cobrado;
                            sale_id[index] = '' + id;
                            sale_cobrado[index] = '' + cobrado;
                            sale_porcobrar[index] = '' + deuda;
                        } else {
                            var cobrado = parseFloat(amount);
                            var deuda = parseFloat($(`.receivable-${id}`).text().replace(",", ""));
                            deuda = deuda - cobrado;
                            $(`.paying-${id}`).text(cobrado.toFixed(2));
                            $(`.receivable-${id}`).text(deuda.toFixed(2));
                            total = total + cobrado;

                            sale_id.push('' + id);
                            sale_cobrado.push('' + cobrado);
                            sale_porcobrar.push('' + deuda);
                        }
                        if (!$(`#check_${id}`).is(":checked")) {
                            $(`#check_${id}`).prop("checked", true);
                        }
                        $("input[name='total_pay']").val(total.toFixed(2));
                    } else {
                        swal("Error al actualizar", "Monto Cobrado es mayor a monto Por Cobrar, intente nuevamente!",
                            "error");
                    }
                });
        }
    </script>
@endsection
