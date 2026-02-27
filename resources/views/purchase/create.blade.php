@extends('layout.main') @section('content')
    @if (session()->has('not_permitted'))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert"
                aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}
        </div>
    @endif
    <section class="forms">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4>{{ trans('file.Add Purchase') }}</h4>
                        </div>
                        <div class="card-body">
                            <p class="italic">
                                <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                            </p>
                            {!! Form::open(['route' => 'purchases.store', 'method' => 'post', 'files' => true, 'id' => 'purchase-form']) !!}
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{ trans('file.Warehouse') }} *</label>
                                                <select required name="warehouse_id" class="selectpicker form-control"
                                                    data-live-search="true" data-live-search-style="begins"
                                                    title="Select warehouse...">
                                                    @foreach ($lims_warehouse_list as $warehouse)
                                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{ trans('file.Supplier') }} *</label>
                                                <select required name="supplier_id" class="selectpicker form-control"
                                                    data-live-search="true" data-live-search-style="begins"
                                                    title="Select supplier...">
                                                    @foreach ($lims_supplier_list as $supplier)
                                                        <option value="{{ $supplier->id }}">
                                                            {{ $supplier->name . ' (' . $supplier->company_name . ')' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label>{{ trans('file.Date') }} <small>(Opcional si quiere guardar con otra
                                                    fecha)</small></label>
                                            <input type="date" name="created_at" class="form-control">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ trans('file.Purchase Status') }}</label>
                                                <select name="status" class="form-control">
                                                    <option value="4">{{ trans('file.Ordered') }}</option>
                                                    <option value="3">{{ trans('file.Pending') }}</option>
                                                    <option value="2">{{ trans('file.Partial') }}</option>
                                                    <option value="1">{{ trans('file.Recieved') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ trans('file.Attach Document') }}</label> <i
                                                    class="dripicons-question" data-toggle="tooltip"
                                                    title="Only jpg, jpeg, png, gif, pdf, csv, docx, xlsx and txt file is supported"></i>
                                                <input type="file" name="document" class="form-control">
                                                @if ($errors->has('extension'))
                                                    <span>
                                                        <strong>{{ $errors->first('extension') }}</strong>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-12 mt-3">
                                            <label>{{ trans('file.Select Product') }}</label>
                                            <div class="search-box input-group">
                                                <button class="btn btn-secondary"><i class="fa fa-barcode"></i></button>
                                                <input type="text" name="product_code_name" id="lims_productcodeSearch"
                                                    placeholder="Please type product code and select..."
                                                    class="form-control" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-4">
                                        <div class="col-md-12">
                                            <h5>{{ trans('file.Order Table') }} *</h5>
                                            <div class="table-responsive mt-3">
                                                <table id="myTable" class="table table-hover order-list">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ trans('file.name') }}</th>
                                                            <th>{{ trans('file.Code') }}</th>
                                                            <th>Lote</th>
                                                            <th>{{ trans('file.Quantity') }}</th>
                                                            <th class="recieved-product-qty d-none">
                                                                {{ trans('file.Recieved') }}</th>
                                                            <th>{{ trans('file.Net Unit Cost') }}</th>
                                                            <th>{{ trans('file.Discount') }}</th>
                                                            <th>{{ trans('file.Tax') }}</th>
                                                            <th>{{ trans('file.Subtotal') }}</th>
                                                            <th><i class="dripicons-trash"></i></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    </tbody>
                                                    <tfoot class="tfoot active">
                                                        <th colspan="2">{{ trans('file.Total') }}</th>
                                                        <th></th>
                                                        <th id="total-qty">0</th>
                                                        <th class="recieved-product-qty d-none"></th>
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
                                                <input type="hidden" name="total_cost" />
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
                                                <input type="hidden" name="paid_amount" value="0.00" />
                                                <input type="hidden" name="payment_status" value="1" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{ trans('file.Order Tax') }}</label>
                                                <select class="form-control" name="order_tax_rate">
                                                    <option value="0">{{ trans('file.No Tax') }}</option>
                                                    @foreach ($lims_tax_list as $tax)
                                                        <option value="{{ $tax->rate }}">{{ $tax->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>
                                                    <strong>{{ trans('file.Discount') }}</strong>
                                                </label>
                                                <input type="number" name="order_discount" class="form-control"
                                                    step="any" />
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>
                                                    <strong>{{ trans('file.Shipping Cost') }}</strong>
                                                </label>
                                                <input type="number" name="shipping_cost" class="form-control"
                                                    step="any" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>{{ trans('file.Note') }}</label>
                                                <textarea rows="5" class="form-control" name="note"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary"
                                            id="submit-btn">{{ trans('file.submit') }}</button>
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
                <td><strong>{{ trans('file.Items') }}</strong>
                    <span class="pull-right" id="item">0.00</span>
                </td>
                <td><strong>{{ trans('file.Total') }}</strong>
                    <span class="pull-right" id="subtotal">0.00</span>
                </td>
                <td><strong>{{ trans('file.Order Tax') }}</strong>
                    <span class="pull-right" id="order_tax">0.00</span>
                </td>
                <td><strong>{{ trans('file.Order Discount') }}</strong>
                    <span class="pull-right" id="order_discount">0.00</span>
                </td>
                <td><strong>{{ trans('file.Shipping Cost') }}</strong>
                    <span class="pull-right" id="shipping_cost">0.00</span>
                </td>
                <td><strong>{{ trans('file.grand total') }}</strong>
                    <span class="pull-right" id="grand_total">0.00</span>
                </td>
            </table>
        </div>
        <div id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
            class="modal fade text-left">
            <div role="document" class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="modal-header" class="modal-title"></h5>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="form-group">
                                <label>{{ trans('file.Quantity') }}</label>
                                <input type="number" name="edit_qty" class="form-control" step="any">
                            </div>
                            <div class="form-group">
                                <label>{{ trans('file.Unit Discount') }}</label>
                                <input type="number" name="edit_discount" class="form-control" step="any">
                            </div>
                            <div class="form-group">
                                <label>{{ trans('file.Unit Cost') }}</label>
                                <input type="number" name="edit_unit_cost" class="form-control" step="any">
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
                            <div class="form-group">
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

        <div id="addLoteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
            class="modal fade text-left">
            <div role="document" class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="modal-header-lote" class="modal-title"></h5>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                    </div>
                    <div class="modal-body">
                        <div id="alert_msg" class="alert alert-warning" role="alert"
                            style="display: inline-table !important;">
                            <strong>Advertencia:</strong> al ingresar varios lotes se recomienta no modificar desde cantidad
                            en la tabla principal!
                        </div>
                        <form>
                            <div class="col-md-12">
                                <h5>Lotes X Producto</h5>
                                <div id="btn_addlote"><button type="button" name="add_lote" class="btn btn-primary"
                                        style="background-color:green" onclick="showformlote()"><i
                                            class="fa fa-plus"></i></span> Añadir</button></div>
                                <div id="form_lote" class="d-none">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Codigo Lote</label>
                                                <input type="text" name="lote_name" class="form-control"
                                                    placeholder="Referencia de Lote">
                                            </div>

                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Fecha Vencimiento</label>
                                                <input required type="date" name="lote_venc" class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <button type="button" name="save_lote" class="btn btn-primary"
                                                    style="background-color:green" onclick="addlote()"><i
                                                        class="fa fa-save"></i></span> Guardar</button>
                                                <button type="button" name="back_lote" class="btn btn-primary"
                                                    style="background-color:steelblue" onclick="hideformlote()"><i
                                                        class="fa fa-back"></i></span> Volver </button>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{ trans('file.Quantity') }}</label>
                                                <input type="number" name="lote_qty" class="form-control"
                                                    step="any">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="listLote" class="table-responsive mt-3">
                                    <table id="dataLote" class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Codigo Lote</th>
                                                <th>Fecha Vencimiento</th>
                                                <th>{{ trans('file.Quantity') }}</th>
                                                <th><i class="dripicons-trash"></i></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <button id="btn_updatelote" type="button" name="update_btn_lote"
                                class="btn btn-primary">{{ trans('file.update') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script type="text/javascript">
        $("ul#purchase").siblings('a').attr('aria-expanded', 'true');
        $("ul#purchase").addClass("show");
        $("ul#purchase #purchase-create-menu").addClass("active");

        // array data depend on warehouse
        var lims_product_array = [];
        var product_code = [];
        var product_name = [];
        var product_qty = [];

        // array data with selection
        var product_cost = [];
        var product_discount = [];
        var tax_rate = [];
        var listLote = [];
        var tax_name = [];
        var tax_method = [];
        var unit_name = [];
        var unit_operator = [];
        var unit_operation_value = [];

        // temporary array
        var temp_unit_name = [];
        var temp_unit_operator = [];
        var temp_unit_operation_value = [];

        var rowindex;
        var customer_group_rate;
        var row_product_cost;

        var fechaMinExp = new Date('1990-01-01');

        $('.selectpicker').selectpicker({
            style: 'btn-link',
        });

        $('[data-toggle="tooltip"]').tooltip();

        $('select[name="status"]').on('change', function() {
            if ($('select[name="status"]').val() == 2) {
                $(".recieved-product-qty").removeClass("d-none");
                $(".qty").each(function() {
                    rowindex = $(this).closest('tr').index();
                    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.recieved').val(
                        $(this).val());
                });
                $(".qty").prop('readOnly', true);
            } else if (($('select[name="status"]').val() == 3) || ($('select[name="status"]').val() == 4)) {
                $(".recieved-product-qty").addClass("d-none");
                $(".recieved").each(function() {
                    $(this).val(0);
                });
                $(".qty").prop('readOnly', false);
            } else {
                $(".recieved-product-qty").addClass("d-none");
                $(".qty").each(function() {
                    rowindex = $(this).closest('tr').index();
                    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.recieved').val(
                        $(this).val());
                });
                $(".qty").prop('readOnly', true);
            }
        });

        var lims_productcodeSearch = $('#lims_productcodeSearch');

        lims_productcodeSearch.autocomplete({
            minLength: 0,
            source: "{{ route('purchases.getproducts') }}",
            focus: function(event, ui) {
                $("#lims_productcodeSearch").val(ui.item.value);
                return false;
            },
            response: function(event, ui) {
                if (ui.content.length == 1) {
                    var data = ui.content[0].value;
                    $(this).autocomplete("close");
                    productSearch(data);
                };
            },
            select: function(event, ui) {
                var data = ui.item.value;
                productSearch(data);
            }
        }).autocomplete("instance")._renderItem = function(ul, item) {
            return $("<li>")
                .append("<div>" + item.value + "</div>")
                .appendTo(ul);
        };

        //Change quantity
        $("#myTable").on('input', '.qty', function() {
            rowindex = $(this).closest('tr').index();
            if ($(this).val() < 1 && $(this).val() != '') {
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(1);
                swal("Error", "Cantidad no puede ser menos que < 1", "error");
            }
            checkQuantity($(this).val(), true);
        });


        //Delete product
        $("#myTable tbody").on("click", ".ibtnDel", function(event) {
            rowindex = $(this).closest('tr').index();
            product_cost.splice(rowindex, 1);
            product_discount.splice(rowindex, 1);
            tax_rate.splice(rowindex, 1);
            tax_name.splice(rowindex, 1);
            tax_method.splice(rowindex, 1);
            unit_name.splice(rowindex, 1);
            unit_operator.splice(rowindex, 1);
            unit_operation_value.splice(rowindex, 1);
            $(this).closest("tr").remove();
            calculateTotal();
        });

        //Edit product
        $("#myTable").on("click", ".edit-product", function() {
            rowindex = $(this).closest('tr').index();
            var row_product_name = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find(
                'td:nth-child(1)').text();
            var row_product_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find(
                'td:nth-child(2)').text();
            $('#modal-header').text(row_product_name + '(' + row_product_code + ')');

            var qty = $(this).closest('tr').find('.qty').val();
            $('input[name="edit_qty"]').val(qty);

            $('input[name="edit_discount"]').val(parseFloat(product_discount[rowindex]).toFixed(2));

            unitConversion();
            $('input[name="edit_unit_cost"]').val(row_product_cost.toFixed(2));

            var tax_name_all = <?php echo json_encode($tax_name_all); ?>;
            var pos = tax_name_all.indexOf(tax_name[rowindex]);
            $('select[name="edit_tax_rate"]').val(pos);

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
            $('.selectpicker').selectpicker('refresh');
        });

        //Update product
        $('button[name="update_btn"]').on("click", function() {
            var edit_discount = $('input[name="edit_discount"]').val();
            var edit_qty = $('input[name="edit_qty"]').val();
            var edit_unit_cost = $('input[name="edit_unit_cost"]').val();

            if (parseFloat(edit_discount) > parseFloat(edit_unit_cost)) {
                swal("Error", "Valor de Descuento invalido!", "error");
                return;
            }

            if (edit_qty < 1) {
                $('input[name="edit_qty"]').val(1);
                edit_qty = 1;
                swal("Error", "Cantidad no puede ser menos que < 1", "error");
            }

            var row_unit_operator = unit_operator[rowindex].slice(0, unit_operator[rowindex].indexOf(","));
            var row_unit_operation_value = unit_operation_value[rowindex].slice(0, unit_operation_value[rowindex]
                .indexOf(","));
            row_unit_operation_value = parseFloat(row_unit_operation_value);
            var tax_rate_all = <?php echo json_encode($tax_rate_all); ?>;


            tax_rate[rowindex] = parseFloat(tax_rate_all[$('select[name="edit_tax_rate"]').val()]);
            tax_name[rowindex] = $('select[name="edit_tax_rate"] option:selected').text();


            if (row_unit_operator == '*') {
                product_cost[rowindex] = $('input[name="edit_unit_cost"]').val() / row_unit_operation_value;
            } else {
                product_cost[rowindex] = $('input[name="edit_unit_cost"]').val() * row_unit_operation_value;
            }

            product_discount[rowindex] = $('input[name="edit_discount"]').val();
            var position = $('select[name="edit_unit"]').val();
            var temp_operator = temp_unit_operator[position];
            var temp_operation_value = temp_unit_operation_value[position];
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.purchase-unit').val(
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
            checkQuantity(edit_qty, false);
        });

        function productSearch(data) {
            $.ajax({
                type: 'GET',
                url: '{{ route("product_purchase.search") }}',
                data: {
                    data: data
                },
                success: function(data) {
                    console.log('Product data received:', data);
                    //console.log(data);
                    var flag = 1;
                    $(".product-code").each(function(i) {
                        if ($(this).val() == data[1]) {
                            rowindex = i;
                            var qty = parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex +
                                1) + ') .qty').val()) + 1;
                            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(
                                qty);
                            calculateRowProductData(qty);
                            flag = 0;
                        }
                    });
                    $("input[name='product_code_name']").val('');
                    if (flag) {
                        var newRow = $("<tr>");
                        var cols = '';
                        temp_unit_name = (data[6]).split(',');
                        cols += '<td>' + data[0] +
                            '<button type="button" class="edit-product btn btn-link" data-toggle="modal" data-target="#editModal"> <i class="dripicons-document-edit"></i></button></td>';
                        cols += '<td>' + data[1] + '</td>';
                        cols += '<td><div class="div_show_' + data[1] +
                            '"><button type="button" class="edit-lote btn btn-link" title="Editar Lote"  data-toggle="modal" data-target="#addLoteModal"> <i style="font-size:20px;color:green" class="fa fa-plus-square"></i></button></div></td>';
                        //    +  '<div class="input-group div_name_'+ data[1]+'"> <input type="text" class="form-control" name="names_lote[]" placeholder="Referencia Lote" /> </div> <div class="input-group div_date_'+ data[1]+'"><input type="date" class="form-control" name="expiration_date[]" /></div> </td>';
                        cols +=
                            '<td><input type="number" class="form-control qty" name="qty[]" value="1" step="any" required/></td>';
                        if ($('select[name="status"]').val() == 1)
                            cols +=
                            '<td class="recieved-product-qty d-none"><input type="number" class="form-control recieved" name="recieved[]" value="1" step="any"/></td>';
                        else if ($('select[name="status"]').val() == 2)
                            cols +=
                            '<td class="recieved-product-qty"><input type="number" class="form-control recieved" name="recieved[]" value="1" step="any"/></td>';
                        else
                            cols +=
                            '<td class="recieved-product-qty d-none"><input type="number" class="form-control recieved" name="recieved[]" value="0" step="any"/></td>';
                        cols += '<td class="net_unit_cost"></td>';
                        cols += '<td class="discount">0.00</td>';
                        cols += '<td class="tax"></td>';
                        cols += '<td class="sub-total"></td>';
                        cols +=
                            '<td><button type="button" class="ibtnDel btn btn-md btn-danger">{{ trans('file.delete') }}</button></td>';
                        cols += '<input type="hidden" class="product-code" name="product_code[]" value="' +
                            data[1] + '"/>';
                        cols += '<input type="hidden" class="lote-data" name="lote_data[]"/>';
                        cols += '<input type="hidden" class="product-id" name="product_id[]" value="' + data[
                            9] + '"/>';
                        cols += '<input type="hidden" class="purchase-unit" name="purchase_unit[]" value="' +
                            temp_unit_name[0] + '"/>';
                        cols += '<input type="hidden" class="net_unit_cost" name="net_unit_cost[]" />';
                        cols += '<input type="hidden" class="discount-value" name="discount[]" />';
                        cols += '<input type="hidden" class="tax-rate" name="tax_rate[]" value="' + data[3] +
                            '"/>';
                        cols += '<input type="hidden" class="tax-value" name="tax[]" />';
                        cols += '<input type="hidden" class="subtotal-value" name="subtotal[]" />';
                        $('.buttons-delete').addClass('d-none');
                        newRow.append(cols);
                        $("table.order-list tbody").append(newRow);
                        product_cost.push(parseFloat(data[2]));
                        product_discount.push('0.00');
                        tax_rate.push(parseFloat(data[3]));
                        listLote.push(null);
                        tax_name.push(data[4]);
                        tax_method.push(data[5]);
                        unit_name.push(data[6]);
                        unit_operator.push(data[7]);
                        unit_operation_value.push(data[8]);
                        rowindex = newRow.index();
                        calculateRowProductData(1);
                    }

                }
            });
        }

        function showLote(row, status) {

            //'<td><div class="div_show_'+ data[1]+'"><button type="button" class="btn btn-link" onclick="showLote('+ data[1]+', 1)" title="Editar Lote"> <i style="font-size:20px;color:green" class="fa fa-plus-square"></i></button></div>'
            //+  '<div class="div_hide_'+ data[1]+'"><button type="button" class="btn btn-link" onclick="showLote('+ data[1]+', 2)" title="Ocultar Editar Lote"> <i style="font-size:20px;color:red" class="fa fa-window-close"></i></button></div>'
            if (status == 1) {
                $(`.div_name_${row}`).removeClass("d-none");
                $(`.div_date_${row}`).removeClass("d-none");
                $(`.div_hide_${row}`).removeClass("d-none");
                $(`.div_show_${row}`).addClass("d-none");
            } else {
                $(`.div_name_${row}`).addClass("d-none");
                $(`.div_date_${row}`).addClass("d-none");
                $(`.div_hide_${row}`).addClass("d-none");
                $(`.div_show_${row}`).removeClass("d-none");
            }

        }

        //Edit Lotes
        $("#myTable").on("click", ".edit-lote", function() {
            rowindex = $(this).closest('tr').index();
            var row_product_name = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find(
                'td:nth-child(1)').text();
            var row_product_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find(
                'td:nth-child(2)').text();
            $('#modal-header-lote').text(row_product_name + '(' + row_product_code + ')');
            var list = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.lote-data').val();
            var table = $('#dataLote').DataTable();
            table.clear().draw();
            table.destroy();
            $("#alert_msg").css('display', 'none');
            if (list != "") {
                var list_json = decodeURIComponent(list);
                var JSONstr = JSON.parse(list_json);
                console.log(JSONstr);

                for (var i in JSONstr) {
                    var newRow = $("<tr>");
                    var cols = '';
                    cols += '<td>' + JSONstr[i][0] + '</td>';
                    cols += '<td>' + JSONstr[i][1] + '</td>';
                    cols += '<td class="loteqty">' + JSONstr[i][2] + '</td>';
                    cols += '<td>' + JSONstr[i][3] + '</td>';

                    newRow.append(cols);
                    $("#dataLote tbody").append(newRow);
                }
                $("#alert_msg").css('display', '');
            }
        });

        function showformlote() {
            $("#btn_addlote").addClass("d-none");
            $("#listLote").addClass("d-none");
            $("#btn_updatelote").addClass("d-none");
            $("#form_lote").removeClass("d-none");
            var code = date_now();
            $('input[name="lote_name"]').val("L-" + code);
            $('input[name="lote_qty"]').val(0);
        }

        function hideformlote() {
            $("#btn_addlote").removeClass("d-none");
            $("#listLote").removeClass("d-none");
            $("#btn_updatelote").removeClass("d-none");
            $("#form_lote").addClass("d-none");
        }

        //add lote
        function addlote() {
            var code = $('input[name="lote_name"]').val();
            var date = $('input[name="lote_venc"]').val();
            var qty = $('input[name="lote_qty"]').val();
            var fechalote = new Date(date);
            if (qty <= 0) {
                swal('Error Validacion!', "Cantidad debe ser mayor 0, intente de nuevo", "error");

            } else if (date == '' || fechalote <= fechaMinExp) {
                swal('Error Validacion!', "Fecha invalida o fuera de rango minimo, intente de nuevo", "error");
            } else {
                var newRow = $("<tr>");
                var cols = '';
                cols += '<td>' + code + '</td>';
                cols += '<td>' + date + '</td>';
                cols += '<td class="loteqty">' + qty + '</td>';
                cols +=
                    '<td><button type="button" class="ibtnDelote btn btn-md btn-danger"><i class="dripicons-trash"></button></td>';
                newRow.append(cols);
                $("#dataLote tbody").append(newRow);
                hideformlote();
                calculateTotalLote();
            }
        }


        //Update lote
        $('button[name="update_btn_lote"]').on("click", function() {
            var total_qty = 0;
            let dt = $('#dataLote').DataTable();
            list = dt.data().toArray().filter((data) => data);
            for (var i in list) {
                total_qty += parseFloat(list[i][2]);
            }
            if (list.length != 0) {
                var JSONstr = encodeURIComponent(JSON.stringify(list));
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.lote-data').val(JSONstr);
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').addClass("noselect");
            } else {
                total_qty = 1;
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.lote-data').val("");
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').removeClass(
                    "noselect");
            }
            checkQuantity(total_qty, false);
            $('#addLoteModal').modal('hide');
        });


        function calculateTotalLote() {
            var total_qty = 0;
            $("#dataLote .loteqty").each(function() {

                if ($(this).val() == '') {
                    total_qty += 0;
                } else {
                    total_qty += parseFloat($(this).val());
                }
            });
            $("#totalqtylote").text(total_qty);
        }

        //Delete Lote
        $("#dataLote tbody").on("click", ".ibtnDelote", function(event) {
            rowindex = $(this).closest('tr').index();
            $(this).closest("tr").remove();
            calculateTotalLote();
        });


        function date_now() {
            var today = new Date();
            var dd = String(today.getDate()).padStart(2, '0');
            var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
            var yyyy = today.getFullYear();
            return dd + mm + yyyy;
        }

        function checkQuantity(purchase_qty, flag) {
            var row_product_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(2)')
                .text();
            var pos = product_code.indexOf(row_product_code);
            var operator = unit_operator[rowindex].split(',');
            var operation_value = unit_operation_value[rowindex].split(',');
            if (operator[0] == '*')
                total_qty = purchase_qty * operation_value[0];
            else if (operator[0] == '/')
                total_qty = purchase_qty / operation_value[0];

            $('#editModal').modal('hide');
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(purchase_qty);
            var status = $('select[name="status"]').val();
            if (status == '1' || status == '2')
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.recieved').val(purchase_qty);
            calculateRowProductData(purchase_qty);
        }

        function calculateRowProductData(quantity) {
            unitConversion();
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(7)').text((product_discount[
                rowindex] * quantity).toFixed(2));
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.discount-value').val((product_discount[
                rowindex] * quantity).toFixed(2));
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-rate').val(tax_rate[rowindex]
                .toFixed(2));

            if (tax_method[rowindex] == 1) {
                var net_unit_cost = row_product_cost - product_discount[rowindex];
                var tax = net_unit_cost * quantity * (tax_rate[rowindex] / 100);
                var sub_total = (net_unit_cost * quantity) + tax;

                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(6)').text(net_unit_cost
                    .toFixed(2));
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.net_unit_cost').val(net_unit_cost
                    .toFixed(2));
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(8)').text(tax.toFixed(
                    2));
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-value').val(tax.toFixed(2));
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(9)').text(sub_total
                    .toFixed(2));
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.subtotal-value').val(sub_total
                    .toFixed(2));
            } else {
                var sub_total_unit = row_product_cost - product_discount[rowindex];
                var iva = (tax_rate[rowindex] / 100) * sub_total_unit;
                var net_unit_cost = sub_total_unit - iva;
                var tax = iva * quantity;
                var sub_total = sub_total_unit * quantity;

                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(6)').text(net_unit_cost
                    .toFixed(2));
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.net_unit_cost').val(net_unit_cost
                    .toFixed(2));
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(8)').text(tax.toFixed(
                    2));
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-value').val(tax.toFixed(2));
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(9)').text(sub_total
                    .toFixed(2));
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.subtotal-value').val(sub_total
                    .toFixed(2));
            }

            calculateTotal();
        }

        function unitConversion() {
            var row_unit_operator = unit_operator[rowindex].slice(0, unit_operator[rowindex].indexOf(","));
            var row_unit_operation_value = unit_operation_value[rowindex].slice(0, unit_operation_value[rowindex].indexOf(
                ","));
            row_unit_operation_value = parseFloat(row_unit_operation_value);
            if (row_unit_operator == '*') {
                row_product_cost = product_cost[rowindex] * row_unit_operation_value;
            } else {
                row_product_cost = product_cost[rowindex] / row_unit_operation_value;
            }
        }

        function calculateTotal() {
            //Sum of quantity
            var total_qty = 0;
            $(".qty").each(function() {

                if ($(this).val() == '') {
                    total_qty += 0;
                } else {
                    total_qty += parseFloat($(this).val());
                }
            });
            $("#total-qty").text(total_qty);
            $('input[name="total_qty"]').val(total_qty);

            //Sum of discount
            var total_discount = 0;
            $(".discount").each(function() {
                total_discount += parseFloat($(this).text());
            });
            $("#total-discount").text(total_discount.toFixed(2));
            $('input[name="total_discount"]').val(total_discount.toFixed(2));

            //Sum of tax
            var total_tax = 0;
            $(".tax").each(function() {
                total_tax += parseFloat($(this).text());
            });
            $("#total-tax").text(total_tax.toFixed(2));
            $('input[name="total_tax"]').val(total_tax.toFixed(2));

            //Sum of subtotal
            var total = 0;
            $(".sub-total").each(function() {
                total += parseFloat($(this).text());
            });
            $("#total").text(total.toFixed(2));
            $('input[name="total_cost"]').val(total.toFixed(2));

            calculateGrandTotal();
        }

        function calculateGrandTotal() {

            var item = $('table.order-list tbody tr:last').index();

            var total_qty = parseFloat($('#total-qty').text());
            var subtotal = parseFloat($('#total').text());
            var order_tax = parseFloat($('select[name="order_tax_rate"]').val());
            var order_discount = parseFloat($('input[name="order_discount"]').val());
            var shipping_cost = parseFloat($('input[name="shipping_cost"]').val());

            if (!order_discount)
                order_discount = 0.00;
            if (!shipping_cost)
                shipping_cost = 0.00;

            item = ++item + '(' + total_qty + ')';
            order_tax = (subtotal - order_discount) * (order_tax / 100);
            var grand_total = (subtotal + order_tax + shipping_cost) - order_discount;

            $('#item').text(item);
            $('input[name="item"]').val($('table.order-list tbody tr:last').index() + 1);
            $('#subtotal').text(subtotal.toFixed(2));
            $('#order_tax').text(order_tax.toFixed(2));
            $('input[name="order_tax"]').val(order_tax.toFixed(2));
            $('#order_discount').text(order_discount.toFixed(2));
            $('#shipping_cost').text(shipping_cost.toFixed(2));
            $('#grand_total').text(grand_total.toFixed(2));
            $('input[name="grand_total"]').val(grand_total.toFixed(2));
        }

        $('input[name="order_discount"]').on("input", function() {
            calculateGrandTotal();
        });

        $('input[name="shipping_cost"]').on("input", function() {
            calculateGrandTotal();
        });

        $('select[name="order_tax_rate"]').on("change", function() {
            calculateGrandTotal();
        });

        $(window).keydown(function(e) {
            if (e.which == 13) {
                var $targ = $(e.target);
                if (!$targ.is("textarea") && !$targ.is(":button,:submit")) {
                    var focusNext = false;
                    $(this).find(":input:visible:not([disabled],[readonly]), a").each(function() {
                        if (this === e.target) {
                            focusNext = true;
                        } else if (focusNext) {
                            $(this).focus();
                            return false;
                        }
                    });
                    return false;
                }
            }
        });

        $('#purchase-form').on('submit', function(e) {
            var rownumber = $('table.order-list tbody tr:last').index();
            if (rownumber < 0) {
                swal("Mensaje", "Por favor, inserte el producto para ordenar la tabla!", "info");
                e.preventDefault();
            } else if ($('select[name="status"]').val() != 1) {
                flag = 0;
                $(".qty").each(function() {
                    rowindex = $(this).closest('tr').index();
                    quantity = $(this).val();
                    recieved = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find(
                        '.recieved').val();

                    if (quantity != recieved) {
                        flag = 1;
                        return false;
                    }
                });
                if (!flag) {
                    swal("Mensaje", "¡La cantidad y el valor recibido es el mismo! Por favor, cambie el estado de compra a recibo ó el valor recibido", "warning");
                    e.preventDefault();
                }
            }
        });
    </script>
    @endsection @section('scripts')
    <script type="text/javascript" src="https://js.stripe.com/v3/"></script>
@endsection
