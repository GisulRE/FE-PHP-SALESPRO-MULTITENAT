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
                <h4 class="text-center"><i class="dripicons-view-list"></i> Paramétricas SIAT</h4>
            </div>
            <div class="card-body">
                <div class="row align-items-end mb-3">
                    <div class="col-md-5">
                        <div class="form-group mb-2">
                            <label><strong>Facturador / Sucursal *</strong></label>
                            <select id="id_select" name="id_select" class="form-control selectpicker" data-live-search="true" title="Seleccione Facturador...">
                                @foreach ($billers as $item)
                                    <option value="{{ $item->id }}" {{ (isset($selected_biller_id) && $selected_biller_id == $item->id) ? 'selected' : '' }}>
                                        {{ $item->name }} {{ $item->company_name }} | {{ optional($item->almacen)->name }} | Sucursal: {{ optional(optional($item->almacen)->sucursal)->sucursal ?? 0 }} - Punto: {{ optional($item->getpuntoventa)->codigo_punto_venta ?? 0 }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-2">
                            <label><strong>Tipo de Clasificador (Opcional)</strong></label>
                            <select id="tipo_select" name="tipo" class="form-control selectpicker" data-live-search="true" title="Todos los Tipos...">
                                <option value="">-- Todos los Clasificadores --</option>
                                @if(isset($tipos))
                                    @foreach($tipos as $tipo)
                                        <option value="{{ $tipo }}" {{ (isset($selected_tipo) && $selected_tipo == $tipo) ? 'selected' : '' }}>
                                            {{ $tipo }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <button type="button" id="btnBuscar" class="btn btn-primary btn-block">
                                <i class="dripicons-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="product-table" class="table table-hover table-bordered table-striped" style="width: 100%;">
                        <thead class="thead-light">
                            <tr>
                                <th class="not-exported" style="width: 50px;">#</th>
                                <th style="width: 100px;">Código</th>
                                <th style="width: 200px;">Tipo Clasificador</th>
                                <th>{{ trans('file.Description') }}</th>
                                <th style="width: 150px;">Sucursal</th>
                                <th style="width: 150px;">Punto de Venta</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($parametricas as $key => $parametrica)
                            <tr data-id="{{ $parametrica->id }}">
                                <td>{{ $key + 1 }}</td>
                                <td><strong>{{ $parametrica->codigo_clasificador }}</strong></td>
                                <td><span class="badge badge-info" style="font-size: 0.85rem;">{{ $parametrica->tipo_clasificador }}</span></td>
                                <td>{{ $parametrica->descripcion }}</td>
                                <td><span class="badge badge-light border">{{ $parametrica->getSucursal() }}</span></td>
                                <td><span class="badge badge-light border">{{ $parametrica->getPuntoVenta() }}</span></td>
                            </tr>
                            @endforeach
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
    $("ul#nav-siat #par").addClass("active");

    $(document).ready(function() {
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#product-table')) {
            $('#product-table').DataTable().destroy();
        }

        $('#product-table').DataTable({
            "order": [],
            'pageLength': 25,
            'language': {
                'lengthMenu': '_MENU_ {{ trans("file.records per page") }}',
                "info":      '<small>{{ trans("file.Showing") }} _START_ - _END_ (_TOTAL_)</small>',
                "search":  '{{ trans("file.Search") }}',
                'paginate': {
                    'previous': '<i class="dripicons-chevron-left"></i>',
                    'next': '<i class="dripicons-chevron-right"></i>'
                }
            },
            'lengthMenu': [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
            dom: '<"row"<"col-md-6"lB><"col-md-6"f>r>tip',
            buttons: [
                {
                    extend: 'pdf',
                    text: '<i class="fa fa-file-pdf-o"></i> {{ trans("file.PDF") }}',
                    className: 'btn btn-sm btn-outline-danger',
                    exportOptions: {
                        columns: ':visible:Not(.not-exported)',
                        rows: ':visible'
                    }
                },
                {
                    extend: 'csv',
                    text: '<i class="fa fa-file-excel-o"></i> {{ trans("file.CSV") }}',
                    className: 'btn btn-sm btn-outline-success',
                    exportOptions: {
                        columns: ':visible:Not(.not-exported)',
                        rows: ':visible'
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fa fa-print"></i> {{ trans("file.Print") }}',
                    className: 'btn btn-sm btn-outline-primary',
                    exportOptions: {
                        columns: ':visible:Not(.not-exported)',
                        rows: ':visible'
                    }
                },
                {
                    extend: 'colvis',
                    text: '<i class="fa fa-eye"></i> {{ trans("file.Column visibility") }}',
                    className: 'btn btn-sm btn-outline-secondary',
                    columns: ':gt(0)'
                }
            ]
        });

        $('#btnBuscar').on('click', function() {
            var billerId = $('#id_select').val();
            var tipo = $('#tipo_select').val();
            var baseUrl = '{{ url("siat_panel/par") }}';
            var targetUrl = billerId ? (baseUrl + '/' + billerId) : baseUrl;
            if (tipo) {
                targetUrl += '?tipo=' + encodeURIComponent(tipo);
            }
            window.location.href = targetUrl;
        });

        $('#id_select, #tipo_select').on('change', function() {
            // Permite búsqueda automática al cambiar filtros
            $('#btnBuscar').trigger('click');
        });
    });
</script>

@include('layout.partials.sweet-alert-siat.sweet-siat')
@endsection
