@extends('layout.main') @section('content')
    @if (session()->has('not_permitted'))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert"
                aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
    @endif
    <section class="forms">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4>{{ trans('file.Add Customer') }}</h4>
                        </div>
                        <ul class="nav nav-tabs ml-4 mt-3" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" href="#customer-data" role="tab"
                                    data-toggle="tab">{{ trans('file.Customer') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#customer-company" role="tab"
                                    data-toggle="tab">{{ trans('file.Aditional Data') }}</a>
                            </li>
                        </ul>
                        <div class="card-body">
                            <p class="italic">
                                <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                            </p>
                            {!! Form::open(['route' => 'customer.store', 'method' => 'post', 'files' => true]) !!}
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane fade show active" id="customer-data">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ trans('file.Customer Group') }} *</strong> </label>
                                                <select required class="form-control selectpicker" name="customer_group_id">
                                                    @foreach ($lims_customer_group_all as $customer_group)
                                                        <option value="{{ $customer_group->id }}">
                                                            {{ $customer_group->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ trans('file.name') }} *</strong> </label>
                                                <input type="text" name="name" required class="form-control">
                                                @if ($errors->has('name'))
                                                    <span>
                                                        <strong>{{ $errors->first('name') }}</strong>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ trans('file.Company Name') }}</label>
                                                <input type="text" name="company_name" class="form-control">
                                            </div>
                                        </div>

                                        {{-- Formularios para tipoDocumento + Razón social --}}
                                        @include('customer.partials-customer')
                                        {{-- Formularios para tipoDocumento + Razón social --}}

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ trans('file.Email') }}</label>
                                                <input type="email" name="email" placeholder="example@example.com"
                                                    class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ trans('file.Phone Number') }}</label>
                                                <input type="text" name="phone_number" class="form-control">
                                                @if ($errors->has('phone_number'))
                                                    <span>
                                                        <strong>{{ $errors->first('phone_number') }}</strong>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ trans('file.Tax Number') }}</label>
                                                <input type="text" name="tax_no" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ trans('file.Address') }}</label>
                                                <input type="text" name="address" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ trans('file.City') }} *</label>
                                                <input type="text" name="city" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ trans('file.State') }}</label>
                                                <input type="text" name="state" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ trans('file.Postal Code') }}</label>
                                                <input type="text" name="postal_code" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ trans('file.Country') }}</label>
                                                <input type="text" name="country" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Fecha Nacimiento (Opcional)</label>
                                                <input type="date" name="date_birh" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Codigo Fijo (Opcional)</label>
                                                <input type="text" name="codigofijo" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Nro. Medidor (Opcional)</label>
                                                <input type="text" name="nro_medidor" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Sucursal (Opcional)</strong> </label>
                                                <select class="form-control selectpicker" name="sucursal_id">
                                                    <option value="">Sin Sucursal</option>
                                                    @foreach ($lims_sucursal_all as $sucursal)
                                                        <option value="{{ $sucursal->sucursal }}">
                                                            {{ $sucursal->sucursal }} - {{ $sucursal->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Tipo de Precio *</strong> </label>
                                                <select required class="form-control selectpicker" name="price_type">
                                                    <option value="0">Precio por Defecto</option>
                                                    <option value="1">Precio A</option>
                                                    <option value="2">Precio B</option>
                                                    <option value="3">Precio C</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Limite de Credito</label>
                                                <input data-suffix="Bs" type="number" name="credit" data-decimals="2"
                                                    value="0" min="0" max="9999999" step="10" />
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Habilitar Credito</label>
                                                <input class="mt-2 form-control" type="checkbox" name="is_credit"
                                                    value="0">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Habilitar Tasa Dignidad</label>
                                                <input id="tasadignidad" class="mt-2 form-control" type="checkbox"
                                                    name="is_tasadignidad" value="0">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Habilitar Descuento Ley:1886</label>
                                                <input id="ley1886" class="mt-2 form-control" type="checkbox"
                                                    name="is_ley1886" value="0">
                                            </div>
                                        </div>
                                        <div id="div_tasadignidad" class="col-md-3">
                                            <div class="form-group">
                                                <label>Porcentaje Tasa Dignidad</label>
                                                <input data-suffix="%" type="number" name="porcentaje_tasadignidad"
                                                    data-decimals="2" value="25" min="0" max="100"
                                                    step="5" />
                                            </div>
                                        </div>
                                        <div id="div_ley1886" class="col-md-3">
                                            <div class="form-group">
                                                <label>Procentaje Ley:1886</label>
                                                <input data-suffix="%" type="number" name="porcentaje_ley1886"
                                                    data-decimals="2" value="20" min="0" max="100"
                                                    step="5" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div role="tabpanel" class="tab-pane fade" id="customer-company">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ trans('file.Fullname') }} *</strong> </label>
                                                <input type="text" name="fullname" id="fullname"
                                                    class="form-control" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ trans('file.Company Name') }} </label>
                                                <input type="text" name="company_name" id="company_name"
                                                    class="form-control" />
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ trans('file.Phone Line') }} </label>
                                                <input type="number" name="phone" id="phone"
                                                    class="form-control" />
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ trans('file.Phone Number') }} </label>
                                                <input type="text" name="telephone" id="telephone"
                                                    class="form-control" placeholder="5916000000" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ trans('file.Address') }} </label>
                                                <input type="text" name="address_company" id="address_company"
                                                    class="form-control" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ trans('file.url') }} </label>
                                                <input type="text" name="url_custom" id="url_custom"
                                                    class="form-control" placeholder="http://example.com" />
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ trans('file.lat') }} </label>
                                                <input type="text" name="lat" id="lat" class="form-control"
                                                    placeholder="38.000000" />
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ trans('file.long') }} </label>
                                                <input type="text" name="lon" id="lon" class="form-control"
                                                    placeholder="-72.00000" />
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div id="map" style="width: 100%; height: 500px"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <input type="hidden" name="pos" value="0">
                                    <button type="submit" class="btn btn-primary">{{ trans('file.submit') }}</button>
                                </div>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
    <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCSZQsIN0kowDXxPWJ9bqLvlyKvzL6t7Dw&callback=initMap&v=weekly"
        defer></script>
    <script type="text/javascript">
        $("ul#people").siblings('a').attr('aria-expanded', 'true');
        $("ul#people").addClass("show");
        $("ul#people #customer-create-menu").addClass("active");
        $("input[name='credit']").inputSpinner();
        $("input[name='porcentaje_tasadignidad']").inputSpinner();
        $("input[name='porcentaje_ley1886']").inputSpinner();
        $("#div_tasadignidad").hide();
        $("#div_ley1886").hide();
        $("#tasadignidad").on("change", function() {
            if ($(this).is(':checked')) {
                $("#div_tasadignidad").show(300);
            } else {
                $("#div_tasadignidad").hide(300);
            }
        });
        $("#ley1886").on("change", function() {
            if ($(this).is(':checked')) {
                $("#div_ley1886").show(300);
            } else {
                $("#div_ley1886").hide(300);
            }
        });
        var marker;

        function initMap() {
            const defaulLocation = {
                lat: -17.7835047,
                lng: -63.1774857
            };
            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 12,
                center: defaulLocation,
            });
            // Create the initial InfoWindow.
            let infoWindow = new google.maps.InfoWindow({
                content: "Click en el mapa para obtener coordenadas!",
                position: defaulLocation,
            });

            infoWindow.open(map);
            // Configure the click listener.
            map.addListener("click", (mapsMouseEvent) => {
                // Close the current InfoWindow.
                infoWindow.close();
                // Create a new InfoWindow.
                infoWindow = new google.maps.InfoWindow({
                    position: mapsMouseEvent.latLng,
                });
                infoWindow.setContent(
                    JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2)
                );
                console.log("Latitude: " + mapsMouseEvent.latLng.lat() + " " + ", longitude: " + mapsMouseEvent
                    .latLng.lng()); // Get separate lat long.
                $('#lat').val(mapsMouseEvent.latLng.lat()); // Get separate lat
                $('#lon').val(mapsMouseEvent.latLng.lng()); // Get separate lat
                infoWindow.open(map);
            });
        }

        window.initMap = initMap;
    </script>
@endsection
