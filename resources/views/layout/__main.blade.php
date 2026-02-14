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
    <link rel="stylesheet" href="/public/vendor/bootstrap-toggle/css/bootstrap-toggle.min.css"
        type="text/css">
    <link rel="stylesheet" href="/public/vendor/bootstrap/css/bootstrap-datepicker.min.css"
        type="text/css">
    <link rel="stylesheet" href="/public/vendor/jquery-timepicker/jquery.timepicker.min.css"
        type="text/css">
    <link rel="stylesheet" href="/public/vendor/bootstrap/css/awesome-bootstrap-checkbox.css"
        type="text/css">
    <link rel="stylesheet" href="/public/vendor/bootstrap/css/bootstrap-select.min.css"
        type="text/css">
    <!-- Font Awesome CSS-->
    <link rel="stylesheet" href="/public/vendor/font-awesome/css/font-awesome.min.css"
        type="text/css">
    <!-- Drip icon font-->
    <link rel="stylesheet" href="/public/vendor/dripicons/webfont.css" type="text/css">
    <!-- Google fonts - Roboto -->
    <link rel="stylesheet"
        href="/public/vendor/fonts-google/webfont.css?family=Nunito:400,500,700">
    <!-- jQuery Circle-->
    <link rel="stylesheet" href="/public/css/grasp_mobile_progress_circle-1.0.0.min.css"
        type="text/css">
    <!-- Custom Scrollbar-->
    <link rel="stylesheet"
        href="/public/vendor/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.css"
        type="text/css">
    <!-- virtual keybord stylesheet-->
    <link rel="stylesheet" href="/public/vendor/keyboard/css/keyboard.css" type="text/css">
    <!-- date range stylesheet-->
    <link rel="stylesheet" href="/public/vendor/daterange/css/daterangepicker.min.css"
        type="text/css">
    <!-- table sorter stylesheet-->
    <link rel="stylesheet" type="text/css"
        href="/public/vendor/datatable/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" type="text/css"
        href="/public/vendor/datatable/buttons.dataTables.min.css">

    <link rel="stylesheet" type="text/css"
        href="/public/vendor/bootstrap/css/fixedHeader.bootstrap.min.css">
    <link rel="stylesheet" type="text/css"
        href="/public/vendor/bootstrap/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="/public/css/style.default.css" id="theme-stylesheet"
        type="text/css">
    <link rel="stylesheet" href="/public/css/dropzone.css">
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/printjs/print.min.css">
    <!-- Tweaks for older IEs-->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->

    <script type="text/javascript" src="/public/vendor/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="/public/vendor/jquery/jquery-ui.min.js"></script>
    <script type="text/javascript"
        src="/public/vendor/jquery/bootstrap-datepicker.min.js"></script>
    <script type="text/javascript" src="/public/vendor/jquery/jquery.timepicker.min.js"></script>
    <script type="text/javascript" src="/public/vendor/popper.js/umd/popper.min.js"></script>
    <script type="text/javascript" src="/public/vendor/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript"
        src="/public/vendor/bootstrap-toggle/js/bootstrap-toggle.min.js"></script>
    <script type="text/javascript"
        src="/public/vendor/bootstrap/js/bootstrap-select.min.js"></script>
    <script type="text/javascript" src="/public/vendor/keyboard/js/jquery.keyboard.js"></script>
    <script type="text/javascript"
        src="/public/vendor/keyboard/js/jquery.keyboard.extension-autocomplete.js"></script>
    <script type="text/javascript"
        src="/public/js/grasp_mobile_progress_circle-1.0.0.min.js"></script>
    <script type="text/javascript" src="/public/vendor/jquery.cookie/jquery.cookie.js"></script>
    <script type="text/javascript"
        src="/public/vendor/bootstrap-input-spinner/src/bootstrap-input-spinner.js"></script>
    <script type="text/javascript" src="/public/vendor/chart.js/Chart.min.js"></script>
    <script type="text/javascript"
        src="/public/vendor/jquery-validation/jquery.validate.min.js"></script>
    <script type="text/javascript"
        src="/public/vendor/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js"></script>
    <script type="text/javascript" src="/public/js/charts-custom.js"></script>
    <script type="text/javascript" src="/public/js/front.js"></script>
    <script type="text/javascript" src="/public/vendor/daterange/js/moment.min.js"></script>
    <script type="text/javascript" src="/public/vendor/daterange/js/knockout-3.4.2.js"></script>
    <script type="text/javascript"
        src="/public/vendor/daterange/js/daterangepicker.min.js"></script>
    <script type="text/javascript"
        src="/public/vendor/tinymce/js/tinymce/tinymce.min.js"></script>
    <script type="text/javascript" src="/public/js/dropzone.js"></script>

    <!-- table sorter js-->
    <script type="text/javascript" src="/public/vendor/datatable/pdfmake.min.js"></script>
    <script type="text/javascript" src="/public/vendor/datatable/vfs_fonts.js"></script>
    <script type="text/javascript"
        src="/public/vendor/datatable/jquery.dataTables.min.js"></script>
    <script type="text/javascript"
        src="/public/vendor/datatable/dataTables.bootstrap4.min.js"></script>
    <script type="text/javascript"
        src="/public/vendor/datatable/dataTables.buttons.min.js"></script>
    <script type="text/javascript"
        src="/public/vendor/datatable/buttons.bootstrap4.min.js"></script>
    <script type="text/javascript" src="/public/vendor/datatable/buttons.colVis.min.js"></script>
    <script type="text/javascript" src="/public/vendor/datatable/buttons.html5.min.js"></script>
    <script type="text/javascript" src="/public/vendor/datatable/buttons.print.min.js"></script>
    <script type="text/javascript" src="/public/vendor/datatable/jszip.min.js"></script>

    <script type="text/javascript" src="/public/vendor/datatable/sum().js"></script>
    <script type="text/javascript"
        src="/public/vendor/datatable/dataTables.checkboxes.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/fixedheader/3.1.6/js/dataTables.fixedHeader.min.js">
    </script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js">
    </script>
    <script type="text/javascript"
        src="/public/vendor/datatable/responsive.bootstrap.min.js"></script>
    <script type="text/javascript" src="/public/js/sweetalert.min.js"></script>
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <!-- Custom stylesheet - for your changes-->
    <link rel="stylesheet" href="/public/css/custom-{{ $general_setting->theme }}" type="text/css"
        id="custom-style">
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

<body onload="myFunction()">
    <div id="loader"></div>
    <!-- Side Navbar -->
    <nav class="side-navbar">
        <div class="side-navbar-wrapper">
            <!-- Sidebar Header    -->
            <!-- Sidebar Navigation Menus-->
            <div class="main-menu">
                <ul id="side-main-menu" class="side-menu list-unstyled">
                    <li><a href="{{ url('/') }}"> <i
                                class="dripicons-meter"></i><span>{{ __('file.dashboard') }}</span></a></li>

                    <?php
$role = DB::table('roles')->find(Auth::user()->role_id);
// Resolve blocked modules for current role
$blocked_modules = [];
if ($role && isset($role->blocked_modules) && $role->blocked_modules) {
    $decoded = json_decode($role->blocked_modules, true);
    $blocked_modules = is_array($decoded) ? $decoded : [];
}
$category_permission_active = DB::table('permissions')
    ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
    ->where([['permissions.name', 'category'], ['role_id', $role->id]])
    ->first();
$index_permission = DB::table('permissions')->where('name', 'products-index')->first();
$index_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $index_permission->id], ['role_id', $role->id]])
    ->first();

$print_barcode = DB::table('permissions')->where('name', 'print_barcode')->first();
$print_barcode_active = DB::table('role_has_permissions')
    ->where([['permission_id', $print_barcode->id], ['role_id', $role->id]])
    ->first();

$stock_count = DB::table('permissions')->where('name', 'stock_count')->first();
$stock_count_active = DB::table('role_has_permissions')
    ->where([['permission_id', $stock_count->id], ['role_id', $role->id]])
    ->first();

$adjustment = DB::table('permissions')->where('name', 'adjustment')->first();
$adjustment_active = DB::table('role_has_permissions')
    ->where([['permission_id', $adjustment->id], ['role_id', $role->id]])
    ->first();
