@extends('layout.main') 
@section('content')
    @if(session()->has('not_permitted'))
    <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div> 
    @endif
    <section class="forms">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4>{{trans('file.Add Sale')}}</h4>
                        </div>
                        <div class="card-body">
                            <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
                            {!! Form::open(['route' => 'sales.store', 'method' => 'post', 'files' => true, 'class' => 'payment-form']) !!}
                            @php
                                if ($lims_pos_setting_data) {
                                    $keybord_active = $lims_pos_setting_data->keybord_active;
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
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{trans('file.customer')}} *</label>
                                                @if ($lims_pos_setting_data)
                                                    <input type="hidden" name="customer_id_hidden"
                                                        value="{{ $lims_pos_setting_data->customer_id }}">
                                                @endif
                                                @if ($customer_active)
                                                    <select required name="customer_id" id="customer_id"
                                                        class="selectpicker form-control" data-live-search="true"
                                                        data-live-search-style="contains" title="Select customer..."
                                                        style="width: 100px">
                                                        <?php $deposit = []; ?>
                                                        @foreach ($lims_customer_list as $customer)
                                                            @php $deposit[$customer->id] = $customer->deposit - $customer->expense; @endphp
                                                            <option value="{{ $customer->id }}">
                                                                {{ $customer->name . ' (' . $customer->phone_number . ')' }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <button type="button" class="btn btn-default btn-sm"
                                                        data-toggle="modal" data-target="#addCustomer"><i
                                                            class="dripicons-plus"></i></button>
                                                @else
                                                    <?php $deposit = []; ?>
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
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{trans('file.Warehouse')}} *</label>
                                                @if ($lims_pos_setting_data)
                                                    <input type="hidden" name="warehouse_id_hidden"
                                                        value="{{ $lims_pos_setting_data->warehouse_id }}">
                                                @endif
                                                <select required name="warehouse_id" id="warehouse_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" title="Select warehouse...">
                                                    @foreach($lims_warehouse_list as $warehouse)
                                                    <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{trans('file.Biller')}} *</label>
                                                @if ($lims_pos_setting_data)
                                                    <input type="hidden" name="biller_id_hidden"
                                                        value="{{ $lims_pos_setting_data->biller_id }}">
                                                @endif
                                                <select required name="biller_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" title="Select Biller...">
                                                    @foreach($lims_biller_list as $biller)
                                                    <option value="{{$biller->id}}">{{$biller->name . ' (' . $biller->company_name . ')'}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <label>{{trans('file.Select Product')}}</label>
                                            <div class="search-box input-group">
                                                <button type="button" class="btn btn-secondary btn-lg"><i class="fa fa-barcode"></i></button>
                                                <input type="text" name="product_code_name" id="lims_productcodeSearch" 
                                                    placeholder="Por favor, escriba el código del producto y seleccione..." class="form-control" 
                                                    onclick="validatemp()" onkeyup="validatemp()"
                                                    onkeypress="validatemp()"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-5">
                                        <div class="col-md-12">
                                            <h5>{{trans('file.Order Table')}} *</h5>
                                            <div class="table-responsive mt-3">
                                                <table id="myTable" class="table table-hover order-list">
                                                    <thead>
                                                        <tr>
                                                            <th>{{trans('file.name')}}</th>
                                                            <th>{{trans('file.Code')}}</th>
                                                            <th>{{trans('file.Quantity')}}</th>
                                                            <th>{{trans('file.Net Unit Price')}}</th>
                                                            <th>{{trans('file.Discount')}}</th>
                                                            <th>{{trans('file.Tax')}}</th>
                                                            <th>{{trans('file.Subtotal')}}</th>
                                                            <th><i class="dripicons-trash"></i></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    </tbody>
                                                    <tfoot class="tfoot active">
                                                        <th colspan="2">{{trans('file.Total')}}</th>
                                                        <th id="total-qty">0</th>
                                                        <th></th>
                                                        <th id="total-discount">0.00</th>
                                                        <th id="total-tax">0.00</th>
                                                        <th id="total">0.00</th>
                                                        <th><i class="dripicons-trash"></i></th>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="hidden" name="total_qty" />
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="hidden" name="total_discount" />
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="hidden" name="total_tax" />
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
                                                <input type="hidden" name="coupon_discount" />
                                                <input type="hidden" name="sale_status" value="1" />
                                                <input type="hidden" name="status" value="1" />
                                                <input type="hidden" name="coupon_active">
                                                <input type="hidden" name="coupon_id">
                                                <input type="hidden" name="coupon_discount" />
                                                <input type="hidden" name="date_sell" />
                                                <input type="hidden" name="pos" value="1" />
                                                <input type="hidden" name="presale_id" value="0" />
                                                <input type="hidden" name="attentionshift_id" value="0" />
                                                <input type="hidden" name="draft" value="0" />
                                                <input type="hidden" name="total_tips" value="0" />
                                                
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{trans('file.Order Tax')}}</label>
                                                <select class="form-control" name="order_tax_rate">
                                                    <option value="0">No Tax</option>
                                                    @foreach($lims_tax_list as $tax)
                                                    <option value="{{$tax->rate}}">{{$tax->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>
                                                    <strong>{{trans('file.Order Discount')}}</strong>
                                                </label>
                                                <input type="number" name="order_discount" class="form-control" step="any"/>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>
                                                    <strong>{{trans('file.Shipping Cost')}}</strong>
                                                </label>
                                                <input type="number" name="shipping_cost" class="form-control" step="any"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{trans('file.Attach Document')}}</label> <i class="dripicons-question" data-toggle="tooltip" title="Only jpg, jpeg, png, gif, pdf, csv, docx, xlsx and txt file is supported"></i>
                                                <input type="file" name="document" class="form-control" />
                                                @if($errors->has('extension'))
                                                    <span>
                                                    <strong>{{ $errors->first('extension') }}</strong>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{trans('file.Sale Status')}} *</label>
                                                <select name="sale_status" class="form-control">
                                                    <option value="1">{{trans('file.Completed')}}</option>
                                                    <option value="2">{{trans('file.Pending')}}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{trans('file.Payment Status')}} *</label>
                                                <select name="payment_status" class="form-control">
                                                    <option value="1">{{trans('file.Pending')}}</option>
                                                    <option value="2">{{trans('file.Due')}}</option>
                                                    <option value="3">{{trans('file.Partial')}}</option>
                                                    <option value="4">{{trans('file.Paid')}}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="payment">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>{{trans('file.Paid By')}}</label>
                                                    <select name="paid_by_id" class="form-control">
                                                        <option value="1">Cash</option>
                                                        <option value="2">Gift Card</option>
                                                        <option value="3">Credit Card</option>
                                                        <option value="4">Cheque</option>
                                                        <option value="5">Paypal</option>
                                                        <option value="6">Deposit</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>{{trans('file.Recieved Amount')}} *</label>
                                                    <input type="number" name="paying_amount" class="form-control" id="paying-amount" step="any" />
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>{{trans('file.Paying Amount')}} *</label>
                                                    <input type="number" name="paid_amount" class="form-control" id="paid-amount" step="any"/>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>{{trans('file.Change')}}</label>
                                                    <p id="change" class="ml-2">0.00</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <div class="card-element form-control" >
                                                    </div>
                                                    <div class="card-errors" role="alert"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row" id="gift-card">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label> {{trans('file.Gift Card')}} *</label>
                                                    <select id="gift_card_id" name="gift_card_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" title="Select Gift Card..."></select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row" id="cheque">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>{{trans('file.Cheque Number')}} *</label>
                                                    <input type="text" name="cheque_no" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label>{{trans('file.Payment Note')}}</label>
                                                <textarea rows="3" class="form-control" name="payment_note"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{trans('file.Sale Note')}}</label>
                                                <textarea rows="5" class="form-control" name="sale_note"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{trans('file.Staff Note')}}</label>
                                                <textarea rows="5" class="form-control" name="staff_note"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <input type="submit" value="{{trans('file.submit')}}" class="btn btn-primary" id="submit-button">
                                    </div>
                                </div>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <table class="table table-bordered table-condensed totals">
                <td><strong>{{trans('file.Items')}}</strong>
                    <span class="pull-right" id="item">0.00</span>
                </td>
                <td><strong>{{trans('file.Total')}}</strong>
                    <span class="pull-right" id="subtotal">0.00</span>
                </td>
                <td><strong>{{trans('file.Order Tax')}}</strong>
                    <span class="pull-right" id="order_tax">0.00</span>
                </td>
                <td><strong>{{trans('file.Order Discount')}}</strong>
                    <span class="pull-right" id="order_discount">0.00</span>
                </td>
                <td><strong>{{trans('file.Shipping Cost')}}</strong>
                    <span class="pull-right" id="shipping_cost">0.00</span>
                </td>
                <td><strong>{{trans('file.grand total')}}</strong>
                    <span class="pull-right" id="grand_total">0.00</span>
                </td>
            </table>
        </div>
        <div id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
            <div role="document" class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="modal_header" class="modal-title"></h5>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="form-group">
                                <label>{{trans('file.Quantity')}}</label>
                                <input type="number" name="edit_qty" class="form-control" step="any">
                            </div>
                            <div class="form-group">
                                <label>{{trans('file.Unit Discount')}}</label>
                                <input type="number" name="edit_discount" class="form-control" step="any">
                            </div>
                            <div class="form-group">
                                <label>{{trans('file.Unit Price')}}</label>
                                <input type="number" name="edit_unit_price" class="form-control" step="any">
                            </div>
                            <?php
                    $tax_name_all[] = 'No Tax';
                    $tax_rate_all[] = 0;
                    foreach($lims_tax_list as $tax) {
                        $tax_name_all[] = $tax->name;
                        $tax_rate_all[] = $tax->rate;
                    }
                ?>
                                <div class="form-group">
                                    <label>{{trans('file.Tax Rate')}}</label>
                                    <select name="edit_tax_rate" class="form-control selectpicker">
                                        @foreach($tax_name_all as $key => $name)
                                        <option value="{{$key}}">{{$name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="edit_unit" class="form-group">
                                    <label>{{trans('file.Product Unit')}}</label>
                                    <select name="edit_unit" class="form-control selectpicker">
                                    </select>
                                </div>
                                <button type="button" name="update_btn" class="btn btn-primary">{{trans('file.update')}}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
@endsection 
@section('scripts')
<script type="text/javascript">
    let date = new Date();
    let day = date.getDate();
    let month = date.getMonth() + 1;
    let year = date.getFullYear();
    var timerIntervalId;
    var checkStatusIntervalId;
    var presale_id = [];
    var changedate = <?php echo json_encode($lims_pos_setting_data->date_sell); ?>;
    $('input[name="presale_id"]').val(0);
    $('input[name="attentionshift_id"]').val(0);
    $('input[name="total_discount"]').val(0);
    $('input[name="total_tips"]').val(0);
    $('input[name="tips"]').val('');
    $('input[name="invoice_no"]').val(0);
    if (month < 10) {
        datef = day + "-0" + month + "-" + year;
    } else {
        datef = day + "-" + month + "-" + year;
    }
    var permission_turno = <?php echo json_encode(in_array('attentionshift', $all_permission)); ?>;
    var baseUrl = "<?php echo url('/'); ?>";
    $("ul#sale").siblings('a').attr('aria-expanded', 'true');
    $("ul#sale").addClass("show");
    $("ul#sale #sale-pos-menu").addClass("active");
    
    

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
    var tips = 0;
    var deposit = <?php echo json_encode($deposit); ?>;
    var product_row_number = <?php echo json_encode($lims_pos_setting_data->product_number); ?>;
    var tc = <?php echo json_encode($lims_pos_setting_data->t_c); ?>;
    var rowindex;
    var customer_group_rate;
    var row_product_price;
    var pos;
    var emp_temp = false;

    var keyboard_active = <?php echo json_encode($keybord_active); ?>;
    var role_id = <?php echo json_encode(\Auth::user()->role_id); ?>;
    var warehouse_id = <?php echo json_encode($biller_data->warehouse_id); ?>;
    var biller_id = <?php echo json_encode(\Auth::user()->biller_id); ?>;
    var coupon_list = <?php echo json_encode($lims_coupon_list); ?>;
    var currency = <?php echo json_encode($general_setting->currency); ?>;
    let limsCustomerList = <?php echo json_encode($lims_customer_list); ?>;

    // Bandera utlizadas para la venta de manera factura
    var bandera_confirmacion_nit = false;
    var bandera_puntoventa_contingencia = false;
    var bandera_servicio_sin = true;

    $.ajaxSetup({

        error: function(xhr, status, error) {
            swal("Error", "Estado: " + status + " Error: " + error, "error");

        }
    });
    $('.selectpicker').selectpicker({
        style: 'btn-link',
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
                buttonAction: 'active'
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
            change: function(e, keyboard) {
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
            change: function(e, keyboard) {
                keyboard.$el.val(keyboard.$preview.val())
                keyboard.$el.trigger('propertychange')
            }
        });

        $('#lims_productcodeSearch').keyboard().autocomplete().addAutocomplete({
            // add autocomplete window positioning
            // options here (using position utility)
            position: {
                of: '#lims_productcodeSearch',
                my: 'top+18px',
                at: 'center',
                collision: 'flip'
            }
        });
    }

    if (role_id > 2) {
        $('#biller_id').addClass('d-none');
        $('#div_biller').remove();
        $('#warehouse_id').addClass('noselect');
        $('#account_id').prop('disabled', true);
        $('select[name=warehouse_id]').val(warehouse_id);
        //$('select[name=biller_id]').val(biller_id);
        $('input[name=biller_id]').val(biller_id);
    } else {
        $('#div_account').remove();
        $('select[name=warehouse_id]').val($("input[name='warehouse_id_hidden']").val());
        $('select[name=biller_id]').val($("input[name='biller_id_hidden']").val());
    }

    $('select[name=customer_id]').val($("input[name='customer_id_hidden']").val());
    $('.selectpicker').selectpicker('refresh');

    var id_c = $("#customer_id").val();
    $.get('getcustomergroup/' + id_c, function(data) {
        customer_group_rate = (data / 100);
    });

    var id = $("#warehouse_id").val();
    $.get('getproduct_customer/' + id + '/' + id_c, function(data) {
        lims_product_array = [];
        product_code = data[0];
        product_name = data[1];
        product_qty = data[2];
        product_type = data[3];
        product_id = data[4];
        product_list = data[5];
        qty_list = data[6];
        $.each(product_code, function(index) {
            const price = data[7][index];
            lims_product_array.push(product_code[index] + ' (' + product_name[index] + ')' +
                ` - Precio: ${getPriceProduct(data, index)} - Stock: ${product_qty[index]}`);
        });
    });

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

    if (keyboard_active == 1) {
        $('#lims_productcodeSearch').bind('keyboardChange', function(e, keyboard, el) {
            var customer_id = $('#customer_id').val();
            var warehouse_id = $('select[name="warehouse_id"]').val();
            temp_data = $('#lims_productcodeSearch').val();
            if (!customer_id) {
                $('#lims_productcodeSearch').val(temp_data.substring(0, temp_data.length - 1));
                swal("Informacion", "Por favor, seleccione cliente!", "info");

            } else if (!warehouse_id) {
                $('#lims_productcodeSearch').val(temp_data.substring(0, temp_data.length - 1));
                swal("Informacion", "Por favor, seleccione almacen!", "info");
            }
        });
    } else {
        $('#lims_productcodeSearch').on('input', function() {
            var customer_id = $('#customer_id').val();
            var warehouse_id = $('#warehouse_id').val();
            temp_data = $('#lims_productcodeSearch').val();
            if (!customer_id) {
                $('#lims_productcodeSearch').val(temp_data.substring(0, temp_data.length - 1));
                swal("Informacion", "Por favor, seleccione cliente!", "info");
            } else if (!warehouse_id) {
                $('#lims_productcodeSearch').val(temp_data.substring(0, temp_data.length - 1));
                swal("Informacion", "Por favor, seleccione almacen!", "info");
            }

        });
    }

    $("#print-btn").on("click", function() {
        var divToPrint = document.getElementById('sale-details');
        var newWin = window.open('', 'Print-Window');
        newWin.document.open();
        newWin.document.write(
            '<link rel="stylesheet" href="/public/vendor/bootstrap/css/bootstrap.min.css" type="text/css"><style type="text/css">@media print {.modal-dialog { max-width: 1000px;} }</style><body onload="window.print()">' +
            divToPrint.innerHTML + '</body>');
        newWin.document.close();
        setTimeout(function() {
            newWin.close();
        }, 10);
    });

    $('body').on('click', function(e) {
        $('.filter-window').hide('slide', {
            direction: 'right'
        }, 'fast');
    });

    $('#category-filter').on('click', function(e) {
        e.stopPropagation();
        $('.filter-window').show('slide', {
            direction: 'right'
        }, 'fast');
        $('.category').show();
        $('.brand').hide();
    });

    $('.category-img').on('click', function() {
        var category_id = $(this).data('category');
        var brand_id = 0;

        $(".table-container").children().remove();
        $.get('getproduct/' + category_id + '/' + brand_id, function(data) {
            populateProduct(data);
        });
    });

    $('#brand-filter').on('click', function(e) {
        e.stopPropagation();
        $('.filter-window').show('slide', {
            direction: 'right'
        }, 'fast');
        $('.brand').show();
        $('.category').hide();
    });

    $('.brand-img').on('click', function() {
        var brand_id = $(this).data('brand');
        var category_id = 0;

        $(".table-container").children().remove();
        $.get('getproduct/' + category_id + '/' + brand_id, function(data) {
            populateProduct(data);
        });
    });

    $('#featured-filter').on('click', function() {
        $(".table-container").children().remove();
        $.get('getfeatured', function(data) {
            populateProduct(data);
        });
    });

    function populateProduct(data) {
        var tableData =
            '<table id="product-table" class="table no-shadow product-list"> <thead class="d-none"> <tr> <th></th> <th></th> <th></th> <th></th> <th></th> </tr></thead> <tbody><tr>';

        if (Object.keys(data).length != 0) {
            $.each(data['name'], function(index) {
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

    $('select[name="customer_id"]').on('change', function() {
        var id = $(this).val();
        var id_w = $("#warehouse_id").val();
        $.get('getcustomergroup/' + id, function(data) {
            customer_group_rate = (data / 100);
        });

        $.get('getproduct_customer/' + id_w + '/' + id, function(data) {
            console.log(data);
            lims_product_array = [];
            product_code = data[0];
            product_name = data[1];
            product_qty = data[2];
            product_type = data[3];
            $.each(product_code, function(index) {
                lims_product_array.push(product_code[index] + ' (' + product_name[index] + ')' +
                    ` - Precio: ${getPriceProduct(data, index)} - Stock: ${product_qty[index]}`
                );
            });
        });
    });

    $('select[name="warehouse_id"]').on('change', function() {
        var id = $(this).val();
        var id_c = $("#customer_id").val();
        $.get('getproduct_customer/' + id + '/' + id_c, function(data) {
            lims_product_array = [];
            product_code = data[0];
            product_name = data[1];
            product_qty = data[2];
            product_type = data[3];
            $.each(product_code, function(index) {
                lims_product_array.push(product_code[index] + ' (' + product_name[index] + ')' +
                    ` - Precio: ${getPriceProduct(data, index)} - Stock: ${product_qty[index]}`
                );
            });
        });
    });



    var lims_productcodeSearch = $('#lims_productcodeSearch');

    lims_productcodeSearch.autocomplete({
        source: function(request, response) {
            var matcher = new RegExp(".?" + $.ui.autocomplete.escapeRegex(request.term), "i");
            response($.grep(lims_product_array, function(item) {
                return matcher.test(item);
            }));
        },
        response: function(event, ui) {
            var customer_id = $('#customer_id').val();
            var filter = [];
            if (ui.content.length == 1) {
                var data = ui.content[0].value;
                $(this).autocomplete("close");
                filter.push(data);
                filter.push(customer_id);
                productSearch(filter);
            };
        },
        select: function(event, ui) {
            var customer_id = $('#customer_id').val();
            var filter = [];
            var data = ui.item.value;
            filter.push(data);
            filter.push(customer_id);
            productSearch(filter);
        },
    });

    $('#myTable').keyboard({
        accepted: function(event, keyboard, el) {
            checkQuantity(el.value, true);
        }
    });

    $("#myTable").on('click', '.plus', function() {
        rowindex = $(this).closest('tr').index();
        var qty = parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val()) + 1;
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(qty.toFixed(1));
        checkQuantity(String(qty), true);
    });

    $("#myTable").on('click', '.minus', function() {
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
    $("#myTable").on('blur', '.qty', function() {
        rowindex = $(this).closest('tr').index();
        if ($(this).val() < 0.1 && $(this).val() != '') {
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(1);
            swal("Advertencia", "La cantidad no puede ser menor que 0.1!", "warning");

        }
        checkQuantity($(this).val(), true);
    });

    $("#myTable").on('click', '.qty', function() {
        rowindex = $(this).closest('tr').index();
    });

    $(document).on('click', '.sound-btn', function() {
        var audio = $("#mysoundclip1")[0];
        audio.play();
    });

    $(document).on('click', '.product-img', function() {
        var customer_id = $('#customer_id').val();
        var warehouse_id = $('select[name="warehouse_id"]').val();
        var filter = [];
        if (!customer_id)
            swal("Informacion", "Por favor, seleccione cliente!", "info");
        else if (!warehouse_id)
            swal("Informacion", "Por favor, seleccione almacen!", "info");
        else {
            var data = $(this).data('product');
            data = data.split(" ");
            pos = product_code.indexOf(data[0]);
            if (pos < 0)
                swal("Error de Producto", "Producto no disponible en el almacen seleccionado!", "error");
            else {
                filter.push(data[0]);
                filter.push(customer_id);
                productSearch(filter);
            }
        }
    });
    //Delete product
    $("table.order-list tbody").on("click", ".ibtnDel", function(event) {
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
    $("table.order-list").on("click", ".edit-product", function() {
        rowindex = $(this).closest('tr').index();
        edit();
    });

    //Update product
    $('button[name="update_btn"]').on("click", function() {
        var edit_discount = $('input[name="edit_discount"]').val();
        var edit_qty = $('input[name="edit_qty"]').val();
        var edit_unit_price = $('input[name="edit_unit_price"]').val();

        if (parseFloat(edit_discount) > parseFloat(edit_unit_price)) {
            swal("Error de Descuento", "Ingreso de descuento invalido!", "error");
            return;
        }

        if (edit_qty < 1) {
            $('input[name="edit_qty"]').val(1);
            edit_qty = 1;
            swal("Advertencia", "La cantidad no puede ser menor que 0.1!", "warning");
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

    $('button[name="order_discount_btn"]').on("click", function() {
        calculateGrandTotal();
    });

    $('button[name="shipping_cost_btn"]').on("click", function() {
        calculateGrandTotal();
    });

    $('button[name="order_tax_btn"]').on("click", function() {
        calculateGrandTotal();
    });

    $(".coupon-check").on("click", function() {
        couponDiscount();
    });

    $('button[name="tip_btn"]').on("click", function() {
        calculateGrandTotal();
    });

    $(".payment-btn").on("click", function() {
        var audio = $("#mysoundclip2")[0];
        audio.play();
        var totalbs = $("#grand-total").text();
        var totalus = totalbs / tc;
        $('input[name="paid_amount"]').val($("#grand-total").text());
        $('input[name="paying_amount_us"]').val(0);
        $('input[name="paying_amount"]').val(0);
        $('.qc').data('initial', 1);
    });

    $("#draft-btn").on("click", function() {
        blockAmounts()
        var audio = $("#mysoundclip2")[0];
        audio.play();
        $('input[name="sale_status"]').val(3);
        $('input[name="paying_amount"]').prop('required', false);
        $('input[name="paid_amount"]').prop('required', false);
        var rownumber = $('table.order-list tbody tr:last').index();
        if (emp_temp == false) {
            $("#submit-btn").removeClass("disabled noselect");
        } else {
            $("#submit-btn").addClass("disabled noselect");
            swal("Advertencia de Servicio", "Complete el empleado de servicio del item", "warning");
        }
        if (rownumber < 0) {
            emp_temp = false;
            swal("Información de Items", "Por favor, inserte el producto para ordenar la tabla!",
                "warning");
        } else
            $('.payment-form').submit();


    });

    $("#presale-btn").on("click", function() {
        blockAmounts()
        var audio = $("#mysoundclip2")[0];
        audio.play();
        if (permission_turno && ($('input[name="attentionshift_id"]').val() == 0 && $(
                'input[name="presale_id"]').val() == 0)) {
            choose_turno();
        } else {
            var method = "POST";
            var action = "store";

            if ($('input[name="presale_id"]').val() != 0) {
                method = "PUT";
                action = "/update";
            } else {
                method = "POST";
                action = "";
            }
            $('input[name="status"]').val(1);
            $('input[name="paying_amount"]').prop('required', false);
            $('input[name="paid_amount"]').prop('required', false);
            var rownumber = $('table.order-list tbody tr:last').index();
            if (emp_temp == false) {
                $("#submit-btn").removeClass("disabled noselect");
            } else {
                $("#submit-btn").addClass("disabled noselect");
                swal("Advertencia de Servicio", "Complete el empleado de servicio del item", "warning");

            }
            if (rownumber < 0) {
                emp_temp = false;
                swal("Información de Items", "Por favor, inserte el producto para ordenar la tabla!",
                    "warning");
            } else {
                var form_data = $("#formPayment").getFormObject();
                console.log(form_data);
                $.ajax({
                    type: method,
                    url: baseUrl + '/presales' + action,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: form_data,
                    success: function(data) {
                        //console.log(data);
                        if (data.status) {
                            if (data.print) {
                                swal({
                                        title: "Mensaje!",
                                        text: "Mensaje : " + data.message,
                                        icon: data.message_code,
                                        buttons: {
                                            cancel: "Cerrar!",
                                            printer: {
                                                text: "Imprimir",
                                                value: true,
                                            },
                                        },
                                    })
                                    .then((printer) => {
                                        if (printer) {
                                            var win = window.open('presales/gen_invoice/' + data
                                                .id,
                                                '_blank');
                                            win.focus();
                                            location.reload(true);
                                        } else {
                                            location.reload(true);
                                        }
                                    });
                            } else {
                                swal("Mensaje", data.message, data.message_code);
                                location.reload(true);
                            }
                        } else {
                            swal("Mensaje", "Error al guardar/actualizar intente de nuevo",
                                "error");
                        }

                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        swal("Error", "Estado: " + textStatus + " Error: " + errorThrown,
                            "error");
                    }
                });
            }
            //$('.payment-form').submit();
        }

    });

    $("#cobrar-btn").on("click", function() {
        blockAmounts();
        const customerId = $('#customer_id').val();
        const customer = limsCustomerList.find(item => item.id == customerId);
        var audio = $("#mysoundclip2")[0];
        audio.play();
        $('input[name="sale_status"]').val(4);
        $('input[name="paying_amount"]').prop('required', false);
        $('input[name="paid_amount"]').prop('required', false);
        var rownumber = $('table.order-list tbody tr:last').index();

        if (emp_temp == false) {
            $("#submit-btn").removeClass("disabled noselect");
        } else {
            $("#submit-btn").addClass("disabled noselect");
            swal("Advertencia de Servicio", "Complete el empleado de servicio del item", "warning");
        }
        if (rownumber < 0) {
            emp_temp = false;
            swal("Información de Items", "Por favor, inserte el producto para ordenar la tabla!",
                "warning");
        } else {
            if (customer.is_credit == false)
                $('.payment-form').submit();
            else {
                $.get('receivable/due/' + customer.id, function(res) {
                    var saldo = customer.credit - res;
                    $("#totalwithcredit").text(saldo.toFixed(2));
                    $('input[name="customer"]').val(customer.name);
                    $('input[name="credits"]').val(customer.credit);
                    $("#totalpaymod").text($("#grand-total").text());
                    var totalgrand = parseFloat($("#grand-total").text());
                    var totalres = saldo - totalgrand;
                    $("#totalgral").text(totalres.toFixed(2));
                    if (totalres < 0) {
                        $("#paycredit-btn").addClass("disabled noselect");
                        swal("Advertencia de Credito", "El Cliente : " + customer.name +
                            " No puede recibir mas ventas por pagar, Creditos Insuficientes",
                            "warning");
                    } else {
                        $("#paycredit-btn").removeClass("disabled noselect");
                    }
                    $('#detailCredit').modal();
                });
            }
        }
    });

    $("#abonar-btn").on("click", function() {
        blockAmounts();
        const customerId = $('#customer_id').val();
        const customer = limsCustomerList.find(item => item.id == customerId);
        console.log(customer);
        var audio = $("#mysoundclip2")[0];
        audio.play();
        $.get('receivable/due/' + customer.id, function(res) {
            var due = parseFloat(res);
            $("#totaldue").text(due.toFixed(2));
            $('input[name="id_customer"]').val(customer.id);
            $('input[name="customer"]').val(customer.name);
            $('input[name="credits"]').val(customer.credit);
            $('input[name="amount_due"]').val(0);
            if (due <= 0)
                $("#payduecredit-btn").addClass("disabled noselect");
            else
                $("#payduecredit-btn").removeClass("disabled noselect");

            $('#formpaydue').modal();
        });
    });

    $("#gift-card-btn").on("click", function() {
        blockAmounts()
        $('select[name="paid_by_id_select"]').val(3);
        $('.selectpicker').selectpicker('refresh');
        $('div.qc').hide();
        giftCard();
        if (emp_temp == false) {
            $("#submit-btn").removeClass("disabled noselect");
        } else {
            $("#submit-btn").addClass("disabled noselect");
            swal("Advertencia de Servicio", "Complete el empleado de servicio del item", "warning");
        }
    });

    function customer_paydue() {
        var id = $('input[name="id_customer"]').val();
        var amount = $('input[name="amount_due"]').val();
        if (amount > 0) {
            $.ajax({
                type: 'POST',
                url: 'receivable/paydue',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    customer_id: id,
                    amount_pay: amount,
                    payment_method: '1'
                },
                success: function(data) {
                    console.log(data);
                    result = JSON.parse(data);
                    swal({
                            title: "Abono Registrado con éxito!",
                            text: "Mensaje : " + result.message + " - Total Procesados : " + result
                                .totalprocess,
                            icon: "success",
                            buttons: {
                                cancel: "Cerrar!",
                                printer: {
                                    text: "Imprimir",
                                    value: true,
                                },
                            },
                        })
                        .then((printer) => {
                            if (printer) {
                                var win = window.open('receivable/report/' + result.report_id,
                                    '_blank');
                                win.focus();
                                $('#formpaydue').modal('hide')
                                $('body').removeClass('modal-open');
                                $('.modal-backdrop').remove();
                            } else {
                                $('#formpaydue').modal('hide')
                                $('body').removeClass('modal-open');
                                $('.modal-backdrop').remove();
                            }
                        });
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    swal("Error", "Estado: " + textStatus + " Error: " + errorThrown, "error");
                }
            });
        } else {
            swal("Error Cliente", "El monto de pago debe ser mayor a 0", "error");
        }
    }

    $("#credit-card-btn").on("click", function() {
        blockAmounts()
        $('select[name="paid_by_id_select"]').val(4);
        $('.selectpicker').selectpicker('refresh');
        $('div.qc').hide();
        creditCard();
        if (emp_temp == false) {
            $("#submit-btn").removeClass("disabled noselect");
        } else {
            $("#submit-btn").addClass("disabled noselect");
            swal("Advertencia de Servicio", "Complete el empleado de servicio del item", "warning");
        }
    });

    $("#cheque-btn").on("click", function() {
        blockAmounts()
        $('select[name="paid_by_id_select"]').val(5);
        $('.selectpicker').selectpicker('refresh');
        $('div.qc').hide();
        cheque();
        if (emp_temp == false) {
            $("#submit-btn").removeClass("disabled noselect");
        } else {
            $("#submit-btn").addClass("disabled noselect");
            swal("Advertencia de Servicio", "Complete el empleado de servicio del item", "warning");
        }
    });

    $("#cash-btn").on("click", function() {
        unblockAmounts()
        $('select[name="paid_by_id_select"]').val(1);
        $('.selectpicker').selectpicker('refresh');
        $('div.qc').show();
        if (emp_temp == false) {
            $("#submit-btn").removeClass("disabled noselect");
        } else {
            $("#submit-btn").addClass("disabled noselect");
            swal("Advertencia de Servicio", "Complete el empleado de servicio del item", "warning");
        }
        var rownumber = $('table.order-list tbody tr:last').index();
        if (rownumber < 0) {
            emp_temp = false;
            $("#submit-btn").addClass("disabled noselect");
            swal("Información de Items", "Por favor, inserte el producto para ordenar la tabla!", "warning");
        }
        if ($("input[name='bandera_factura_hidden']").val() == true) {
            $("#submit-btn").addClass("disabled noselect");
        }
        hide();
    });

    $("#paypal-btn").on("click", function() {
        blockAmounts()
        $('select[name="paid_by_id_select"]').val(8);
        $('.selectpicker').selectpicker('refresh');
        $('div.qc').hide();
        hide();
        if (emp_temp == false) {
            $("#submit-btn").removeClass("disabled noselect");
        } else {
            $("#submit-btn").addClass("disabled noselect");
            swal("Advertencia de Servicio", "Complete el empleado de servicio del item", "warning");
        }
    });

    $("#deposit-btn").on("click", function() {
        blockAmounts()
        $('select[name="paid_by_id_select"]').val(7);
        $('.selectpicker').selectpicker('refresh');
        $('div.qc').hide();
        hide();
        deposits();
        if (emp_temp == false) {
            $("#submit-btn").removeClass("disabled noselect");
        } else {
            $("#submit-btn").addClass("disabled noselect");
            swal("Advertencia de Servicio", "Complete el empleado de servicio del item", "warning");
        }
    });

    $("#qrsimple-btn").on("click", function() {
        blockAmounts()
        $('select[name="paid_by_id_select"]').val(6);
        $('.selectpicker').selectpicker('refresh');
        $('div.qc').hide();
        hide();
        //$("#submit-btn").addClass("disabled noselect");
        /**  disabled for payment incomplete  */
        //qrsimple();
        if (emp_temp == false) {
            $("#submit-btn").removeClass("disabled noselect");
        } else {
            $("#submit-btn").addClass("disabled noselect");
            swal("Advertencia de Servicio", "Complete el empleado de servicio del item", "warning");
        }
    });

    // botón confirmarVenta
    $("#submit-btn").on("click", function(e) {
        $("#spinner-div").show();
        e.preventDefault();
        if ($("input[name='bandera_factura_hidden']").val() == true) {
            determinarVigenciaCUFDxBiller();
            setValoresTipoDocumentoCasoEspecial();
        }
        if (bandera_puntoventa_contingencia != true && $("input[name='bandera_factura_hidden']").val() == true) {
            getEstadoSIN();
        }
        if ($("input[name='bandera_factura_hidden']").val() == true && bandera_puntoventa_contingencia == false) {

            // Caso ServiciosSIN
            if (bandera_servicio_sin == false) {
                ocultarSpinner();
                alertaPuntoVentaContingencia(); 
                return;
            };

            // Caso vigenciaCUFD 
            var bandera_vigencia_cufd = $("input[name='bandera_vigencia_cufd_hidden']").val();
            console.log("El estado de la vigencia del CUFD es => "+bandera_vigencia_cufd  +' [0:inválido, 1:válido]')
            if (bandera_vigencia_cufd != 1) {
                ocultarSpinner();
                // la vigencia del cufd está en el límite
                console.log('botón confirmarVenta, El CUFD se encuentra en el límite de la vigencia');
                
                swal({
                    icon: 'warning',
                    title: 'Vigencia del CUFD al límite de terminar',
                    showConfirmButton: false,
                    html: 'Se recomienda <b>renovar los cufd. </b> Pulsa el siguiente botón para ' +
                        '<button type="button" class="vigencia-renovar-cufd btn btn-warning">Renovar vigencia CUFD</button> ' +
                        'para el día siguiente. ',
                });
                
                return;
            };
            // fin vigenciaCUFD

            // Caso NIT
            var caso_especial = $("input[name='sales_caso_especial_hidden']").val();
            var tipo_documento = $("input[name='sales_tipo_documento_hidden']").val();
            if ( caso_especial == 1 && tipo_documento == 5) {
                
                sales_consultarNIT();
                // si Codigo 0 => nit activo/inactivo; Código 1 => nit inexistente
                console.log('botón confirmarVenta, operación NIT,' + bandera_nit + ' [0:activo/inactivo; 1:inexistente]');
                var bandera_nit = $("input[name='bandera_codigo_excepcion_hidden']").val();
                if (bandera_nit != 0 && bandera_confirmacion_nit == false) {
                    
                    var nit_cliente = $('input[name=sales_valor_documento]').val();
                    console.log('La venta tiene NIT inexistente, mostrar alerta');
                    swal({
                        icon: 'warning',
                        title: 'NIT Inexistente',
                        text: 'El NIT ' + nit_cliente + ' no está registrado en Impuestos Nacionales',
                        showCancelButton: true,
                        confirmButtonText: 'Continuar la venta',
                        cancelButtonText: 'Cancelar',
                        }).then((result) => {    
                            if (result.isConfirmed) {
                                // el cliente confirma que desea continuar la venta con su NIT todo inválido
                                console.log('NIT: el cliente confirma que desea continuar la venta con su NIT todo inválido');
                                $("input[name='bandera_codigo_excepcion_hidden']").val(1);
                                bandera_confirmacion_nit = true;
                                console.log('La bandera de confirmación es => '+ bandera_confirmacion_nit);
                                
                            } else if (result.dismiss === Swal.DismissReason.cancel) {
                                // La venta de detiene, con opción de volver a digitar el NIT;
                                console.log('NIT: el cliente cancela la venta');
                                bandera_confirmacion_nit = false;
                                console.log('La bandera de confirmación es => '+ bandera_confirmacion_nit);
                            }
                        });
                    console.log('La bandera de confirmación es => '+ bandera_confirmacion_nit);
                    if ( bandera_confirmacion_nit != true) {
                        ocultarSpinner();
                        return;
                    };
                }
            }
            // fin NIT

            console.log('La bandera entra por true');
            $('form').unbind('submit').submit();
            return;
        } 
        $("#submit-btn").addClass("disabled noselect");
        $('form').unbind('submit').submit();
    });

    $('#add-payment').on('hidden.bs.modal', function(e) {
        $(this).modal("hide");
        if (checkStatusIntervalId) clearInterval(checkStatusIntervalId);
        if (timerIntervalId) clearInterval(timerIntervalId);
    });

    $('select[name="paid_by_id_select"]').on("change", function() {
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


    $('#add-payment select[name="gift_card_id_select"]').on("change", function() {
        var balance = gift_card_amount[$(this).val()] - gift_card_expense[$(this).val()];
        var expense = 0;
        $('#add-payment input[name="gift_card_id"]').val($(this).val());
        if ($('input[name="paid_amount"]').val() > balance) {
            alert('La cantidad excede el saldo de la tarjeta! Saldo de la tarjeta de regalo: ' + balance);
        }

        // resolvemos visualmente el monto de la tarjeta de regalo
        var saldo_total = balance - expense;
        $('.badge-gc-credito').text('Crédito: ' + balance);
        $('.badge-gc-debito').text('Débito: ' + expense);
        $('.badge-gc-saldo-total').text('Saldo total: ' + saldo_total);
        $('.visual_monto_gift_card').show();
    });

    $('#add-payment input[name="paying_amount"]').on("input", function() {
        change($(this).val(), $('input[name="paid_amount"]').val(), "BOB");
    });

    $('#add-payment input[name="paying_amount_us"]').on("input", function() {
        change($(this).val(), $('input[name="paid_amount"]').val(), "USD");
    });

    $('input[name="paid_amount"]').on("input", function() {
        if ($(this).val() > parseFloat($('input[name="paying_amount"]').val())) {
            swal("Advertencia de Pago",
                "La cantidad de pago no puede ser más grande que la cantidad recibida",
                "warning");
            $(this).val('');
        } else if ($(this).val() > parseFloat($('#grand-total').text())) {
            swal("Advertencia de Pago", "La cantidad de pago no puede ser más grande que el gran total",
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

    $('.transaction-btn-plus').on("click", function() {
        $(this).addClass('d-none');
        $('.transaction-btn-close').removeClass('d-none');
    });

    $('.transaction-btn-close').on("click", function() {
        $(this).addClass('d-none');
        $('.transaction-btn-plus').removeClass('d-none');
    });

    $('.coupon-btn-plus').on("click", function() {
        $(this).addClass('d-none');
        $('.coupon-btn-close').removeClass('d-none');
    });

    $('.coupon-btn-close').on("click", function() {
        $(this).addClass('d-none');
        $('.coupon-btn-plus').removeClass('d-none');
    });

    $(document).on('click', '.qc-btn', function(e) {
        if ($(this).data('amount')) {
            if ($('.qc').data('initial')) {
                $('input[name="paying_amount"]').val($(this).data('amount').toFixed(2));
                $('.qc').data('initial', 0);
            } else {
                $('input[name="paying_amount"]').val((parseFloat($('input[name="paying_amount"]').val()) + $(
                    this).data('amount')).toFixed(2));
            }
        } else {
            $('input[name="paying_amount"]').val('0.00');
            $('input[name="paying_amount_us"]').val('0.00');
        }
        change($('input[name="paying_amount"]').val(), $('input[name="paid_amount"]').val(), "BOB");
    });

    $(document).on('click', '.qc-btn-us', function(e) {
        if ($(this).data('amount')) {
            if ($('.qc').data('initial')) {
                $('input[name="paying_amount_us"]').val($(this).data('amount').toFixed(2));
                $('.qc').data('initial', 0);
            } else {
                $('input[name="paying_amount_us"]').val((parseFloat($('input[name="paying_amount_us"]').val()) +
                    $(this).data('amount')).toFixed(2));
            }
        } else {
            $('input[name="paying_amount_us"]').val('0.00');
            $('input[name="paying_amount"]').val('0.00');
        }
        change($('input[name="paying_amount_us"]').val(), $('input[name="paid_amount"]').val(), "USD");
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

    function productSearch(data, isCourtesy = false, employee = false, presale = false) {
        var alm = $('select[name="warehouse_id"]').val();
        qty_list2 = null;
        $.ajax({
            type: 'GET',
            url: 'lims_product_search',
            data: {
                data: data
            },
            success: function(data) {
                var flag = 1;
                $(".product-code").each(function(i) {
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
                        $.get('getstockprofinish/' + data[1] + '/' + alm, function(res) {
                            //console.log(res);
                            qty_list2 = res[1];
                            if (res[0] === true) {
                                swal("Error de Stock!",
                                    "No hay stock disponible! en uno o mas insumos", "error");
                            } else {
                                addNewProduct(data, employee, presale);
                            }
                        }).catch((error) => {
                            swal("Error de Insumos!",
                                "No hay stock disponible! en uno o mas insumos", "error");
                        });
                    } else {
                        if (data[2] <= 0) {
                            swal({
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
                                        $.get('getstockprofinish/' + data[1] + '/' + alm,
                                            function(res) {
                                                //console.log(res);
                                                qty_list2 = res[1];
                                                if (res[0] === true) {
                                                    swal("Error de Stock!",
                                                        "No hay stock disponible! en uno o mas insumos",
                                                        "error");
                                                } else {
                                                    addNewProduct(data, employee, presale);
                                                }
                                            }).catch((error) => {
                                            swal("Error de Insumos!",
                                                "No hay stock disponible! en uno o mas insumos",
                                                "error");
                                        });
                                    } else {
                                        swal("Error al Ingresar",
                                            "Monto ingresado invalido, intente nuevamente!", "error"
                                        );
                                    }
                                });
                        } else {
                            $.get('getstockprofinish/' + data[1] + '/' + alm, function(res) {
                                //console.log(res);
                                qty_list2 = res[1];
                                if (res[0] === true) {
                                    swal("Error de Stock!",
                                        "No hay stock disponible! en uno o mas insumos", "error"
                                    );

                                } else {
                                    addNewProduct(data, employee, presale);
                                }
                            }).catch((error) => {
                                swal("Error de Insumos!",
                                    "No hay stock disponible! en uno o mas insumos", "error");
                            });
                        }
                    }
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                swal("Error", "Estado: " + textStatus + " Error: " + errorThrown, "error");
            }
        });
    }

    function addNewProduct(data, employee = false, presale = false) {
        console.log(data);
        console.log('employee: ' + employee);
        var newRow = $("<tr>");
        var cols = '';
        var pre = 0;
        if (employee)
            var emp = employee;
        else
            var emp = 0;

        if (presale != false) {
            //data[2] = presale.net_unit_price;
            pre = presale.presale_id;
        }
        temp_unit_name = (data[6]).split(',');
        cols += '<td class="col-sm-4 product-title" style="text-align: start;"><button type="button" class="edit-product btn btn-link" data-toggle="modal" data-target="#editModal" style="font-size: smaller; white-space: normal;"><strong>' +
                data[0] + '</strong></button> [' + data[1] + ']' +
                '<div class="input-group div_emp_' + data[1] + pre + '"><select id="employee_id_' + data[1] + pre +
                '" name="employee_id" class="selectpicker form-control courtesy-select" data-live-search="true" data-live-search-style="contains"><option value="0">Seleccione Personal...</option></select></div> <input type="hidden" id="service_' +
                data[1] + pre + '" class="service-pro" name="service_kind" value="false"/> </td>';
        cols += '<td class="col-sm-2 product-price" style="text-align: end;"></td>';
        cols += '<td class="col-sm-3"><div class="input-group"><span class="input-group-btn"><button type="button" class="btn btn-default minus"><span class="dripicons-minus"></span></button></span><input type="text" name="qty[]" class="form-control qty numkey input-number" value="1" step="0.01" required><span class="input-group-btn">' +
                    '<button type="button" class="btn btn-default plus"><span class="dripicons-plus"></span></button></span></div><div class="input-group"><select id="cortesia_id_' +  data[1] +
                '" name="cortesia_id" class="selectpicker form-control courtesy-select" data-live-search="true" data-live-search-style="contains" onchange="validatemp()"><option value="0">Seleccione Cortesia...</option></select></div></td>';
        cols += '<td class="col-sm-2 sub-total" style="text-align: end;"></td>';
        cols += '<td class="col-sm-1"><button type="button" class="ibtnDel btn btn-danger btn-sm"><i class="dripicons-cross"></i></button></td>';

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
        cols += '<input type="hidden" class="presale" name="presale[]" value="' + pre + '"/>';

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
                $(`#cortesia_id_${data[1]}`).on("change", function() {
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
            $(`#service_${data[1]+pre}`).val('true');
            if (data[12].length > 0) {
                addOptions(`employee_id_${data[1]+pre}`, data[12], 2);
                //add a tabla cuando selecciona
                emp_temp = true;
                $(`#employee_id_${data[1]+pre}`).on("change", function() {
                    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.employee-id').val($(this).val());
                    emp_temp = false;
                });
                if (emp) {
                    $(`#employee_id_${data[1]+pre}`).val(emp);
                    emp_temp = false;
                }
                $('.selectpicker').selectpicker('refresh');
            } else {
                $(`#employee_id_${data[1]+pre}`).addClass('d-none');
                emp_temp = false;
            }
        } else {
            $(`#employee_id_${data[1]+pre}`).addClass('d-none');
            emp_temp = false;
        }
        rowindex = newRow.index();
        if (presale != false) {
            checkQuantity(presale.qty, true);
        } else
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

        if (op == 3) {
            for (value in array) {
                var option = document.createElement("option");
                option.text = array[value].reference_nro + '[' + array[value].customer_name + ']';
                option.value = array[value].id;
                select.add(option);
            }
        }
    }

    function validatemp() {
        if (emp_temp == true) {
            swal("Advertencia de Servicio", "Seleccione el empleado de servicio antes de ingresar otro item",
                "warning");
        }
    }

    function edit() {
        var row_product_name_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find(
            '.product-title').text();
        var title = row_product_name_code.split(']');
        $('#modal_header').text(title[0] + ']');

        var qty = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val();
        $('input[name="edit_qty"]').val(qty);

        $('input[name="edit_discount"]').val(parseFloat(product_discount[rowindex]).toFixed(2));

        var tax_name_all = <?php echo json_encode($tax_name_all); ?>;
        pos = tax_name_all.indexOf(tax_name[rowindex]);
        $('select[name="edit_tax_rate"]').val(pos);

        var row_product_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-code').val();
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
            $.each(temp_unit_name, function(key, value) {
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

    function couponDiscount() {
        var rownumber = $('table.order-list tbody tr:last').index();
        if (rownumber < 0) {
            emp_temp = false;
            swal("Información de Items", "Por favor, inserte el producto para ordenar la tabla!", "info");

        } else if ($("#coupon-code").val() != '') {
            valid = 0;
            $.each(coupon_list, function(key, value) {
                if ($("#coupon-code").val() == value['code']) {
                    valid = 1;
                    todyDate = <?php echo json_encode(date('Y-m-d')); ?>;
                    if (parseFloat(value['quantity']) <= parseFloat(value['used']))
                        swal("Error de Cupón!", "Este cupón ya no está disponible", "error");

                    else if (todyDate > value['expired_date'])
                        swal("Error de Cupón!", "Este cupon esta expirado", "error");

                    else if (value['type'] == 'fixed') {
                        if (parseFloat($('input[name="grand_total"]').val()) >= value['minimum_amount']) {
                            $('input[name="grand_total"]').val($('input[name="grand_total"]').val() - value[
                                'amount']);
                            $('#grand-total').text(parseFloat($('input[name="grand_total"]').val()).toFixed(2));
                            if (!$('input[name="coupon_active"]').val())
                                swal("Descuento Aplicado!", "¡Felicidades! Tu tienes " + value['amount'] +
                                    ' ' +
                                    currency + ' de descuento', "success");

                            $(".coupon-check").prop("disabled", true);
                            $("#coupon-code").prop("disabled", true);
                            $('input[name="coupon_active"]').val(1);
                            $("#coupon-modal").modal('hide');
                            $('input[name="coupon_id"]').val(value['id']);
                            $('input[name="coupon_discount"]').val(value['amount']);
                            $('#coupon-text').text(parseFloat(value['amount']).toFixed(2));
                        } else
                            swal("Error de Descuento!",
                                "¡El gran total no es suficiente para el descuento! Requerido " + value[
                                    'minimum_amount'] + ' ' + currency, "error");

                    } else {
                        var grand_total = $('input[name="grand_total"]').val();
                        var coupon_discount = grand_total * (value['amount'] / 100);
                        grand_total = grand_total - coupon_discount;
                        $('input[name="grand_total"]').val(grand_total);
                        $('#grand-total').text(parseFloat(grand_total).toFixed(2));
                        if (!$('input[name="coupon_active"]').val())
                            swal("Descuento Aplicado!", "¡Felicidades! Tu tienes " + value['amount'] +
                                '% de descuento', "success");
                        $(".coupon-check").prop("disabled", true);
                        $("#coupon-code").prop("disabled", true);
                        $('input[name="coupon_active"]').val(1);
                        $("#coupon-modal").modal('hide');
                        $('input[name="coupon_id"]').val(value['id']);
                        $('input[name="coupon_discount"]').val(coupon_discount);
                        $('#coupon-text').text(parseFloat(coupon_discount).toFixed(2));
                    }
                }
            });
            if (!valid)
                swal("Error de Cupón!", "Codigo de cupón invalido", "error");

        }
    }

    function checkQuantity(sale_qty, flag) {
        var row_product_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-code')
            .val();
        pos = product_code.indexOf(row_product_code);
        var alm = $('select[name="warehouse_id"]').val();
        if (pos == -1) { //no existe producto
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').remove();
            swal("Error de Stock!", "No hay stock disponible!", "error");
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
                swal("Advertencia de Stock!", "Cantidad excede el stock disponible!", "warning");
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
            console.log("child_id : " + child_id + " - child_qty : " + child_qty);
            $.get('getstockprofinish/' + row_product_code + '/' + alm, function(res) {
                $(child_id).each(function(index) {
                    console.log("index: " + index);
                    console.log(sale_qty * child_qty[index] + " - " + res[1][index].qty + " - " + res[1][index].type);
                    if (res[1][index].type != 'digital' && parseFloat(sale_qty * child_qty[index]) > res[1][index].qty) {
                        swal("Advertencia de Stock!",
                            "Cantidad excede el stock disponible! de uno o mas productos, alerta en producto : " +
                            res[1][index].name, "warning");
                        if (flag) {
                            sale_qty = 1;
                            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find(
                                '.qty').val(
                                sale_qty);
                        } else {
                            edit();
                            flag = true;
                            return false;
                        }
                    }
                });
            }).catch((error) => {
                swal("Error de Productos!", "No hay stock disponible! en uno o mas productos", "error");
            });
        } else if (product_type[pos] == 'producto_terminado') {
            child_id = product_list[pos].split(',');
            child_qty = qty_list[pos].split(',');
            var sold = false;
            console.log("child_id : " + child_id + " - child_qty : " + child_qty);
            $.get('getstockprofinish/' + row_product_code + '/' + alm, function(res) {
                $(child_id).each(function(index) {
                    //res[1].forEach(function(stock) {
                    console.log(sale_qty * child_qty[index] + " - " + res[1][index].qty);
                    if (parseFloat(sale_qty * child_qty[index]) > res[1][index].qty) {
                        sold = true;
                        sale_qty = 1;
                        swal("Advertencia de Stock!",
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
                swal("Error de Insumos!", "No hay stock disponible! en uno o mas insumos", "error");
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
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(2)').text(sub_total_unit
                .toFixed(2));
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
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(2)').text(sub_total_unit
                .toFixed(2));
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
        $("table.order-list tbody .qty").each(function(index) {
            if ($(this).val() == '') {
                total_qty += 0;
            } else {
                total_qty += parseFloat($(this).val());
            }
        });
        $('input[name="total_qty"]').val(total_qty);

        //Sum of discount
        var total_discount = 0;
        $("table.order-list tbody .discount-value").each(function() {
            total_discount += parseFloat($(this).val());
        });

        $('input[name="total_discount"]').val(total_discount.toFixed(2));

        //Sum of tax
        var total_tax = 0;
        $(".tax-value").each(function() {
            total_tax += parseFloat($(this).val());
        });

        $('input[name="total_tax"]').val(total_tax.toFixed(2));

        //Sum of subtotal
        var total = 0;
        $(".sub-total").each(function() {
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

        
        if(tip_cost == 0){
            //tips = Math.abs(tips) * -1;
            tips = tip_cost;
        }else{
            tips = tips + tip_cost;
        }

        item = ++item + '(' + total_qty + ')';
        order_tax = (subtotal - order_discount) * (order_tax / 100);
        var grand_total = (subtotal + order_tax + shipping_cost + tips) - order_discount;
        $('input[name="grand_total"]').val(grand_total.toFixed(2));

        couponDiscount();
        var coupon_discount = parseFloat($('input[name="coupon_discount"]').val());
        if (!coupon_discount)
            coupon_discount = 0.00;
        grand_total -= coupon_discount;

        $('#item').text(item);
        $('input[name="item"]').val($('table.order-list tbody tr:last').index() + 1);
        $('#subtotal').text(subtotal.toFixed(2));
        $('#tax').text(order_tax.toFixed(2));
        $('input[name="order_tax"]').val(order_tax.toFixed(2));
        $('#shipping-cost').text(shipping_cost.toFixed(2));
        $('#grand-total').text(grand_total.toFixed(2));
        $('input[name="grand_total"]').val(grand_total.toFixed(2));
        $('#tips').text(tips.toFixed(2));
        $('input[name="total_tips"]').val(tips.toFixed(2));
    }

    function hide() {
        $(".card-element").hide();
        $("#name_card").hide();
        $("#tarjeta_de_credito_debito").hide();
        $('input[name="number_card"]').val("");
        $(".card-errors").hide();
        $(".cheque").hide();
        $(".gift-card").hide();
        $('.visual_monto_gift_card').hide();
        $(".qrsimple").hide();
        $('input[name="cheque_no"]').attr('required', false);
    }

    function giftCard() {
        var id_c = $("#customer_id").val();
        $(".gift-card").show();
        $.ajax({
            url: 'get_gift_card/' + id_c,
            type: "GET",
            dataType: "json",
            success: function(data) {
                $('#add-payment select[name="gift_card_id_select"]').empty();
                $.each(data, function(index) {
                    gift_card_amount[data[index]['id']] = data[index]['amount'];
                    gift_card_expense[data[index]['id']] = data[index]['expense'];
                    $('#add-payment select[name="gift_card_id_select"]').append('<option value="' +
                        data[index]['id'] + '">' + data[index]['card_no'] + '</option>');
                });
                $('.selectpicker').selectpicker('refresh');
                $('.selectpicker').selectpicker();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                swal("Error", "Estado: " + textStatus + " Error: " + errorThrown, "error");
            }
        });
        $(".card-element").hide();
        $(".card-errors").hide();
        $(".cheque").hide();
        $(".qrsimple").hide();
        $('input[name="cheque_no"]').attr('required', false);
        $("#name_card").hide();
        $("#tarjeta_de_credito_debito").hide();
        $('.visual_monto_gift_card').hide();
    }

    function cheque() {
        $(".cheque").show();
        $('input[name="cheque_no"]').attr('required', true);
        $(".card-element").hide();
        $(".card-errors").hide();
        $(".gift-card").hide();
        $(".qrsimple").hide();
        $("#tarjeta_de_credito_debito").hide();
    }

    function creditCard() {
        const customerId = $('#customer_id').val();
        const customer = limsCustomerList.find(item => item.id == customerId);
        //$.getScript("public/vendor/stripe/checkout.js");
        $(".card-element").show();
        $(".card-errors").show();
        $("#name_card").show();
        $("#tarjeta_de_credito_debito").show();
        $('input[name="name_card"]').val(customer.name);
        $(".cheque").hide();
        $(".gift-card").hide();
        $(".qrsimple").hide();
        $('input[name="cheque_no"]').attr('required', false);
    }

    function deposits() {
        if ($('input[name="paid_amount"]').val() >= deposit[$('#customer_id').val()]) {
            alert('Monto excede el depósito del cliente! Depósito del cliente : ' + deposit[$('#customer_id').val()]);
        }
        $('input[name="cheque_no"]').attr('required', false);
        $('#add-payment select[name="gift_card_id_select"]').attr('required', false);
    }

    async function qrsimple() {
        $(".qrsimple").show();
        const customerId = $('#customer_id').val();
        const customer = limsCustomerList.find(item => item.id == customerId);
        console.log("customer", customer);
        const form = new FormData(document.querySelector('.payment-form'));
        const qrImg = document.querySelector(".qrsimple-img");
        const cantidades = form.getAll("qty[]");
        const productosId = form.getAll("product_id[]");
        const subTotal = form.getAll("subtotal[]");
        const subTotalUnit = form.getAll("sub_total_unit[]");
        const detalles = [];
        const facturasId = [];
        let nrofact = '';
        let nroOrden = 0;

        for (let i = 0; i < productosId.length; i++) {
            detalles.push({
                item: productosId[i],
                descripcion: `Factura Nro. #${productosId[i]}`,
                precioUnitario: subTotalUnit[i],
                cantidad: cantidades[i],
                subTotal: subTotal[i]
            });
            nrofact = nrofact + " - " + productosId[i];
            nroOrden = nroOrden + productosId[i];
            facturasId.push({
                idfac: productosId[i]
            });
        }

        const body = {
            clienteNombre: customer.name,
            clienteApellidoPaterno: customer.name,
            clienteApellidoMaterno: customer.name,
            email: customer.email,
            nit: "0",
            nroDocumento: '0',
            nroPedido: nroOrden,
            razonSocial: customer.name,
            direccion: customer.address,
            tipoDocumento: "CI",
            codigoComercio: 'DANSOL',
            montoTotal: form.get("total_price"),
            esFacturado: 0,
            tipoAltaFacturas: "factura_unica",
            urlCallback: '',
            moneda: 1,
            detalle: detalles,
            conceptoGlosa: `Factura Nro. ${nrofact}`,
            codigoCiudad: 'S',
            vencimiento: '2022-01-01',
            codigoPais: 'BO',
            codigoAreaTelefono: '591',
            telefono: customer.phone_number,
            banco: 'BNB',
        };
        console.log("body", body);
        timer(10 * 60);

        try {
            const result = await generateQR(body);
            console.log('result', result);
            qrImg.src = `data:image/png;base64,${result.qr}`
            checkStatusIntervalId = setInterval(async () => {
                const resultStatus = await checkStatus(result['numeroTransaccion'], facturasId);
                console.log("resultStatus", resultStatus);
                if (result['estadoqr'] == "PAGADO") {
                    clearInterval(checkStatusIntervalId);
                    const resultPayBill = await payBill(result['numeroTransaccion'], facturasId);
                    console.log("resultPayBill", resultPayBill);
                    $('.payment-form').submit();
                }
            }, 20000);
        } catch (error) {
            console.log("error", error);
            alert(error.message);
        }
    }

    function timer(duration = 10000) {
        const timer = document.querySelector("#timer");
        timerIntervalId = setInterval(() => {
            const minutes = parseInt(duration / 60) + "";
            const seconds = parseInt(duration % 60) + "";
            timer.textContent = `${minutes.padStart(2, "0")}:${seconds.padStart(2, "0")}`;
            if (duration == 0) {
                clearInterval(timerIntervalId);
                $('#add-payment').modal("hide");
            }
            duration--;
        }, 1000);
    }

    async function checkStatus(transactionId) {
        // const url = 'https://dan-solutions.net/v2/wp-json/gisul/v1/statusqr';
        const url = 'http://192.168.0.11:90/tienda/wp-json/gisul/v1/statusqr';
        const resp = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Auth-Key': '17G1sU109'
            },
            body: JSON.stringify({
                'idtra': transactionId
            }),
        });
        const result = await resp.json();
        if (resp.ok) {
            return result;
        } else {
            throw new Error("Ups ocurrio un error");
        }
    }

    async function payBill(transactionId, facturasId = []) {
        // const url = 'https://dan-solutions.net/v2/wp-json/gisul/v1/billpay';
        const url = 'http://192.168.0.11:90/tienda/wp-json/gisul/v1/billpay';
        const resp = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Auth-Key': '17G1sU109'
            },
            body: JSON.stringify({
                'idtrac': transactionId,
                'idfact': facturasId
            }),
        });
        const result = await resp.json();
        if (resp.ok) {
            return result;
        } else {
            throw new Error("Ups ocurrio un error");
        }
    }

    async function getToken() {
        const url = 'http://181.188.132.73:5001/TokenRest/v1/token';
        // const url = 'http://66.94.100.10:5001/TokenRest/v1/token';
        const resp = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                dataUser: 'ziiREpYSkCGeG7hIk5j0ng==',
                dataPassword: 'ziiREpYSkCGeG7hIk5j0ng==',
            }),
        });
        const result = await resp.json();
        if (resp.ok) {
            return result["token"];
        } else {
            throw new Error("Ups ocurrio un error");
        }
    }

    async function generateQR(body = {}) {
        const url = "http://181.188.132.73:5003/apirest/qr/v1/genera";
        // const url = 'http://66.94.100.10:5003/apirest/qr/v1/genera';
        const token = await getToken();
        const resp = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Authorization: token,
            },
            body: JSON.stringify(body),
        });
        const result = await resp.json();
        if (resp.ok) {
            return result;
        } else {
            throw {
                ...result
            };
        }
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
        $('select[name="order_tax_rate_select"]').val(0);
        $('input[name="total_tips"]').val(0);
        $('input[name="tips"]').val('');
        $('#tips').text('0');
        tips = 0;
        calculateTotal();
    }

    function confirmCancel() {
        var audio = $("#mysoundclip2")[0];
        audio.play();

        swal({
                title: "Esta seguro de querer cancelar?",
                text: "Esta accion limpiara los item de la tabla!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((res) => {
                if (res) {
                    cancel($('table.order-list tbody tr:last').index());
                    $('input[name="presale_id"]').val(0);
                    $("input[name=attentionshift_id]").val(0);
                    $("#presale-btn").html('<i class="fa fa-save"></i> Generar Pre-Venta');
                } else {
                    return false;
                }
            });
    }

    $(document).on('submit', '.payment-form', function(e) {
        var rownumber = $('table.order-list tbody tr:last').index();
        if (rownumber < 0) {
            emp_temp = false;
            swal("Informacion", "Por favor, inserte el producto para ordenar la tabla!", "info");

            e.preventDefault();
        } else if (parseFloat($('input[name="paying_amount"]').val()) < parseFloat($(
                'input[name="paid_amount"]').val())) {
            swal("Informacion", "La cantidad de pago no puede ser más grande que la cantidad recibida",
                "info");
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

    $("#showpresale-btn").on("click", function() {
        filterpresale();
    });

    function filterpresale() {
        if (permission_turno) {
            $('#table-presale').DataTable({
                destroy: true,
                "processing": true,
                "serverSide": true,
                "ajax": {
                    url: baseUrl + "/presales/list/1",
                    dataType: "json",
                    type: "get"
                },
                "createdRow": function(row, data, dataIndex) {
                    $(row).addClass('presale-link');
                    $(row).attr('data-presale', data['id']);
                },
                "columns": [{
                        "data": "key"
                    },
                    {
                        "data": "reference_no"
                    },
                    {
                        "data": "attentionshift"
                    },
                    {
                        "data": "employee"
                    },
                    {
                        "data": "customer"
                    },
                    {
                        "data": "grand_total"
                    },
                    {
                        "data": "options"
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
                order: [
                    ['1', 'desc']
                ],
                'columnDefs': [{
                        "orderable": false,
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
                    text: 'Cargar PreVentas',
                    className: 'btn-success fa fa-upload',
                    action: function(e, dt, node, config) {
                        presale_id.length = 0;
                        $(':checkbox:checked').each(function(i) {
                            if (i) {
                                var presale = $(this).closest('tr').data('presale');
                                presale_id[i - 1] = presale;
                            }
                        });
                        console.log(presale_id);
                        if (presale_id.length) {
                            cancel($('table.order-list tbody tr:last').index());
                            $('input[name="presale_id"]').val(0);
                            presale_id.forEach(element => {
                                loadPresale(element, true);
                            });
                        } else if (!presale_id.length)
                            msg = new swal("Mensaje", "No se selecciono ninguna PreVenta", 'error');

                    }
                }]
            });
        } else {
            $('#table-presale').DataTable({
                destroy: true,
                "processing": true,
                "serverSide": true,
                "ajax": {
                    url: baseUrl + "/presales/list/1",
                    dataType: "json",
                    type: "get"
                },
                "createdRow": function(row, data, dataIndex) {
                    //$(row).addClass('sale-link');
                    //$(row).attr('data-sale', data['sale']);
                },
                "columns": [{
                        "data": "key"
                    },
                    {
                        "data": "date"
                    },
                    {
                        "data": "reference_no"
                    },
                    {
                        "data": "customer"
                    },
                    {
                        "data": "grand_total"
                    },
                    {
                        "data": "options"
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
                order: [
                    ['1', 'desc']
                ],
                'columnDefs': [{
                        "orderable": false,
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
                    text: 'Cargar PreVentas',
                    className: 'btn-success fa fa-upload',
                    action: function(e, dt, node, config) {
                        presale_id.length = 0;
                        $(':checkbox:checked').each(function(i) {
                            if (i) {
                                var presale = $(this).closest('tr').data('presale');
                                presale_id[i - 1] = presale;
                            }
                        });
                        console.log(presale_id);
                        if (presale_id.length) {
                            cancel($('table.order-list tbody tr:last').index());
                            $('input[name="presale_id"]').val(0);
                            presale_id.forEach(element => {
                                loadPresale(element, true);
                            });
                        } else if (!presale_id.length)
                        msg = new swal("Mensaje", "No se selecciono ninguna PreVenta", 'error');

                    }
                }]
            });
        }
    }


    function loadPresale(id, checked = false) {
        if (checked == false) {
            if ($('input[name="presale_id"]').val() != 0) {
                msg = new swal("Advertencia", "Se detecto datos de una preventa anterior se limpiara la venta.", 'info');
                cancel($('table.order-list tbody tr:last').index());
                $('input[name="presale_id"]').val(0);
            }
        }
        var url = "presales/"
        url = url.concat(id).concat("/edit");
        $.get(url, function(data) {

            var customer_id = data.head.customer_id;
            $('#customer_id').val(customer_id);
            $('input[name="presale_id"]').val(data.head.id);
            var warehouse_id = data.head.warehouse_id;
            $('select[name="warehouse_id"]').val(warehouse_id);
            if (data.head.order_discount != null && data.head.order_discount != 0) {
                $('input[name="order_discount"]').val(data.head.order_discount);
            } else {
                $('input[name="order_discount"]').val('');
            }
            if (data.head.shipping_cost != null && data.head.shipping_cost != 0) {
                $('input[name="shipping_cost"]').val(data.head.shipping_cost);
            } else {
                $('input[name="shipping_cost"]').val('');
            }
            if (data.head.tips != null) {
                tips = parseFloat(tips + data.head.tips);
                $('#tips').text(tips.toFixed(2));
                $('input[name="total_tips"]').val(tips.toFixed(2));
            }
            $('.selectpicker').selectpicker('refresh');
            var list_item = [];
            list_item = data.body;

            if (!customer_id)
            msg = new swal("Informacion", "Por favor, seleccione cliente!", "info");
            else if (!warehouse_id)
            msg = new swal("Informacion", "Por favor, seleccione almacen!", "info");
            else {
                list_item.forEach(element => {
                    pos = product_code.indexOf(element.code);
                    if (pos < 0)
                    msg = new swal("Error de Producto", "Producto no disponible en el almacen seleccionado!",
                            "error");
                    else {
                        var filter = [];
                        filter.push(element.code);
                        filter.push(customer_id);
                        console.log(element);
                        productSearch(filter, false, element.employee_id, element);
                    }
                });
                $('#presaleTransaction').modal('hide')
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
                if (checked == false) {
                    $("#presale-btn").prop('disabled', false);
                    $("#presale-btn").html('<i class="fa fa-save"></i> Actualizar Pre-Venta');
                } else {
                    $("#presale-btn").prop('disabled', true);
                }
            }
        });
    }



    function choose_turno() {
        $("#turno_id").empty();
        $.get('attention/listsimple', function(data) {
            if (data) {
                addOptions("turno_id", data, 3);
            } else {
                swal('Asignacion', "Sin turnos disponibles, intente de nuevo!", "error");
            }
            //$('#selecturno-modal').modal('show');
            $('#selecturno-modal').modal();
            $('.selectpicker').selectpicker('refresh');
        });
    }

    $('#btn_updturno').on('click', function() {
        var genPresalebtn = document.getElementById("presale-btn");
        $("input[name=attentionshift_id]").val($('select[name=turno_id]').val());
        $('#selecturno-modal').modal('hide')
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
        genPresalebtn.click();
    });
    // Function start
    $.fn.getFormObject = function() {
        var object = $(this).serializeArray().reduce(function(obj, item) {
            var name = item.name.replace("[]", "");
            if (typeof obj[name] !== "undefined") {
                if (!Array.isArray(obj[name])) {
                    obj[name] = [obj[name], item.value];
                } else {
                    obj[name].push(item.value);
                }
            } else {
                obj[name] = item.value;
            }
            return obj;
        }, {});
        return object;
    }
    // Function ends


    // 
    // funciones solo para el tabs modal metodo de pago, y salga facturar
    // y mostrar alertas si son necesarios
    // ventanaFacturar
    $(function() {

        $('#segundoTabContinue').click(function(e) {
            e.preventDefault();
            mostrarLabelContingencia();
            getEstadoSIN();
            determinarVigenciaCUFDxBiller();
            $("#submit-btn").removeClass("disabled noselect");
            $('#myTab a[href="#segundoTab"]').tab('show');
        });

        $('#myTab a[href="#segundoTab"]').click(function(e) {
            e.preventDefault();
            mostrarLabelContingencia();
            getEstadoSIN();
            determinarVigenciaCUFDxBiller();
            $("#submit-btn").removeClass("disabled noselect");
        });

        $('#adsContinue').click(function(e) {
            e.preventDefault();
            $('#myTab a[href="#placementPanel"]').tab('show');
        });
    })

    // Funcion para el numero de tarjeta de credito/debito
    const formulario = document.querySelector('#number_card');

    

    // Funciones para determinar los servicios de Impuestos Nacionales
    function getEstadoSIN() {
        var url = '{{ route('estado_servicios_sin') }}';
        console.log('funcion getEstadoSIN, para determinar los Servicios de Impuestos Nacionales');
        $('#label_contingencia').hide();
        $.ajax({
            url: url,
            type: "GET",
            async: false,
            success: function(data) {
                if (data == true) {
                    console.log('Servicios SIN en línea => ' + data);
                    bandera_servicio_sin = true;
                } else {
                    console.log('Falso, los servicios no están funcionando, SIN caído => '+ data);
                    bandera_servicio_sin = false;
                    <?php if(session()->has('token_siat')){ ?>
                    alertaPuntoVentaContingencia();
                    <?php } ?>
                }
            }
        });
    }

    function alertaPuntoVentaContingencia() {
        var id = $('select[name=biller_id]').val();
        var url = '{{ route('estado_punto_venta_contingencia', ':id') }}';
        url = url.replace(':id', id);

        $.ajax({
            url: url,
            type: "GET",
            success: function(data) {
                if (data == true) {
                    // el punto de venta se encuentra en modo contingencia
                    console.log('El punto de venta se encuentra en modo contingencia! ');
                    bandera_puntoventa_contingencia = true;
                } else {
                    bandera_puntoventa_contingencia = false;
                    swal({
                        icon: 'warning',
                        title: 'Problemas de conexión con SIAT',
                        html: 'Se recomienda activar <b>Modo Contingencia</b>, ' +
                            '<a href="{{ route('contingencia.index') }}" target="_blank">casos especiales</a> ' +
                            'para generar facturas. ',
                    });
                }
            }
        });
    }

    function mostrarLabelContingencia() {
        var id = $('select[name=biller_id]').val();
        var url = '{{ route('estado_punto_venta_contingencia', ':id') }}';
        url = url.replace(':id', id);

        $.ajax({
            url: url,
            type: "GET",
            success: function(data) {
                if (data == true) {
                    // el punto de venta se encuentra en modo contingencia
                    console.log('mostrarLabelContingencia ')
                    $('#label_contingencia').show();
                    bandera_puntoventa_contingencia = true;
                } else {
                    $('#label_contingencia').hide();
                    bandera_puntoventa_contingencia = false;
                }
            }
        });

    }
    // Fin Funciones para determinar los servicios de Impuestos Nacionales

    function determinarVigenciaCUFDxBiller() {
        var id = $('select[name=biller_id]').val();
        var url = '{{ route('estado_vigencia_cufd', ':id') }}';
        url = url.replace(':id', id);

        $.ajax({
            url: url,
            type: "GET",
            async: false,
            success: function(data) {
                console.log('determinarVigenciaCUFD del Biller => '+ id + ', respuesta => ' + data + '  [1:ok, 0:not]');
                if (data == true) {
                    // Por verdad, la hora actual es menos de las 23:30, la vigencia está ok
                    $("input[name='bandera_vigencia_cufd_hidden']").val(1);
                    console.log('La vigencia del cufd está dentro de la hora actual');
                    
                } else {
                    // Por falso, la hora actual es más de las 23:30 o pasada las 00:00
                    $("input[name='bandera_vigencia_cufd_hidden']").val(0);
                    console.log('La hora actual está en el límite de la vigencia del cufd');
                    swal({
                        icon: 'warning',
                        title: 'Vigencia del CUFD al límite de terminar',
                        showConfirmButton: false,
                        html: 'Se recomienda <b>renovar los cufd. </b> Pulsa el siguiente botón para ' +
                            '<button type="button" class="vigencia-renovar-cufd btn btn-warning">Renovar vigencia CUFD</button> ' +
                            'para el día siguiente. ',
                    });
                }
            }
        }); 
    }

    // Para el botón existente en es sweet alert de la vigencia 23:00
    // Botón ajax para forzar la vigencia de los cufd.
    $(document).on("click", ".vigencia-renovar-cufd", function(event) {
        var id = $('select[name=biller_id]').val();
        var url = '{{ route('vigencia_renovar_cufd', ':id') }}';
        url_data = url.replace(':id', id);
        
        $("#spinner-div").show(); //Mostrar icon spinner de cargando
        $.ajax({
            url: url_data, 
            type: "GET",
            async: false,
            success: function (data) {
                if (data == true) {
                    $("input[name='bandera_vigencia_cufd_hidden']").val(1);
                    swal('Renovación Exitosa', 'Cufd renovado para el punto de venta!'); 
                }
                else{
                    $("input[name='bandera_vigencia_cufd_hidden']").val(0);
                    swal('Error', 'no se logró renovar los cufd.'); 
                }
            },
            complete: function () {
                $("#spinner-div").hide(); //Ocultar icon spinner de cargando
            },
            error: function () {
                swal('Error', 'error en el servicio!'); 
            },
        });
    });

    // permite pulsar tecla enter y buscar coincidencias
    $('#sales_valor_documento').keypress(function(event){
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == '13'){
            consultar_ValorDocumento();
            console.log('El valor documento ha sido presionado para buscar coincidencias en la Base de Datos -----');
        }
        event.stopPropagation();
    });

    // funcion en desuso
    function verificacionDeLasBanderas() {
        console.log('Verificación de las banderas SIN, Contingencia');
        var url_sin = '{{ route('estado_servicios_sin') }}';
        $.ajax({
            url: url_sin,
            type: "GET",
            success: function(data) {
                if (data == true) {
                    bandera_servicio_sin = true;
                } else {
                    bandera_servicio_sin = false;
                }
            }
        });


        var id = $('select[name=biller_id]').val();
        var url = '{{ route('estado_punto_venta_contingencia', ':id') }}';
        url = url.replace(':id', id);
        $.ajax({
            url: url,
            type: "GET",
            success: function(data) {
                if (data == true) {
                    bandera_puntoventa_contingencia = true;
                } else {
                    bandera_puntoventa_contingencia = false;
                }
            }
        });
    }

    function ocultarSpinner() {
        $("#spinner-div").hide();
    }

    // Mostrar tabla con paginación de 5 items para botón ventasRecientes
    $('#ventas-recientes-table').DataTable( {
        dom: 'rt<"d-flex align-items-baseline"lpi>',
        'language': {
            'lengthMenu': '_MENU_ {{trans("file.records per page")}}',
            "info":      '<small>{{trans("file.Showing")}} _START_ - _END_ (_TOTAL_)</small>',
            "search":  '{{trans("file.Search")}}',
            'paginate': {
                    'previous': '<i class="dripicons-chevron-left"></i>',
                    'next': '<i class="dripicons-chevron-right"></i>'
            }
        },
        'lengthMenu': [[5, 10, -1], [5, 10, "All"]],
        
    });

    
</script>

@endsection