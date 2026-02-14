<script>
    if (!localStorage.getItem('clicked')) {

        localStorage.setItem('url', "{{ route('qty_adjustment.create') }}");

        window.location.href = "{{ route('home') }}";
    }
</script>
@extends('layout.layout')
@section('content')
    <section class="forms">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4>{{ trans('file.Add Adjustment') }}</h4>
                        </div>
                        <div class="card-body">
                            <p class="italic">
                                <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                            </p>
                            {!! Form::open([
                                // 'route' => 'qty_adjustment.store',
                                // 'method' => 'post',
                                'files' => true,
                                'id' => 'adjustment-form',
                            ]) !!}
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{ trans('file.Warehouse') }} *</label>
                                                <select required id="warehouse_id" name="warehouse_id"
                                                    class="selectpicker form-control" data-live-search="true"
                                                    data-live-search-style="begins" title="Select warehouse...">
                                                    @foreach ($lims_warehouse_list as $warehouse)
                                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{ trans('file.Attach Document') }}</label>
                                                <input type="file" name="document" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <label>{{ trans('file.Select Product') }}</label>
                                            <div class="search-box input-group">
                                                <button type="button" class="btn btn-secondary btn-lg"><i
                                                        class="fa fa-barcode"></i></button>
                                                <input type="text" name="product_code_name" id="lims_productcodeSearch"
                                                    placeholder="Please type product code and select..."
                                                    class="form-control" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-5">
                                        <div class="col-md-12">
                                            <h5>{{ trans('file.Order Table') }} *</h5>
                                            <div class="table-responsive mt-3">
                                                <table id="myTable" class="table table-hover order-list">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ trans('file.name') }}</th>
                                                            <th>{{ trans('file.Code') }}</th>
                                                            <th>{{ trans('file.Quantity') }}</th>
                                                            <th>{{ trans('file.action') }}</th>
                                                            <th><i class="dripicons-trash"></i></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    </tbody>
                                                    <tfoot class="tfoot active">
                                                        <th colspan="2">{{ trans('file.Total') }}</th>
                                                        <th id="total-qty" colspan="2">0</th>
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
                                                <input type="hidden" name="item" />
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
                                        <input type="buttom" value="{{ trans('file.submit') }}" class="btn btn-primary"
                                            id="submit-button">
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
        $("ul#product").siblings('a').attr('aria-expanded', 'true');
        $("ul#product").addClass("show");
        $("ul#product #adjustment-create-menu").addClass("active");
        // array data depend on warehouse
        var lims_product_array = [];
        var product_code = [];
        var product_name = [];
        var product_qty = [];

        $('.selectpicker').selectpicker({
            style: 'btn-link',
        });

        $(document).ready(function() {

            $('#lims_productcodeSearch').on('input', function() {
                var warehouse_id = $('#warehouse_id').val();
                temp_data = $('#lims_productcodeSearch').val();
                if (!warehouse_id) {
                    $('#lims_productcodeSearch').val(temp_data.substring(0, temp_data.length - 1));
                    swal('Advertencia!', "Por favor seleccione un almacen.", "warning");
                }
            });

            $('select[name="warehouse_id"]').on('change', function() {
                var id = $(this).val();
                $.get('getproduct/' + id, function(data) {
                    lims_product_array = [];
                    product_code = data[0];
                    product_name = data[1];
                    product_qty = data[2];
                    $.each(product_code, function(index) {
                        lims_product_array.push(product_code[index] + ' (' + product_name[
                            index] + ')');
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
            });

            $("#myTable").on('blur', '.qty', function() {
                rowindex = $(this).closest('tr').index();
                checkQuantity($(this).val(), true);
            });

            $("#myTable").on('change', '.qty', function() {
                rowindex = $(this).closest('tr').index();
                checkQuantity($(this).val(), true);
            });

            $("table.order-list tbody").on("click", ".ibtnDel", function(event) {
                rowindex = $(this).closest('tr').index();
                $(this).closest("tr").remove();
                calculateTotal();
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

            $('#adjustment-form').on('submit', function(e) {
                var rownumber = $('table.order-list tbody tr:last').index();
                if (rownumber < 0) {
                    swal('Error!', "Por favor ingrese un producto en la tabla.", "error");
                    e.preventDefault();
                }
            });

            function productSearch(data) {
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
                                var qty = parseFloat($('table.order-list tbody tr:nth-child(' +
                                    (rowindex + 1) + ') .qty').val()) + 1;
                                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) +
                                    ') .qty').val(qty);
                                checkQuantity(qty);
                                flag = 0;
                            }
                        });
                        $("input[name='product_code_name']").val('');
                        if (flag) {
                            var newRow = $("<tr>");
                            var cols = '';
                            cols += '<td>' + data[0] + '</td>';
                            if (data[4] != null)
                                cols += '<td>' + data[4] + '</td>';
                            else
                                cols += '<td>' + data[1] + '</td>';
                            cols +=
                                '<td><input type="number" class="form-control qty" name="qty[]" value="1" required step="any" onfocus="this.focus();this.select()" /></td>';
                            cols +=
                                '<td class="action"><select name="action[]" class="form-control act-val" onchange="updateAction()"><option value="-">{{ trans('file.Subtraction') }}</option><option value="+">{{ trans('file.Addition') }}</option></select></td>';
                            cols +=
                                '<td><button type="button" class="ibtnDel btn btn-md btn-danger">{{ trans('file.delete') }}</button></td>';
                            if (data[4] != null)
                                cols +=
                                '<input type="hidden" class="product-code" name="product_code[]" value="' +
                                data[4] + '"/>';
                            else
                                cols +=
                                '<input type="hidden" class="product-code" name="product_code[]" value="' +
                                data[1] + '"/>';

                            cols +=
                                '<input type="hidden" class="product-id" name="product_id[]" value="' +
                                data[2] + '"/>';

                            newRow.append(cols);
                            $("table.order-list tbody").append(newRow);
                            rowindex = newRow.index();
                            calculateTotal();
                        }
                    }
                });
            }

            // prueba send form adjustmen

            $('#submit-button').on("click", function(e) {
                e.preventDefault();
                $(this).prop("disabled", true);
                $('#loader').show();
                $.ajax({
                    type: 'POST',
                    url: '{{ route('qty_adjustment.store') }}',
                    data: $("#adjustment-form").serialize(),
                    success: function(response) {

                        setTimeout(() => {

                            $('#loader').hide();

                            setPage("{{ route('qty_adjustment.index') }}")

                        }, 1000);

                    },
                    error: function(response) {
                        if (response.responseJSON.errors.name) {
                            $("#name-error").text(response
                                .responseJSON.errors
                                .name);
                        } else if (response.responseJSON.errors
                            .code) {
                            $("#code-error").text(response
                                .responseJSON.errors
                                .code);
                        }
                    },
                });
            });
        });
        
        function updateAction(){
            checkQuantity(1);
        }

        function checkQuantity(qty) {
            var row_product_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find(
                'td:nth-child(2)').text();
            var action = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.act-val')
                .val();
            var warehouse_id = $('#warehouse_id').val();
            $.get('getproduct-data/' + warehouse_id + '/' + row_product_code, function(res) {
                console.log(res);
                if (res.status === true) {
                    if ((qty > parseFloat(res.qty)) && (action == '-')) {
                        swal('Advertencia!', "Cantidad excede el stock disponible.", "warning");
                        var row_qty = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')')
                            .find(
                                '.qty')
                            .val();
                        row_qty = row_qty.substring(0, row_qty.length - 1);
                        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty')
                            .val(
                                row_qty);
                    } else {
                        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty')
                            .val(
                                qty);
                    }
                } else {
                    swal('Error!',
                        "Stock no disponible producto no existente en almacen, contacte con administrador",
                        "error");
                }
                calculateTotal();
            });
        }

        function calculateTotal() {
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
            $('input[name="item"]').val($('table.order-list tbody tr:last').index() + 1);
        }

    </script>
@endsection