$adjustment_qty_add = DB::table('permissions')->where('name', 'qty_adjustment-add')->first();
$adjustment_add_active = DB::table('role_has_permissions')
    ->where([['permission_id', $adjustment_qty_add->id], ['role_id', $role->id]])
    ->first();
                    ?>

                    @if (
                            $category_permission_active ||
                            $index_permission_active ||
                            $print_barcode_active ||
                            $stock_count_active ||
                            $adjustment_active ||
                            $adjustment_add_active
                        )
                        <li><a href="#product" aria-expanded="false" data-toggle="collapse"> <i
                                    class="dripicons-list"></i><span>{{ __('file.product') }}</span></a>
                            <ul id="product" class="collapse list-unstyled ">
                                @if ($category_permission_active)
                                    <li id="category-menu"><a href="{{ route('category.index') }}">{{ __('file.category') }}</a>
                                    </li>
                                @endif
                                @if ($index_permission_active)
                                                        <li id="product-list-menu"><a
                                                                href="{{ route('products.index') }}">{{ __('file.product_list') }}</a></li>
                                                        <?php
                                    $add_permission = DB::table('permissions')->where('name', 'products-add')->first();
                                    $add_permission_active = DB::table('role_has_permissions')
                                        ->where([['permission_id', $add_permission->id], ['role_id', $role->id]])
                                        ->first();
                                                                                                                        ?>
                                                        @if ($add_permission_active)
                                                            <li id="product-create-menu"><a
                                                                    href="{{ route('products.create') }}">{{ __('file.add_product') }}</a>
                                                            </li>
                                                        @endif
                                @endif
                                @if ($print_barcode_active)
                                    <li id="printBarcode-menu"><a
                                            href="{{ route('product.printBarcode') }}">{{ __('file.print_barcode') }}</a>
                                    </li>
                                @endif
                                @if ($adjustment_active)
                                    <li id="adjustment-list-menu"><a
                                            href="{{ route('qty_adjustment.index') }}">{{ trans('file.Adjustment List') }}</a>
                                    </li>
                                @endif
                                @if ($adjustment_add_active)
                                    <li id="adjustment-create-menu"><a
                                            href="{{ route('qty_adjustment.create') }}">{{ trans('file.Add Adjustment') }}</a>
                                    </li>
                                @endif
                                @if ($stock_count_active)
                                    <li id="stock-count-menu"><a
                                            href="{{ route('stock-count.index') }}">{{ trans('file.Stock Count') }}</a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif
                    <?php
$index_permission = DB::table('permissions')->where('name', 'purchases-index')->first();
$index_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $index_permission->id], ['role_id', $role->id]])
    ->first();
                    ?>
                    @if ($index_permission_active)
                                        <li><a href="#purchase" aria-expanded="false" data-toggle="collapse"> <i
                                                    class="dripicons-card"></i><span>{{ trans('file.Purchase') }}</span></a>
                                            <ul id="purchase" class="collapse list-unstyled ">
                                                <li id="purchase-list-menu"><a
                                                        href="{{ route('purchases.index') }}">{{ trans('file.Purchase List') }}</a>
                                                </li>
                                                <?php
                        $add_permission = DB::table('permissions')->where('name', 'purchases-add')->first();
                        $add_permission_active = DB::table('role_has_permissions')
                            ->where([['permission_id', $add_permission->id], ['role_id', $role->id]])
                            ->first();
                                                                                            ?>
                                                @if ($add_permission_active)
                                                    <li id="purchase-create-menu"><a
                                                            href="{{ route('purchases.create') }}">{{ trans('file.Add Purchase') }}</a>
                                                    </li>
                                                    <li id="purchase-import-menu"><a
                                                            href="{{ url('purchases/purchase_by_csv') }}">{{ trans('file.Import Purchase By CSV') }}</a>
                                                    </li>
                                                @endif
                                            </ul>
                                        </li>
                    @endif
                    <?php
$sale_index_permission = DB::table('permissions')->where('name', 'sales-index')->first();
$sale_index_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $sale_index_permission->id], ['role_id', $role->id]])
    ->first();

$presale_index_permission = DB::table('permissions')->where('name', 'presale-create')->first();
$presale_index_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $presale_index_permission->id], ['role_id', $role->id]])
    ->first();

$gift_card_permission = DB::table('permissions')->where('name', 'gift_card')->first();
$gift_card_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $gift_card_permission->id], ['role_id', $role->id]])
    ->first();

$coupon_permission = DB::table('permissions')->where('name', 'coupon')->first();
$coupon_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $coupon_permission->id], ['role_id', $role->id]])
    ->first();

$delivery_permission_active = DB::table('permissions')
    ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
    ->where([['permissions.name', 'delivery'], ['role_id', $role->id]])
    ->first();

$sale_add_permission = DB::table('permissions')->where('name', 'sales-add')->first();
$sale_add_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $sale_add_permission->id], ['role_id', $role->id]])
    ->first();

$attentionshift = DB::table('permissions')->where('name', 'attentionshift')->first();
$attentionshift_active = DB::table('role_has_permissions')
    ->where([['permission_id', $attentionshift->id], ['role_id', $role->id]])
    ->first();
$contigenciasiat = DB::table('permissions')->where('name', 'contingencia_siat')->first();
$contigenciasiat_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $contigenciasiat->id], ['role_id', $role->id]])
    ->first();
$facturamasivasiat = DB::table('permissions')->where('name', 'facturamasiva_siat')->first();
$facturamasivasiat_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $facturamasivasiat->id], ['role_id', $role->id]])
    ->first();
$sale_pendingdue = DB::table('permissions')->where('name', 'sale_pendingdue')->first();
$sale_pendingdue_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $sale_pendingdue->id], ['role_id', $role->id]])
    ->first();
$sale_booksale = DB::table('permissions')->where('name', 'sales-list-booksale')->first();
$sale_booksale_active = DB::table('role_has_permissions')
    ->where([['permission_id', $sale_booksale->id], ['role_id', $role->id]])
    ->first();
                    ?>
                    @if (
                                            $sale_index_permission_active ||
                                            $gift_card_permission_active ||
                                            $coupon_permission_active ||
                                            $delivery_permission_active ||
                                            $presale_index_permission_active ||
                                            $sale_booksale_active
                                        )
                                        <li><a href="#sale" aria-expanded="false" data-toggle="collapse"> <i
                                                    class="dripicons-cart"></i><span>{{ trans('file.Sale') }}</span></a>
                                            <ul id="sale" class="collapse list-unstyled ">
                                                @if ($sale_index_permission_active || $sale_booksale_active)
                                                    @if ($sale_index_permission_active)
                                                        <li id="sale-list-menu">
                                                            <a href="{{ route('sales.index') }}">{{ trans('file.Sale List') }}</a>
                                                        </li>
                                                    @endif
                                                    @if ($sale_booksale_active)
                                                        <li id="sale-book-menu"><a href="{{ route('sale.libro-ventas') }}">Libro de
                                                                Ventas </a>
                                                        </li>
                                                    @endif
                                                    @if ($sale_pendingdue_permission_active)
                                                        <li id="salerec-list-menu"><a
                                                                href="{{ route('receivable.index') }}">{{ trans('file.Sale List') }}
                                                                Por Pagar</a>
                                                        </li>
                                                    @endif
                                                    @if ($sale_add_permission_active)
                                                        @if (!in_array('pos', $blocked_modules))
                                                            <li><a href="{{ route('sale.pos') }}">POS</a></li>
                                                        @endif
                                                        <li id="sale-import-menu"><a
                                                                href="{{ url('sales/sale_by_csv') }}">{{ trans('file.Import Sale By CSV') }}</a>
                                                        </li>
                                                    @endif
                                                @endif
                                                @if ($presale_index_permission_active)
                                                    <li><a href="{{ route('presale.prepos') }}">{{ trans('file.Pre Sale') }}</a>
                                                    </li>
                                                @endif
                                                @if ($attentionshift_active)
                                                    <li id="shift-menu"><a
                                                            href="{{ route('attentionshift.index') }}">{{ trans('file.Attention Shift') }}
                                                            Servicios</a>
                                                    </li>
                                                @endif
                                                <?php
                        $reservations_permission = DB::table('permissions')->where('name', 'reservations-index')->first();
                        $reservations_permission_active = $reservations_permission ? DB::table('role_has_permissions')
                            ->where([['permission_id', $reservations_permission->id], ['role_id', $role->id]])->first() : null;
                                                    ?>
                                                @if ($reservations_permission_active)
                                                    <li id="reservation-menu"><a href="{{ route('reservations.index') }}">Reservas</a></li>
                                                @endif
                                                @if ($gift_card_permission_active)
                                                    <li id="gift-card-menu"><a
                                                            href="{{ route('gift_cards.index') }}">{{ trans('file.Gift Card List') }}</a>
                                                    </li>
                                                @endif
                                                @if ($coupon_permission_active)
                                                    <li id="coupon-menu"><a
                                                            href="{{ route('coupons.index') }}">{{ trans('file.Coupon List') }}</a>
                                                    </li>
                                                @endif
                                                @if ($delivery_permission_active)
                                                    <li id="delivery-menu"><a
                                                            href="{{ route('delivery.index') }}">{{ trans('file.Delivery List') }}</a>
                                                    </li>
                                                @endif
                                                @if ($contigenciasiat_permission_active)
                                                    <li id="contingencia-menu"><a
                                                            href="{{ route('contingencia.index') }}">{{ trans('file.Contingency') }}</a>
                                                    </li>
                                                @endif
                                                {{-- inico Factura Masiva --}}
                                                @if ($facturamasivasiat_permission_active)
                                                    <li id="factura-masiva-menu"><a href="{{ route('factura-masiva.index') }}">Factura
                                                            Masiva</a>
                                                    </li>
                                                @endif
                                                {{-- fin --}}

                                                {{-- inico Factura Masiva Manchaco --}}
                                                <!-- <li id="factura-masiva-manchaco-menu"><a href="{{ route('factura-old-masiva.index') }}">Factura
                                                                    Masiva Manchaco</a>
                                                            </li>-->
                                                {{-- fin --}}
                                            </ul>
                                        </li>
                    @endif

                    <?php
