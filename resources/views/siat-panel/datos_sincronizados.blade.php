@extends('layout.main')
@section('content')

@include('layout.partials.session-flash')

<section>
    {{-- Navs siat --}}
    @include('layout.partials.navs-siat')
    {{-- Navs siat --}}

    <div class="container fluid mt-4">
        <div class="form-row form-group">
            <div class="input-group col-3">
                <label>Sucursal</label>
                <select id="sucursales_id" name="sucursal" class="form-control selectpicker" data-live-search="true" data-live-search-style="begins" title="Seleccionar sucursal...">
                    <option value="">-- Seleccionar --</option>
                    @foreach ($sucursales as $s)
                        <option value="{{ $s->sucursal }}">
                            {{ $s->sucursal }}.- {{ $s->nombre }} | {{ $s->domicilio_tributario }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="input-group col-3">
                <label>Punto de Venta</label>
                <select id="punto_venta" name="codigo_punto_venta" class="form-control selectpicker" title="Punto Venta...">
                    <option value="">-- Seleccionar --</option>
                </select>
            </div>
            <div class="input-group col-3">
                <label>Tipo de Dato</label>
                <select id="filtro_parametro" name="parametro" class="form-control selectpicker" data-live-search="true" title="Seleccionar tipo...">
                    <option value="">-- Seleccionar --</option>
                    @foreach ($operaciones as $ope)
                        <option value="{{ $ope['operacion'] }}">{{ $ope['descripcion'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-2">
                <label>NIT</label>
                <input type="text" name="nit" class="form-control" disabled value="{{ $nit[0] }}">
            </div>
        </div>

        <div class="table-responsive">
            <table id="product-table" class="table">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
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
        'columnDefs': [
            {
                "orderable": false,
                'targets': [0]
            },
            {
                'render': function(data, type, row, meta){
                    if(type === 'display'){
                        data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
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
        'select': { style: 'multi', selector: 'td:first-child'},
        'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, "All"]],
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
        if (!sucursal || !pv || !parametro) return;

        $.ajax({
            url: '{{ url("siat_panel/listar-nit-data") }}',
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                parametro: parametro,
                sucursal: sucursal,
                codigo_punto_venta: pv,
            },
            dataType: "json",
            success: function(res) {
                table.clear().destroy();
                var tbody = $('#tblEntidades');
                tbody.empty();
                if (res.entidades && res.entidades.length > 0) {
                    $.each(res.entidades, function(i, e) {
                        var estado = e.estado || '';
                        var badge = (estado === 'A' || estado === 'S')
                            ? '<div class="badge badge-success">' + (estado === 'A' ? 'Activo' : 'Sincronizado') + '</div>'
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
            }
        });
    }

    $('#sucursales_id').on('change', function() {
        var sucursal_ID = $(this).val();
        $('select[name="codigo_punto_venta"]').empty().append('<option value="">-- Seleccionar --</option>');
        $('.selectpicker').selectpicker('refresh');
        if (sucursal_ID) {
            $.ajax({
                url: '{{ url("siat_panel/p_venta") }}/' + sucursal_ID,
                type: "GET",
                dataType: "json",
                success: function(data) {
                    $('select[name="codigo_punto_venta"]').append('<option value="' + data.codigo_punto_venta + '">' + data.nombre_punto_venta + '</option>');
                    $('.selectpicker').selectpicker('refresh');
                    cargarDatos();
                },
            });
        }
    });

    $('#punto_venta, #filtro_parametro').on('change', function() {
        cargarDatos();
    });
</script>

<script>
    $("ul#setting #siat").siblings('a').attr('aria-expanded','true');
    $("ul#setting #siat").addClass("show");
    $("ul#setting #nav-siat #datos").addClass("active");
</script>

@include('layout.partials.sweet-alert-siat.sweet-siat')
@endsection