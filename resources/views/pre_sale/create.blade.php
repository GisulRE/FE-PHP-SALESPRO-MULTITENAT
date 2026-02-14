@extends('layout.top-head') @section('content')
    @if ($errors->has('phone_number'))
        <div class="alert alert-danger alert-dismissible text-center">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{{ $errors->first('phone_number') }}
        </div>
    @endif
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert"
                aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('message') !!}</div>
    @endif
    @if (session()->has('not_permitted'))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert"
                aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
    @endif
    <style>
        p {
            color: black !important;
            font-weight: bold !important;
        }
    </style>
    <section class="forms pos-section">
        <div class="container-fluid">
            <div class="row">
                <audio id="mysoundclip1" preload="auto">
                    <source src="{{ url('public/beep/beep-timber.mp3') }}">
                    </source>
                </audio>
                <audio id="mysoundclip2" preload="auto">
                    <source src="{{ url('public/beep/beep-07.mp3') }}">
                    </source>
                </audio>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body" style="padding-bottom: 0">
                            {!! Form::open(['route' => 'presales.store', 'method' => 'post', 'files' => true, 'class' => 'payment-form']) !!}
                            @php
                                if ($lims_pos_setting_data) {
                                    $keybord_active = $lims_pos_setting_data->keybord_presale;
                                } else {
                                    $keybord_active = 0;
                                }

                                $customer_active = DB::table('permissions')
                                    ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                                    ->where([['permissions.name', 'customers-add'], ['role_id', \Auth::user()->role_id]])
                                    ->first();
                            @endphp
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <input type="text" class="form-control" name="employee_name"
                                                placeholder="Empleado de Servicio..." readonly>
                                            <input type="hidden" class="form-control" name="employee_id">
                                            <input id="biller_id" type="hidden" name="biller_id"
                                                value="{{ $lims_pos_setting_data->biller_id }}">
                                            <input id="warehouse_id" type="hidden" name="warehouse_id"
                                                value="{{ $lims_pos_setting_data->warehouse_id }}">
                                        </div>
                                        <div id="div_account" class="col-md-4">
                                            <div class="form-group">
                                                @if ($account_data)
                                                    <label id="account_id">{{ $account_data }}</label>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                @if ($lims_pos_setting_data)
                                                    <input type="hidden" name="customer_id_hidden"
                                                        value="{{ $lims_pos_setting_data->customer_id }}">
                                                @endif
                                                <div class="input-group pos">
                                                    @if ($customer_active)
                                                        <select required name="customer_id" id="customer_id"
                                                            class="selectpicker form-control" data-live-search="true"
                                                            data-live-search-style="contains" title="Select customer..."
                                                            style="width: 100px">
                                                            <?php    $deposit = []; ?>
                                                            @foreach ($lims_customer_list as $customer)
                                                                @php $deposit[$customer->id] = $customer->deposit - $customer->expense; @endphp
                                                                <option value="{{ $customer->id }}">
                                                                    {{ $customer->name . ' (' . $customer->phone_number . ')' }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <button type="button" class="btn btn-default btn-sm" data-toggle="modal"
                                                            data-target="#addCustomer"><i class="dripicons-plus"></i></button>
                                                    @else
                                                        <?php    $deposit = []; ?>
                                                        <select required name="customer_id" id="customer_id"
                                                            class="selectpicker form-control" data-live-search="true"
                                                            title="Select customer...">
                                                            @foreach ($lims_customer_list as $customer)
                                                                @php $deposit[$customer->id] = $customer->deposit - $customer->expense; @endphp
                                                                <option value="{{ $customer->id }}">
                                                                    {{ $customer->name . ' (' . $customer->phone_number . ')' }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="table-responsive transaction-list">
                                            <table id="myTable"
                                                class="table table-hover table-striped order-list table-fixed"
                                                style="height: 350px;">
                                                <thead style="text-align: center;">
                                                    <tr>
                                                        <th class="col-sm-4">{{ trans('file.product') }}</th>
                                                        <th class="col-sm-2">{{ trans('file.Price') }}</th>
                                                        <th class="col-sm-3">{{ trans('file.Quantity') }}</th>
                                                        <th class="col-sm-3">{{ trans('file.Subtotal') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="text-right mt-2 mb-2">
                                            <button type="button" class="btn btn-warning btn-sm" id="clear-services-btn">
                                                <i class="fa fa-eraser"></i> Limpiar Servicios
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row" style="display: none;">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="hidden" name="total_qty" />
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="hidden" name="total_discount" value="0.00" />
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="hidden" name="total_tax" value="0.00" />
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="hidden" name="total_price" />
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="hidden" name="item" />
                                                <input type="hidden" name="order_tax" />
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="hidden" name="grand_total" />
                                                <input type="hidden" name="status" value="1" />
                                                <input type="hidden" name="coupon_active">
                                                <input type="hidden" name="coupon_id">
                                                <input type="hidden" name="date_sell" />
                                                <input type="hidden" name="prepos" value="1" />
                                                <input type="hidden" name="draft" value="0" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="payment-amount">
                            <h2>{{ trans('file.grand total') }} <span id="grand-total">0.00</span></h2>
                        </div>
                        <div class="col-12 row" style="border-top: 2px solid #e4e6fc; padding-top: 10px;">
                            <div class="col-4">
                                <span class="totals-title">{{ trans('file.Discount') }} <button type="button"
                                        class="btn btn-link btn-sm" data-toggle="modal" data-target="#order-discount"> <i
                                            style="font-size: 20px;"
                                            class="dripicons-document-edit"></i></button></span><span
                                    id="discount">0.00</span>
                            </div>
                            <div class="col-4">
                                <span class="totals-title" style="display:none">Propina <button type="button"
                                        class="btn btn-link btn-sm" data-toggle="modal" data-target="#tips-modal"> <i
                                            style="font-size: 20px;"
                                            class="dripicons-document-edit"></i></button></span><span id="tips_amount"
                                    style="display:none">0.00</span>
                            </div>
                            <div class="col-4">
                                <span class="totals-title">Cargo Adicional <button type="button" class="btn btn-link btn-sm"
                                        data-toggle="modal" data-target="#shipping-cost-modal"><i style="font-size: 20px;"
                                            class="dripicons-document-edit"></i></button></span><span
                                    id="shipping-cost">0.00</span>
                            </div>
                        </div>
                        <div class="payment-options">
                            <div class="column-5"></div>
                            <div class="column-5"></div>
                            <div class="column-5">
                                <button style="background-color: #2bf710" type="button" class="btn btn-custom"
                                    id="presale-btn"><i class="fa fa-save"></i> Generar Pre-Venta</button>
                            </div>
                            <div class="column-5">
                                <button id="showreport-btn" style="background-color: #830df1;" type="button"
                                    class="btn btn-custom" data-toggle="modal" data-target="#report-comission"><i
                                        class="fa fa-list"></i>Reporte Comisiones</button>
                            </div>
                            <div class="column-5">
                                <button style="background-color: #67adee;" type="button" class="btn btn-custom"
                                    id="cancel-btn" onclick="return confirmCancel()"><i class="fa fa-trash"></i>
                                    Cancelar</button>
                            </div>
                            <div class="column-5">
                                <button style="background-color: #d63031;" type="button" class="btn btn-custom"
                                    id="exit-btn" onclick="return confirmExit()"><i class="fa fa-close"></i>
                                    Salir</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- order_discount modal -->
                <div id="order-discount" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
                    class="modal fade text-left">
                    <div role="document" class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ trans('file.Order Discount') }}</h5>
                                <button type="button" data-dismiss="modal" aria-label="image.pngClose" class="close"><span
                                        aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <input type="text" name="order_discount" class="form-control numkey">
                                </div>
                                <button type="button" name="order_discount_btn" class="btn btn-primary"
                                    data-dismiss="modal">{{ trans('file.submit') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- shipping_cost modal -->
                <div id="shipping-cost-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true" class="modal fade text-left">
                    <div role="document" class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Cargo Adicional</h5>
                                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                        aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <input type="text" name="shipping_cost" class="form-control numkey" step="any">
                                </div>
                                <button type="button" name="shipping_cost_btn" class="btn btn-primary"
                                    data-dismiss="modal">{{ trans('file.submit') }}</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- order_tax modal -->
                <div id="order-tax" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
                    class="modal fade text-left">
                    <div role="document" class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ trans('file.Order Tax') }}</h5>
                                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                        aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <input type="hidden" name="order_tax_rate">
                                    <select class="form-control" name="order_tax_rate_select">
                                        <option value="0">No Tax</option>
                                        @foreach ($lims_tax_list as $tax)
                                            <option value="{{ $tax->rate }}">{{ $tax->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" name="order_tax_btn" class="btn btn-primary"
                                    data-dismiss="modal">{{ trans('file.submit') }}</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- tips modal -->
                <div id="tips-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
                    class="modal fade text-left">
                    <div role="document" class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Propina</h5>
                                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                        aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <input type="text" name="tips" class="form-control numkey" step="any">
                                </div>
                                <button type="button" name="tip_btn" class="btn btn-primary"
                                    data-dismiss="modal">{{ trans('file.submit') }}</button>
                            </div>
                        </div>
                    </div>
                </div>

                {!! Form::close() !!}
                <!-- product list -->
                <div class="col-md-6">
                    <div class="filter-window">
                        <div class="employee mt-3">
                            <div class="row ml-2 mr-2 px-2">
                                <div class="col-7">Seleccione Empleado</div>
                                <div class="col-5 text-right">
                                    <span class="btn btn-default btn-sm">
                                        <i class="dripicons-cross"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="row ml-2 mt-3">
                                @foreach ($lims_employee_list as $employee)
                                    <div class="col-md-3 employee-img text-center" data-employee="{{ $employee->id }}"
                                        data-employee_name="{{ $employee->name }}"
                                        data-warehouse="{{ $employee->warehouse_id }}">
                                        @if ($employee->image)
                                            <img src="{{ url('public/images/employee', $employee->image) }}" />
                                        @else
                                            <img src="{{ url('public/images/product/zummXD2dvAtI.png') }}" />
                                        @endif
                                        <p class="text-center">{{ $employee->name }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="category mt-3">
                            <div class="row ml-2 mr-2 px-2">
                                <div class="col-7">Seleccione Categoria</div>
                                <div class="col-5 text-right">
                                    <span class="btn btn-default btn-sm">
                                        <i class="dripicons-cross"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="row ml-2 mt-3">
                                @foreach ($lims_category_list as $category)
                                    <div class="col-md-3 category-img text-center" data-category="{{ $category->id }}">
                                        @if ($category->image)
                                            <img src="{{ url('public/images/category', $category->image) }}" />
                                        @else
                                            <img src="{{ url('public/images/product/zummXD2dvAtI.png') }}" />
                                        @endif
                                        <p class="text-center">{{ $category->name }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="brand mt-3">
                            <div class="row ml-2 mr-2 px-2">
                                <div class="col-7">Seleccione Marca</div>
                                <div class="col-5 text-right">
                                    <span class="btn btn-default btn-sm">
                                        <i class="dripicons-cross"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="row ml-2 mt-3">
                                @foreach ($lims_brand_list as $brand)
                                    @if ($brand->image)
                                        <div class="col-md-3 brand-img text-center" data-brand="{{ $brand->id }}">
                                            <img src="{{ url('public/images/brand', $brand->image) }}" />
                                            <p class="text-center">{{ $brand->title }}</p>
                                        </div>
                                    @else
                                        <div class="col-md-3 brand-img" data-brand="{{ $brand->id }}">
                                            <img src="{{ url('public/images/product/zummXD2dvAtI.png') }}" />
                                            <p class="text-center">{{ $brand->title }}</p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <button class="btn btn-block btn-success"
                                id="employee-filter">{{ trans('file.Employee') }}</button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-block btn-primary"
                                id="category-filter">{{ trans('file.category') }}</button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-block btn-danger"
                                id="featured-filter">{{ trans('file.Featured') }}</button>
                        </div>
                        @if (in_array('attentionshift', $all_permission))
                            <div class="col-md-3">
                                <button class="btn btn-block btn-info" id="check-in-out" data-toggle="modal"
                                    data-target="#attendance-modal">{{ trans('file.Attendance') }}</button>
                            </div>
                        @else
                            <div class="col-md-3">
                                <button class="btn btn-block btn-info" id="brand-filter">{{ trans('file.Brand') }}</button>
                            </div>
                        @endif
                        <div class="col-md-12 mt-1 table-container">
                            <table id="product-table" class="table no-shadow product-list">
                                <thead class="d-none">
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody style="color: black;font-weight: bold;">
                                    @for ($i = 0; $i < ceil($product_number / 5); $i++)
                                        <tr>
                                            <td class="product-img sound-btn" title="{{ $lims_product_list[0 + $i * 5]->name }}"
                                                data-product="{{ $lims_product_list[0 + $i * 5]->code . ' (' . $lims_product_list[0 + $i * 5]->name . ')' }}">
                                                <img src="{{ url('./public/images/product', $lims_product_list[0 + $i * 5]->base_image) }}"
                                                    width="100%" />
                                                <p style="color: black;font-weight: bold;">
                                                    {{ $lims_product_list[0 + $i * 5]->name }}
                                                </p>
                                                <span>{{ $lims_product_list[0 + $i * 5]->code }}</span>
                                            </td>
                                            @if (!empty($lims_product_list[1 + $i * 5]))
                                                <td class="product-img sound-btn" title="{{ $lims_product_list[1 + $i * 5]->name }}"
                                                    data-product="{{ $lims_product_list[1 + $i * 5]->code . ' (' . $lims_product_list[1 + $i * 5]->name . ')' }}">
                                                    <img src="{{ url('public/images/product', $lims_product_list[1 + $i * 5]->base_image) }}"
                                                        width="100%" />
                                                    <p style="color: black;font-weight: bold;">
                                                        {{ $lims_product_list[1 + $i * 5]->name }}
                                                    </p>
                                                    <span>{{ $lims_product_list[1 + $i * 5]->code }}</span>
                                                </td>
                                            @else
                                                <td style="border:none;"></td>
                                            @endif
                                            @if (!empty($lims_product_list[2 + $i * 5]))
                                                <td class="product-img sound-btn" title="{{ $lims_product_list[2 + $i * 5]->name }}"
                                                    data-product="{{ $lims_product_list[2 + $i * 5]->code . ' (' . $lims_product_list[2 + $i * 5]->name . ')' }}">
                                                    <img src="{{ url('public/images/product', $lims_product_list[2 + $i * 5]->base_image) }}"
                                                        width="100%" />
                                                    <p style="color: black;font-weight: bold;">
                                                        {{ $lims_product_list[2 + $i * 5]->name }}
                                                    </p>
                                                    <span>{{ $lims_product_list[2 + $i * 5]->code }}</span>
                                                </td>
                                            @else
                                                <td style="border:none;"></td>
                                            @endif
                                            @if (!empty($lims_product_list[3 + $i * 5]))
                                                <td class="product-img sound-btn" title="{{ $lims_product_list[3 + $i * 5]->name }}"
                                                    data-product="{{ $lims_product_list[3 + $i * 5]->code . ' (' . $lims_product_list[3 + $i * 5]->name . ')' }}">
                                                    <img src="{{ url('public/images/product', $lims_product_list[3 + $i * 5]->base_image) }}"
                                                        width="100%" />
                                                    <p style="color: black;font-weight: bold;">
                                                        {{ $lims_product_list[3 + $i * 5]->name }}
                                                    </p>
                                                    <span>{{ $lims_product_list[3 + $i * 5]->code }}</span>
                                                </td>
                                            @else
                                                <td style="border:none;"></td>
                                            @endif
                                            @if (!empty($lims_product_list[4 + $i * 5]))
                                                <td class="product-img sound-btn" title="{{ $lims_product_list[4 + $i * 5]->name }}"
                                                    data-product="{{ $lims_product_list[4 + $i * 5]->code . ' (' . $lims_product_list[4 + $i * 5]->name . ')' }}">
                                                    <img src="{{ url('public/images/product', $lims_product_list[4 + $i * 5]->base_image) }}"
                                                        width="100%" />
                                                    <p style="color: black;font-weight: bold;">
                                                        {{ $lims_product_list[4 + $i * 5]->name }}
                                                    </p>
                                                    <span>{{ $lims_product_list[4 + $i * 5]->code }}</span>
                                                </td>
                                            @else
                                                <td style="border:none;"></td>
                                            @endif
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- product edit modal -->
                <div id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
                    class="modal fade text-left">
                    <div role="document" class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 id="modal_header" class="modal-title"></h5>
                                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                        aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                            </div>
                            <div class="modal-body">
                                <form>
                                    <div class="form-group">
                                        <label>{{ trans('file.Quantity') }}</label>
                                        <input type="text" name="edit_qty" class="form-control numkey">
                                    </div>
                                    <div class="form-group">
                                        <label>{{ trans('file.Unit Discount') }}</label>
                                        <input type="text" name="edit_discount" class="form-control numkey">
                                    </div>
                                    <div class="form-group">
                                        <label>{{ trans('file.Unit Price') }}</label>
                                        <input type="text" name="edit_unit_price" class="form-control numkey" step="any">
                                    </div>
                                    <?php
    $tax_name_all[] = 'No Tax';
    $tax_rate_all[] = 0;
    foreach ($lims_tax_list as $tax) {
        $tax_name_all[] = $tax->name;
        $tax_rate_all[] = $tax->rate;
    }
                                            ?>
                                    <div class="form-group">
                                        <label>{{ trans('file.Tax Rate') }}</label>
                                        <select name="edit_tax_rate" class="form-control selectpicker">
                                            @foreach ($tax_name_all as $key => $name)
                                                <option value="{{ $key }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div id="edit_unit" class="form-group">
                                        <label>{{ trans('file.Product Unit') }}</label>
                                        <select name="edit_unit" class="form-control selectpicker">
                                        </select>
                                    </div>
                                    <button type="button" name="update_btn"
                                        class="btn btn-primary">{{ trans('file.update') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- add customer modal -->
                <div id="addCustomer" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
                    class="modal fade text-left">
                    <div role="document" class="modal-dialog">
                        <div class="modal-content">
                            {!! Form::open(['route' => 'customer.store', 'method' => 'post', 'files' => true]) !!}
                            <div class="modal-header">
                                <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Add Customer') }}</h5>
                                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                        aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                            </div>
                            <div class="modal-body">
                                <p class="italic">
                                    <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                                </p>
                                <div class="form-group">
                                    <label>{{ trans('file.Customer Group') }} *</strong> </label>
                                    <select required class="form-control selectpicker" name="customer_group_id">
                                        @foreach ($lims_customer_group_all as $customer_group)
                                            <option value="{{ $customer_group->id }}">{{ $customer_group->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>{{ trans('file.name') }} *</strong> </label>
                                    <input type="text" name="name" required class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>{{ trans('file.Email') }}</label>
                                    <input type="text" name="email" placeholder="example@example.com" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>{{ trans('file.Phone Number') }}</label>
                                    <input type="text" name="phone_number" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>{{ trans('file.Address') }}</label>
                                    <input type="text" name="address" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>{{ trans('file.City') }}</label>
                                    <input type="text" name="city" class="form-control">
                                </div>
                                <div class="form-group">
                                    <input type="hidden" name="pos" value="1">
                                    <input type="submit" value="{{ trans('file.submit') }}" class="btn btn-primary">
                                </div>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>

                <!-- panel attendance -->
                <div id="attendance-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true" class="modal fade text-left">
                    <div role="document" class="modal-dialog">
                        <div class="modal-content">

                            <div class="modal-header">
                                <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Attendance') }}
                                    {{ date('d-m-Y') }}
                                </h5>
                                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                        aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                            </div>
                            <div class="modal-body">
                                <div class="row ml-2 mt-3">
                                    @foreach ($lims_employee_list as $employee)
                                        @php
                                            $date = date('Y-m-d');
                                            $attendance_data = App\Attendance::whereDate('date', $date)
                                                ->where('employee_id', $employee->id)
                                                ->first();
                                        @endphp
                                        <div id="emp_tab_{{ $employee->id }}" class="col-md-3 attendance-img text-center" @if ($attendance_data)
                                        style="border: 3px solid green;margin-right: 2px;margin-top: 3px;" @endif
                                            data-employee="{{ $employee->id }}">
                                            @if ($employee->image)
                                                <img src="{{ url('public/images/employee', $employee->image) }}" />
                                            @else
                                                <img src="{{ url('public/images/product/zummXD2dvAtI.png') }}" />
                                            @endif
                                            <p class="text-center">{{ $employee->name }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- pre-sales transaction modal -->
                <div id="report-comission" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle"
                    aria-hidden="true" class="modal fade text-left bd-example-modal-lg">
                    <div role="document" class="modal-dialog modal-lg" style="max-width: 900px;">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 id="exampleModalLabel" class="modal-title">Reporte Comision de Servicios

                                </h5>
                                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                        aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-1 form-group"></div>
                                    <div class="col-md-3 form-group">
                                        <label class="d-tc mt-2"><strong>Total Pagado.</strong> &nbsp;</label>
                                        <input id="totalrp" type="text" name="totalrp" class="form-control" readonly />
                                    </div>
                                    <div class="col-md-3 form-group">
                                        <label class="d-tc mt-2"><strong>Descuento QR.</strong> &nbsp;</label>
                                        <input id="totalqr_rp" type="text" name="totalqr_rp" class="form-control"
                                            readonly />
                                    </div>
                                    <div class="col-md-3 form-group">
                                        <label class="d-tc mt-2"><strong>Total Comision.</strong> &nbsp;</label>
                                        <input id="totalrp_win" type="text" name="totalrp_win" class="form-control"
                                            readonly />
                                    </div>
                                </div>
                                <div class="tab-content">
                                    <div class="table-responsive">
                                        <table id="table-report" class="table">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>{{ trans('file.reference') }}</th>
                                                    <th>{{ trans('file.Service') }}</th>
                                                    <th>{{ trans('file.Employee') }}</th>
                                                    <th>{{ trans('file.date') }}</th>
                                                    <th>{{ trans('file.grand total') }} Bs.</th>
                                                    <th>%</th>
                                                    <th>Descuento QR Bs.</th>
                                                    <th>Total Comision Bs.</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>


    </section>

    <script type="text/javascript">
        let date = new Date()
        let day = date.getDate()
        let month = date.getMonth() + 1
        let year = date.getFullYear()
        var timerIntervalId;
        var checkStatusIntervalId;

        if (month < 10) {
            datef = day + "-0" + month + "-" + year;
        } else {
            datef = day + "-" + month + "-" + year;
        }
        var permission_turno = <?php echo json_encode(in_array('attentionshift', $all_permission)); ?>;

        $("ul#sale").siblings('a').attr('aria-expanded', 'true');
        $("ul#sale").addClass("show");
        $("ul#sale #sale-pos-menu").addClass("active");
        $('input[name="total_discount"]').val(0);
        $('input[name="shipping_cost"]').val('');
        $('input[name="tips"]').val('');
        $("input[name='employee_id']").val('');
        $('input[name="employee_name"]').val('');
        var public_key = <?php echo json_encode($lims_pos_setting_data->stripe_public_key); ?>;
        var valid;

        // array data depend on warehouse
        var lims_product_array = [];
        var product_code = [];
        var product_name = [];
        var product_qty = [];
        var product_type = [];
        var product_id = [];
        var product_list = [];
        var qty_list = [];
        var qty_list2 = [];

        // array data with selection
        var product_price = [];
        var product_discount = [];
        var tax_rate = [];
        var tax_name = [];
        var tax_method = [];
        var unit_name = [];
        var unit_operator = [];
        var unit_operation_value = [];
        var gift_card_amount = [];
        var gift_card_expense = [];

        // temporary array
        var temp_unit_name = [];
        var temp_unit_operator = [];
        var temp_unit_operation_value = [];

        var deposit = <?php echo json_encode($deposit); ?>;
        var product_row_number = 3;
        var tc = <?php echo json_encode($lims_pos_setting_data->t_c); ?>;
        var rowindex;
        var customer_group_rate;
        var row_product_price;
        var pos;
        var emp_temp = false;
        var employee_id = null;

        var keyboard_active = <?php echo json_encode($keybord_active); ?>;
        var role_id = <?php echo json_encode(\Auth::user()->role_id); ?>;
        var customer_idefault = <?php echo json_encode($lims_pos_setting_data->customer_id); ?>;
        var warehouse_id = <?php echo json_encode($biller_data->warehouse_id); ?>;
        var warehouse_idefault = <?php echo json_encode($lims_pos_setting_data->warehouse_id); ?>;
        var biller_id = <?php echo json_encode(\Auth::user()->biller_id); ?>;
        var biller_idefault = <?php echo json_encode($lims_pos_setting_data->biller_id); ?>;
        var coupon_list = <?php echo json_encode($lims_coupon_list); ?>;
        var currency = <?php echo json_encode($general_setting->currency); ?>;
        let limsCustomerList = <?php echo json_encode($lims_customer_list); ?>;

        var baseUrl = "<?php echo url('/'); ?>";
        $('.selectpicker').selectpicker({
            style: 'btn-link',
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            error: function (xhr, status, error) {
                msg = new swal("Error", "Estado: " + status + " Error: " + error, "error");

            }
        });

        if (keyboard_active == 1) {

            $("input.numkey:text").keyboard({
                usePreview: false,
                layout: 'custom',
                display: {
                    'accept': '&#10004;',
                    'cancel': '&#10006;'
                },
                customLayout: {
                    'normal': ['1 2 3', '4 5 6', '7 8 9', '0 {dec} {bksp}', '{clear} {cancel} {accept}']
                },
                restrictInput: true, // Prevent keys not in the displayed keyboard from being typed in
                preventPaste: true, // prevent ctrl-v and right click
                autoAccept: true,
                css: {
                    // input & preview
                    // keyboard container
                    container: 'center-block dropdown-menu', // jumbotron
                    // default state
                    buttonDefault: 'btn btn-default',
                    // hovered button
                    buttonHover: 'btn-primary',
                    // Action keys (e.g. Accept, Cancel, Tab, etc);
                    // this replaces "actionClass" option
                    buttonAction: 'active',
                },
            });

            $('input[type="text"]').keyboard({
                usePreview: false,
                autoAccept: true,
                autoAcceptOnEsc: true,
                css: {
                    // input & preview
                    // keyboard container
                    container: 'center-block dropdown-menu', // jumbotron
                    // default state
                    buttonDefault: 'btn btn-default',
                    // hovered button
                    buttonHover: 'btn-primary',
                    // Action keys (e.g. Accept, Cancel, Tab, etc);
                    // this replaces "actionClass" option
                    buttonAction: 'active',
                    // used when disabling the decimal button {dec}
                    // when a decimal exists in the input area
                    buttonDisabled: 'disabled'
                },
                change: function (e, keyboard) {
                    keyboard.$el.val(keyboard.$preview.val())
                    keyboard.$el.trigger('propertychange')
                }
            });

            $('textarea').keyboard({
                usePreview: false,
                autoAccept: true,
                autoAcceptOnEsc: true,
                css: {
                    // input & preview
                    // keyboard container
                    container: 'center-block dropdown-menu', // jumbotron
                    // default state
                    buttonDefault: 'btn btn-default',
                    // hovered button
                    buttonHover: 'btn-primary',
                    // Action keys (e.g. Accept, Cancel, Tab, etc);
                    // this replaces "actionClass" option
                    buttonAction: 'active',
                    // used when disabling the decimal button {dec}
                    // when a decimal exists in the input area
                    buttonDisabled: 'disabled'
                },
                change: function (e, keyboard) {
                    keyboard.$el.val(keyboard.$preview.val())
                    keyboard.$el.trigger('propertychange')
                }
            });
        }

        $('select[name="customer_id"]').val(customer_idefault);
        $('.selectpicker').selectpicker('refresh');
        if (role_id > 2) {
            $('#biller_id').addClass('d-none');
            $('#account_id').prop('disabled', true);
            $('input[name="warehouse_id"]').val(warehouse_id);
            $('input[name="biller_id"]').val(biller_id);
        } else {
            $('#div_account').remove();
            $('input[name="warehouse_id"]').val(warehouse_idefault);
            $('input[name="biller_id"]').val(biller_idefault);
        }


        var id_c = $("#customer_id").val();
        $.get('sales/getcustomergroup/' + id_c, function (data) {
            customer_group_rate = (data / 100);
        });

        var id = $("#warehouse_id").val();
        $.get('sales/getproduct_customer/' + id + '/' + id_c, function (data) {
            lims_product_array = [];
            product_code = data[0];
            product_name = data[1];
            product_qty = data[2];
            product_type = data[3];
            product_id = data[4];
            product_list = data[5];
            qty_list = data[6];
            $.each(product_code, function (index) {
                const price = data[7][index];
                lims_product_array.push(product_code[index] + ' (' + product_name[index] + ')' +
                    ` - Precio: ${getPriceProduct(data, index)} - Stock: ${product_qty[index]}`);
            });
        });

        if (employee_id != null) {

        } else {
            $('.filter-window').show('slide', {
                direction: 'right'
            }, 'fast');
            $('.employee').show();
            $('.brand').hide();
            $('.category').hide();
        }

        function getPriceProduct(data = [], index) {
            const types = data[3];
            const prices = data[7]
            const taxs = data[8];
            const units = data[9];
            const unitOperator = units[index][1].split(",")[0];
            const unitOperationValue = units[index][2].split(",")[0];
            let price = Number(prices[index]);
            /*if(types[index] == 'standard'){
              if (unitOperator == '*')  
                  price = price * unitOperationValue;
              else  
                  price = price / unitOperationValue;
            }*/
            if (taxs[index][2] == 1) {
                const tax = price * (taxs[index][0] / 100);
                price = price + tax;
            }
            return price.toFixed(2);
        }

        $("#print-btn").on("click", function () {
            var divToPrint = document.getElementById('sale-details');
            var newWin = window.open('', 'Print-Window');
            newWin.document.open();
            newWin.document.write(
                '<link rel="stylesheet" href="/public/vendor/bootstrap/css/bootstrap.min.css" type="text/css"><style type="text/css">@media print {.modal-dialog { max-width: 1000px;} }</style><body onload="window.print()">' +
                divToPrint.innerHTML + '</body>');
            newWin.document.close();
            setTimeout(function () {
                newWin.close();
            }, 10);
        });

        $('body').on('click', function (e) {
            $('.filter-window').hide('slide', {
                direction: 'right'
            }, 'fast');
        });

        $('#employee-filter').on('click', function (e) {
            e.stopPropagation();
            $('.filter-window').show('slide', {
                direction: 'right'
            }, 'fast');
            $('.employee').show();
            $('.brand').hide();
            $('.category').hide();
        });

        $('#category-filter').on('click', function (e) {
            e.stopPropagation();
            $('.filter-window').show('slide', {
                direction: 'right'
            }, 'fast');
            if (employee_id != null) {
                $('.category').show();
                $('.employee').hide();
                $('.brand').hide();
            } else {
                //swal("Recuerde", "Seleccione primero el empleado de servicio...", "info");
                $('.employee').show();
                $('.brand').hide();
                $('.category').hide();
            }
        });

        $('.employee-img').on('click', function () {
            var newEmployee = $(this).data('employee');
            var newWarehouse = $(this).data('warehouse');
            var newEmployeeName = $(this).data('employee_name');

            // Set global selection so new items use this employee
            employee_id = newEmployee;
            warehouse_id = newWarehouse;
            $('input[name="warehouse_id"]').val(warehouse_id);
            $('input[name="employee_id"]').val(employee_id);
            $('input[name="employee_name"]').val(newEmployeeName);

            // Assign the new employee id to all existing items in the cart
            $('table.order-list tbody tr').each(function () {
                $(this).find('.employee-id').val(employee_id);
            });

            // Refresh product list for the selected warehouse
            $(".table-container").children().remove();
            $.get('sales/getfeatured', function (data) {
                populateProduct(data);
            });

            calculateTotal();
        });

        $('.category-img').on('click', function () {
            var category_id = $(this).data('category');
            var brand_id = 0;

            $(".table-container").children().remove();
            $.get('sales/getproduct/' + category_id + '/' + brand_id, function (data) {
                populateProduct(data);
            });
        });

        $('.attendance-img').on('click', function () {
            var id = $(this).data('employee');
            $.get('attendance/checked/' + id, function (data) {
                if (data.status) {
                    if (data.type == 'checkin') {
                        $(`#emp_tab_${id}`).attr("style",
                            "border:3px solid green;margin-right: 2px;margin-top: 3px;");
                        msg = new swal('Marcaje', "Empleado registrado con éxito", "success");
                    } else {
                        $(`#emp_tab_${id}`).attr("style",
                            "border:3px solid red;margin-right: 2px;margin-top: 3px;");
                        msg = new swal('Marcaje', "Empleado registro salida con éxito", "success");
                    }
                } else {
                    msg = new swal('Marcaje', "Empleado fallo al marcar, intente de nuevo!", "error");
                }
                /*$('#attendance-modal').modal('hide')
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();*/
            });
        });

        $('#brand-filter').on('click', function (e) {
            e.stopPropagation();
            $('.filter-window').show('slide', {
                direction: 'right'
            }, 'fast');;
            if (employee_id != null) {
                $('.brand').show();
                $('.employee').hide();
                $('.category').hide();
            } else {
                //swal("Recuerde", "Seleccione primero el empleado de servicio...", "info");
                $('.employee').show();
                $('.brand').hide();
                $('.category').hide();
            }
        });

        $('#featured-filter').on('click', function () {
            $(".table-container").children().remove();
            $.get('sales/getfeatured', function (data) {
                populateProduct(data);
            });
        });

        function populateProduct(data) {
            var tableData =
                '<table id="product-table" class="table no-shadow product-list"> <thead class="d-none"> <tr> <th></th> <th></th> <th></th> <th></th> <th></th> </tr></thead> <tbody><tr>';

            if (Object.keys(data).length != 0) {
                $.each(data['name'], function (index) {
                    var product_info = data['code'][index] + ' (' + data['name'][index] + ')';
                    if (index % 5 == 0 && index != 0)
                        tableData += '</tr><tr><td class="product-img sound-btn" title="' + data['name'][index] +
                            '" data-product = "' + product_info + '"><img  src="public/images/product/' + data['image'][
                            index
                            ] + '" width="100%" /><p>' + data['name'][index] + '</p><span>' + data['code'][index] +
                            '</span></td>';
                    else
                        tableData += '<td class="product-img sound-btn" title="' + data['name'][index] +
                            '" data-product = "' + product_info + '"><img  src="public/images/product/' + data['image'][
                            index
                            ] + '" width="100%" /><p>' + data['name'][index] + '</p><span>' + data['code'][index] +
                            '</span></td>';
                });

                if (data['name'].length % 5) {
                    var number = 5 - (data['name'].length % 5);
                    while (number > 0) {
                        tableData += '<td style="border:none;"></td>';
                        number--;
                    }
                }

                tableData += '</tr></tbody></table>';
                $(".table-container").html(tableData);
                $('#product-table').DataTable({
                    "order": [],
                    'pageLength': product_row_number,
                    'language': {
                        'paginate': {
                            'previous': '<i class="fa fa-angle-left"></i>',
                            'next': '<i class="fa fa-angle-right"></i>'
                        }
                    },
                    dom: 'tp'
                });
                $('table.product-list').hide();
                $('table.product-list').show(500);
            } else {
                tableData += '<td class="text-center">No data avaialable</td></tr></tbody></table>'
                $(".table-container").html(tableData);
            }
        }

        $('select[name="customer_id"]').on('change', function () {
            var id = $(this).val();
            var id_w = $("#warehouse_id").val();
            $.get('sales/getcustomergroup/' + id, function (data) {
                customer_group_rate = (data / 100);
            });

            $.get('sales/getproduct_customer/' + id_w + '/' + id, function (data) {
                console.log(data);
                lims_product_array = [];
                product_code = data[0];
                product_name = data[1];
                product_qty = data[2];
                product_type = data[3];
                $.each(product_code, function (index) {
                    lims_product_array.push(product_code[index] + ' (' + product_name[index] + ')' +
                        ` - Precio: ${getPriceProduct(data, index)} - Stock: ${product_qty[index]}`
                    );
                });
            });
        });

        $('input[name="warehouse_id"]').on('change', function () {
            var id = $(this).val();
            var id_c = $("#customer_id").val();
            $.get('sales/getproduct_customer/' + id + '/' + id_c, function (data) {
                lims_product_array = [];
                product_code = data[0];
                product_name = data[1];
                product_qty = data[2];
                product_type = data[3];
                $.each(product_code, function (index) {
                    lims_product_array.push(product_code[index] + ' (' + product_name[index] + ')' +
                        ` - Precio: ${getPriceProduct(data, index)} - Stock: ${product_qty[index]}`
                    );
                });
            });
        });


        $('#myTable').keyboard({
            accepted: function (event, keyboard, el) {
                checkQuantity(el.value, true);
            }
        });

        /** + - qty */
        $("#myTable").on('click', '.plus', function () {
            rowindex = $(this).closest('tr').index();
            var qty = parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val()) + 1;
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(qty.toFixed(1));
            checkQuantity(String(qty), true);
        });

        $("#myTable").on('click', '.minus', function () {
            rowindex = $(this).closest('tr').index();
            var qty = parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val()) - 1;
            if (qty > 0) {
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(qty.toFixed(1));
            } else {
                qty = (1).toFixed(1);
            }
            checkQuantity(String(qty), true);
        });


        //Change quantity
        $("#myTable").on('blur', '.qty', function () {
            rowindex = $(this).closest('tr').index();
            if ($(this).val() < 0.1 && $(this).val() != '') {
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(1);
                msg = new swal("Advertencia", "La cantidad no puede ser menor que 0.1!", "warning");

            }
            checkQuantity($(this).val(), true);
        });

        $("#myTable").on('click', '.qty', function () {
            rowindex = $(this).closest('tr').index();
        });

        /** + - price_unit */
        $("#myTable").on('click', '.plus-price', function () {
            rowindex = $(this).closest('tr').index();
            var price = parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .price_unit').val()) + 1;
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .price_unit').val(price.toFixed(2));
            product_price[rowindex] = price;
            console.log(product_price[rowindex]);
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.price_unit').val(price.toFixed(2));
            checkQuantity(1, true);
        });

        $("#myTable").on('click', '.minus-price', function () {
            rowindex = $(this).closest('tr').index();
            var price = parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .price_unit').val()) - 1;
            if (price > 0) {
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .price_unit').val(price.toFixed(2));
            } else {
                price = (1).toFixed(2);
            }
            product_price[rowindex] = price;
            console.log(product_price[rowindex]);
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.price_unit').val(price.toFixed(2));
            checkQuantity(1, true);
        });

        //Change price
        $("#myTable").on('blur', '.price_unit', function () {
            rowindex = $(this).closest('tr').index();
            if ($(this).val() < 1.0 && $(this).val() != '') {
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .price_unit').val(1);
                msg = new swal("Advertencia", "El monto no puede ser menor que 1.0!", "warning");

            }
            var price = $(this).val();
            product_price[rowindex] = price;
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.price_unit').val(price);

            checkQuantity(1, true);
        });

        $("#myTable").on('click', '.price_unit', function () {
            rowindex = $(this).closest('tr').index();
        });

        $(document).on('click', '.sound-btn', function () {
            var audio = $("#mysoundclip1")[0];
            audio.play();
        });

        $(document).on('click', '.product-img', function () {
            var customer_id = $('#customer_id').val();
            var warehouse_id = $('input[name="warehouse_id"]').val();
            var filter = [];
            if (!customer_id)
                msg = new swal("Informacion", "Por favor, seleccione cliente!", "info");
            else if (!warehouse_id)
                msg = new swal("Informacion", "Por favor, seleccione almacen!", "info");
            else if (!employee_id) {
                msg = new swal("Informacion", "Por favor, seleccione empleado de servicio!", "info");
            } else {
                var data = $(this).data('product');
                data = data.split(" ");
                pos = product_code.indexOf(data[0]);
                if (pos < 0)
                    msg = new swal("Error de Producto", "Producto no disponible en el almacen seleccionado!", "error");
                else {
                    filter.push(data[0]);
                    filter.push(customer_id);
                    productSearch(filter);
                }
            }
        });
        //Delete product
        $("table.order-list tbody").on("click", ".ibtnDel", function (event) {
            var audio = $("#mysoundclip2")[0];
            audio.play();
            rowindex = $(this).closest('tr').index();
            product_price.splice(rowindex, 1);
            product_discount.splice(rowindex, 1);
            tax_rate.splice(rowindex, 1);
            tax_name.splice(rowindex, 1);
            tax_method.splice(rowindex, 1);
            unit_name.splice(rowindex, 1);
            unit_operator.splice(rowindex, 1);
            unit_operation_value.splice(rowindex, 1);
            var service = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.service-pro')
                .val();
            if (service == "true") {
                emp_temp = false;
            }
            $(this).closest("tr").remove();
            calculateTotal();
        });

        //Edit product
        $("table.order-list").on("click", ".edit-product", function () {
            rowindex = $(this).closest('tr').index();
            edit();
        });

        //Update product
        $('button[name="update_btn"]').on("click", function () {
            var edit_discount = $('input[name="edit_discount"]').val();
            var edit_qty = $('input[name="edit_qty"]').val();
            var edit_unit_price = $('input[name="edit_unit_price"]').val();

            if (parseFloat(edit_discount) > parseFloat(edit_unit_price)) {
                msg = new swal("Error de Descuento", "Ingreso de descuento invalido!", "error");
                return;
            }

            if (edit_qty < 1) {
                $('input[name="edit_qty"]').val(1);
                edit_qty = 1;
                msg = new swal("Advertencia", "La cantidad no puede ser menor que 0.1!", "warning");
            }

            var tax_rate_all = <?php echo json_encode($tax_rate_all); ?>;

            tax_rate[rowindex] = parseFloat(tax_rate_all[$('select[name="edit_tax_rate"]').val()]);
            tax_name[rowindex] = $('select[name="edit_tax_rate"] option:selected').text();

            product_discount[rowindex] = $('input[name="edit_discount"]').val();
            if (product_type[pos] == 'standard') {
                var row_unit_operator = unit_operator[rowindex].slice(0, unit_operator[rowindex].indexOf(","));
                var row_unit_operation_value = unit_operation_value[rowindex].slice(0, unit_operation_value[
                    rowindex].indexOf(","));
                if (row_unit_operator == '*') {
                    product_price[rowindex] = $('input[name="edit_unit_price"]').val() / row_unit_operation_value;
                } else {
                    product_price[rowindex] = $('input[name="edit_unit_price"]').val() * row_unit_operation_value;
                }
                var position = $('select[name="edit_unit"]').val();
                var temp_operator = temp_unit_operator[position];
                var temp_operation_value = temp_unit_operation_value[position];
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.sale-unit').val(
                    temp_unit_name[position]);
                temp_unit_name.splice(position, 1);
                temp_unit_operator.splice(position, 1);
                temp_unit_operation_value.splice(position, 1);

                temp_unit_name.unshift($('select[name="edit_unit"] option:selected').text());
                temp_unit_operator.unshift(temp_operator);
                temp_unit_operation_value.unshift(temp_operation_value);

                unit_name[rowindex] = temp_unit_name.toString() + ',';
                unit_operator[rowindex] = temp_unit_operator.toString() + ',';
                unit_operation_value[rowindex] = temp_unit_operation_value.toString() + ',';
            }
            checkQuantity(edit_qty, false);
        });

        $('button[name="order_discount_btn"]').on("click", function () {
            calculateGrandTotal();
        });

        $('button[name="shipping_cost_btn"]').on("click", function () {
            calculateGrandTotal();
        });

        $('button[name="tip_btn"]').on("click", function () {
            calculateGrandTotal();
        });

        $('button[name="order_tax_btn"]').on("click", function () {
            calculateGrandTotal();
        });

        $(".payment-btn").on("click", function () {
            var audio = $("#mysoundclip2")[0];
            audio.play();
            var totalbs = $("#grand-total").text();
            var totalus = totalbs / tc;
            $('input[name="paid_amount"]').val($("#grand-total").text());
            $('input[name="paying_amount_us"]').val(totalus.toFixed(2));
            $('input[name="paying_amount"]').val($("#grand-total").text());
            $('.qc').data('initial', 1);
        });

        $("#presale-btn").on("click", function () {
            blockAmounts()
            var audio = $("#mysoundclip2")[0];
            audio.play();
            $('input[name="status"]').val(1);
            $('input[name="paying_amount"]').prop('required', false);
            $('input[name="paid_amount"]').prop('required', false);
            var rownumber = $('table.order-list tbody tr:last').index();
            if (rownumber < 0) {
                emp_temp = false;
                msg = new swal("Informacion de Items", "Por favor, inserte el producto para ordenar la tabla!", "warning");
            } else if (!employee_id) {
                msg = new swal("Informacion", "Por favor, seleccione empleado de servicio!", "info");
            } else if (permission_turno) {
                $.get('attention/verifyemp/' +
                    employee_id,
                    function (res) {
                        if (res.enabled)
                            $('.payment-form').submit();
                        else
                            msg = new swal("Mensaje", "Error no tiene un turno asignado, intente mas tarde", "error");
                    }).catch((error) => {
                        msg = new swal("Error de servicio!",
                            "Error: " + error + " contacte con soporte", "error");
                    });
            } else
                $('.payment-form').submit();

            if (emp_temp == false) {
                $("#submit-btn").removeClass("disabled noselect");
            } else {
                $("#submit-btn").addClass("disabled noselect");
                msg = new swal("Advertencia de Servicio", "Complete el empleado de servicio del item", "warning");
            }
        });

        $("#submit-btn").on("click", function () {
            $("#submit-btn").addClass("disabled noselect");
        });

        $('#add-payment').on('hidden.bs.modal', function (e) {
            $(this).modal("hide");
            if (checkStatusIntervalId) clearInterval(checkStatusIntervalId);
            if (timerIntervalId) clearInterval(timerIntervalId);
        });

        $('select[name="paid_by_id_select"]').on("change", function () {
            var id = $(this).val();
            $(".payment-form").off("submit");
            if (checkStatusIntervalId) clearInterval(checkStatusIntervalId);
            if (timerIntervalId) clearInterval(timerIntervalId);
            if (emp_temp == false)
                $("#submit-btn").removeClass("disabled noselect");
            else
                $("#submit-btn").addClass("disabled noselect");

            if (id == 3) {
                $('div.qc').hide();
                blockAmounts()
                giftCard();
            } else if (id == 4) {
                $('div.qc').hide();
                blockAmounts()
                creditCard();
            } else if (id == 5) {
                $('div.qc').hide();
                blockAmounts()
                cheque();
            } else {
                hide();
                if (id == 1) {
                    $('div.qc').show();
                    unblockAmounts()
                } else if (id == 7) {
                    $('div.qc').hide();
                    blockAmounts()
                    deposits();
                } else if (id == 6) {
                    $('div.qc').hide();
                    blockAmounts()
                    //qrsimple();
                }
            }
        });

        function blockAmounts() {
            $('input[name="paying_amount"]').prop('readonly', true);
            $('input[name="paying_amount_us"]').prop('readonly', true);
            $('input[name="paid_amount"]').prop('readonly', true);
        }

        function unblockAmounts() {
            $('input[name="paying_amount"]').prop('readonly', false);
            $('input[name="paying_amount_us"]').prop('readonly', false);
            $('input[name="paid_amount"]').prop('readonly', false);
        }


        $('#add-payment select[name="gift_card_id_select"]').on("change", function () {
            var balance = gift_card_amount[$(this).val()] - gift_card_expense[$(this).val()];
            $('#add-payment input[name="gift_card_id"]').val($(this).val());
            if ($('input[name="paid_amount"]').val() > balance) {
                alert('La cantidad excede el saldo de la tarjeta! Saldo de la tarjeta de regalo: ' + balance);
            }
        });

        $('#add-payment input[name="paying_amount"]').on("input", function () {
            change($(this).val(), $('input[name="paid_amount"]').val(), "BOB");
        });

        $('#add-payment input[name="paying_amount_us"]').on("input", function () {
            change($(this).val(), $('input[name="paid_amount"]').val(), "USD");
        });

        $('input[name="paid_amount"]').on("input", function () {
            if ($(this).val() > parseFloat($('input[name="paying_amount"]').val())) {
                msg = new swal("Advertencia de Pago", "La cantidad de pago no puede ser más grande que la cantidad recibida",
                    "warning");
                $(this).val('');
            } else if ($(this).val() > parseFloat($('#grand-total').text())) {
                msg = new swal("Advertencia de Pago", "La cantidad de pago no puede ser más grande que el gran total",
                    "warning");
                $(this).val('');
            }

            change($('input[name="paying_amount"]').val(), $(this).val(), "BS");
            var id = $('select[name="paid_by_id_select"]').val();
            if (id == 3) {
                var balance = gift_card_amount[$("#gift_card_id_select").val()] - gift_card_expense[$(
                    "#gift_card_id_select").val()];
                if ($(this).val() > balance)
                    alert('La cantidad excede el saldo de la tarjeta! Saldo de la tarjeta de regalo: ' + balance);
            } else if (id == 6) {
                if ($('input[name="paid_amount"]').val() > deposit[$('#customer_id').val()])
                    alert('Monto excede el depósito del cliente! Depósito del cliente: ' + deposit[$('#customer_id')
                        .val()]);
            }
        });


        function change(paying_amount, paid_amount, current) {
            console.log("TC : " + tc + " current use : " + current);
            if (current == "BOB") {
                var paying_amount_us = paying_amount / tc;
                console.log("BOB To USD : " + paying_amount_us.toFixed(2));
                $("#change").text(parseFloat(paying_amount - paid_amount).toFixed(2));
                $('input[name="paying_amount_us"]').val(paying_amount_us.toFixed(2));
            } else if (current == "USD") {
                var paying_amount_bs = paying_amount * tc;
                console.log("USD To BOB : " + paying_amount_bs.toFixed(2));
                $("#change").text(parseFloat(paying_amount_bs - paid_amount).toFixed(2));
                $('input[name="paying_amount"]').val(paying_amount_bs.toFixed(2));
            }
        }


        function confirmDelete() {
            if (confirm("Esta seguro de eliminar?")) {
                return true;
            }
            return false;
        }

        function productSearch(data, isCourtesy = false) {
            var alm = $('input[name="warehouse_id"]').val();
            qty_list2 = null;
            $.ajax({
                type: 'GET',
                url: 'sales/lims_product_search',
                data: {
                    data: data
                },
                success: function (data) {
                    var flag = 1;
                    $(".product-code").each(function (i) {
                        if ($(this).val() == data[1]) {
                            rowindex = i;
                            var pre_qty = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) +
                                ') .qty').val();
                            if (pre_qty)
                                var qty = parseFloat(pre_qty) + 1;
                            else
                                var qty = 1;
                            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(
                                qty);
                            flag = 0;
                            checkQuantity(String(qty), true);
                            flag = 0;
                        }
                    });
                    $("input[name='product_code_name']").val('');
                    if (flag) {
                        if (isCourtesy) {
                            data[2] = 0;
                            $.get('sales/getstockprofinish/' + data[1] + '/' + alm, function (res) {
                                //console.log(res);
                                qty_list2 = res[1];
                                if (res[0] === true) {
                                    msg = new swal("Error de Stock!",
                                        "No hay stock disponible! en uno o mas insumos", "error");
                                } else {
                                    addNewProduct(data);
                                }
                            }).catch((error) => {
                                msg = new swal("Error de Insumos!",
                                    "No hay stock disponible! en uno o mas insumos", "error");
                            });
                        } else {
                            if (data[2] <= 0) {
                                msg = new swal({
                                    title: 'Precio no definido para este cliente en producto',
                                    text: 'Por favor ingrese un precio:',
                                    content: {
                                        element: "input",
                                        attributes: {
                                            defaultValue: 0,
                                        }
                                    },
                                })
                                    .then((amount) => {
                                        if (amount > data[2]) {
                                            data[2] = amount;
                                            $.get('sales/getstockprofinish/' + data[1] + '/' + alm,
                                                function (res) {
                                                    //console.log(res);
                                                    qty_list2 = res[1];
                                                    if (res[0] === true) {
                                                        msg = new swal("Error de Stock!",
                                                            "No hay stock disponible! en uno o mas insumos",
                                                            "error");
                                                    } else {
                                                        addNewProduct(data);
                                                    }
                                                }).catch((error) => {
                                                    msg = new swal("Error de Insumos!",
                                                        "No hay stock disponible! en uno o mas insumos",
                                                        "error");
                                                });
                                        } else {
                                            msg = new swal("Error al Ingresar",
                                                "Monto ingresado invalido, intente nuevamente!", "error"
                                            );
                                        }
                                    });
                            } else {
                                $.get('sales/getstockprofinish/' + data[1] + '/' + alm, function (res) {
                                    //console.log(res);
                                    qty_list2 = res[1];
                                    if (res[0] === true) {
                                        msg = new swal("Error de Stock!",
                                            "No hay stock disponible! en uno o mas insumos", "error"
                                        );

                                    } else {
                                        addNewProduct(data);
                                    }
                                }).catch((error) => {
                                    msg = new swal("Error de Insumos!",
                                        "No hay stock disponible! en uno o mas insumos", "error");
                                });
                            }
                        }
                    }
                }
            });
        }

        function addNewProduct(data) {
            console.log(data);
            var newRow = $("<tr>");
            var cols = '';
            var emp = 0;
            if (data[12] != null && data[12].length > 0) {
                emp = employee_id
            } else {
                emp = 0;
            }
            temp_unit_name = (data[6]).split(',');
            cols += '<td class="col-sm-3 product-title" style="text-align: start;"><strong>' + data[0] + '</strong> [' +
                data[1] + '] <input type="hidden" id="service_' + data[1] +
                '" class="service-pro" name="service_kind" value="false"/></td>';
            //cols += '<td class="col-sm-2 product-price" style="text-align: end;"></td>';
            cols += '<td class="col-sm-3" ><div class="input-group" ><span class="input-group-btn" ><button type="button" class="btn btn-default minus-price"  style="padding: 2px 10px;"><span class="dripicons-minus"></span></button></span><input type="text" name="price_unit[]" class="form-control price_unit numkey input-number" value="1" step="1.0" required><span class="input-group-btn">' +
                '<button type="button" class="btn btn-default plus-price" style="padding: 2px 10px;"><span class="dripicons-plus"></span></button></span></div><div class="input-group"></div></td>';
            cols +=
                '<td class="col-sm-3" style="left: 20px;"><div class="input-group"><span class="input-group-btn"><button type="button" class="btn btn-default minus"  style="padding: 2px 10px;"><span class="dripicons-minus"></span></button></span><input type="text" name="qty[]" class="form-control qty numkey input-number" value="1" step="0.01" required><span class="input-group-btn">' +
                '<button type="button" class="btn btn-default plus" style="padding: 2px 10px;"><span class="dripicons-plus"></span></button></span></div><div class="input-group"><select id="cortesia_id_' +
                data[1] +
                '" name="cortesia_id" class="selectpicker form-control courtesy-select" data-live-search="true" data-live-search-style="contains" onchange="validatemp()"><option value="0">Seleccione Cortesia...</option></select></div></td>';
            cols += '<td class="col-sm-2 sub-total" style="text-align: end;"></td>';
            cols +=
                '<td class="col-sm-1"><button type="button" class="ibtnDel btn btn-danger btn-sm"><i class="dripicons-cross"></i></button></td>';
            cols += '<input type="hidden" class="product-code" name="product_code[]" value="' + data[1] + '"/>';
            cols += '<input type="hidden" class="product-id" name="product_id[]" value="' + data[9] + '"/>';
            cols += '<input type="hidden" class="sale-unit" name="sale_unit[]" value="' + temp_unit_name[0] + '"/>';
            cols += '<input type="hidden" class="employee-id" name="employee[]" value="' + emp + '"/>';
            cols += '<input type="hidden" class="net_unit_price" name="net_unit_price[]" />';
            cols += '<input type="hidden" class="discount-value" name="discount[]" />';
            cols += '<input type="hidden" class="tax-rate" name="tax_rate[]" value="' + data[3] + '"/>';
            cols += '<input type="hidden" class="tax-value" name="tax[]" />';
            cols += '<input type="hidden" class="subtotal-value" name="subtotal[]" />';
            cols += '<input type="hidden" class="sub_total_unit" name="sub_total_unit[]" />';

            newRow.append(cols);
            if (keyboard_active == 1) {
                $("table.order-list tbody").append(newRow).find('.qty').keyboard({
                    usePreview: false,
                    layout: 'custom',
                    display: {
                        'accept': '&#10004;',
                        'cancel': '&#10006;'
                    },
                    customLayout: {
                        'normal': ['1 2 3', '4 5 6', '7 8 9', '0 {dec} {bksp}', '{clear} {cancel} {accept}']
                    },
                    restrictInput: true,
                    preventPaste: true,
                    autoAccept: true,
                    css: {
                        container: 'center-block dropdown-menu',
                        buttonDefault: 'btn btn-default',
                        buttonHover: 'btn-primary',
                        buttonAction: 'active',
                        buttonDisabled: 'disabled'
                    },
                });
                $("table.order-list tbody").append(newRow).find('.price_unit').keyboard({
                    usePreview: false,
                    layout: 'custom',
                    display: {
                        'accept': '&#10004;',
                        'cancel': '&#10006;'
                    },
                    customLayout: {
                        'normal': ['1 2 3', '4 5 6', '7 8 9', '0 {dec} {bksp}', '{clear}']
                    },
                    restrictInput: true,
                    preventPaste: true,
                    autoAccept: true,
                    css: {
                        container: 'center-block dropdown-menu',
                        buttonDefault: 'btn btn-default',
                        buttonHover: 'btn-primary',
                        buttonAction: 'active',
                        buttonDisabled: 'disabled'
                    },
                });
            } else
                $("table.order-list tbody").append(newRow);

            product_price.push(parseFloat(data[2]) + parseFloat(data[2] * customer_group_rate));
            product_discount.push('0.00');
            tax_rate.push(parseFloat(data[3]));
            tax_name.push(data[4]);
            tax_method.push(data[5]);
            unit_name.push(data[6]);
            unit_operator.push(data[7]);
            unit_operation_value.push(data[8]);
            if (data[11] != null) {
                if (data[11].length > 0) {
                    addOptions(`cortesia_id_${data[1]}`, data[11], 1);
                    //add a tabla cuando selecciona
                    $(`#cortesia_id_${data[1]}`).on("change", function () {
                        var filter = [];
                        var customer_id = $('#customer_id').val();
                        if (emp_temp == false) {
                            filter.push($(this).val());
                            filter.push(customer_id);
                            productSearch(filter, true);
                        } else {
                            $(`#cortesia_id_${data[1]}`).val(0);
                        }
                    });
                    $('.selectpicker').selectpicker('refresh');
                } else {
                    $(`#cortesia_id_${data[1]}`).addClass('d-none');
                }
            } else {
                $('#cortesia_id').addClass('d-none');
                $(`#cortesia_id_${data[1]}`).addClass('d-none');
            }
            if (data[12] != null) {
                $(`#service_${data[1]}`).val('true');
            }
            rowindex = newRow.index();
            checkQuantity(1, true);
        }

        // Rutina para agregar opciones a un <select>
        function addOptions(domElement, array, op) {
            var select = document.getElementById(domElement);
            if (op == 1) {
                for (value in array) {
                    var option = document.createElement("option");
                    option.text = array[value].name;
                    option.value = array[value].code;
                    select.add(option);
                }
            }
            if (op == 2) {
                for (value in array) {
                    var option = document.createElement("option");
                    option.text = array[value].name;
                    option.value = array[value].id;
                    select.add(option);
                }
            }
        }

        function validatemp() {
            if (emp_temp == true) {
                msg = new swal("Advertencia de Servicio", "Seleccione el empleado de servicio antes de ingresar otro item",
                    "warning");
            }
        }

        function edit() {
            var row_product_name_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find(
                'td:nth-child(1)').text();
            $('#modal_header').text(row_product_name_code);

            var qty = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val();
            $('input[name="edit_qty"]').val(qty);

            $('input[name="edit_discount"]').val(parseFloat(product_discount[rowindex]).toFixed(2));

            var tax_name_all = <?php echo json_encode($tax_name_all); ?>;
            pos = tax_name_all.indexOf(tax_name[rowindex]);
            $('select[name="edit_tax_rate"]').val(pos);

            var row_product_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-code')
                .val();
            pos = product_code.indexOf(row_product_code);
            if (product_type[pos] == 'standard') {
                unitConversion();
                temp_unit_name = (unit_name[rowindex]).split(',');
                temp_unit_name.pop();
                temp_unit_operator = (unit_operator[rowindex]).split(',');
                temp_unit_operator.pop();
                temp_unit_operation_value = (unit_operation_value[rowindex]).split(',');
                temp_unit_operation_value.pop();
                $('select[name="edit_unit"]').empty();
                $.each(temp_unit_name, function (key, value) {
                    $('select[name="edit_unit"]').append('<option value="' + key + '">' + value + '</option>');
                });
                $("#edit_unit").show();
            } else {
                row_product_price = product_price[rowindex];
                $("#edit_unit").hide();
            }
            $('input[name="edit_unit_price"]').val(row_product_price.toFixed(2));
            $('.selectpicker').selectpicker('refresh');
        }

        function checkQuantity(sale_qty, flag) {
            var row_product_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-code')
                .val();
            pos = product_code.indexOf(row_product_code);
            var alm = $('input[name="warehouse_id"]').val();
            if (pos == -1) { //no existe producto
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').remove();
                msg = new swal("Error de Stock!", "No hay stock disponible!", "error");
            }
            if (product_type[pos] == 'standard') {
                var operator = unit_operator[rowindex].split(',');
                var operation_value = unit_operation_value[rowindex].split(',');
                /*if(operator[0] == '*')
                            total_qty = sale_qty * operation_value[0];
                        else if(operator[0] == '/')
                            total_qty = sale_qty / operation_value[0];
                */
                total_qty = sale_qty * 1;
                if (total_qty > parseFloat(product_qty[pos])) {
                    msg = new swal("Advertencia de Stock!", "Cantidad excede el stock disponible!", "warning");
                    if (flag) {
                        sale_qty = 1;
                        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(sale_qty);
                        checkQuantity(sale_qty, true);
                    } else {
                        edit();
                        return;
                    }
                }
            } else if (product_type[pos] == 'combo') {
                child_id = product_list[pos].split(',');
                child_qty = qty_list[pos].split(',');
                console.log(child_id + " - " + child_qty);
                $(child_id).each(function (index) {
                    var position = product_id.indexOf(parseInt(child_id[index]));
                    console.log(sale_qty + " - " + product_qty[position]);
                    if (parseFloat(sale_qty * child_qty[index]) > product_qty[position]) {
                        msg = new swal("Advertencia de Stock!", "Cantidad excede el stock disponible!", "warning");

                        if (flag) {
                            sale_qty = 1;
                            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(
                                sale_qty);
                        } else {
                            edit();
                            flag = true;
                            return false;
                        }
                    }
                });
            } else if (product_type[pos] == 'producto_terminado') {
                child_id = product_list[pos].split(',');
                child_qty = qty_list[pos].split(',');
                var sold = false;
                console.log("child_id : " + child_id + " - child_qty : " + child_qty);
                $.get('sales/getstockprofinish/' + row_product_code + '/' + alm, function (res) {
                    $(child_id).each(function (index) {
                        //res[1].forEach(function(stock) {
                        console.log(sale_qty * child_qty[index] + " - " + res[1][index].qty);
                        if (parseFloat(sale_qty * child_qty[index]) > res[1][index].qty) {
                            sold = true;
                            sale_qty = 1;
                            msg = new swal("Advertencia de Stock!",
                                "Cantidad excede el stock disponible! de uno o mas insumos, alerta en insumo : " +
                                res[1][index].code, "warning");

                            if (flag) {
                                sale_qty = 1;
                                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find(
                                    '.qty').val(sale_qty);
                            } else {
                                edit();
                                flag = true;
                                return false;
                            }

                        }
                        //});
                    });
                }).catch((error) => {
                    msg = new swal("Error de Insumos!", "No hay stock disponible! en uno o mas insumos", "error");
                });
            } else {
                total_qty = sale_qty * 1;
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(sale_qty);
            }

            if (!flag) {
                $('#editModal').modal('hide');
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(sale_qty);
            }
            if (sold) {
                sale_qty = 1;
            }
            calculateRowProductData(sale_qty);

        }

        function calculateRowProductData(quantity) {
            if (product_type[pos] == 'standard')
                unitConversion();
            else
                row_product_price = product_price[rowindex];

            console.log(product_price[rowindex]);
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.discount-value').val((product_discount[
                rowindex] * quantity).toFixed(2));
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-rate').val(tax_rate[rowindex]
                .toFixed(2));

            if (tax_method[rowindex] == 1) {
                var net_unit_price = row_product_price - product_discount[rowindex];
                var tax = net_unit_price * quantity * (tax_rate[rowindex] / 100);
                var sub_total = (net_unit_price * quantity) + tax;

                if (parseFloat(quantity))
                    var sub_total_unit = sub_total / quantity;
                else
                    var sub_total_unit = sub_total;

                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.net_unit_price').val(net_unit_price
                    .toFixed(2));
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-value').val(tax.toFixed(2));
                //$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(2)').text(sub_total_unit.toFixed(2));
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.price_unit').val(sub_total_unit.toFixed(2));
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(4)').text(sub_total
                    .toFixed(2));
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.subtotal-value').val(sub_total
                    .toFixed(2));
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.sub_total_unit').val(sub_total_unit
                    .toFixed(2));
            } else {
                var sub_total_unit = row_product_price - product_discount[rowindex];
                var iva = (tax_rate[rowindex] / 100) * sub_total_unit;
                var net_unit_price = sub_total_unit - iva;
                var tax = iva * quantity;
                var sub_total = sub_total_unit * quantity;

                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.net_unit_price').val(net_unit_price
                    .toFixed(2));
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-value').val(tax.toFixed(2));
                //$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(2)').text(sub_total_unit.toFixed(2));
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.price_unit').val(sub_total_unit.toFixed(2));
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(4)').text(sub_total
                    .toFixed(2));
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.subtotal-value').val(sub_total
                    .toFixed(2));
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.sub_total_unit').val(sub_total_unit
                    .toFixed(2));
            }

            calculateTotal();
        }

        function unitConversion() {
            var row_unit_operator = unit_operator[rowindex].slice(0, unit_operator[rowindex].indexOf(","));
            var row_unit_operation_value = unit_operation_value[rowindex].slice(0, unit_operation_value[rowindex].indexOf(
                ","));

            /*if (row_unit_operator == '*') {
                row_product_price = product_price[rowindex] * row_unit_operation_value;
            } else {
                row_product_price = product_price[rowindex] / row_unit_operation_value;
            }*/
            row_product_price = product_price[rowindex] * 1;


        }

        function calculateTotal() {
            //Sum of quantity
            var total_qty = 0;
            $("table.order-list tbody .qty").each(function (index) {
                if ($(this).val() == '') {
                    total_qty += 0;
                } else {
                    total_qty += parseFloat($(this).val());
                }
            });
            $('input[name="total_qty"]').val(total_qty);

            //Sum of discount
            var total_discount = 0;
            $("table.order-list tbody .discount-value").each(function () {
                total_discount += parseFloat($(this).val());
            });

            $('input[name="total_discount"]').val(total_discount.toFixed(2));

            //Sum of tax
            var total_tax = 0;
            $(".tax-value").each(function () {
                total_tax += parseFloat($(this).val());
            });

            $('input[name="total_tax"]').val(total_tax.toFixed(2));

            //Sum of subtotal
            var total = 0;
            $(".sub-total").each(function () {
                total += parseFloat($(this).text());
            });
            $('input[name="total_price"]').val(total.toFixed(2));

            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            var item = $('table.order-list tbody tr:last').index();
            var total_qty = parseFloat($('input[name="total_qty"]').val());
            var subtotal = parseFloat($('input[name="total_price"]').val());
            var order_tax = parseFloat($('select[name="order_tax_rate_select"]').val());
            var order_discount = parseFloat($('input[name="order_discount"]').val());
            if (!order_discount)
                order_discount = 0.00;
            $("#discount").text(order_discount.toFixed(2));

            var shipping_cost = parseFloat($('input[name="shipping_cost"]').val());
            if (!shipping_cost)
                shipping_cost = 0.00;

            var tip_cost = parseFloat($('input[name="tips"]').val());
            if (!tip_cost)
                tip_cost = 0.00;

            item = ++item + '(' + total_qty + ')';
            order_tax = (subtotal - order_discount) * (order_tax / 100);
            var grand_total = (subtotal + order_tax + shipping_cost + tip_cost) - order_discount;
            $('input[name="grand_total"]').val(grand_total.toFixed(2));

            $('#item').text(item);
            $('input[name="item"]').val($('table.order-list tbody tr:last').index() + 1);
            $('#subtotal').text(subtotal.toFixed(2));
            $('#tax').text(order_tax.toFixed(2));
            $('input[name="order_tax"]').val(order_tax.toFixed(2));
            $('#shipping-cost').text(shipping_cost.toFixed(2));
            $('#tips_amount').text(tip_cost.toFixed(2));
            $('#grand-total').text(grand_total.toFixed(2));
            $('input[name="grand_total"]').val(grand_total.toFixed(2));
        }

        // Limpiar solo los servicios/items agregados (sin tocar empleado u otros campos)
        $(document).on('click', '#clear-services-btn', function () {
            var rownumber = $('table.order-list tbody tr:last').index();
            if (rownumber < 0) {
                msg = new swal('Información', 'No hay servicios agregados para limpiar.', 'info');
                return;
            }

            msg = new swal({
                title: "¿Está seguro de limpiar los servicios agregados?",
                text: "Esta acción eliminará todos los items agregados en la tabla.",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    var lastIndex = $('table.order-list tbody tr:last').index();
                    while (lastIndex >= 0) {
                        product_price.pop();
                        product_discount.pop();
                        tax_rate.pop();
                        tax_name.pop();
                        tax_method.pop();
                        unit_name.pop();
                        unit_operator.pop();
                        unit_operation_value.pop();
                        $('table.order-list tbody tr:last').remove();
                        lastIndex = $('table.order-list tbody tr:last').index();
                    }
                    calculateTotal();
                }
            });
        });

        function hide() {
            $(".card-element").hide();
            $("#name_card").hide();
            $(".card-errors").hide();
            $(".cheque").hide();
            $(".gift-card").hide();
            $(".qrsimple").hide();
            $('input[name="cheque_no"]').attr('required', false);
        }


        async function fetchPost(url = "", body = {}, headers = {}) {
            const resp = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    ...headers
                },
                body: JSON.stringify(body),
            });
            const result = await resp.json();
            if (resp.ok) {
                return result;
            } else {
                throw new Error("Ups ocurrio un error");
            }
        }

        function cancel(rownumber) {
            while (rownumber >= 0) {
                product_price.pop();
                product_discount.pop();
                tax_rate.pop();
                tax_name.pop();
                tax_method.pop();
                unit_name.pop();
                unit_operator.pop();
                unit_operation_value.pop();
                $('table.order-list tbody tr:last').remove();
                rownumber--;
            }
            $('input[name="shipping_cost"]').val('');
            $('input[name="order_discount"]').val('');
            $('input[name="employee_name"]').val('');
            $('input[name="employee_id"]').val('');
            $('select[name="order_tax_rate_select"]').val(0);
            employee_id = null;
            calculateTotal();
        }

        function confirmCancel() {
            var audio = $("#mysoundclip2")[0];
            audio.play();

            msg = new swal({
                title: "Esta seguro de querer cancelar?",
                text: "Esta accion limpiara los item de la tabla!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
                .then((res) => {
                    if (res) {
                        cancel($('table.order-list tbody tr:last').index());
                    } else {
                        return false;
                    }
                });
        }

        function confirmExit() {
            var audio = $("#mysoundclip2")[0];
            audio.play();

            msg = new swal({
                title: "Esta seguro de querer salir?",
                text: "Esta accion cerrara el panel de Pre-Vena!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
                .then((res) => {
                    if (res) {
                        window.location.href = baseUrl;
                    } else {
                        return false;
                    }
                });
        }

        $(document).on('submit', '.payment-form', function (e) {
            var rownumber = $('table.order-list tbody tr:last').index();
            if (rownumber < 0) {
                emp_temp = false;
                msg = new swal("Informacion", "Por favor, inserte el producto para ordenar la tabla!", "info");

                e.preventDefault();
            } else if (parseFloat($('input[name="paying_amount"]').val()) < parseFloat($(
                'input[name="paid_amount"]').val())) {
                msg = new swal("Informacion", "La cantidad de pago no puede ser más grande que la cantidad recibida", "info");
                e.preventDefault();
            }
            $('input[name="paid_by_id"]').val($('select[name="paid_by_id_select"]').val());
            $('input[name="order_tax_rate"]').val($('select[name="order_tax_rate_select"]').val());

        });

        $('#product-table').DataTable({
            "order": [],
            'pageLength': product_row_number,
            'language': {
                'paginate': {
                    'previous': '<i class="fa fa-angle-left"></i>',
                    'next': '<i class="fa fa-angle-right"></i>'
                }
            },
            dom: 'tp'
        });

        $("#showreport-btn").on("click", function () {
            $("#totalrp").val("");
            $("#totalqr_rp").val("");
            $("#totalrp_win").val("");
            filtereport();
        });

        function filtereport() {
            var employee = 0;
            employee = $("input[name='employee_id']").val();
            if (employee == null || employee == '') {
                employee = 0;
                $('#report-comission').modal('hide')
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
                msg = new swal("Mensaje", "Seleccione su empleado de servicio", "warning");

                return false;
            }
            var utc = "<?php echo date('Y-m-d'); ?>";
            $('#table-report').DataTable({
                destroy: true,
                "processing": true,
                "serverSide": true,
                "ajax": {
                    url: baseUrl + "/report/service_employeecomission_report",
                    dataType: "json",
                    type: "post",
                    data: {
                        start_date: utc,
                        end_date: utc,
                        employee_id: employee,
                        guess: true
                    },

                },
                "createdRow": function (row, data, dataIndex) {

                },
                "columns": [{
                    "data": "key"
                },
                {
                    "data": "service"
                },
                {
                    "data": "reference_no"
                },
                {
                    "data": "employee"
                },
                {
                    "data": "date"
                },
                {
                    "data": "total"
                },
                {
                    "data": "percentaje"
                },
                {
                    "data": "comision_qr"
                },
                {
                    "data": "comision"
                },
                ],
                'language': {

                    'lengthMenu': '_MENU_ {{ trans('file.records per page') }}',
                    "info": '<small>{{ trans('file.Showing') }} _START_ - _END_ (_TOTAL_)</small>',
                    "search": '{{ trans('file.Search') }}',
                    'paginate': {
                        'previous': '<i class="dripicons-chevron-left"></i>',
                        'next': '<i class="dripicons-chevron-right"></i>'
                    }
                },
                "drawCallback": function (response) {
                    console.log(response.json);
                    $("#totalrp").val(response.json.total + "Bs.");
                    $("#totalqr_rp").val(response.json.total_qr + "Bs.");
                    $("#totalrp_win").val(response.json.total_com + "Bs.");
                },
                order: [
                    ['1', 'desc']
                ],
                'columnDefs': [{
                    "orderable": false,
                },
                {
                    'render': function (data, type, row, meta) {

                        return data;
                    },
                    'targets': [0]
                }
                ],
                'lengthMenu': [
                    [5, 10, 25, -1],
                    [5, 10, 25, "All"]
                ]
            });
        }
    </script>
@endsection
@section('scripts')
@endsection