$index_permission = DB::table('permissions')->where('name', 'expenses-index')->first();
$index_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $index_permission->id], ['role_id', $role->id]])
    ->first();
                    ?>
                    @if ($index_permission_active)
                                        <li><a href="#expense" aria-expanded="false" data-toggle="collapse"> <i
                                                    class="dripicons-wallet"></i><span>{{ trans('file.Expense') }}</span></a>
                                            <ul id="expense" class="collapse list-unstyled ">
                                                <li id="exp-cat-menu"><a
                                                        href="{{ route('expense_categories.index') }}">{{ trans('file.Expense Category') }}</a>
                                                </li>
                                                <li id="exp-list-menu"><a
                                                        href="{{ route('expenses.index') }}">{{ trans('file.Expense List') }}</a>
                                                </li>
                                                <?php
                        $add_permission = DB::table('permissions')->where('name', 'expenses-add')->first();
                        $add_permission_active = DB::table('role_has_permissions')
                            ->where([['permission_id', $add_permission->id], ['role_id', $role->id]])
                            ->first();
                                                                                            ?>
                                                @if ($add_permission_active)
                                                    <li><a id="add-expense" href=""> {{ trans('file.Add Expense') }}</a></li>
                                                @endif
                                            </ul>
                                        </li>
                    @endif
                    {{-- Indice SIAT --}}
                    <?php
$siat_permission = DB::table('permissions')->where('name', 'module_siat')->first();
$siat_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $siat_permission->id], ['role_id', $role->id]])
    ->first();
                    ?>
                    @if ($siat_permission_active && !in_array('panel_siat', $blocked_modules))
                        @include('layout.partials.aside-siat')
                    @endif
                    {{-- Fin SIAT --}}

                    <?php
$index_permission = DB::table('permissions')->where('name', 'quotes-index')->first();
$index_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $index_permission->id], ['role_id', $role->id]])
    ->first();
                    ?>
                    @if ($index_permission_active)
                                        <li><a href="#quotation" aria-expanded="false" data-toggle="collapse"> <i
                                                    class="dripicons-document"></i><span>{{ trans('file.Quotation') }}</span></a>
                                            <ul id="quotation" class="collapse list-unstyled ">
                                                <li id="quotation-list-menu"><a
                                                        href="{{ route('quotations.index') }}">{{ trans('file.Quotation List') }}</a>
                                                </li>
                                                <?php
                        $add_permission = DB::table('permissions')->where('name', 'quotes-add')->first();
                        $add_permission_active = DB::table('role_has_permissions')
                            ->where([['permission_id', $add_permission->id], ['role_id', $role->id]])
                            ->first();
                                                                                            ?>
                                                @if ($add_permission_active)
                                                    <li id="quotation-create-menu"><a
                                                            href="{{ route('quotations.create') }}">{{ trans('file.Add Quotation') }}</a>
                                                    </li>
                                                @endif
                                            </ul>
                                        </li>
                    @endif
                    <?php
$index_permission = DB::table('permissions')->where('name', 'transfers-index')->first();
$index_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $index_permission->id], ['role_id', $role->id]])
    ->first();
                    ?>
                    @if ($index_permission_active && !in_array('transfers', $blocked_modules))
                                        <li><a href="#transfer" aria-expanded="false" data-toggle="collapse"> <i
                                                    class="dripicons-export"></i><span>{{ trans('file.Transfer') }}</span></a>
                                            <ul id="transfer" class="collapse list-unstyled ">
                                                <li id="transfer-list-menu"><a
                                                        href="{{ route('transfers.index') }}">{{ trans('file.Transfer List') }}</a>
                                                </li>
                                                <?php
                        $add_permission = DB::table('permissions')->where('name', 'transfers-add')->first();
                        $add_permission_active = DB::table('role_has_permissions')
                            ->where([['permission_id', $add_permission->id], ['role_id', $role->id]])
                            ->first();
                                                                                            ?>
                                                @if ($add_permission_active)
                                                    <li id="transfer-create-menu"><a
                                                            href="{{ route('transfers.create') }}">{{ trans('file.Add Transfer') }}</a>
                                                    </li>
                                                    <li id="transfer-import-menu"><a
                                                            href="{{ url('transfers/transfer_by_csv') }}">{{ trans('file.Import Transfer By CSV') }}</a>
                                                    </li>
                                                @endif
                                            </ul>
                                        </li>
                    @endif

                    <?php
$sale_return_index_permission = DB::table('permissions')->where('name', 'returns-index')->first();

$sale_return_index_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $sale_return_index_permission->id], ['role_id', $role->id]])
    ->first();

$purchase_return_index_permission = DB::table('permissions')->where('name', 'purchase-return-index')->first();

$purchase_return_index_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $purchase_return_index_permission->id], ['role_id', $role->id]])
    ->first();
                    ?>
                    @if ($sale_return_index_permission_active || $purchase_return_index_permission_active)
                        <li><a href="#return" aria-expanded="false" data-toggle="collapse"> <i
                                    class="dripicons-archive"></i><span>{{ trans('file.return') }}</span></a>
                            <ul id="return" class="collapse list-unstyled ">
                                @if ($sale_return_index_permission_active)
                                    <li id="sale-return-menu"><a
                                            href="{{ route('return-sale.index') }}">{{ trans('file.Sale') }}</a></li>
                                @endif
                                @if ($purchase_return_index_permission_active)
                                    <li id="purchase-return-menu"><a
                                            href="{{ route('return-purchase.index') }}">{{ trans('file.Purchase') }}</a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif
                    <?php
$index_permission = DB::table('permissions')->where('name', 'account-index')->first();
$index_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $index_permission->id], ['role_id', $role->id]])
    ->first();

$money_transfer_permission = DB::table('permissions')->where('name', 'money-transfer')->first();
$money_transfer_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $money_transfer_permission->id], ['role_id', $role->id]])
    ->first();

$balance_sheet_permission = DB::table('permissions')->where('name', 'balance-sheet')->first();
$balance_sheet_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $balance_sheet_permission->id], ['role_id', $role->id]])
    ->first();
$balance_sheet_account_permission = DB::table('permissions')->where('name', 'balance-sheet-account')->first();
$balance_sheet_account_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $balance_sheet_account_permission->id], ['role_id', $role->id]])
    ->first();
$close_balance_account_permission = DB::table('permissions')->where('name', 'close-balance-account')->first();
$close_balance_account_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $close_balance_account_permission->id], ['role_id', $role->id]])
    ->first();
$account_statement_permission = DB::table('permissions')->where('name', 'account-statement')->first();
$account_statement_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $account_statement_permission->id], ['role_id', $role->id]])
    ->first();
$adjaccount_statement_permission = DB::table('permissions')->where('name', 'adjustment-account-index')->first();
$adjaccount_statement_permission_active = $adjaccount_statement_permission
    ? DB::table('role_has_permissions')
        ->where([['permission_id', $adjaccount_statement_permission->id], ['role_id', $role->id]])
        ->first()
    : null;
                    
                    ?>
                    @if (
                            $index_permission_active ||
                            $balance_sheet_account_permission_active ||
                            $balance_sheet_permission_active ||
                            $account_statement_permission_active ||
                            $close_balance_account_permission
                        )
                        <li class=""><a href="#account" aria-expanded="false" data-toggle="collapse"> <i
                                    class="dripicons-briefcase"></i><span>{{ trans('file.Accounting') }}</span></a>
                            <ul id="account" class="collapse list-unstyled ">
                                @if ($index_permission_active)
                                    <li id="account-list-menu"><a
                                            href="{{ route('accounts.index') }}">{{ trans('file.Account List') }}</a>
                                    </li>
                                    <li><a id="add-account" href=""
                                            onclick="openDialogNew()">{{ trans('file.Add Account') }}</a></li>
                                @endif
                                @if ($money_transfer_permission_active)
                                    <li id="money-transfer-menu"><a
                                            href="{{ route('money-transfers.index') }}">{{ trans('file.Money Transfer') }}</a>
                                    </li>
                                @endif
                                @if ($balance_sheet_permission_active)
                                    <li id="balance-sheet-menu"><a
                                            href="{{ route('accounts.balancesheet') }}">{{ trans('file.Balance Sheet') }}</a>
                                    </li>
                                @endif
                                @if ($adjaccount_statement_permission_active && !in_array('adjustment-account', $blocked_modules) && !in_array('qty_adjustment', $blocked_modules))
                                    <li id="adjustment_account-list-menu"><a
                                            href="{{ route('adjustment_account.index') }}">{{ trans('file.Adjustment List') }}</a>
                                    </li>
                                @endif
                                @if ($adjaccount_statement_permission_active && !in_array('adjustment-account', $blocked_modules) && !in_array('qty_adjustment', $blocked_modules))
                                    <li id="adjustment_account-create-menu"><a
                                            href="{{ route('adjustment_account.create') }}">{{ trans('file.Add Adjustment') }}</a>
                                    </li>
                                @endif
                                @if ($balance_sheet_account_permission_active)
                                                        <li id="account-mov-report-menu">
                                                            {!! Form::open([
                                        'route' => 'accounts.balancesheetaccount',
                                        'method' => 'post',
                                        'id' => 'account-mov-report-form',
                                    ]) !!}
                                                            <input type="hidden" name="start_date" value="{{ date('Y-m-d') }}" />
                                                            <input type="hidden" name="end_date" value="{{ date('Y-m-d', strtotime(' 1 day')) }}" />
                                                            <input type="hidden" name="account_id" value="0" />
                                                            <a id="account-mov-report-link" href="">Arqueo Caja</a>
                                                            {!! Form::close() !!}
                                                        </li>
                                @endif
                                @if ($close_balance_account_permission_active)
                                    <li id="cashier_account-list-menu"><a
                                            href="{{ route('cashier.index') }}">{{ trans('file.Cashier') }}</a></li>
                                @endif
                                @if ($account_statement_permission_active)
                                    <li id="account-statement-menu"><a id="account-statement"
                                            href="">{{ trans('file.Account Statement') }}</a></li>
                                @endif
                                @if ($account_statement_permission_active)
                                    <li id="account-resumen-menu"><a id="account-resumen"
                                            href="{{ url('report/resumen_account/' . date('Y-m') . '-' . '01' . '/' . date('Y-m-d')) . '/0/-1' }}">Reporte
                                            Cuentas Contable</a></li>
                                @endif
                            </ul>
                        </li>
                    @endif
                    <?php
