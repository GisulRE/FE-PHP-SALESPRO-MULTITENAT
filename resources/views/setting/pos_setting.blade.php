@extends('layout.main') @section('content')
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close"
                data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{{ session()->get('message') }}</div>
    @endif

    @if (session()->has('not_permitted'))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert"
                aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}
        </div>
    @endif
    <section class="forms">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4>{{ trans('file.POS Setting') }}</h4>
                        </div>
                        <div class="card-body">
                            <p class="italic">
                                <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                            </p>
                            {!! Form::open(['route' => 'setting.posStore', 'method' => 'post']) !!}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ trans('file.Default Customer') }} *</label>
                                        @if ($lims_pos_setting_data)
                                            <input type="hidden" name="customer_id_hidden"
                                                value="{{ $lims_pos_setting_data->customer_id }}">
                                        @endif
                                        <select required name="customer_id" id="customer_id"
                                            class="selectpicker form-control" data-live-search="true"
                                            data-live-search-style="begins" title="Select customer...">
                                            @foreach ($lims_customer_list as $customer)
                                                <option value="{{ $customer->id }}">
                                                    {{ $customer->name . ' (' . $customer->phone_number . ')' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>{{ trans('file.Default Biller') }} *</label>
                                        @if ($lims_pos_setting_data)
                                            <input type="hidden" name="biller_id_hidden"
                                                value="{{ $lims_pos_setting_data->biller_id }}">
                                        @endif
                                        <select required name="biller_id" class="selectpicker form-control"
                                            data-live-search="true" data-live-search-style="begins"
                                            title="Select Biller...">
                                            @foreach ($lims_biller_list as $biller)
                                                <option value="{{ $biller->id }}">
                                                    {{ $biller->name . ' (' . $biller->company_name . ')' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Tasa de Cambio</label>
                                        <input type="text" name="t_c" class="form-control"
                                            value="@if ($lims_pos_setting_data) {{ $lims_pos_setting_data->t_c }} @endif" />
                                    </div>
                                    <div class="form-group">
                                        <label>Impresora Pre-Orden *</label>
                                        @if ($lims_pos_setting_data)
                                            <input type="hidden" name="type_printorder_id_hidden"
                                                value="{{ $lims_pos_setting_data->print_order }}">
                                        @endif
                                        <select required name="type_printorder_id" class="selectpicker form-control"
                                            title="Seleccione Impresora...">
                                            <option value="0">Desactivado</option>
                                            @foreach ($lims_printer_list as $print)
                                                <option value="{{ $print->id }}">{{ $print->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        @if ($lims_pos_setting_data)
                                            <input class="mt-2" type="checkbox" name="customer_sucursal"
                                                value="{{ $lims_pos_setting_data->customer_sucursal }}"
                                                @if ($lims_pos_setting_data->customer_sucursal == 1) checked @endif>
                                        @endif
                                        <label class="mt-2"><strong>Filtrar Clientes por Sucursal?</strong></label>
                                    </div>
                                    <div class="form-group">
                                        @if ($lims_pos_setting_data)
                                            <input class="mt-2" type="checkbox" name="user_category"
                                                value="{{ $lims_pos_setting_data->user_category }}"
                                                @if ($lims_pos_setting_data->user_category == 1) checked @endif>
                                        @endif
                                        <label class="mt-2"><strong>Filtrar Categorias por Usuario?</strong></label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ trans('file.Default Warehouse') }} *</label>
                                        @if ($lims_pos_setting_data)
                                            <input type="hidden" name="warehouse_id_hidden"
                                                value="{{ $lims_pos_setting_data->warehouse_id }}">
                                        @endif
                                        <select required name="warehouse_id" class="selectpicker form-control"
                                            data-live-search="true" data-live-search-style="begins"
                                            title="Select warehouse...">
                                            @foreach ($lims_warehouse_list as $warehouse)
                                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Formato de Impresion *</label>
                                        @if ($lims_pos_setting_data)
                                            <input type="hidden" name="type_print_id_hidden"
                                                value="{{ $lims_pos_setting_data->type_print }}">
                                        @endif
                                        <select required name="type_print_id" class="form-control"
                                            title="Seleccione formato...">
                                            @foreach ($lims_formatprint_list as $formatprint)
                                                <option value="{{ $formatprint['id'] }}">{{ $formatprint['name'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>{{ trans('file.Displayed Number of Product Row') }} *</label>
                                        <input type="number" name="product_number" class="form-control"
                                            value="{{ $lims_pos_setting_data->product_number }}" required />
                                    </div>
                                    <div class="form-group">
                                        <label>Cantidad Decimales en POS *</label>
                                        <input type="number" name="cant_decimal" class="form-control" min="0"
                                            max="5" value="{{ $lims_pos_setting_data->cant_decimal }}" required />
                                    </div>
                                    <div class="form-group">
                                        @if ($lims_pos_setting_data && $lims_pos_setting_data->keybord_active)
                                            <input class="mt-2" type="checkbox" name="keybord_active" value="1"
                                                checked>
                                        @else
                                            <input class="mt-2" type="checkbox" name="keybord_active" value="1">
                                        @endif
                                        <label class="mt-2"><strong>{{ trans('file.Touchscreen keybord') }}
                                                Venta</label>
                                        &nbsp;&nbsp;
                                        @if ($lims_pos_setting_data && $lims_pos_setting_data->keybord_presale)
                                            <input class="mt-2" type="checkbox" name="keybord_presale" value="1"
                                                checked>
                                        @else
                                            <input class="mt-2" type="checkbox" name="keybord_presale" value="1">
                                        @endif
                                        <label class="mt-2"><strong>{{ trans('file.Touchscreen keybord') }}
                                                Pre-Venta</label>
                                    </div>
                                    <div class="form-group">
                                        @if ($lims_pos_setting_data)
                                            <input class="mt-2" type="checkbox" name="print"
                                                value="{{ $lims_pos_setting_data->print }}"
                                                @if ($lims_pos_setting_data->print == 1) checked @endif>
                                        @endif
                                        <label class="mt-2"><strong>{{ trans('file.Print Function') }}?</label>
                                        &nbsp;&nbsp;
                                        @if ($lims_pos_setting_data)
                                            <input class="mt-2" type="checkbox" name="print_presale"
                                                value="{{ $lims_pos_setting_data->print_presale }}"
                                                @if ($lims_pos_setting_data->print_presale == 1) checked @endif>
                                        @endif
                                        <label class="mt-2"><strong>{{ trans('file.Print PreSale') }}?</label>
                                    </div>

                                    <div class="form-group">
                                        @if ($lims_pos_setting_data)
                                            <input class="mt-2" type="checkbox" name="date_sell"
                                                value="{{ $lims_pos_setting_data->date_sell }}"
                                                @if ($lims_pos_setting_data->date_sell == 1) checked @endif>
                                        @endif
                                        <label class="mt-2"><strong>Mantener Fecha de Venta?</strong></label>
                                    </div>

                                    <div class="form-group">
                                        @if ($lims_pos_setting_data)
                                            <input class="mt-2" type="checkbox" name="require_transfer_authorization"
                                                value="{{ $lims_pos_setting_data->require_transfer_authorization }}"
                                                @if ($lims_pos_setting_data->require_transfer_authorization == 1) checked @endif>
                                        @endif
                                        <label class="mt-2"><strong>Requerir Autorización de
                                                transferencia?</strong></label>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <p class="italic"><small>Información SIAT.</small></p>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Facturación SIAT</label>
                                    @if ($lims_pos_setting_data)
                                        <input type="hidden" name="facturacion_id_hidden"
                                            value="{{ $lims_pos_setting_data->facturacion_id }}">
                                    @endif
                                    <select required name="facturacion_id" class="selectpicker form-control"
                                        title="Seleccione facturación...">
                                        <option value="0">Facturar Opcional</option>
                                        <option value="1">Facturar Siempre</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Tipo Emisión</label>
                                    @if ($lims_pos_setting_data)
                                        <input type="hidden" name="codigo_emision_hidden"
                                            value="{{ $lims_pos_setting_data->codigo_emision }}">
                                    @endif
                                    <select required name="codigo_emision" class="form-control"
                                        title="Seleccione emisión...">
                                        @foreach ($tipo_emision_list as $tipo_emision)
                                            <option value="{{ $tipo_emision['id'] }}">{{ $tipo_emision['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Tipo Moneda </label>
                                    <input type="hidden" name="tipo_moneda_id_hidden"
                                        value="{{ $lims_pos_setting_data->tipo_moneda_siat }}">
                                    <select name="tipo_moneda_siat" class="selectpicker form-control"
                                        title="Seleccione moneda...">
                                        @include('setting.partials-tipo_moneda')
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>NIT Emisor </label>
                                    <input type="text" class="form-control" name="nit_emisor"
                                        value="{{ $lims_pos_setting_data->nit_emisor }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Razón Social </label>
                                    <input type="text" class="form-control" name="razon_social_emisor"
                                        value="{{ $lims_pos_setting_data->razon_social_emisor }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Dirección </label>
                                    <textarea name="direccion_emisor" id="direccion_emisor" rows="2" class="form-control">{{ $lims_pos_setting_data->direccion_emisor }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" class="modal-tabla-cufd btn btn-warning " data-toggle="modal"
                                        data-target="#modal-tabla-cufd">
                                        <i class="dripicons-toggles"></i> Información de CUFD
                                    </button>
                                </div>

                            </div>
                            <div class="dropdown-divider"></div>
                            <p class="italic"><small>Credenciales SIAT.</small></p>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>User </label>
                                    <input type="text" class="form-control" name="user_siat"
                                        value="{{ $lims_pos_setting_data->user_siat }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Pass </label>
                                    <input type="text" class="form-control" name="pass_siat"
                                        value="{{ $lims_pos_setting_data->pass_siat }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Url Token</label>
                                    <input type="text" class="form-control" name="url_siat"
                                        value="{{ $lims_pos_setting_data->url_siat }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Url Siat </label>
                                    <input type="text" class="form-control" name="url_operaciones"
                                        value="{{ $lims_pos_setting_data->url_operaciones }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Url Optimo </label>
                                    <input type="text" class="form-control" name="url_optimo"
                                        value="{{ $lims_pos_setting_data->url_optimo }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Url Cobranza </label>
                                    <input type="text" class="form-control" name="url_cobranza"
                                        value="{{ $lims_pos_setting_data->url_cobranza }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Url Whatsapp</label>
                                    <input type="text" class="form-control" name="url_whatsapp"
                                        value="@if ($lims_pos_setting_data) {{ $lims_pos_setting_data->url_whatsapp }} @endif">
                                </div>
                                <div class="form-group col-md-6">
                                    @if ($lims_pos_setting_data)
                                        <input class="mt-2" type="checkbox" name="cufd_centralizado"
                                            value="{{ $lims_pos_setting_data->cufd_centralizado }}"
                                            @if ($lims_pos_setting_data->cufd_centralizado == 1) checked @endif>
                                    @endif
                                    <label class="mt-2"><strong>CUFD Centralizado</strong></label>
                                </div>

                            </div>
                            <div class="row">
                                <div class="form-group col mt-3">
                                    <input type="submit" value="{{ trans('file.submit') }}" class="btn btn-primary">
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <p class="italic"><small>Credenciales WhatsApp.</small></p>
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="button" class="modal-tabla-cufd btn btn-success " data-toggle="modal"
                                        data-target="#modal-whatsapp">
                                        <i class="dripicons-toggles"></i> Información de WhatsApp
                                    </button>
                                </div>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal WhatsApp -->
    <div id="modal-whatsapp" tabindex="-1" role="dialog" aria-labelledby="waModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header align-items-center">
                    <h5 id="waModalLabel" class="modal-title mr-2">Información WhatsApp</h5>
                    <span id="wa-status-badge" class="badge badge-secondary" style="font-size:0.85em;">Sin sesión</span>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close ml-auto"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">

                    {{-- ══════════════════════════════════════════════════ --}}
                    {{-- PANEL DE AUTENTICACIÓN (registro / login de cuenta) --}}
                    {{-- ══════════════════════════════════════════════════ --}}
                    <div id="wa-auth-panel" style="{{ $lims_pos_setting_data->whatsapp_access_token ? 'display:none' : '' }}">
                        <p class="text-muted small mb-3">
                            <i class="dripicons-information mr-1"></i>
                            Crea o inicia sesión en tu cuenta del servicio WhatsApp para gestionar instancias de tu empresa.
                        </p>

                        <ul class="nav nav-tabs mb-3" id="waAuthTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="wa-tab-login-link" data-toggle="tab"
                                    href="#wa-tab-login" role="tab">
                                    <i class="dripicons-enter mr-1"></i> Iniciar Sesión
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="wa-tab-register-link" data-toggle="tab"
                                    href="#wa-tab-register" role="tab">
                                    <i class="dripicons-user-group mr-1"></i> Registrar Cuenta
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            {{-- Tab: Login --}}
                            <div id="wa-tab-login" class="tab-pane fade show active" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Usuario</label>
                                            <input type="text" id="wa-login-username" class="form-control form-control-sm"
                                                placeholder="username" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Contraseña</label>
                                            <input type="password" id="wa-login-password" class="form-control form-control-sm"
                                                placeholder="••••••••" autocomplete="current-password">
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-primary btn-sm btn-block wa-btn-login">
                                    <i class="dripicons-enter"></i> Iniciar Sesión
                                </button>
                            </div>

                            {{-- Tab: Registro --}}
                            <div id="wa-tab-register" class="tab-pane fade" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">NIT / RUC</label>
                                            <input type="text" id="wa-reg-nit" class="form-control form-control-sm"
                                                placeholder="900123456-7">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Nombre empresa</label>
                                            <input type="text" id="wa-reg-businessName" class="form-control form-control-sm"
                                                placeholder="Mi Empresa S.A.">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Sucursal</label>
                                            <input type="text" id="wa-reg-sucursal" class="form-control form-control-sm"
                                                placeholder="Principal">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Dirección</label>
                                            <input type="text" id="wa-reg-address" class="form-control form-control-sm"
                                                placeholder="Calle 123 #45-67">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Teléfono</label>
                                            <input type="text" id="wa-reg-phone" class="form-control form-control-sm"
                                                placeholder="+591 7xxxxxxx">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Correo empresa</label>
                                            <input type="email" id="wa-reg-companyEmail" class="form-control form-control-sm"
                                                placeholder="contacto@empresa.com">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Usuario admin</label>
                                            <input type="text" id="wa-reg-username" class="form-control form-control-sm"
                                                placeholder="admin">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Nombre</label>
                                            <input type="text" id="wa-reg-firstName" class="form-control form-control-sm"
                                                placeholder="Juan">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Apellido</label>
                                            <input type="text" id="wa-reg-lastName" class="form-control form-control-sm"
                                                placeholder="Pérez">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Correo admin</label>
                                            <input type="email" id="wa-reg-email" class="form-control form-control-sm"
                                                placeholder="admin@empresa.com">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group mb-3">
                                            <label class="small font-weight-bold">Contraseña</label>
                                            <input type="password" id="wa-reg-password" class="form-control form-control-sm"
                                                placeholder="Mínimo 8 caracteres" autocomplete="new-password">
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-success btn-sm btn-block wa-btn-register">
                                    <i class="dripicons-user-group"></i> Registrar Cuenta
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- ══════════════════════════════════════════════════ --}}
                    {{-- PANEL DE SESIÓN (gestión de instancias WhatsApp) --}}
                    {{-- ══════════════════════════════════════════════════ --}}
                    <div id="wa-session-wrapper" style="{{ $lims_pos_setting_data->whatsapp_access_token ? '' : 'display:none' }}">

                        {{-- ── Vista: lista de sesiones ── --}}
                        <div id="wa-sessions-list-view">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong style="font-size:0.9em;"><i class="dripicons-list mr-1"></i> Sesiones activas</strong>
                                <button type="button" class="btn btn-link btn-sm p-0 wa-reload-sessions" title="Recargar">
                                    <i class="dripicons-clockwise"></i>
                                </button>
                            </div>

                            {{-- Tabla de sesiones --}}
                            <div id="wa-sessions-table-wrap" style="max-height:180px;overflow-y:auto;">
                                <table class="table table-sm table-hover mb-0" style="font-size:0.82em;">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Sesión</th>
                                            <th class="text-center">Estado</th>
                                            <th class="text-center">Principal</th>
                                            <th class="text-center">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody id="wa-sessions-tbody">
                                        <tr><td colspan="3" class="text-center text-muted py-2">Cargando…</td></tr>
                                    </tbody>
                                </table>
                            </div>

                            <hr class="my-3">

                            {{-- Nueva sesión --}}
                            <div class="form-group mb-2">
                                <label class="font-weight-bold mb-1" style="font-size:0.88em;">
                                    <i class="dripicons-plus mr-1"></i> Nueva sesión
                                </label>
                                <div class="input-group input-group-sm">
                                    <input type="text" id="wa-new-session-name" class="form-control"
                                        placeholder="Ej: sucursal-1"
                                        pattern="[a-zA-Z0-9_\-]+" maxlength="64">
                                    <div class="input-group-append">
                                        <button type="button" id="wa-btn-start-new" class="btn btn-success btn-sm">
                                            <i class="dripicons-power"></i> Iniciar
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">Solo letras, números, <code>-</code> y <code>_</code>.</small>
                            </div>
                        </div>{{-- /wa-sessions-list-view --}}

                        {{-- ── Vista: detalle de una sesión ── --}}
                        <div id="wa-session-detail-view" style="display:none;">
                            <div class="d-flex align-items-center mb-2">
                                <button type="button" id="wa-btn-back-list" class="btn btn-link btn-sm p-0 mr-2">
                                    <i class="dripicons-arrow-thin-left"></i> Volver
                                </button>
                                <strong id="wa-detail-session-name" style="font-size:0.9em;"></strong>
                            </div>

                            {{-- Botones de acción --}}
                            <div class="row mb-2">
                                <div class="col-6 col-md-3 mb-2">
                                    <button type="button" class="obtener-qr btn btn-info btn-sm btn-block">
                                        <i class="dripicons-camera"></i> Obtener QR
                                    </button>
                                </div>
                                <div class="col-6 col-md-3 mb-2">
                                    <button type="button" class="logout-whatsapp btn btn-warning btn-sm btn-block">
                                        <i class="dripicons-exit"></i> Cerrar Sesión
                                    </button>
                                </div>
                                <div class="col-6 col-md-3 mb-2">
                                    <button type="button" class="eliminar-session-whatsapp btn btn-danger btn-sm btn-block">
                                        <i class="dripicons-trash"></i> Eliminar
                                    </button>
                                </div>
                                <div class="col-6 col-md-3 mb-2">
                                    <button type="button" class="wa-btn-set-primary btn btn-outline-primary btn-sm btn-block">
                                        <i class="dripicons-star"></i> Principal
                                    </button>
                                </div>
                            </div>

                            {{-- Panel de estado --}}
                            <div id="wa-status-panel" class="alert alert-secondary d-flex align-items-center mb-0 py-2" style="display:none !important; font-size:0.88em;">
                                <span class="mr-2" style="font-size:1.1em;">&#9679;</span>
                                <strong class="mr-1">Estado:</strong>
                                <span id="whatsappStatus" class="text-muted">—</span>
                            </div>

                            {{-- QR panel --}}
                            <div id="results" class="mt-3" style="display: none;">
                                <p class="text-center small text-muted mb-1">Escanee el código QR para vincular WhatsApp.</p>
                                <p class="text-center small text-muted mb-2">El código QR tiene límite de tiempo. Use <strong>Obtener QR</strong> para actualizarlo.</p>
                                <div class="text-center">
                                    <img id="resultImage" src="" class="img-fluid rounded border" alt="QR code" style="max-width:240px;">
                                </div>
                            </div>

                            {{-- Panel API Key (Session Token) --}}
                            <div class="mt-3 border rounded p-2" style="background:#f8f9fa;">
                                <p class="mb-1" style="font-size:0.85em;font-weight:600;">
                                    <i class="dripicons-lock"></i> API Key (Session Token)
                                </p>
                                <p class="text-muted mb-2" style="font-size:0.78em;">
                                    Genera un token JWT para enviar mensajes de WhatsApp desde integraciones externas.
                                    Úsalo como cabecera <code>Authorization: Bearer &lt;token&gt;</code>.
                                </p>
                                <div class="d-flex mb-2">
                                    <button type="button" id="wa-btn-gen-token" class="btn btn-success btn-sm mr-2">
                                        <i class="dripicons-plus"></i> Generar API Key
                                    </button>
                                    <button type="button" id="wa-btn-revoke-token" class="btn btn-outline-danger btn-sm">
                                        <i class="dripicons-minus"></i> Revocar
                                    </button>
                                </div>
                                <div id="wa-token-result" style="display:none;">
                                    <div class="input-group input-group-sm">
                                        <textarea id="wa-token-value" class="form-control form-control-sm font-monospace"
                                               readonly rows="4" style="font-size:0.72em;background:#fff;resize:none;"></textarea>
                                        <div class="input-group-append">
                                            <button type="button" id="wa-btn-copy-token" class="btn btn-outline-secondary btn-sm">
                                                <i class="dripicons-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <p class="text-muted mt-1 mb-0" style="font-size:0.72em;">
                                        Guarda este token en un lugar seguro. No se mostrará de nuevo hasta que generes uno nuevo.
                                    </p>
                                </div>
                            </div>
                        </div>{{-- /wa-session-detail-view --}}

                        {{-- Desconectar cuenta --}}
                        <div class="text-right mt-3">
                            <button type="button" class="btn btn-link btn-sm text-danger p-0 wa-btn-auth-logout">
                                <i class="dripicons-exit"></i> Desconectar cuenta del servicio
                            </button>
                        </div>

                    </div>{{-- /wa-session-wrapper --}}

                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm"
                        data-dismiss="modal">{{ __('file.Close') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="modal-tabla-cufd" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">Información CUFD</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <p class="italic">
                        <small>
                            Este proceso consiste en renovar los CUFD de los Puntos de Venta.*
                        </small>
                    </p>
                    <div class="row justify-content-center">
                        {{-- <a href="{{route('')}}" type="button" class="btn btn-secondary">botón Renovar CUFD</a> --}}
                        <div class="col">
                            <button type="button" class="forzar-renovar-cufd btn btn-warning">Renovar CUFD
                                (Todos)</button>
                        </div>
                        <div class="col">
                            <button type="button" class="tarea-programada-cufd btn btn-warning">Activar Tarea
                                Programada</button>
                        </div>
                    </div>
                    {{-- Insertando datos en forma de tabla --}}
                    <div id="tabla_modal" class="table-responsive">
                        @include('layout.partials.spinner-ajax')
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nombre Punto Venta </th>
                                    <th>Sucursal</th>
                                    <th>Punto de Venta </th>
                                    <th>Estado Contingencia </th>
                                    <th>Fecha Vigencia CUFD</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Llenado por ajax jquery --}}
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">{{ __('file.Close') }}</button>
                    </div>
                </div>


            </div>
        </div>
    </div>

    <script type="text/javascript">
        $("ul#setting").siblings('a').attr('aria-expanded', 'true');
        $("ul#setting").addClass("show");
        $("ul#setting #pos-setting-menu").addClass("active");


        $('select[name="type_print_id"]').val($("input[name='type_print_id_hidden']").val());
        $('select[name="type_printorder_id"]').val($("input[name='type_printorder_id_hidden']").val());
        $('select[name="customer_id"]').val($("input[name='customer_id_hidden']").val());
        $('select[name="biller_id"]').val($("input[name='biller_id_hidden']").val());
        $('select[name="warehouse_id"]').val($("input[name='warehouse_id_hidden']").val());
        $('select[name="facturacion_id"]').val($("input[name='facturacion_id_hidden']").val());
        $('select[name="codigo_emision"]').val($("input[name='codigo_emision_hidden']").val());
        $('select[name="tipo_moneda_siat"]').val($("input[name='tipo_moneda_id_hidden']").val());
        $('.selectpicker').selectpicker('refresh');


        // Modal para mostrar el registro de los puntos de venta con CUIS
        $(document).on("click", ".modal-tabla-cufd", function(event) {
            // obtener y rellenar tabla en el modal de información
            var url = '{{ route('setting.lista_puntos_venta') }}';
            $.ajax({
                url: url,
                type: "GET",
                success: function(data) {
                    $("#tabla_modal").find("tr:gt(0)").remove();
                    $.each(data, function(key, value) {
                        console.log("la data es => " + data);
                        var estado = value['modo_contingencia'];
                        if (estado == true) {
                            textHtmlEstadoContingencia = '<td> Activado </td>';
                        } else {
                            textHtmlEstadoContingencia = '<td> Desactivado </td>';
                        }
                        var codigo = value['id'];
                        var htmlTags = '<tr>' +
                            '<td>' + value['nombre_punto_venta'] + '</td>' +
                            '<td>' + value['sucursal'] + '</td>' +
                            '<td>' + value['codigo_punto_venta'] + '</td>' +
                            textHtmlEstadoContingencia +
                            '<td>' + value['fecha_vencimiento'] + '</td>';
                        if (value['is_siat'] == true && value['is_active'] == true)
                            htmlTags +=
                            '<td> <button type="button" class="btn btn-warning" onclick="renovarPuntoV(' +
                            codigo +
                            ')" title="Renovar CUFD"> <i class = "fa fa-refresh" > </i></button > </td>';

                        htmlTags += '</tr>';

                        $('#tabla_modal tbody').append(htmlTags);
                    });
                }
            });
            $('#enviar-evento-modal').modal('show');
        });

        function refrescarDatosTablaCUFD() {
            var url = '{{ route('setting.lista_puntos_venta') }}';
            $.ajax({
                url: url,
                type: "GET",
                success: function(data) {
                    $("#tabla_modal").find("tr:gt(0)").remove();
                    $.each(data, function(key, value) {
                        var estado = value['modo_contingencia'];
                        if (estado == true) {
                            textHtmlEstadoContingencia = '<td> Activado </td>';
                        } else {
                            textHtmlEstadoContingencia = '<td> Desactivado </td>';
                        }
                        var codigo = value['id'];
                        var htmlTags = '<tr>' +
                            '<td>' + value['nombre_punto_venta'] + '</td>' +
                            '<td>' + value['sucursal'] + '</td>' +
                            '<td>' + value['codigo_punto_venta'] + '</td>' +
                            textHtmlEstadoContingencia +
                            '<td>' + value['fecha_vencimiento'] + '</td>';
                        if (value['is_siat'] == true && value['is_active'] == true)
                            htmlTags +=
                            '<td> <button type="button" class="btn btn-warning" onclick="renovarPuntoV(' +
                            codigo +
                            ')" title="Renovar CUFD"> <i class = "fa fa-refresh" > </i></button > </td>';

                        htmlTags += '</tr>';
                        $('#tabla_modal tbody').append(htmlTags);
                    });
                }
            });
        }

        // Botón ajax para forzar la renovación de los cufd.
        $(document).on("click", ".forzar-renovar-cufd", function(event) {
            var url_data = "{{ route('forzar_renovar_cufd') }}";

            $("#spinner-div").show(); //Mostrar icon spinner de cargando
            $.ajax({
                url: url_data,
                type: "GET",
                success: function(data) {
                    if (data == true) {
                        swal('Renovación Exitosa', 'Cufd renovados para todos los puntos de venta!');
                        refrescarDatosTablaCUFD();
                    } else {
                        swal('Error', 'no se logró renovar los cufd');
                    }
                },
                complete: function() {
                    $("#spinner-div").hide(); //Ocultar icon spinner de cargando
                },
                error: function() {
                    swal('Error', 'error en el servicio');
                },
            });
        });

        // ─── WhatsApp: URLs de los endpoints ────────────────────────────────────
        var waSessionsLocalBase = "{{ url('/api/whatsapp/sessions') }}";
        var waUrls = {
            authRegister:   "{{ route('whatsapp.auth.register', [], false) }}",
            authLogin:      "{{ route('whatsapp.auth.login', [], false) }}",
            authLogout:     "{{ route('whatsapp.auth.logout', [], false) }}",
            authRefresh:    "{{ route('whatsapp.auth.refresh', [], false) }}",
            start:          "{{ route('whatsapp.session.start', [], false) }}",
            qr:             "{{ route('whatsapp.session.qr', [], false) }}",
            status:         "{{ route('whatsapp.session.status', [], false) }}",
            logout:         "{{ route('whatsapp.session.logout', [], false) }}",
            deleteSession:  "{{ route('whatsapp.session.delete', [], false) }}",
            listSessions:   "{{ route('whatsapp.sessions.list', [], false) }}",
            activateSess:   "{{ route('whatsapp.sessions.activate', [], false) }}",
        };

        // ─── Estado multi-sesión ─────────────────────────────────────────────
        var waSelectedSession = null;

        // ─── Timers de polling ────────────────────────────────────────────────
        var whatsappTimers = { qr: null, status: null };

        function stopWhatsAppPolling() {
            if (whatsappTimers.qr)     { clearInterval(whatsappTimers.qr);     whatsappTimers.qr = null; }
            if (whatsappTimers.status) { clearInterval(whatsappTimers.status); whatsappTimers.status = null; }
        }

        function startWhatsAppPolling() {
            if (whatsappTimers.status) return; // ya corriendo
            downloadStatus();
            whatsappTimers.status = setInterval(downloadStatus, 5000);
            whatsappTimers.qr = setInterval(function() {
                if (document.getElementById('results').style.display === 'block') {
                    downloadQR(true);
                }
            }, 60000);
        }

        // ─── Lista de sesiones ────────────────────────────────────────────────
        function loadWaSessions() {
            fetch(waUrls.listSessions, { method: 'GET', headers: { 'Content-Type': 'application/json' } })
            .then(async function(r) {
                var data = null;
                try { data = await r.json(); } catch(e) {}
                if (data && data.sessions) renderWaSessions(data.sessions);
                else document.getElementById('wa-sessions-tbody').innerHTML = '<tr><td colspan="3" class="text-center text-muted py-2">Sin sesiones registradas.</td></tr>';
            })
            .catch(function() {
                document.getElementById('wa-sessions-tbody').innerHTML = '<tr><td colspan="3" class="text-center text-danger py-2">Error al cargar sesiones.</td></tr>';
            });
        }

        function renderWaSessions(sessions) {
            var tbody = document.getElementById('wa-sessions-tbody');
            if (!sessions || sessions.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-2">Sin sesiones. Crea una nueva abajo.</td></tr>';
                return;
            }
            var statusMap = {
                'connected': '<span class="badge badge-success">connected</span>',
                'authenticated': '<span class="badge badge-success">authenticated</span>',
                'connecting': '<span class="badge badge-warning">connecting</span>',
                'starting': '<span class="badge badge-info">starting</span>',
            };
            var html = '';
            sessions.forEach(function(s) {
                var upStatus = s.upstream_status || null;
                var statusBadge = upStatus
                    ? (statusMap[upStatus.toLowerCase()] || '<span class="badge badge-secondary">' + upStatus + '</span>')
                    : '<span class="badge badge-light text-muted">—</span>';
                var primaryBadge = s.is_active
                    ? '<span class="badge badge-success"><i class="dripicons-star"></i> Principal</span>'
                    : '<button type="button" class="btn btn-outline-secondary btn-xs wa-set-primary-btn" data-name="' + s.session_name + '" style="font-size:0.75em;padding:1px 5px;">Activar</button>';
                html += '<tr data-id="' + s.id + '" data-name="' + s.session_name + '">' +
                    '<td><a href="#" class="wa-open-session-link" data-name="' + s.session_name + '">' + s.session_name + '</a></td>' +
                    '<td class="text-center">' + statusBadge + '</td>' +
                    '<td class="text-center">' + primaryBadge + '</td>' +
                    '<td class="text-center" style="white-space:nowrap;">' +
                        '<button type="button" class="btn btn-link btn-sm p-0 text-primary wa-open-session-btn mr-2" data-name="' + s.session_name + '" title="Gestionar"><i class="dripicons-settings"></i></button>' +
                        '<button type="button" class="btn btn-link btn-sm p-0 text-danger wa-delete-local-btn" data-id="' + s.id + '" data-name="' + s.session_name + '" title="Eliminar de BD"><i class="dripicons-trash"></i></button>' +
                    '</td>' +
                '</tr>';
            });
            tbody.innerHTML = html;
        }

        function openWaSession(name) {
            waSelectedSession = name;
            document.getElementById('wa-detail-session-name').textContent = name;
            document.getElementById('wa-sessions-list-view').style.display = 'none';
            document.getElementById('wa-session-detail-view').style.display = '';
            document.getElementById('results').style.display = 'none';
            document.getElementById('wa-token-result').style.display = 'none';
            document.getElementById('wa-token-value').value = '';
            setWhatsAppStatusText('', null);
            stopWhatsAppPolling();
            startWhatsAppPolling();
        }

        function backToSessionList() {
            stopWhatsAppPolling();
            setWhatsAppStatusText('', null);
            document.getElementById('results').style.display = 'none';
            document.getElementById('wa-token-result').style.display = 'none';
            document.getElementById('wa-token-value').value = '';
            waSelectedSession = null;
            document.getElementById('wa-session-detail-view').style.display = 'none';
            document.getElementById('wa-sessions-list-view').style.display = '';
            loadWaSessions();
        }

        // ─── Handlers de lista ────────────────────────────────────────────────
        $(document).on('click', '.wa-open-session-link, .wa-open-session-btn', function(e) {
            e.preventDefault();
            openWaSession($(this).data('name'));
        });

        $(document).on('click', '.wa-set-primary-btn', function() {
            var name = $(this).data('name');
            $("#spinner-div").show();
            fetch(waUrls.activateSess, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                body: JSON.stringify({ session_name: name })
            })
            .then(async function(r) {
                var data = null;
                try { data = await r.json(); } catch(e) {}
                swal('WhatsApp', (data && data.message) ? data.message : 'Sesión principal actualizada.');
                loadWaSessions();
            })
            .catch(function() { swal('Error', 'No se pudo activar la sesión.'); })
            .finally(function() { $("#spinner-div").hide(); });
        });

        $(document).on('click', '.wa-reload-sessions', function() { loadWaSessions(); });

        // ─── Eliminar sesión de BD local ──────────────────────────────────────
        $(document).on('click', '.wa-delete-local-btn', function(e) {
            e.stopPropagation();
            var id   = $(this).data('id');
            var name = $(this).data('name');
            swal({
                title: '¿Eliminar registro?',
                text: 'Se eliminará "' + name + '" del registro local (BD). La sesión en el servicio NO se eliminará.',
                icon: 'warning',
                buttons: {
                    cancel:  { text: 'Cancelar', value: null, visible: true },
                    confirm: { text: 'Sí, eliminar', value: true, visible: true, className: 'btn-danger' },
                },
                dangerMode: true,
            }).then(function(confirmed) {
                if (!confirmed) return;
                $("#spinner-div").show();
                fetch(waSessionsLocalBase + '/' + id + '/local', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': $("meta[name='csrf-token']").attr('content') },
                })
                .then(async function(r) {
                    var data = null;
                    try { data = await r.json(); } catch(e) {}
                    if (!r.ok) throw new Error((data && data.message) ? data.message : 'Error al eliminar.');
                    $('tr[data-id="' + id + '"]').fadeOut(300, function() { $(this).remove(); });
                    swal('WhatsApp', data.message || 'Registro eliminado.');
                })
                .catch(function(err) { swal('Error', err.message); })
                .finally(function() { $("#spinner-div").hide(); });
            });
        });

        $(document).on('click', '#wa-btn-back-list', function() { backToSessionList(); });

        // ─── Marcar como principal desde detalle ──────────────────────────────
        $(document).on('click', '.wa-btn-set-primary', function() {
            if (!waSelectedSession) return;
            $("#spinner-div").show();
            fetch(waUrls.activateSess, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                body: JSON.stringify({ session_name: waSelectedSession })
            })
            .then(async function(r) {
                var data = null;
                try { data = await r.json(); } catch(e) {}
                swal('WhatsApp', (data && data.message) ? data.message : 'Sesión marcada como principal.');
            })
            .catch(function() { swal('Error', 'No se pudo activar la sesión.'); })
            .finally(function() { $("#spinner-div").hide(); });
        });

        // ─── Generar API Key (session token) ─────────────────────────────────
        $(document).on('click', '#wa-btn-gen-token', function() {
            if (!waSelectedSession) return;
            $("#spinner-div").show();
            var url = waSessionsLocalBase.replace(/\/$/, '') + '/../sessions/' + encodeURIComponent(waSelectedSession) + '/token';
            // Construimos la URL limpiamente
            url = '/api/whatsapp/sessions/' + encodeURIComponent(waSelectedSession) + '/token';
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            })
            .then(async function(r) {
                var data = null;
                try { data = await r.json(); } catch(e) {}
                if (!r.ok) {
                    var errMsg = (data && (data.message || data.error)) ? (data.message || data.error) : ('Error HTTP ' + r.status);
                    throw new Error(errMsg);
                }
                // Mostrar el JSON completo tal como lo retorna el servidor
                $('#wa-token-value').val(JSON.stringify(data, null, 2));
                $('#wa-token-result').show();
            })
            .catch(function(err) { swal('API Key', 'Error: ' + err.message); })
            .finally(function() { $("#spinner-div").hide(); });
        });

        // ─── Revocar API Key (session token) ─────────────────────────────────
        $(document).on('click', '#wa-btn-revoke-token', function() {
            if (!waSelectedSession) return;
            swal({
                title: '¿Revocar API Key?',
                text: 'El token actual dejará de funcionar inmediatamente.',
                icon: 'warning',
                buttons: ['Cancelar', 'Revocar'],
                dangerMode: true,
            }).then(function(confirmed) {
                if (!confirmed) return;
                $("#spinner-div").show();
                var url = '/api/whatsapp/sessions/' + encodeURIComponent(waSelectedSession) + '/token';
                fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                })
                .then(async function(r) {
                    var data = null;
                    try { data = await r.json(); } catch(e) {}
                    $('#wa-token-result').hide();
                    $('#wa-token-value').val('');
                    swal('API Key', (data && data.message) ? data.message : 'Token revocado correctamente.');
                })
                .catch(function() { swal('Error', 'No se pudo revocar el token.'); })
                .finally(function() { $("#spinner-div").hide(); });
            });
        });

        // ─── Copiar API Key al portapapeles ───────────────────────────────────
        $(document).on('click', '#wa-btn-copy-token', function() {
            var val = $('#wa-token-value').val();
            if (!val) return;
            if (navigator.clipboard) {
                navigator.clipboard.writeText(val).then(function() {
                    swal('Copiado', 'Token copiado al portapapeles.', 'success');
                });
            } else {
                var el = document.getElementById('wa-token-value');
                el.select();
                document.execCommand('copy');
                swal('Copiado', 'Token copiado al portapapeles.', 'success');
            }
        });

        // ─── Iniciar nueva sesión ─────────────────────────────────────────────
        $(document).on('click', '#wa-btn-start-new', function() {
            var name = $.trim($('#wa-new-session-name').val());
            if (name && !/^[a-zA-Z0-9_\-]+$/.test(name)) {
                swal('Nueva sesión', 'Solo letras, números, guiones (-) y guiones bajos (_).');
                return;
            }
            $("#spinner-div").show();
            var url = waUrls.start + (name ? '?session_name=' + encodeURIComponent(name) : '');
            fetch(url, { method: 'GET', headers: { 'Content-Type': 'application/json' } })
            .then(async function(response) {
                var data = null;
                try { data = await response.json(); } catch(e) {}
                if (!response.ok) {
                    var msg = (data && (data.message || data.error)) ? (data.message || data.error) : ('Error (HTTP ' + response.status + ')');
                    throw new Error(msg);
                }
                var sessionId = (data && data.sessionId) ? data.sessionId : (name || '');
                $('#wa-new-session-name').val('');
                openWaSession(sessionId);
                return downloadQR();
            })
            .catch(function(err) { swal('Iniciar sesión', err.message); })
            .finally(function() { $("#spinner-div").hide(); });
        });

        // ─── Actualizar badge e indicador de estado ───────────────────────────
        function setWhatsAppStatusText(text, isOk) {
            var el  = document.getElementById('whatsappStatus');
            var badge = document.getElementById('wa-status-badge');
            var panel = document.getElementById('wa-status-panel');

            if (el) {
                el.textContent = text || '—';
                el.className = '';
                if (isOk === true)  el.classList.add('text-success');
                else if (isOk === false) el.classList.add('text-danger');
                else el.classList.add('text-muted');
            }

            // Mostrar/ocultar panel de estado
            if (panel) panel.style.display = (text && text !== '—') ? 'flex' : 'none';

            if (badge) {
                badge.textContent = text || 'Sin sesión';
                badge.className = 'badge';
                if (isOk === true)       badge.classList.add('badge-success');
                else if (isOk === false) badge.classList.add('badge-danger');
                else                     badge.classList.add('badge-warning');
            }
        }

        function isWhatsAppAuthenticated(status) {
            if (!status) return false;
            var s = String(status).toLowerCase();
            return (s === 'authenticated' || s === 'connected' || s === 'open' || s === 'ready');
        }

        // ─── Mostrar/ocultar paneles de auth vs sesión ───────────────────────
        function showWaAuthPanel() {
            document.getElementById('wa-auth-panel').style.display = '';
            document.getElementById('wa-session-wrapper').style.display = 'none';
            setWhatsAppStatusText('', null);
            stopWhatsAppPolling();
        }

        function showWaSessionPanel() {
            document.getElementById('wa-auth-panel').style.display = 'none';
            document.getElementById('wa-session-wrapper').style.display = '';
            // Mostrar lista de sesiones al entrar al panel
            document.getElementById('wa-sessions-list-view').style.display = '';
            document.getElementById('wa-session-detail-view').style.display = 'none';
            loadWaSessions();
        }

        // ─── Login de cuenta ──────────────────────────────────────────────────
        $(document).on('click', '.wa-btn-login', function() {
            var username = $.trim($('#wa-login-username').val());
            var password = $('#wa-login-password').val();
            if (!username || !password) { swal('Iniciar Sesión', 'Ingresa usuario y contraseña.'); return; }
            $("#spinner-div").show();
            fetch(waUrls.authLogin, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                body: JSON.stringify({ username: username, password: password })
            })
            .then(async function(response) {
                var data = null;
                try { data = await response.json(); } catch(e) {}
                if (!response.ok) {
                    var msg = (data && data.message) ? data.message : ('Error al iniciar sesión (HTTP ' + response.status + ')');
                    throw new Error(msg);
                }
                swal('WhatsApp', data.message || 'Sesión de cuenta iniciada.');
                showWaSessionPanel();
                startWhatsAppPolling();
            })
            .catch(function(err) { swal('Error', err.message); })
            .finally(function() { $("#spinner-div").hide(); });
        });

        // ─── Registro de cuenta ───────────────────────────────────────────────
        $(document).on('click', '.wa-btn-register', function() {
            var payload = {
                nit:          $.trim($('#wa-reg-nit').val()),
                businessName: $.trim($('#wa-reg-businessName').val()),
                sucursal:     $.trim($('#wa-reg-sucursal').val()),
                address:      $.trim($('#wa-reg-address').val()),
                phone:        $.trim($('#wa-reg-phone').val()),
                companyEmail: $.trim($('#wa-reg-companyEmail').val()),
                username:     $.trim($('#wa-reg-username').val()),
                email:        $.trim($('#wa-reg-email').val()),
                password:     $('#wa-reg-password').val(),
                firstName:    $.trim($('#wa-reg-firstName').val()),
                lastName:     $.trim($('#wa-reg-lastName').val()),
            };
            for (var key in payload) {
                if (!payload[key]) {
                    swal('Registrar Cuenta', 'Completa todos los campos del formulario.');
                    return;
                }
            }
            if (payload.password.length < 8) {
                swal('Registrar Cuenta', 'La contraseña debe tener al menos 8 caracteres.');
                return;
            }
            $("#spinner-div").show();
            fetch(waUrls.authRegister, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                body: JSON.stringify(payload)
            })
            .then(async function(response) {
                var data = null;
                try { data = await response.json(); } catch(e) {}
                if (!response.ok) {
                    var msg = (data && data.message) ? data.message : ('Error al registrar (HTTP ' + response.status + ')');
                    throw new Error(msg);
                }
                swal('WhatsApp', data.message || 'Cuenta registrada exitosamente.');
                showWaSessionPanel();
                startWhatsAppPolling();
            })
            .catch(function(err) { swal('Error', err.message); })
            .finally(function() { $("#spinner-div").hide(); });
        });

        // ─── Desconectar cuenta del servicio ─────────────────────────────────
        $(document).on('click', '.wa-btn-auth-logout', function() {
            swal({
                title: '¿Desconectar cuenta?',
                text: 'Se eliminarán los tokens de acceso guardados. Tendrás que volver a iniciar sesión.',
                icon: 'warning',
                buttons: { cancel: { text: 'Cancelar', value: null, visible: true }, confirm: { text: 'Sí, desconectar', value: true, visible: true, className: 'btn-danger' } },
                dangerMode: true,
            }).then(function(confirmed) {
                if (!confirmed) return;
                $("#spinner-div").show();
                fetch(waUrls.authLogout, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                })
                .then(async function(response) {
                    var data = null;
                    try { data = await response.json(); } catch(e) {}
                    if (!response.ok) throw new Error((data && data.message) ? data.message : 'Error');
                    document.getElementById('results').style.display = 'none';
                    showWaAuthPanel();
                    swal('WhatsApp', data.message || 'Cuenta desconectada.');
                })
                .catch(function(err) { swal('Error', err.message); })
                .finally(function() { $("#spinner-div").hide(); });
            });
        });

        // ─── Abrir modal → cargar lista si hay cuenta autenticada ────────────
        $('#modal-whatsapp').on('show.bs.modal', function() {
            var hasAccount = document.getElementById('wa-session-wrapper').style.display !== 'none';
            if (hasAccount) {
                // Asegurar que empieza en la vista de lista
                document.getElementById('wa-sessions-list-view').style.display = '';
                document.getElementById('wa-session-detail-view').style.display = 'none';
                loadWaSessions();
            }
        });

        $('#modal-whatsapp').on('hidden.bs.modal', function() {
            stopWhatsAppPolling();
            setWhatsAppStatusText('', null);
            document.getElementById('results').style.display = 'none';
        });

        // ─── Obtener QR (desde detalle de sesión) ────────────────────────────
        $(document).on("click", ".obtener-qr", function() {
            startWhatsAppPolling();
            downloadQR();
        });

        // ─── Cerrar Sesión (logout) ───────────────────────────────────────────
        $(document).on("click", ".logout-whatsapp", function() {
            if (!waSelectedSession) { swal('WhatsApp', 'Selecciona primero una sesión.'); return; }
            swal({
                title: '¿Cerrar sesión?',
                text: 'Se cerrará la sesión de WhatsApp en el servicio. Necesitará escanear el QR nuevamente.',
                icon: 'warning',
                buttons: {
                    cancel: { text: 'Cancelar', value: null, visible: true },
                    confirm: { text: 'Sí, cerrar sesión', value: true, visible: true, className: 'btn-warning' },
                },
                dangerMode: false,
            }).then(function(confirmed) {
                if (!confirmed) return;
                $("#spinner-div").show();
                fetch(waUrls.logout + '?session_name=' + encodeURIComponent(waSelectedSession), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                })
                .then(async function(response) {
                    var data = null;
                    try { data = await response.json(); } catch(e) {}
                    if (!response.ok) {
                        var msg = (data && data.message) ? data.message : ('Error (HTTP ' + response.status + ')');
                        throw new Error(msg);
                    }
                    var msg = (data && data.data && data.data.message) ? data.data.message : 'Sesión cerrada.';
                    swal('WhatsApp', msg);
                    document.getElementById('results').style.display = 'none';
                    setWhatsAppStatusText('Sesión cerrada', null);
                    stopWhatsAppPolling();
                })
                .catch(function(err) { swal('Error', err.message); })
                .finally(function() { $("#spinner-div").hide(); });
            });
        });

        // ─── Eliminar Sesión ──────────────────────────────────────────────────
        $(document).on("click", ".eliminar-session-whatsapp", function() {
            if (!waSelectedSession) { swal('WhatsApp', 'Selecciona primero una sesión.'); return; }
            swal({
                title: '¿Eliminar sesión?',
                text: 'Se eliminará la sesión "' + waSelectedSession + '" permanentemente del servicio.',
                icon: 'warning',
                buttons: {
                    cancel: { text: 'Cancelar', value: null, visible: true },
                    confirm: { text: 'Sí, eliminar', value: true, visible: true, className: 'btn-danger' },
                },
                dangerMode: true,
            }).then(function(confirmed) {
                if (!confirmed) return;
                $("#spinner-div").show();
                fetch(waUrls.deleteSession + '?session_name=' + encodeURIComponent(waSelectedSession), {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                })
                .then(async function(response) {
                    var data = null;
                    try { data = await response.json(); } catch(e) {}
                    if (!response.ok) {
                        var msg = (data && data.message) ? data.message : ('Error (HTTP ' + response.status + ')');
                        throw new Error(msg);
                    }
                    var msg = (data && data.data && data.data.message) ? data.data.message : 'Sesión eliminada.';
                    swal('WhatsApp', msg);
                    backToSessionList();
                })
                .catch(function(err) { swal('Error', err.message); })
                .finally(function() { $("#spinner-div").hide(); });
            });
        });

        // ─── Descargar QR ─────────────────────────────────────────────────────
        function downloadQR(silent) {
            if (!silent) $("#spinner-div").show();
            var qrUrl = waUrls.qr + (waSelectedSession ? '?session_name=' + encodeURIComponent(waSelectedSession) : '');
            return fetch(qrUrl, { method: 'GET', headers: { 'Content-Type': 'application/json' } })
            .then(function(response) {
                if (response.status === 404) throw new Error("WhatsApp está generando el QR, en un momento estará disponible con la opción: Obtener QR.");
                if (response.status === 400) throw new Error("Servicio WhatsApp requiere la opción: Iniciar Servicio.");
                if (!response.ok) throw new Error("Error obteniendo QR (HTTP " + response.status + ")");
                return response.blob();
            })
            .then(function(imageBlob) {
                if (imageBlob) showResults(URL.createObjectURL(imageBlob));
            })
            .catch(function(err) {
                document.getElementById('results').style.display = 'none';
                if (!silent) swal('Servicio de WhatsApp', err.message);
            })
            .finally(function() { if (!silent) $("#spinner-div").hide(); });
        }

        // ─── Consultar estado en el upstream ─────────────────────────────────
        function downloadStatus() {
            var statusUrl = waUrls.status + (waSelectedSession ? '?session_name=' + encodeURIComponent(waSelectedSession) : '');
            fetch(statusUrl, { method: 'GET', headers: { 'Content-Type': 'application/json' } })
            .then(async function(response) {
                var data = null;
                try { data = await response.json(); } catch(e) {}
                if (!response.ok) {
                    var msg = (data && (data.message || data.error)) ? (data.message || data.error) : ('Error status (HTTP ' + response.status + ')');
                    throw new Error(msg);
                }
                var status = data ? (data.status || (data.upstreamBody && data.upstreamBody.data && data.upstreamBody.data.status)) : null;
                if (status) {
                    var authenticated = isWhatsAppAuthenticated(status);
                    setWhatsAppStatusText(status + (authenticated ? ' ✓' : ''), authenticated ? true : null);
                    if (authenticated) stopWhatsAppPolling();
                } else {
                    setWhatsAppStatusText('(sin datos)', null);
                }
            })
            .catch(function(err) { setWhatsAppStatusText(err.message, false); });
        }

        function showResults(objectURL) {
            document.getElementById('resultImage').src = objectURL;
            document.getElementById('results').style.display = 'block';
        }

        function renovarPuntoV(codigo) {
            var url_data = "{{ route('setting.renovar_puntoventa', ':id') }}";
            url_data = url_data.replace(':id', codigo);

            $("#spinner-div").show(); //Mostrar icon spinner de cargando
            $.ajax({
                url: url_data,
                type: "GET",
                success: function(data) {
                    if (data == true) {
                        swal('Renovación Exitosa', 'Cufd renovado para el punto de venta: ' + codigo);
                        refrescarDatosTablaCUFD();
                    } else {
                        swal('Error', 'no se logró renovar los cufd');
                    }
                },
                complete: function() {
                    $("#spinner-div").hide(); //Ocultar icon spinner de cargando
                },
                error: function() {
                    swal('Error', 'error en el servicio');
                },
            });
        }

        // Botón ajax activar la tarea programada del kernel.
        $(document).on("click", ".tarea-programada-cufd", function(event) {
            var url_data = "{{ route('run_tarea_programada') }}";

            $("#spinner-div").show(); //Mostrar icon spinner de cargando
            $.ajax({
                url: url_data,
                type: "GET",
                success: function(data) {
                    if (data == true) {
                        swal('Renovación Exitosa', 'tarea programada realizada!');
                    } else {
                        swal('Error', 'no se logró renovar los cufd');
                    }
                },
                complete: function() {
                    $("#spinner-div").hide(); //Ocultar icon spinner de cargando
                },
                error: function() {
                    swal('Error', 'error en el servicio');
                },
            });
        });
    </script>
@endsection
