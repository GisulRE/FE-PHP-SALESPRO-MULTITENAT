@extends('layout.main') @section('content')
    <section class="forms">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4>{{ trans('file.Update Biller') }}</h4>
                        </div>
                        <div class="card-body">
                            <p class="italic">
                                <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                            </p>
                            {!! Form::open(['route' => ['biller.update', $lims_biller_data->id], 'method' => 'put', 'files' => true]) !!}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ trans('file.name') }} *</strong> </label>
                                        <input type="text" name="name" value="{{ $lims_biller_data->name }}" required
                                            class="form-control">
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
                                        <input type="text" name="company_name"
                                            value="{{ $lims_biller_data->company_name }}" required class="form-control">
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
                                        <input type="text" name="vat_number" value="{{ $lims_biller_data->vat_number }}"
                                            class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ trans('file.Email') }} *</label>
                                        <input type="email" name="email" value="{{ $lims_biller_data->email }}"
                                            required class="form-control">
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
                                        <input type="text" name="phone_number"
                                            value="{{ $lims_biller_data->phone_number }}" required class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ trans('file.Address') }} *</label>
                                        <input type="text" name="address" value="{{ $lims_biller_data->address }}"
                                            required class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ trans('file.City') }} *</label>
                                        <input type="text" name="city" value="{{ $lims_biller_data->city }}" required
                                            class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ trans('file.State') }}</label>
                                        <input type="text" name="state" value="{{ $lims_biller_data->state }}"
                                            class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ trans('file.Country') }}</label>
                                        <input type="text" name="country" value="{{ $lims_biller_data->country }}"
                                            class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label> {{ trans('file.Account') }} Efectivo *</label>
                                        @if ($lims_biller_data)
                                            <input type="hidden" name="account_id_hidden"
                                                value="{{ $lims_biller_data->account_id }}">
                                        @endif
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
                                        @if ($lims_biller_data)
                                            <input type="hidden" name="account_id_tarjeta_hidden"
                                                value="{{ $lims_biller_data->account_id_tarjeta }}">
                                        @endif
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
                                        @if ($lims_biller_data)
                                            <input type="hidden" name="account_id_qr_hidden"
                                                value="{{ $lims_biller_data->account_id_qr }}">
                                        @endif
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
                                        @if ($lims_biller_data)
                                            <input type="hidden" name="account_id_deposito_hidden"
                                                value="{{ $lims_biller_data->account_id_deposito }}">
                                        @endif
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
                                        @if ($lims_biller_data)
                                            <input type="hidden" name="account_id_cheque_hidden"
                                                value="{{ $lims_biller_data->account_id_cheque }}">
                                        @endif
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
                                        @if ($lims_biller_data)
                                            <input type="hidden" name="account_id_receivable_hidden"
                                                value="{{ $lims_biller_data->account_id_receivable }}">
                                        @endif
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
                                        @if ($lims_biller_data)
                                            <input type="hidden" name="account_id_giftcard_hidden"
                                                value="{{ $lims_biller_data->account_id_giftcard }}">
                                        @endif
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
                                        @if ($lims_biller_data)
                                            <input type="hidden" name="account_id_vale_hidden"
                                                value="{{ $lims_biller_data->account_id_vale }}">
                                        @endif
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
                                        @if ($lims_biller_data)
                                            <input type="hidden" name="account_id_otros_hidden"
                                                value="{{ $lims_biller_data->account_id_otros }}">
                                        @endif
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
                                        @if ($lims_biller_data)
                                            <input type="hidden" name="account_id_pagoposterior_hidden"
                                                value="{{ $lims_biller_data->account_id_pagoposterior }}">
                                        @endif
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
                                        @if ($lims_biller_data)
                                            <input type="hidden" name="account_id_transferenciabancaria_hidden"
                                                value="{{ $lims_biller_data->account_id_transferenciabancaria }}">
                                        @endif
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
                                        @if ($lims_biller_data)
                                            <input type="hidden" name="account_id_swift_hidden"
                                                value="{{ $lims_biller_data->account_id_swift }}">
                                        @endif
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
                                        @if ($lims_biller_data)
                                            <input type="hidden" name="customer_id_hidden"
                                                value="{{ $lims_biller_data->customer_id }}">
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
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ trans('file.Default Warehouse') }} *</label>
                                        @if ($lims_biller_data)
                                            <input type="hidden" name="warehouse_id_hidden"
                                                value="{{ $lims_biller_data->warehouse_id }}">
                                        @endif
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
                                    <input type="hidden" name="sucursal_id_hidden" value="{{ $lims_biller_data->sucursal }}">
                                    <select name="sucursal" id="sucursal" class="selectpicker form-control" title="Seleccionar...">
                                        @foreach ($sucursales as $sucursal)
                                            <option value="{{ $sucursal->sucursal }}">{{ $sucursal->sucursal }} | {{ $sucursal->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Codigo Punto Venta *</label>
                                    <input type="hidden" name="punto_venta_siat_hidden" value="{{ $lims_biller_data->punto_venta_siat }}">
                                    <select required name="punto_venta_siat" id="punto_venta_siat" class="form-control selectpicker" title="Seleccionar...">
                                        @foreach ($p_ventas as $item)
                                            <option value="{{ $item->codigo_punto_venta }}">
                                                {{ $item->codigo_punto_venta }} | {{ $item->nombre_punto_venta }} |
                                                {{ $item->codigo_cuis }} | Sucursal {{ $item->sucursal }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mt-3">
                                        <input type="submit" value="{{ trans('file.submit') }}"
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
        $('select[name="sucursal"]').val($("input[name='sucursal_id_hidden']").val());
        $('select[name="punto_venta_siat"]').val($("input[name='punto_venta_siat_hidden']").val());
        $('select[name="customer_id"]').val($("input[name='customer_id_hidden']").val());
        $('select[name="account_id"]').val($("input[name='account_id_hidden']").val());
        $('select[name="account_id_tarjeta"]').val($("input[name='account_id_tarjeta_hidden']").val());
        $('select[name="account_id_qr"]').val($("input[name='account_id_qr_hidden']").val());
        $('select[name="account_id_deposito"]').val($("input[name='account_id_deposito_hidden']").val());
        $('select[name="account_id_cheque"]').val($("input[name='account_id_cheque_hidden']").val());
        $('select[name="account_id_giftcard"]').val($("input[name='account_id_giftcard_hidden']").val());
        $('select[name="account_id_receivable"]').val($("input[name='account_id_receivable_hidden']").val());
        $('select[name="account_id_vale"]').val($("input[name='account_id_vale_hidden']").val());
        $('select[name="account_id_otros"]').val($("input[name='account_id_otros_hidden']").val());
        $('select[name="account_id_pagoposterior"]').val($("input[name='account_id_pagoposterior_hidden']").val());
        $('select[name="account_id_transferenciabancaria"]').val($("input[name='account_id_transferenciabancaria_hidden']").val());
        $('select[name="account_id_swift"]').val($("input[name='account_id_swift_hidden']").val());

        $('select[name="warehouse_id"]').val($("input[name='warehouse_id_hidden']").val());
        var listWarehouses = <?php echo json_encode($lims_warehouse_selects); ?>;
        $.each(listWarehouses, function(i,e){
            $("#warehouses option[value='" + e.warehouse_id + "']").prop("selected", true);
        });
        $('.selectpicker').selectpicker('refresh');

        // Cuando se seleccione una sucursal, mostrar sus puntos de ventas respectivos. 
    $('#sucursal').on('change', function () {
        var id = $(this).val();
        var url = '{{ route("getPuntoVentaxSucursal", ":id") }}';
        url = url.replace(':id', id);

        $("select[name='punto_venta_siat']").empty();
        
        $.ajax({
            url: url,
            type: "GET",
            success:function(data) {
                console.log(data);
                for (let i = 0; i < data.length; i++) {
                    $("select[name='punto_venta_siat']").append(
                        '<option value="'+ data[i].codigo_punto_venta +'">'
                            + data[i].codigo_punto_venta + ' | '
                            + data[i].nombre_punto_venta + ' | '
                            + data[i].codigo_cuis + ' | '
                            + 'Sucursal ' + data[i].sucursal 
                            + '</option>');
                };
                $('.selectpicker').selectpicker('refresh');

            }
        });
    });
    </script>
@endsection