$hrm_menu = DB::table('permissions')->where('name', 'hrm-menu')->first();
$hrm_menu_active = DB::table('role_has_permissions')
    ->where([['permission_id', $hrm_menu->id], ['role_id', $role->id]])
    ->first();
$department = DB::table('permissions')->where('name', 'department')->first();
$department_active = DB::table('role_has_permissions')
    ->where([['permission_id', $department->id], ['role_id', $role->id]])
    ->first();
$index_employee = DB::table('permissions')->where('name', 'employees-index')->first();
$index_employee_active = DB::table('role_has_permissions')
    ->where([['permission_id', $index_employee->id], ['role_id', $role->id]])
    ->first();
$attendance = DB::table('permissions')->where('name', 'attendance')->first();
$attendance_active = DB::table('role_has_permissions')
    ->where([['permission_id', $attendance->id], ['role_id', $role->id]])
    ->first();
$payroll = DB::table('permissions')->where('name', 'payroll')->first();
$payroll_active = DB::table('role_has_permissions')
    ->where([['permission_id', $payroll->id], ['role_id', $role->id]])
    ->first();
                    ?>
                    @if ($hrm_menu_active)
                        <li class=""><a href="#hrm" aria-expanded="false" data-toggle="collapse"> <i
                                    class="dripicons-user-group"></i><span>HRM</span></a>
                            <ul id="hrm" class="collapse list-unstyled ">
                                @if ($department_active)
                                    <li id="dept-menu"><a
                                            href="{{ route('departments.index') }}">{{ trans('file.Department') }}</a>
                                    </li>
                                @endif
                                @if ($index_employee_active && !in_array('employee', $blocked_modules))
                                    <li id="employee-menu"><a
                                            href="{{ route('employees.index') }}">{{ trans('file.Employee') }}</a>
                                    </li>
                                @endif
                                @if ($attendance_active)
                                    <li id="attendance-menu"><a
                                            href="{{ route('attendance.index') }}">{{ trans('file.Attendance') }}</a>
                                    </li>
                                @endif
                                @if ($payroll_active)
                                    <li id="payroll-menu"><a href="{{ route('payroll.index') }}">{{ trans('file.Payroll') }}</a>
                                    </li>
                                @endif
                                <li id="holiday-menu"><a
                                        href="{{ route('holidays.index') }}">{{ trans('file.Holiday') }}</a></li>

                            </ul>
                        </li>
                    @endif
                    <?php
$user_index_permission_active = DB::table('permissions')
    ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
    ->where([['permissions.name', 'users-index'], ['role_id', $role->id]])
    ->first();

$customer_index_permission = DB::table('permissions')->where('name', 'customers-index')->first();

$customer_index_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $customer_index_permission->id], ['role_id', $role->id]])
    ->first();

$biller_index_permission = DB::table('permissions')->where('name', 'billers-index')->first();

$biller_index_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $biller_index_permission->id], ['role_id', $role->id]])
    ->first();

$supplier_index_permission = DB::table('permissions')->where('name', 'suppliers-index')->first();

$supplier_index_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $supplier_index_permission->id], ['role_id', $role->id]])
    ->first();
                    ?>
                    @if (
                            $user_index_permission_active ||
                            $customer_index_permission_active ||
                            $biller_index_permission_active ||
                            $supplier_index_permission_active
                        )
                        <li><a href="#people" aria-expanded="false" data-toggle="collapse"> <i
                                    class="dripicons-user"></i><span>{{ trans('file.People') }}</span></a>
                            <ul id="people" class="collapse list-unstyled ">

                                @if ($user_index_permission_active && !in_array('user', $blocked_modules))
                                                    <li id="user-list-menu"><a
                                                            href="{{ route('user.index') }}">{{ trans('file.User List') }}</a></li>
                                                    <?php        $user_add_permission_active = DB::table('permissions')
                                    ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                                    ->where([['permissions.name', 'users-add'], ['role_id', $role->id]])
                                    ->first();
                                                                                                            ?>
                                                    @if ($user_add_permission_active)
                                                        <li id="user-create-menu"><a
                                                                href="{{ route('user.create') }}">{{ trans('file.Add User') }}</a>
                                                        </li>
                                                    @endif
                                @endif

                                @if ($customer_index_permission_active && !in_array('customer', $blocked_modules))
                                                        <li id="customer-list-menu"><a
                                                                href="{{ route('customer.index') }}">{{ trans('file.Customer List') }}</a>
                                                        </li>
                                                        <?php
                                    $customer_add_permission = DB::table('permissions')->where('name', 'customers-add')->first();
                                    $customer_add_permission_active = DB::table('role_has_permissions')
                                        ->where([['permission_id', $customer_add_permission->id], ['role_id', $role->id]])
                                        ->first();
                                                                                                                        ?>
                                                        @if ($customer_add_permission_active)
                                                            <li id="customer-create-menu"><a
                                                                    href="{{ route('customer.create') }}">{{ trans('file.Add Customer') }}</a>
                                                            </li>
                                                        @endif
                                @endif

                                @if ($biller_index_permission_active && !in_array('biller', $blocked_modules))
                                                        <li id="biller-list-menu"><a
                                                                href="{{ route('biller.index') }}">{{ trans('file.Biller List') }}</a>
                                                        </li>
                                                        <?php
                                    $biller_add_permission = DB::table('permissions')->where('name', 'billers-add')->first();
                                    $biller_add_permission_active = DB::table('role_has_permissions')
                                        ->where([['permission_id', $biller_add_permission->id], ['role_id', $role->id]])
                                        ->first();
                                                                                                                        ?>
                                                        @if ($biller_add_permission_active)
                                                            <li id="biller-create-menu"><a
                                                                    href="{{ route('biller.create') }}">{{ trans('file.Add Biller') }}</a>
                                                            </li>
                                                        @endif
                                @endif

                                @if ($supplier_index_permission_active && !in_array('supplier', $blocked_modules))
                                                        <li id="supplier-list-menu"><a
                                                                href="{{ route('supplier.index') }}">{{ trans('file.Supplier List') }}</a>
                                                        </li>
                                                        <?php
                                    $supplier_add_permission = DB::table('permissions')->where('name', 'suppliers-add')->first();
                                    $supplier_add_permission_active = DB::table('role_has_permissions')
                                        ->where([['permission_id', $supplier_add_permission->id], ['role_id', $role->id]])
                                        ->first();
                                                                                                                        ?>
                                                        @if ($supplier_add_permission_active)
                                                            <li id="supplier-create-menu"><a
                                                                    href="{{ route('supplier.create') }}">{{ trans('file.Add Supplier') }}</a>
                                                            </li>
                                                        @endif
                                @endif
                            </ul>
                        </li>
                    @endif

                    <?php
$profit_loss_active = DB::table('permissions')
    ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
    ->where([['permissions.name', 'profit-loss'], ['role_id', $role->id]])
    ->first();
$best_seller_active = DB::table('permissions')
    ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
    ->where([['permissions.name', 'best-seller'], ['role_id', $role->id]])
    ->first();
$warehouse_report_active = DB::table('permissions')
    ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
    ->where([['permissions.name', 'warehouse-report'], ['role_id', $role->id]])
    ->first();
