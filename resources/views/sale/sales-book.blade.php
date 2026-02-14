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

    <section class="forms">

        <div class="container-fluid">
            <div class="card">
                <div class="card-header mt-2">
                    <h3 class="text-center">Libro de Ventas</h3>
                </div>
                <div class="row mb-12" style="margin-left: 5px; margin-right: 5px;">
                    <div class="col-md-3">
                        <label>Documento Sector</label>
                        <select name="documentoSector" id="documentoSector" class="form-control" title="Seleccionar...">
                            <option value="1" selected>FACTURA COMPRA/VENTA</option>
                            <option value="2">FACTURA ALQUILER</option>
                            <option value="13">FACTURA SERVICIOS BASICOS</option>
                            <option value="24">NOTA DE CREDITO/DEBITO</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Fecha inicio</strong> </label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control"
                            value="{{ date('Y-m-d', strtotime(' -30 day')) }}">
                    </div>
                    <div class="col-md-2">
                        <label>Fecha fin</strong> </label>
                        <input type="date" id="fecha_fin" name="fecha_fin" class="form-control"
                            value="{{ date('Y-m-d', strtotime(' +1 day')) }}">
                    </div>
                    <div class="col-md-2">
                        <label>Buscar Por</label>
                        <select name="buscarFiltro" id="buscarFiltro" class="form-control" title="Seleccionar...">
                            <option value="razonSocial" selected>Razón Social</option>
                            <option value="numeroDocumento">Numero NIT/CI</option>
                            <option value="codigoCliente">Código Cliente/Fijo</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label> &nbsp;</label>
                        <input id="textoBuscar" type="text" class="form-control" name="textoBuscar"
                            placeholder="Ingrese Nit/CI, Razón Social ó Código Cliente/Fijo...">
                    </div>
                    <div class="col-md-3">
                        <label>Sucursal *</label>
                        <input type="hidden" name="sucursal_biller_id_hidden" value="{{ $data_biller->sucursal }}">
                        <select name="sucursal" id="sucursal" class="form-control selectpicker" title="Seleccionar...">
                            @foreach ($sucursales as $sucursal)
                                <option value="{{ $sucursal->sucursal }}">{{ $sucursal->sucursal }} |
                                    {{ $sucursal->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Codigo Punto Venta *</label>
                        <input type="hidden" name="punto_venta_biller_id_hidden"
                            value="{{ $data_biller->punto_venta_siat }}">
                        <select name="codigo_punto_venta" id="codigo_punto_venta" class="form-control selectpicker"
                            title="Seleccionar...">
                            @foreach ($data_p_venta as $p_venta)
                                <option value="{{ $p_venta->codigo_punto_venta }}">{{ $p_venta->codigo_punto_venta }} -
                                    {{ $p_venta->nombre_punto_venta }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Estado *</label>
                        <select name="estado_factura" id="estado_factura" class="form-control selectpicker"
                            title="Seleccionar...">
                            <option selected value="T">Todas</option>
                            <option value="A">Válidas</option>
                            <option value="B">Anuladas</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label> &nbsp;</label>
                        <button type="submit" class="btn btn-primary" onclick="filtrarFacturas()"><i
                                class="dripicons-search"></i> Buscar</button>
                    </div>
                    @if (in_array('lv_facturas_cobradas', $all_permission))
                        <div class="col-md-2" style="margin-top: 2rem !important;">
                            <label> &nbsp;</label>
                            <a href="#" class="btn btn-info" onclick="mostrarFacturas()"><i
                                    class="dripicons-blog"></i>
                                Facturas Cobradas</a>
                        </div>
                    @endif
                    @if (in_array('lv_facturas_revertidas', $all_permission))
                        <div class="col-md-2">
                            <label> &nbsp;</label>
                            <a href="#" class="btn btn-info" onclick="mostrarReporteRevertidos()"><i
                                    class="dripicons-document"></i> Facturas Revertidas</a>
                        </div>
                    @endif
                    @if (in_array('lv_arqueogralpdf', $all_permission))
                        <div class="col-md-2" style="margin-top: 2rem !important;">
                            <label> &nbsp;</label>
                            <a href="#" class="btn btn-info" onclick="mostrarArqueoReporte()"><i
                                    class="dripicons-document"></i> Arqueo General</a>
                        </div>
                    @endif
                    @if (in_array('lv_arqueogral_categ', $all_permission))
                        <div class="col-md-2">
                            <label> &nbsp;</label>
                            <a href="#" class="btn btn-info" onclick="mostrarArqueoCategReporte()"><i
                                    class="dripicons-document"></i> Arqueo General Categoria</a>
                        </div>
                    @endif
                    @if (in_array('lv_reportespdf_excel', $all_permission))
                        <div class="col-md-2" style="margin-top: 2rem !important;">
                            <label> &nbsp;</label>
                            <a href="#" class="btn btn-info" onclick="mostrarReporte()"><i
                                    class="dripicons-document"></i> Reportes</a>
                        </div>
                    @endif
                    
                    <div class="col-md-12">
                        <label> &nbsp;</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            @include('layout.partials.spinner-ajax')
            <table id="facturas_modal" class="table table-hover">
                <thead>
                    <tr>
                        <th>Nro Factura</th>
                        <th>Documento Sector </th>
                        <th>Nro Documento/NIT</th>
                        <th>Nombre/Razón Social</th>
                        <th>Descuento</th>
                        <th>Monto Sujeto IVA</th>
                        <th>Monto Total</th>
                        <th>Fecha Emisión </th>
                        <th>Estado </th>
                        <th class="not-exported"> </th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th>Total</th>
                    <th>0.00</th>
                    <th>0.00</th>
                    <th>0.00</th>
                    <th></th>
                    <th></th>
                </tfoot>
            </table>
        </div>

        <div id="imprimir-factura-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true" class="modal fade text-left">
            <div class="modal-dialog ">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="exampleModalLabel" class="modal-title"> Imprimir Factura </h5>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                            <span aria-hidden="true">
                                <i class="dripicons-cross"></i></span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="italic">
                        </p>
                        <object>
                            <embed id="pdfID" type="text/html" width="780" height="450" src="" />
                        </object>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">{{ __('file.Close') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="pagar-factura-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true" class="modal fade text-left">
            <div class="modal-dialog ">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="exampleModalLabel" class="modal-title"> Pagar Factura </h5>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                            <span aria-hidden="true">
                                <i class="dripicons-cross"></i></span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="italic">
                            <small>
                                Este proceso Registrara la venta facturada como pagado.
                            </small>
                        <div class="row">
                            <div class="col-md-3">
                                <label>Fecha Pago</strong> </label>
                                <input type="date" id="fecha_pago" name="fecha_pago" class="form-control"
                                    value="{{ $fecha_actual }}">
                            </div>
                            <div class="col-md-3">
                                <label>Hora Pago</strong> </label>
                                <input type="time" id="hora_pago" name="hora_pago" class="form-control"
                                    value="{{ date('H:i:s') }}">
                            </div>
                            <div class="col-md-4">
                                <label>Fecha Factura</strong> </label>
                                <input type="text" id="fecha_factura" name="fecha_factura" class="form-control"
                                    readonly>
                            </div>
                            <div class="col-md-6">
                                <label>Razon Social</strong> </label>
                                <input type="text" id="razon_factura" name="razon_factura" class="form-control"
                                    readonly>
                            </div>
                            <div class="col-md-2">
                                <label>Nro. Factura</strong> </label>
                                <input type="text" id="nro_factura" name="nro_factura" class="form-control" readonly>
                            </div>
                            <div class="col-md-2">
                                <label>Monto Total</strong> </label>
                                <input type="text" id="monto_factura" name="monto_factura"
                                    class="form-control text-right" readonly>
                            </div>
                        </div>
                        </p>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <input type="hidden" name="cuf_id" required />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div>
                                <b>
                                    ¿Está seguro?
                                </b>
                            </div>
                            <div class="">
                                @csrf
                                <input id="btn_payment" type="button" value="Confirmar" class="btn btn-success">
                            </div>
                            <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">{{ __('file.Close') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="revertir-factura-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true" class="modal fade text-left">
            <div class="modal-dialog ">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="exampleModalLabel" class="modal-title"> Revertir Pago Factura </h5>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                            <span aria-hidden="true">
                                <i class="dripicons-cross"></i></span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="italic">
                            <small>
                                Este proceso revertira el pago de la factura seleccionada.
                            </small>
                        <div class="row">
                            <div class="col-md-4">
                                <label>Fecha Factura</strong> </label>
                                <input type="text" id="fecha_factura" name="fecha_factura" class="form-control"
                                    readonly>
                            </div>
                            <div class="col-md-6">
                                <label>Razon Social</strong> </label>
                                <input type="text" id="razon_factura" name="razon_factura" class="form-control"
                                    readonly>
                            </div>
                            <div class="col-md-2">
                                <label>Nro. Factura</strong> </label>
                                <input type="text" id="nro_factura" name="nro_factura" class="form-control" readonly>
                            </div>
                            <div class="col-md-2">
                                <label>Monto Total</strong> </label>
                                <input type="text" id="monto_factura" name="monto_factura"
                                    class="form-control text-right" readonly>
                            </div>
                            <div class="col-md-10">
                                <label>Observaciones</strong>* </label>
                                <input type="text" id="observaciones_reversion" name="observaciones_reversion"
                                    class="form-control" placeholder="Ingrese motivo de reversión">
                            </div>
                        </div>
                        </p>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <input type="hidden" name="cuf_id_revertir" required />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div>
                                <b>
                                    ¿Está seguro?
                                </b>
                            </div>
                            <div class="">
                                @csrf
                                <input id="btn_revertPayment" type="button" value="Confirmar" class="btn btn-success">
                            </div>
                            <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">{{ __('file.Close') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="anular-factura-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true" class="modal fade text-left">
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
                        <p class="italic">
                            <small>
                                Este proceso anulará la venta facturada.* (Servicio de Impuestos)
                            </small>
                        </p>
                        
                        <!-- Sección de datos de la factura -->
                        <div class="card mb-3" id="factura-info-card-book" style="display:none;">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fa fa-file-text"></i> Datos de la Factura</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>N° Factura:</strong> <span id="modal-nro-factura-book">-</span></p>
                                        <p class="mb-1"><strong>Cliente:</strong> <span id="modal-cliente-book">-</span></p>
                                        <p class="mb-1"><strong>NIT/CI:</strong> <span id="modal-nit-book">-</span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Fecha:</strong> <span id="modal-fecha-book">-</span></p>
                                        <p class="mb-1"><strong>Total:</strong> <span id="modal-total-book">-</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <input type="text" name="cuf_anulacion_id" id="cuf_anulacion_id" hidden>
                            <input type="text" name="punto_venta_anulacion_id" id="punto_venta_anulacion_id" hidden>
                            <input type="text" name="sucursal_anulacion_id" id="sucursal_anulacion_id" hidden>
                            <div class="form-group col-md-12">
                                <label>Motivo de Anulación</label>
                                <select name="motivo_anulacion_id" id="motivo_anulacion_id"
                                    class="selectpicker form-control" title="Seleccione motivo...">
                                </select>
                            </div>
                            
                            <!-- Opción para enviar WhatsApp -->
                            <div class="form-group col-md-12">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="send_whatsapp_book" name="send_whatsapp" value="1">
                                    <label class="custom-control-label" for="send_whatsapp_book">
                                        <i class="fa fa-whatsapp text-success"></i> Enviar notificación por WhatsApp al cliente
                                    </label>
                                </div>
                                <small class="form-text text-muted">Se enviará un mensaje al cliente informando sobre la anulación de la factura.</small>
                            </div>
                            
                            <!-- Campo número WhatsApp -->
                            <div class="form-group col-md-12" id="whatsapp_phone_container_book" style="display:none;">
                                <label>Número de WhatsApp <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-phone"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="whatsapp_phone_book" name="whatsapp_phone" placeholder="Ej: 59176543210" maxlength="15">
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
                                @csrf
                                <input id="btn_anulaFactura" type="button" value="Confirmar" class="btn btn-danger">
                            </div>
                            <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">{{ __('file.Close') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="reporte-factura-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true" class="modal fade text-left">
            <div class="modal-dialog modal-lg" style="max-width: 70%">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="exampleModalLabel" class="modal-title"> Facturas Cobradas </h5>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                            <span aria-hidden="true">
                                <i class="dripicons-cross"></i></span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <h3 id="exampleModalLabel" class="modal-title"></h3>
                        <p class="italic">
                        </p>
                        <div class="row">
                            <div class="col-md-2">
                                <label>Fecha</strong> </label>
                                <input type="date" id="fecha_filtro" name="fecha_filtro" class="form-control"
                                    value="{{ $fecha_actual }}" required>
                            </div>
                            <div class="col-md-3">
                                <label>Sucursal *</label>
                                <select name="sucursal_filtro" id="sucursal_filtro" class="form-control selectpicker"
                                    title="Seleccionar..." required>
                                    @foreach ($sucursales as $sucursal)
                                        <option value="{{ $sucursal->sucursal }}">{{ $sucursal->sucursal }} |
                                            {{ $sucursal->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Codigo Punto Venta *</label>
                                <select name="codigo_punto_venta_filtro" id="codigo_punto_venta_filtro"
                                    class="form-control selectpicker" title="Seleccionar..." required>
                                    @foreach ($data_p_venta as $p_venta)
                                        <option value="{{ $p_venta->codigo_punto_venta }}">
                                            {{ $p_venta->codigo_punto_venta }} -
                                            {{ $p_venta->nombre_punto_venta }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Usuario</label>
                                <select name="usuario_filtro" id="usuario_filtro" class="form-control selectpicker"
                                    required>
                                    @foreach ($usuarios as $usuario)
                                        <option value="{{ $usuario->name }}">{{ $usuario->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label> &nbsp;</label>
                                <button type="submit" class="btn btn-primary" onclick="reporteFacturas()"><i
                                        class="dripicons-search"></i> Buscar</button>
                            </div>
                            <div class="col-md-12">
                                <label> &nbsp;</label>
                            </div>
                        </div>
                        <object>
                            <embed id="pdfIDReporte" type="text/html" width="800" height="450" src=""
                                style="width: 100%" />
                        </object>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">{{ __('file.Close') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="reporte-arqueo-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true" class="modal fade text-left">
            <div class="modal-dialog modal-lg" style="max-width: 70%">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="exampleModalLabel" class="modal-title"> Arqueo General </h5>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                            <span aria-hidden="true">
                                <i class="dripicons-cross"></i></span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <h3 id="exampleModalLabel" class="modal-title"></h3>
                        <p class="italic">
                        </p>
                        <div class="row">
                            <div class="col-md-2">
                                <label>Fecha Inicio</strong> </label>
                                <input type="date" id="fechainc_filtro" name="fechainc_filtro" class="form-control"
                                    value="{{ $fecha_actual }}">
                            </div>
                            <div class="col-md-2">
                                <label>Fecha Fin</strong> </label>
                                <input type="date" id="fechafin_filtro" name="fechafin_filtro" class="form-control"
                                    value="{{ $fecha_actual }}">
                            </div>
                            <div class="col-md-3">
                                <label>Sucursal *</label>
                                <select name="sucursalarq_filtro" id="sucursalarq_filtro"
                                    class="form-control selectpicker" title="Seleccionar...">
                                    @foreach ($sucursales as $sucursal)
                                        <option value="{{ $sucursal->sucursal }}">{{ $sucursal->sucursal }} |
                                            {{ $sucursal->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label> &nbsp;</label>
                                <button type="submit" class="btn btn-primary" onclick="reporteArqueoCaja()"><i
                                        class="dripicons-search"></i> Buscar</button>
                            </div>
                            <div class="col-md-12">
                                <label> &nbsp;</label>
                            </div>
                        </div>
                        <object>
                            <embed id="pdfIDReporteArqueo" type="text/html" width="800" height="450"
                                src="" style="width: 100%" />
                        </object>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">{{ __('file.Close') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="reporte-arqueocateg-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true" class="modal fade text-left">
            <div class="modal-dialog modal-lg" style="max-width: 70%">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="exampleModalLabel" class="modal-title"> Arqueo General por Categoria</h5>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                            <span aria-hidden="true">
                                <i class="dripicons-cross"></i></span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <h3 id="exampleModalLabel" class="modal-title"></h3>
                        <p class="italic">
                        </p>
                        <div class="row">
                            <div class="col-md-2">
                                <label>Fecha Inicio</strong> </label>
                                <input type="date" id="fechaincat_filtro" name="fechaincat_filtro"
                                    class="form-control" value="{{ $fecha_actual }}">
                            </div>
                            <div class="col-md-2">
                                <label>Fecha Fin</strong> </label>
                                <input type="date" id="fechafincat_filtro" name="fechafincat_filtro"
                                    class="form-control" value="{{ $fecha_actual }}">
                            </div>
                            <div class="col-md-3">
                                <label>Sucursal *</label>
                                <select name="sucursalarqcat_filtro" id="sucursalarqcat_filtro"
                                    class="form-control selectpicker" title="Seleccionar...">
                                    @foreach ($sucursales as $sucursal)
                                        <option value="{{ $sucursal->sucursal }}">{{ $sucursal->sucursal }} |
                                            {{ $sucursal->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label> &nbsp;</label>
                                <button type="submit" class="btn btn-primary" onclick="reporteArqueoCategoria()"><i
                                        class="dripicons-search"></i> Buscar</button>
                            </div>
                            <div class="col-md-12">
                                <label> &nbsp;</label>
                            </div>
                        </div>
                        <object>
                            <embed id="pdfIDReporteArqueoCat" type="text/html" width="800" height="450"
                                src="" style="width: 100%" />
                        </object>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">{{ __('file.Close') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="reportes-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
            class="modal fade text-left">
            @include('layout.partials.spinner-ajax')
            <div class="modal-dialog modal-lg" style="max-width: 70%">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="exampleModalLabel" class="modal-title"> Reporte</h5>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                            <span aria-hidden="true">
                                <i class="dripicons-cross"></i></span>
                        </button>
                    </div>
                    <div class="modal-body">
                        {!! Form::open(['route' => 'sales.reporte.reporteLVEXCEL', 'method' => 'post', 'id' => 'frm_reporte']) !!}
                        <h3 id="exampleModalLabel" class="modal-title"></h3>
                        <p class="italic">
                        </p>
                        <div class="row">
                            <div class="col-md-3">
                                <label>Documento Sector *</label>
                                <select name="documentoSectorFiltroR" id="documentoSectorFiltroR" class="form-control"
                                    title="Seleccionar..." required>
                                    <option value="0" selected>CONSOLIDADO</option>
                                    <option value="1">FACTURA COMPRA/VENTA</option>
                                    <option value="2">FACTURA ALQUILER</option>
                                    <option value="13">FACTURA SERVICIOS BASICOS</option>
                                    <option value="24">NOTA DE CREDITO/DEBITO</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Gestión</strong> </label>
                                <select name="anioFiltroR" id="anioFiltroR" class="form-control" required></select>
                            </div>
                            <div class="col-md-2">
                                <label>Mes</strong> </label>
                                <select name="mesFiltroR" id="mesFiltroR" class="form-control" required>
                                    <option selected value='1'>Enero</option>
                                    <option value='2'>Febrero</option>
                                    <option value='3'>Marzo</option>
                                    <option value='4'>Abril</option>
                                    <option value='5'>Mayo</option>
                                    <option value='6'>Junio</option>
                                    <option value='7'>Julio</option>
                                    <option value='8'>Agosto</option>
                                    <option value='9'>Septiembre</option>
                                    <option value='10'>Octubre</option>
                                    <option value='11'>Noviembre</option>
                                    <option value='12'>Diciembre</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Sucursal *</label>
                                <select name="sucursalFiltroR" id="sucursalFiltroR"
                                    class="form-control selectpicker" title="Seleccionar..." required>
                                    <option value="999" selected>Consolidado</option>
                                    @foreach ($sucursales as $sucursal)
                                        <option value="{{ $sucursal->sucursal }}">{{ $sucursal->sucursal }} |
                                            {{ $sucursal->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label> &nbsp;</label>
                                <button type="button" class="btn btn-primary" onclick="reportePDF()"><i
                                        class="dripicons-preview"></i> PDF</button>
                            </div>
                            <div class="col-md-1">
                                <label> &nbsp;</label>
                                <button type="submit" class="btn btn-success"><i class="dripicons-download"></i>
                                    Excel</button>
                            </div>
                            <div class="col-md-12">
                                <label> &nbsp;</label>
                            </div>
                        </div>
                        <object>
                            <embed id="pdfIDReporteC" type="text/html" width="800" height="450" src=""
                                style="width: 100%" />
                        </object>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">{{ __('file.Close') }}</button>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>

        <div id="reporte-revertidos-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true" class="modal fade text-left">
            <div class="modal-dialog modal-lg" style="max-width: 70%">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="exampleModalLabel" class="modal-title"> Facturas Revertidas</h5>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                            <span aria-hidden="true">
                                <i class="dripicons-cross"></i></span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <h3 id="exampleModalLabel" class="modal-title"></h3>
                        <p class="italic">
                        </p>
                        <div class="row">
                            <div class="col-md-2">
                                <label>Fecha</strong> </label>
                                <input type="date" id="fecharev_filtro" name="fecharev_filtro"
                                    class="form-control" value="{{ $fecha_actual }}">
                            </div>
                            <div class="col-md-3">
                                <label>Sucursal *</label>
                                <select name="sucursalrev_filtro" id="sucursalrev_filtro"
                                    class="form-control selectpicker" title="Seleccionar...">
                                    @foreach ($sucursales as $sucursal)
                                        <option value="{{ $sucursal->sucursal }}">{{ $sucursal->sucursal }} |
                                            {{ $sucursal->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Usuario</label>
                                <select name="operadorrev_filtro" id="operadorrev_filtro" class="form-control selectpicker"
                                    required>
                                    @foreach ($usuarios as $usuario)
                                        <option value="{{ $usuario->name }}">{{ $usuario->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label> &nbsp;</label>
                                <button type="submit" class="btn btn-primary" onclick="reporteFacturasRevertidas()"><i
                                        class="dripicons-search"></i> Buscar</button>
                            </div>
                            <div class="col-md-12">
                                <label> &nbsp;</label>
                            </div>
                        </div>
                        <object>
                            <embed id="pdfIDReporteRevertidos" type="text/html" width="800" height="450"
                                src="" style="width: 100%" />
                        </object>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">{{ __('file.Close') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script type="text/javascript">
        window.facturaAnulacionData = window.facturaAnulacionData || {};
        $("ul#sale").siblings('a').attr('aria-expanded', 'true');
        $("ul#sale").addClass("show");
        $("ul#sale #sale-book-menu").addClass("active");

        let startYear = 1800;
        let endYear = new Date().getFullYear();
        for (i = endYear; i > startYear; i--) {
            $('#anioFiltroR').append($('<option />').val(i).html(i));
        }
        var all_permission = <?php echo json_encode($all_permission); ?>;
        var role_id = <?php echo json_encode(\Auth::user()->role_id); ?>;
        if (role_id > 2) {
            $('#sucursal').addClass('noselect');
            $('#codigo_punto_venta').addClass('noselect');
            $('select[name="sucursal"]').val($("input[name='sucursal_biller_id_hidden']").val());
            $('select[name="codigo_punto_venta"]').val($("input[name='punto_venta_biller_id_hidden']").val());
            $('.selectpicker').selectpicker('refresh');
        }

        if (role_id > 2) {
            $('#usuario_filtro').addClass('noselect');
            $('#sucursal_filtro').addClass('noselect');
            $('#sucursalFiltroR').addClass('noselect');
            $('#sucursalarq_filtro').addClass('noselect');
            $('#sucursalarqcat_filtro').addClass('noselect');
            $('#sucursalrev_filtro').addClass('noselect');
            $('#operadorrev_filtro').addClass('noselect');
            //$('#codigo_punto_venta_filtro').addClass('noselect');
            $('select[name=usuario_filtro]').val('{{ Auth::user()->name }}');
            $('select[name="codigo_punto_venta_filtro"]').val($("input[name='punto_venta_biller_id_hidden']").val());
            $('select[name="sucursal_filtro"]').val($("input[name='sucursal_biller_id_hidden']").val());
            $('select[name="sucursalFiltroR"]').val($("input[name='sucursal_biller_id_hidden']").val());
            $('select[name="sucursalarq_filtro"]').val($("input[name='sucursal_biller_id_hidden']").val());
            $('select[name="sucursalarqcat_filtro"]').val($("input[name='sucursal_biller_id_hidden']").val());
            $('select[name="sucursalrev_filtro"]').val($("input[name='sucursal_biller_id_hidden']").val());
            $('select[name="operadorrev_filtro"]').val('{{ Auth::user()->name }}');
            $('.selectpicker').selectpicker('refresh');
        } else {
            $('#usuario_filtro').removeClass('noselect');
            $('#sucursal_filtro').removeClass('noselect');
            $('#sucursalFiltroR').removeClass('noselect');
            $('#sucursalarq_filtro').removeClass('noselect');
            $('#sucursalarqcat_filtro').removeClass('noselect');
            $('#sucursalrev_filtro').removeClass('noselect');
            $('#operadorrev_filtro').removeClass('noselect');
           // $('#codigo_punto_venta_filtro').removeClass('noselect');
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        loadPuntoVenta();

        function loadPuntoVenta() {
            var id = $("select[name='sucursal']").val();
            var url = '{{ route('getPuntosVentas', ':id') }}';
            url = url.replace(':id', id);
            $("select[name='codigo_punto_venta']").empty();
            $.ajax({
                url: url,
                type: "GET",
                success: function(data) {
                    console.log(data);
                    for (let i = 0; i < data.length; i++) {
                        $("select[name='codigo_punto_venta']").append('<option value="' + data[i]
                            .codigo_punto_venta + '">' + data[i].codigo_punto_venta + ' - ' + data[
                                i].nombre_punto_venta + '</option>');
                    };
                    $('.selectpicker').selectpicker('refresh');

                }
            });
        }

        function loadPuntoVentaReporte() {
            var id = $("select[name='sucursal_filtro']").val();
            var url = '{{ route('getPuntosVentas', ':id') }}';
            url = url.replace(':id', id);
            $("select[name='codigo_punto_venta_filtro']").empty();
            $.ajax({
                url: url,
                type: "GET",
                success: function(data) {
                    console.log(data);
                    for (let i = 0; i < data.length; i++) {
                        $("select[name='codigo_punto_venta_filtro']").append('<option value="' + data[i]
                            .codigo_punto_venta + '">' + data[i].codigo_punto_venta + ' - ' + data[
                                i].nombre_punto_venta + '</option>');
                    };
                    $('.selectpicker').selectpicker('refresh');

                }
            });
        }
        // Cuando se seleccione una sucursal, mostrar sus puntos de ventas respectivos. 
        $('#sucursal').on('change', function() {
            var id = $(this).val();
            var url = '{{ route('getPuntosVentas', ':id') }}';
            url = url.replace(':id', id);

            $("select[name='codigo_punto_venta']").empty();

            $.ajax({
                url: url,
                type: "GET",
                success: function(data) {
                    console.log(data);
                    for (let i = 0; i < data.length; i++) {
                        $("select[name='codigo_punto_venta']").append('<option value="' + data[i]
                            .codigo_punto_venta + '">' + data[i].codigo_punto_venta + ' - ' + data[
                                i].nombre_punto_venta + '</option>');
                    };
                    $('.selectpicker').selectpicker('refresh');

                }
            });
        })

        $('#sucursal_filtro').on('change', function() {
            var id = $(this).val();
            var url = '{{ route('getPuntosVentas', ':id') }}';
            url = url.replace(':id', id);

            $("select[name='codigo_punto_venta_filtro']").empty();

            $.ajax({
                url: url,
                type: "GET",
                success: function(data) {
                    console.log(data);
                    for (let i = 0; i < data.length; i++) {
                        $("select[name='codigo_punto_venta_filtro']").append('<option value="' + data[i]
                            .codigo_punto_venta + '">' + data[i].codigo_punto_venta + ' - ' + data[
                                i].nombre_punto_venta + '</option>');
                    };
                    $('.selectpicker').selectpicker('refresh');

                }
            });
        })

        $('#facturas_modal').DataTable({
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
            'lengthMenu': [
                [10, 50, 100, -1],
                [10, 50, 100, "All"]
            ],
            'columnDefs': [{
                "orderable": false,
                'targets': [1, 2, 3, 4, 5, 6, 7, 8]
            }],
        });

        function filtrarFacturas() {
            var start_date_get = $("#fecha_inicio").val();
            var end_date_get = $("#fecha_fin").val();
            var puntoVenta = $("select[name='codigo_punto_venta']").val();
            
            console.log('=== Iniciando filtrado de facturas ===');
            console.log('Fechas:', start_date_get, '-', end_date_get);
            console.log('Punto de Venta:', puntoVenta);
            
            if (puntoVenta == "" || puntoVenta == null) {
                swal("Mensaje", "Seleccione un Punto de Venta", "info");
                return;
            }
            
            if (!start_date_get || !end_date_get) {
                swal("Mensaje", "Seleccione las fechas de inicio y fin", "info");
                return;
            }
            
            $('#facturas_modal').DataTable({
                destroy: true,
                "processing": true,
                "serverSide": true,
                "ajax": {
                    url: "list_booksales",
                    dataType: "json",
                    data: {
                        fechaInc: start_date_get,
                        fechaFin: end_date_get,
                        documentoSector: $("select[name='documentoSector']").val(),
                        opcion: $("select[name='buscarFiltro']").val(),
                        sucursal: $("select[name='sucursal']").val(),
                        puntoVenta: $("select[name='codigo_punto_venta']").val(),
                        valor: $("#textoBuscar").val(),
                        estado: $("select[name='estado_factura']").val(),
                    },
                    type: "post",
                    error: function(xhr, error, code) {
                        console.error('Error Ajax:', error);
                        console.error('Status:', xhr.status);
                        console.error('Response:', xhr.responseText);
                        
                        var errorMsg = 'Error al cargar las facturas. ';
                        if (xhr.status === 0) {
                            errorMsg += 'No se pudo conectar al servidor.';
                        } else if (xhr.status === 404) {
                            errorMsg += 'Ruta no encontrada (404).';
                        } else if (xhr.status === 500) {
                            errorMsg += 'Error interno del servidor (500).';
                        } else if (error === 'parsererror') {
                            errorMsg += 'Error al procesar la respuesta JSON.';
                        } else if (error === 'timeout') {
                            errorMsg += 'Tiempo de espera agotado.';
                        } else {
                            errorMsg += xhr.responseText || code;
                        }
                        
                        swal({
                            title: "Error",
                            text: errorMsg,
                            icon: "error",
                            button: "Aceptar",
                        });
                    },
                    dataSrc: function(json) {
                        // Verificar si hay un mensaje de error en la respuesta JSON
                        if (json.error) {
                            console.error('Error en respuesta JSON:', json.error);
                            swal({
                                title: "Error",
                                text: json.error,
                                icon: "error",
                                button: "Aceptar",
                            });
                            return [];
                        }
                        return json.data;
                    }
                },
                "createdRow": function(row, data, dataIndex) {
                    $(row).addClass('invoice-link');
                    //$(row).attr('data-presale', data['id']);
                },
                "columns": [{
                        "data": "numeroFactura"
                    },
                    {
                        "data": "documentoSector"
                    },
                    {
                        "data": "numeroDocumento"
                    },
                    {
                        "data": "nombreRazonSocial"
                    },
                    {
                        "data": "descuentoAdicional"
                    },
                    {
                        "data": "montoTotalSujetoIva"
                    },
                    {
                        "data": "montoTotal"
                    },
                    {
                        "data": "fechaEmision"
                    },
                    {
                        "data": "estado"
                    },
                    {
                        "data": "options"
                    },
                ],
                'columnDefs': [{
                    "orderable": false,
                    'targets': [1, 2, 3, 4, 5, 6, 7, 8]
                }],
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
                    ['0', 'desc']
                ],
                'lengthMenu': [
                    [10, 50, 100, -1],
                    [10, 50, 100, "All"]
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

                $(dt_selector.column(4).footer()).html(dt_selector.cells(rows, 4, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(5).footer()).html(dt_selector.cells(rows, 5, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(6).footer()).html(dt_selector.cells(rows, 6, {
                    page: 'current'
                }).data().sum().toFixed(2));
            } else {
                $(dt_selector.column(4).footer()).html(dt_selector.column(4, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(5).footer()).html(dt_selector.column(5, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(6).footer()).html(dt_selector.column(6, {
                    page: 'current'
                }).data().sum().toFixed(2));
            }
        }

        $(document).on("click", "table tbody .imprimir-factura-modal", function(event) {
            var id = $(this).data('id').toString();
            var url = '{{ route('sales.print-factura', ':id') }}';
            url = url.replace(':id', id);
            $.ajax({
                url: url,
                type: "GET",
                async: false,
                beforeSend: function() {
                    $("#spinner-div").show(); //Mostrar icon spinner de cargando
                },
                success: function(data) {
                    console.log(data);
                    if (data.ESTADO === "OK") {
                        $('#pdfID').attr('src', 'data:application/pdf;base64,' + data['bytes']);
                        console.log('data:application/pdf;base64,' + data['bytes']);
                        $('#imprimir-factura-modal').modal('show');
                    } else {
                        swal("Error", "Error: " + data, "error");
                    }
                    $("#spinner-div").hide(); //Ocultar icon spinner de cargando
                },
                error: function(request, status, error) {
                    $("#spinner-div").hide(); //Ocultar icon spinner de cargando
                    swal("Error", "Error: " + request.responseText, "error");
                },
            });
        });

        $(document).on("click", "table tbody .pagar-factura-modal", function(event) {
            var id = $(this).data('id').toString();
            $('input[name="cuf_id"]').val(id);
            var url_data = "{{ route('sales.factura', ':id') }}";
            url_data = url_data.replace(':id', id);
            $('#pagar-factura-modal').removeData('bs.modal');
            $("#spinner-div").show(); //Mostrar icon spinner de cargando
            $.ajax({
                url: url_data,
                type: "GET",
                success: function(data) {
                    if (data.estado) {
                        $('input[name="fecha_factura"]').val(data.factura.fechaEmision);
                        $('input[name="razon_factura"]').val(data.factura.nombreRazonSocial);
                        $('input[name="nro_factura"]').val(data.factura.numeroFactura);
                        $('input[name="monto_factura"]').val(data.factura.montoTotal);
                        $('input[name="hora_pago"]').val('{{ date('H:i:s') }}');
                        $('#pagar-factura-modal').modal('show');
                    } else {
                        $('#pagar-factura-modal').modal('hide');
                        swal('Mensaje', 'No se encontro la factura', 'error');
                    }
                    $("#spinner-div").hide(); //Ocultar icon spinner de cargando
                },
                error: function() {
                    swal('Error', 'error en el servicio');
                },
            });
        });

        $(document).on("click", "table tbody .revertir-factura-modal", function(event) {
            var id = $(this).data('id').toString();
            $('input[name="cuf_id_revertir"]').val(id);
            var url_data = "{{ route('sales.factura', ':id') }}";
            url_data = url_data.replace(':id', id);

            $("#spinner-div").show(); //Mostrar icon spinner de cargando
            $.ajax({
                url: url_data,
                type: "GET",
                success: function(data) {
                    if (data.estado) {
                        $('input[name="fecha_factura"]').val(data.factura.fechaEmision);
                        $('input[name="razon_factura"]').val(data.factura.nombreRazonSocial);
                        $('input[name="nro_factura"]').val(data.factura.numeroFactura);
                        $('input[name="monto_factura"]').val(data.factura.montoTotal);
                        $('#revertir-factura-modal').modal('show');
                    } else {
                        $('#revertir-factura-modal').modal('hide');
                        swal('Mensaje', 'No se encontro la factura', 'error');
                    }
                    $("#spinner-div").hide(); //Ocultar icon spinner de cargando
                },
                error: function() {
                    swal('Error', 'error en el servicio');
                },
            });
        });

        // Cachear motivos de anulación globalmente
        var motivosAnulacionCache = null;

        // Cargar motivos de anulación una sola vez
        function cargarMotivosAnulacion(callback) {
            if (motivosAnulacionCache !== null) {
                // Ya están en caché, usar directamente
                callback(motivosAnulacionCache);
                return;
            }

            // Primera carga, obtener del servidor
            var url = '{{ route('sales.get_motivo_anulacion') }}';
            $("#spinner-div").show();
            $.ajax({
                url: url,
                type: "GET",
                success: function(data) {
                    motivosAnulacionCache = data; // Guardar en caché
                    callback(data);
                    $("#spinner-div").hide();
                },
                error: function() {
                    $("#spinner-div").hide();
                    swal('Error', 'No se pudieron cargar los motivos de anulación', 'error');
                }
            });
        }

        $(document).on("click", "table tbody .anular-factura-modal", function(event) {
            var id = $(this).data('id').toString();          // CUF
            var puntoVenta = $(this).data('ptoventa');
            var sucursal = $(this).data('sucursal');
            var $tr = $(this).closest('tr');

            console.log('Anular factura - CUF:', id, 'PtoVenta:', puntoVenta, 'Sucursal:', sucursal);

            $('input[name="cuf_anulacion_id"]').val(id);
            $('input[name="punto_venta_anulacion_id"]').val(puntoVenta);
            $('input[name="sucursal_anulacion_id"]').val(sucursal);

            // Resetear checkbox y campo de WhatsApp
            $('#send_whatsapp_book').prop('checked', false);
            $('#whatsapp_phone_container_book').hide();
            $('#whatsapp_phone_book').val('');

            // Extraer datos correctos desde la fila (según tu THEAD)
            // 0 Nro Factura | 1 Doc Sector | 2 NIT/CI | 3 Razon Social | 4 Desc | 5 Sujeto IVA | 6 Total | 7 Fecha | 8 Estado
            try {
                var cells = $tr.find('td');
                if (cells.length > 0) {
                    var nroFactura   = $(cells[0]).text().trim() || '';
                    var nit          = $(cells[2]).text().trim() || '';
                    var razonSocial  = $(cells[3]).text().trim() || '';
                    var montoTotal   = $(cells[6]).text().trim() || '';
                    var fechaEmision = $(cells[7]).text().trim() || '';

                    // Mostrar los datos en el modal
                    $('#modal-nro-factura-book').text(nroFactura || '-');
                    $('#modal-cliente-book').text(razonSocial || '-');
                    $('#modal-nit-book').text(nit || '-');
                    $('#modal-fecha-book').text(fechaEmision || '-');
                    $('#modal-total-book').text('Bs. ' + (montoTotal || '-'));
                    $('#factura-info-card-book').show();

                    // ✅ Guardar datos globalmente para el POST (el botón Confirmar está en el modal, no en la tabla)
                    window.facturaAnulacionData = {
                        nroFactura: nroFactura,
                        nit: nit,
                        razonSocial: razonSocial,
                        total: montoTotal,
                        fecha: fechaEmision
                    };

                    console.log('Factura guardada para anulación:', window.facturaAnulacionData);

                    // Intentar obtener el número de teléfono del cliente
                    $.ajax({
                        url: '{{ route('sales.get_customer_phone_by_cuf') }}',
                        type: 'POST',
                        data: {
                            cuf: id,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success && response.phone) {
                                $('#whatsapp_phone_book').val(response.phone);
                            }
                        },
                        error: function() {
                            console.log('No se pudo obtener el teléfono del cliente');
                        }
                    });

                } else {
                    $('#factura-info-card-book').hide();
                    window.facturaAnulacionData = {};
                }
            } catch (e) {
                console.error('Error al extraer datos de la factura:', e);
                $('#factura-info-card-book').hide();
                window.facturaAnulacionData = {};
            }

            // Cargar motivos (desde caché o servidor)
            cargarMotivosAnulacion(function(data) {
                $("select[name='motivo_anulacion_id']").empty();
                for (let i = 0; i < data.length; i++) {
                    $("select[name='motivo_anulacion_id']").append(
                        '<option value="' + data[i].codigo_clasificador + '">' +
                        data[i].codigo_clasificador + ' - ' + data[i].descripcion +
                        '</option>'
                    );
                }
                $('.selectpicker').selectpicker('refresh');
                $('#anular-factura-modal').modal('show');
            });
        });

        /** Pagar Factura */
        $(document).on("click", "#btn_payment", function(event) {
            var id = $('input[name="cuf_id"]').val();
            var fechaPago = $('input[name="fecha_pago"]').val();
            var horaPago = $('input[name="hora_pago"]').val();
            var url_data = "{{ route('sales.pagar-factura') }}";
            $('#pagar-factura-modal').modal('hide');
            $("#spinner-div").show(); //Mostrar icon spinner de cargando
            $.ajax({
                url: url_data,
                type: "POST",
                data: {
                    cuf: id,
                    fecha: fechaPago,
                    hora: horaPago
                },
                success: function(data) {
                    $("#spinner-div").hide();
                    if (data.estado) {
                        swal('Mensaje', 'Se completo el pago', 'success');
                        filtrarFacturas();
                    } else {
                        swal('Mensaje', 'No se completo el pago', 'error');
                    }
                },
                error: function(request, status, error) {
                    swal("Error", "Error: " + request.responseText, "error");
                },
            });
        });

        /** Revertir Pago Factura */
        $(document).on("click", "#btn_revertPayment", function(event) {
            var id = $('input[name="cuf_id_revertir"]').val();
            var nota = $('input[name="observaciones_reversion"]').val();
            var url_data = "{{ route('sales.revertirpago-factura') }}";
            if (nota == null || nota == '') {
                swal("Error Validación", "El campo observaciones es obligatorio", "error");
                return;
            }
            $('#revertir-factura-modal').modal('hide');
            $("#spinner-div").show(); //Mostrar icon spinner de cargando
            $.ajax({
                url: url_data,
                type: "POST",
                data: {
                    cuf: id,
                    observaciones: nota
                },
                success: function(data) {
                    $("#spinner-div").hide();
                    if (data.estado) {
                        swal('Mensaje', 'Se completo el reversión', 'success');
                        filtrarFacturas();
                    } else {
                        swal('Mensaje Error', 'No se completo la reversión, ' + data.mensaje,
                            'error');
                    }
                },
                error: function(request, status, error) {
                    swal("Error", "Error: " + request.responseText, "error");
                },
            });
        });

        /** Anular Factura */
        $(document).on("click", "#btn_anulaFactura", function(event) {
            var id = $('input[name="cuf_anulacion_id"]').val(); // CUF
            var motivo = $("select[name='motivo_anulacion_id']").val();
            var puntoVenta = $('input[name="punto_venta_anulacion_id"]').val();
            var sucursal = $('input[name="sucursal_anulacion_id"]').val();

            var sendWhatsapp = $('#send_whatsapp_book').is(':checked') ? '1' : '0';
            var whatsappPhone = $('#whatsapp_phone_book').val();

            // Validar número de WhatsApp si el checkbox está marcado
            if (sendWhatsapp === '1' && (!whatsappPhone || whatsappPhone.trim() === '')) {
                swal('Error', 'Por favor ingrese el número de WhatsApp', 'warning');
                return;
            }

            // ✅ Tomar datos guardados al hacer click en el icono de anulación
            var facturaData = window.facturaAnulacionData || {};

            console.log('Enviando anulación:', {
                cuf: id,
                motivo: motivo,
                puntoVenta: puntoVenta,
                sucursal: sucursal,
                whatsapp: sendWhatsapp,
                phone: whatsappPhone,
                facturaData: facturaData
            });

            var url_data = "{{ route('sales.anular_factura') }}";
            $('#anular-factura-modal').modal('hide');
            $("#spinner-div").show();

            $.ajax({
                url: url_data,
                type: "POST",
                data: {
                    cuf_anulacion_id: id,
                    motivo_anulacion_id: motivo,
                    punto_venta_id: puntoVenta,
                    sucursal_id: sucursal,
                    send_whatsapp: sendWhatsapp,
                    whatsapp_phone: whatsappPhone,

                    // ✅ Datos correctos de la factura (de la fila)
                    factura_numero: facturaData.nroFactura || '',
                    factura_cliente: facturaData.razonSocial || '',
                    factura_nit: facturaData.nit || '',
                    factura_fecha: facturaData.fecha || '',
                    factura_total: facturaData.total || '',

                    _token: '{{ csrf_token() }}'
                },
                success: function(data) {
                    $("#spinner-div").hide();
                    if (data.estado) {
                        swal('Mensaje', '' + data.mensaje, 'success');
                        filtrarFacturas();
                    } else {
                        swal('Mensaje Error', '' + data.mensaje, 'error');
                    }
                },
                error: function(request, status, error) {
                    $("#spinner-div").hide();
                    swal("Error", "Error: " + request.responseText, "error");
                }
            });
        });
        
        // Handler para mostrar/ocultar campo de WhatsApp
        $(document).on('change', '#send_whatsapp_book', function() {
            if ($(this).is(':checked')) {
                $('#whatsapp_phone_container_book').slideDown();
            } else {
                $('#whatsapp_phone_container_book').slideUp();
            }
        });

        function mostrarFacturas() {
            loadPuntoVentaReporte();
            $('#reporte-factura-modal').modal('show');
        }

        function mostrarArqueoReporte() {
            $('#reporte-arqueo-modal').modal('show');
        }

        function mostrarArqueoCategReporte() {
            $('#reporte-arqueocateg-modal').modal('show');
        }

        function mostrarReporte() {
            $('#reportes-modal').modal('show');
        }

        function mostrarReporteRevertidos() {
            $('#reporte-revertidos-modal').modal('show');
        }

        function reporteFacturas() {
            var fechaF = $('input[name="fecha_filtro"]').val();
            var sucursalF = $('select[name="sucursal_filtro"]').val();
            var puntoF = $('select[name="codigo_punto_venta_filtro"]').val();
            var usuarioF = $('select[name="usuario_filtro"]').val();
            if (fechaF == "" || sucursalF == "" || puntoF == "" || usuarioF == null) {
                swal("Error Validación", "Selecione los filtros necesarios, hay uno o más filtros sin seleccionar",
                    "error");
                return;
            }
            var url = '{{ route('sales.reporte.cobranza') }}';
            $.ajax({
                url: url,
                type: "POST",
                data: {
                    fecha: fechaF,
                    sucursal: sucursalF,
                    puntoVenta: puntoF,
                    usuario: usuarioF
                },
                dataType: "json",
                async: false,
                success: function(data) {
                    console.log(data);
                    if (data.ESTADO === "OK") {
                        $('#pdfIDReporte').attr('src', 'data:application/pdf;base64,' + data['bytes']);
                        console.log('data:application/pdf;base64,' + data['bytes']);
                    } else {
                        $('#pdfIDReporte').attr('src', '');
                        swal("Error", "Error: " + data.mensaje, "error");
                    }

                },
                error: function(request, status, error) {
                    swal("Error", "Error: " + request.responseText, "error");
                },
            });
        }

        function reporteArqueoCaja() {
            var fechaInicialF = $('input[name="fechainc_filtro"]').val();
            var fechaFinalF = $('input[name="fechafin_filtro"]').val();
            var sucursalF = $('select[name="sucursalarq_filtro"]').val();
            if (fechaInicialF == '' || fechaFinalF == '' || sucursalF == '') {
                swal("Error Validación", "Selecione los filtros necesarios, hay uno o más filtros sin seleccionar",
                    "error");
                return;
            }
            var url = '{{ route('sales.reporte.arqueogral') }}';
            $.ajax({
                url: url,
                type: "POST",
                data: {
                    fechaInicial: fechaInicialF,
                    sucursal: sucursalF,
                    fechaFin: fechaFinalF,
                },
                dataType: "json",
                async: false,
                success: function(data) {
                    console.log(data);
                    if (data.ESTADO === "OK") {
                        $('#pdfIDReporteArqueo').attr('src', 'data:application/pdf;base64,' + data['bytes']);
                    } else {
                        $('#pdfIDReporteArqueo').attr('src', '');
                        swal("Error", "Error: " + data.mensaje, "error");
                    }

                },
                error: function(request, status, error) {
                    swal("Error", "Error: " + request.responseText, "error");
                },
            });
        }

        function reporteArqueoCategoria() {
            var fechaInicialF = $('input[name="fechaincat_filtro"]').val();
            var fechaFinalF = $('input[name="fechafincat_filtro"]').val();
            var sucursalF = $('select[name="sucursalarqcat_filtro"]').val();
            if (fechaInicialF == '' || fechaFinalF == '' || sucursalF == '') {
                swal("Error Validación", "Selecione los filtros necesarios, hay uno o más filtros sin seleccionar",
                    "error");
                return;
            }
            var url = '{{ route('sales.reporte.arqueogralcateg') }}';
            $.ajax({
                url: url,
                type: "POST",
                data: {
                    fechaInicial: fechaInicialF,
                    sucursal: sucursalF,
                    fechaFin: fechaFinalF,
                },
                dataType: "json",
                async: false,
                success: function(data) {
                    console.log(data);
                    if (data.ESTADO === "OK") {
                        $('#pdfIDReporteArqueoCat').attr('src', 'data:application/pdf;base64,' + data['bytes']);
                    } else {
                        $('#pdfIDReporteArqueoCat').attr('src', '');
                        swal("Error", "Error: " + data.mensaje, "error");
                    }

                },
                error: function(request, status, error) {
                    swal("Error", "Error: " + request.responseText, "error");
                },
            });
        }

        function reportePDF() {
            var documentoF = $('select[name="documentoSectorFiltroR"]').val();
            var gestionF = $('select[name="anioFiltroR"]').val();
            var mesF = $('select[name="mesFiltroR"]').val();
            var sucursalF = $('select[name="sucursalFiltroR"]').val();
            if (documentoF == '' || gestionF == '' || mesF == '' || sucursalF == '') {
                swal("Error Validación", "Selecione los filtros necesarios, hay uno o más filtros sin seleccionar",
                    "error");
                return;
            }
            var url = '{{ route('sales.reporte.reporteLVPDF') }}';
            $.ajax({
                url: url,
                type: "POST",
                data: {
                    documento: documentoF,
                    sucursal: sucursalF,
                    gestion: gestionF,
                    mes: mesF
                },
                dataType: "json",
                async: false,
                beforeSend: function() {
                    $("#spinner-div").show();
                },
                success: function(data) {
                    console.log(data);
                    $("#spinner-div").hide();
                    if (data.ESTADO === "OK") {
                        $('#pdfIDReporteC').attr('src', 'data:application/pdf;base64,' + data['bytes']);
                    } else {
                        $('#pdfIDReporteC').attr('src', '');
                        swal("Error", "Error: " + data.mensaje, "error");
                    }
                },
                error: function(request, status, error) {
                    $("#spinner-div").hide();
                    swal("Error", "Error: " + request.responseText, "error");
                },
            });
        }

        function reporteFacturasRevertidas(){
            var fechaF = $('input[name="fecharev_filtro"]').val();
            var sucursalF = $('select[name="sucursalrev_filtro"]').val();
            var operadorF = $('select[name="operadorrev_filtro"]').val();

            if (fechaF == '' || sucursalF == '' || operadorF == '') {
                swal("Error Validación", "Selecione los filtros necesarios, hay uno o más filtros sin seleccionar",
                    "error");
                return;
            }
            var url = '{{ route('sales.reporte.revertido') }}';
            $.ajax({
                url: url,
                type: "POST",
                data: {
                    fecha: fechaF,
                    sucursal: sucursalF,
                    usuario: operadorF
                },
                dataType: "json",
                async: false,
                beforeSend: function() {
                    $("#spinner-div").show();
                },
                success: function(data) {
                    console.log(data);
                    $("#spinner-div").hide();
                    if (data.ESTADO === "OK") {
                        $('#pdfIDReporteRevertidos').attr('src', 'data:application/pdf;base64,' + data['bytes']);
                    } else {
                        $('#pdfIDReporteRevertidos').attr('src', '');
                        swal("Error", "Error: " + data.mensaje, "error");
                    }
                },
                error: function(request, status, error) {
                    $("#spinner-div").hide();
                    swal("Error", "Error: " + request.responseText, "error");
                },
            });
        }
    </script>
@endsection('content')
