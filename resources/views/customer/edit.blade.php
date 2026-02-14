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
                            <h4>{{ trans('file.Update Customer') }}</h4>
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
                            {!! Form::open(['route' => ['customer.update', $lims_customer_data->id], 'method' => 'put', 'files' => true]) !!}
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane fade show active" id="customer-data">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <input type="hidden" name="customer_group"
                                                    value="{{ $lims_customer_data->customer_group_id }}">
                                                <label>{{ trans('file.Customer Group') }} *</strong> </label>
                                                <select required class="form-control selectpicker" name="customer_group_id">
                                                    @foreach ($lims_customer_group_all as $customer_group)
                                                        <option value="{{ $customer_group->id }}">
                                                            {{ $customer_group->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ trans('file.name') }} *</strong> </label>
                                                <input type="text" name="name"
                                                    value="{{ $lims_customer_data->name }}" required class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ trans('file.Company Name') }} </label>
                                                <input type="text" name="company_name"
                                                    value="{{ $lims_customer_data->company_name }}" class="form-control">
                                            </div>
                                        </div>
                                        {{-- Formularios para tipoDocumento + Razón social --}}
                                        @include('customer.partials-customer-edit')
                                        {{-- Formularios para tipoDocumento + Razón social --}}
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ trans('file.Email') }}</label>
                                                <input type="email" name="email"
                                                    value="{{ $lims_customer_data->email }}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ trans('file.Phone Number') }}</label>
                                                <input type="text" name="phone_number"
                                                    value="{{ $lims_customer_data->phone_number }}" class="form-control">
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
                                                <input type="text" name="tax_no" class="form-control"
                                                    value="{{ $lims_customer_data->tax_no }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ trans('file.Address') }} </label>
                                                <input type="text" name="address"
                                                    value="{{ $lims_customer_data->address }}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ trans('file.City') }} </label>
                                                <input type="text" name="city"
                                                    value="{{ $lims_customer_data->city }}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ trans('file.State') }}</label>
                                                <input type="text" name="state"
                                                    value="{{ $lims_customer_data->state }}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ trans('file.Postal Code') }}</label>
                                                <input type="text" name="postal_code"
                                                    value="{{ $lims_customer_data->postal_code }}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ trans('file.Country') }}</label>
                                                <input type="text" name="country"
                                                    value="{{ $lims_customer_data->country }}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Fecha Nacimiento (Opcional)</label>
                                                <input type="date" name="date_birh"
                                                    value="{{ $lims_customer_data->date_birh }}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Codigo Fijo (Opcional)</label>
                                                <input type="text" name="codigofijo"
                                                    value="{{ $lims_customer_data->codigofijo }}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Nro. Medidor (Opcional)</label>
                                                <input type="text" name="nro_medidor"
                                                    value="{{ $lims_customer_data->nro_medidor }}" class="form-control">
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
                                                <input type="hidden" name="price_type_id"
                                                    value="{{ $lims_customer_data->price_type }}">
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
                                                    value="{{ $lims_customer_data->credit }}" min="0"
                                                    max="9999999" step="10" />
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Habilitar Credito</label>
                                                <input class="mt-2 form-control" type="checkbox" name="is_credit"
                                                    value="{{ $lims_customer_data->is_credit }}"
                                                    @if ($lims_customer_data->is_credit == 1) checked @endif>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Habilitar Tasa Dignidad</label>
                                                <input id="tasadignidad" class="mt-2 form-control" type="checkbox"
                                                    name="is_tasadignidad"
                                                    value="{{ $lims_customer_data->is_tasadignidad }}"
                                                    @if ($lims_customer_data->is_tasadignidad == 1) checked @endif>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Habilitar Descuento Ley:1886</label>
                                                <input id="ley1886" class="mt-2 form-control" type="checkbox"
                                                    name="is_ley1886" value="{{ $lims_customer_data->is_ley1886 }}"
                                                    @if ($lims_customer_data->is_ley1886 == 1) checked @endif>
                                            </div>
                                        </div>
                                        <div id="div_tasadignidad" class="col-md-3">
                                            <div class="form-group">
                                                <label>Porcentaje Tasa Dignidad</label>
                                                <input data-suffix="%" type="number" name="porcentaje_tasadignidad"
                                                    data-decimals="2"
                                                    value="{{ $lims_customer_data->porcentaje_tasadignidad }}"
                                                    min="0" max="100" step="5" />
                                            </div>
                                        </div>
                                        <div id="div_ley1886" class="col-md-3">
                                            <div class="form-group">
                                                <label>Procentaje Ley:1886</label>
                                                <input data-suffix="%" type="number" name="porcentaje_ley1886"
                                                    data-decimals="2"
                                                    value="{{ $lims_customer_data->porcentaje_ley1886 }}" min="0"
                                                    max="100" step="5" />
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
                                                    class="form-control"
                                                    @if ($lims_customer_company) value="{{ $lims_customer_company->fullname }}" @endif />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ trans('file.Company Name') }} </label>
                                                <input type="text" name="company_name" id="company_name"
                                                    class="form-control"
                                                    @if ($lims_customer_company) value="{{ $lims_customer_company->company_name }}" @endif />
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ trans('file.Phone Line') }} </label>
                                                <input type="number" name="phone" id="phone"
                                                    @if ($lims_customer_company) value="{{ $lims_customer_company->phone }}" @endif
                                                    class="form-control" />
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ trans('file.Phone Number') }} </label>
                                                <input type="text" name="telephone" id="telephone"
                                                    @if ($lims_customer_company) value="{{ $lims_customer_company->telephone }}" @endif
                                                    class="form-control" placeholder="5916000000" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ trans('file.Address') }} </label>
                                                <input type="text" name="address_company" id="address_company"
                                                    class="form-control"
                                                    @if ($lims_customer_company) value="{{ $lims_customer_company->address_company }}" @endif />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ trans('file.url') }} </label>
                                                <input type="text" name="url_custom" id="url_custom"
                                                    @if ($lims_customer_company) value="{{ $lims_customer_company->url_custom }}" @endif
                                                    class="form-control" placeholder="http://example.com" />
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ trans('file.lat') }} </label>
                                                <input type="text" name="lat" id="lat" class="form-control"
                                                    placeholder="38.000000"
                                                    @if ($lims_customer_company) value="{{ $lims_customer_company->lat }}" @endif />
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ trans('file.long') }} </label>
                                                <input type="text" name="lon" id="lon" class="form-control"
                                                    placeholder="-72.00000"
                                                    @if ($lims_customer_company) value="{{ $lims_customer_company->lon }}" @endif />
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div id="map" style="width: 100%; height: 500px"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">{{ trans('file.submit') }}</button>
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
        var tasadignidad = {{ $lims_customer_data->is_tasadignidad }};
        var ley1886 = {{ $lims_customer_data->is_ley1886 }};
       
        const latgmap = @if($lims_customer_company && $lims_customer_company->lat != null){{ $lims_customer_company->lat }} @else null @endif;
        const longmap = @if($lims_customer_company && $lims_customer_company->lon != null){{ $lims_customer_company->lon }} @else null @endif;
        var defaulLocation;
        if (latgmap != null && longmap != null) {
            defaulLocation = {
                lat: latgmap,
                lng: longmap
            };
        } else {
            defaulLocation = {
                lat: -17.7835047,
                lng: -63.1774857
            };
        }
        console.log(defaulLocation);
        console.log(tasadignidad + "|" + ley1886);
        var customer_group = $("input[name='customer_group']").val();
        $('select[name=customer_group_id]').val(customer_group);
        $('select[name=sucursal_id]').val({{ $lims_customer_data->sucursal_id }});
        $('select[name=price_type]').val($("input[name='price_type_id']").val());
        $("input[name='credit']").inputSpinner();
        $("input[name='porcentaje_ley1886']").inputSpinner();
        $("input[name='porcentaje_tasadignidad']").inputSpinner();
        if (tasadignidad == 1) {
            $("#div_tasadignidad").show();
        } else {
            $("#div_tasadignidad").hide();
        }
        if (ley1886 == 1) {
            $("#div_ley1886").show();
        } else {
            $("#div_ley1886").hide();
        }
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

            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 12,
                center: defaulLocation,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            });
            var marker = new google.maps.Marker({
                position: defaulLocation,
                animation: google.maps.Animation.BOUNCE
            });

            marker.setMap(map);
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
        if (latgmap != null && longmap != null) {
            //showMap(lat, lon);
        }

        function showMap(latitude, longitude) {
            console.log("This is latitude :" + latitude);
            console.log("This is longitude :" + longitude);

            var myCenter = new google.maps.LatLng(latitude, longitude);
            var marker;

            function initialize() {
                var mapProp = {
                    center: myCenter,
                    zoom: 15,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                };

                var map = new google.maps.Map(document.getElementById("map"), mapProp);

                var marker = new google.maps.Marker({
                    position: myCenter,
                    animation: google.maps.Animation.BOUNCE
                });

                marker.setMap(map);
            }

            initialize();
        }
    </script>
@endsection
