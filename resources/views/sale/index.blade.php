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
    @if (session()->has('info'))
        <script>
            swal("{{ session()->get('info') }}", {
                icon: "info",
            });
        </script>
        {{ session()->forget('info') }}
    @endif

    <section>
        <div class="container-fluid">
            @if (in_array('sales-add', $all_permission))
                <a href="{{ route('sales.create') }}" class="btn btn-info noselect disabled"><i class="dripicons-plus"></i>
                    {{ trans('file.Add Sale') }}</a>&nbsp;
                <a href="{{ url('sales/sale_by_csv') }}" class="btn btn-primary"><i class="dripicons-copy"></i>
                    {{ trans('file.Import Sale') }}</a>
            @endif
        </div>
        <form method="POST">
            <div class="col-md-7 offset-md-3 mt-4">
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
                            <input id="end_date" name="end_date" class="form-control" placeholder="DD/MM/YYYY"
                                type="date" value="{{ $end_date }}" onchange="filterdate()" required>
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="dripicons-calendar tx-10 lh-0 op-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 mt-2">
                        <div class="form-group">
                            <label><strong>Filtrar por Estado de Facturación:</strong></label>
                            <select id="filtro_facturacion" class="form-control selectpicker" onchange="filtrarFacturacion()">
                                <option value="">Todas las ventas</option>
                                <option value="sin_factura">Sin factura</option>
                                <option value="facturadas">Facturadas</option>
                                <option value="vigentes">Facturas vigentes</option>
                                <option value="anuladas">Facturas anuladas</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <table id="sale-table" class="table sale-list" style="width: 100%">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
                        <th>{{ trans('file.Date') }}</th>
                        <th>{{ trans('file.reference') }} / Factura</th>
                        <th>{{ trans('file.Biller') }} / Persona Servicio</th>
                        <th>{{ trans('file.customer') }}</th>
                        <th>{{ trans('file.Sale Status') }}</th>
                        <th>{{ trans('file.Payment Status') }}</th>
                        <th>{{ trans('file.grand total') }}</th>
                        <th>{{ trans('file.Paid') }}</th>
                        <th>{{ trans('file.Due') }}</th>
                        <th>Metodo Pago</th>
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
                    <th></th>
                    <th></th>
                    <th></th>
                </tfoot>
            </table>
        </div>
    </section>

    <div id="sale-details" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="container mt-3 pb-2 border-bottom">
                    <div class="row">
                        <div class="col-md-3">
                            <button id="print-btn" type="button" class="btn btn-default btn-sm d-print-none"><i
                                    class="dripicons-print"></i> {{ trans('file.Print') }}</button>

                            {{ Form::open(['route' => 'sale.sendmail', 'method' => 'post', 'class' => 'sendmail-form']) }}
                            <input type="hidden" name="sale_id">
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
                            <i style="font-size: 15px;">{{ trans('file.Sale Details') }}</i>
                        </div>
                    </div>
                </div>
                <div id="sale-content" class="modal-body">
                </div>
                <br>
                <table class="table table-bordered product-sale-list">
                    <thead>
                        <th>#</th>
                        <th>{{ trans('file.product') }}</th>
                        <th>{{ trans('file.Qty') }}</th>
                        <th>{{ trans('file.Unit Price') }}</th>
                        <th>{{ trans('file.Tax') }}</th>
                        <th>{{ trans('file.Discount') }}</th>
                        <th>{{ trans('file.Subtotal') }}</th>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <div id="sale-footer" class="modal-body"></div>
            </div>
        </div>
    </div>

    <div id="view-payment" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.All') }} {{ trans('file.Payment') }}
                    </h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <table class="table table-hover payment-list">
                        <thead>
                            <tr>
                                <th>{{ trans('file.date') }}</th>
                                <th>{{ trans('file.reference') }}</th>
                                <th>{{ trans('file.Account') }}</th>
                                <th>{{ trans('file.Amount') }}</th>
                                <th>{{ trans('file.Paid By') }}</th>
                                <th>{{ trans('file.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="add-payment" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Add Payment') }}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    {!! Form::open(['route' => 'sale.add-payment', 'method' => 'post', 'files' => true, 'class' => 'payment-form']) !!}
                    <div class="row">
                        <input type="hidden" name="balance">
                        <div class="col-md-6">
                            <label>{{ trans('file.Recieved Amount') }} *</label>
                            <input type="text" name="paying_amount" class="form-control numkey" step="any"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label>{{ trans('file.Paying Amount') }} *</label>
                            <input type="text" id="amount" name="amount" class="form-control" step="any"
                                required>
                        </div>
                        <div class="col-md-6 mt-1">
                            <label>{{ trans('file.Change') }} : </label>
                            <p class="change ml-2">0.00</p>
                        </div>
                        <div class="col-md-6 mt-1">
                            <label>{{ trans('file.Paid By') }}</label>
                            <select name="paid_by_id" class="form-control selectpicker">
                                @foreach ($lims_methodpay_list as $method)
                                    <option value="{{ $method->id }}">{{ $method->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="gift-card form-group">
                        <label> {{ trans('file.Gift Card') }} *</label>
                        <select id="gift_card_id" name="gift_card_id" class="selectpicker form-control"
                            data-live-search="true" data-live-search-style="begins" title="Select Gift Card...">
                            @php
                                $balance = [];
                                $expired_date = [];
                            @endphp
                            @foreach ($lims_gift_card_list as $gift_card)
                                <?php
                                $balance[$gift_card->id] = $gift_card->amount - $gift_card->expense;
                                $expired_date[$gift_card->id] = $gift_card->expired_date;
                                ?>
                                <option value="{{ $gift_card->id }}">{{ $gift_card->card_no }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mt-2">
                        <div class="card-element" class="form-control">
                        </div>
                        <div class="card-errors" role="alert"></div>
                    </div>
                    <div id="cheque">
                        <div class="form-group">
                            <label>{{ trans('file.Cheque Number') }} *</label>
                            <input type="text" name="cheque_no" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label> {{ trans('file.Account') }}</label>
                        <select class="form-control selectpicker" name="account_id">
                            @foreach ($lims_account_list as $account)
                                @if ($account->is_default)
                                    <option selected value="{{ $account->id }}">{{ $account->name }}
                                        [{{ $account->account_no }}]</option>
                                @else
                                    <option value="{{ $account->id }}">{{ $account->name }}
                                        [{{ $account->account_no }}]</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ trans('file.Payment Note') }}</label>
                        <textarea rows="3" class="form-control" name="payment_note"></textarea>
                    </div>

                    <input type="hidden" name="sale_id">

                    <button type="submit" class="btn btn-primary">{{ trans('file.submit') }}</button>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    <div id="edit-payment" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Update Payment') }}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    {!! Form::open(['route' => 'sale.update-payment', 'method' => 'post', 'class' => 'payment-form']) !!}
                    <div class="row">
                        <div class="col-md-6">
                            <label>{{ trans('file.Recieved Amount') }} *</label>
                            <input type="text" name="edit_paying_amount" class="form-control numkey" step="any"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label>{{ trans('file.Paying Amount') }} *</label>
                            <input type="text" name="edit_amount" class="form-control" step="any" required>
                        </div>
                        <div class="col-md-6 mt-1">
                            <label>{{ trans('file.Change') }} : </label>
                            <p class="change ml-2">0.00</p>
                        </div>
                        <div class="col-md-6 mt-1">
                            <label>{{ trans('file.Paid By') }}</label>
                            <select name="edit_paid_by_id" class="form-control selectpicker">
                                @foreach ($lims_methodpay_list as $method)
                                    <option value="{{ $method->id }}">{{ $method->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="gift-card form-group">
                        <label> {{ trans('file.Gift Card') }} *</label>
                        <select id="gift_card_id" name="gift_card_id" class="selectpicker form-control"
                            data-live-search="true" data-live-search-style="begins" title="Select Gift Card...">
                            @foreach ($lims_gift_card_list as $gift_card)
                                <option value="{{ $gift_card->id }}">{{ $gift_card->card_no }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mt-2">
                        <div class="card-element" class="form-control">
                        </div>
                        <div class="card-errors" role="alert"></div>
                    </div>
                    <div id="edit-cheque">
                        <div class="form-group">
                            <label>{{ trans('file.Cheque Number') }} *</label>
                            <input type="text" name="edit_cheque_no" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label> {{ trans('file.Account') }}</label>
                        <select class="form-control selectpicker" name="account_id">
                            @foreach ($lims_account_list as $account)
                                <option value="{{ $account->id }}">{{ $account->name }} [{{ $account->account_no }}]
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ trans('file.Payment Note') }}</label>
                        <textarea rows="3" class="form-control" name="edit_payment_note"></textarea>
                    </div>

                    <input type="hidden" name="payment_id">

                    <button type="submit" class="btn btn-primary">{{ trans('file.update') }}</button>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    <div id="add-delivery" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Add Delivery') }}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    {!! Form::open(['route' => 'delivery.store', 'method' => 'post', 'files' => true]) !!}
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.Delivery Reference') }}</label>
                            <p id="dr"></p>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.Sale Reference') }}</label>
                            <p id="sr"></p>
                        </div>
                        <div class="col-md-12 form-group">
                            <label>{{ trans('file.Status') }} *</label>
                            <select name="status" required class="form-control selectpicker">
                                <option value="1">{{ trans('file.Packing') }}</option>
                                <option value="2">{{ trans('file.Delivering') }}</option>
                                <option value="3">{{ trans('file.Delivered') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6 mt-2 form-group">
                            <label>{{ trans('file.Delivered By') }}</label>
                            <input type="text" name="delivered_by" class="form-control">
                        </div>
                        <div class="col-md-6 mt-2 form-group">
                            <label>{{ trans('file.Recieved By') }} </label>
                            <input type="text" name="recieved_by" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.customer') }} *</label>
                            <p id="customer"></p>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.Attach File') }}</label>
                            <input type="file" name="file" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.Address') }} *</label>
                            <textarea rows="3" name="address" class="form-control" required></textarea>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.Note') }}</label>
                            <textarea rows="3" name="note" class="form-control"></textarea>
                        </div>
                    </div>
                    <input type="hidden" name="reference_no">
                    <input type="hidden" name="sale_id">
                    <button type="submit" class="btn btn-primary">{{ trans('file.submit') }}</button>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    <div id="anular-factura-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title"> {{ __('file.Cancel Invoice') }} </h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                        <span aria-hidden="true">
                            <i class="dripicons-cross"></i></span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formDelete" action="{{ route('sales.anular_factura') }}" method="POST">
                        <p class="italic">
                            <small>
                                Este proceso anulará la venta facturada.* (Servicio de Impuestos)
                            </small>
                        </p>
                        
                        <!-- Sección de datos de la factura -->
                        <div class="card mb-3" id="factura-info-card" style="display:none;">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fa fa-file-text"></i> Datos de la Factura</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>N° Factura:</strong> <span id="modal-nro-factura">-</span></p>
                                        <p class="mb-1"><strong>Cliente:</strong> <span id="modal-cliente">-</span></p>
                                        <p class="mb-1"><strong>NIT/CI:</strong> <span id="modal-nit">-</span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Fecha:</strong> <span id="modal-fecha">-</span></p>
                                        <p class="mb-1"><strong>Total:</strong> <span id="modal-total">-</span></p>
                                        <p class="mb-1"><strong>Estado:</strong> <span id="modal-estado">-</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <input type="text" name="sale_anulacion_id" id="sale_anulacion_id" hidden>
                            <div class="form-group col-md-12">
                                <label>Motivo de Anulación</label>
                                <select name="motivo_anulacion_id" id="motivo_anulacion_id"
                                    class="selectpicker form-control" title="Seleccione motivo...">
                                </select>
                            </div>
                            
                            <!-- Opción para enviar WhatsApp -->
                            <div class="form-group col-md-12">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="send_whatsapp" name="send_whatsapp" value="1">
                                    <label class="custom-control-label" for="send_whatsapp">
                                        <i class="fa fa-whatsapp text-success"></i> Enviar notificación por WhatsApp al cliente
                                    </label>
                                </div>
                                <small class="form-text text-muted">Se enviará un mensaje al cliente informando sobre la anulación de la factura.</small>
                            </div>
                            
                            <!-- Campo número WhatsApp -->
                            <div class="form-group col-md-12" id="whatsapp_phone_container" style="display:none;">
                                <label>Número de WhatsApp <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-phone"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="whatsapp_phone" name="whatsapp_phone" placeholder="Ej: 59176543210" maxlength="15">
                                </div>
                                <small class="form-text text-muted">Incluir código de país (591 para Bolivia)</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div>
                                <b>
                                    ¿Está seguro?
                                </b>
                            </div>
                            <div class="">
                                @method('POST')
                                @csrf
                                <input type="submit" value="Confirmar" class="btn btn-danger">
                            </div>
                            <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">{{ __('file.Close') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="imprimir-factura-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true" class="modal fade text-left">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title"> Imprimir Factura </h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                        <span aria-hidden="true"><i class="dripicons-cross"></i></span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="imprimir-factura-steps">
                        <!-- Paso 1: Detalles de la venta -->
                        <div id="imprimir-step-1">
                            <h6>Detalles de la Venta / Datos para Facturar</h6>
                                <form id="imprimir-factura-form">
                                <!-- Hidden inputs and flags copied from POS modal -->
                                <input type="hidden" name="facturacion_id_hidden" value="{{ $lims_pos_setting_data->facturacion_id ?? ''}}">
                                <input type="hidden" name="codigo_emision_hidden" value="{{ $lims_pos_setting_data->codigo_emision ?? ''}}">
                                <input type="hidden" id="imprimir_sale_id" name="ajax_sale_id" value="">
                                <input type="hidden" name="bandera_factura_hidden" value="1">
                                <input type="hidden" name="bandera_vigencia_cufd_hidden">
                                <input type="hidden" name="bandera_codigo_excepcion_hidden">
                                <input type="hidden" name="bandera_codigo_documento_sector_hidden" value="1">
                                <input type="hidden" name="montoLey1886_hidden" value="0">
                                <input type="hidden" name="montoTasaDignidad_hidden" value="0">
                                <input type="hidden" name="sales_caso_especial_hidden" value="1">
                                <input type="hidden" name="sales_tipo_documento_hidden">

                                <!-- Incluir campos completos del POS -->
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Tipo de Factura</label>
                                        <div class="input-group">
                                            <select name="tipo_factura" id="imprimir_tipo_factura_id" class="selectpicker form-control" title="Seleccione...">
                                                <option value="1" selected>FACTURA COMPRA-VENTA</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Casos especiales</label>
                                        <div class="input-group">
                                            <select name="sales_caso_especial" id="imprimir_sales_caso_especial_id" class="selectpicker form-control"
                                                title="Seleccione...">
                                                <option value="1" selected>Ninguna</option>
                                                <option value="2">99001 (Extranjero no inscrito)</option>
                                                <option value="3">99002 (Control Tributario)</option>
                                                <option value="4">99003 (Ventas Menores)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                @if ($lims_pos_setting_data->codigo_emision == 1)
                                    <div class="row" id="imprimir_label_contingencia" style="display:none;">
                                        <div class="form-group col text-center">
                                            <p class="text-danger">Se está generando ventas en modo contingencia.</p>
                                            <div class="row">
                                                <div class="form-group col">
                                                    <label>Nro. Factura</label>
                                                    <input name="nro_factura_manual" type="number" min="0" step="1" class="form-control">
                                                </div>
                                                <div class="form-group col">
                                                    <label>Fecha emisión</label>
                                                    <input name="fecha_manual" type="datetime-local" step="any" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="dropdown-divider"></div>

                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Tipo de Documento</label>
                                        <div class="input-group">
                                            <select name="sales_tipo_documento" id="imprimir_sales_tipo_documento_id" class="selectpicker form-control"
                                                title="Seleccione documento..." data-live-search="true">
                                                @include('customer.partials-tipo-documentos')
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-4" id="imprimir_sales_valor_documento">
                                        <label>Valor Documento</label>
                                        <div class="input-group">
                                            <input type="text" name="sales_valor_documento" class="form-control">
                                            <div class="invalid-feedback"></div>
                                            <div class="valid-feedback"></div>
                                        </div>
                                    </div>
                                    <div class="form-group col" id="imprimir_sales_complemento">
                                        <label for="sales_complemento_documento">Complemento</label>
                                        <input type="text" name="sales_complemento_documento" class="form-control">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-5 mt-1">
                                        <label>Nombre Fiscal/Razón Social <span class="text-danger">*</span></label>
                                        <input type="text" name="sales_razon_social" id="imprimir_sales_razon_social" class="form-control" required>
                                    </div>
                                    <div class="col-md-5 mt-1" id="imprimir_sales_correo_electronico">
                                        <label>Email</label>
                                        <input type="email" name="sales_email" id="imprimir_sales_email" class="form-control">
                                    </div>
                                    <div class="col-md-2 mt-1">
                                        <label>Cod. Cliente/Fijo</label>
                                        <input type="text" name="codigo_fijo" id="imprimir_codigo_fijo" class="form-control">
                                    </div>
                                </div>

                                <hr>
                                <div id="imprimir-step-1-saleinfo">
                                    <!-- Información de la venta (solo lectura) -->
                                    <p><strong>Referencia:</strong> <span id="imprimir_ref">-</span></p>
                                    <p><strong>Fecha:</strong> <span id="imprimir_fecha">-</span></p>
                                    <p><strong>Grand Total:</strong> <span id="imprimir_grandtotal">-</span> &nbsp; <strong>Pagado:</strong> <span id="imprimir_paid">-</span> &nbsp; <strong>Due:</strong> <span id="imprimir_due">-</span></p>
                                </div>

                                <div class="mt-3 text-right">
                                    <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">Cerrar</button>
                                    <button type="button" id="imprimir-next-btn" class="btn btn-primary">Generar y Ver Factura</button>
                                </div>
                            </form>
                        </div>

                        <!-- Paso 2: PDF -->
                        <div id="imprimir-step-2" style="display:none;">
                            <h6 class="mb-3">Factura (PDF)</h6>
                            
                            <div id="imprimir-pdf-container" style="height:55vh; overflow:auto; border: 1px solid #dee2e6; border-radius: 4px; margin-bottom: 15px;">
                                <iframe id="imprimir-pdf-frame" src="" style="width:100%;height:100%;border:0;" frameborder="0"></iframe>
                            </div>
                            
                            <div class="text-right" style="padding-top: 10px; border-top: 1px solid #dee2e6;">
                                <button type="button" id="imprimir-back-btn" class="btn btn-secondary btn-sm mr-2">
                                    <i class="fa fa-arrow-left"></i> Atrás
                                </button>
                                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">
                                    <i class="fa fa-times"></i> Cerrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="importSales" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                {!! Form::open(['route' => 'sale.import', 'method' => 'post', 'files' => true]) !!}
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">Importar Ventas con Factura (SIAT)</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <p class="italic">
                        <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                    </p>
                    <p>{{ trans('file.The correct column order is') }} (image, name*, code*, type*, brand, category*,
                        unit_code*, cost*, price*, product_details) {{ trans('file.and you must follow this') }}.</p>
                    <p>{{ trans('file.To display Image it must be stored in') }} public/images/product
                        {{ trans('file.directory') }}. {{ trans('file.Image name must be same as product name') }}</p>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ trans('file.Upload CSV File') }} *</label>
                                {{ Form::file('file', ['class' => 'form-control', 'required']) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label> {{ trans('file.Sample File') }}</label>
                                <a href="public/sample_file/sample_products.csv" class="btn btn-info btn-block btn-md"><i
                                        class="dripicons-download"></i> {{ trans('file.Download') }}</a>
                            </div>
                        </div>
                    </div>
                    {{ Form::submit('Subir', ['class' => 'btn btn-primary']) }}
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $("ul#sale").siblings('a').attr('aria-expanded', 'true');
        $("ul#sale").addClass("show");
        $("ul#sale #sale-list-menu").addClass("active");
        var public_key = <?php echo json_encode($lims_pos_setting_data->stripe_public_key); ?>;
        var all_permission = <?php echo json_encode($all_permission); ?>;
        var sale_id = [];
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var balance = <?php echo json_encode($balance); ?>;
        var expired_date = <?php echo json_encode($expired_date); ?>;
        var current_date = <?php echo json_encode(date('Y-m-d')); ?>;
        var start_date_get = document.getElementById("start_date").value;
        var end_date_get = document.getElementById("end_date").value;
        var payment_date = [];
        var payment_reference = [];
        var paid_amount = [];
        var paying_method = [];
        var payment_id = [];
        var payment_note = [];
        var account = [];
        var deposit;
        
        // Cachear motivos de anulación globalmente
        var motivosAnulacionCache = null;
        
        blockAmounts();
        $(".gift-card").hide();
        $(".card-element").hide();
        $("#cheque").hide();
        $('#view-payment').modal('hide');
        $(document).on("click", "tr.sale-link td:not(:first-child, :last-child)", function() {
            var sale = $(this).parent().data('sale');
            saleDetails(sale);
        });
        $(document).on("click", ".view", function() {
            var sale = $(this).parent().parent().parent().parent().parent().data('sale');
            saleDetails(sale);
        });
        $("#print-btn").on("click", function() {
            var divToPrint = document.getElementById('sale-details');
            var newWin = window.open('', 'Print-Window');
            newWin.document.open();
            newWin.document.write(
                '<link rel="stylesheet" href="/public/vendor/bootstrap/css/bootstrap.min.css" type="text/css"><style type="text/css">@media print {.modal-dialog { max-width: 1000px;} }</style><body onload="window.print()">' +
                divToPrint.innerHTML + '</body>');
            newWin.document.close();
            setTimeout(function() {
                newWin.close();
            }, 10);
        });
        $(document).on("click", "table.sale-list tbody .add-payment", function() {
            $("#cheque").hide();
            $(".gift-card").hide();
            $(".card-element").hide();
            unblockAmounts();
            $('select[name="paid_by_id"]').val(1);
            $('.selectpicker').selectpicker('refresh');
            rowindex = $(this).closest('tr').index();
            deposit = $('table.sale-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.deposit').val();
            var sale_id = $(this).data('id').toString();
            var balance = $('table.sale-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(10)')
                .text();
            balance = parseFloat(balance.replace(/,/g, ''));
            $('input[name="paying_amount"]').val(balance);
            $('#add-payment input[name="balance"]').val(balance);
            $('input[name="amount"]').val(balance);
            $('input[name="sale_id"]').val(sale_id);
        });
        $(document).on("click", "table.sale-list tbody .get-payment", function(event) {
            rowindex = $(this).closest('tr').index();
            deposit = $('table.sale-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.deposit').val();
            var id = $(this).data('id').toString();
            $.get('sales/getpayment/' + id, function(data) {
                $(".payment-list tbody").remove();
                var newBody = $("<tbody>");
                payment_date = data[0];
                payment_reference = data[1];
                paid_amount = data[2];
                paying_method = data[3];
                payment_id = data[4];
                payment_note = data[5];
                cheque_no = data[6];
                gift_card_id = data[7];
                change = data[8];
                paying_amount = data[9];
                account_name = data[10];
                account_id = data[11];
                $.each(payment_date, function(index) {
                    var newRow = $("<tr>");
                    var cols = '';
                    cols += '<td>' + payment_date[index] + '</td>';
                    cols += '<td>' + payment_reference[index] + '</td>';
                    cols += '<td>' + account_name[index] + '</td>';
                    cols += '<td>' + paid_amount[index] + '</td>';
                    cols += '<td>' + paying_method[index] + '</td>';
                    if (paying_method[index] != 'Paypal')
                        cols +=
                        '<td><div class="btn-group"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ trans('file.action') }}<span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu"><li><button type="button" class="btn btn-link edit-btn" data-id="' +
                        payment_id[index] +
                        '" data-clicked=false data-toggle="modal" data-target="#edit-payment"><i class="dripicons-document-edit"></i> {{ trans('file.edit') }}</button></li><li class="divider"></li>{{ Form::open(['route' => 'sale.delete-payment', 'method' => 'post']) }}<li><input type="hidden" name="id" value="' +
                        payment_id[index] +
                        '" /> <button type="submit" class="btn btn-link" onclick="return confirmPaymentDelete()"><i class="dripicons-trash"></i> {{ trans('file.delete') }}</button></li>{{ Form::close() }}</ul></div></td>';
                    else
                        cols +=
                        '<td><div class="btn-group"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ trans('file.action') }}<span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">{{ Form::open(['route' => 'sale.delete-payment', 'method' => 'post']) }}<li><input type="hidden" name="id" value="' +
                        payment_id[index] +
                        '" /> <button type="submit" class="btn btn-link" onclick="return confirmPaymentDelete()"><i class="dripicons-trash"></i> {{ trans('file.delete') }}</button></li>{{ Form::close() }}</ul></div></td>';
                    newRow.append(cols);
                    newBody.append(newRow);
                    $("table.payment-list").append(newBody);
                });
                $('#view-payment').modal('show');
            });
        });
        $("table.payment-list").on("click", ".edit-btn", function(event) {
            $(".edit-btn").attr('data-clicked', true);
            $(".card-element").hide();
            $("#edit-cheque").hide();
            $('.gift-card').hide();
            $('#edit-payment select[name="edit_paid_by_id"]').prop('disabled', false);
            var id = $(this).data('id').toString();
            $.each(payment_id, function(index) {
                if (payment_id[index] == parseFloat(id)) {
                    $('input[name="payment_id"]').val(payment_id[index]);
                    $('#edit-payment select[name="account_id"]').val(account_id[index]);
                    if (paying_method[index] == 'Efectivo') {
                        unblockAmounts();
                        $('select[name="edit_paid_by_id"]').val(1);
                    } else if (paying_method[index] == 'Tarjeta_Regalo') {
                        blockAmounts();
                        $('select[name="edit_paid_by_id"]').val(3);
                        $('#edit-payment select[name="gift_card_id"]').val(gift_card_id[index]);
                        $('.gift-card').show();
                        $('#edit-payment select[name="edit_paid_by_id"]').prop('disabled', true);
                    } else if (paying_method[index] == 'Tarjeta_Credito_Debito') {
                        blockAmounts();
                        $('select[name="edit_paid_by_id"]').val(4);
                        $.getScript("public/vendor/stripe/checkout.js");
                        $(".card-element").show();
                        $('#edit-payment select[name="edit_paid_by_id"]').prop('disabled', true);
                    } else if (paying_method[index] == 'Cheque') {
                        blockAmounts();
                        $('select[name="edit_paid_by_id"]').val(5);
                        $("#edit-cheque").show();
                        $('input[name="edit_cheque_no"]').val(cheque_no[index]);
                        $('input[name="edit_cheque_no"]').attr('required', true);
                    } else if (paying_method[index] == 'Qr_Simple' || paying_method[index] == 'Qr_simple') {
                        blockAmounts();
                        $('select[name="edit_paid_by_id"]').val(6);
                    } else if (paying_method[index] == 'Paypal') {
                        blockAmounts();
                        $('select[name="edit_paid_by_id"]').val(8);
                    } else {
                        blockAmounts();
                        $('select[name="edit_paid_by_id"]').val(7);
                    }
                    $('.selectpicker').selectpicker('refresh');
                    $("#payment_reference").html(payment_reference[index]);
                    $('input[name="edit_paying_amount"]').val(paying_amount[index]);
                    $('#edit-payment .change').text(change[index]);
                    $('input[name="edit_amount"]').val(paid_amount[index]);
                    $('textarea[name="edit_payment_note"]').val(payment_note[index]);
                    return false;
                }
            });
            $('#view-payment').modal('hide');
        });
        $('select[name="paid_by_id"]').on("change", function() {
            var id = $(this).val();
            $('input[name="cheque_no"]').attr('required', false);
            $('#add-payment select[name="gift_card_id"]').attr('required', false);
            $(".payment-form").off("submit");
            if (id == 3) {
                blockAmounts();
                $(".gift-card").show();
                $(".card-element").hide();
                $("#cheque").hide();
                $('#add-payment select[name="gift_card_id"]').attr('required', true);
            } else if (id == 4) {
                blockAmounts();
                $.getScript("public/vendor/stripe/checkout.js");
                $(".card-element").show();
                $(".gift-card").hide();
                $("#cheque").hide();
            } else if (id == 5) {
                blockAmounts();
                $("#cheque").show();
                $(".gift-card").hide();
                $(".card-element").hide();
                $('input[name="cheque_no"]').attr('required', true);
            } else if (id == 8) {
                blockAmounts();
                $(".card-element").hide();
                $(".gift-card").hide();
                $("#cheque").hide();
            } else if (id == 6) {
                blockAmounts();
                $(".card-element").hide();
                $("#edit-cheque").hide();
                $('.gift-card').hide();
            } else {
                unblockAmounts();
                $(".card-element").hide();
                $(".gift-card").hide();
                $("#cheque").hide();
                if (id == 7) {
                    blockAmounts();
                    if ($('#add-payment input[name="amount"]').val() > parseFloat(deposit))
                        alert('Amount exceeds customer deposit! Customer deposit : ' + deposit);
                }
            }
        });
        $('#add-payment select[name="gift_card_id"]').on("change", function() {
            var id = $(this).val();
            if (expired_date[id] < current_date)
                alert('This card is expired!');
            else if ($('#add-payment input[name="amount"]').val() > balance[id]) {
                alert('Amount exceeds card balance! Gift Card balance: ' + balance[id]);
            }
        });
        $('input[name="paying_amount"]').on("input", function() {
            $(".change").text(parseFloat($(this).val() - $('input[name="amount"]').val()).toFixed(2));
        });
        $('input[name="amount"]').on("input", function() {
            if ($(this).val() > parseFloat($('input[name="paying_amount"]').val())) {
                alert('Paying amount cannot be bigger than recieved amount');
                $(this).val('');
            } else if ($(this).val() > parseFloat($('input[name="balance"]').val())) {
                alert('Paying amount cannot be bigger than due amount');
                $(this).val('');
            }
            $(".change").text(parseFloat($('input[name="paying_amount"]').val() - $(this).val()).toFixed(2));
            var id = $('#add-payment select[name="paid_by_id"]').val();
            var amount = $(this).val();
            if (id == 3) {
                id = $('#add-payment select[name="gift_card_id"]').val();
                if (amount > balance[id])
                    alert('Amount exceeds card balance! Gift Card balance: ' + balance[id]);
            } else if (id == 7) {
                if (amount > parseFloat(deposit))
                    alert('Amount exceeds customer deposit! Customer deposit : ' + deposit);
            }
        });
        $('select[name="edit_paid_by_id"]').on("change", function() {
            var id = $(this).val();
            $('input[name="edit_cheque_no"]').attr('required', false);
            $('#edit-payment select[name="gift_card_id"]').attr('required', false);
            $(".payment-form").off("submit");
            if (id == 3) {
                blockAmounts();
                $(".card-element").hide();
                $("#edit-cheque").hide();
                $('.gift-card').show();
                $('#edit-payment select[name="gift_card_id"]').attr('required', true);
            } else if (id == 4) {
                blockAmounts();
                $(".edit-btn").attr('data-clicked', true);
                $.getScript("public/vendor/stripe/checkout.js");
                $(".card-element").show();
                $("#edit-cheque").hide();
                $('.gift-card').hide();
            } else if (id == 5) {
                blockAmounts();
                $("#edit-cheque").show();
                $(".card-element").hide();
                $('.gift-card').hide();
                $('input[name="edit_cheque_no"]').attr('required', true);
            } else if (id == 6) {
                blockAmounts();
                $(".card-element").hide();
                $("#edit-cheque").hide();
                $('.gift-card').hide();
            } else {
                unblockAmounts();
                $(".card-element").hide();
                $("#edit-cheque").hide();
                $('.gift-card').hide();
                if (id == 7) {
                    blockAmounts();
                    if ($('input[name="edit_amount"]').val() > parseFloat(deposit))
                        alert('Amount exceeds customer deposit! Customer deposit : ' + deposit);
                }
            }
        });
        $('#edit-payment select[name="gift_card_id"]').on("change", function() {
            var id = $(this).val();
            if (expired_date[id] < current_date)
                alert('This card is expired!');
            else if ($('#edit-payment input[name="edit_amount"]').val() > balance[id])
                alert('Amount exceeds card balance! Gift Card balance: ' + balance[id]);
        });
        $('input[name="edit_paying_amount"]').on("input", function() {
            $(".change").text(parseFloat($(this).val() - $('input[name="edit_amount"]').val()).toFixed(2));
        });
        $('input[name="edit_amount"]').on("input", function() {
            if ($(this).val() > parseFloat($('input[name="edit_paying_amount"]').val())) {
                alert('Paying amount cannot be bigger than recieved amount');
                $(this).val('');
            }
            $(".change").text(parseFloat($('input[name="edit_paying_amount"]').val() - $(this).val()).toFixed(2));
            var amount = $(this).val();
            var id = $('#edit-payment select[name="gift_card_id"]').val();
            if (amount > balance[id]) {
                alert('Amount exceeds card balance! Gift Card balance: ' + balance[id]);
            }
            var id = $('#edit-payment select[name="edit_paid_by_id"]').val();
            if (id == 7) {
                if (amount > parseFloat(deposit))
                    alert('Amount exceeds customer deposit! Customer deposit : ' + deposit);
            }
        });
        $(document).on("click", "table.sale-list tbody .add-delivery", function(event) {
            var id = $(this).data('id').toString();
            $.get('delivery/create/' + id, function(data) {
                $('#dr').text(data[0]);
                $('#sr').text(data[1]);
                if (data[2]) {
                    $('select[name="status"]').val(data[2]);
                    $('.selectpicker').selectpicker('refresh');
                }
                $('input[name="delivered_by"]').val(data[3]);
                $('input[name="recieved_by"]').val(data[4]);
                $('#customer').text(data[5]);
                $('textarea[name="address"]').val(data[6]);
                $('textarea[name="note"]').val(data[7]);
                $('input[name="reference_no"]').val(data[0]);
                $('input[name="sale_id"]').val(id);
                $('#add-delivery').modal('show');
            });
        });
        filterdate();

        function filterdate() {
            var start_date_get = document.getElementById("start_date").value;
            var end_date_get = document.getElementById("end_date").value;
            //$("#sale-table").empty();
            console.log("call filter date: " + start_date_get + " to " + end_date_get);
            $('#sale-table').DataTable({
                destroy: true,
                "processing": true,
                "serverSide": true,
                "ajax": {
                    url: "sales/sale-data",
                    data: {
                        all_permission: all_permission,
                        start_date: start_date_get,
                        end_date: end_date_get
                    },
                    dataType: "json",
                    type: "post"
                },
                "createdRow": function(row, data, dataIndex) {
                    $(row).addClass('sale-link');
                    $(row).attr('data-sale', data['sale']);
                    
                    // Aplicar filtro de facturación si está activo
                    var filtro = $('#filtro_facturacion').val();
                    var referenceText = data['reference_no'] || '';
                    
                    if (filtro) {
                        var shouldShow = false;
                        if (filtro === 'sin_factura' && referenceText.includes('SIN FACTURA')) {
                            shouldShow = true;
                        } else if (filtro === 'facturadas' && !referenceText.includes('SIN FACTURA') && (referenceText.includes('COM-VEN') || referenceText.includes('ALQ') || referenceText.includes('SERV'))) {
                            shouldShow = true;
                        } else if (filtro === 'vigentes' && referenceText.includes('VIGENTE')) {
                            shouldShow = true;
                        } else if (filtro === 'anuladas' && referenceText.includes('ANULADA')) {
                            shouldShow = true;
                        }
                        
                        if (!shouldShow) {
                            $(row).hide();
                        }
                    }
                    
                    // Agregar botón "Imprimir Factura" dentro del menú de acciones si existe
                    try {
                        var saleId = data['sale'] ? data['sale'][13] : '';
                        var $optionsCell = $('td', row).eq(11);
                        var $menu = $optionsCell.find('.dropdown-menu');
                        if ($menu.length) {
                            // Añadir al inicio del menú
                            $menu.prepend('<li><button type="button" class="btn btn-link imprimir-factura-modal" data-id="' + saleId + '"><i class="fa fa-print"></i> Imprimir Factura</button></li>');
                        } else if (saleId) {
                            // Si no hay menú, añadir botón simple
                            $optionsCell.append('<button type="button" class="btn btn-link imprimir-factura-modal" data-id="' + saleId + '"><i class="fa fa-print"></i></button>');
                        }
                    } catch (e) {
                        console.log('No se pudo añadir el botón imprimir-factura:', e);
                    }
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
                        "data": "biller"
                    },
                    {
                        "data": "customer"
                    },
                    {
                        "data": "sale_status"
                    },
                    {
                        "data": "payment_status"
                    },
                    {
                        "data": "grand_total"
                    },
                    {
                        "data": "paid_amount"
                    },
                    {
                        "data": "due"
                    },
                    {
                        "data": "paymethod"
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
                        'targets': [0, 3, 4, 5, 6, 9, 10, 11]
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
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
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
                        extend: 'excel',
                        text: '<span class="fa fa-file-excel-o"> Excel</span>',
                        exportOptions: {
                            columns: ':visible:Not(.not-exported)',
                            rows: ':visible'
                        }
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
                            sale_id.length = 0;
                            $(':checkbox:checked').each(function(i) {
                                if (i) {
                                    var sale = $(this).closest('tr').data('sale');
                                    sale_id[i - 1] = sale[13];
                                }
                            });
                            if (sale_id.length && confirm("¿Esta seguro de querer eliminar?")) {
                                $.ajax({
                                    type: 'POST',
                                    url: 'sales/deletebyselection',
                                    data: {
                                        saleIdArray: sale_id
                                    },
                                    success: function(data) {
                                        //dt.rows({ page: 'current', selected: true }).deselect();
                                        dt.rows({
                                            page: 'current',
                                            selected: true
                                        }).remove().draw(false);
                                        swal('Mensaje', '' + data, 'success');
                                    }
                                });
                            } else if (!sale_id.length)
                                swal('Error', 'Ninguna fila seleccionada!', 'error');
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
                },
            });
        }

        function datatable_sum(dt_selector, is_calling_first) {
            if (dt_selector.rows('.selected').any() && is_calling_first) {
                var rows = dt_selector.rows('.selected').indexes();
                $(dt_selector.column(7).footer()).html(dt_selector.cells(rows, 7, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(8).footer()).html(dt_selector.cells(rows, 8, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(9).footer()).html(dt_selector.cells(rows, 9, {
                    page: 'current'
                }).data().sum().toFixed(2));
            } else {
                $(dt_selector.column(7).footer()).html(dt_selector.cells(rows, 7, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(8).footer()).html(dt_selector.cells(rows, 8, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(9).footer()).html(dt_selector.cells(rows, 9, {
                    page: 'current'
                }).data().sum().toFixed(2));
            }
        }

        function saleDetails(sale) {
            $("#sale-details input[name='sale_id']").val(sale[13]);
            var htmltext = '<strong>{{ trans('file.Date') }}: </strong>' + sale[0] +
                '<br><strong>{{ trans('file.reference') }}: </strong>' + sale[1] +
                '<br><strong>{{ trans('file.Warehouse') }}: </strong>' + sale[27] +
                '<br><strong>{{ trans('file.Sale Status') }}: </strong>' + sale[2] +
                '<br><br><div class="row"><div class="col-md-6"><strong>{{ trans('file.From') }}:</strong><br>' + sale[
                    3] + '<br>' + sale[4] + '<br>' + sale[5] + '<br>' + sale[6] + '<br>' + sale[7] + '<br>' + sale[8] +
                '</div><div class="col-md-6"><div class="float-right"><strong>{{ trans('file.To') }}:</strong><br>' +
                sale[9] + '<br>' + sale[10] + '<br>' + sale[11] + '<br>' + sale[12] + '</div></div></div>';
            $.get('sales/product_sale/' + sale[13], function(data) {
                $(".product-sale-list tbody").remove();
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
                cols += '<td>' + sale[14] + '</td>';
                cols += '<td>' + sale[15] + '</td>';
                cols += '<td>' + sale[16] + '</td>';
                newRow.append(cols);
                newBody.append(newRow);
                var newRow = $("<tr>");
                cols = '';
                cols += '<td colspan=6><strong>{{ trans('file.Order Tax') }}:</strong></td>';
                cols += '<td>' + sale[17] + '(' + sale[18] + '%)' + '</td>';
                newRow.append(cols);
                newBody.append(newRow);
                var newRow = $("<tr>");
                cols = '';
                cols += '<td colspan=6><strong>{{ trans('file.Order Discount') }}:</strong></td>';
                cols += '<td>' + sale[19] + '</td>';
                newRow.append(cols);
                newBody.append(newRow);
                if (sale[28]) {
                    var newRow = $("<tr>");
                    cols = '';
                    cols += '<td colspan=6><strong>{{ trans('file.Coupon Discount') }} [' + sale[28] +
                        ']:</strong></td>';
                    cols += '<td>' + sale[29] + '</td>';
                    newRow.append(cols);
                    newBody.append(newRow);
                }

                var newRow = $("<tr>");
                cols = '';
                cols += '<td colspan=6><strong>{{ trans('file.Shipping Cost') }}:</strong></td>';
                cols += '<td>' + sale[20] + '</td>';
                newRow.append(cols);
                newBody.append(newRow);
                if (sale[30] > 0) {
                    var newRow = $("<tr>");
                    cols = '';
                    cols += '<td colspan=6><strong>Propinas:</strong></td>';
                    cols += '<td>' + sale[30] + '</td>';
                    newRow.append(cols);
                    newBody.append(newRow);
                }

                var newRow = $("<tr>");
                cols = '';
                cols += '<td colspan=6><strong>{{ trans('file.grand total') }}:</strong></td>';
                cols += '<td>' + sale[21] + '</td>';
                newRow.append(cols);
                newBody.append(newRow);
                var newRow = $("<tr>");
                cols = '';
                cols += '<td colspan=6><strong>{{ trans('file.Paid Amount') }}:</strong></td>';
                cols += '<td>' + sale[22] + '</td>';
                newRow.append(cols);
                newBody.append(newRow);
                var newRow = $("<tr>");
                cols = '';
                cols += '<td colspan=6><strong>{{ trans('file.Due') }}:</strong></td>';
                cols += '<td>' + parseFloat(sale[21] - sale[22]).toFixed(2) + '</td>';
                newRow.append(cols);
                newBody.append(newRow);
                $("table.product-sale-list").append(newBody);
            });
            var htmlfooter = '<p><strong>{{ trans('file.Sale Note') }}:</strong> ' + sale[23] +
                '</p><p><strong>{{ trans('file.Staff Note') }}:</strong> ' + sale[24] +
                '</p><strong>{{ trans('file.Created By') }}:</strong><br>' + sale[25] + '<br>' + sale[26];
            $('#sale-content').html(htmltext);
            $('#sale-footer').html(htmlfooter);
            $('#sale-details').modal('show');
        }

        $(document).on('submit', '.payment-form', function(e) {
            if ($('input[name="paying_amount"]').val() < parseFloat($('#amount').val())) {
                alert('Paying amount cannot be bigger than recieved amount');
                $('input[name="amount"]').val('');
                $(".change").text(parseFloat($('input[name="paying_amount"]').val() - $('#amount').val()).toFixed(
                    2));
                e.preventDefault();
            } else if ($('input[name="edit_paying_amount"]').val() < parseFloat($('input[name="edit_amount"]')
                    .val())) {
                alert('Paying amount cannot be bigger than recieved amount');
                $('input[name="edit_amount"]').val('');
                $(".change").text(parseFloat($('input[name="edit_paying_amount"]').val() - $(
                    'input[name="edit_amount"]').val()).toFixed(2));
                e.preventDefault();
            }

            $('#edit-payment select[name="edit_paid_by_id"]').prop('disabled', false);
        });
        if (all_permission.indexOf("sales-delete") == -1)
            $('.buttons-delete').addClass('d-none');

        function confirmDelete() {
            if (confirm("Are you sure want to delete?")) {
                return true;
            }
            return false;
        }

        function confirmPaymentDelete() {
            if (confirm("Are you sure want to delete? If you delete this money will be refunded.")) {
                return true;
            }
            return false;
        }

        function blockAmounts() {
            $('input[name="paying_amount"]').prop('readonly', true);
            $('input[name="edit_paying_amount"]').prop('readonly', true);
            $('input[name="amount"]').prop('readonly', true);
            $('input[name="edit_amount"]').prop('readonly', true);
        }

        function unblockAmounts() {
            $('input[name="paying_amount"]').prop('readonly', false);
            $('input[name="edit_paying_amount"]').prop('readonly', false);
            $('input[name="amount"]').prop('readonly', false);
            $('input[name="edit_amount"]').prop('readonly', false);
        }

        // Cargar motivos de anulación una sola vez
        function cargarMotivosAnulacion(callback) {
            if (motivosAnulacionCache !== null) {
                // Ya están en caché, usar directamente
                callback(motivosAnulacionCache);
                return;
            }

            // Primera carga, obtener del servidor
            var url = '{{ route('sales.get_motivo_anulacion') }}';
            $.ajax({
                url: url,
                type: "GET",
                success: function(data) {
                    motivosAnulacionCache = data; // Guardar en caché
                    callback(data);
                },
                error: function() {
                    swal('Error', 'No se pudieron cargar los motivos de anulación', 'error');
                }
            });
        }

        $(document).on("click", "table.sale-list tbody .anular-factura-modal", function(event) {
            var id = $(this).data('id').toString();
            var $tr = $(this).closest('tr');

            $('input[name="sale_anulacion_id"]').val(id);
            
            // Resetear checkbox y campo de WhatsApp
            $('#send_whatsapp').prop('checked', false);
            $('#whatsapp_phone_container').hide();
            $('#whatsapp_phone').val('');

            // Obtener datos de la venta desde el atributo data-sale del tr
            var sale = $tr.data('sale');
            
            // Mostrar datos de la factura si están disponibles
            if (sale) {
                // Extraer información del array sale
                // Índices aproximados basados en el array de ventas
                var fecha = sale[0] || '-';
                var referencia = sale[1] || '-';
                var cliente = sale[2] || '-';
                var grandTotal = sale[7] || '0.00';
                var estadoFactura = 'VIGENTE'; // Por defecto
                var nroFactura = sale[32] || '-';
                var nitCliente = '-';
                
                // Mostrar los datos en el modal
                $('#modal-nro-factura').text(nroFactura);
                $('#modal-cliente').text(cliente);
                $('#modal-nit').text(nitCliente);
                $('#modal-fecha').text(fecha);
                $('#modal-total').text('Bs. ' + grandTotal);
                $('#modal-estado').html('<span class="badge badge-success">VIGENTE</span>');
                
                $('#factura-info-card').show();
                
                // Intentar obtener el número de teléfono del cliente
                $.ajax({
                    url: '{{ route('sales.get_customer_phone') }}',
                    type: 'POST',
                    data: {
                        sale_id: id,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success && response.phone) {
                            $('#whatsapp_phone').val(response.phone);
                        }
                    },
                    error: function() {
                        console.log('No se pudo obtener el teléfono del cliente');
                    }
                });
            } else {
                $('#factura-info-card').hide();
            }

            // Cargar motivos (desde caché o servidor)
            cargarMotivosAnulacion(function(data) {
                $("select[name='motivo_anulacion_id']").empty();
                for (let i = 0; i < data.length; i++) {
                    $("select[name='motivo_anulacion_id']").append('<option value="' + data[i]
                        .codigo_clasificador + '">' + data[i].codigo_clasificador + ' - ' +
                        data[i].descripcion + '</option>');
                }
                $('.selectpicker').selectpicker('refresh');
                $('#anular-factura-modal').modal('show');
            });
        });
        $(document).on("click", "table.sale-list tbody .imprimir-factura-modal", function(event) {
            var id = $(this).data('id').toString();
            var $tr = $(this).closest('tr');
            var sale = $tr.data('sale');
            
            console.log('═══════════════════════════════════════════════════════');
            console.log('🔍 INICIO - Click en botón Imprimir Factura');
            console.log('Sale ID:', id);
            console.log('¿Existe array sale?', !!sale);
            console.log('Datos completos de venta:', sale);
            
            if (sale) {
                console.log('📊 Estructura del array sale:');
                console.log('  - Índice 0 (fecha):', sale[0]);
                console.log('  - Índice 1 (referencia):', sale[1]);
                console.log('  - Índice 13 (id):', sale[13]);
                console.log('  - Índice 31 (CUF):', sale[31]);
                console.log('  - Índice 32 (nro_factura):', sale[32]);
                console.log('  - Total elementos en array:', sale.length);
            }
            
            // Extraer CUF y nro_factura del array sale (índices 31 y 32)
            var cuf = sale ? (sale[31] || '').trim() : '';
            var nroFactura = sale ? (sale[32] || '').trim() : '';
            
            console.log('📄 CUF extraído:', cuf);
            console.log('📄 Nro Factura extraído:', nroFactura);
            console.log('📄 Longitud CUF:', cuf.length);
            console.log('═══════════════════════════════════════════════════════');

            // Rellenar formulario y vistas con datos de la venta
            if (sale) {
                $('#imprimir_sale_id').val(sale[13] || id);
                $('#imprimir_ref').text(sale[1] || '');
                $('#imprimir_fecha').text(sale[0] || '');
                $('#imprimir_grandtotal').text(sale[21] || '');
                $('#imprimir_paid').text(sale[22] || '');
                $('#imprimir_due').text(parseFloat((sale[21] || 0) - (sale[22] || 0)).toFixed(2));
                
                // Obtener customer_id del array de sale (suponiendo que está en el índice 4)
                var customerId = sale[4] || sale[14];
                
                if (customerId) {
                    // Obtener datos completos del cliente
                    $.get('{{ url("sales/getcliente") }}/' + customerId, function(data) {
                        $('#imprimir_sales_razon_social').val(data.name || '');
                        $('#imprimir_sales_email').val(data.email || '');
                        $('#imprimir_codigo_fijo').val(data.codigofijo || data.id || '');
                        
                        // Auto-poblar teléfono para WhatsApp
                        var whatsappPhone = '';
                        if (data.phone_number) {
                            // Limpiar el teléfono (quitar espacios, guiones, etc)
                            var cleanPhone = data.phone_number.toString().replace(/[\s\-\(\)]/g, '');
                            // Si no empieza con +, agregar +591 (Bolivia)
                            if (!cleanPhone.startsWith('+')) {
                                if (!cleanPhone.startsWith('591') && cleanPhone.length < 11) {
                                    cleanPhone = '591' + cleanPhone;
                                }
                                cleanPhone = '+' + cleanPhone;
                            }
                            whatsappPhone = cleanPhone;
                            $('#imprimir_whatsapp_phone').val(cleanPhone);
                        } else {
                            $('#imprimir_whatsapp_phone').val('');
                        }
                        
                        // Guardar teléfono en data del modal para usar en paso 2
                        $('#imprimir-factura-modal').data('customer-phone', whatsappPhone);
                        
                        if (data.tipo_documento) {
                            $('#imprimir_sales_tipo_documento_id').val(data.tipo_documento);
                            $('#imprimir_sales_tipo_documento_id').selectpicker('refresh');
                            $('input[name="sales_tipo_documento_hidden"]').val(data.tipo_documento);
                            
                            // Mostrar/ocultar campos según el tipo
                            if (data.tipo_documento == '5') { // NIT
                                $('#imprimir_sales_valor_documento').show();
                                $('#imprimir_sales_complemento').hide();
                            } else if (data.tipo_documento == '1') { // CI
                                $('#imprimir_sales_valor_documento').show();
                                $('#imprimir_sales_complemento').show();
                            } else if (data.tipo_documento == '99001' || data.tipo_documento == '99002' || data.tipo_documento == '99003') {
                                $('#imprimir_sales_valor_documento').hide();
                                $('#imprimir_sales_complemento').hide();
                            } else {
                                $('#imprimir_sales_valor_documento').show();
                                $('#imprimir_sales_complemento').hide();
                            }
                        }
                        
                        if (data.valor_documento) {
                            $('input[name="sales_valor_documento"]').val(data.valor_documento);
                        }
                        
                        if (data.complemento_documento) {
                            $('input[name="sales_complemento_documento"]').val(data.complemento_documento);
                        }
                        
                        $('.selectpicker').selectpicker('refresh');
                    }).fail(function() {
                        // Si falla, usar datos básicos del sale
                        $('#imprimir_sales_razon_social').val(sale[9] || sale[3] || '');
                        $('#imprimir_sales_email').val(sale[12] || '');
                        $('#imprimir_whatsapp_phone').val('');
                    });
                } else {
                    // Sin customer_id, usar datos del array sale
                    $('#imprimir_sales_razon_social').val(sale[9] || sale[3] || '');
                    $('#imprimir_sales_email').val(sale[12] || '');
                    $('#imprimir_whatsapp_phone').val('');
                }
            } else {
                $('#imprimir_sale_id').val(id);
            }

            // Clear any previous PDF
            $('#imprimir-pdf-frame').attr('src', '');
            $('#imprimir-step-1').show();
            $('#imprimir-step-2').hide();
            
            // Limpiar badges y alertas previas antes de verificar
            $('#exampleModalLabel').html('Imprimir Factura');
            $('#imprimir-step-1-saleinfo .alert').remove();
            
            // Mostrar loader durante verificación
            var loaderHtml = '<div id="factura-loader" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">';
            loaderHtml += '<div style="background: white; padding: 30px; border-radius: 10px; text-align: center;">';
            loaderHtml += '<i class="fa fa-spinner fa-spin fa-3x text-primary mb-3"></i>';
            loaderHtml += '<h5>Verificando factura...</h5>';
            loaderHtml += '<p class="text-muted">Por favor espere</p>';
            loaderHtml += '</div></div>';
            $('body').append(loaderHtml);

            // Función helper para cargar factura SIAT
            var facturaVerificada = false;
            var facturaData = null;
            
            function loadFacturaSiat(saleId, verificada, dataFactura) {
                var url = '{{ url('sales/imprimir_factura') }}/' + saleId;
                
                // Limpiar container
                $('#imprimir-pdf-container').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-3x"></i><p>Cargando factura...</p></div>');
                
                // Cargar vista parcial con AJAX
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(html) {
                        $('#imprimir-pdf-container').html(html);
                        
                        // Si la factura fue verificada, mostrar información adicional
                        if (verificada && dataFactura) {
                            var infoVerificacion = '<div class="alert alert-success mt-2">';
                            infoVerificacion += '<h6><i class="fa fa-check-circle"></i> Factura Verificada en SIAT</h6>';
                            infoVerificacion += '<div class="row">';
                            infoVerificacion += '<div class="col-md-6">';
                            infoVerificacion += '<small><strong>CUF:</strong> ' + (dataFactura.cuf || cuf || 'N/A') + '</small><br>';
                            infoVerificacion += '<small><strong>Nro. Factura:</strong> ' + (dataFactura.nro_factura || nroFactura || 'N/A') + '</small><br>';
                            infoVerificacion += '<small><strong>Fecha:</strong> ' + (dataFactura.fecha_factura || 'N/A') + '</small>';
                            infoVerificacion += '</div>';
                            infoVerificacion += '<div class="col-md-6">';
                            infoVerificacion += '<small><strong>Código Recepción:</strong> ' + (dataFactura.codigo_recepcion || 'N/A') + '</small><br>';
                            infoVerificacion += '<small><strong>CUFD:</strong> ' + (dataFactura.cufd ? dataFactura.cufd.substring(0, 30) + '...' : 'N/A') + '</small>';
                            infoVerificacion += '</div>';
                            infoVerificacion += '</div>';
                            
                            // Agregar botón para ver XML si existe
                            if (dataFactura.xml) {
                                infoVerificacion += '<hr>';
                                infoVerificacion += '<button type="button" class="btn btn-sm btn-info" id="ver-xml-factura"><i class="fa fa-code"></i> Ver XML de Factura</button>';
                            }
                            infoVerificacion += '</div>';
                            $('#imprimir-pdf-container').prepend(infoVerificacion);
                        }
                        
                        // Agregar botón de WhatsApp
                        var whatsappPhone = $('#imprimir-factura-modal').data('customer-phone') || '';
                        var whatsappBtn = '<div class="alert alert-info mt-3" id="whatsapp-section">';
                        whatsappBtn += '<h6><i class="fa fa-whatsapp"></i> Enviar por WhatsApp</h6>';
                        whatsappBtn += '<div class="form-group">';
                        whatsappBtn += '<label for="whatsapp-phone">Teléfono del Cliente (incluir código de país):</label>';
                        whatsappBtn += '<div class="input-group">';
                        whatsappBtn += '<div class="input-group-prepend">';
                        whatsappBtn += '<span class="input-group-text"><i class="fa fa-phone"></i></span>';
                        whatsappBtn += '</div>';
                        whatsappBtn += '<input type="text" class="form-control" id="whatsapp-phone" placeholder="Ej: +59171234567" value="' + whatsappPhone + '">';
                        whatsappBtn += '</div>';
                        whatsappBtn += '<small class="form-text text-muted">Ingrese el número de teléfono con código de país (Ej: +591 para Bolivia)</small>';
                        whatsappBtn += '</div>';
                        whatsappBtn += '<button type="button" class="btn btn-success btn-block" id="send-whatsapp-btn" data-sale-id="' + saleId + '">';
                        whatsappBtn += '<i class="fa fa-whatsapp"></i> Enviar Factura por WhatsApp';
                        whatsappBtn += '</button>';
                        whatsappBtn += '<div id="whatsapp-result" class="mt-2"></div>';
                        whatsappBtn += '</div>';
                        $('#imprimir-pdf-container').append(whatsappBtn);
                        
                        $('#imprimir-step-1').hide();
                        $('#imprimir-step-2').show();
                    },
                    error: function() {
                        $('#imprimir-pdf-container').html(
                            '<div class="alert alert-warning">' +
                            'Error al cargar la factura. ' +
                            '<a href="' + url + '" target="_blank" class="btn btn-primary btn-sm">Abrir en nueva ventana</a>' +
                            '</div>'
                        );
                        $('#imprimir-step-1').hide();
                        $('#imprimir-step-2').show();
                    }
                });
            }

            // Verificar factura con datosFactura DIRECTAMENTE usando el CUF (similar al flujo en POS)
            if (cuf && cuf.length > 0) {
                // Tenemos CUF, verificar con datosFactura DIRECTAMENTE (sin getBytesFactura)
                var urlDatosFactura = '{{ url('factura.venta/datos-factura') }}?cuf=' + cuf;
                
                console.log('🔍 Verificando factura directamente con CUF:', cuf);
                console.log('📡 URL de verificación:', urlDatosFactura);
                console.log('📤 Parámetros enviados:', JSON.stringify({
                    cuf: cuf,
                    sale_id: sale ? sale[13] : id,
                    nro_factura: nroFactura
                }));
                
                $.ajax({
                    url: urlDatosFactura,
                    type: 'GET',
                    dataType: 'json',
                    success: function(facturaData) {
                        console.log('✅ Verificación de factura exitosa');
                        console.log('📥 Respuesta recibida:', JSON.stringify(facturaData, null, 2));
                        
                        if (facturaData.ESTADO === 'OK') {
                            // Factura verificada exitosamente
                            console.log('🚀 Factura verificada - Cargando PDF directamente...');
                            
                            $('#imprimir-factura-modal').data('factura-verificada', true);
                            $('#imprimir-factura-modal').data('factura-data', facturaData.ENTITY);
                            $('#imprimir-factura-modal').data('cuf', cuf);
                            $('#imprimir-factura-modal').data('sale_id', sale[13] || id);
                            
                            // Mostrar indicador de factura verificada en el título
                            var badgeVerificado = '<span class="badge badge-success ml-2"><i class="fa fa-check-circle"></i> Factura Verificada en SIAT</span>';
                            $('#exampleModalLabel').html('Imprimir Factura ' + badgeVerificado);
                            
                            // IMPORTANTE: Ocultar paso 1 y mostrar paso 2 ANTES de abrir el modal
                            $('#imprimir-step-1').hide();
                            $('#imprimir-step-2').show();
                            
                            // Remover loader y abrir modal (ahora ya está en paso 2)
                            $('#factura-loader').remove();
                            $('#imprimir-factura-modal').modal('show');
                            
                            // Cargar PDF DESPUÉS de abrir el modal
                            loadFacturaSiat(sale[13] || id, true, facturaData.ENTITY);
                        } else {
                            // Factura no verificada - mostrar formulario
                            console.warn('⚠️ Estado de factura no es OK:', facturaData.ESTADO);
                            $('#imprimir-factura-modal').data('factura-verificada', false);
                            
                            var badgeNoVerificado = '<span class="badge badge-warning ml-2"><i class="fa fa-exclamation-triangle"></i> No Verificada</span>';
                            $('#exampleModalLabel').html('Imprimir Factura ' + badgeNoVerificado);
                            
                            // Remover loader y abrir modal en paso 1 (formulario)
                            $('#factura-loader').remove();
                            $('#imprimir-factura-modal').modal('show');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('❌ ERROR al verificar factura con SIAT');
                        console.error('📊 Status HTTP:', xhr.status);
                        console.error('📊 Status Text:', xhr.statusText);
                        console.error('📊 Ready State:', xhr.readyState);
                        console.error('📊 Error Type:', status);
                        console.error('📊 Error Message:', error);
                        console.error('📥 Response completa:', JSON.stringify({
                            status: xhr.status,
                            statusText: xhr.statusText,
                            responseText: xhr.responseText,
                            responseJSON: xhr.responseJSON,
                            readyState: xhr.readyState,
                            headers: xhr.getAllResponseHeaders()
                        }, null, 2));
                        
                        if (xhr.responseJSON) {
                            console.error('📋 Detalle del error:', JSON.stringify(xhr.responseJSON, null, 2));
                        }
                        
                        $('#imprimir-factura-modal').data('factura-verificada', false);
                        $('#imprimir-factura-modal').data('cuf', cuf);
                        $('#imprimir-factura-modal').data('sale_id', sale[13] || id);
                        
                        var badgeAdvertencia = '<span class="badge badge-warning ml-2"><i class="fa fa-exclamation-triangle"></i> Sin Verificar</span>';
                        $('#exampleModalLabel').html('Imprimir Factura ' + badgeAdvertencia);
                        
                        var alertAdvertencia = '<div class="alert alert-warning mt-2">';
                        alertAdvertencia += '<strong><i class="fa fa-exclamation-triangle"></i> Advertencia</strong><br>';
                        alertAdvertencia += '<small>No se pudo verificar el estado de la factura con SIAT.</small><br>';
                        
                        // Agregar información de error si está disponible
                        if (xhr.status === 404) {
                            alertAdvertencia += '<small class="text-muted">Error 404: Endpoint no encontrado</small>';
                        } else if (xhr.status === 500) {
                            alertAdvertencia += '<small class="text-muted">Error 500: Error interno del servidor</small>';
                        } else if (xhr.status === 0) {
                            alertAdvertencia += '<small class="text-muted">Error de conexión: No se pudo contactar con el servidor</small>';
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            alertAdvertencia += '<small class="text-muted">' + xhr.responseJSON.message + '</small>';
                        }
                        
                        alertAdvertencia += '</div>';
                        $('#imprimir-step-1-saleinfo').append(alertAdvertencia);
                        
                        // Remover loader y abrir modal incluso con error
                        $('#factura-loader').remove();
                        $('#imprimir-factura-modal').modal('show');
                    }
                });
            } else {
                // Sin CUF - factura no generada aún
                console.log('⚠️ Sin CUF - factura no generada');
                $('#imprimir-factura-modal').data('factura-verificada', false);
                $('#imprimir-factura-modal').data('sale_id', sale[13] || id);
                
                var badgeSinFactura = '<span class="badge badge-secondary ml-2"><i class="fa fa-exclamation-circle"></i> Sin Factura</span>';
                $('#exampleModalLabel').html('Imprimir Factura ' + badgeSinFactura);
                
                // Remover loader y abrir modal sin CUF
                $('#factura-loader').remove();
                $('#imprimir-factura-modal').modal('show');
            }
        });

        // Siguiente -> intentar obtener/generar factura SIAT y mostrar paso 2
        $(document).on('click', '#imprimir-next-btn', function() {
            var saleId = $('#imprimir_sale_id').val();
            if (!saleId) {
                alert('No se encontró el id de la venta.');
                return;
            }

            var urlGet = '{{ route('sales.obtener_bytes_factura', ':id') }}'.replace(':id', saleId);
            var urlFactura = '{{ url('sales/imprimir_factura') }}/' + saleId;
            var urlFinalize = '{{ route('sales.finalize-ajax') }}';
            
            // Obtener datos de verificación si existen
            var facturaVerificada = $('#imprimir-factura-modal').data('factura-verificada');
            var facturaData = $('#imprimir-factura-modal').data('factura-data');
            var tieneCuf = $('#imprimir-factura-modal').data('cuf');

            // Verificar si la factura ya existe (usando CUF del array)
            if (tieneCuf && tieneCuf.length > 0) {
                // Factura existe, cargar directamente usando función global
                console.log('✅ Factura existe (CUF presente), cargando...');
                loadFacturaSiat(saleId, facturaVerificada, facturaData);
            } else {

                // No invoice yet -> call finalize-ajax to generate it
                console.log('⚠️ Factura no encontrada, generando...');
                $('#imprimir-pdf-container').html('<div class="alert alert-info"><i class="fa fa-spinner fa-spin"></i> Factura no encontrada. Generando factura SIAT...</div>');
                $('#imprimir-step-1').hide();
                $('#imprimir-step-2').show();
                
                // Recopilar todos los datos del formulario
                var formData = {
                    sale_id: saleId,
                    sales_razon_social: $('input[name="sales_razon_social"]').val(),
                    sales_email: $('input[name="sales_email"]').val(),
                    sales_tipo_documento: $('select[name="sales_tipo_documento"]').val() || $('#imprimir_sales_tipo_documento_id').val(),
                    sales_valor_documento: $('input[name="sales_valor_documento"]').val(),
                    sales_complemento_documento: $('input[name="sales_complemento_documento"]').val(),
                    codigo_fijo: $('input[name="codigo_fijo"]').val(),
                    sales_caso_especial: $('select[name="sales_caso_especial"]').val() || '1',
                    tipo_factura: $('select[name="tipo_factura"]').val() || '1',
                    bandera_factura_hidden: '1',
                    bandera_codigo_documento_sector_hidden: '1',
                    nro_factura_manual: $('input[name="nro_factura_manual"]').val(),
                    fecha_manual: $('input[name="fecha_manual"]').val()
                };
                
                $.ajax({
                    url: urlFinalize,
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: formData,
                    dataType: 'json',
                    success: function(fin) {
                        if (fin && fin.status) {
                            // After successful generation, load the SIAT invoice
                            setTimeout(function() {
                                loadFacturaSiat(saleId);
                            }, 1000);
                        } else {
                            $('#imprimir-pdf-container').html(
                                '<div class="alert alert-danger">Error generando factura: ' + 
                                (fin.message || 'Desconocido') + '</div>' +
                                '<button type="button" id="imprimir-back-btn" class="btn btn-secondary">Volver</button>'
                            );
                        }
                    },
                    error: function(xhr) {
                        var msg = 'Error al generar la factura.';
                        try { msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : msg; } catch (e) {}
                        $('#imprimir-pdf-container').html(
                            '<div class="alert alert-danger">' + msg + '</div>' +
                            '<button type="button" id="imprimir-back-btn" class="btn btn-secondary">Volver</button>'
                        );
                    }
                });
            }
        });

        // Atrás -> volver a paso 1
        $(document).on('click', '#imprimir-back-btn', function() {
            $('#imprimir-step-2').hide();
            $('#imprimir-step-1').show();
        });

        // Al cerrar modal limpiar estado
        $('#imprimir-factura-modal').on('hidden.bs.modal', function () {
            $('#imprimir-pdf-container').html('<iframe id="imprimir-pdf-frame" src="" style="width:100%;height:100%;border:0;" frameborder="0"></iframe>');
            $('#imprimir-step-2').hide();
            $('#imprimir-step-1').show();
            $(this).removeData('factura-verificada');
            $(this).removeData('factura-data');
            $(this).removeData('cuf');
            $(this).removeData('sale_id');
            
            // Limpiar alertas de verificación
            $('#imprimir-step-1-saleinfo .alert').remove();
            
            // Restaurar título modal
            $('#exampleModalLabel').html('Imprimir Factura');
            
            // Restaurar título del modal
            $('#exampleModalLabel').html('Imprimir Factura');
            
            // Limpiar alertas de verificación del paso 1
            $('#imprimir-step-1-saleinfo .alert').remove();
            
            // Limpiar estado de WhatsApp
            $('#imprimir_whatsapp_phone').val('');
            $('#imprimir-whatsapp-status').html('').hide();
            $('#imprimir-send-whatsapp-btn').prop('disabled', false);
        });

        // Manejador del botón de envío por WhatsApp
        $(document).on('click', '#send-whatsapp-btn', function() {
            var saleId = $(this).data('sale-id');
            var phone = $('#whatsapp-phone').val().trim();
            
            // Validar campos
            if (!phone) {
                $('#whatsapp-result').html(
                    '<div class="alert alert-warning">' +
                    '<i class="fa fa-exclamation-triangle"></i> Por favor ingrese un número de teléfono.' +
                    '</div>'
                );
                return;
            }
            
            // Validar formato de teléfono (debe incluir +)
            if (!phone.startsWith('+')) {
                $('#whatsapp-result').html(
                    '<div class="alert alert-warning">' +
                    '<i class="fa fa-exclamation-triangle"></i> El número debe incluir el código de país (Ej: +591...)' +
                    '</div>'
                );
                return;
            }
            
            // Remover el símbolo + antes de enviar
            var phoneClean = phone.replace(/^\+/, '');
            
            // Deshabilitar botón mientras se procesa
            $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Enviando...');
            
            // Enviar petición AJAX
            $.ajax({
                url: '{{ route('sales.send-invoice-whatsapp') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    sale_id: saleId,
                    phone: phoneClean
                },
                success: function(response) {
                    console.log('✅ WhatsApp enviado exitosamente', response);
                    
                    $('#whatsapp-result').html(
                        '<div class="alert alert-success">' +
                        '<i class="fa fa-check-circle"></i> <strong>¡Factura enviada por WhatsApp exitosamente!</strong><br>' +
                        '<small>' + (response.message || 'La factura ha sido enviada al cliente.') + '</small>' +
                        '</div>'
                    );
                    
                    // Limpiar el campo de teléfono
                    $('#whatsapp-phone').val('');
                    
                    // Habilitar botón nuevamente
                    $('#send-whatsapp-btn').prop('disabled', false).html('<i class="fa fa-whatsapp"></i> Enviar Factura por WhatsApp');
                },
                error: function(xhr, status, error) {
                    console.error('❌ Error al enviar WhatsApp', xhr.responseJSON);
                    
                    var errorMessage = 'Ocurrió un error al enviar la factura por WhatsApp.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    $('#whatsapp-result').html(
                        '<div class="alert alert-danger">' +
                        '<i class="fa fa-times-circle"></i> <strong>Error:</strong> ' + errorMessage +
                        '</div>'
                    );
                    
                    // Habilitar botón nuevamente
                    $('#send-whatsapp-btn').prop('disabled', false).html('<i class="fa fa-whatsapp"></i> Enviar Factura por WhatsApp');
                }
            });
        });

        // ===== FUNCIONES PARA MANEJO DE CAMPOS EN MODAL IMPRIMIR =====
        
        // Manejar cambio en tipo de documento
        $(document).on('change', '#imprimir_sales_tipo_documento_id', function() {
            var tipo = $(this).val();
            $('input[name="sales_tipo_documento_hidden"]').val(tipo);
            
            // Mostrar/ocultar campos según el tipo
            if (tipo == '5') { // NIT
                $('#imprimir_sales_valor_documento').show();
                $('#imprimir_sales_complemento').hide();
                $('input[name="sales_complemento_documento"]').val('');
            } else if (tipo == '1') { // CI
                $('#imprimir_sales_valor_documento').show();
                $('#imprimir_sales_complemento').show();
            } else if (tipo == '99001' || tipo == '99002' || tipo == '99003') {
                $('#imprimir_sales_valor_documento').hide();
                $('#imprimir_sales_complemento').hide();
                $('input[name="sales_valor_documento"]').val('0');
                $('input[name="sales_complemento_documento"]').val('');
            } else {
                $('#imprimir_sales_valor_documento').show();
                $('#imprimir_sales_complemento').hide();
                $('input[name="sales_complemento_documento"]').val('');
            }
        });

        // Función para filtrar ventas por estado de facturación
        function filtrarFacturacion() {
            filterdate(); // Recargar la tabla con el filtro aplicado
        }

        // Manejar cambio en casos especiales
        $(document).on('change', '#imprimir_sales_caso_especial_id', function() {
            var caso = $(this).val();
            $('input[name="sales_caso_especial_hidden"]').val(caso);
            
            // Ajustar campos según el caso especial
            if (caso == '2') { // 99001 - Extranjero
                $('#imprimir_sales_tipo_documento_id').val('99001');
                $('#imprimir_sales_tipo_documento_id').selectpicker('refresh');
                $('#imprimir_sales_valor_documento').hide();
                $('#imprimir_sales_complemento').hide();
                $('input[name="sales_valor_documento"]').val('0');
            } else if (caso == '3') { // 99002 - Control Tributario
                $('#imprimir_sales_tipo_documento_id').val('99002');
                $('#imprimir_sales_tipo_documento_id').selectpicker('refresh');
                $('#imprimir_sales_valor_documento').hide();
                $('#imprimir_sales_complemento').hide();
                $('input[name="sales_valor_documento"]').val('0');
            } else if (caso == '4') { // 99003 - Ventas Menores
                $('#imprimir_sales_tipo_documento_id').val('99003');
                $('#imprimir_sales_tipo_documento_id').selectpicker('refresh');
                $('#imprimir_sales_valor_documento').hide();
                $('#imprimir_sales_complemento').hide();
                $('input[name="sales_valor_documento"]').val('0');
            }
        });

        // Validar que Razón Social no esté vacía antes de generar factura
        $(document).on('click', '#imprimir-next-btn', function(e) {
            var razonSocial = $('#imprimir_sales_razon_social').val().trim();
            if (!razonSocial || razonSocial.length === 0) {
                e.stopImmediatePropagation();
                Swal.fire({
                    icon: 'error',
                    title: 'Campo Requerido',
                    text: 'El campo "Nombre Fiscal/Razón Social" es obligatorio y no puede estar vacío.'
                });
                $('#imprimir_sales_razon_social').focus();
                return false;
            }
        });

        // Manejar envío de factura por WhatsApp desde modal
        $(document).on('click', '#imprimir-send-whatsapp-btn', function() {
            var saleId = $('#imprimir_sale_id').val();
            var phone = $('#imprimir_whatsapp_phone').val().trim();
            
            // Validar que haya un teléfono
            if (!phone || phone.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Teléfono requerido',
                    text: 'Por favor ingrese el número de WhatsApp (con código de país)'
                });
                $('#imprimir_whatsapp_phone').focus();
                return;
            }
            
            // Validar formato básico (solo números, al menos 10 dígitos)
            if (!/^\d{10,15}$/.test(phone)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Formato inválido',
                    text: 'El número debe contener solo dígitos (10-15 números) incluyendo código de país sin +'
                });
                return;
            }
            
            // Mostrar loading
            $('#imprimir-whatsapp-status').html(
                '<div class="alert alert-info"><i class="fa fa-spinner fa-spin"></i> Enviando factura por WhatsApp...</div>'
            ).show();
            
            // Deshabilitar botón
            $('#imprimir-send-whatsapp-btn').prop('disabled', true);
            
            // Enviar petición AJAX
            $.ajax({
                url: '{{ route('sales.send-invoice-whatsapp') }}',
                type: 'POST',
                data: {
                    sale_id: saleId,
                    phone: phone,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#imprimir-whatsapp-status').html(
                        '<div class="alert alert-success">' +
                        '<i class="fa fa-check-circle"></i> ' + response.message +
                        '</div>'
                    );
                    
                    Swal.fire({
                        icon: 'success',
                        title: '¡Enviado!',
                        text: response.message,
                        timer: 3000,
                        showConfirmButton: false
                    });
                    
                    // Re-habilitar botón después de 3 segundos
                    setTimeout(function() {
                        $('#imprimir-send-whatsapp-btn').prop('disabled', false);
                    }, 3000);
                },
                error: function(xhr) {
                    var errorMsg = 'Error al enviar por WhatsApp';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    
                    $('#imprimir-whatsapp-status').html(
                        '<div class="alert alert-danger">' +
                        '<i class="fa fa-exclamation-circle"></i> ' + errorMsg +
                        '</div>'
                    );
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg
                    });
                    
                    // Re-habilitar botón
                    $('#imprimir-send-whatsapp-btn').prop('disabled', false);
                }
            });
        });

        // Manejar click en ver XML de factura
        $(document).on('click', '#ver-xml-factura', function() {
            var facturaData = $('#imprimir-factura-modal').data('factura-data');
            
            if (facturaData && facturaData.xml) {
                // Formatear XML para mejor visualización
                var formattedXml = facturaData.xml
                    .replace(/></g, '>\n<')
                    .replace(/\n\s*\n/g, '\n');
                
                // Mostrar en un modal con SweetAlert
                Swal.fire({
                    title: 'XML de la Factura',
                    html: '<textarea class="form-control" rows="20" readonly style="font-family: monospace; font-size: 12px;">' + 
                          formattedXml + 
                          '</textarea>' +
                          '<div class="mt-2">' +
                          '<button type="button" class="btn btn-sm btn-primary" id="copiar-xml"><i class="fa fa-copy"></i> Copiar XML</button>' +
                          '</div>',
                    width: '80%',
                    showConfirmButton: true,
                    confirmButtonText: 'Cerrar',
                    didOpen: () => {
                        // Handler para copiar al portapapeles
                        $('#copiar-xml').on('click', function() {
                            var xmlText = facturaData.xml;
                            navigator.clipboard.writeText(xmlText).then(function() {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Copiado!',
                                    text: 'XML copiado al portapapeles',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            }).catch(function(err) {
                                console.error('Error al copiar:', err);
                                // Fallback para navegadores antiguos
                                var textarea = document.createElement('textarea');
                                textarea.value = xmlText;
                                document.body.appendChild(textarea);
                                textarea.select();
                                document.execCommand('copy');
                                document.body.removeChild(textarea);
                                
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Copiado!',
                                    text: 'XML copiado al portapapeles',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            });
                        });
                    }
                });
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'XML no disponible',
                    text: 'No se encontró el XML de la factura'
                });
            }
        });
        
        // Handler para mostrar/ocultar campo de WhatsApp en modal anular
        $(document).on('change', '#send_whatsapp', function() {
            if ($(this).is(':checked')) {
                $('#whatsapp_phone_container').slideDown();
            } else {
                $('#whatsapp_phone_container').slideUp();
            }
        });
        
        // Validación antes de enviar formulario de anulación
        $('#formDelete').on('submit', function(e) {
            if ($('#send_whatsapp').is(':checked')) {
                var phone = $('#whatsapp_phone').val();
                if (!phone || phone.trim() === '') {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Campo requerido',
                        text: 'Por favor ingrese el número de WhatsApp'
                    });
                    return false;
                }
            }
        });

    </script>
@endsection
