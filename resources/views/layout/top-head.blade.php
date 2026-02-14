<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" type="image/png" href="{{ url('public/logo', $general_setting->site_logo) }}" />
    <title>{{ $general_setting->site_title }}</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all,follow">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap CSS-->
    <link rel="stylesheet" href="/public/vendor/bootstrap/css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="/public/vendor/bootstrap-toggle/css/bootstrap-toggle.min.css" type="text/css">
    <link rel="stylesheet" href="/public/vendor/bootstrap/css/bootstrap-datepicker.min.css" type="text/css">
    <link rel="stylesheet" href="/public/vendor/jquery-timepicker/jquery.timepicker.min.css" type="text/css">
    <link rel="stylesheet" href="/public/vendor/bootstrap/css/awesome-bootstrap-checkbox.css" type="text/css">
    <link rel="stylesheet" href="/public/vendor/bootstrap/css/bootstrap-select.min.css" type="text/css">
    <!-- Font Awesome CSS-->
    <link rel="stylesheet" href="/public/vendor/font-awesome/css/font-awesome.min.css" type="text/css">
    <!-- Drip icon font-->
    <link rel="stylesheet" href="/public/vendor/dripicons/webfont.css" type="text/css">
    <!-- Google fonts - Roboto -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:400,500,700">
    <!-- jQuery Circle-->
    <link rel="stylesheet" href="/public/css/grasp_mobile_progress_circle-1.0.0.min.css" type="text/css">
    <!-- Custom Scrollbar-->
    <link rel="stylesheet" href="/public/vendor/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.css" type="text/css">
    <!-- virtual keybord stylesheet-->
    <link rel="stylesheet" href="/public/vendor/keyboard/css/keyboard.css" type="text/css">
    <!-- date range stylesheet-->
    <link rel="stylesheet" href="/public/vendor/daterange/css/daterangepicker.min.css" type="text/css">
    <!-- table sorter stylesheet-->
    <link rel="stylesheet" type="text/css" href="/public/vendor/datatable/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/fixedheader/3.1.6/css/fixedHeader.bootstrap.min.css">
    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="/public/css/style.default.css" id="theme-stylesheet" type="text/css">
    <link rel="stylesheet" href="/public/css/dropzone.css">
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/printjs/print.min.css">
    <!-- Tweaks for older IEs--><!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->

    <script type="text/javascript" src="/public/vendor/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="/public/vendor/jquery/jquery-ui.min.js"></script>
    <script type="text/javascript" src="/public/vendor/jquery/bootstrap-datepicker.min.js"></script>
    <script type="text/javascript" src="/public/vendor/jquery/jquery.timepicker.min.js"></script>
    <script type="text/javascript" src="/public/vendor/popper.js/umd/popper.min.js"></script>
    <script type="text/javascript" src="/public/vendor/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="/public/vendor/bootstrap-toggle/js/bootstrap-toggle.min.js"></script>
    <script type="text/javascript" src="/public/vendor/bootstrap/js/bootstrap-select.min.js"></script>
    <script type="text/javascript" src="/public/vendor/keyboard/js/jquery.keyboard.js"></script>
    <script type="text/javascript" src="/public/vendor/keyboard/js/jquery.keyboard.extension-autocomplete.js"></script>
    <script type="text/javascript" src="/public/js/grasp_mobile_progress_circle-1.0.0.min.js"></script>
    <script type="text/javascript" src="/public/vendor/jquery.cookie/jquery.cookie.js"></script>
    <script type="text/javascript" src="/public/vendor/chart.js/Chart.min.js"></script>
    <script type="text/javascript" src="/public/vendor/jquery-validation/jquery.validate.min.js"></script>
    <script type="text/javascript" src="/public/vendor/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js"></script>
    <script type="text/javascript" src="/public/js/charts-custom.js"></script>
    <script type="text/javascript" src="/public/js/front.js"></script>
    <script type="text/javascript" src="/public/vendor/daterange/js/moment.min.js"></script>
    <script type="text/javascript" src="/public/vendor/daterange/js/knockout-3.4.2.js"></script>
    <script type="text/javascript" src="/public/vendor/daterange/js/daterangepicker.min.js"></script>
    <script type="text/javascript" src="/public/vendor/tinymce/js/tinymce/tinymce.min.js"></script>
    <script type="text/javascript" src="/public/js/dropzone.js"></script>

    <!-- table sorter js-->
    <script type="text/javascript" src="/public/vendor/datatable/pdfmake.min.js"></script>
    <script type="text/javascript" src="/public/vendor/datatable/vfs_fonts.js"></script>
    <script type="text/javascript" src="/public/vendor/datatable/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="/public/vendor/datatable/dataTables.bootstrap4.min.js"></script>
    <script type="text/javascript" src="/public/vendor/datatable/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="/public/vendor/datatable/buttons.bootstrap4.min.js"></script>
    <script type="text/javascript" src="/public/vendor/datatable/buttons.colVis.min.js"></script>
    <script type="text/javascript" src="/public/vendor/datatable/buttons.html5.min.js"></script>
    <script type="text/javascript" src="/public/vendor/datatable/buttons.print.min.js"></script>

    <script type="text/javascript" src="/public/vendor/datatable/sum().js"></script>
    <script type="text/javascript" src="/public/vendor/datatable/dataTables.checkboxes.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/fixedheader/3.1.6/js/dataTables.fixedHeader.min.js">
    </script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js">
    </script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.3/js/responsive.bootstrap.min.js">
    </script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.28/dist/sweetalert2.all.min.js"></script>

    <!-- Custom stylesheet - for your changes -->
    <link rel="stylesheet" href="/public/css/custom-{{ $general_setting->theme }}" type="text/css" id="custom-style">
    <script type="text/javascript" src="/public/printjs/print.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.14.1/moment.min.js"></script>
    <style>
        .noselect {
            pointer-events: none;
            cursor: default;
            -webkit-touch-callout: none;
            /* iOS Safari */
            -webkit-user-select: none;
            /* Safari */
            -khtml-user-select: none;
            /* Konqueror HTML */
            -moz-user-select: none;
            /* Old versions of Firefox */
            -ms-user-select: none;
            /* Internet Explorer/Edge */
            user-select: none;
            /* Non-prefixed version, currently
                                        supported by Chrome, Edge, Opera and Firefox */
        }
    </style>