$warehouse_stock_report_active = DB::table('permissions')
    ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
    ->where([['permissions.name', 'warehouse-stock-report'], ['role_id', $role->id]])
    ->first();
$product_report_active = DB::table('permissions')
    ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
    ->where([['permissions.name', 'product-report'], ['role_id', $role->id]])
    ->first();
$productdetail_report_active = DB::table('permissions')
    ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
    ->where([['permissions.name', 'product-detail-report'], ['role_id', $role->id]])
    ->first();
$daily_sale_active = DB::table('permissions')
    ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
    ->where([['permissions.name', 'daily-sale'], ['role_id', $role->id]])
    ->first();
$monthly_sale_active = DB::table('permissions')
    ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
    ->where([['permissions.name', 'monthly-sale'], ['role_id', $role->id]])
    ->first();
$daily_purchase_active = DB::table('permissions')
    ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
    ->where([['permissions.name', 'daily-purchase'], ['role_id', $role->id]])
    ->first();
$monthly_purchase_active = DB::table('permissions')
    ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
    ->where([['permissions.name', 'monthly-purchase'], ['role_id', $role->id]])
    ->first();
$purchase_report_active = DB::table('permissions')
    ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
    ->where([['permissions.name', 'purchase-report'], ['role_id', $role->id]])
    ->first();
$sale_report_active = DB::table('permissions')
    ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
    ->where([['permissions.name', 'sale-report'], ['role_id', $role->id]])
    ->first();
$salebiller_report_active = DB::table('permissions')
    ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
    ->where([['permissions.name', 'salebiller-report'], ['role_id', $role->id]])
    ->first();
$sale_detail_report_active = DB::table('permissions')
    ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
    ->where([['permissions.name', 'saledetail-report'], ['role_id', $role->id]])
    ->first();
$salecustomer_report_active = DB::table('permissions')
    ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
    ->where([['permissions.name', 'salecustomer-report'], ['role_id', $role->id]])
    ->first();
$payment_report_active = DB::table('permissions')
    ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
    ->where([['permissions.name', 'payment-report'], ['role_id', $role->id]])
    ->first();
$product_qty_alert_active = DB::table('permissions')
    ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
    ->where([['permissions.name', 'product-qty-alert'], ['role_id', $role->id]])
    ->first();
$user_report_active = DB::table('permissions')
    ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
    ->where([['permissions.name', 'user-report'], ['role_id', $role->id]])
    ->first();

$customer_report_active = DB::table('permissions')
    ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
    ->where([['permissions.name', 'customer-report'], ['role_id', $role->id]])
    ->first();
$supplier_report_active = DB::table('permissions')
    ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
    ->where([['permissions.name', 'supplier-report'], ['role_id', $role->id]])
    ->first();
$due_report_active = DB::table('permissions')
    ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
    ->where([['permissions.name', 'due-report'], ['role_id', $role->id]])
    ->first();
$onlycommision_report_active = DB::table('permissions')
    ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
    ->where([['permissions.name', 'only-commision-report'], ['role_id', $role->id]])
    ->first();
