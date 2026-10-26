@extends('layout.main')
@section('content')

@include('layout.partials.session-flash')

<section class="forms">
    {{-- Navs siat --}}
    @include('layout.partials.navs-siat')
    {{-- Navs siat --}}

    <div class="container-fluid mt-3">
        <div class="card">
            <div class="card-header mt-2">
                <h4 class="text-center"><i class="dripicons-cloud"></i> Datos Sincronizados SIAT</h4>
            </div>
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <label><strong>Sucursal *</strong></label>
                            <select id="sucursales_id" name="sucursal" class="form-control selectpicker" data-live-search="true" title="Seleccionar sucursal...">
                                @foreach ($sucursales as $s)
                                    <option value="{{ $s->sucursal }}" {{ $loop->first ? 'selected' : '' }}>
                                        {{ $s->sucursal }} .- {{ $s->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <label><strong>Punto de Venta *</strong></label>
                            <select id="punto_venta" name="codigo_punto_venta" class="form-control selectpicker" data-live-search="true" title="Punto Venta...">
                                @if (isset($puntos_venta) && count($puntos_venta) > 0)
                                    @foreach ($puntos_venta as $pv)
                                        <option value="{{ $pv->codigo_punto_venta }}" {{ $loop->first ? 'selected' : '' }}>
                                            {{ $pv->codigo_punto_venta }} - {{ $pv->nombre_punto_venta }}
                                        </option>
                                    @endforeach
                                @else
                                    <option value="0" selected>0 - Punto de Venta Principal (0)</option>
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <label><strong>Tipo de Dato *</strong></label>
                            <select id="filtro_parametro" name="parametro" class="form-control selectpicker" data-live-search="true" title="Seleccionar tipo...">
                                @foreach ($operaciones as $ope)
                                    <option value="{{ $ope['operacion'] }}" {{ $loop->first ? 'selected' : '' }}>
                                        {{ $ope['descripcion'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-2">
                            <label><strong>NIT Emisor</strong></label>
                            <input type="text" name="nit" class="form-control" disabled value="{{ $nit[0] ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group mb-2">
                            <button type="button" id="btnBuscar" class="btn btn-primary btn-block" onclick="cargarDatos()" title="Buscar datos sincronizados">
                                <i class="dripicons-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-2">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="product-table" class="table table-hover">
                        <thead>
                            <tr>
                                <th class="not-exported">#</th>
                                <th>ID</th>
                                <th>Descripción</th>
                                <th>Clave</th>
                                <th>Valor</th>
                                <th>Empresa</th>
                                <th>NIT</th>
                                <th>Estado</th>
                                <th>Fecha Sincronización</th>
                                <th>Fecha Alta</th>
                            </tr>
                        </thead>
                        <tbody id="tblEntidades">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">
    $("ul#siat").siblings('a').attr('aria-expanded','true');
    $("ul#siat").addClass("show");
    $("ul#siat #siat-menu-panel").addClass("active");

    var dtConfig = {
        "order": [],
        'language': {
            'lengthMenu': '_MENU_ {{trans("file.records per page")}}',
            "info": '<small>{{trans("file.Showing")}} _START_ - _END_ (_TOTAL_)</small>',
            "search": '{{trans("file.Search")}}',
            'paginate': {
                'previous': '<i class="dripicons-chevron-left"></i>',
                'next': '<i class="dripicons-chevron-right"></i>'
            }
        },
        'lengthMenu': [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        dom: '<"row"lfB>rtip',
        buttons: [
            {
                extend: 'pdf',
                text: '{{trans("file.PDF")}}',
                exportOptions: { columns: ':visible:Not(.not-exported)', rows: ':visible' },
                footer: true
            },
            {
                extend: 'csv',
                text: '{{trans("file.CSV")}}',
                exportOptions: { columns: ':visible:Not(.not-exported)', rows: ':visible' },
                footer: true
            },
            {
                extend: 'print',
                text: '{{trans("file.Print")}}',
                exportOptions: { columns: ':visible:Not(.not-exported)', rows: ':visible' },
                footer: true
            },
            {
                extend: 'colvis',
                text: '{{trans("file.Column visibility")}}',
                columns: ':gt(0)'
            },
        ],
    };

    var table = $('#product-table').DataTable(dtConfig);

    function cargarDatos() {
        var sucursal = $("select[name='sucursal']").val();
        var pv = $("select[name='codigo_punto_venta']").val();
        var parametro = $("select[name='parametro']").val();

        if (!parametro) {
            swal("Aviso", "Seleccione un Tipo de Dato para buscar", "info");
            return;
        }

        var $btn = $('#btnBuscar');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> ...');

        $.ajax({
            url: '{{ url("siat_panel/listar-nit-data") }}',
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                parametro: parametro,
                sucursal: sucursal !== null && sucursal !== undefined && sucursal !== '' ? sucursal : 0,
                codigo_punto_venta: pv !== null && pv !== undefined && pv !== '' ? pv : 0,
            },
            dataType: "json",
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="dripicons-search"></i> Buscar');
                table.clear().destroy();
                var tbody = $('#tblEntidades');
                tbody.empty();
                if (res.entidades && res.entidades.length > 0) {
                    $.each(res.entidades, function(i, e) {
                        var estado = e.estado || '';
                        var badge = (estado === 'A' || estado === 'S' || estado === 1 || estado === '1')
                            ? '<div class="badge badge-success">' + (estado === 'A' || estado === 1 || estado === '1' ? 'Activo' : 'Sincronizado') + '</div>'
                            : '<div class="badge badge-warning text-white">' + (estado || 'N/A') + '</div>';
                        tbody.append('<tr>' +
                            '<td>' + (i + 1) + '</td>' +
                            '<td>' + (e.id || '') + '</td>' +
                            '<td>' + (e.descripcion || '') + '</td>' +
                            '<td>' + (e.clave || '') + '</td>' +
                            '<td>' + (e.valor || '') + '</td>' +
                            '<td>' + ((e.empresa && e.empresa.nombre) || '') + '</td>' +
                            '<td>' + ((e.empresa && e.empresa.nit) || '') + '</td>' +
                            '<td>' + badge + '</td>' +
                            '<td>' + (e.fecha_sincronizacion || '') + '</td>' +
                            '<td>' + (e.fecha_alta || '') + '</td>' +
                            '</tr>');
                    });
                }
                table = $('#product-table').DataTable(dtConfig);
            },
            error: function(xhr, status, error) {
                $btn.prop('disabled', false).html('<i class="dripicons-search"></i> Buscar');
                console.error("Error al cargar datos sincronizados:", error, xhr.responseText);
                swal("Error", "No se pudieron obtener los datos sincronizados.", "error");
            }
        });
    }

    $('#sucursales_id').on('change', function() {
        var sucursal_ID = $(this).val();
        var $pv = $('select[name="codigo_punto_venta"]');
        $pv.empty();
        if (sucursal_ID !== '' && sucursal_ID !== null && sucursal_ID !== undefined) {
            $.ajax({
                url: '{{ url("siat_panel/p_venta") }}/' + sucursal_ID,
                type: "GET",
                dataType: "json",
                success: function(data) {
                    $pv.empty();
                    if (Array.isArray(data)) {
                        $.each(data, function(idx, item) {
                            $pv.append('<option value="' + item.codigo_punto_venta + '"' + (idx === 0 ? ' selected' : '') + '>' + item.codigo_punto_venta + ' - ' + item.nombre_punto_venta + '</option>');
                        });
                    } else if (data && data.codigo_punto_venta !== undefined) {
                        $pv.append('<option value="' + data.codigo_punto_venta + '" selected>' + data.codigo_punto_venta + ' - ' + data.nombre_punto_venta + '</option>');
                    } else {
                        $pv.append('<option value="0" selected>0 - Punto de Venta Principal (0)</option>');
                    }
                    $pv.selectpicker('refresh');
                    cargarDatos();
                },
                error: function() {
                    $pv.empty().append('<option value="0" selected>0 - Punto de Venta Principal (0)</option>');
                    $pv.selectpicker('refresh');
                }
            });
        } else {
            $pv.append('<option value="0" selected>0 - Punto de Venta Principal (0)</option>');
            $pv.selectpicker('refresh');
        }
    });

    $('#punto_venta, #filtro_parametro').on('change', function() {
        cargarDatos();
    });

    $(document).ready(function() {
        if ($('#filtro_parametro').val()) {
            cargarDatos();
        }
    });
</script>

<script>
    $("ul#setting #siat").siblings('a').attr('aria-expanded','true');
    $("ul#setting #siat").addClass("show");
    $("ul#setting #nav-siat #datos").addClass("active");
</script>

@include('layout.partials.sweet-alert-siat.sweet-siat')
@endsection
