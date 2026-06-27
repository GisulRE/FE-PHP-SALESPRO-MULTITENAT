<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" type="image/png" href="{{ url('logo', $general_setting->site_logo) }}" />
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
    <link rel="stylesheet" href="/public/vendor/fonts-google/webfont.css?family=Nunito:400,500,700">
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
    <link rel="stylesheet" type="text/css" href="/public/vendor/datatable/buttons.dataTables.min.css">

    <link rel="stylesheet" type="text/css" href="/public/vendor/bootstrap/css/fixedHeader.bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/public/vendor/bootstrap/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="/public/css/style.default.css" id="theme-stylesheet" type="text/css">
    <link rel="stylesheet" href="/public/css/dropzone.css">
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/printjs/print.min.css">
    <!-- Tweaks for older IEs-->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->

    <!-- Custom stylesheet - for your changes-->
    <link rel="stylesheet" href="/public/css/custom-{{ $general_setting->theme }}" type="text/css" id="custom-style">
    <script type="text/javascript" src="/public/printjs/print.min.js"></script>
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

<body>

    <div id="scripts-cont">
        @include('layout.partials.scripts-boostrap')
    </div>

    <div id="loader" style="display: none"></div>
    <!-- Side Navbar -->
    @include('layout.partials.side_navbar')

    <!-- navbar-->
    @include('layout.partials.navbar')

    <?php use App\Http\Controllers\BillerController;
    if (Auth::user()->role_id > 2 && Auth::user()->biller_id) {
        $lims_warehouse_authorizates = BillerController::warehouseAuthorizate(Auth::user()->biller_id);
    } else {
        $lims_warehouse_authorizates = \App\Warehouse::where('is_active', true)->get();
    }
    ?>
    <div class="page">

        <!-- expense modal -->
        <div id="expense-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
            class="modal fade text-left">
            <div role="document" class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Add Expense') }}</h5>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                    </div>
                    <div class="modal-body">
                        <p class="italic">
                            <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                        </p>
                        {!! Form::open(['route' => 'expenses.store', 'method' => 'post']) !!}
                        @php
                            $lims_expense_category_list = \App\ExpenseCategory::where('is_active', true)->get();
                            $lims_account_list = \App\Account::where('is_active', true)->get();
                        @endphp
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>{{ trans('file.Expense Category') }} *</label>
                                <select name="expense_category_id" class="selectpicker form-control" required
                                    data-live-search="true" data-live-search-style="begins"
                                    title="Select Expense Category...">
                                    @foreach ($lims_expense_category_list as $expense_category)
                                        <option value="{{ $expense_category->id }}">
                                            {{ $expense_category->name . ' (' . $expense_category->code . ')' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{ trans('file.Warehouse') }} *</label>
                                <select name="warehouse_id" class="selectpicker form-control" required
                                    data-live-search="true" data-live-search-style="begins"
                                    title="Select Warehouse...">
                                    @foreach ($lims_warehouse_authorizates as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{ trans('file.Amount') }} *</label>
                                <input type="number" name="amount" step="any" required class="form-control">
                            </div>
                            <div class="col-md-6 form-group">
                                <label> {{ trans('file.Account') }}</label>
                                <select class="form-control selectpicker" name="account_id">
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
                            <div class="col-md-6 form-group">
                                <label>{{ trans('file.Date') }} <small>(Opcional si quiere guardar con otra
                                        fecha)</small></label>
                                <input type="date" name="created_at" class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>{{ trans('file.Note') }}</label>
                            <textarea name="note" rows="3" class="form-control"></textarea>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">{{ trans('file.submit') }}</button>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- account modal -->
        <div id="account-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true" class="modal fade text-left">
            <div role="document" class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Add Account') }}</h5>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                    </div>
                    <div class="modal-body">
                        <p class="italic">
                            <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                        </p>
                        {!! Form::open(['route' => 'accounts.store', 'method' => 'post']) !!}
                        <div class="form-group">
                            <label>{{ trans('file.Account No') }} *</label>
                            <input type="text" name="account_no" required class="form-control">
                        </div>
                        <div class="form-group">
                            <label>{{ trans('file.name') }} *</label>
                            <input type="text" name="name" required class="form-control">
                        </div>
                        <div class="form-group">
                            <label>{{ trans('file.Initial Balance') }}</label>
                            <input type="number" name="initial_balance" step="any" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Tipo Cuenta</strong> </label>
                            <select name="type" class="form-control selectpicker">
                                <option value="1">Compras/Ventas</option>
                                <option value="2">Productos</option>
                            </select>
                        </div>
                        <div class="form-group" style="display: none">
                            <label>Metodos de Pagos</label>
                            <select class="selectpicker form-control" name="methodpaynew[]" id="methodpaynew"
                                title="Seleccione..." multiple></select>
                        </div>
                        <div class="form-group">
                            <label>{{ trans('file.Note') }}</label>
                            <textarea name="note" rows="3" class="form-control"></textarea>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">{{ trans('file.submit') }}</button>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- account statement modal -->
        <div id="account-statement-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true" class="modal fade text-left">
            <div role="document" class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Account Statement') }}</h5>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                    </div>
                    <div class="modal-body">
                        <p class="italic">
                            <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                        </p>
                        {!! Form::open(['route' => 'accounts.statement', 'method' => 'post']) !!}
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label> {{ trans('file.Account') }}</label>
                                <select class="form-control selectpicker" name="account_id">
                                    @foreach ($lims_account_list as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }}
                                            [{{ $account->account_no }}]</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label> {{ trans('file.Type') }}</label>
                                <select class="form-control selectpicker" name="type">
                                    <option value="0">{{ trans('file.All') }}</option>
                                    <option value="1">{{ trans('file.Debit') }}</option>
                                    <option value="2">{{ trans('file.Credit') }}</option>
                                </select>
                            </div>
                            <div class="col-md-12 form-group">
                                <label>{{ trans('file.Choose Your Date') }}</label>
                                <div class="input-group">
                                    <input type="text" class="daterangepicker-field form-control" required />
                                    <input type="hidden" name="start_date" />
                                    <input type="hidden" name="end_date" />
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">{{ trans('file.submit') }}</button>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>


        <!-- warehouse modal -->
        <div id="warehouse-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true" class="modal fade text-left">
            <div role="document" class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Warehouse Report') }}</h5>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                    </div>
                    <div class="modal-body">
                        <p class="italic">
                            <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                        </p>
                        {!! Form::open(['route' => 'report.warehouse', 'method' => 'post']) !!}
                        <?php
                        ?>
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

        <div id="content">
            @include('layout.layout')
        </div>

        <footer class="main-footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-12">
                        <p>&copy; {{ $general_setting->site_title }} | {{ trans('file.Developed') }}
                            {{ trans('file.By') }} <a href="http://www.gisul.com.bo" class="external">Gisul
                                S.R.L.</a></p>
                    </div>
                </div>
            </div>
        </footer>

    </div>
    @yield('scripts')
    <script type="text/javascript">
        if (localStorage.getItem('url') && localStorage.getItem('url') != location.href) {
            localStorage.setItem('clicked', 1);
            setPage(localStorage.getItem('url'));
        }

        if ($(window).outerWidth() > 1199) {
            $('nav.side-navbar').removeClass('shrink');
        }

        // function myFunction() {
        //     setTimeout(showPage, 150);
        // }

        // function showPage() {
        //     document.getElementById("loader").style.display = "none";
        //     document.getElementById("content").style.display = "block";
        // }

        $("div.alert").delay(3000).slideUp(750);

        function confirmDelete() {
            if (confirm("Esta seguro de querer eliminar esto?")) {
                return true;
            }
            return false;
        }

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

        $('.selectpicker').selectpicker({
            style: 'btn-link',
        });

        function openDialogNew() {
            var url = "accounts/"
            url = url.concat("create");
            $('#methodpaynew').empty();
            $.get(url, function(data) {
                addOptions("methodpaynew", data['list_method']);
                $('.selectpicker').selectpicker('refresh');
            });
        }
        // Rutina para agregar opciones a un <select>
        function addOptions(domElement, array) {
            var select = document.getElementById(domElement);

            for (value in array) {
                var option = document.createElement("option");
                option.text = array[value].name;
                option.value = array[value].id;
                select.add(option);
            }

        }
        $(document).ready(function() {

            $('.stopReload').click(function(e) {
                e.preventDefault();

                let url = $(this).attr('href');
                if (window.location.href != url) {
                    localStorage.setItem('clicked', 1);

                    setPage(url);

                }
            });
        });
    </script>
</body>

</html>