$servicecommision_report_active = DB::table('permissions')
    ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
    ->where([['permissions.name', 'service-commission-report'], ['role_id', $role->id]])
    ->first();
                    ?>
                    @if (
                            $profit_loss_active ||
                            $best_seller_active ||
                            $warehouse_report_active ||
                            $warehouse_stock_report_active ||
                            $product_report_active ||
                            $productdetail_report_active ||
                            $daily_sale_active ||
                            $monthly_sale_active ||
                            $daily_purchase_active ||
                            $monthly_purchase_active ||
                            $purchase_report_active ||
                            $sale_report_active ||
                            $salebiller_report_active ||
                            $sale_detail_report_active ||
                            $payment_report_active ||
                            $product_qty_alert_active ||
                            $user_report_active ||
                            $customer_report_active ||
                            $supplier_report_active ||
                            $due_report_active
                        )
                        <li><a href="#report" aria-expanded="false" data-toggle="collapse"> <i
                                    class="dripicons-document-remove"></i><span>{{ trans('file.Reports') }}</span></a>
                            <ul id="report" class="collapse list-unstyled ">
                                @if ($best_seller_active)
                                    <li id="general-report-menu">
                                        <a
                                            href="{{ url('report/general_report/' . date('Y-m-d') . '/' . date('Y-m-d')) . '/0/0' }}">Reporte
                                            General</a>
                                    </li>
                                @endif
                                @if ($best_seller_active)
                                    <li id="general-util-report-menu">
                                        <a
                                            href="{{ url('report/generalutil_report/' . date('Y-m') . '-' . '01' . '/' . date('Y-m-d')) . '/0/0' }}">Reporte
                                            General + Utilidades</a>
                                    </li>
                                @endif
                                @if ($profit_loss_active)
                                    <li id="profit-loss-report-menu">
                                        {!! Form::open(['route' => 'report.profitLoss', 'method' => 'post', 'id' => 'profitLoss-report-form']) !!}
                                        <input type="hidden" name="start_date" value="{{ date('Y-m') . '-' . '01' }}" />
                                        <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}" />
                                        <a id="profitLoss-link" href="">{{ trans('file.Summary Report') }}</a>
                                        {!! Form::close() !!}
                                    </li>
                                @endif
                                @if ($best_seller_active)
                                    <li id="best-seller-report-menu">
                                        <a href="{{ url('report/best_seller') }}">{{ trans('file.Best Seller') }}</a>
                                    </li>
                                @endif
                                @if ($productdetail_report_active)
                                    <li id="product-detail-report-menu">
                                        <a href="{{ url('report/product_detail_report') }}">Informe Producto Por
                                            Precios</a>
                                    </li>
                                @endif
                                @if ($product_report_active)
                                    <li id="product-report-menu">
                                        <a id="warehouse-pro-report-link" href="">{{ trans('file.Product Report') }}</a>
                                    </li>
                                @endif
                                @if ($product_report_active)
                                    <li id="productfinish-report-menu">
                                        {!! Form::open(['route' => 'report.productFinish', 'method' => 'post', 'id' => 'product-finish-report-form']) !!}
                                        <input type="hidden" name="start_date"
                                            value="{{ date('Y-m-d', strtotime(' -7 day')) }}" />
                                        <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}" />
                                        <input type="hidden" name="product_id" value="0" />
                                        <a id="productfinish-report-link" href="">{{ trans('file.Product Report Finish') }}</a>
                                        {!! Form::close() !!}
                                    </li>
                                @endif
                                @if ($daily_sale_active)
                                    <li id="daily-sale-report-menu">
                                        <a
                                            href="{{ url('report/daily_sale/' . date('Y') . '/' . date('m')) }}">{{ trans('file.Daily Sale') }}</a>
                                    </li>
                                @endif
                                @if ($monthly_sale_active)
                                    <li id="monthly-sale-report-menu">
                                        <a
                                            href="{{ url('report/monthly_sale/' . date('Y')) }}">{{ trans('file.Monthly Sale') }}</a>
                                    </li>
                                @endif
                                @if ($daily_purchase_active)
                                    <li id="daily-purchase-report-menu">
                                        <a
                                            href="{{ url('report/daily_purchase/' . date('Y') . '/' . date('m')) }}">{{ trans('file.Daily Purchase') }}</a>
                                    </li>
                                @endif
                                @if ($monthly_purchase_active)
                                    <li id="monthly-purchase-report-menu">
                                        <a
                                            href="{{ url('report/monthly_purchase/' . date('Y')) }}">{{ trans('file.Monthly Purchase') }}</a>
                                    </li>
                                @endif
                                @if ($sale_report_active)
                                    <li id="sale-report-menu">
                                        <a id="warehouse-sale-report-link" href="">{{ trans('file.Sale Report') }}</a>
                                    </li>
                                @endif
                                @if ($salebiller_report_active)
                                    <li id="sale-biller-report-menu">
                                        {!! Form::open(['route' => 'report.saleBiller', 'method' => 'post', 'id' => 'sale-biller-report-form']) !!}
                                        <input type="hidden" name="start_date"
                                            value="{{ date('Y-m-d', strtotime(' -7 day')) }}" />
                                        <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}" />
                                        <input type="hidden" name="warehouse_id" value="0" />
                                        <input type="hidden" name="biller_id" value="0" />
                                        <a id="sale-biller-report-link" href="">{{ trans('file.Sale Biller Report') }}</a>
                                        {!! Form::close() !!}
                                    </li>
                                @endif
                                @if ($sale_detail_report_active)
                                    <li id="sale-item-report-menu">
                                        {!! Form::open(['route' => 'report.saleByProduct', 'method' => 'post', 'id' => 'sale-item-report-form']) !!}
                                        <input type="hidden" name="start_date"
                                            value="{{ date('Y-m-d', strtotime(' -7 day')) }}" />
                                        <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}" />
                                        <input type="hidden" name="warehouse_id" value="0" />
                                        <input type="hidden" name="biller_id" value="0" />
                                        <a id="sale-item-report-link" href="">{{ trans('file.Sale Items Report') }}</a>
                                        {!! Form::close() !!}
                                    </li>
                                @endif
                                @if ($salecustomer_report_active)
                                    <li id="sale-customer-report-menu">
                                        {!! Form::open(['route' => 'report.saleCustomer', 'method' => 'post', 'id' => 'sale-customer-report-form']) !!}
                                        <input type="hidden" name="start_date"
                                            value="{{ date('Y-m-d', strtotime(' -7 day')) }}" />
                                        <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}" />
                                        <input type="hidden" name="warehouse_id" value="0" />
                                        <input type="hidden" name="customer_id" value="0" />
                                        <a id="sale-customer-report-link" href="">{{ trans('file.Sale Customer Report') }}</a>
                                        {!! Form::close() !!}
                                    </li>
                                @endif
                                @if ($salecustomer_report_active)
                                    <li id="sale-product-report-menu">
                                        {!! Form::open(['route' => 'report.saleProduct', 'method' => 'post', 'id' => 'sale-product-report-form']) !!}
                                        <input type="hidden" name="start_date" value="{{ date('Y-m-d') }}" />
                                        <input type="hidden" name="end_date" value="{{ date('Y-m-d', strtotime(' 1 day')) }}" />
                                        <input type="hidden" name="category_id" value="0" />
                                        <a id="sale-product-report-link" href="">{{ trans('file.Sale Product Report') }}</a>
                                        {!! Form::close() !!}
                                    </li>
                                @endif
                                @if ($salecustomer_report_active)
                                    <li id="sale-courtesy-report-menu">
                                        {!! Form::open(['route' => 'report.saleCourtesy', 'method' => 'post', 'id' => 'sale-courtesy-report-form']) !!}
                                        <input type="hidden" name="start_date" value="{{ date('Y-m-d') }}" />
                                        <input type="hidden" name="end_date" value="{{ date('Y-m-d', strtotime(' 1 day')) }}" />
                                        <input type="hidden" name="category_id" value="0" />
                                        <a id="sale-courtesy-report-link" href="">{{ trans('file.Sale Courtesy Report') }}</a>
                                        {!! Form::close() !!}
                                    </li>
                                @endif
                                @if ($servicecommision_report_active)
                                                        <li id="sale-servicempcom-report-menu">
                                                            {!! Form::open([
                                        'route' => 'report.employeeComissionService',
                                        'method' => 'post',
                                        'id' => 'servicempcom-report-form',
                                    ]) !!}
                                                            <input type="hidden" name="start_date" value="{{ date('Y-m-d') }}" />
                                                            <input type="hidden" name="end_date" value="{{ date('Y-m-d', strtotime(' 1 day')) }}" />
                                                            <input type="hidden" name="employee_id" value="0" />
                                                            <a id="servicempcom-report-link"
                                                                href="">{{ trans('file.Service Commission Employee Report') }}</a>
                                                            {!! Form::close() !!}
                                                        </li>
                                @endif
                                @if ($onlycommision_report_active)
                                    <li id="sale-servicemp-report-menu">
                                        {!! Form::open(['route' => 'report.employeeService', 'method' => 'post', 'id' => 'servicemp-report-form']) !!}
                                        <input type="hidden" name="start_date" value="{{ date('Y-m-d') }}" />
                                        <input type="hidden" name="end_date" value="{{ date('Y-m-d', strtotime(' 1 day')) }}" />
                                        <input type="hidden" name="employee_id" value="0" />
                                        <a id="servicemp-report-link" href="">{{ trans('file.Service Employee Report') }}</a>
                                        {!! Form::close() !!}
                                    </li>
                                @endif
                                @if ($payment_report_active)
                                    <li id="payment-report-menu">
                                        {!! Form::open(['route' => 'report.paymentByDate', 'method' => 'post', 'id' => 'payment-report-form']) !!}
                                        <input type="hidden" name="start_date"
                                            value="{{ date('Y-m-d', strtotime(' -7 day')) }}" />
                                        <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}" />
                                        <input type="hidden" name="kind_payment" value="1" />
                                        <a id="payment-report-link" href="">{{ trans('file.Payment Report') }}</a>
                                        {!! Form::close() !!}
                                    </li>
                                @endif
                                @if ($purchase_report_active)
                                    <li id="purchase-report-menu">
                                        {!! Form::open(['route' => 'report.purchase', 'method' => 'post', 'id' => 'purchase-report-form']) !!}
                                        <input type="hidden" name="start_date"
                                            value="{{ date('Y-m-d', strtotime(' -7 day')) }}" />
                                        <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}" />
                                        <input type="hidden" name="warehouse_id" value="0" />
                                        <a id="purchase-report-link" href="">{{ trans('file.Purchase Report') }}</a>
                                        {!! Form::close() !!}
                                    </li>
                                @endif
                                @if ($warehouse_report_active)
                                    <li id="warehouse-report-menu">
                                        <a id="warehouse-report-link" href="">{{ trans('file.Warehouse Report') }}</a>
                                    </li>
                                @endif
                                @if ($warehouse_stock_report_active)
                                    <li id="warehouse-stock-report-menu">
                                        <a
                                            href="{{ route('report.warehouseStock') }}">{{ trans('file.Warehouse Stock Chart') }}</a>
                                    </li>
                                @endif
                                @if ($product_qty_alert_active)
                                    <li id="qtyAlert-report-menu">
                                        <a href="{{ route('report.qtyAlert') }}">{{ trans('file.Product Quantity Alert') }}</a>
                                    </li>
                                @endif
                                @if ($product_qty_alert_active)
                                    <li id="loteAlert-report-menu">
                                        <a
                                            href="{{ route('report.alertExpiration', ['filter' => 0, 'days' => $general_setting->alert_expiration]) }}">{{ trans('file.Alert of Lotes') }}</a>
                                    </li>
                                @endif
                                @if ($product_qty_alert_active)
                                    <li id="proLotes-report-menu">
                                        <a
                                            href="{{ route('report.productsLotes') }}">{{ trans('file.Products With Lotes') }}</a>
                                    </li>
                                @endif
                                @if ($user_report_active)
                                    <li id="user-report-menu">
                                        <a id="user-report-link" href="">{{ trans('file.User Report') }}</a>
                                    </li>
                                @endif
                                @if ($customer_report_active)
                                    <li id="customer-report-menu">
                                        <a id="customer-report-link" href="">{{ trans('file.Customer Report') }}</a>
                                    </li>
                                @endif
                                @if ($supplier_report_active)
                                    <li id="supplier-report-menu">
                                        <a id="supplier-report-link" href="">{{ trans('file.Supplier Report') }}</a>
                                    </li>
                                @endif
                                @if ($due_report_active)
                                    <li id="due-report-menu">
                                        {!! Form::open(['route' => 'report.dueByDate', 'method' => 'post', 'id' => 'due-report-form']) !!}
                                        <input type="hidden" name="start_date"
                                            value="{{ date('Y-m-d', strtotime(' -7 day')) }}" />
                                        <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}" />
                                        <a id="due-report-link" href="">{{ trans('file.Due Report') }}</a>
                                        {!! Form::close() !!}
                                    </li>
                                @endif
                                <li id="report-invoicesale-menu"><a id="account-resumen"
                                        href="{{ url('report/sales_report/' . date('Y-m') . '-' . '01' . '/' . date('Y-m-d')) . '/-1/0' }}">Informe
                                        Factura/Venta</a></li>
                            </ul>
                        </li>
                    @endif

                    <li><a href="#setting" aria-expanded="false" data-toggle="collapse"> <i
                                class="dripicons-gear"></i><span>{{ trans('file.settings') }}</span></a>
                        <ul id="setting" class="collapse list-unstyled ">
                            <?php

$warehouse_permission = DB::table('permissions')->where('name', 'warehouse')->first();
$warehouse_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $warehouse_permission->id], ['role_id', $role->id]])
    ->first();

$customer_group_permission = DB::table('permissions')->where('name', 'customer_group')->first();
$customer_group_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $customer_group_permission->id], ['role_id', $role->id]])
    ->first();

$brand_permission = DB::table('permissions')->where('name', 'brand')->first();
$brand_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $brand_permission->id], ['role_id', $role->id]])
    ->first();

$unit_permission = DB::table('permissions')->where('name', 'unit')->first();
$unit_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $unit_permission->id], ['role_id', $role->id]])
    ->first();

$tax_permission = DB::table('permissions')->where('name', 'tax')->first();
$tax_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $tax_permission->id], ['role_id', $role->id]])
    ->first();

$general_setting_permission = DB::table('permissions')->where('name', 'general_setting')->first();
$general_setting_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $general_setting_permission->id], ['role_id', $role->id]])
    ->first();

$mail_setting_permission = DB::table('permissions')->where('name', 'mail_setting')->first();
$mail_setting_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $mail_setting_permission->id], ['role_id', $role->id]])
    ->first();

