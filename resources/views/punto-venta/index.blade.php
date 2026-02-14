@extends('layout.main')
@section('content')
    @include('layout.partials.session-flash')

    <section>
        <div class="container-fluid">
            <a href="#" data-toggle="modal" data-target="#createModal" class="btn btn-info">
                <i class="dripicons-plus"></i>
                Punto de Venta
            </a>
            <a href="#" data-toggle="modal" data-target="#createComisionistaModal" class="btn btn-info">
                <i class="dripicons-plus"></i>
                Punto de Venta Comisionista
            </a>
            <a href="#" data-toggle="modal" data-target="#renovacionMasiva" class="btn btn-warning">
                <i class="fa fa-refresh"></i>
                Cuis Renovacion Masiva
            </a>

        </div>
        <div class="table-responsive">
            <table id="item-table" class="table">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
                        <th>COD</th>
                        <th>Nombre Punto Venta</th>
                        <th>Tipo Punto de Venta</th>
                        <th>Código Cuis</th>
                        <th>Vigencia Cuis</th>
                        <th>SIAT</th>
                        <th>Estado</th>
                        <th class="not-exported">{{ __('file.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $key => $item)
                        <?php
                        $fechaCuis = date('Y-m-d', strtotime($item->fecha_vigencia_cuis));
                        $diff = abs(strtotime($fechaCuis) - strtotime(date('Y-m-d')));
                        $years = floor($diff / (365 * 60 * 60 * 24));
                        $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                        $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
                        ?>
                        <tr data-id="{{ $item->id }}">
                            <td>{{ $key }}</td>
                            <td>{{ $item->codigo_punto_venta }}</td>
                            <td>{{ $item->nombre_punto_venta }}</td>
                            <td>{{ $item->getTipoVenta() }}</td>
                            <td>{{ $item->codigo_cuis }}</td>
                            @if ($years == 0 && $months == 0 && $days < 6)
                                <td>
                                    <div class="badge badge-warning">{{ $item->getFecha() }}</div>
                                </td>
                            @else
                                <td>{{ $item->getFecha() }}</td>
                            @endif
                            @if ($item->is_siat)
                                <td>
                                    <div class="badge badge-success">SI</div>
                                </td>
                            @else
                                <td>
                                    <div class="badge badge-warning">NO</div>
                                </td>
                            @endif
                            @if ($item->is_active)
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
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        {{ __('file.action') }}
                                        <span class="caret"></span>
                                        <span class="sr-only">Toggle Dropdown</span>
                                    </button>
                                    <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default"
                                        user="menu">
                                        @if ($item->is_active)
                                            <li>
                                                <button type="button" data-id="{{ $item->id }}"
                                                    data-codigo_punto_venta="{{ $item->codigo_punto_venta }}"
                                                    data-nombre_punto_venta="{{ $item->nombre_punto_venta }}"
                                                    data-descripcion="{{ $item->descripcion }}"
                                                    data-tipo_punto_venta="{{ $item->tipo_punto_venta }}"
                                                    data-codigo_cuis="{{ $item->codigo_cuis }}"
                                                    data-fecha_vigencia_cuis="{{ $item->fecha_vigencia_cuis }}"
                                                    data-sucursal="{{ $item->sucursal }}" data-ready="false"
                                                    class="edit-btn btn btn-link" data-toggle="modal"
                                                    data-target="#editModal">
                                                    <i class="dripicons-document-edit"></i>
                                                    {{ trans('file.edit') }}
                                                </button>
                                            </li>
                                        @endif
                                        <li class="divider"></li>

                                        <li>
                                            <button type="button" data-id="{{ $item->id }}"
                                                data-codigo_punto_venta="{{ $item->codigo_punto_venta }}"
                                                data-nombre_punto_venta="{{ $item->nombre_punto_venta }}"
                                                data-descripcion="{{ $item->descripcion }}"
                                                data-tipo_punto_venta="{{ $item->tipo_punto_venta }}"
                                                data-codigo_cuis="{{ $item->codigo_cuis }}"
                                                data-fecha_vigencia_cuis="{{ $item->fecha_vigencia_cuis }}"
                                                data-fecha_inicio="{{ $item->fecha_inicio }}"
                                                data-fecha_fin="{{ $item->fecha_fin }}"
                                                data-nit_comisionista="{{ $item->nit_comisionista }}"
                                                data-numero_contrato="{{ $item->numero_contrato }}"
                                                data-sucursal="{{ $item->sucursal }}" data-ready="true"
                                                class="edit-btn btn btn-link" data-toggle="modal"
                                                @if ($item->codigo_punto_venta) 
                                                data-target="#editModal" 
                                                @else
                                                data-target="#editComisionistaModal" 
                                                @endif>
                                                <i class="dripicons-preview"></i>
                                                Ver
                                            </button>
                                        </li>
                                        {{ Form::open(['route' => ['punto_venta.destroy', $item->id], 'method' => 'DELETE']) }}
                                        <li>
                                            <button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i
                                                    class="dripicons-trash"></i> Dar Baja</button>
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

    <!-- Create Modal -->
    <div id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                {!! Form::open(['route' => 'punto_venta.store', 'method' => 'post']) !!}
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">Agregar Punto de Venta</h5>
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
                        <div class="row">
                            <input type="hidden" id="modoSIN" name="modoSIN" value="1">
                            <input type="hidden" id="modoComisionista" name="modoComisionista" value="0">
                            <div class="form-group col-md-3">
                                <label>Código Punto Venta *</label>
                                <input type="text" name="codigo_punto_venta" value="0" required readonly
                                    class="form-control" placeholder="1, 24, 28, 34, 35...">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Nombre Punto Venta *</label>
                                <input type="text" name="nombre_punto_venta" required class="form-control">
                            </div>
                            <div class="form-group col-md-3">
                                <label><small> (Modo Conectado a SIN)</small></label>
                                <li class="nav-item">SIAT
                                    <input id="toggle-event-pt" checked type="checkbox" data-toggle="toggle"
                                        data-on="Si" data-off="No" data-onstyle="primary" data-offstyle="secondary">
                                </li>
                            </div>
                            <div class="form-group col-md-12">
                                <label>Descripcion *</label>
                                <input type="text" name="descripcion" required class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>Tipo Punto Venta *</label>
                                <select name="tipo_punto_venta" class="selectpicker form-control" required>
                                    <option selected>Seleccionar</option>
                                    @include('punto-venta.partials-parametros-punto_ventas')
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Sucursal *</label>
                                <select name="sucursal" id="sucursal" class="selectpicker form-control"
                                    title="Seleccionar..." required>
                                    @foreach ($sucursales as $sucursal)
                                        <option value="{{ $sucursal->sucursal }}">
                                            {{ $sucursal->nombre }} | {{ $sucursal->domicilio_tributario }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>Código CUIS <small> (Generado Automaticamente)</small></label>
                                <input type="text" name="codigo_cuis" readonly class="form-control">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Vencimiento CUIS <small> (Generado Automaticamente)</small></label>
                                <input type="datetime-local" name="fecha_vigencia_cuis" readonly class="form-control">
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <input type="submit" value="{{ trans('file.submit') }}" class="btn btn-primary">
                        </div>
                    </form>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div id="createComisionistaModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true" class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                {!! Form::open(['route' => 'punto_venta.store', 'method' => 'post']) !!}
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">Agregar Punto de Venta</h5>
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
                        <div class="row">
                            <input type="hidden" id="modoSIN" name="modoSIN" value="1">
                            <input type="hidden" id="modoComisionista" name="modoComisionista" value="1">
                            <div class="form-group col-md-3">
                                <label>Código Punto Venta *</label>
                                <input type="text" name="codigo_punto_venta" value="0" required readonly
                                    class="form-control" placeholder="1, 24, 28, 34, 35...">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Nombre Punto Venta *</label>
                                <input type="text" name="nombre_punto_venta" required class="form-control">
                            </div>
                            <div class="form-group col-md-3">
                                <label><small> (Modo Conectado a SIN)</small></label>
                                <li class="nav-item">SIAT
                                    <input id="toggle-event-pt" checked type="checkbox" data-toggle="toggle"
                                        data-on="Si" data-off="No" data-onstyle="primary" data-offstyle="secondary">
                                </li>
                            </div>
                            <div class="form-group col-md-12">
                                <label>Descripcion *</label>
                                <input type="text" name="descripcion" required class="form-control">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Sucursal *</label>
                                <select name="sucursal" id="sucursal" class="selectpicker form-control"
                                    title="Seleccionar..." required>
                                    @foreach ($sucursales as $sucursal)
                                        <option value="{{ $sucursal->sucursal }}">
                                            {{ $sucursal->nombre }} | {{ $sucursal->domicilio_tributario }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label>NIT Comisionista *</label>
                                <input type="text" name="nit_comisionista" required class="form-control">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Nro Contrato *</label>
                                <input type="text" name="numero_contrato" required class="form-control">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Fecha Inicio *</label>
                                <input type="date" name="fecha_inicio" required class="form-control">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Fecha Fin *</label>
                                <input type="date" name="fecha_fin" required class="form-control">
                            </div>
                            <div class="form-group col-md-6"></div>
                            <div class="form-group col-md-6">
                                <label>Código CUIS <small> (Generado Automaticamente)</small></label>
                                <input type="text" name="codigo_cuis" readonly class="form-control">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Vencimiento CUIS <small> (Generado Automaticamente)</small></label>
                                <input type="datetime-local" name="fecha_vigencia_cuis" readonly class="form-control">
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <input type="submit" value="{{ trans('file.submit') }}" class="btn btn-primary">
                        </div>
                    </form>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>

    <!-- Edit/View Modal -->
    <div id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                {{ Form::open(['route' => ['punto_venta.update', 1], 'method' => 'PUT']) }}
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">Actualizar Punto de Venta</h5>
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
                    <input type="hidden" name="punto_venta_id">
                    <form>
                        <div class="row">
                            <input type="hidden" id="modoSINEdit" name="modoSINEdit" value="1">
                            <div class="form-group col-md-3">
                                <label>Código Punto Venta *</label>
                                <input type="text" id="codigo_punto_venta_edit" name="codigo_punto_venta" required
                                    class="form-control" readonly>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Nombre Punto Venta *</label>
                                <input type="text" name="nombre_punto_venta" required class="form-control">
                            </div>
                            <div class="form-group col-md-3">
                                <label><small> (Modo Conectado a SIN)</small></label>
                                <li class="nav-item">SIAT
                                    <input id="toggle-event-pted" checked type="checkbox" data-toggle="toggle"
                                        data-on="Si" data-off="No" data-onstyle="primary" data-offstyle="secondary">
                                </li>
                            </div>
                            <div class="form-group col-md-12">
                                <label>Descripcion *</label>
                                <input type="text" name="descripcion" required class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>Tipo Punto Venta *</label>
                                <select name="tipo_punto_venta" class="selectpicker form-control" required>
                                    <option>Seleccionar</option>
                                    @include('punto-venta.partials-parametros-punto_ventas')
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Sucursal *</label>
                                <select name="sucursal" id="sucursal" class="selectpicker form-control" required>
                                    @foreach ($sucursales as $sucursal)
                                        <option value="{{ $sucursal->sucursal }}">
                                            {{ $sucursal->nombre }} | {{ $sucursal->domicilio_tributario }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Código CUIS *</label>
                                    <div class="input-group">
                                        <input type="text" name="codigo_cuis" required class="form-control" readonly>
                                        <div class="input-group-append">
                                            <button id="btn_renovar" type="button" class="btn btn-sm btn-warning"
                                                title="Renovar CUIS"><i class="fa fa-refresh"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Vencimiento CUIS *</label>
                                <input type="datetime-local" name="fecha_vigencia_cuis" required class="form-control"
                                    readonly>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <input id="btn_editar" type="submit" value="{{ trans('file.submit') }}"
                                class="btn btn-primary">
                        </div>
                    </form>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>

    <!-- Edit/View Modal -->
    <div id="editComisionistaModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true" class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                {{ Form::open(['route' => ['punto_venta.update', 1], 'method' => 'PUT']) }}
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">Actualizar Punto de Venta</h5>
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
                    <input type="hidden" name="punto_venta_id">
                    <form>
                        <div class="row">
                            <input type="hidden" id="modoSINEdit" name="modoSINEdit" value="1">
                            <div class="form-group col-md-3">
                                <label>Código Punto Venta *</label>
                                <input type="text" id="codigo_punto_venta_edit" name="codigo_punto_venta" required
                                    class="form-control" readonly>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Nombre Punto Venta *</label>
                                <input type="text" name="nombre_punto_venta" required class="form-control">
                            </div>
                            <div class="form-group col-md-3">
                                <label><small> (Modo Conectado a SIN)</small></label>
                                <li class="nav-item">SIAT
                                    <input id="toggle-event-pted" checked type="checkbox" data-toggle="toggle"
                                        data-on="Si" data-off="No" data-onstyle="primary" data-offstyle="secondary">
                                </li>
                            </div>
                            <div class="form-group col-md-12">
                                <label>Descripcion *</label>
                                <input type="text" name="descripcion" required class="form-control">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Sucursal *</label>
                                <select name="sucursal" id="sucursal" class="selectpicker form-control" required>
                                    @foreach ($sucursales as $sucursal)
                                        <option value="{{ $sucursal->sucursal }}">
                                            {{ $sucursal->nombre }} | {{ $sucursal->domicilio_tributario }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label>NIT Comisionista *</label>
                                <input type="text" name="nit_comisionista" required class="form-control">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Nro Contrato *</label>
                                <input type="text" name="numero_contrato" required class="form-control">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Fecha Inicio *</label>
                                <input type="date" name="fecha_inicio" required class="form-control">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Fecha Fin *</label>
                                <input type="date" name="fecha_fin" required class="form-control">
                            </div>
                            <div class="form-group col-md-6"></div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Código CUIS *</label>
                                    <div class="input-group">
                                        <input type="text" name="codigo_cuis" required class="form-control" readonly>
                                        <div class="input-group-append">
                                            <button id="btn_renovar" type="button" class="btn btn-sm btn-warning"
                                                title="Renovar CUIS"><i class="fa fa-refresh"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Vencimiento CUIS *</label>
                                <input type="datetime-local" name="fecha_vigencia_cuis" required class="form-control"
                                    readonly>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <input id="btn_editar" type="submit" value="{{ trans('file.submit') }}"
                                class="btn btn-primary">
                        </div>
                    </form>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
    <!-- Renovacion Masiva Modal -->
    <div id="renovacionMasiva" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">Renovacion Masiva</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                        <span aria-hidden="true">
                            <i class="dripicons-cross"></i></span>
                    </button>
                </div>
                <div class="modal-body">
                    @include('layout.partials.spinner-ajax')
                    <p class="italic">
                        <small>
                            {{ __('file.This process will renovation cuis for all of point sales') }}.
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
                            <input id="btn_renovar_todos" type="button" value="{{ __('file.submit') }}"
                                class="btn btn-primary">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $("ul#siat").siblings('a').attr('aria-expanded', 'true');
        $("ul#siat").addClass("show");
        $("ul#siat #siat-menu-p_venta").addClass("active");

        var punto_venta_id = [];
        $('#toggle-event-pt').prop('checked', true);
        $('#toggle-event-pted').prop('checked', true);
        $('input[name="codigo_punto_venta"]').val(0);
        /**** Modo SIN  */
        $('#toggle-event-pt').change(function() {
            console.log('Toggle Modo SIN: ' + $(this).prop('checked'))
            if ($(this).prop('checked') == true) {
                $('input[name="codigo_punto_venta"]').prop('readonly', true);
                $('input[name="modoSIN"]').val(true);
            } else {
                $('input[name="codigo_punto_venta"]').prop('readonly', false);
                $('input[name="modoSIN"]').val(false);
            }
        })

        $('#toggle-event-pted').change(function() {
            console.log('Toggle Modo SIN: ' + $(this).prop('checked'))
            if ($(this).prop('checked') == true) {
                $('#codigo_punto_venta_edit').prop('readonly', true);
                $('input[name="modoSINEdit"]').val(true);
            } else {
                $('#codigo_punto_venta_edit').prop('readonly', false);
                $('input[name="modoSINEdit"]').val(false);
            }
        })
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function confirmDelete() {
            if (confirm("¿Está seguro de dar de Baja este Punto de Venta?")) {
                return true;
            }
            return false;
        }
        $('.edit-btn').on('click', function() {
            $('#toggle-event-pted').prop('checked', true);
            $("#editModal input[name='punto_venta_id']").val($(this).data('id'));
            $("#editModal input[name='codigo_punto_venta']").val($(this).data('codigo_punto_venta'));
            $("#editModal input[name='nombre_punto_venta']").val($(this).data('nombre_punto_venta'));
            $("#editModal select[name='tipo_punto_venta']").val($(this).data('tipo_punto_venta'));
            $("#editModal input[name='codigo_cuis']").val($(this).data('codigo_cuis'));
            $("#editModal input[name='fecha_vigencia_cuis']").val($(this).data('fecha_vigencia_cuis'));
            $("#editModal input[name='descripcion']").val($(this).data('descripcion'));
            $("#editModal select[name='sucursal']").val($(this).data('sucursal'));
            $('.selectpicker').selectpicker('refresh');
            var modeReady = $(this).data('ready');
            if (modeReady) {
                $("#btn_editar").hide();
                $("#btn_renovar").hide();
                $("#toggle-event-pted").prop('disabled', true);
            } else {
                $("#btn_editar").show();
                $("#btn_renovar").show();
                $("#toggle-event-pted").prop('disabled', false);
            }
        });

        $('#btn_renovar').on('click', function() {
            var id = $("#editModal input[name='punto_venta_id']").val();
            var punto_venta = $("#editModal input[name='codigo_punto_venta']").val();
            var sucursal = $("#editModal select[name='sucursal']").val();
            $.ajax({
                url: "punto_venta/renovar-cuis/" + id + "/" + punto_venta + "/" + sucursal,
                type: "GET",
                dataType: "json",
                async: false,
                success: function(data) {
                    console.log(data);
                    if (data.status) {
                        swal("Mensaje", data.mensaje, "success");
                        location.reload();
                    } else
                        swal("Error", "Error: " + data.mensaje, "error");
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    swal("Error",
                        "Ocurrio un error del servidor, intente de nuevo ó contacte a soporte \n" +
                        "error: " + errorThrown,
                        "error");
                }
            });
        });

        $('#btn_renovar_todos').on('click', function() {
            $("#spinner-div").show();
            $.ajax({
                url: "punto_venta/renovacion-masiva-cuis",
                type: "GET",
                success: function(data) {
                    console.log(data);
                    if (data.status) {
                        $("#spinner-div").hide();
                        swal("Mensaje", data.mensaje, "success");
                        location.reload();
                    } else {
                        $("#spinner-div").hide();
                        swal("Error", "Error: " + data.mensaje, "error");
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    swal("Error",
                        "Ocurrio un error del servidor, intente de nuevo ó contacte a soporte \n" +
                        "error: " + errorThrown,
                        "error");
                }
            });
        });

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
                /*{
                    text: '{{ trans('file.delete') }}',
                    className: 'buttons-delete',
                    action: function(e, dt, node, config) {
                            punto_venta_id.length = 0;
                            $(':checkbox:checked').each(function(i) {
                                if (i) {
                                    punto_venta_id[i - 1] = $(this).closest('tr').data('id');
                                }
                            });
                            if (punto_venta_id.length && confirm("Are you sure want to delete?")) {
                                $.ajax({
                                    type: 'POST',
                                    url: 'punto_venta/deletebyselection',
                                    data: {
                                        departmentIdArray: punto_venta_id
                                    },
                                    success: function(data) {
                                        alert(data);
                                    }
                                });
                                dt.rows({
                                    page: 'current',
                                    selected: true
                                }).remove().draw(false);
                            } else if (!punto_venta_id.length)
                                alert('No department is selected!');
                    }
                },*/
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
