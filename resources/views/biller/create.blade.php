@extends('layout.main') @section('content')
    <section class="forms">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4>{{ trans('file.Add Biller') }}</h4>
                        </div>
                        <div class="card-body">
                            <p class="italic">
                                <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                            </p>
                            {!! Form::open(['route' => 'biller.store', 'method' => 'post', 'files' => true]) !!}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ trans('file.name') }} *</strong> </label>
                                        <input type="text" name="name" required class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ trans('file.Image') }}</label>
                                        <input type="file" name="image" class="form-control">
                                        @if ($errors->has('image'))
                                            <span>
                                                <strong>{{ $errors->first('image') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ trans('file.Company Name') }} *</label>
                                        <input type="text" name="company_name" required class="form-control">
                                        @if ($errors->has('company_name'))
                                            <span>
                                                <strong>{{ $errors->first('company_name') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ trans('file.VAT Number') }}</label>
                                        <input type="text" name="vat_number" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ trans('file.Email') }} *</label>
                                        <input type="email" name="email" placeholder="example@example.com" required
                                            class="form-control">
                                        @if ($errors->has('email'))
                                            <span>
                                                <strong>{{ $errors->first('email') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ trans('file.Phone Number') }} *</label>
                                        <input type="text" name="phone_number" required class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ trans('file.Address') }} *</label>
                                        <input type="text" name="address" required class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ trans('file.City') }} *</label>
                                        <input type="text" name="city" required class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ trans('file.State') }}</label>
                                        <input type="text" name="state" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6" style="display: none">
                                    <div class="form-group">
                                        <label>{{ trans('file.Postal Code') }}</label>
                                        <input type="hidden" name="postal_code" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ trans('file.Country') }}</label>
                                        <input type="text" name="country" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label> {{ trans('file.Account') }} Efectivo *</label>
                                        <select required class="form-control selectpicker" name="account_id">
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
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label> {{ trans('file.Account') }} Tarjeta *</label>
                                        <select required class="form-control selectpicker" name="account_id_tarjeta"
                                            title="Seleccione una cuenta...">
                                            @foreach ($lims_account_list as $account)
                                                <option value="{{ $account->id }}">{{ $account->name }}
                                                    [{{ $account->account_no }}]</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label> {{ trans('file.Account') }} QR *</label>
                                        <select required class="form-control selectpicker" name="account_id_qr"
                                            title="Seleccione una cuenta...">
                                            @foreach ($lims_account_list as $account)
                                                <option value="{{ $account->id }}">{{ $account->name }}
                                                    [{{ $account->account_no }}]</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label> {{ trans('file.Account') }} Deposito *</label>
                                        <select required class="form-control selectpicker" name="account_id_deposito"
                                            title="Seleccione una cuenta...">
                                            @foreach ($lims_account_list as $account)
                                                <option value="{{ $account->id }}">{{ $account->name }}
                                                    [{{ $account->account_no }}]</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label> {{ trans('file.Account') }} Cheque *</label>
                                        <select required class="form-control selectpicker" name="account_id_cheque"
                                            title="Seleccione una cuenta...">
                                            @foreach ($lims_account_list as $account)
                                                <option value="{{ $account->id }}">{{ $account->name }}
                                                    [{{ $account->account_no }}]</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label> {{ trans('file.Account') }} por Cobrar *</label>
                                        <select required class="form-control selectpicker" name="account_id_receivable"
                                            title="Seleccione una cuenta...">
                                            @foreach ($lims_account_list as $account)
                                                <option value="{{ $account->id }}">{{ $account->name }}
                                                    [{{ $account->account_no }}]</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label> {{ trans('file.Account') }} Gift Cards *</label>
                                        <select required class="form-control selectpicker" name="account_id_giftcard"
                                            title="Seleccione una cuenta...">
                                            @foreach ($lims_account_list as $account)
                                                <option value="{{ $account->id }}">{{ $account->name }}
                                                    [{{ $account->account_no }}]</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label> {{ trans('file.Account') }} Vale *</label>
                                        <select required class="form-control selectpicker" name="account_id_vale"
                                            title="Seleccione una cuenta...">
                                            @foreach ($lims_account_list as $account)
                                                <option value="{{ $account->id }}">{{ $account->name }}
                                                    [{{ $account->account_no }}]</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label> {{ trans('file.Account') }} Otros *</label>
                                        <select required class="form-control selectpicker" name="account_id_otros"
                                            title="Seleccione una cuenta...">
                                            @foreach ($lims_account_list as $account)
                                                <option value="{{ $account->id }}">{{ $account->name }}
                                                    [{{ $account->account_no }}]</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label> {{ trans('file.Account') }} Pago Posterior *</label>
                                        <select required class="form-control selectpicker" name="account_id_pagoposterior"
                                            title="Seleccione una cuenta...">
                                            @foreach ($lims_account_list as $account)
                                                <option value="{{ $account->id }}">{{ $account->name }}
                                                    [{{ $account->account_no }}]</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label> {{ trans('file.Account') }} Transferencia Bancaria *</label>
                                        <select required class="form-control selectpicker"
                                            name="account_id_transferenciabancaria" title="Seleccione una cuenta...">
                                            @foreach ($lims_account_list as $account)
                                                <option value="{{ $account->id }}">{{ $account->name }}
                                                    [{{ $account->account_no }}]</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label> {{ trans('file.Account') }} Switf *</label>
                                        <select required class="form-control selectpicker" name="account_id_swift"
                                            title="Seleccione una cuenta...">
                                            @foreach ($lims_account_list as $account)
                                                <option value="{{ $account->id }}">{{ $account->name }}
                                                    [{{ $account->account_no }}]</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ trans('file.Default Customer') }} *</label>
                                        <select required name="customer_id" id="customer_id"
                                            class="selectpicker form-control" data-live-search="true"
                                            data-live-search-style="begins" title="Select customer...">
                                            @foreach ($lims_customer_list as $customer)
                                                <option value="{{ $customer->id }}">
                                                    {{ $customer->name . ' (' . $customer->phone_number . ')' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ trans('file.Default Warehouse') }} *</label>
                                        <select required name="warehouse_id" class="selectpicker form-control"
                                            data-live-search="true" data-live-search-style="begins"
                                            title="Select warehouse...">
                                            @foreach ($lims_warehouse_list as $warehouse)
                                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ trans('file.Secondary Warehouse') }} <small>(Opcional)</small></label>
                                        <select class="selectpicker form-control" name="warehouses[]" id="warehouses"
                                            title="Seleccione uno o más..." multiple>
                                            @foreach ($lims_warehouse_list as $warehouse)
                                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Sucursal *</label>
                                    <select name="sucursal" id="sucursal" class="selectpicker form-control"
                                        title="Seleccionar...">
                                        @foreach ($sucursales as $sucursal)
                                            <option value="{{ $sucursal->sucursal }}">{{ $sucursal->sucursal }} |
                                                {{ $sucursal->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Codigo Punto Venta *</label>
                                    <select required name="punto_venta_siat" id="punto_venta_siat"
                                        class="form-control selectpicker" title="Seleccionar...">
                                    </select>
                                </div>
                                <div class="col-md-12 mt-3">
                                    <div class="form-group mt-4">
                                        <input required type="submit" value="{{ trans('file.submit') }}"
                                            class="btn btn-primary">
                                    </div>
                                </div>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script type="text/javascript">
        $("ul#people").siblings('a').attr('aria-expanded', 'true');
        $("ul#people").addClass("show");
        $("ul#people #biller-create-menu").addClass("active");

        // Cuando se seleccione una sucursal, mostrar sus puntos de ventas respectivos. 
        $('#sucursal').on('change', function() {
            var id = $(this).val();
            var url = '{{ route('getPuntoVentaxSucursal', ':id') }}';
            url = url.replace(':id', id);

            $("select[name='punto_venta_siat']").empty();

            $.ajax({
                url: url,
                type: "GET",
                success: function(data) {
                    console.log(data);
                    for (let i = 0; i < data.length; i++) {
                        $("select[name='punto_venta_siat']").append(
                            '<option value="' + data[i].codigo_punto_venta + '">' +
                            data[i].codigo_punto_venta + ' | ' +
                            data[i].nombre_punto_venta + ' | ' +
                            data[i].codigo_cuis + ' | ' +
                            'Sucursal ' + data[i].sucursal +
                            '</option>');
                    };
                    $('.selectpicker').selectpicker('refresh');
                }
            });
        });
    </script>
@endsection