$sms_setting_permission = DB::table('permissions')->where('name', 'sms_setting')->first();
$sms_setting_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $sms_setting_permission->id], ['role_id', $role->id]])
    ->first();

$create_sms_permission = DB::table('permissions')->where('name', 'create_sms')->first();
$create_sms_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $create_sms_permission->id], ['role_id', $role->id]])
    ->first();

$pos_setting_permission = DB::table('permissions')->where('name', 'pos_setting')->first();
$pos_setting_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $pos_setting_permission->id], ['role_id', $role->id]])
    ->first();

$hrm_setting_permission = DB::table('permissions')->where('name', 'hrm_setting')->first();
$hrm_setting_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $hrm_setting_permission->id], ['role_id', $role->id]])
    ->first();

$qr_setting_permission = DB::table('permissions')->where('name', 'module_qr')->first();
$qr_setting_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $qr_setting_permission->id], ['role_id', $role->id]])
    ->first();
                            ?>
                            @if ($role->id <= 2)
                                <li id="role-menu"><a
                                        href="{{ route('role.index') }}">{{ trans('file.Role Permission') }}</a>
                                </li>
                            @endif
                            @if ($warehouse_permission_active)
                                <li id="warehouse-menu"><a
                                        href="{{ route('warehouse.index') }}">{{ trans('file.Warehouse') }}</a>
                                </li>
                            @endif
                            @if ($customer_group_permission_active)
                                <li id="customer-group-menu"><a
                                        href="{{ route('customer_group.index') }}">{{ trans('file.Customer Group') }}</a>
                                </li>
                            @endif
                            @if ($brand_permission_active)
                                <li id="brand-menu"><a href="{{ route('brand.index') }}">{{ trans('file.Brand') }}</a></li>
                            @endif
                            @if ($unit_permission_active)
                                <li id="unit-menu"><a href="{{ route('unit.index') }}">{{ trans('file.Unit') }}</a></li>
                            @endif
                            @if ($tax_permission_active)
                                <li id="tax-menu"><a href="{{ route('tax.index') }}">{{ trans('file.Tax') }}</a>
                                </li>
                            @endif
                            <li id="user-menu"><a
                                    href="{{ route('user.profile', ['id' => Auth::id()]) }}">{{ trans('file.User Profile') }}</a>
                            </li>
                            @if ($create_sms_permission_active)
                                <li id="create-sms-menu"><a
                                        href="{{ route('setting.createSms') }}">{{ trans('file.Create SMS') }}</a>
                                </li>
                            @endif
                            @if ($general_setting_permission_active)
                                <li id="general-setting-menu"><a
                                        href="{{ route('setting.general') }}">{{ trans('file.General Setting') }}</a>
                                </li>
                            @endif
                            @if ($mail_setting_permission_active)
                                <li id="mail-setting-menu"><a
                                        href="{{ route('setting.mail') }}">{{ trans('file.Mail Setting') }}</a>
                                </li>
                            @endif
                            @if ($sms_setting_permission_active)
                                <li id="sms-setting-menu"><a
                                        href="{{ route('setting.sms') }}">{{ trans('file.SMS Setting') }}</a></li>
                            @endif
                            @if ($pos_setting_permission_active)
                                <li id="pos-setting-menu"><a href="{{ route('setting.pos') }}">POS
                                        {{ trans('file.settings') }}</a></li>
                            @endif
                            @if ($hrm_setting_permission_active)
                                <li id="hrm-setting-menu"><a href="{{ route('setting.hrm') }}">
                                        {{ trans('file.HRM Setting') }}</a></li>
                            @endif
                            @if ($pos_setting_permission_active)
                                <li id="printer-menu"><a
                                        href="{{ route('printer.index') }}">{{ trans('file.Printers') }}</a></li>
                            @endif
                            @if ($qr_setting_permission_active)
                                <li id="qr-setting-menu"><a href="{{ route('setting.qrsimple') }}">
                                        {{ trans('file.QR Setting') }}</a></li>
                            @endif
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- navbar-->
    <header class="header">
        <nav class="navbar">
            <div class="container-fluid">
                <div class="navbar-holder d-flex align-items-center justify-content-between">
                    <ul class="nav-menu list-unstyled d-flex flex-md-row align-items-md-center">
                        <a id="toggle-btn" href="#" class="menu-btn"><i class="fa fa-bars"> </i></a>
                        @if (session()->has('token_siat'))
                            <li class="nav-item"><span><img src="{{ url('public/logo/logo_siat.png') }}" alt="logo_siat"
                                        width="60px"></span></li>
                        @endif
                    </ul>
                    <span class="brand-big">
                        @if ($general_setting->site_logo)
                            <img src="{{ url('public/logo', $general_setting->site_logo) }}" width="50">&nbsp;&nbsp;
                        @endif
                        <a href="{{ url('/') }}">
                            <h1 class="d-inline">{{ $general_setting->site_title }}</h1>
                        </a>
                    </span>

                    <ul class="nav-menu list-unstyled d-flex flex-md-row align-items-md-center">
                        <?php
$add_permission = DB::table('permissions')->where('name', 'sales-add')->first();
$add_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $add_permission->id], ['role_id', $role->id]])
    ->first();

$empty_database_permission = DB::table('permissions')->where('name', 'empty_database')->first();
$empty_database_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $empty_database_permission->id], ['role_id', $role->id]])
    ->first();
$backup_database_permission = DB::table('permissions')->where('name', 'backup_database')->first();
$backup_database_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $backup_database_permission->id], ['role_id', $role->id]])
    ->first();
                        ?>
                        @if ($add_permission_active)
                            <li class="nav-item"><a class="dropdown-item btn-pos btn-sm" href="{{ route('sale.pos') }}"><i
                                        class="dripicons-shopping-bag"></i><span>
                                        POS</span></a></li>
                        @endif
                        <li class="nav-item"><a id="btnFullscreen"><i class="dripicons-expand"></i></a></li>
                        @if ($alert_product > 0 || $alert_lote > 0 || $alert_cuis > 0)
                            <li class="nav-item">
                                <a rel="nofollow" data-target="#" href="#" data-toggle="dropdown" aria-haspopup="true"
                                    aria-expanded="false" class="nav-link dropdown-item"><i class="dripicons-bell"></i><span
                                        class="badge badge-danger">{{ $alert_product + $alert_lote + $alert_cuis }}</span>
                                </a>
                                <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default notifications"
                                    user="menu">
                                    @if ($alert_product > 0)
                                        <li class="notifications" style="width: 50%;">
                                            <a href="{{ route('report.qtyAlert') }}" class="btn btn-link">
                                                {{ $alert_product }} producto(s) excenden cantidad de alerta</a>
                                        </li>
                                    @endif
                                    @if ($alert_lote > 0)
                                        <li class="notifications" style="width: 50%;">
                                            <a href="{{ url('report/alert_expiration/0/' . $general_setting->alert_expiration) }}"
                                                class="btn btn-link"> {{ $alert_lote }} lote(s) por expirar
                                                pronto</a>
                                        </li>
                                    @endif
                                    @if ($alert_cuis > 0)
                                        <li class="notifications" style="width: 50%;">
                                            <a href="{{ url('punto_venta') }}" class="btn btn-link">
                                                {{ $alert_cuis }} cuis por expirar
                                                pronto! Punto Venta</a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        <li class="nav-item">
                            <a rel="nofollow" data-target="#" href="#" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false" class="nav-link dropdown-item"><i class="dripicons-web"></i>
                                <span>{{ __('file.language') }}</span> <i class="fa fa-angle-down"></i></a>
                            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                                <li>
                                    <a href="{{ url('language_switch/en') }}" class="btn btn-link"> English</a>
                                </li>
                                <li>
                                    <a href="{{ url('language_switch/es') }}" class="btn btn-link"> Español</a>
                                </li>
                                <li>
                                    <a href="{{ url('language_switch/ar') }}" class="btn btn-link"> عربى</a>
                                </li>
                                <li>
                                    <a href="{{ url('language_switch/pt_BR') }}" class="btn btn-link">
                                        Portuguese</a>
                                </li>
                                <li>
                                    <a href="{{ url('language_switch/fr') }}" class="btn btn-link"> Français</a>
                                </li>
                                <li>
                                    <a href="{{ url('language_switch/de') }}" class="btn btn-link"> Deutsche</a>
                                </li>
                                <li>
                                    <a href="{{ url('language_switch/id') }}" class="btn btn-link"> Malay</a>
                                </li>
                                <li>
                                    <a href="{{ url('language_switch/hi') }}" class="btn btn-link"> हिंदी</a>
                                </li>
                                <li>
                                    <a href="{{ url('language_switch/vi') }}" class="btn btn-link"> Tiếng Việt</a>
                                </li>
                                <li>
                                    <a href="{{ url('language_switch/ru') }}" class="btn btn-link"> русский</a>
                                </li>
                                <li>
                                    <a href="{{ url('language_switch/tr') }}" class="btn btn-link"> Türk</a>
                                </li>
                                <li>
                                    <a href="{{ url('language_switch/it') }}" class="btn btn-link"> Italiano</a>
                                </li>
                                <li>
                                    <a href="{{ url('language_switch/nl') }}" class="btn btn-link"> Nederlands</a>
                                </li>
                                <li>
                                    <a href="{{ url('language_switch/lao') }}" class="btn btn-link"> Lao</a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="dropdown-item" href="{{ url('read_me') }}" target="_blank"><i
                                    class="dripicons-information"></i> {{ trans('file.Help') }}</a>
                        </li>
                        <li class="nav-item">
                            <a rel="nofollow" data-target="#" href="#" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false" class="nav-link dropdown-item"><i class="dripicons-user"></i>
                                <span>{{ ucfirst(Auth::user()->name) }}</span> <i class="fa fa-angle-down"></i>
                            </a>
                            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                                <li>
                                    <a href="{{ route('user.profile', ['id' => Auth::id()]) }}"><i
                                            class="dripicons-user"></i> {{ trans('file.profile') }}</a>
                                </li>
                                @if ($general_setting_permission_active)
                                    <li>
                                        <a href="{{ route('setting.general') }}"><i class="dripicons-gear"></i>
                                            {{ trans('file.settings') }}</a>
                                    </li>
                                @endif
                                <li>
                                    <a href="{{ url('my-transactions/' . date('Y') . '/' . date('m')) }}"><i
                                            class="dripicons-swap"></i> {{ trans('file.My Transaction') }}</a>
                                </li>
                                <li>
                                    <a href="{{ url('holidays/my-holiday/' . date('Y') . '/' . date('m')) }}"><i
                                            class="dripicons-vibrate"></i> {{ trans('file.My Holiday') }}</a>
                                </li>
                                @if ($empty_database_permission_active)
                                    <li>
                                        <a onclick="return confirm('Está seguro de eliminar todo? Si tu aceptas esta accion limpiara y se perdera los datos.')"
                                            href="{{ route('setting.emptyDatabase') }}"><i class="dripicons-stack"></i>
                                            {{ trans('file.Empty Database') }}</a>
                                    </li>
                                @endif
                                @if ($backup_database_permission_active)
                                    <li>
                                        <a onclick="return confirm('Realizará una copia actual de la base de datos, ¿Deseas Descargar la copia?.')"
                                            href="{{ route('setting.backupDatabase') }}"><i class="dripicons-stack"></i>
                                            {{ trans('file.Backup Database') }}</a>
                                    </li>
                                @endif
                                <li>
                                    <a href="{{ route('logout') }}" onclick="event.preventDefault();
                                         document.getElementById('logout-form').submit();"><i
                                            class="dripicons-power"></i>
                                        {{ trans('file.logout') }}
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                        style="display: none;">
                                        @csrf
                                    </form>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
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
                        <?php
