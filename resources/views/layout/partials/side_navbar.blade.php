@php
    $permissions = session('permissions');
    $role = \App\Roles::find(Auth::user()->role_id);
    $blocked_modules = [];
    if ($role && $role->blocked_modules) {
        $blocked_modules = json_decode($role->blocked_modules, true) ?? [];
    }
@endphp

<nav class="side-navbar">
    <div class="side-navbar-wrapper">
        <div class="main-menu">
            <ul id="side-main-menu" class="side-menu list-unstyled">
                @if (!in_array('dashboard', $blocked_modules))
                    <li><a href="{{ url('/') }}">
                            <i class="dripicons-meter"></i>
                            <span>{{ __('file.dashboard') }}</span></a>
                    </li>
                @endif

                @if ((in_array('reservations-index', $permissions) || in_array('reservations-add', $permissions)) && !in_array('reservations', $blocked_modules))
                    <li>
                        <a href="#reservations" aria-expanded="false" data-toggle="collapse">
                            <i class="dripicons-calendar"></i><span>{{ __('Reservas') }}</span>
                        </a>
                        <ul id="reservations" class="collapse list-unstyled ">
                            @if (in_array('reservations-index', $permissions))
                                <li id="reservation-list-menu"><a
                                        href="{{ route('reservations.index') }}">{{ __('Listado') }}</a>
                                </li>
                            @endif
                            @if (in_array('reservations-add', $permissions))
                                <li id="reservation-create-menu"><a
                                        href="{{ route('reservations.create') }}">{{ __('Agregar Reserva') }}</a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if (!in_array('products', $blocked_modules))
                    <li>
                        <a href="#product" aria-expanded="false" data-toggle="collapse">
                            <i class="dripicons-list"></i>
                            <span>{{ __('file.product') }}</span>
                        </a>
                        <ul id="product" class="collapse list-unstyled ">
                            @if (in_array('category', $permissions))
                                <li id="category-menu">
                                    <a class="stopReload" href="{{ route('category.index') }}">{{ __('file.category') }}</a>
                                </li>
                            @endif
                            @if (in_array('products-index', $permissions))
                                <li id="product-list-menu">
                                    <a class="stopReload" href="{{ route('products.index') }}">{{ __('file.product_list') }}</a>
                                </li>
                                @if (in_array('products-add', $permissions))
                                    <li id="product-create-menu">
                                        <a class="stopReload" href="{{ route('products.create') }}">{{ __('file.add_product') }}</a>
                                    </li>
                                @endif
                            @endif
                            @if (in_array('print_barcode', $permissions))
                                <li id="printBarcode-menu">
                                    <a class="stopReload"
                                        href="{{ route('product.printBarcode') }}">{{ __('file.print_barcode') }}</a>
                                </li>
                            @endif
                            @if (in_array('adjustment', $permissions))
                                <li id="adjustment-menu">
                                    <a class="stopReload" href="{{ route('qty_adjustment.index') }}">{{ __('file.Adjustment') }}</a>
                                </li>
                            @endif
                            {{-- Reservas moved to top-level menu (outside Sales group) --}}
                            <li id="kardex-menu"><a href="{{ route('kardex.index') }}">Kardex</a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if (in_array('purchases-index', $permissions) && !in_array('purchases', $blocked_modules))
                    <li>
                        <a href="#purchase" aria-expanded="false" data-toggle="collapse">
                            <i class="dripicons-card"></i>
                            <span>{{ trans('file.Purchase') }}</span>
                        </a>
                        <ul id="purchase" class="collapse list-unstyled ">
                            <li id="purchase-list-menu"><a
                                    href="{{ route('purchases.index') }}">{{ trans('file.Purchase List') }}</a>
                            </li>

                            @if (in_array('purchases-add', $permissions))
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

                @if (!in_array('sales', $blocked_modules))
                    <li>
                        <a href="#sale" aria-expanded="false" data-toggle="collapse">
                            <i class="dripicons-cart"></i><span>{{ trans('file.Sale') }}</span>
                        </a>
                        <ul id="sale" class="collapse list-unstyled ">
                            @if (in_array('sales-index', $permissions) || in_array('sales-list-booksale', $permissions))
                                @if (in_array('sales-index', $permissions))
                                    <li id="sale-list-menu">
                                        <a href="{{ route('sales.index') }}">{{ trans('file.Sale List') }}</a>
                                    </li>
                                @endif
                                @if (in_array('sales-list-booksale', $permissions))
                                    <li id="sale-book-menu"><a href="{{ route('sale.libro-ventas') }}">Libro de
                                            Ventas </a>
                                    </li>
                                @endif
                                @if (in_array('sale_pendingdue', $permissions))
                                    <li id="salerec-list-menu">
                                        <a href="{{ route('receivable.index') }}">{{ trans('file.Sale List') }}
                                            Por Pagar
                                        </a>
                                    </li>
                                @endif
                                @if (in_array('sales-add', $permissions))
                                    @if (!in_array('pos', $blocked_modules))
                                        <li><a href="{{ route('sale.pos') }}">POS</a></li>
                                    @endif
                                    <li id="sale-import-menu"><a
                                            href="{{ url('sales/sale_by_csv') }}">{{ trans('file.Import Sale By CSV') }}</a>
                                    </li>
                                @endif
                            @endif
                            @if (in_array('presale-create', $permissions))
                                <li>
                                    <a href="{{ route('presale.prepos') }}">{{ trans('file.Pre Sale') }}</a>
                                </li>
                            @endif
                            @if (in_array('attentionshift', $permissions))
                                <li id="shift-menu">
                                    <a href="{{ route('attentionshift.index') }}">{{ trans('file.Attention Shift') }}
                                        Servicios
                                    </a>
                                </li>
                            @endif
                            {{-- Reservations moved to top-level; nested block removed --}}
                            @if (in_array(' gift_card', $permissions))
                                <li id="gift-card-menu"><a
                                        href="{{ route('gift_cards.index') }}">{{ trans('file.Gift Card List') }}</a>
                                </li>
                            @endif
                            @if (in_array('coupon', $permissions))
                                <li id="coupon-menu"><a href="{{ route('coupons.index') }}">{{ trans('file.Coupon List') }}</a>
                                </li>
                            @endif
                            @if (in_array(' delivery', $permissions))
                                <li id="delivery-menu"><a
                                        href="{{ route('delivery.index') }}">{{ trans('file.Delivery List') }}</a>
                                </li>
                            @endif
                            @if (in_array('contingencia_siat', $permissions))
                                <li id="contingencia-menu"><a
                                        href="{{ route('contingencia.index') }}">{{ trans('file.Contingency') }}</a>
                                </li>
                            @endif
                            {{-- inico Factura Masiva --}}
                            @if (in_array('facturamasiva_siat', $permissions))
                                <li id="factura-masiva-menu"><a href="{{ route('factura-masiva.index') }}">Factura
                                        Masiva</a>
                                </li>
                            @endif
                            {{-- fin --}}
                        </ul>
                    </li>
                @endif

                @if (in_array('expenses-index', $permissions) && !in_array('expenses', $blocked_modules))
                    <li>
                        <a href="#expense" aria-expanded="false" data-toggle="collapse"> <i
                                class="dripicons-wallet"></i><span>{{ trans('file.Expense') }}</span></a>
                        <ul id="expense" class="collapse list-unstyled ">
                            <li id="exp-cat-menu">
                                <a href="{{ route('expense_categories.index') }}">{{ trans('file.Expense Category') }}
                                </a>
                            </li>
                            <li id="exp-list-menu">
                                <a href="{{ route('expenses.index') }}">{{ trans('file.Expense List') }}</a>
                            </li>
                            @if (in_array('expenses-add', $permissions))
                                <li><a id="add-expense" href=""> {{ trans('file.Add Expense') }}</a></li>
                            @endif
                        </ul>
                    </li>
                @endif
                {{-- Indice SIAT --}}

                @if (in_array('module_siat', $permissions) && !in_array('panel_siat', $blocked_modules))
                    @include('layout.partials.aside-siat')
                @endif
                {{-- Fin SIAT --}}

                @if (in_array('quotes-index', $permissions) && !in_array('proforma', $blocked_modules))
                    <li>
                        <a href="#quotation" aria-expanded="false" data-toggle="collapse">
                            <i class="dripicons-document"></i><span>{{ trans('file.Quotation') }}</span>
                        </a>
                        <ul id="quotation" class="collapse list-unstyled ">
                            <li id="quotation-list-menu"><a
                                    href="{{ route('quotations.index') }}">{{ trans('file.Quotation List') }}</a>
                            </li>

                            @if (in_array('quotes-add', $permissions))
                                <li id="quotation-create-menu"><a
                                        href="{{ route('quotations.create') }}">{{ trans('file.Add Quotation') }}</a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if (
                        in_array('transfers-index', $permissions)
                        && !in_array('transfers', $blocked_modules)
                    )
                    <li>
                        <a href="#transfer" aria-expanded="false" data-toggle="collapse">
                            <i class="dripicons-export"></i><span>{{ trans('file.Transfer') }}</span>
                        </a>
                        <ul id="transfer" class="collapse list-unstyled ">
                            <li id="transfer-list-menu"><a href="{{ route('transfers.index') }}">
                                    {{ trans('file.Transfer List') }}</a>
                            </li>
                            @if (!in_array('transfers-request', $blocked_modules))
                                <li id="transfer-request-menu"><a href="{{ url('transfers/requests') }}">
                                        {{ trans('file.Transfer Requests') }}</a>
                                </li>
                            @endif
                            @if (in_array('transfers-add', $permissions))
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

                @if ((in_array('returns-index', $permissions) || in_array('purchase-return-index', $permissions)) && !in_array('returns', $blocked_modules))
                    <li>
                        <a href="#return" aria-expanded="false" data-toggle="collapse">
                            <i class="dripicons-archive"></i><span>{{ trans('file.return') }}</span>
                        </a>
                        <ul id="return" class="collapse list-unstyled ">
                            @if (in_array('returns-index', $permissions))
                                <li id="sale-return-menu">
                                    <a href="{{ route('return-sale.index') }}">{{ trans('file.Sale') }}</a>
                                </li>
                            @endif
                            @if (in_array('purchase-return-index', $permissions))
                                <li id="purchase-return-menu">
                                    <a href="{{ route('return-purchase.index') }}">{{ trans('file.Purchase') }}</a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if (!in_array('accounting', $blocked_modules))
                    <li class="">
                        <a href="#account" aria-expanded="false" data-toggle="collapse">
                            <i class="dripicons-briefcase"></i><span>{{ trans('file.Accounting') }}</span>
                        </a>
                        <ul id="account" class="collapse list-unstyled ">
                            @if (in_array('account-index', $permissions))
                                <li id="account-list-menu">
                                    <a href="{{ route('accounts.index') }}">{{ trans('file.Account List') }}</a>
                                </li>
                                <li>
                                    <a id="add-account" href="" onclick="openDialogNew()">{{ trans('file.Add Account') }}</a>
                                </li>
                            @endif
                            @if (in_array('money-transfer', $permissions))
                                <li id="money-transfer-menu">
                                    <a href="{{ route('money-transfers.index') }}">{{ trans('file.Money Transfer') }}</a>
                                </li>
                            @endif
                            @if (in_array('balance-sheet', $permissions))
                                <li id="balance-sheet-menu">
                                    <a href="{{ route('accounts.balancesheet') }}">{{ trans('file.Balance Sheet') }}</a>
                                </li>
                            @endif
                            @if (in_array('adjustment-account-index', $permissions) && !in_array('adjustment-account', $blocked_modules) && !in_array('qty_adjustment', $blocked_modules))
                                <li id="adjustment_account-list-menu">
                                    <a href="{{ route('adjustment_account.index') }}">{{ trans('file.Adjustment List') }}</a>
                                </li>
                            @endif
                            @if (in_array(' account-statement', $permissions) && !in_array('adjustment-account', $blocked_modules) && !in_array('qty_adjustment', $blocked_modules))
                                <li id="adjustment_account-create-menu"><a
                                        href="{{ route('adjustment_account.create') }}">{{ trans('file.Add Adjustment') }}</a>
                                </li>
                            @endif
                            @if (in_array('balance-sheet-account', $permissions))
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
                            @if (in_array('close-balance-account', $permissions))
                                <li id="cashier_account-list-menu">
                                    <a href="{{ route('cashier.index') }}">{{ trans('file.Cashier') }}</a>
                                </li>
                            @endif
                            @if (in_array('account-statement', $permissions))
                                <li id="account-statement-menu">
                                    <a id="account-statement" href="">{{ trans('file.Account Statement') }}</a>
                                </li>
                            @endif
                            <li id="account-resumen-menu"><a id="account-resumen"
                                    href="{{ url('report/resumen_account/' . date('Y-m') . '-' . '01' . '/' . date('Y-m-d')) . '/0/-1' }}">Reporte
                                    Cuentas Contable</a></li>
                        </ul>
                    </li>
                @endif

                @if (in_array('hrm-menu', $permissions) && !in_array('hrm', $blocked_modules))
                    <li class="">
                        <a href="#hrm" aria-expanded="false" data-toggle="collapse"> <i
                                class="dripicons-user-group"></i><span>HRM</span></a>
                        <ul id="hrm" class="collapse list-unstyled ">
                            @if (in_array('department', $permissions))
                                <li id="dept-menu">
                                    <a href="{{ route('departments.index') }}">{{ trans('file.Department') }}</a>
                                </li>
                            @endif
                            @if (in_array('employees-index', $permissions) && !in_array('employee', $blocked_modules))
                                <li id="employee-menu">
                                    <a href="{{ route('employees.index') }}">{{ trans('file.Employee') }}</a>
                                </li>
                            @endif
                            @if (in_array('attendance', $permissions))
                                <li id="attendance-menu">
                                    <a href="{{ route('attendance.index') }}">{{ trans('file.Attendance') }}</a>
                                </li>
                            @endif
                            @if (in_array('payroll', $permissions))
                                <li id="payroll-menu">
                                    <a href="{{ route('payroll.index') }}">{{ trans('file.Payroll') }}</a>
                                </li>
                            @endif
                            <li id="holiday-menu">
                                <a href="{{ route('holidays.index') }}">{{ trans('file.Holiday') }}</a>
                            </li>

                        </ul>
                    </li>
                @endif

                @if (!in_array('people', $blocked_modules))
                    <li>
                        <a href="#people" aria-expanded="false" data-toggle="collapse">
                            <i class="dripicons-user"></i><span>{{ trans('file.People') }}</span>
                        </a>
                        <ul id="people" class="collapse list-unstyled ">

                            @if (in_array('customers-index', $permissions) && !in_array('user', $blocked_modules))
                                <li id="user-list-menu">
                                    <a href="{{ route('user.index') }}">{{ trans('file.User List') }}</a>
                                </li>

                                @if (in_array('users-add', $permissions))
                                    <li id="user-create-menu">
                                        <a href="{{ route('user.create') }}">{{ trans('file.Add User') }}</a>
                                    </li>
                                @endif
                            @endif

                            @if (in_array('customers-index', $permissions) && !in_array('customer', $blocked_modules))
                                <li id="customer-list-menu">
                                    <a href="{{ route('customer.index') }}">{{ trans('file.Customer List') }}
                                    </a>
                                </li>

                                @if (in_array('customers-add', $permissions))
                                    <li id="customer-create-menu">
                                        <a href="{{ route('customer.create') }}">{{ trans('file.Add Customer') }}
                                        </a>
                                    </li>
                                @endif
                            @endif

                            @if (in_array('billers-index', $permissions) && !in_array('biller', $blocked_modules))
                                <li id="biller-list-menu">
                                    <a href="{{ route('biller.index') }}">{{ trans('file.Biller List') }}
                                    </a>
                                </li>

                                @if (in_array('billers-add', $permissions))
                                    <li id="biller-create-menu">
                                        <a href="{{ route('biller.create') }}">{{ trans('file.Add Biller') }}
                                        </a>
                                    </li>
                                @endif
                            @endif

                            @if (in_array('suppliers-index', $permissions) && !in_array('supplier', $blocked_modules))
                                <li id="supplier-list-menu"><a
                                        href="{{ route('supplier.index') }}">{{ trans('file.Supplier List') }}</a>
                                </li>

                                @if (in_array('suppliers-add', $permissions))
                                    <li id="supplier-create-menu"><a
                                            href="{{ route('supplier.create') }}">{{ trans('file.Add Supplier') }}</a>
                                    </li>
                                @endif
                            @endif
                        </ul>
                    </li>
                @endif

                @if (!in_array('reports', $blocked_modules))
                    <li>
                        <a href="#report" aria-expanded="false" data-toggle="collapse"> <i
                                class="dripicons-document-remove"></i><span>{{ trans('file.Reports') }}</span>
                        </a>
                        <ul id="report" class="collapse list-unstyled ">
                            @if (in_array('best-seller', $permissions))
                                <li id="general-report-menu">
                                    <a
                                        href="{{ url('report/general_report/' . date('Y-m-d') . '/' . date('Y-m-d')) . '/0/0' }}">Reporte
                                        General</a>
                                </li>
                            @endif
                            @if (in_array('best-seller', $permissions))
                                <li id="general-util-report-menu">
                                    <a
                                        href="{{ url('report/generalutil_report/' . date('Y-m') . '-' . '01' . '/' . date('Y-m-d')) . '/0/0' }}">Reporte
                                        General + Utilidades</a>
                                </li>
                            @endif
                            @if (in_array('profit-loss', $permissions))
                                <li id="profit-loss-report-menu">
                                    {!! Form::open(['route' => 'report.profitLoss', 'method' => 'post', 'id' => 'profitLoss-report-form']) !!}
                                    <input type="hidden" name="start_date" value="{{ date('Y-m') . '-' . '01' }}" />
                                    <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}" />
                                    <a id="profitLoss-link" href="">{{ trans('file.Summary Report') }}</a>
                                    {!! Form::close() !!}
                                </li>
                            @endif
                            @if (in_array('sale-renueve-report', $permissions))
                                <li id="sale-renueve-report-menu">
                                    {!! Form::open(['route' => 'report.saleRenueve', 'method' => 'post', 'id' => 'sale-renueve-report-form']) !!}
                                    <input type="hidden" name="start_date" value="{{ date('Y-m-d', strtotime(' -7 day')) }}" />
                                    <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}" />
                                    <input type="hidden" name="warehouse_id" value="0" />
                                    <input type="hidden" name="biller_id" value="0" />
                                    <a id="sale-renueve-report-link" href="">{{ trans('file.Report Product Renueve') }}</a>
                                    {!! Form::close() !!}
                                </li>
                            @endif
                            @if (in_array('best-seller', $permissions))
                                <li id="best-seller-report-menu">
                                    <a href="{{ url('report/best_seller') }}">{{ trans('file.Best Seller') }}</a>
                                </li>
                            @endif
                            @if (in_array('product-detail-report', $permissions))
                                <li id="product-detail-report-menu">
                                    <a href="{{ url('report/product_detail_report') }}">Informe Producto Por
                                        Precios</a>
                                </li>
                            @endif
                            @if (in_array('product-report', $permissions))
                                <li id="product-report-menu">
                                    <a id="warehouse-pro-report-link" href="">{{ trans('file.Product Report') }}</a>
                                </li>
                            @endif
                            @if (in_array('product-report', $permissions))
                                <li id="productfinish-report-menu">
                                    {!! Form::open(['route' => 'report.productFinish', 'method' => 'post', 'id' => 'product-finish-report-form']) !!}
                                    <input type="hidden" name="start_date" value="{{ date('Y-m-d', strtotime(' -7 day')) }}" />
                                    <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}" />
                                    <input type="hidden" name="product_id" value="0" />
                                    <a id="productfinish-report-link" href="">{{ trans('file.Product Report Finish') }}</a>
                                    {!! Form::close() !!}
                                </li>
                            @endif
                            @if (in_array('daily-sale', $permissions))
                                <li id="daily-sale-report-menu">
                                    <a
                                        href="{{ url('report/daily_sale/' . date('Y') . '/' . date('m')) }}">{{ trans('file.Daily Sale') }}</a>
                                </li>
                            @endif
                            @if (in_array('monthly-sale', $permissions))
                                <li id="monthly-sale-report-menu">
                                    <a href="{{ url('report/monthly_sale/' . date('Y')) }}">{{ trans('file.Monthly Sale') }}</a>
                                </li>
                            @endif
                            @if (in_array('daily-purchase', $permissions))
                                <li id="daily-purchase-report-menu">
                                    <a
                                        href="{{ url('report/daily_purchase/' . date('Y') . '/' . date('m')) }}">{{ trans('file.Daily Purchase') }}</a>
                                </li>
                            @endif
                            @if (in_array('monthly-purchase', $permissions))
                                <li id="monthly-purchase-report-menu">
                                    <a
                                        href="{{ url('report/monthly_purchase/' . date('Y')) }}">{{ trans('file.Monthly Purchase') }}</a>
                                </li>
                            @endif
                            @if (in_array('sale-repor', $permissions))
                                <li id="sale-report-menu">
                                    <a id="warehouse-sale-report-link" href="">{{ trans('file.Sale Report') }}</a>
                                </li>
                            @endif
                            @if (in_array('salebiller-report', $permissions))
                                <li id="sale-biller-report-menu">
                                    {!! Form::open(['route' => 'report.saleBiller', 'method' => 'post', 'id' => 'sale-biller-report-form']) !!}
                                    <input type="hidden" name="start_date" value="{{ date('Y-m-d', strtotime(' -7 day')) }}" />
                                    <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}" />
                                    <input type="hidden" name="warehouse_id" value="0" />
                                    <input type="hidden" name="biller_id" value="0" />
                                    <a id="sale-biller-report-link" href="">{{ trans('file.Sale Biller Report') }}</a>
                                    {!! Form::close() !!}
                                </li>
                            @endif
                            @if (in_array('saledetail-report', $permissions))
                                <li id="sale-item-report-menu">
                                    {!! Form::open(['route' => 'report.saleByProduct', 'method' => 'post', 'id' => 'sale-item-report-form']) !!}
                                    <input type="hidden" name="start_date" value="{{ date('Y-m-d', strtotime(' -7 day')) }}" />
                                    <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}" />
                                    <input type="hidden" name="warehouse_id" value="0" />
                                    <input type="hidden" name="biller_id" value="0" />
                                    <a id="sale-item-report-link" href="">{{ trans('file.Sale Items Report') }}</a>
                                    {!! Form::close() !!}
                                </li>
                            @endif
                            @if (in_array('salecustomer-report', $permissions))
                                <li id="sale-customer-report-menu">
                                    {!! Form::open(['route' => 'report.saleCustomer', 'method' => 'post', 'id' => 'sale-customer-report-form']) !!}
                                    <input type="hidden" name="start_date" value="{{ date('Y-m-d', strtotime(' -7 day')) }}" />
                                    <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}" />
                                    <input type="hidden" name="warehouse_id" value="0" />
                                    <input type="hidden" name="customer_id" value="0" />
                                    <a id="sale-customer-report-link" href="">{{ trans('file.Sale Customer Report') }}</a>
                                    {!! Form::close() !!}
                                </li>
                            @endif
                            @if (in_array('salecustomer-report', $permissions))
                                <li id="sale-product-report-menu">
                                    {!! Form::open(['route' => 'report.saleProduct', 'method' => 'post', 'id' => 'sale-product-report-form']) !!}
                                    <input type="hidden" name="start_date" value="{{ date('Y-m-d') }}" />
                                    <input type="hidden" name="end_date" value="{{ date('Y-m-d', strtotime(' 1 day')) }}" />
                                    <input type="hidden" name="category_id" value="0" />
                                    <a id="sale-product-report-link" href="">{{ trans('file.Sale Product Report') }}</a>
                                    {!! Form::close() !!}
                                </li>
                            @endif
                            @if (in_array('salecustomer-report', $permissions))
                                <li id="sale-courtesy-report-menu">
                                    {!! Form::open(['route' => 'report.saleCourtesy', 'method' => 'post', 'id' => 'sale-courtesy-report-form']) !!}
                                    <input type="hidden" name="start_date" value="{{ date('Y-m-d') }}" />
                                    <input type="hidden" name="end_date" value="{{ date('Y-m-d', strtotime(' 1 day')) }}" />
                                    <input type="hidden" name="category_id" value="0" />
                                    <a id="sale-courtesy-report-link" href="">{{ trans('file.Sale Courtesy Report') }}</a>
                                    {!! Form::close() !!}
                                </li>
                            @endif
                            @if (in_array('service-commission-report', $permissions))
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
                            @if (in_array('only-commision-report', $permissions))
                                <li id="sale-servicemp-report-menu">
                                    {!! Form::open(['route' => 'report.employeeService', 'method' => 'post', 'id' => 'servicemp-report-form']) !!}
                                    <input type="hidden" name="start_date" value="{{ date('Y-m-d') }}" />
                                    <input type="hidden" name="end_date" value="{{ date('Y-m-d', strtotime(' 1 day')) }}" />
                                    <input type="hidden" name="employee_id" value="0" />
                                    <a id="servicemp-report-link" href="">{{ trans('file.Service Employee Report') }}</a>
                                    {!! Form::close() !!}
                                </li>
                            @endif
                            @if (in_array('payment-report', $permissions))
                                <li id="payment-report-menu">
                                    {!! Form::open(['route' => 'report.paymentByDate', 'method' => 'post', 'id' => 'payment-report-form']) !!}
                                    <input type="hidden" name="start_date" value="{{ date('Y-m-d', strtotime(' -7 day')) }}" />
                                    <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}" />
                                    <input type="hidden" name="kind_payment" value="1" />
                                    <a id="payment-report-link" href="">{{ trans('file.Payment Report') }}</a>
                                    {!! Form::close() !!}
                                </li>
                            @endif
                            @if (in_array('purchase-report', $permissions))
                                <li id="purchase-report-menu">
                                    {!! Form::open(['route' => 'report.purchase', 'method' => 'post', 'id' => 'purchase-report-form']) !!}
                                    <input type="hidden" name="start_date" value="{{ date('Y-m-d', strtotime(' -7 day')) }}" />
                                    <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}" />
                                    <input type="hidden" name="warehouse_id" value="0" />
                                    <a id="purchase-report-link" href="">{{ trans('file.Purchase Report') }}</a>
                                    {!! Form::close() !!}
                                </li>
                            @endif
                            @if (in_array('warehouse-report', $permissions))
                                <li id="warehouse-report-menu">
                                    <a id="warehouse-report-link" href="">{{ trans('file.Warehouse Report') }}</a>
                                </li>
                            @endif
                            @if (in_array('warehouse-stock-report', $permissions))
                                <li id="warehouse-stock-report-menu">
                                    <a href="{{ route('report.warehouseStock') }}">{{ trans('file.Warehouse Stock Chart') }}</a>
                                </li>
                            @endif
                            @if (in_array('product-qty-alert', $permissions))
                                <li id="qtyAlert-report-menu">
                                    <a href="{{ route('report.qtyAlert') }}">{{ trans('file.Product Quantity Alert') }}</a>
                                </li>
                            @endif
                            @if (in_array('product-qty-alert', $permissions))
                                <li id="loteAlert-report-menu">
                                    <a
                                        href="{{ route('report.alertExpiration', ['filter' => 0, 'days' => $general_setting->alert_expiration]) }}">{{ trans('file.Alert of Lotes') }}</a>
                                </li>
                            @endif
                            @if (in_array('product-qty-alert', $permissions))
                                <li id="proLotes-report-menu">
                                    <a href="{{ route('report.productsLotes') }}">{{ trans('file.Products With Lotes') }}</a>
                                </li>
                            @endif
                            @if (in_array('user-report', $permissions))
                                <li id="user-report-menu">
                                    <a id="user-report-link" href="">{{ trans('file.User Report') }}</a>
                                </li>
                            @endif
                            @if (in_array('customer-report', $permissions))
                                <li id="customer-report-menu">
                                    <a id="customer-report-link" href="">{{ trans('file.Customer Report') }}</a>
                                </li>
                            @endif
                            @if (in_array('supplier-report', $permissions))
                                <li id="supplier-report-menu">
                                    <a id="supplier-report-link" href="">{{ trans('file.Supplier Report') }}</a>
                                </li>
                            @endif
                            @if (in_array('due-report', $permissions))
                                <li id="due-report-menu">
                                    {!! Form::open(['route' => 'report.dueByDate', 'method' => 'post', 'id' => 'due-report-form']) !!}
                                    <input type="hidden" name="start_date" value="{{ date('Y-m-d', strtotime(' -7 day')) }}" />
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

                @if (!in_array('settings', $blocked_modules))
                    <li><a href="#setting" aria-expanded="false" data-toggle="collapse"> <i
                                class="dripicons-gear"></i><span>{{ trans('file.settings') }}</span></a>
                        <ul id="setting" class="collapse list-unstyled ">

                            @if ($role->id <= 2)
                                <li id="role-menu"><a href="{{ route('role.index') }}">{{ trans('file.Role Permission') }}</a>
                                </li>
                            @endif

                            @if (in_array('warehouse', $permissions))
                                <li id="warehouse-menu"><a
                                        href="{{ route('warehouse.index') }}">{{ trans('file.Warehouse') }}</a>
                                </li>
                            @endif
                            @if (in_array('customer_group', $permissions))
                                <li id="customer-group-menu"><a
                                        href="{{ route('customer_group.index') }}">{{ trans('file.Customer Group') }}</a>
                                </li>
                            @endif
                            @if (in_array('brand', $permissions))
                                <li id="brand-menu"><a href="{{ route('brand.index') }}">{{ trans('file.Brand') }}</a>
                                </li>
                            @endif
                            @if (in_array('unit', $permissions))
                                <li id="unit-menu"><a href="{{ route('unit.index') }}">{{ trans('file.Unit') }}</a>
                                </li>
                            @endif
                            @if (in_array('tax', $permissions))
                                <li id="tax-menu"><a href="{{ route('tax.index') }}">{{ trans('file.Tax') }}</a>
                                </li>
                            @endif
                            <li id="user-menu">
                                <a href="{{ route('user.profile', ['id' => Auth::id()]) }}">{{ trans('file.User Profile') }}
                                </a>
                            </li>
                            @if (in_array('create_sms', $permissions))
                                <li id="create-sms-menu"><a
                                        href="{{ route('setting.createSms') }}">{{ trans('file.Create SMS') }}</a>
                                </li>
                            @endif
                            @if (in_array('general_setting', $permissions))
                                <li id="general-setting-menu"><a
                                        href="{{ route('setting.general') }}">{{ trans('file.General Setting') }}</a>
                                </li>
                            @endif
                            @if (in_array('mail_setting', $permissions))
                                <li id="mail-setting-menu"><a
                                        href="{{ route('setting.mail') }}">{{ trans('file.Mail Setting') }}</a>
                                </li>
                            @endif
                            @if (in_array('sms_setting', $permissions))
                                <li id="sms-setting-menu"><a
                                        href="{{ route('setting.sms') }}">{{ trans('file.SMS Setting') }}</a></li>
                            @endif
                            @if (in_array('pos_setting', $permissions))
                                <li id="pos-setting-menu"><a href="{{ route('setting.pos') }}">POS
                                        {{ trans('file.settings') }}</a></li>
                            @endif
                            @if (in_array('hrm_setting', $permissions))
                                <li id="hrm-setting-menu"><a href="{{ route('setting.hrm') }}">
                                        {{ trans('file.HRM Setting') }}</a></li>
                            @endif
                            @if (in_array('pos_setting', $permissions))
                                <li id="printer-menu"><a href="{{ route('printer.index') }}">{{ trans('file.Printers') }}</a>
                                </li>
                            @endif
                            @if (in_array('module_qr ', $permissions))
                                <li id="qr-setting-menu"><a href="{{ route('setting.qrsimple') }}">
                                        {{ trans('file.QR Setting') }}</a></li>
                            @endif
                        </ul>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</nav>