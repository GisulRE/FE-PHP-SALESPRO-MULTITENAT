@extends('layout.main')
@section('content')
    @include('layout.partials.session-flash')

    <section>
        <div class="container-fluid">
            <div class="row">
                <div class="container-fluid">
                    <div class="card-header mt-2">
                        <h3 class="text-center">Lista de credenciales CAFC</h3>
                    </div>
                    <div class="row mb-12">
                        <button type="button" class="btn btn-info ml-4" data-toggle="modal" data-target="#addCredencialCafc">
                            <i class="dripicons-plus"></i>Añadir CAFC
                        </button>
                    </div>
                </div>
            </div>

            <hr>
            <div class="table-responsive">
                <table id="modo-table" class="table">
                    <thead>
                        <tr>
                            <th>Año</th>
                            <th>Sucursal</th>
                            <th>Punto de Venta</th>
                            <th>Tipo Factura</th>
                            <th>Rango</th>
                            <th>Correlativo</th>
                            <th>Estado </th>
                            <th class="not-exported">{{ trans('file.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lista_cafc as $key => $item_cafc)
                            <tr data-id="{{ $item_cafc->id }}">
                                <td>{{ $item_cafc->año }}</td>
                                <td>{{ $item_cafc->getNombreSucursal() }}</td>
                                <td>{{ $item_cafc->getNombrePuntoVenta() }}</td>
                                <td>{{ $item_cafc->tipo_factura }}</td>
                                <td>{{ 'Del ' . $item_cafc->nro_min . ', al ' . $item_cafc->nro_max }}</td>
                                <td>{{ $item_cafc->correlativo_factura }}</td>
                                <td>
                                    @if ($item_cafc->is_active)
                                        <div class="badge badge-success">Activo</div>
                                    @else
                                        <div class="badge badge-warning">Inactivo</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-default btn-sm dropdown-toggle"
                                            data-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false">{{ trans('file.action') }}
                                            <span class="caret"></span>
                                            <span class="sr-only">Toggle Dropdown</span>
                                        </button>
                                        <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default"
                                            user="menu">
                                            {{ Form::open(['route' => ['credencial-cafc.destroy', $item_cafc->id], 'method' => 'DELETE']) }}
                                            <li>
                                                <button type="submit" class="btn btn-link"
                                                    onclick="return confirmEstado()">
                                                    <i class="dripicons-swap"></i>
                                                    Cambiar Estado
                                                </button>
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

        </div>

    </section>
    <!-- add CredencialesCAFC modal -->
    <div id="addCredencialCafc" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                {!! Form::open(['route' => 'credencial-cafc.store', 'method' => 'post']) !!}
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">Registrar Cafc</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <p class="italic">
                        <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                    </p>
                    <div class="row">
                        <div class="form-group col-4">
                            <label>Tipo Factura *</label>
                            <select name="tipo_factura" id="tipo_factura" class="form-control" title="Seleccionar..."
                                required>
                                <option value="compra-venta">Compra-Venta</option>
                                <option value="alquiler">Alquiler</option>
                                <option value="servicio-basico">Servicio Básico</option>
                            </select>
                        </div>
                        <div class="form-group col-4">
                            <label>Año gestión *</label>
                            <select name="año" class="form-control" title="Seleccionar año..." required>
                                @for ($i = 2000; $i <= 2100; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group col-4">
                            <label>Código cafc *</label>
                            <input type="text" class="form-control" name="codigo_cafc" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col">
                            <label>Fecha emisión * </label>
                            <input type="datetime-local" name="fecha_emision" class="form-control"
                                value="{{ $fecha_actual }}">
                        </div>
                        <div class="form-group col">
                            <label>Fecha vigencia * </label>
                            <input type="datetime-local" name="fecha_vigencia" class="form-control"
                                value="{{ $fecha_actual }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col">
                            <label>Nro. Mínimo * </label>
                            <input type="number" class="form-control" name="nro_min" min="0" required>
                        </div>
                        <div class="form-group col">
                            <label>Nro. Máximo * </label>
                            <input type="number" class="form-control" name="nro_max" min="0" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col">
                            <label>Sucursal *</label>
                            <select name="sucursal" id="sucursal" class="form-control" title="Seleccionar..." required>
                                @foreach ($sucursales as $sucursal)
                                    <option value="{{ $sucursal->sucursal }}">{{ $sucursal->sucursal }} |
                                        {{ $sucursal->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col">
                            <label>Codigo Punto Venta *</label>
                            <select name="codigo_punto_venta" id="codigo_punto_venta" class="form-control selectpicker"
                                title="Seleccionar..." required>
                            </select>
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <input type="submit" value="{{ trans('file.submit') }}" class="btn btn-primary">
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>

    <script>
        $("ul#siat").siblings('a').attr('aria-expanded', 'true');
        $("ul#siat").addClass("show");
        $("ul#siat #cafc-menu").addClass("active");


        $('#modo-table').DataTable({
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
                [5, 10, 25, 50, -1],
                [5, 10, 25, 50, "All"]
            ],
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

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
        });
    </script>
@endsection