$lims_expense_category_list = DB::table('expense_categories')->where('is_active', true)->get();
$lims_account_list = \App\Account::where('is_active', true)->get();
                        
                        ?>
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
                                    data-live-search="true" data-live-search-style="begins" title="Select Warehouse...">
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
        <div id="account-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
            class="modal fade text-left">
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
        <div id="warehouse-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
            class="modal fade text-left">
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
        <div id="warehouse-pro-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
            class="modal fade text-left">
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
$lims_user_list = DB::table('users')->where('is_active', true)->get();
                        ?>
                        <div class="form-group">
                            <label>{{ trans('file.User') }} *</label>
                            <select name="user_id" class="selectpicker form-control" required data-live-search="true"
                                id="user-id" data-live-search-style="begins" title="Select user...">
                                @foreach ($lims_user_list as $user)
                                    <option value="{{ $user->id }}">
                                        {{ $user->name . ' (' . $user->phone . ')' }}
                                    </option>
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
        <div id="customer-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
            class="modal fade text-left">
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
$lims_customer_list = DB::table('customers')->where('is_active', true)->limit(10)->get();
                        ?>
                        <div class="form-group">
                            <label>{{ trans('file.customer') }} *</label>
                            <select name="customer_id" class="selectpicker form-control" required
                                data-live-search="true" id="customer-id" data-live-search-style="begins"
                                title="Select customer...">
                                @foreach ($lims_customer_list as $customer)
                                    <option value="{{ $customer->id }}">
                                        {{ $customer->name . ' (' . $customer->phone_number . ')' }}
                                    </option>
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
        <div id="supplier-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
            class="modal fade text-left">
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
$lims_supplier_list = DB::table('suppliers')->where('is_active', true)->get();
                        ?>
                        <div class="form-group">
                            <label>{{ trans('file.Supplier') }} *</label>
                            <select name="supplier_id" class="selectpicker form-control" required
                                data-live-search="true" id="supplier-id" data-live-search-style="begins"
                                title="Select Supplier...">
                                @foreach ($lims_supplier_list as $supplier)
                                    <option value="{{ $supplier->id }}">
                                        {{ $supplier->name . ' (' . $supplier->phone_number . ')' }}
                                    </option>
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

        <div style="display:none" id="content" class="animate-bottom">
            @yield('content')
        </div>

        <footer class="main-footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-12">
                        <p>&copy; {{ $general_setting->site_title }} | {{ trans('file.Developed') }}
                            {{ trans('file.By') }} <a href="http://www.gisul.com.bo" class="external">Gisul
                                S.R.L.</a>
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    @yield('scripts')
    <script type="text/javascript">
        if ($(window).outerWidth() > 1199) {
            $('nav.side-navbar').removeClass('shrink');
        }

        function myFunction() {
            setTimeout(showPage, 150);
        }

        function showPage() {
            document.getElementById("loader").style.display = "none";
            document.getElementById("content").style.display = "block";
        }

        $("div.alert").delay(3000).slideUp(750);

        function confirmDelete() {
            if (confirm("Esta seguro de querer eliminar esto?")) {
                return true;
            }
            return false;
        }

        $("a#add-expense").click(function (e) {
            e.preventDefault();
            $('#expense-modal').modal();
        });

        $("a#add-account").click(function (e) {
            e.preventDefault();
            $('#account-modal').modal();
        });

        $("a#account-statement").click(function (e) {
            e.preventDefault();
            $('#account-statement-modal').modal();
        });

        $("a#profitLoss-link").click(function (e) {
            e.preventDefault();
            $("#profitLoss-report-form").submit();
        });

        $("a#productfinish-report-link").click(function (e) {
            e.preventDefault();
            $("#product-finish-report-form").submit();
        });

        $("a#report-link").click(function (e) {
            e.preventDefault();
            $("#product-report-form").submit();
        });

        $("a#purchase-report-link").click(function (e) {
            e.preventDefault();
            $("#purchase-report-form").submit();
        });

        $("a#sale-report-link").click(function (e) {
            e.preventDefault();
            $("#sale-report-form").submit();
        });

        $("a#sale-biller-report-link").click(function (e) {
            e.preventDefault();
            $("#sale-biller-report-form").submit();
        });

        $("a#sale-item-report-link").click(function (e) {
            e.preventDefault();
            $("#sale-item-report-form").submit();
        });

        $("a#sale-customer-report-link").click(function (e) {
            e.preventDefault();
            $("#sale-customer-report-form").submit();
        });

        $("a#sale-product-report-link").click(function (e) {
            e.preventDefault();
            $("#sale-product-report-form").submit();
        });
        $("a#sale-courtesy-report-link").click(function (e) {
            e.preventDefault();
            $("#sale-courtesy-report-form").submit();
        });
        $("a#servicemp-report-link").click(function (e) {
            e.preventDefault();
            $("#servicemp-report-form").submit();
        });
        $("a#servicempcom-report-link").click(function (e) {
            e.preventDefault();
            $("#servicempcom-report-form").submit();
        });
        $("a#payment-report-link").click(function (e) {
            e.preventDefault();
            $("#payment-report-form").submit();
        });

        $("a#account-mov-report-link").click(function (e) {
            e.preventDefault();
            $("#account-mov-report-form").submit();
        });

        $("a#warehouse-report-link").click(function (e) {
            e.preventDefault();
            $('#warehouse-modal').modal();
        });

        $("a#warehouse-pro-report-link").click(function (e) {
            e.preventDefault();
            $('#warehouse-pro-modal').modal();
        });

        $("a#warehouse-sale-report-link").click(function (e) {
            e.preventDefault();
            $('#warehouse-sale-modal').modal();
        });

        $("a#user-report-link").click(function (e) {
            e.preventDefault();
            $('#user-modal').modal();
        });

        $("a#customer-report-link").click(function (e) {
            e.preventDefault();
            $('#customer-modal').modal();
        });

        $("a#supplier-report-link").click(function (e) {
            e.preventDefault();
            $('#supplier-modal').modal();
        });

        $("a#due-report-link").click(function (e) {
            e.preventDefault();
            $("#due-report-form").submit();
        });

        $(".daterangepicker-field").daterangepicker({
            callback: function (startDate, endDate, period) {
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
            $.get(url, function (data) {
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
    </script>
</body>

</html>