</head>

<body onload="myFunction()">
    <div id="loader"></div>
    <div class="pos-page">

        <div style="display:none;" id="content" class="animate-bottom">
            @yield('content')
        </div>
        <?php use App\Http\Controllers\BillerController;
        if(Auth::user()->role_id > 2 && Auth::user()->biller_id){
            $lims_warehouse_authorizates = BillerController::warehouseAuthorizate(Auth::user()->biller_id);
        }else{
            $lims_warehouse_authorizates = \App\Warehouse::where('is_active', true)->get();
        }
        ?>
        <!-- warehouse product modal -->
        <div id="warehouse-pro-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true" class="modal fade text-left">
            <div role="document" class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Product Report') }}</h5>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                    </div>
                    <div class="modal-body">
                        <p class="italic">
                            <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                        </p>
                        {!! Form::open(['route' => 'report.product', 'method' => 'post']) !!}
                        <div class="form-group">
                            <label>{{ trans('file.Warehouse') }} *</label>
                            <select name="warehouse_id" class="selectpicker form-control" required
                                data-live-search="true" id="warehouse-id" data-live-search-style="begins"
                                title="Seleccione almacen...">
                                @foreach ($lims_warehouse_authorizates as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <input type="hidden" name="start_date" value="{{ date('Y-m-d', strtotime(' -7 day')) }}" />
                        <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}" />
                        <input type="hidden" name="con_stock" value="true" />

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">{{ trans('file.submit') }}</button>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- warehouse sales modal -->
        <div id="warehouse-sale-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true" class="modal fade text-left">
            <div role="document" class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Sale Report') }}</h5>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                    </div>
                    <div class="modal-body">
                        <p class="italic">
                            <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                        </p>
                        {!! Form::open(['route' => 'report.sale', 'method' => 'post']) !!}
                        <div class="form-group">
                            <label>{{ trans('file.Warehouse') }} *</label>
                            <select name="warehouse_id" class="selectpicker form-control" required
                                data-live-search="true" id="warehouse-id" data-live-search-style="begins"
                                title="Seleccione almacen...">
                                @foreach ($lims_warehouse_authorizates as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <input type="hidden" name="start_date" value="{{ date('Y-m-d', strtotime(' -7 day')) }}" />
                        <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}" />

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">{{ trans('file.submit') }}</button>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- user modal -->
        <div id="user-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
            class="modal fade text-left">
            <div role="document" class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.User Report') }}</h5>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                    </div>
                    <div class="modal-body">
                        <p class="italic">
                            <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                        </p>
                        {!! Form::open(['route' => 'report.user', 'method' => 'post']) !!}
                        <?php
                        $lims_user_list = DB::table('users')
                            ->where('is_active', true)
                            ->get();
                        ?>
                        <div class="form-group">
                            <label>{{ trans('file.User') }} *</label>
                            <select name="user_id" class="selectpicker form-control" required data-live-search="true"
                                id="user-id" data-live-search-style="begins" title="Select user...">
                                @foreach ($lims_user_list as $user)
                                    <option value="{{ $user->id }}">
                                        {{ $user->name . ' (' . $user->phone . ')' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <input type="hidden" name="start_date" value="{{ date('Y-m-d', strtotime(' -7 day')) }}" />
                        <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}" />

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">{{ trans('file.submit') }}</button>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- customer modal -->
        <div id="customer-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true" class="modal fade text-left">
            <div role="document" class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Customer Report') }}</h5>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                    </div>
                    <div class="modal-body">
                        <p class="italic">
                            <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                        </p>
                        {!! Form::open(['route' => 'report.customer', 'method' => 'post']) !!}
                        <?php
                        $lims_customer_list = DB::table('customers')
                            ->where('is_active', true)
                            ->limit(10)
                            ->get();
                        ?>
                        <div class="form-group">
                            <label>{{ trans('file.customer') }} *</label>
                            <select name="customer_id" class="selectpicker form-control" required
                                data-live-search="true" id="customer-id" data-live-search-style="begins"
                                title="Select customer...">
                                @foreach ($lims_customer_list as $customer)
                                    <option value="{{ $customer->id }}">
                                        {{ $customer->name . ' (' . $customer->phone_number . ')' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <input type="hidden" name="start_date" value="{{ date('Y-m-d', strtotime(' -7 day')) }}" />
                        <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}" />

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">{{ trans('file.submit') }}</button>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- supplier modal -->
        <div id="supplier-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true" class="modal fade text-left">
            <div role="document" class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Supplier Report') }}</h5>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                    </div>
                    <div class="modal-body">
                        <p class="italic">
                            <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                        </p>
                        {!! Form::open(['route' => 'report.supplier', 'method' => 'post']) !!}
                        <?php
                        $lims_supplier_list = DB::table('suppliers')
                            ->where('is_active', true)
                            ->get();
                        ?>
                        <div class="form-group">
                            <label>{{ trans('file.Supplier') }} *</label>
                            <select name="supplier_id" class="selectpicker form-control" required
                                data-live-search="true" id="supplier-id" data-live-search-style="begins"
                                title="Select Supplier...">
                                @foreach ($lims_supplier_list as $supplier)
                                    <option value="{{ $supplier->id }}">
                                        {{ $supplier->name . ' (' . $supplier->phone_number . ')' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <input type="hidden" name="start_date" value="{{ date('Y-m-d', strtotime(' -7 day')) }}" />
                        <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}" />

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">{{ trans('file.submit') }}</button>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @yield('scripts')
    <script type="text/javascript">
        function myFunction() {
            setTimeout(showPage, 300);
        }

        function showPage() {
            document.getElementById("loader").style.display = "none";
            document.getElementById("content").style.display = "flex";
            $("#lims_productcodeSearch").focus();
        }

        $("div.alert").delay(10000).slideUp(3000);
        $("a#add-expense").click(function(e) {
            e.preventDefault();
            $('#expense-modal').modal();
        });

        $("a#add-account").click(function(e) {
            e.preventDefault();
            $('#account-modal').modal();
        });

        $("a#account-statement").click(function(e) {
            e.preventDefault();
            $('#account-statement-modal').modal();
        });

        $("a#profitLoss-link").click(function(e) {
            e.preventDefault();
            $("#profitLoss-report-form").submit();
        });

        $("a#productfinish-report-link").click(function(e) {
            e.preventDefault();
            $("#product-finish-report-form").submit();
        });

        $("a#report-link").click(function(e) {
            e.preventDefault();
            $("#product-report-form").submit();
        });

        $("a#purchase-report-link").click(function(e) {
            e.preventDefault();
            $("#purchase-report-form").submit();
        });

        $("a#sale-report-link").click(function(e) {
            e.preventDefault();
            $("#sale-report-form").submit();
        });

        $("a#sale-biller-report-link").click(function(e) {
            e.preventDefault();
            $("#sale-biller-report-form").submit();
        });

        $("a#sale-renueve-report-link").click(function(e) {
            e.preventDefault();
            $("#sale-renueve-report-form").submit();
        });

        $("a#sale-item-report-link").click(function(e) {
            e.preventDefault();
            $("#sale-item-report-form").submit();
        });

        $("a#sale-customer-report-link").click(function(e) {
            e.preventDefault();
            $("#sale-customer-report-form").submit();
        });

        $("a#sale-product-report-link").click(function(e) {
            e.preventDefault();
            $("#sale-product-report-form").submit();
        });
        $("a#sale-courtesy-report-link").click(function(e) {
            e.preventDefault();
            $("#sale-courtesy-report-form").submit();
        });
        $("a#servicemp-report-link").click(function(e) {
            e.preventDefault();
            $("#servicemp-report-form").submit();
        });
        $("a#servicempcom-report-link").click(function(e) {
            e.preventDefault();
            $("#servicempcom-report-form").submit();
        });
        $("a#payment-report-link").click(function(e) {
            e.preventDefault();
            $("#payment-report-form").submit();
        });

        $("a#account-mov-report-link").click(function(e) {
            e.preventDefault();
            $("#account-mov-report-form").submit();
        });

        $("a#warehouse-report-link").click(function(e) {
            e.preventDefault();
            $('#warehouse-modal').modal();
        });

        $("a#warehouse-pro-report-link").click(function(e) {
            e.preventDefault();
            $('#warehouse-pro-modal').modal();
        });

        $("a#warehouse-sale-report-link").click(function(e) {
            e.preventDefault();
            $('#warehouse-sale-modal').modal();
        });

        $("a#user-report-link").click(function(e) {
            e.preventDefault();
            $('#user-modal').modal();
        });

        $("a#customer-report-link").click(function(e) {
            e.preventDefault();
            $('#customer-modal').modal();
        });

        $("a#supplier-report-link").click(function(e) {
            e.preventDefault();
            $('#supplier-modal').modal();
        });

        $("a#due-report-link").click(function(e) {
            e.preventDefault();
            $("#due-report-form").submit();
        });

        $(".daterangepicker-field").daterangepicker({
            callback: function(startDate, endDate, period) {
                var start_date = startDate.format('YYYY-MM-DD');
                var end_date = endDate.format('YYYY-MM-DD');
                var title = start_date + ' To ' + end_date;
                $(this).val(title);
                $('#account-statement-modal input[name="start_date"]').val(start_date);
                $('#account-statement-modal input[name="end_date"]').val(end_date);
            }
        });

        $('select').selectpicker({
            style: 'btn-link',
        });
    </script>
</body>

</html>
