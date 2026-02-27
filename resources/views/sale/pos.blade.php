@extends('layout.top-head') @section('content')

    <!-- Side Navbar -->
    <nav class="side-navbar shrink">
        <div class="side-navbar-wrapper">
            <!-- Sidebar Header    -->
            <!-- Sidebar Navigation Menus-->
            <div class="main-menu">
                <ul id="side-main-menu" class="side-menu list-unstyled">
                    <li><a href="{{ url('/') }}"> <i
                                class="dripicons-meter"></i><span>{{ __('file.dashboard') }}</span></a></li>
                    <?php
                    $role = DB::table('roles')->find(Auth::user()->role_id);
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

                    <li><a href="#product" aria-expanded="false" data-toggle="collapse"> <i
                                class="dripicons-list"></i><span>{{ __('file.product') }}</span><span></a>
                        <ul id="product" class="collapse list-unstyled ">
                            <li id="category-menu"><a href="{{ route('category.index') }}">{{ __('file.category') }}</a>
                            </li>
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
                                            href="{{ route('products.create') }}">{{ __('file.add_product') }}</a></li>
                                @endif
                            @endif
                            @if ($print_barcode_active)
                                <li id="printBarcode-menu"><a
                                        href="{{ route('product.printBarcode') }}">{{ __('file.print_barcode') }}</a></li>
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
                                        href="{{ route('stock-count.index') }}">{{ trans('file.Stock Count') }}</a></li>
                            @endif
                        </ul>
                    </li>
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
                                        href="{{ route('purchases.index') }}">{{ trans('file.Purchase List') }}</a></li>
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
                    $index_permission = DB::table('permissions')->where('name', 'sales-index')->first();
                    $index_permission_active = DB::table('role_has_permissions')
                        ->where([['permission_id', $index_permission->id], ['role_id', $role->id]])
                        ->first();
                    
                    $gift_card_permission = DB::table('permissions')->where('name', 'gift_card')->first();
                    $gift_card_permission_active = DB::table('role_has_permissions')
                        ->where([['permission_id', $gift_card_permission->id], ['role_id', $role->id]])
                        ->first();
                    
                    $coupon_permission = DB::table('permissions')->where('name', 'coupon')->first();
                    $coupon_permission_active = DB::table('role_has_permissions')
                        ->where([['permission_id', $coupon_permission->id], ['role_id', $role->id]])
                        ->first();
                    
                    $presale_index_permission = DB::table('permissions')->where('name', 'presale-create')->first();
                    $presale_index_permission_active = DB::table('role_has_permissions')
                        ->where([['permission_id', $presale_index_permission->id], ['role_id', $role->id]])
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

                    <li><a href="#sale" aria-expanded="false" data-toggle="collapse"> <i
                                class="dripicons-cart"></i><span>{{ trans('file.Sale') }}</span></a>
                        <ul id="sale" class="collapse list-unstyled ">
                            @if ($index_permission_active)
                                <li id="sale-list-menu"><a
                                        href="{{ route('sales.index') }}">{{ trans('file.Sale List') }}</a></li>
                                @if ($sale_booksale_active)
                                    <li id="sale-book-menu"><a href="{{ route('sale.libro-ventas') }}">Libro de Ventas </a>
                                    </li>
                                @endif
                                @if ($sale_pendingdue_permission_active)
                                    <li id="salerec-list-menu"><a
                                            href="{{ route('receivable.index') }}">{{ trans('file.Sale List') }}
                                            Por Pagar</a>
                                    </li>
                                @endif
                                <?php
                                $add_permission = DB::table('permissions')->where('name', 'sales-add')->first();
                                $add_permission_active = DB::table('role_has_permissions')
                                    ->where([['permission_id', $add_permission->id], ['role_id', $role->id]])
                                    ->first();
                                ?>
                                @if ($add_permission_active)
                                    <li><a href="{{ route('sale.pos') }}">POS</a></li>
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
                            @if ($gift_card_permission_active)
                                <li id="gift-card-menu"><a
                                        href="{{ route('gift_cards.index') }}">{{ trans('file.Gift Card List') }}</a>
                                </li>
                            @endif
                            @if ($coupon_permission_active)
                                <li id="coupon-menu"><a
                                        href="{{ route('coupons.index') }}">{{ trans('file.Coupon List') }}</a> </li>
                            @endif
                            <li id="delivery-menu"><a
                                    href="{{ route('delivery.index') }}">{{ trans('file.Delivery List') }}</a></li>

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
                            {{-- fin  --}}
                        </ul>
                    </li>
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
                                        href="{{ route('expenses.index') }}">{{ trans('file.Expense List') }}</a></li>
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
                    <?php
                    $index_permission = DB::table('permissions')->where('name', 'quotes-index')->first();
                    $index_permission_active = DB::table('role_has_permissions')
                        ->where([['permission_id', $index_permission->id], ['role_id', $role->id]])
                        ->first();
                    ?>
                    @if ($index_permission_active)
                        <li><a href="#quotation" aria-expanded="false" data-toggle="collapse"> <i
                                    class="dripicons-document"></i><span>{{ trans('file.Quotation') }}</span><span></a>
                            <ul id="quotation" class="collapse list-unstyled ">
                                <li id="quotation-list-menu"><a
                                        href="{{ route('quotations.index') }}">{{ trans('file.Quotation List') }}</a></li>
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
                    @if ($index_permission_active)
                        <li><a href="#transfer" aria-expanded="false" data-toggle="collapse"> <i
                                    class="dripicons-export"></i><span>{{ trans('file.Transfer') }}</span></a>
                            <ul id="transfer" class="collapse list-unstyled ">
                                <li id="transfer-list-menu"><a
                                        href="{{ route('transfers.index') }}">{{ trans('file.Transfer List') }}</a></li>
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

                    <li><a href="#return" aria-expanded="false" data-toggle="collapse"> <i
                                class="dripicons-archive"></i><span>{{ trans('file.return') }}</span></a>
                        <ul id="return" class="collapse list-unstyled ">
                            <?php
                            $index_permission = DB::table('permissions')->where('name', 'returns-index')->first();
                            $index_permission_active = DB::table('role_has_permissions')
                                ->where([['permission_id', $index_permission->id], ['role_id', $role->id]])
                                ->first();
                            ?>
                            @if ($index_permission_active)
                                <li id="sale-return-menu"><a
                                        href="{{ route('return-sale.index') }}">{{ trans('file.Sale') }}</a></li>
                            @endif
                            <?php
                            $index_permission = DB::table('permissions')->where('name', 'purchase-return-index')->first();
                            $index_permission_active = DB::table('role_has_permissions')
                                ->where([['permission_id', $index_permission->id], ['role_id', $role->id]])
                                ->first();
                            ?>
                            @if ($index_permission_active)
                                <li id="purchase-return-menu"><a
                                        href="{{ route('return-purchase.index') }}">{{ trans('file.Purchase') }}</a></li>
                            @endif
                        </ul>
                    </li>
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
                    $adjaccount_statement_permission_active = DB::table('role_has_permissions')
                        ->where([['permission_id', $account_statement_permission->id], ['role_id', $role->id]])
                        ->first();
                    
                    ?>
                    @if (
                        $index_permission_active ||
                            $balance_sheet_account_permission_active ||
                            $balance_sheet_permission_active ||
                            $account_statement_permission_active ||
                            $close_balance_account_permission)
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
                                @if ($adjaccount_statement_permission)
                                    <li id="adjustment_account-list-menu"><a
                                            href="{{ route('adjustment_account.index') }}">{{ trans('file.Adjustment List') }}</a>
                                    </li>
                                @endif
                                @if ($adjaccount_statement_permission)
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
                                        <input type="hidden" name="end_date"
                                            value="{{ date('Y-m-d', strtotime(' 1 day')) }}" />
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
                                @if ($index_employee_active)
                                    <li id="employee-menu"><a
                                            href="{{ route('employees.index') }}">{{ trans('file.Employee') }}</a></li>
                                @endif
                                @if ($attendance_active)
                                    <li id="attendance-menu"><a
                                            href="{{ route('attendance.index') }}">{{ trans('file.Attendance') }}</a>
                                    </li>
                                @endif
                                @if ($payroll_active)
                                    <li id="payroll-menu"><a
                                            href="{{ route('payroll.index') }}">{{ trans('file.Payroll') }}</a></li>
                                @endif
                                <li id="holiday-menu"><a
                                        href="{{ route('holidays.index') }}">{{ trans('file.Holiday') }}</a></li>
                            </ul>
                        </li>
                    @endif
                    <li><a href="#people" aria-expanded="false" data-toggle="collapse"> <i
                                class="dripicons-user"></i><span>{{ trans('file.People') }}</span></a>
                        <ul id="people" class="collapse list-unstyled ">
                            <?php $index_permission_active = DB::table('permissions')
                                ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                                ->where([['permissions.name', 'users-index'], ['role_id', $role->id]])
                                ->first();
                            ?>
                            @if ($index_permission_active)
                                <li id="user-list-menu"><a
                                        href="{{ route('user.index') }}">{{ trans('file.User List') }}</a></li>
                                <?php $add_permission_active = DB::table('permissions')
                                    ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                                    ->where([['permissions.name', 'users-add'], ['role_id', $role->id]])
                                    ->first();
                                ?>
                                @if ($add_permission_active)
                                    <li id="user-create-menu"><a
                                            href="{{ route('user.create') }}">{{ trans('file.Add User') }}</a></li>
                                @endif
                            @endif
                            <?php
                            $index_permission = DB::table('permissions')->where('name', 'customers-index')->first();
                            $index_permission_active = DB::table('role_has_permissions')
                                ->where([['permission_id', $index_permission->id], ['role_id', $role->id]])
                                ->first();
                            ?>
                            @if ($index_permission_active)
                                <li id="customer-list-menu"><a
                                        href="{{ route('customer.index') }}">{{ trans('file.Customer List') }}</a></li>
                                <?php
                                $add_permission = DB::table('permissions')->where('name', 'customers-add')->first();
                                $add_permission_active = DB::table('role_has_permissions')
                                    ->where([['permission_id', $add_permission->id], ['role_id', $role->id]])
                                    ->first();
                                ?>
                                @if ($add_permission_active)
                                    <li id="customer-create-menu"><a
                                            href="{{ route('customer.create') }}">{{ trans('file.Add Customer') }}</a>
                                    </li>
                                @endif
                            @endif
                            <?php
                            $index_permission = DB::table('permissions')->where('name', 'billers-index')->first();
                            $index_permission_active = DB::table('role_has_permissions')
                                ->where([['permission_id', $index_permission->id], ['role_id', $role->id]])
                                ->first();
                            ?>
                            @if ($index_permission_active)
                                <li id="biller-list-menu"><a
                                        href="{{ route('biller.index') }}">{{ trans('file.Biller List') }}</a></li>
                                <?php
                                $add_permission = DB::table('permissions')->where('name', 'billers-add')->first();
                                $add_permission_active = DB::table('role_has_permissions')
                                    ->where([['permission_id', $add_permission->id], ['role_id', $role->id]])
                                    ->first();
                                ?>
                                @if ($add_permission_active)
                                    <li id="biller-create-menu"><a
                                            href="{{ route('biller.create') }}">{{ trans('file.Add Biller') }}</a></li>
                                @endif
                            @endif
                            <?php
                            $index_permission = DB::table('permissions')->where('name', 'suppliers-index')->first();
                            $index_permission_active = DB::table('role_has_permissions')
                                ->where([['permission_id', $index_permission->id], ['role_id', $role->id]])
                                ->first();
                            ?>
                            @if ($index_permission_active)
                                <li id="supplier-list-menu"><a
                                        href="{{ route('supplier.index') }}">{{ trans('file.Supplier List') }}</a></li>
                                <?php
                                $add_permission = DB::table('permissions')->where('name', 'suppliers-add')->first();
                                $add_permission_active = DB::table('role_has_permissions')
                                    ->where([['permission_id', $add_permission->id], ['role_id', $role->id]])
                                    ->first();
                                ?>
                                @if ($add_permission_active)
                                    <li id="supplier-create-menu"><a
                                            href="{{ route('supplier.create') }}">{{ trans('file.Add Supplier') }}</a>
                                    </li>
                                @endif
                            @endif
                        </ul>
                    </li>
                    <li><a href="#report" aria-expanded="false" data-toggle="collapse"> <i
                                class="dripicons-document-remove"></i><span>{{ trans('file.Reports') }}</span></a>
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
                        $salerenueve_report_active = DB::table('permissions')
                            ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                            ->where([['permissions.name', 'sale-renueve-report'], ['role_id', $role->id]])
                            ->first();
                        $holidayemp_report_active = DB::table('permissions')
                            ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                            ->where([['permissions.name', 'holiday-employee-report'], ['role_id', $role->id]])
                            ->first();
                        $attendancemp_report_active = DB::table('permissions')
                            ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                            ->where([['permissions.name', 'attendance-employee-report'], ['role_id', $role->id]])
                            ->first();
                        ?>
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
                                    <a href="{{ url('report/product_detail_report') }}">Informe Producto Por Precios</a>
                                </li>
                            @endif
                            @if ($product_report_active)
                                <li id="product-report-menu">
                                    <a id="warehouse-pro-report-link"
                                        href="">{{ trans('file.Product Report') }}</a>
                                </li>
                            @endif
                            @if ($product_report_active)
                                <li id="productfinish-report-menu">
                                    {!! Form::open(['route' => 'report.productFinish', 'method' => 'post', 'id' => 'product-finish-report-form']) !!}
                                    <input type="hidden" name="start_date"
                                        value="{{ date('Y-m-d', strtotime(' -7 day')) }}" />
                                    <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}" />
                                    <input type="hidden" name="product_id" value="0" />
                                    <a id="productfinish-report-link"
                                        href="">{{ trans('file.Product Report Finish') }}</a>
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
                                    <a id="warehouse-sale-report-link"
                                        href="">{{ trans('file.Sale Report') }}</a>
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
                                    <a id="sale-biller-report-link"
                                        href="">{{ trans('file.Sale Biller Report') }}</a>
                                    {!! Form::close() !!}
                                </li>
                            @endif
                            @if ($salerenueve_report_active)
                                <li id="sale-renueve-report-menu">
                                    {!! Form::open(['route' => 'report.saleRenueve', 'method' => 'post', 'id' => 'sale-renueve-report-form']) !!}
                                    <input type="hidden" name="start_date"
                                        value="{{ date('Y-m-d', strtotime(' -7 day')) }}" />
                                    <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}" />
                                    <input type="hidden" name="warehouse_id" value="0" />
                                    <input type="hidden" name="biller_id" value="0" />
                                    <a id="sale-renueve-report-link"
                                        href="">{{ trans('file.Report Product Renueve') }}</a>
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
                                    <a id="sale-item-report-link"
                                        href="">{{ trans('file.Sale Items Report') }}</a>
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
                                    <a id="sale-customer-report-link"
                                        href="">{{ trans('file.Sale Customer Report') }}</a>
                                    {!! Form::close() !!}
                                </li>
                            @endif
                            @if ($salecustomer_report_active)
                                <li id="sale-product-report-menu">
                                    {!! Form::open(['route' => 'report.saleProduct', 'method' => 'post', 'id' => 'sale-product-report-form']) !!}
                                    <input type="hidden" name="start_date" value="{{ date('Y-m-d') }}" />
                                    <input type="hidden" name="end_date"
                                        value="{{ date('Y-m-d', strtotime(' 1 day')) }}" />
                                    <input type="hidden" name="category_id" value="0" />
                                    <a id="sale-product-report-link"
                                        href="">{{ trans('file.Sale Product Report') }}</a>
                                    {!! Form::close() !!}
                                </li>
                            @endif
                            @if ($salecustomer_report_active)
                                <li id="sale-courtesy-report-menu">
                                    {!! Form::open(['route' => 'report.saleCourtesy', 'method' => 'post', 'id' => 'sale-courtesy-report-form']) !!}
                                    <input type="hidden" name="start_date" value="{{ date('Y-m-d') }}" />
                                    <input type="hidden" name="end_date"
                                        value="{{ date('Y-m-d', strtotime(' 1 day')) }}" />
                                    <input type="hidden" name="category_id" value="0" />
                                    <a id="sale-courtesy-report-link"
                                        href="">{{ trans('file.Sale Courtesy Report') }}</a>
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
                                    <input type="hidden" name="end_date"
                                        value="{{ date('Y-m-d', strtotime(' 1 day')) }}" />
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
                                    <input type="hidden" name="end_date"
                                        value="{{ date('Y-m-d', strtotime(' 1 day')) }}" />
                                    <input type="hidden" name="employee_id" value="0" />
                                    <a id="servicemp-report-link"
                                        href="">{{ trans('file.Service Employee Report') }}</a>
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
                                    <a id="warehouse-report-link"
                                        href="">{{ trans('file.Warehouse Report') }}</a>
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
                                    <a
                                        href="{{ route('report.qtyAlert') }}">{{ trans('file.Product Quantity Alert') }}</a>
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
                            @if ($holidayemp_report_active)
                                <li id="holiday-report-menu">
                                    <a
                                        href="{{ url('report/holiday-employee/' . date('Y-m-d', strtotime(' -7 day')) . '/' . date('Y-m-d')) . '/0' }}">{{ trans('file.Report Holiday By Employee') }}</a>
                                </li>
                            @endif
                            @if ($attendancemp_report_active)
                                <li id="attendance-report-menu">
                                    <a
                                        href="{{ url('report/attendance-employee/' . date('Y-m-d', strtotime(' -7 day')) . '/' . date('Y-m-d')) . '/0' }}">{{ trans('file.Report Attendance By Employee') }}</a>
                                </li>
                            @endif
                            <li id="report-invoicesale-menu"><a id="account-resumen"
                                href="{{ url('report/sales_report/' . date('Y-m') . '-' . '01' . '/' . date('Y-m-d')) . '/-1/0' }}">Informe
                                Factura/Venta</a></li>
                        </ul>
                    </li>
                    {{-- Indice SIAT --}}
                    <?php
                    $siat_permission = DB::table('permissions')->where('name', 'module_siat')->first();
                    $siat_permission_active = DB::table('role_has_permissions')
                        ->where([['permission_id', $siat_permission->id], ['role_id', $role->id]])
                        ->first();
                    ?>
                    @if ($siat_permission_active)
                        @include('layout.partials.aside-siat')
                    @endif
                    {{-- Fin SIAT --}}

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
                            ?>
                            @if ($role->id <= 2)
                                <li id="role-menu"><a
                                        href="{{ route('role.index') }}">{{ trans('file.Role Permission') }}</a></li>
                            @endif
                            @if ($warehouse_permission_active)
                                <li id="warehouse-menu"><a
                                        href="{{ route('warehouse.index') }}">{{ trans('file.Warehouse') }}</a></li>
                            @endif
                            @if ($customer_group_permission_active)
                                <li id="customer-group-menu"><a
                                        href="{{ route('customer_group.index') }}">{{ trans('file.Customer Group') }}</a>
                                </li>
                            @endif
                            @if ($brand_permission_active)
                                <li id="brand-menu"><a href="{{ route('brand.index') }}">{{ trans('file.Brand') }}</a>
                                </li>
                            @endif
                            @if ($unit_permission_active)
                                <li id="unit-menu"><a href="{{ route('unit.index') }}">{{ trans('file.Unit') }}</a>
                                </li>
                            @endif
                            @if ($tax_permission_active)
                                <li id="tax-menu"><a href="{{ route('tax.index') }}">{{ trans('file.Tax') }}</a></li>
                            @endif
                            <li id="user-menu"><a
                                    href="{{ route('user.profile', ['id' => Auth::id()]) }}">{{ trans('file.User Profile') }}</a>
                            </li>
                            @if ($create_sms_permission_active)
                                <li id="create-sms-menu"><a
                                        href="{{ route('setting.createSms') }}">{{ trans('file.Create SMS') }}</a></li>
                            @endif
                            @if ($general_setting_permission_active)
                                <li id="general-setting-menu"><a
                                        href="{{ route('setting.general') }}">{{ trans('file.General Setting') }}</a>
                                </li>
                            @endif
                            @if ($mail_setting_permission_active)
                                <li id="mail-setting-menu"><a
                                        href="{{ route('setting.mail') }}">{{ trans('file.Mail Setting') }}</a></li>
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
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <section class="forms pos-section">
        <div class="container-fluid">
            <div class="row">
                @include('layout.partials.session-flash-swal-fire')

                <audio id="mysoundclip1" preload="auto">
                    <source src="{{ url('beep/beep-timber.mp3') }}">
                </audio>
                <audio id="mysoundclip2" preload="auto">
                    <source src="{{ url('beep/beep-07.mp3') }}">
                </audio>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body" style="padding-bottom: 0">
                            {!! Form::open([
                                'route' => 'sales.store',
                                'method' => 'post',
                                'files' => true,
                                'class' => 'payment-form',
                                'id' => 'formPayment',
                            ]) !!}
                            @php
                                if ($lims_pos_setting_data) {
                                    $keybord_active = $lims_pos_setting_data->keybord_active;
                                } else {
                                    $keybord_active = 0;
                                }

                                $customer_active = DB::table('permissions')
                                    ->join(
                                        'role_has_permissions',
                                        'permissions.id',
                                        '=',
                                        'role_has_permissions.permission_id',
                                    )
                                    ->where([
                                        ['permissions.name', 'customers-add'],
                                        ['role_id', \Auth::user()->role_id],
                                    ])
                                    ->first();
                            @endphp
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                @if ($biller_data)
                                                    <input type="hidden" name="warehouse_id_hidden"
                                                        value="{{ $biller_data->warehouse_id }}">
                                                @endif
                                                <select required id="warehouse_id" name="warehouse_id"
                                                    class="selectpicker form-control" data-live-search="true"
                                                    data-live-search-style="contains" title="Select warehouse...">
                                                    @if (sizeof($lims_warehouse_selects) > 0)
                                                        @foreach ($lims_warehouse_selects as $warehouse)
                                                            <option value="{{ $warehouse->id }}">
                                                                {{ $warehouse->name }}
                                                            </option>
                                                        @endforeach
                                                    @else
                                                        @foreach ($lims_warehouse_list as $warehouse)
                                                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <div id="div_biller" class="col-md-4">
                                            <div class="form-group">
                                                @if ($biller_data)
                                                    <input type="hidden" name="biller_id_hidden"
                                                        value="{{ $biller_data->id }}">
                                                @endif
                                                <select required id="biller_id" name="biller_id"
                                                    class="selectpicker form-control" data-live-search="true"
                                                    data-live-search-style="contains" title="Select Biller...">
                                                    @foreach ($lims_biller_list as $biller)
                                                        <option value="{{ $biller->id }}">
                                                            {{ $biller->name . ' (' . $biller->company_name . ')' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div id="div_account" class="col-md-4">
                                            <div class="form-group">
                                                <input type="hidden" name="biller_id" value="{{ $biller_data->id }}">
                                                @if ($account_data)
                                                    <label id="account_id">{{ $account_data }}</label>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                @if ($biller_data)
                                                    <input type="hidden" name="customer_id_hidden"
                                                        value="{{ $biller_data->customer_id }}">
                                                @endif
                                                <div class="input-group pos">
                                                    @if ($customer_active)
                                                        <?php $deposit = []; ?>
                                                        <input type="hidden" name="customer_id" id="customer_id" />
                                                        <input type="text" name="customer_name"
                                                            id="lims_customerSearch" onfocus="this.select();"
                                                            onmouseup="return false;"
                                                            placeholder="Buscar cliente por Nombre/Documento/Codigo"
                                                            class="form-control" />
                                                        <button type="button" class="btn btn-default btn-sm"
                                                            data-toggle="modal" data-target="#addCustomer"><i
                                                                class="dripicons-plus"></i></button>
                                                    @else
                                                        <?php $deposit = []; ?>
                                                        <input type="hidden" name="customer_id" id="customer_id" />
                                                        <input type="text" name="customer_name"
                                                            id="lims_customerSearch"
                                                            placeholder="Buscar cliente por Nombre/Documento/Codigo"
                                                            onfocus="this.select();" onmouseup="return false;"
                                                            class="form-control" />
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="search-box form-group">
                                                <input type="text" name="product_code_name"
                                                    id="lims_productcodeSearch"
                                                    placeholder="Buscar/Escanear por Nombre/Código" class="form-control"
                                                    onclick="validatemp()" onkeyup="validatemp()"
                                                    onkeypress="validatemp()" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="table-responsive transaction-list">
                                            <table id="myTable"
                                                class="table table-hover table-striped order-list table-fixed"
                                                style="height: 400px;">
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
                                                <input type="hidden" name="valid_date" />
                                                <input type="hidden" name="note" value="" />
                                                <input type="hidden" name="quotation_status" value="1" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 totals" style="border-top: 2px solid #e4e6fc; padding-top: 10px;">
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <span class="totals-title">{{ trans('file.Items') }}</span><span
                                                    id="item">0</span>
                                            </div>
                                            <div class="col-sm-3">
                                                <span class="totals-title">{{ trans('file.Total') }}</span><span
                                                    id="subtotal">0.00</span>
                                            </div>
                                            <div class="col-3">
                                                <span class="totals-title">Propinas <button type="button"
                                                        class="btn btn-link btn-sm" data-toggle="modal"
                                                        data-target="#tips-modal"> <i style="font-size: 20px;"
                                                            class="dripicons-document-edit"></i></button></span><span
                                                    id="tips">0.00</span>
                                            </div>
                                            <div class="col-3">
                                                <span class="totals-title">Beneficio <button type="button"
                                                        class="btn btn-link btn-sm" data-toggle="modal"
                                                        data-target="#benefits-modal"> <i style="font-size: 20px; color: #00de00;"
                                                            class="dripicons-heart"></i></button></span><span
                                                    id="benefits">0</span>%
                                            </div>
                                            <div class="col-sm-3">
                                                <span class="totals-title">{{ __('file.Discount') }}
                                                    <button type="button" class="btn btn-link btn-sm"
                                                        data-toggle="modal" data-target="#order-discount"
                                                        onclick="mostrarPorcentaje()"
                                                        @if (in_array('pos_discount_gral', $all_permission)) : disabled @endif>
                                                        <i class="dripicons-document-edit"></i>
                                                    </button>
                                                </span>
                                                <span id="discount">0.00</span>
                                            </div>
                                            <div class="col-sm-3">
                                                <span class="totals-title">{{ trans('file.Coupon') }} <button
                                                        type="button" class="btn btn-link btn-sm" data-toggle="modal"
                                                        data-target="#coupon-modal"><i
                                                            class="dripicons-document-edit"></i></button></span><span
                                                    id="coupon-text">0.00</span>
                                            </div>
                                            <div class="col-sm-3">
                                                <span class="totals-title">{{ trans('file.Tax') }} <button
                                                        type="button" class="btn btn-link btn-sm" data-toggle="modal"
                                                        data-target="#order-tax"><i
                                                            class="dripicons-document-edit"></i></button></span><span
                                                    id="tax">0.00</span>
                                            </div>
                                            <div class="col-sm-3">
                                                <span class="totals-title">{{ trans('file.Shipping') }} <button
                                                        type="button" class="btn btn-link btn-sm" data-toggle="modal"
                                                        data-target="#shipping-cost-modal"><i
                                                            class="dripicons-document-edit"></i></button></span><span
                                                    id="shipping-cost">0.00</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="payment-amount">
                            <h2>{{ trans('file.grand total') }} <span id="grand-total">0.00</span></h2>
                        </div>
                        <div class="payment-options">
                            @if (in_array('pos_payment_card', $all_permission))
                                <div class="column-5">
                                    <button style="background: #0984e3" type="button" class="btn btn-custom payment-btn"
                                        data-toggle="modal" data-target="#add-payment" id="credit-card-btn"><i
                                            class="fa fa-credit-card"></i> Tarjeta</button>
                                </div>
                            @endif
                            @if (in_array('pos_payment_cash', $all_permission))
                                <div class="column-5">
                                    <button style="background: #00cec9" type="button" class="btn btn-custom payment-btn"
                                        data-toggle="modal" data-target="#add-payment" id="cash-btn"><i
                                            class="fa fa-money"></i> Efectivo</button>
                                </div>
                            @endif
                            @if (in_array('pos_payment_qrcash', $all_permission))
                                <div class="column-5">
                                    <button style="background: #b130ec" type="button" class="btn btn-custom payment-btn"
                                        data-toggle="modal" data-target="#add-payment" id="qrcash-btn"><i
                                            class="fa fa-money"></i>Efectivo - QR</button>
                                </div>
                            @endif
                            @if (in_array('pos_create_due', $all_permission))
                                <div class="column-5">
                                    <button style="background-color: #4024e0" type="button" class="btn btn-custom" data-toggle="modal"
                                    data-target="#order-cobrar"><i class="fa fa-save"></i> Por Cobrar</button>
                                </div>
                            @endif
                            @if (in_array('pos_payment_qr', $all_permission))
                                <div class="column-5">
                                    <button style="background-color: #2bf710" type="button"
                                        class="btn btn-custom payment-btn" data-toggle="modal" data-target="#add-payment"
                                        id="qrsimple-btn"><i class="fa fa-qrcode"></i> Qr</button>
                                </div>
                            @endif
                            @if (in_array('pos_payment_check', $all_permission))
                                <div class="column-5">
                                    <button style="background-color: #fd7272" type="button"
                                        class="btn btn-custom payment-btn" data-toggle="modal" data-target="#add-payment"
                                        id="cheque-btn"><i class="fa fa-money"></i> Cheque</button>
                                </div>
                            @endif
                            @if (in_array('pos_payment_giftcard', $all_permission))
                                <div class="column-5">
                                    <button style="background-color: #5f27cd" type="button"
                                        class="btn btn-custom payment-btn" data-toggle="modal" data-target="#add-payment"
                                        id="gift-card-btn"><i class="fa fa-credit-card-alt"></i> Gift Card</button>
                                </div>
                            @endif
                            @if (in_array('pos_payment_deposit', $all_permission))
                                <div class="column-5">
                                    <button style="background-color: #b33771" type="button"
                                        class="btn btn-custom payment-btn" data-toggle="modal" data-target="#add-payment"
                                        id="deposit-btn"><i class="fa fa-university"></i> Deposito</button>
                                </div>
                            @endif
                            <!--<div class="column-5" style="display: none">
                                <button style="background-color: #2bf710;" type="button" class="btn btn-custom"
                                    id="draft-btn"><i class="fa fa-save"></i> Guardar Venta</button>
                                </div>-->
                            @if (in_array('presale-create', $all_permission))
                                <div class="column-5">
                                    <button style="background-color: #2bf710" type="button" class="btn btn-custom"
                                        id="presale-btn"><i class="fa fa-save"></i> Generar Pre-Venta</button>
                                </div>
                            @endif
                            <div class="column-5">
                                <button style="background-color: #d63031;" type="button" class="btn btn-custom"
                                    id="cancel-btn" onclick="return confirmCancel()"><i class="fa fa-close"></i>
                                    Cancelar</button>
                            </div>
                            @if (in_array('pos_recent_sales', $all_permission))
                                <div class="column-5">
                                    <button style="background-color: #ffc107;" type="button" class="btn btn-custom"
                                        data-toggle="modal" data-target="#recentTransaction"><i
                                            class="dripicons-clock"></i>
                                        Ventas Recientes</button>
                                </div>
                            @endif
                            @if (in_array('pos_paid_due', $all_permission))
                                <div class="column-5">
                                    <button style="background-color: #07c5cc;" type="button" class="btn btn-custom"
                                        id="abonar-btn"><i class="fa fa-download"></i> Abonar Ventas</button>
                                </div>
                            @endif
                            @if (in_array('presale-index', $all_permission))
                                <div class="column-5">
                                    <button id="showpresale-btn" style="background-color: #ff07de;" type="button"
                                        class="btn btn-custom" data-toggle="modal" data-target="#presaleTransaction"><i
                                            class="dripicons-cart"></i>
                                        PreVentas</button>
                                </div>
                            @endif
                            @if (in_array('quotes-add', $all_permission))
                                <div class="column-5">
                                    <button id="proforma-btn" style="background-color: #0b07ff;" type="button"
                                        class="btn btn-custom disabled noselect">
                                        <i class="fa fa-save"></i>
                                        Generar Pro-Forma</button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- payment modal -->
                <div id="add-payment" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    data-backdrop="static" data-keyboard="false" aria-hidden="true" class="modal fade text-left">
                    <div role="document" class="modal-dialog" style="max-width: 80%; margin: 1rem auto;">
                        @include('layout.partials.spinner-ajax')
                        @include('layout.partials.spinner-contingencia-ajax')
                        <div class="modal-content">
                            <div class="modal-header bg-light" style="padding: 0.75rem 1rem;">
                                <h6 id="exampleModalLabel" class="modal-title font-weight-bold" style="font-size: 0.95rem;">
                                    @if (session()->has('token_siat'))
                                        <i class="fa fa-file-invoice"></i> Facturar Venta
                                    @else
                                        <i class="fa fa-check-circle"></i> Finalizar Venta
                                    @endif
                                </h6>
                                <div style="display:none;">
                                    @if (session()->has('token_siat'))
                                        <input id="toggle-event" type="checkbox" checked data-toggle="toggle"
                                            data-on="Si" data-off="No" data-onstyle="primary"
                                            data-offstyle="secondary">
                                    @endif
                                </div>
                                <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                                    <span aria-hidden="true"><i class="dripicons-cross"></i></span>
                                </button>
                            </div>
                            <div class="modal-body" style="padding: 1rem; max-height: 70vh; overflow-y: auto; font-size: 0.875rem;">

                                {{-- se mostrará si solo si, tiene credenciales siat --}}
                                <input type="hidden" name="facturacion_id_hidden"
                                    value="{{ $lims_pos_setting_data->facturacion_id }}">
                                <input type="hidden" name="codigo_emision_hidden"
                                    value="{{ $lims_pos_setting_data->codigo_emision }}">
                                <input type="hidden" name="ajax_sale_id" value="">
                                <input type="hidden" name="bandera_factura_hidden">
                                <input type="hidden" name="bandera_vigencia_cufd_hidden">
                                <input type="hidden" name="bandera_codigo_excepcion_hidden">
                                <input type="hidden" name="bandera_codigo_documento_sector_hidden" value="1">
                                <input type="hidden" name="montoLey1886_hidden" value="0">
                                <input type="hidden" name="montoTasaDignidad_hidden" value="0">
                                <div id="ventana_nav" class="mb-3">
                                    <ul class="nav nav-tabs nav-justified" id="myTab" role="tablist" style="border-bottom: 2px solid #dee2e6;">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-toggle="tab" href="#primerTab" role="tab">
                                                <i class="fa fa-money-bill"></i> Monto
                                            </a>
                                        </li>
                                        @if (session()->has('token_siat'))
                                        <li class="nav-item">
                                            <a id="tab_preview" class="nav-link disabled" data-toggle="tab" href="#segundoTab" role="tab">
                                                <i class="fa fa-eye"></i> Imprimible
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a id="tab_billing" class="nav-link disabled" data-toggle="tab" href="#tercerTab" role="tab">
                                                <i class="fa fa-file-alt"></i> Datos Facturación
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a id="tab_final" class="nav-link disabled" data-toggle="tab" href="#cuartoTab" role="tab">
                                                <i class="fa fa-print"></i> Impresión
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                                </div>

                                <div class="tab-content" style="padding: 1rem 0;">
                                    {{-- formulario de la primera parte --}}
                                    <div class="tab-pane fade show active" id="primerTab" role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="row">
                                                    <div class="col-md-6 mt-1">
                                                        <label>{{ trans('file.Paying Amount') }} *</label>
                                                        <input type="text" name="paid_amount"
                                                            class="form-control numkey" step="any">
                                                    </div>
                                                    <div class="col-md-6 mt-1">
                                                        <label>{{ trans('file.Recieved Amount') }} *</label>
                                                        <input type="text" name="paying_amount"
                                                            class="form-control numkey" required step="any">
                                                    </div>
                                                    <div class="col-md-3 mt-1">
                                                        <label>{{ trans('file.Change') }} : </label>
                                                        <p id="change" class="ml-2">0.00</p>
                                                    </div>
                                                    <div class="col-md-3 mt-1">
                                                        <label>{{ trans('file.Tc') }} : </label>
                                                        <p id="tc" class="ml-2">
                                                            {{ $lims_pos_setting_data->t_c }}</p>
                                                    </div>
                                                    <div class="col-md-6 mt-1">
                                                        <label>{{ trans('file.Recieved Amount') }} USD*</label>
                                                        <input type="text" name="paying_amount_us"
                                                            class="form-control numkey" step="any">
                                                    </div>
                                                    <div class="col-md-6 mt-1" style="display:none">
                                                        <label>{{ trans('file.Date') }}</label>
                                                        <input type="hidden" id="date_format" name="date_seller"
                                                            class="form-control">
                                                    </div>
                                                    {{-- listaMetodoDePagos --}}
                                                    <div class="col-md-6 mt-1">
                                                        <input type="hidden" name="paid_by_id">
                                                        <input type="hidden" name="monto_efectivo">
                                                        <input type="hidden" name="monto_tarjeta">
                                                        <input type="hidden" name="monto_cheque">
                                                        <input type="hidden" name="monto_vale">
                                                        <input type="hidden" name="monto_otros">
                                                        <input type="hidden" name="monto_pago_posterior">
                                                        <input type="hidden" name="monto_transferencia_bancaria">
                                                        <input type="hidden" name="monto_deposito">
                                                        <input type="hidden" name="monto_swift">
                                                        <input type="hidden" name="monto_cambio">
                                                        <input type="hidden" name="monto_canal_pago">
                                                        <input type="hidden" name="monto_billetera">
                                                        <input type="hidden" name="monto_pago_online">
                                                        <input type="hidden" name="monto_debito_automatico">
                                                        <label>{{ trans('file.Paid By') }}</label>
                                                        @if (session()->has('token_siat'))
                                                            <select name="paid_by_id_select"
                                                                class="form-control selectpicker" data-live-search="true"
                                                                data-live-search-style="contains"
                                                                id="paid_by_id_select_select">
                                                                @foreach ($lista_metodo_pago as $method)
                                                                    <option value="{{ $method->codigo_clasificador }}"
                                                                        data-descripcion="{{ $method->descripcion }}">
                                                                        {{ $method->codigo_clasificador . ' - ' . $method->descripcion }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        @else
                                                            <select name="paid_by_id_select"
                                                                class="form-control selectpicker" data-live-search="true"
                                                                data-live-search-style="contains"
                                                                id="paid_by_id_select_select" disabled>
                                                                @foreach ($lims_methodpay_list as $method)
                                                                    <option value="{{ $method->id }}">
                                                                        {{ $method->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        @endif
                                                    </div>

                                                    @include('sale.partials-sale-datos-metodopago')
                                                    {{-- insertando inputs para gift --}}
                                                    @include('sale.partials-mp-giftcard')
                                                    {{-- insertando inputs para gift --}}
                                                    <div class="form-group col-md-6" id="MP_tarjeta">
                                                        <label>Nro. Tarjeta crédito/débito</label>
                                                        <div class="input-group" id="tarjeta_de_credito_debito">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text badge-card"></span>
                                                            </div>
                                                            <input type="text" id="number_card" name="number_card"
                                                                maxlength="19" autocomplete="off" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-12" id="MP_cheque">
                                                        <label>{{ trans('file.Cheque Number') }} *</label>
                                                        <input type="text" name="cheque_no" class="form-control"
                                                            onkeyup="this.value=this.value.replace(/[^0-9]/g,'');">
                                                    </div>
                                                    <div class="form-group col-md-12 qrsimple" style="display: none;">
                                                        <p class="text-center"><i
                                                                class="dripicons-clock text-primary"></i> Se cerrara en
                                                            <span id="timer">10:00</span> minutos
                                                        </p>
                                                        <div class="text-center mb-3">
                                                            <img class="qrsimple-img"
                                                                src="/public/images/29497618_1212833785513817_2887668216967394137_n.jpg"
                                                                alt="qrsimple">
                                                        </div>
                                                        <p class="mb-0 text-center">No cierre esta ventana o cambie el tipo
                                                            de pago, <strong>esperando escaneo...</strong></p>
                                                    </div>
                                                    <div class="col-md-6 form-group" id="fact_manual"
                                                        style="display: none">
                                                        <label>Nro. Factura (Facturación Manual)</label>
                                                        <input type="number" class="form-control" name="invoice_no"
                                                            value="0" />
                                                    </div>
                                                    <div class="d-flex flex-wrap" id="html_montos_metodos_de_pago">
                                                        {{-- se insertarán los input --}}
                                                    </div>
                                                    {{-- <div class="form-group col-md-12">
                                                        <label>{{ trans('file.Payment Note') }}</label>
                                                        <textarea id="payment_note" rows="1" class="form-control" name="payment_note"></textarea>
                                                    </div> --}}
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 form-group">
                                                        <label>{{ trans('file.Sale Note') }}</label>
                                                        <textarea rows="1" class="form-control" name="sale_note"></textarea>
                                                    </div>
                                                    <!--<div class="col-md-6 form-group">
                                                                                            <label>{{ trans('file.Staff Note') }}</label>
                                                                                            <textarea rows="1" class="form-control" name="staff_note"></textarea>
                                                                                        </div>-->
                                                </div>
                                                <div class="mt-3">
                                                    <button class="btn btn-secondary btn-sm"
                                                        id="segundoTabContinue" 
                                                        @if (!session()->has('token_siat')) style="display:none;" @endif>
                                                        Facturar
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-md-2 qc" data-initial="1">
                                                <h6 style="font-size: 0.875rem;"><strong>{{ trans('file.Quick Cash') }}</strong></h6>
                                                <button class="btn btn-block btn-primary btn-sm qc-btn sound-btn" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;"
                                                    data-amount="0.5" type="button">0.50 Ctv</button>
                                                <button class="btn btn-block btn-primary btn-sm qc-btn sound-btn" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;" data-amount="1"
                                                    type="button">1</button>
                                                <button class="btn btn-block btn-primary btn-sm qc-btn sound-btn" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;" data-amount="2"
                                                    type="button">2</button>
                                                <button class="btn btn-block btn-primary btn-sm qc-btn sound-btn" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;" data-amount="5"
                                                    type="button">5</button>
                                                <button class="btn btn-block btn-primary btn-sm qc-btn sound-btn" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;"
                                                    data-amount="10" type="button">10</button>
                                                <button class="btn btn-block btn-primary btn-sm qc-btn sound-btn" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;"
                                                    data-amount="20" type="button">20</button>
                                                <button class="btn btn-block btn-primary btn-sm qc-btn sound-btn" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;"
                                                    data-amount="50" type="button">50</button>
                                                <button class="btn btn-block btn-primary btn-sm qc-btn sound-btn" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;"
                                                    data-amount="100" type="button">100</button>
                                                <button class="btn btn-block btn-primary btn-sm qc-btn sound-btn" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;"
                                                    data-amount="200" type="button">200</button>
                                                <button class="btn btn-block btn-danger btn-sm qc-btn sound-btn" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;" data-amount="0"
                                                    type="button">{{ trans('file.Clear') }}</button>
                                            </div>
                                            <div class="col-md-2 qc" data-initial="1">
                                                <h6 style="font-size: 0.875rem;"><strong>{{ trans('file.Quick Cash') }} USD.</strong></h6>
                                                <button class="btn btn-block btn-primary btn-sm qc-btn-us sound-btn" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;"
                                                    data-amount="20" type="button">20</button>
                                                <button class="btn btn-block btn-primary btn-sm qc-btn-us sound-btn" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;"
                                                    data-amount="50" type="button">50</button>
                                                <button class="btn btn-block btn-primary btn-sm qc-btn-us sound-btn" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;"
                                                    data-amount="100" type="button">100</button>
                                                <button class="btn btn-block btn-danger btn-sm qc-btn-us sound-btn" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;"
                                                    data-amount="0" type="button">{{ trans('file.Clear') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- formulario de la segunda parte: IMPRIMIBLE (preview) --}}
                                    @if (session()->has('token_siat'))
                                        <div class="tab-pane fade" id="segundoTab" role="tabpanel">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="text-center mb-3">
                                                        <button type="button" class="btn btn-primary btn-sm" id="btn-print-preview">
                                                            <i class="dripicons-print"></i> Imprimir Vista Previa
                                                        </button>
                                                    </div>
                                                    {{-- contenedor donde se inyectará el HTML imprimible devuelto por AJAX --}}
                                                    <div id="print_preview_container"></div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- formulario de la tercera parte: DATOS FACTURACIÓN --}}
                                        <div class="tab-pane fade" id="tercerTab" role="tabpanel">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    {{-- datos a facturar --}}
                                                    @include('sale.partials-sale-modal')
                                                </div>
                                            </div>
                                        </div>

                                        {{-- formulario de la cuarta parte: IMPRESIÓN FINAL --}}
                                        <div class="tab-pane fade" id="cuartoTab" role="tabpanel">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div id="final_print_container" class="mb-4" style="min-height: 650px; padding: 1.5rem; background: #f8f9fa; border-radius: 8px;">
                                                        <p id="final_print_message" class="text-center font-weight-bold" style="font-size: 1.1rem; color: #28a745;">
                                                            <i class="fa fa-check-circle"></i> Listo para facturar.
                                                        </p>
                                                        <a id="final_print_link" href="#" target="_blank" class="btn btn-primary" style="display:none">Abrir Factura</a>
                                                    </div>
                                                    
                                                    <!-- Sección WhatsApp en el panel final -->
                                                    <div class="card border-success shadow-sm">
                                                        <div class="card-header bg-success text-white">
                                                            <h6 class="mb-0" style="font-size: 0.875rem;"><i class="fa fa-whatsapp"></i> Enviar Factura por WhatsApp</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="form-group mb-3">
                                                                <label for="final_whatsapp_phone" class="font-weight-bold" style="font-size: 0.875rem;">Número de teléfono</label>
                                                                <div class="input-group">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text"><i class="fa fa-phone"></i></span>
                                                                    </div>
                                                                    <input type="tel" class="form-control form-control-sm" id="final_whatsapp_phone" 
                                                                        placeholder="Ej: 59176543210" 
                                                                        pattern="[0-9]+"
                                                                        title="Ingrese solo números, formato internacional sin + ni espacios">
                                                                </div>
                                                                <small class="form-text text-muted">
                                                                    <i class="fa fa-info-circle"></i> Formato internacional sin + ni espacios (Ej: 59176543210)
                                                                </small>
                                                            </div>
                                                            <button type="button" class="btn btn-success btn-block btn-sm" id="send-final-whatsapp-btn">
                                                                <i class="fa fa-whatsapp"></i> Enviar Factura por WhatsApp
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="modal-footer bg-light" style="padding: 0.75rem 1rem;">
                                <button id="submit-btn" type="submit" class="btn btn-primary btn-sm px-3">
                                    <i class="fa fa-check"></i> Confirmar Venta
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- order_discount modal -->
                <div id="order-discount" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true" class="modal fade text-left">
                    <div role="document" class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ trans('file.Order Discount') }}</h5>
                                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                        aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="form-group col">
                                        <label>Descuento general manual</label>
                                        <input type="number" id="order_discount" name="order_discount"
                                            class="form-control numkey" value="0" step="0.01">
                                    </div>
                                    <div class="form-group col">
                                        <label>Descuento general en porcentaje</label>
                                        <input id="porcentaje_order_discount" type="number"
                                            class=" form-control numkey" value="0" step="0.01">
                                    </div>
                                </div>
                                <button type="button" name="order_discount_btn" class="btn btn-primary mt-3"
                                    data-dismiss="modal">{{ trans('file.submit') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- coupon modal -->
                <div id="coupon-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true" class="modal fade text-left">
                    <div role="document" class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ trans('file.Coupon Code') }}</h5>
                                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                        aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <input type="text" id="coupon-code" class="form-control"
                                        placeholder="Type Coupon Code...">
                                </div>
                                <button type="button" class="btn btn-primary coupon-check"
                                    data-dismiss="modal">{{ trans('file.submit') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- order_tax modal -->
                <div id="order-tax" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true" class="modal fade text-left">
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
                <!-- shipping_cost modal -->
                <div id="shipping-cost-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true" class="modal fade text-left">
                    <div role="document" class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ trans('file.Shipping Cost') }}</h5>
                                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                        aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <input type="text" name="shipping_cost" class="form-control numkey"
                                        step="any">
                                </div>
                                <button type="button" name="shipping_cost_btn" class="btn btn-primary"
                                    data-dismiss="modal">{{ trans('file.submit') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- tips modal -->
                <div id="tips-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true" class="modal fade text-left">
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
                <!-- order_discount modal -->
                <div id="order-cobrar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true" class="modal fade text-left">
                    <div role="document" class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Venta Por Cobrar</h5>
                                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                        aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-12 form-group">
                                        <label>{{ trans('file.Staff Note') }} (Opcional)</label>
                                        <textarea rows="3" class="form-control" name="staff_note"></textarea>
                                    </div>
                                </div>
                                <button id="cobrar-btn" type="button" name="cobrar-btn" class="btn btn-primary mt-3"
                                    data-dismiss="modal">Confirmar Venta
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- tips modal -->
                <div id="benefits-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true" class="modal fade text-left">
                    <div role="document" class="modal-dialog modal-sm">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Beneficio Especial</h5>
                                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                        aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                            </div>
                            <div class="modal-body">
                                <small>
                                    <p>El porcentaje de beneficio se aplicará a todos los productos de la venta.</p>
                                </small>
                                <label  for="benefits_value">Porcentaje de Beneficio (%):</label>
                                <div class="form-group">
                                    <input type="number" name="benefits_value" class="form-control numkey" value="0" step="any" onclick="this.select()" nim="0" max="99">
                                </div>
                                <button type="button" name="benefits_btn" class="btn btn-primary" onclick="descuentoBeneficio()"
                                    data-dismiss="modal">Aplicar</button>
                            </div>
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
                <!-- product list -->
                <div class="col-md-6">
                    <!-- navbar-->
                    <header class="header">
                        <nav class="navbar">
                            <div class="container-fluid">
                                <div class="navbar-holder d-flex align-items-center justify-content-between">
                                    <a id="toggle-btn" href="#" class="menu-btn"><i class="fa fa-bars">
                                        </i></a>
                                    <div class="navbar-header">

                                        <ul class="nav-menu list-unstyled d-flex flex-md-row align-items-md-center">
                                            @if (in_array('quotes-add', $all_permission))
                                                <li class="nav-item">PROFORMA
                                                    <input id="toggle-event-pro" type="checkbox" data-toggle="toggle"
                                                        data-on="Si" data-off="No" data-onstyle="primary"
                                                        data-offstyle="secondary">
                                                </li>
                                            @endif
                                            <li class="nav-item"><a id="btnFullscreen" title="Full Screen"><i
                                                        class="dripicons-expand"></i></a></li>
                                            <?php
                                            $general_setting_permission = DB::table('permissions')->where('name', 'general_setting')->first();
                                            $general_setting_permission_active = DB::table('role_has_permissions')
                                                ->where([['permission_id', $general_setting_permission->id], ['role_id', Auth::user()->role_id]])
                                                ->first();
                                            
                                            $pos_setting_permission = DB::table('permissions')->where('name', 'pos_setting')->first();
                                            
                                            $pos_setting_permission_active = DB::table('role_has_permissions')
                                                ->where([['permission_id', $pos_setting_permission->id], ['role_id', Auth::user()->role_id]])
                                                ->first();
                                            ?>
                                            @if ($pos_setting_permission_active)
                                                <li class="nav-item"><a class="dropdown-item"
                                                        href="{{ route('setting.pos') }}"><i
                                                            class="dripicons-gear"></i>
                                                        <span>{{ trans('file.POS Setting') }}</span></a> </li>
                                            @endif
                                            @if ($alert_product > 0 || $alert_lote > 0)
                                                <li class="nav-item">
                                                    <a rel="nofollow" data-target="#" href="#"
                                                        data-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false" class="nav-link dropdown-item"><i
                                                            class="dripicons-bell"></i><span
                                                            class="badge badge-danger">{{ $alert_product + $alert_lote }}</span>
                                                    </a>
                                                    <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default notifications"
                                                        user="menu" style="width: 240px;">
                                                        @if ($alert_product > 0)
                                                            <li class="notifications" style="width: 50%;">
                                                                <a href="{{ route('report.qtyAlert') }}"
                                                                    class="btn btn-link"> {{ $alert_product }}
                                                                    producto(s) excenden cantidad de alerta</a>
                                                            </li>
                                                        @endif
                                                        @if ($alert_lote > 0)
                                                            <li class="notifications" style="width: 50%;">
                                                                <a href="{{ route('report.alertExpiration', ['filter' => 0, 'days' => $general_setting->alert_expiration]) }}"
                                                                    class="btn btn-link"> {{ $alert_lote }} lote(s)
                                                                    por
                                                                    expirar pronto</a>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </li>
                                            @endif
                                            <li class="nav-item">
                                                <a class="dropdown-item" href="{{ url('read_me') }}"
                                                    target="_blank"><i class="dripicons-information"></i>
                                                    {{ trans('file.Help') }}</a>
                                            </li>&nbsp;
                                            <li class="nav-item">
                                                <a rel="nofollow" data-target="#" href="#"
                                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                                    class="nav-link dropdown-item"><i class="dripicons-user"></i>
                                                    <span>{{ ucfirst(Auth::user()->name) }}</span> <i
                                                        class="fa fa-angle-down"></i>
                                                </a>
                                                <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default"
                                                    user="menu">
                                                    <li>
                                                        <a href="{{ route('user.profile', ['id' => Auth::id()]) }}"><i
                                                                class="dripicons-user"></i>
                                                            {{ trans('file.profile') }}</a>
                                                    </li>
                                                    @if ($general_setting_permission_active)
                                                        <li>
                                                            <a href="{{ route('setting.general') }}"><i
                                                                    class="dripicons-gear"></i>
                                                                {{ trans('file.settings') }}</a>
                                                        </li>
                                                    @endif
                                                    <li>
                                                        <a
                                                            href="{{ url('my-transactions/' . date('Y') . '/' . date('m')) }}"><i
                                                                class="dripicons-swap"></i>
                                                            {{ trans('file.My Transaction') }}</a>
                                                    </li>
                                                    <li>
                                                        <a
                                                            href="{{ url('holidays/my-holiday/' . date('Y') . '/' . date('m')) }}"><i
                                                                class="dripicons-vibrate"></i>
                                                            {{ trans('file.My Holiday') }}</a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ route('logout') }}"
                                                            onclick="event.preventDefault();
                                                            document.getElementById('logout-form').submit();"><i
                                                                class="dripicons-power"></i>
                                                            {{ trans('file.logout') }}
                                                        </a>
                                                        <form id="logout-form" action="{{ route('logout') }}"
                                                            method="POST" style="display: none;">
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
                    <div class="filter-window">
                        <div class="category mt-3">
                            <div class="row ml-2 mr-2 px-2">
                                <div class="col-7">Choose category</div>
                                <div class="col-5 text-right">
                                    <span class="btn btn-default btn-sm">
                                        <i class="dripicons-cross"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="row ml-2 mt-3">
                                @foreach ($lims_category_list as $category)
                                    <div class="col-md-3 category-img text-center"
                                        data-category="{{ $category->id }}">
                                        @if ($category->image)
                                            <img src="{{ url('images/category', $category->image) }}" />
                                        @else
                                            <img src="{{ url('images/product/zummXD2dvAtI.png') }}" />
                                        @endif
                                        <p class="text-center">{{ $category->name }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="brand mt-3">
                            <div class="row ml-2 mr-2 px-2">
                                <div class="col-7">Choose brand</div>
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
                                            <img src="{{ url('images/brand', $brand->image) }}" />
                                            <p class="text-center">{{ $brand->title }}</p>
                                        </div>
                                    @else
                                        <div class="col-md-3 brand-img" data-brand="{{ $brand->id }}">
                                            <img src="{{ url('images/product/zummXD2dvAtI.png') }}" />
                                            <p class="text-center">{{ $brand->title }}</p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <button class="btn btn-block btn-primary"
                                id="category-filter">{{ trans('file.category') }}</button>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-block btn-info"
                                id="brand-filter">{{ trans('file.Brand') }}</button>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-block btn-danger"
                                id="featured-filter">{{ trans('file.Featured') }}</button>
                        </div>
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
                                <tbody>
                                    @for ($i = 0; $i < ceil($product_number / 5); $i++)
                                        <tr>
                                            <td class="product-img sound-btn"
                                                title="{{ $lims_product_list[0 + $i * 5]->name }}"
                                                data-product="{{ $lims_product_list[0 + $i * 5]->code . ' (' . $lims_product_list[0 + $i * 5]->name . ')' }}">
                                                <img src="{{ url('images/product', $lims_product_list[0 + $i * 5]->base_image) }}"
                                                    width="100%" />
                                                <p>{{ $lims_product_list[0 + $i * 5]->name }}</p>
                                                <span>{{ $lims_product_list[0 + $i * 5]->code }}</span>
                                            </td>
                                            @if (!empty($lims_product_list[1 + $i * 5]))
                                                <td class="product-img sound-btn"
                                                    title="{{ $lims_product_list[1 + $i * 5]->name }}"
                                                    data-product="{{ $lims_product_list[1 + $i * 5]->code . ' (' . $lims_product_list[1 + $i * 5]->name . ')' }}">
                                                    <img src="{{ url('images/product', $lims_product_list[1 + $i * 5]->base_image) }}"
                                                        width="100%" />
                                                    <p>{{ $lims_product_list[1 + $i * 5]->name }}</p>
                                                    <span>{{ $lims_product_list[1 + $i * 5]->code }}</span>
                                                </td>
                                            @else
                                                <td style="border:none;"></td>
                                            @endif
                                            @if (!empty($lims_product_list[2 + $i * 5]))
                                                <td class="product-img sound-btn"
                                                    title="{{ $lims_product_list[2 + $i * 5]->name }}"
                                                    data-product="{{ $lims_product_list[2 + $i * 5]->code . ' (' . $lims_product_list[2 + $i * 5]->name . ')' }}">
                                                    <img src="{{ url('images/product', $lims_product_list[2 + $i * 5]->base_image) }}"
                                                        width="100%" />
                                                    <p>{{ $lims_product_list[2 + $i * 5]->name }}</p>
                                                    <span>{{ $lims_product_list[2 + $i * 5]->code }}</span>
                                                </td>
                                            @else
                                                <td style="border:none;"></td>
                                            @endif
                                            @if (!empty($lims_product_list[3 + $i * 5]))
                                                <td class="product-img sound-btn"
                                                    title="{{ $lims_product_list[3 + $i * 5]->name }}"
                                                    data-product="{{ $lims_product_list[3 + $i * 5]->code . ' (' . $lims_product_list[3 + $i * 5]->name . ')' }}">
                                                    <img src="{{ url('images/product', $lims_product_list[3 + $i * 5]->base_image) }}"
                                                        width="100%" />
                                                    <p>{{ $lims_product_list[3 + $i * 5]->name }}</p>
                                                    <span>{{ $lims_product_list[3 + $i * 5]->code }}</span>
                                                </td>
                                            @else
                                                <td style="border:none;"></td>
                                            @endif
                                            @if (!empty($lims_product_list[4 + $i * 5]))
                                                <td class="product-img sound-btn"
                                                    title="{{ $lims_product_list[4 + $i * 5]->name }}"
                                                    data-product="{{ $lims_product_list[4 + $i * 5]->code . ' (' . $lims_product_list[4 + $i * 5]->name . ')' }}">
                                                    <img src="{{ url('images/product', $lims_product_list[4 + $i * 5]->base_image) }}"
                                                        width="100%" />
                                                    <p>{{ $lims_product_list[4 + $i * 5]->name }}</p>
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
                <!-- product edit modal editarProducto-->
                <div id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true" class="modal fade text-left">
                    <div role="document" class="modal-dialog modal-lg" style="max-width: 70%">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 id="modal_header" class="modal-title"></h5>
                                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                        aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                            </div>
                            <div class="modal-body">
                                <form>
                                    <?php
                                    $tax_name_all[] = 'No Tax';
                                    $tax_rate_all[] = 0;
                                    foreach ($lims_tax_list as $tax) {
                                        $tax_name_all[] = $tax->name;
                                        $tax_rate_all[] = $tax->rate;
                                    }
                                    ?>
                                    <div class="row">
                                        <div class="form-group col-2">
                                            <label>{{ trans('file.Unit Price') }}</label>
                                            <input type="number" id="edit_unit_price_mod" name="edit_unit_price"
                                                onkeyup="mostrarPorcentaDesdeMontoManual()" class="form-control numkey"
                                                step="any" >
                                        </div>
                                        <div class="form-group col-1">
                                            <label>{{ trans('file.Quantity') }}</label>
                                            <input type="number" name="edit_qty" class="form-control numkey">
                                        </div>
                                        <div class="form-group col-6">
                                            <label>{{ __('file.Additional description') }}</label>
                                            <input type="text" name="edit_description" maxlength="800"
                                                class="form-control">
                                        </div>
                                        <div id="edit_unit" class="form-group col">
                                            <label>{{ trans('file.Product Unit') }}</label>
                                            <select name="edit_unit" class="form-control selectpicker">
                                            </select>
                                        </div>
                                        <div class="form-group col-3">
                                            <label>{{ trans('file.Tax Rate') }}</label>
                                            <select name="edit_tax_rate" class="form-control selectpicker">
                                                @foreach ($tax_name_all as $key => $name)
                                                    <option value="{{ $key }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="form-group col">
                                            <label>Precio final con descuento</label>
                                            <input id="descuento_unit_price" type="text" class="form-control"
                                                readonly>
                                        </div>
                                        <div class="form-group col">
                                            <label>{{ trans('file.Unit Discount') }}</label>
                                            <input id="edit_discount" name="edit_discount" type="number"
                                                value="0" class="form-control numkey" step="0.01">
                                        </div>
                                        <div class="form-group col">
                                            <label>Descuento por porcentaje (%)</label>
                                            <input id="porcentaje_discount" type="number" value="0"
                                                step="0.01" class="form-control numkey">
                                        </div>
                                    </div>
                                    <button type="button" name="update_btn"
                                        class="btn btn-primary mt-3">{{ trans('file.update') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- add customer modal -->
                <div id="addCustomer" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true" class="modal fade text-left">
                    <div role="document" class="modal-dialog">
                        <div class="modal-content">
                            <form id="frmAddCustomer" method="POST">
                                <div class="modal-header">
                                    <h5 id="exampleModalLabel" class="modal-title">
                                        {{ trans('file.Add Customer') }}</h5>
                                    <button type="button" data-dismiss="modal" aria-label="Close"
                                        class="close"><span aria-hidden="true"><i
                                                class="dripicons-cross"></i></span></button>
                                </div>
                                <div class="modal-body">
                                    <p class="italic">
                                        <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                                    </p>
                                    <div class="form-group">
                                        <label>{{ trans('file.Customer Group') }} *</strong> </label>
                                        <select required class="form-control selectpicker" name="customer_group_id">
                                            @foreach ($lims_customer_group_all as $customer_group)
                                                <option value="{{ $customer_group->id }}">
                                                    {{ $customer_group->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>{{ trans('file.name') }} *</strong> </label>
                                        <input type="text" name="name" required class="form-control">
                                    </div>
                                    <div class="row">
                                        <div class="form-group col">
                                            <label>{{ trans('file.Email') }}</label>
                                            <input type="email" name="email" placeholder="example@example.com"
                                                class="form-control">
                                        </div>
                                        <div class="form-group col">
                                            <label>{{ trans('file.Phone Number') }}</label>
                                            <input type="text" name="phone_number" class="form-control">
                                        </div>
                                    </div>
                                    {{-- Formularios para tipoDocumento + Razón social --}}
                                    @include('sale.partials-sale-tipo-documento')
                                    {{-- Formularios para tipoDocumento + Razón social --}}
                                    @if (in_array('pos_customer_advanced', $all_permission))
                                        <div class="row">
                                            <div class="form-group col">
                                                <label>{{ trans('file.Address') }}</label>
                                                <input type="text" name="address" class="form-control">
                                            </div>
                                            <div class="form-group col">
                                                <label>{{ trans('file.City') }}</label>
                                                <input type="text" name="city" class="form-control">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col">
                                                <label>Codigo Fijo (Opcional)</label>
                                                <input type="text" name="codigofijo" class="form-control"
                                                    placeholder="codigo fijo para servicios basicos">
                                            </div>
                                            <div class="form-group col">
                                                <label>Nro. Medidor (Opcional)</label>
                                                <input type="text" name="nro_medidor" class="form-control"
                                                    placeholder="nro. medidor para servicios basicos">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col">
                                                <label>Sucursal (Opcional)</strong> </label>
                                                <select required class="form-control selectpicker" name="sucursal_id">
                                                    @foreach ($lims_sucursal_all as $sucursal)
                                                        <option value="{{ $sucursal->sucursal }}">
                                                            {{ $sucursal->sucursal }} -
                                                            {{ $sucursal->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="form-group">
                                        <input type="hidden" name="pos" value="1">
                                        <input id="btnSaveCustomer" type="button"
                                            value="{{ trans('file.submit') }}" class="btn btn-primary">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- recent transaction modal -->
                <div id="recentTransaction" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true" class="modal fade text-left">
                    <div role="document" class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Recent Transaction') }}
                                    <div class="badge badge-primary">Últimas Ventas</div>
                                </h5>
                                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                        aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                            </div>
                            <div class="modal-body">
                                <ul class="nav nav-tabs" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" href="#sale-latest" role="tab"
                                            data-toggle="tab">{{ trans('file.Sale') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="#draft-latest" role="tab"
                                            data-toggle="tab">{{ trans('file.Draft') }}</a>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <div role="tabpanel" class="tab-pane show active" id="sale-latest">
                                        <div class="table-responsive">
                                            <table class="table" id="ventas-recientes-table">
                                                <thead>
                                                    <tr>
                                                        <th>{{ trans('file.date') }}</th>
                                                        <th>{{ trans('file.reference') }}</th>
                                                        <th>{{ trans('file.customer') }}</th>
                                                        <th>{{ trans('file.grand total') }}</th>
                                                        <th>{{ trans('file.action') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($recent_sale as $sale)
                                                        <?php $customer = DB::table('customers')->find($sale->customer_id); ?>
                                                        <?php $venta_facturada = DB::table('customer_sales')
                                                            ->where('sale_id', $sale->id)
                                                            ->first(); ?>
                                                        <tr>
                                                            <td>{{ date('d-m-Y', strtotime($sale->created_at)) }}</td>
                                                            @if (!empty($venta_facturada))
                                                                @if ($venta_facturada->estado_factura != null)
                                                                    <td>
                                                                        @if ($venta_facturada->nro_factura != null)
                                                                            {{ $sale->reference_no . ' [FACT#' . $venta_facturada->nro_factura . '|' . $venta_facturada->estado_factura . ']' }}
                                                                        @else
                                                                            {{-- la factura es manual --}}
                                                                            {{ $sale->reference_no . ' [FACT-' }} <div
                                                                                class="badge badge-info">Manual</div>
                                                                            {{ '#' . $venta_facturada->nro_factura_manual . '|' . $venta_facturada->estado_factura . ']' }}
                                                                        @endif
                                                                    </td>
                                                                    <td>{{ $venta_facturada->razon_social . ' - ' . $venta_facturada->email }}
                                                                    </td>
                                                                    <td>{{ $sale->grand_total }}</td>
                                                                    <td>
                                                                        <div class="btn-group">
                                                                            @if ($venta_facturada->estado_factura != 'CONTINGENCIA')
                                                                                <button type="button"
                                                                                    class="imprimir-factura-modal btn btn-info btn-sm"
                                                                                    data-id="{{ $sale->id }}"
                                                                                    data-toggle="modal"
                                                                                    data-target="#imprimir-factura-modal"
                                                                                    title="Print">
                                                                                    <i class="fa fa-print"></i>
                                                                                </button>
                                                                            @endif
                                                                        </div>
                                                                    </td>
                                                                @endif
                                                            @else
                                                                <td>{{ $sale->reference_no }}</td>
                                                                <td>{{ $customer->name }}</td>
                                                                <td>{{ $sale->grand_total }}</td>
                                                                <td>
                                                                    <div class="btn-group">
                                                                        @if (in_array('sales-edit', $all_permission))
                                                                            <a href="{{ route('sales.edit', $sale->id) }}"
                                                                                class="btn btn-success btn-sm"
                                                                                title="Edit"><i
                                                                                    class="dripicons-document-edit"></i></a>&nbsp;
                                                                        @endif
                                                                        @if (in_array('sales-delete', $all_permission))
                                                                            {{ Form::open(['route' => ['sales.destroy', $sale->id], 'method' => 'DELETE']) }}
                                                                            <button type="submit"
                                                                                class="btn btn-danger btn-sm"
                                                                                onclick="return confirmDelete()"
                                                                                title="Delete"><i
                                                                                    class="dripicons-trash"></i></button>
                                                                            {{ Form::close() }}
                                                                        @endif
                                                                    </div>
                                                                </td>
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div role="tabpanel" class="tab-pane fade" id="draft-latest">
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>{{ trans('file.date') }}</th>
                                                        <th>{{ trans('file.reference') }}</th>
                                                        <th>{{ trans('file.customer') }}</th>
                                                        <th>{{ trans('file.grand total') }}</th>
                                                        <th>{{ trans('file.action') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($recent_draft as $draft)
                                                        <?php $customer = DB::table('customers')->find($draft->customer_id); ?>
                                                        <tr>
                                                            <td>{{ date('d-m-Y', strtotime($draft->created_at)) }}</td>
                                                            <td>{{ $draft->reference_no }}</td>
                                                            <td>{{ $customer->name }}</td>
                                                            <td>{{ $draft->grand_total }}</td>
                                                            <td>
                                                                <div class="btn-group">
                                                                    @if (in_array('sales-edit', $all_permission))
                                                                        <a href="{{ url('sales/' . $draft->id . '/create') }}"
                                                                            class="btn btn-success btn-sm"
                                                                            title="Edit"><i
                                                                                class="dripicons-document-edit"></i></a>&nbsp;
                                                                    @endif
                                                                    @if (in_array('sales-delete', $all_permission))
                                                                        {{ Form::open(['route' => ['sales.destroy', $draft->id], 'method' => 'DELETE']) }}
                                                                        <button type="submit"
                                                                            class="btn btn-danger btn-sm"
                                                                            onclick="return confirmDelete()"
                                                                            title="Delete"><i
                                                                                class="dripicons-trash"></i></button>
                                                                        {{ Form::close() }}
                                                                    @endif
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- pre-sales transaction modal -->
                <div id="presaleTransaction" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true" class="modal fade text-left">
                    <div role="document" class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Pre Sale') }}
                                    <div class="badge badge-primary">{{ trans('file.latest') }} 10</div>
                                </h5>
                                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                        aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                            </div>
                            <div class="modal-body">
                                <div class="tab-content">
                                    <div class="table-responsive">
                                        <table id="table-presale" class="table">
                                            <thead>
                                                <tr>
                                                    <th class="not-exported"></th>
                                                    @if (!in_array('attentionshift', $all_permission))
                                                        <th>{{ trans('file.date') }}</th>
                                                    @endif
                                                    <th>{{ trans('file.reference presale') }}</th>
                                                    @if (in_array('attentionshift', $all_permission))
                                                        <th>Nro. Turno</th>
                                                        <th>{{ trans('file.Employee') }}</th>
                                                    @endif
                                                    <th>{{ trans('file.customer') }}</th>
                                                    <th>{{ trans('file.grand total') }}</th>
                                                    <th>{{ trans('file.action') }}</th>
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

                <!-- attentionshift selector before create presale -->
                <div id="selecturno-modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel"
                    aria-hidden="true" class="modal fade bd-example-modal-sm">
                    <div role="document" class="modal-dialog modal-dialog-centered modal-sm"
                        style="max-width: 400px;">
                        <div class="modal-content">

                            <div class="modal-header">
                                <h5 id="exampleModalLabel" class="modal-title">Seleccione
                                    {{ trans('file.Attention Shift') }}
                                </h5>
                                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                        aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                            </div>
                            <div class="modal-body">
                                <div class="col-md-12 form-group">
                                    <select id="turno_id" class="form-control selectpicker" name="turno_id" required
                                        data-live-search="true" data-live-search-style="contains"
                                        title="Seleccione Turno...">
                                    </select>
                                </div>
                                <div class="col-md-12 form-group">
                                    <button id="btn_updturno" class="btn btn-success"><i
                                            class="dripicons-plus"></i>{{ trans('file.Select Attention Shift') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- check begin pay credit modal -->
                <div id="detailCredit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true" class="modal fade text-left">
                    <div role="document" class="modal-dialog">
                        <div class="modal-content" style="width: 80%;">
                            <div class="modal-header">
                                <h5 id="exampleModalLabel" class="modal-title">Detalle de Venta por Pagar</h5>
                                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                        aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ trans('file.customer') }} </strong> </label>
                                            <input type="text" name="customer" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Limite de Creditos Bs.: </strong> </label>
                                            <input type="number" name="credits" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-12" style="text-align: center;">
                                        <div class="form-group" style="font-size: larger;">
                                            <Strong>Por Cobrar: <span id="totalpaymod"></span> - Credito : <span
                                                    id="totalwithcredit"></span> = Saldo: <span
                                                    id="totalgral"></span></Strong>
                                        </div>
                                        <br>
                                        <div class="form-group">
                                            <input type="hidden" name="pos" value="1">
                                            <a id="paycredit-btn" class="btn btn-primary"
                                                onclick="$('.payment-form').submit()">{{ trans('file.submit') }}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- form abono pay credit modal -->
                <div id="formpaydue" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true" class="modal fade text-left">
                    <div role="document" class="modal-dialog">
                        <div class="modal-content" style="width: 80%;">
                            <div class="modal-header">
                                <h5 id="exampleModalLabel" class="modal-title">Cliente - Abonar ventas por cobrar</h5>
                                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                        aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-12" style="text-align: center;">
                                        <div class="form-group" style="font-size: larger;">
                                            <Strong>Total Deuda: <span id="totaldue"></span> Bs. </Strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ trans('file.customer') }} </strong> </label>
                                            <input type="text" name="customer" class="form-control" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label>{{ trans('file.Amount') }} a Abonar *</strong> </label>
                                            <input type="number" name="amount_due" class="form-control"
                                                step="5" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Limite de Creditos Bs.: </strong> </label>
                                            <input type="number" name="credits" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-12" style="text-align: center;">
                                        <div class="form-group">
                                            <input type="hidden" name="id_customer" value="0">
                                            <a id="payduecredit-btn" class="btn btn-primary"
                                                onclick="customer_paydue()">{{ trans('file.submit') }}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- proforma complete before create proforma -->
                <div id="proforma-modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel"
                    aria-hidden="true" class="modal fade bd-example-modal-sm">
                    <div role="document" class="modal-dialog modal-dialog-centered modal-sm"
                        style="max-width: 400px;">
                        @include('layout.partials.spinner-ajax')
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 id="exampleModalLabel" class="modal-title">Confirmar
                                    {{ trans('file.Quotation') }}
                                </h5>
                                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                        aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col form-group">
                                        <label>{{ trans('file.Status') }}</label>
                                        <select id="quotation_statusModal" class="form-control"
                                            name="quotation_statusModal">
                                            <option value="1">{{ trans('file.Pending') }}</option>
                                            <option value="2">{{ trans('file.Sent') }}</option>
                                        </select>
                                    </div>
                                    <div class="col form-group">
                                        <label>{{ trans('file.date_valid') }}</label>
                                        <input type="date" id="valid_dateModal" class="form-control"
                                            name="valid_dateModal" />
                                    </div>
                                </div>
                                <div class="col-md-12 form-group">
                                    <label>{{ trans('file.Note') }}</label>
                                    <textarea id="noteModal" rows="2" name="noteModal" class="form-control"></textarea>
                                </div>
                                <div class="col-md-12 form-group">
                                    <button id="submitPro-btn" class="btn btn-success"><i
                                            class="dripicons-plus"></i>Generar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @include('sale.partials-sale-modal-tabla-coincidencia-nit')

    <style>
        /* Estilos para modal de pago #add-payment */
        #add-payment label {
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }
        
        #add-payment .form-control {
            font-size: 0.875rem;
            padding: 0.375rem 0.5rem;
        }
        
        #add-payment .selectpicker {
            font-size: 0.875rem;
        }
        
        #add-payment .nav-link {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
        }
        
        #add-payment p {
            font-size: 0.875rem;
        }
        
        #add-payment textarea {
            font-size: 0.875rem;
        }
        
        #add-payment .card-header h6,
        #add-payment .card-body small,
        #add-payment .form-text {
            font-size: 0.75rem;
        }
    </style>

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
        $('input[name="order_discount"]').val('');
        if (month < 10) {
            datef = day + "-0" + month + "-" + year;
        } else {
            datef = day + "-" + month + "-" + year;
        }
        var permission_turno = <?php echo json_encode(in_array('attentionshift', $all_permission)); ?>;
        var permission_proforma = <?php echo json_encode(in_array('quotes-add', $all_permission)); ?>;
        var permission_discount_item = <?php echo json_encode(in_array('pos_discount_item', $all_permission)); ?>;

        var baseUrl = "<?php echo url('/'); ?>";
        $("ul#sale").siblings('a').attr('aria-expanded', 'true');
        $("ul#sale").addClass("show");
        $("ul#sale #sale-pos-menu").addClass("active");
        if (changedate) {
            $("#date_format").val(sessionStorage.getItem('datesell'));
            $('input[name="date_sell"]').val(sessionStorage.getItem('datesell'));
        } else {
            $("#date_format").val($.datepicker.formatDate('dd-mm-yy', new Date()));
            sessionStorage.setItem("datesell", datef);
            $('input[name="date_sell"]').val(document.getElementById("date_format").value);
        }
        var date_sell = $('#date_format');
        date_sell.datepicker({
            format: "dd-mm-yyyy",
            autoclose: true,
            todayHighlight: true,
            startDate: '01/01/1996'
        }).on('changeDate', function(e) {
            sessionStorage.setItem('datesell', document.getElementById("date_format").value);
            $('input[name="date_sell"]').val(document.getElementById("date_format").value);
        });

        // $( document ).ready(function() {
        //     $("#lims_productcodeSearch").focus();
        // });

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
        var producto_data;

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

        // Descripcion adicional al producto
        var product_description = [];

        // temporary array
        var temp_unit_name = [];
        var temp_unit_operator = [];
        var temp_unit_operation_value = [];
        var tips = 0;
        var deposit = <?php echo json_encode($deposit); ?>;
        var product_row_number = <?php echo json_encode($lims_pos_setting_data->product_number); ?>;
        var tc = <?php echo json_encode($lims_pos_setting_data->t_c); ?>;
        var cantDecimal = <?php echo json_encode($lims_pos_setting_data->cant_decimal); ?>;
        var rowindex;
        var customer_group_rate;
        var row_product_price;
        var pos;
        var emp_temp = false;
        var max_monto_permitido = 0;
        var min_monto_price = 0;

        var keyboard_active = <?php echo json_encode($keybord_active); ?>;
        var role_id = <?php echo json_encode(\Auth::user()->role_id); ?>;
        var warehouse_id = <?php echo json_encode($biller_data->warehouse_id); ?>;
        var biller_id = <?php echo json_encode($biller_data->id); ?>;
        var coupon_list = <?php echo json_encode($lims_coupon_list); ?>;
        var currency = <?php echo json_encode($general_setting->currency); ?>;
        var warehouse_biller = <?php echo json_encode(sizeof($lims_warehouse_selects)); ?>;
        var benefit_desc = 0;

        // Bandera y variables utilizadas para venta tipo servicio basico
        var bandera_tasadignidad = false;
        var bandera_ley1886 = false;
        var porcentaje_tasadignidad = 0;
        var porcentaje_ley1886 = 0;
        // Bandera utlizadas para la venta de manera factura
        var bandera_confirmacion_nit = false;
        var bandera_puntoventa_contingencia = false;
        var bandera_servicio_sin = true;
        var tipo_evento_contigencia = 0;
        // Bandera para contingencia mayor a 5
        var bandera_evento_contingencia = false;
        var bandera_fecha_manual_cafc = new Date();
        var bandera_siat = 0;
        var bandera_siat = <?php if (session()->has('token_siat')) {
            echo json_encode(1);
        } else {
            echo json_encode(0);
        } ?>;

        // Bandera para modo proforma
        var modo_proforma = false;
        
        // Inicializar el toggle después de que el DOM esté listo
        $(document).ready(function() {
            // Asegurar que el toggle esté en OFF al iniciar
            $('#toggle-event-pro').bootstrapToggle('off');
            
            console.log('[INIT] Toggle inicializado. modo_proforma:', modo_proforma);
            
            // IMPORTANTE: Bootstrap Toggle NO dispara el evento 'change' estándar
            // Hay que sincronizar manualmente la variable con el estado del checkbox
            // cada vez que el usuario busque un producto
        });
        
        // Función helper para obtener el estado REAL del toggle
        // Esta función también sincroniza la variable modo_proforma
        function getModoProforma() {
            var estado = $('#toggle-event-pro').prop('checked');
            modo_proforma = estado; // Sincronizar la variable global
            console.log('[getModoProforma] Estado del toggle:', estado, '| Variable sincronizada');
            return estado;
        }
        
        $('#btn_modeOnline').hide();

        // Aseguramos que el valor inicial tenga el formato correcto
        const priceUnitInput = document.getElementById("edit_unit_price_mod");
        const descInput = document.getElementById("edit_discount");

        // Manejador que asegura que el valor tenga el formato correcto al modificarse
        priceUnitInput.addEventListener("change", function (event) {
        event.target.value = event.target.valueAsNumber.toFixed(cantDecimal);
        });

        descInput.addEventListener("change", function (event) {
            event.target.value = event.target.valueAsNumber.toFixed(cantDecimal);
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            error: function(xhr, status, error) {
                Swal.fire("Error", "Estado: " + status + " Error: " + error, "error");
            }
        });
        $(document).ready(function() {
            //getEstadoSIN();
            console.log("Ready POS!");
            console.log("SIAT:" + bandera_siat);
            verificaEstadoPV();
            @if (session()->has('printsale') && session()->has('saleid'))
                var id = '{{ session()->get('saleid') }}';
                var win = window.open('sales/gen_invoice/' + id, '_blank');
                win.focus();
                // No recargar automáticamente al cerrar la ventana de impresión
            @endif
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

            $('#lims_customerSearch').keyboard().autocomplete().addAutocomplete({
                // add autocomplete window positioning
                // options here (using position utility)
                position: {
                    of: '#lims_customerSearch',
                    my: 'top+30px',
                    at: 'right',
                    collision: 'flip'
                }
            });

        }

        if (role_id > 2) {
            $('#biller_id').addClass('noselect');
            if (warehouse_biller < 1)
                $('#warehouse_id').addClass('noselect');

            $('#account_id').prop('disabled', true);
            $('select[name=warehouse_id]').val(warehouse_id);
            $('select[name=biller_id]').val(biller_id);
            $('#div_account').remove();
        } else {
            $('#div_account').remove();
            $('select[name=warehouse_id]').val($("input[name='warehouse_id_hidden']").val());
            $('select[name=biller_id]').val($("input[name='biller_id_hidden']").val());
        }

        $('input[name=customer_id]').val($("input[name='customer_id_hidden']").val());
        $('input[name=customer_name]').val('{{ $customer_data->name }}');
        $('.selectpicker').selectpicker('refresh');

        var id_c = $("#customer_id").val();
        $.get('sales/getcustomergroup/' + id_c, function(data) {
            customer_group_rate = (data / 100);
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
                    Swal.fire("Información", "Por favor, seleccione cliente!", "info");

                } else if (!warehouse_id) {
                    $('#lims_productcodeSearch').val(temp_data.substring(0, temp_data.length - 1));
                    Swal.fire("Información", "Por favor, seleccione almacén!", "info");
                }
            });
        } else {
            $('#lims_productcodeSearch').on('input', function() {
                var customer_id = $('#customer_id').val();
                var warehouse_id = $('#warehouse_id').val();
                temp_data = $('#lims_productcodeSearch').val();
                if (!customer_id) {
                    $('#lims_productcodeSearch').val(temp_data.substring(0, temp_data.length - 1));
                    Swal.fire("Información", "Por favor, seleccione cliente!", "info");
                } else if (!warehouse_id) {
                    $('#lims_productcodeSearch').val(temp_data.substring(0, temp_data.length - 1));
                    Swal.fire("Información", "Por favor, seleccione almacén!", "info");
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
            $.get('sales/getproduct/' + category_id + '/' + brand_id, function(data) {
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
            $.get('sales/getproduct/' + category_id + '/' + brand_id, function(data) {
                populateProduct(data);
            });
        });

        $('#featured-filter').on('click', function() {
            $(".table-container").children().remove();
            $.get('sales/getfeatured', function(data) {
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

        function getProductCustomer() {

        }

        $('select[name="biller_id"]').on('change', function() {
            verificaEstadoPV();
        });

        function getProductsDataProforma() {

            var id = $('select[name="warehouse_id"]').val();
            var id_c = $("#customer_id").val();

            $.get('quotations/getproduct/' + id + '/' + id_c, function(data) {
                lims_product_array = [];
                product_list = product_qty = [];
                product_code = data[0];
                product_name = data[1];
                product_qty = data[2];
                product_type = data[3];
                product_id = data[4];
                product_list = data[5];
                qty_list = data[6];
                $.each(product_code, function(index) {
                    lims_product_array.push(product_code[index] + ' (' + product_name[index] + ')' +
                        ` - Precio: ${getPriceProduct(data, index)} - Stock: ${product_qty[index]}`
                    );
                });
            });
        }

        /** search product **/
        var lims_productcodeSearch = $('#lims_productcodeSearch');
        lims_productcodeSearch.autocomplete({
            source: function(request, response) {
                // Obtener el estado REAL del toggle en el momento de la búsqueda
                var modoProformaActual = getModoProforma();
                
                console.log('[Autocomplete] Estado del toggle AL BUSCAR:', modoProformaActual);
                
                var searchParams = { 
                    term: request.term,
                    id_customer: $('#customer_id').val(),  
                    id_warehouse: $('select[name="warehouse_id"]').val(),
                    modo_proforma: modoProformaActual.toString()
                };
                console.log('[Autocomplete] Enviando búsqueda:', searchParams);
                
                $.get("sales/search_product", searchParams, function(data) {
                    console.log('[Autocomplete] Productos recibidos:', data.length, 'productos');
                    response(data);
                });
            },
            response: function(event, ui) {
                var customer_id = $('#customer_id').val();
                var filter = [];
                if (ui.content.length == 1) {
                    var data = ui.content[0].code;
                    $(this).autocomplete("close");
                    filter.push(data);
                    filter.push(customer_id);
                    product_code.push(ui.content[0].code);
                    product_name.push(ui.content[0].name);
                    product_qty.push(ui.content[0].qty);
                    product_type.push(ui.content[0].type);
                    product_id.push(ui.content[0].id);
                    product_list.push(ui.content[0].product_list);
                    qty_list.push(ui.content[0].qty_list);
                    productSearch(filter);
                    return false;
                };
            },
            select: function(event, ui) {
                var customer_id = $('#customer_id').val();
                var filter = [];
                var data = ui.item.code;
                filter.push(data);
                filter.push(customer_id);
                product_code.push(ui.item.code);
                product_name.push(ui.item.name);
                product_qty.push(ui.item.qty);
                product_type.push(ui.item.type);
                product_id.push(ui.item.id);
                product_list.push(ui.item.product_list);
                qty_list.push(ui.item.qty_list);
                productSearch(filter);
                return false;
            },
        })
        .autocomplete("instance")._renderItem = function(ul, item) {
                return $("<li>")
                    .append("<div>" + item.code + " (" + item.name + ") - Precio:"
                     + item.price_value + " - Stock:" + item.qty +"</div>")
                    .appendTo(ul);
            };
        /** end search product **/

        /** search customer **/
        var lims_customerSearch = $('#lims_customerSearch');

        lims_customerSearch.autocomplete({
                source: "customer/customer_search",
                focus: function(event, ui) {
                    $("#lims_customerSearch").val(ui.item.name);
                    $("#customer_id").val(ui.item.id);
                    return false;
                },
                response: function(event, ui) {
                    if (ui.content.length == 1) {
                        $("#lims_customerSearch").val(ui.content[0].name);
                        $("#customer_id").val(ui.content[0].id);
                    }
                    return false;
                },
                select: function(event, ui) {
                    $("#lims_customerSearch").val(ui.item.name);
                    $("#customer_id").val(ui.item.id);
                    return false;
                }
            })
            .autocomplete("instance")._renderItem = function(ul, item) {
                return $("<li>")
                    .append("<div>" + item.name + " - " + item.valor_documento + " - " + item.codigofijo + "</div>")
                    .appendTo(ul);
            };

        function consultarClientePOS() {
            console.log("Cargando Cliente Visual...");
            var cliente_id = $('#customer_id').val();
            $.get('sales/getcliente/' + cliente_id, function(data) {
                $("#lims_customerSearch").val(data.name);
                $("input[name='sales_razon_social']").val(data.name);
                $("input[name='sales_email']").val(data.email);
                $("input[name='sales_valor_documento']").val(data.valor_documento);
                bandera_tasadignidad = data.is_tasadignidad;
                bandera_ley1886 = data.is_ley1886;
                porcentaje_ley1886 = data.porcentaje_ley1886;
                porcentaje_tasadignidad = data.porcentaje_tasadignidad;
            });
            console.log("tasaDignidad:" + bandera_tasadignidad + " - " + porcentaje_tasadignidad + "%");
            console.log("ley1886:" + bandera_ley1886 + " - " + porcentaje_ley1886 + "%");
        }

        /** end search customer **/


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
                Swal.fire("Advertencia", "La cantidad no puede ser menor que 0.1!", "warning");

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
                Swal.fire("Información", "Por favor, seleccione cliente!", "info");
            else if (!warehouse_id)
                Swal.fire("Información", "Por favor, seleccione almacén!", "info");
            else {
                var data = $(this).data('product');
                data = data.split(" ");
                $.get("sales/search_product", { 
                    term: data[0],
                    id_customer: $('#customer_id').val(),  
                    id_warehouse: $('select[name="warehouse_id"]').val(),  
                }, 
                function(res) {
                    filter.push(data[0]);
                    filter.push(customer_id);
                    product_code.push(res[0].code);
                    product_name.push(res[0].name);
                    product_qty.push(res[0].qty);
                    product_type.push(res[0].type);
                    product_id.push(res[0].id);
                    product_list.push(res[0].product_list);
                    qty_list.push(res[0].qty_list);
                    productSearch(filter);
                });
            }
        });

        //Delete product
        $("table.order-list tbody").on("click", ".ibtnDel", function(event) {
            var audio = $("#mysoundclip2")[0];
            audio.play();
            rowindex = $(this).closest('tr').index();
            product_price.splice(rowindex, 1);
            product_discount.splice(rowindex, 1);
            product_description.splice(rowindex, 1);
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

            if (parseFloat(edit_discount) >= parseFloat(edit_unit_price)) {
                Swal.fire("Error de Descuento", "Ingreso de descuento inválido!", "error");
                $('input[name="edit_discount"]').val("");
                return;
            }

            if (edit_qty < 1) {
                $('input[name="edit_qty"]').val(1);
                edit_qty = 1;
                Swal.fire("Advertencia", "La cantidad no puede ser menor que 0.1!", "warning");
            }

            var tax_rate_all = <?php echo json_encode($tax_rate_all); ?>;

            tax_rate[rowindex] = parseFloat(tax_rate_all[$('select[name="edit_tax_rate"]').val()]);
            tax_name[rowindex] = $('select[name="edit_tax_rate"] option:selected').text();

            product_discount[rowindex] = $('input[name="edit_discount"]').val();
            product_description[rowindex] = $('input[name="edit_description"]').val();
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
            console.log('El tipo de producto es => ' + product_type[pos]);
            if (product_type[pos] == 'digital') {
                product_price[rowindex] = parseFloat($('input[name="edit_unit_price"]').val());
            }
            if (product_type[pos] == 'combo') {
                product_price[rowindex] = parseFloat($('input[name="edit_unit_price"]').val());
            }
            checkQuantity(edit_qty, false);
        });

        $('button[name="order_discount_btn"]').on("click", function() {
            // El descuento adicional no debe ser mayor o igual al total
            var subtotal = parseFloat($('input[name="total_price"]').val());
            var order_discount = parseFloat($('input[name="order_discount"]').val());

            if (parseFloat(order_discount) >= parseFloat(subtotal)) {
                Swal.fire("Error de Descuento", "El descuento adicional no debe ser mayor o igual al total!",
                    "error");
                $('input[name="order_discount"]').val("");
                return;
            }
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
                        Swal.fire({
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
                        Swal.fire("Error", "Estado: " + textStatus + " Error: " + errorThrown, "error");
                    }
                });
            } else {
                Swal.fire("Error Cliente", "El monto de pago debe ser mayor a 0", "error");
            }
        }

        // Cada botón de venta pasa por aquí
        $(".payment-btn").on("click", function() {
            var audio = $("#mysoundclip2")[0];
            audio.play();
            var totalbs = $("#grand-total").text();
            var totalus = totalbs / tc;

            $('input[name="paid_amount"]').val($("#grand-total").text());
            $('input[name="paying_amount_us"]').val(0);
            $('input[name="paying_amount"]').val(0);
            $('.qc').data('initial', 1);
            alertaTablaItem_o_Empleado_vacio();
            refrescarMontos();
            hideDatosInputTexto();
        });

        $("#draft-btn").on("click", function() {
            $("#number_card").prop("required", false)
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
                Swal.fire("Advertencia de Servicio", "Complete el empleado de servicio del item", "warning");
            }
            if (rownumber < 0) {
                emp_temp = false;
                Swal.fire("Información de Items", "Por favor, inserte el producto para ordenar la tabla!",
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
                    Swal.fire("Advertencia de Servicio", "Complete el empleado de servicio del item", "warning");

                }
                if (rownumber < 0) {
                    emp_temp = false;
                    Swal.fire("Información de Items", "Por favor, inserte el producto para ordenar la tabla!",
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
                                    Swal.fire({
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
                                                var win = window.open('presales/gen_invoice/' + data.id, '_blank');
                                                win.focus();
                                                // No recargar automáticamente al cerrar la ventana de impresión
                                            } else {
                                                // No recargar automáticamente; simplemente mostrar mensaje
                                                // location.reload(true);
                                            }
                                        });
                                } else {
                                    Swal.fire("Mensaje", data.message, data.message_code);
                                    location.reload(true);
                                }
                            } else {
                                Swal.fire("Mensaje", "Error al guardar/actualizar intente de nuevo",
                                    "error");
                            }

                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            Swal.fire("Error", "Estado: " + textStatus + " Error: " + errorThrown,
                                "error");
                        }
                    });
                }
                //$('.payment-form').submit();
            }
        });

        $("#cobrar-btn").on("click", function() {
            $("#number_card").prop("required", false)
            blockAmounts();
            var audio = $("#mysoundclip2")[0];
            audio.play();
            const customerId = $('#customer_id').val();
            var customer = null;
            $.get('sales/getcliente/' + customerId, function(data) {
                customer = data;
                $('input[name="sale_status"]').val(4);
                //$('input[name="paid_amount"]').val(0);
                $('input[name="paying_amount"]').prop('required', false);
                $('input[name="paid_amount"]').prop('required', false);
                var rownumber = $('table.order-list tbody tr:last').index();

                if (emp_temp == false) {
                    $("#submit-btn").removeClass("disabled noselect");
                } else {
                    $("#submit-btn").addClass("disabled noselect");
                    Swal.fire("Advertencia de Servicio", "Complete el empleado de servicio del item",
                        "warning");
                }
                if (rownumber < 0) {
                    emp_temp = false;
                    Swal.fire("Información de Items",
                        "Por favor, inserte el producto para ordenar la tabla!",
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
                                Swal.fire("Advertencia de Crédito", "El Cliente : " + customer
                                    .name +
                                    " No puede recibir más ventas por pagar, Créditos Insuficientes",
                                    "warning");
                            } else {
                                $("#paycredit-btn").removeClass("disabled noselect");
                            }
                            $('#detailCredit').modal();
                        });
                    }
                }
            });
        });

        $("#abonar-btn").on("click", function() {
            $("#number_card").prop("required", false)
            blockAmounts();
            const customerId = $('#customer_id').val();
            var customer = null;
            var audio = $("#mysoundclip2")[0];
            audio.play();
            $.get('sales/getcliente/' + customerId, function(data) {
                customer = data;
                console.log(customer);
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

        });

        $("#gift-card-btn").on("click", function() {
            $("#number_card").prop("required", false)
            if (bandera_siat == 1)
                $('select[name="paid_by_id_select"]').val(27);
            else
                $('select[name="paid_by_id_select"]').val(3);

            $('.selectpicker').selectpicker('refresh');
            giftCard();
            bloqueoSegundoTabs();
        });

        $("#credit-card-btn").on("click", function() {
            $("#number_card").prop("required", true)
            if (bandera_siat == 1)
                $('select[name="paid_by_id_select"]').val(2);
            else
                $('select[name="paid_by_id_select"]').val(4);

            $('.selectpicker').selectpicker('refresh');
            creditCard();
            MPtarjeta();
            bloqueoSegundoTabs();
        });

        $("#cheque-btn").on("click", function() {
            $("#number_card").prop("required", false)
            if (bandera_siat == 1)
                $('select[name="paid_by_id_select"]').val(3);
            else
                $('select[name="paid_by_id_select"]').val(5);

            $('.selectpicker').selectpicker('refresh');
            cheque();
            MPcheque();
            bloqueoSegundoTabs();
        });

        $("#cash-btn").on("click", function() {
            $("#number_card").prop("required", false)
            $('select[name="paid_by_id_select"]').val(1);
            $('.selectpicker').selectpicker('refresh');
            MPefectivo();
            $('div.qc').show();
            // unblockAmounts();
            bloqueoSegundoTabs();
        });

        $("#proforma-btn").on("click", function() {
            $("#number_card").prop("required", false)
            $('select[name="quotation_statusModal"]').val(1);
            $('#date_validModal').val();
            $('#noteModal').val("");
            $('#proforma-modal').modal();
        });

        function bloqueoSegundoTabs() {
            if ($("input[name='bandera_factura_hidden']").val() == true) {
                $("#segundoTabContinue").addClass("disabled noselect");
                $('#myTab a[href="#segundoTab"]').addClass("disabled noselect");
            }
        }

        $("#deposit-btn").on("click", function() {
            $("#number_card").prop("required", false)
            if (bandera_siat == 1)
                $('select[name="paid_by_id_select"]').val(8);
            else
                $('select[name="paid_by_id_select"]').val(7);

            $('.selectpicker').selectpicker('refresh');
            deposits();
            MPdepositoCuenta();
            bloqueoSegundoTabs();
        });

        $("#qrsimple-btn").on("click", function() {
            $("#number_card").prop("required", false)
            if (bandera_siat == 1)
                $('select[name="paid_by_id_select"]').val(8);
            else
                $('select[name="paid_by_id_select"]').val(6);

            $('.selectpicker').selectpicker('refresh');
            MPdepositoCuenta();
        });


        $("#qrcash-btn").on("click", function() {
            $("#number_card").prop("required", false)
            if (bandera_siat == 1)
                $('select[name="paid_by_id_select"]').val(14);
            else
                $('select[name="paid_by_id_select"]').val(11);

            $('.selectpicker').selectpicker('refresh');
            MPefectivo();
            $('div.qc').show();
            MPdepositoCuenta();
            bloqueoSegundoTabs();
        });

        $("#submit-btn").on("click", function(e) {
            if ($("#number_card").prop('required') == true && ($("#number_card").val() == null || $("#number_card")
                    .val() == '' || $("#number_card").val().length < 19)) {
                Swal.fire('Error Validación', 'Campo Tarjeta de Credito/Debito Nulo ó Incompleto, Ingrese un valor',
                    'error');
                e.preventDefault();
                ocultarSpinner();
                return;
            }
            if ($("input[name='codigo_emision_hidden']").val() == 1) {
                // Emisión ONLINE
                $("#spinner-div").show();
                if ($("input[name='bandera_factura_hidden']").val() == true) {
                    //determinarVigenciaCUFDxBiller();
                    setValoresTipoDocumentoCasoEspecial();
                }
                if (bandera_puntoventa_contingencia != true && $("input[name='bandera_factura_hidden']").val() ==
                    true) {
                    getEstadoSIN();
                }
                if ($("input[name='bandera_factura_hidden']").val() == true && bandera_puntoventa_contingencia ==
                    false) {

                    // Caso ServiciosSIN
                    if (bandera_servicio_sin == false) {
                        ocultarSpinner();
                        e.preventDefault();
                        alertaPuntoVentaContingencia();
                        return;
                    };
                    // Caso NIT
                    var caso_especial = $("input[name='sales_caso_especial_hidden']").val();
                    var tipo_documento = $("input[name='sales_tipo_documento_hidden']").val();
                    if (caso_especial == 1 && tipo_documento == 5) {

                        sales_consultarNIT();
                        // si Codigo 0 => nit activo/inactivo; Código 1 => nit inexistente
                        console.log('botón confirmarVenta, operación NIT,' + bandera_nit +
                            ' [0:activo/inactivo; 1:inexistente]');
                        var bandera_nit = $("input[name='bandera_codigo_excepcion_hidden']").val();
                        if (bandera_nit != 0 && bandera_confirmacion_nit == false) {
                            e.preventDefault();
                            var nit_cliente = $('input[name=sales_valor_documento]').val();
                            console.log('La venta tiene NIT inexistente, mostrar alerta');
                            Swal.fire({
                                icon: 'warning',
                                title: 'NIT Inexistente',
                                text: 'El NIT ' + nit_cliente +
                                    ' no está registrado en Impuestos Nacionales',
                                showCancelButton: true,
                                confirmButtonText: 'Continuar la venta',
                                cancelButtonText: 'Cancelar',
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // el cliente confirma que desea continuar la venta con su NIT todo inválido
                                    console.log(
                                        'NIT: el cliente confirma que desea continuar la venta con su NIT todo inválido'
                                    );
                                    $("input[name='bandera_codigo_excepcion_hidden']").val(1);
                                    bandera_confirmacion_nit = true;
                                    console.log('La bandera de confirmación es => ' +
                                        bandera_confirmacion_nit);

                                } else if (result.dismiss === Swal.DismissReason.cancel) {
                                    // La venta de detiene, con opción de volver a digitar el NIT;
                                    console.log('NIT: el cliente cancela la venta');
                                    bandera_confirmacion_nit = false;
                                    console.log('La bandera de confirmación es => ' +
                                        bandera_confirmacion_nit);
                                }
                            });
                            console.log('La bandera de confirmación es => ' + bandera_confirmacion_nit);
                            if (bandera_confirmacion_nit != true) {
                                ocultarSpinner();
                                return;
                            };
                        }
                    }
                    // fin NIT

                }
                var consultaAlquiler = $("input[name='bandera_codigo_documento_sector_hidden']").val();
                if (consultaAlquiler == 2) {
                    if ($('input[name=glosa_periodo_facturado]').val().length === 0) {
                        ocultarSpinner();
                    }
                    if ($('input[name=balance_gift_card]').val() > 0) {
                        e.preventDefault();
                        Swal.fire('Factura Alquiler', 'No se permite pagar con giftcard', 'warning');
                        ocultarSpinner();
                    }
                }
                if ($('input[name=sales_razon_social]').val().length === 0 || $('input[name=sales_email]').val()
                    .length === 0 || $('input[name=sales_valor_documento]').val().length === 0) {
                    ocultarSpinner();
                }

                // En caso de evento con Cafc
                if (bandera_evento_contingencia) {
                    if ($('input[name=nro_factura_manual]').val().length === 0 || $('input[name=fecha_manual]')
                        .val().length === 0) {
                        ocultarSpinner();
                    }
                }
            }
        });

        $('#add-payment').on('hidden.bs.modal', function(e) {
            $(this).modal("hide");
            if (checkStatusIntervalId) clearInterval(checkStatusIntervalId);
            if (timerIntervalId) clearInterval(timerIntervalId);
            mostrarPanelMontosModal();
            // reset stepper to primer tab
            try {
                $('#myTab a[href="#primerTab"]').tab('show');
                if ($('#segundoTab').length) $('#submit-btn').text('Siguiente');
                else $('#submit-btn').text('Confirmar Venta');
            } catch (err) {}
        });

        function blockAmounts() {
            $('input[name="paying_amount"]').prop('readonly', true);
            $('input[name="paying_amount_us"]').prop('readonly', true);
            $('input[name="paid_amount"]').prop('readonly', true);
            $('div.qc').hide();
        }

        function unblockAmounts() {
            $('input[name="paying_amount"]').prop('readonly', false);
            $('input[name="paying_amount_us"]').prop('readonly', false);
            $('input[name="paid_amount"]').prop('readonly', false);
            $('div.qc').show();
        }


        $('#add-payment input[name="paying_amount"]').on("input", function() {
            change($(this).val(), $('input[name="paid_amount"]').val(), "BOB");
        });

        $('#add-payment input[name="paying_amount_us"]').on("input", function() {
            change($(this).val(), $('input[name="paid_amount"]').val(), "USD");
        });

        $('input[name="paid_amount"]').on("input", function() {
            if ($(this).val() > parseFloat($('input[name="paying_amount"]').val())) {
                Swal.fire("Advertencia de Pago",
                    "La cantidad de pago no puede ser más grande que la cantidad recibida",
                    "warning");
                $(this).val('');
            } else if ($(this).val() > parseFloat($('#grand-total').text())) {
                Swal.fire("Advertencia de Pago", "La cantidad de pago no puede ser más grande que el gran total",
                    "warning");
                $(this).val('');
            }

            change($('input[name="paying_amount"]').val(), $(this).val(), "BS");
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
                    $("#montoEfectivo").val($(this).data('amount').toFixed(2));
                    $('.qc').data('initial', 0);
                } else {
                    $("#montoEfectivo").val((parseFloat($("#montoEfectivo").val()) + $(this).data('amount'))
                        .toFixed(2));
                }
            } else {
                $("#montoEfectivo").val('0.00');

            }
            ValidacionMetodoPago();
        });

        $(document).on('click', '.qc-btn-us', function(e) {
            if ($(this).data('amount')) {

                var montoDolarizado = ($(this).data('amount').toFixed(2) * tc);
                if ($('.qc').data('initial')) {
                    $("#montoEfectivo").val(montoDolarizado);
                    $('.qc').data('initial', 0);
                } else {
                    $("#montoEfectivo").val((parseFloat($("#montoEfectivo").val()) + montoDolarizado).toFixed(2));
                }
            } else {
                $("#montoEfectivo").val('0.00');
            }
            ValidacionMetodoPago();
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
            
            // Obtener el estado REAL del toggle en el momento de la búsqueda
            var modoProformaActual = getModoProforma();
            
            console.log('[productSearch] Iniciando búsqueda:', {
                data: data,
                modo_proforma_toggle: modoProformaActual,
                isCourtesy: isCourtesy
            });
            $.ajax({
                type: 'GET',
                url: 'sales/lims_product_search',
                data: {
                    data: data,
                    modo_proforma: modoProformaActual.toString()
                },
                success: function(data) {
                    console.log('[productSearch] Datos del producto recibidos:', data);
                    var flag = 1;
                    producto_data = data;
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
                            $.get('sales/getstockprofinish/' + data[1] + '/' + alm, function(res) {
                                //console.log(res);
                                qty_list2 = res[1];
                                if (modo_proforma == false && res[0] === true) {
                                    sale_qty = 1;
                                    Swal.fire("Error de Stock!",
                                        "No hay stock disponible! en uno o más insumos", "error");
                                } else {
                                    addNewProduct(data, employee, presale);
                                }
                            }).catch((error) => {
                                Swal.fire("Error de Insumos!",
                                    "No hay stock disponible! en uno o más insumos", "error");
                            });
                        } else {
                            if (data[2] <= 0) {
                                Swal.fire({
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
                                                function(res) {
                                                    //console.log(res);
                                                    qty_list2 = res[1];
                                                    if (modo_proforma == false && res[0] === true) {
                                                        sale_qty = 1;
                                                        Swal.fire("Error de Stock!",
                                                            "No hay stock disponible! en uno o más insumos",
                                                            "error");
                                                    } else {
                                                        addNewProduct(data, employee, presale);
                                                    }
                                                }).catch((error) => {
                                                Swal.fire("Error de Insumos!",
                                                    "No hay stock disponible! en uno o más insumos",
                                                    "error");
                                            });
                                        } else {
                                            Swal.fire("Error al Ingresar",
                                                "Monto ingresado inválido, intente nuevamente!", "error"
                                            );
                                        }
                                    });
                            } else {
                                $.get('sales/getstockprofinish/' + data[1] + '/' + alm, function(res) {
                                    //console.log(res);
                                    qty_list2 = res[1];
                                    if (modo_proforma == false && res[0] === true) {
                                        sale_qty = 1;
                                        Swal.fire("Error de Stock!",
                                            "No hay stock disponible! en uno o más insumos", "error"
                                        );

                                    } else {
                                        addNewProduct(data, employee, presale);
                                    }
                                }).catch((error) => {
                                    Swal.fire("Error de Insumos!",
                                        "No hay stock disponible! en uno o más insumos", "error");
                                });
                            }
                        }
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    Swal.fire("Error", "Estado: " + textStatus + " Error: " + errorThrown, "error");
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
            cols +=
                '<td class="col-sm-4 product-title" style="text-align: start;"><button type="button" class="edit-product btn btn-link" style="font-size: smaller; white-space: normal;"><strong>' +
                data[0] + '</strong></button> [' + data[1] + ']' +
                '<div class="input-group div_emp_' + data[1] + pre + '"><select id="employee_id_' + data[1] + pre +
                '" name="employee_id" class="selectpicker form-control courtesy-select" data-live-search="true" data-live-search-style="contains"><option value="0">Seleccione Personal...</option></select></div> <input type="hidden" id="service_' +
                data[1] + pre + '" class="service-pro" name="service_kind" value="false"/></td>';
            cols += '<td class="col-sm-2 product-price" style="text-align: end;"></td>';
            cols +=
                '<td class="col-sm-3"><div class="input-group"><span class="input-group-btn"><button type="button" class="btn btn-default minus"><span class="dripicons-minus"></span></button></span><input type="text" name="qty[]" class="form-control qty numkey input-number" value="1" step="0.01" required><span class="input-group-btn">' +
                '<button type="button" class="btn btn-default plus"><span class="dripicons-plus"></span></button></span></div><div class="input-group"><select id="cortesia_id_' +
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
            cols += '<input type="hidden" class="presale" name="presale[]" value="' + pre + '"/>';
            cols += '<input type="hidden" class="basic-service" name="basicservice[]" value="' + data[13] + '"/>';
            cols += '<input type="hidden" class="product-description" name="product_description[]"/>';

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
            product_description.push('');
            tax_rate.push(parseFloat(data[3]));
            tax_name.push(data[4]);
            tax_method.push(data[5]);
            unit_name.push(data[6]);
            unit_operator.push(data[7]);
            unit_operation_value.push(data[8]);
            if (data[11] != null && data[13] == false) {
                if (data[11].length > 0) {
                    addOptions(`cortesia_id_${data[1]}`, data[11], 1);
                    //add a tabla cuando selecciona
                    $(`#cortesia_id_${data[1]}`).on("change", function() {
                        var filter = [];
                        var customer_id = $('#customer_id').val();
                        if (emp_temp == false) {
                            $.get("sales/search_product", { 
                                term: $(this).val(),
                                id_customer: customer_id,  
                                id_warehouse: $('select[name="warehouse_id"]').val(),  
                            }, 
                            function(res) {
                                filter.push($(this).val());
                                filter.push(customer_id);
                                product_code.push(res[0].code);
                                product_name.push(res[0].name);
                                product_qty.push(res[0].qty);
                                product_type.push(res[0].type);
                                product_id.push(res[0].id);
                                product_list.push(res[0].product_list);
                                qty_list.push(res[0].qty_list);
                                productSearch(filter, true);
                            });
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

            if (data[12] != null && data[13] == false) {
                $(`#service_${data[1]+pre}`).val('true');
                if (data[12].length > 0) {
                    addOptions(`employee_id_${data[1]+pre}`, data[12], 2);
                    //add a tabla cuando selecciona
                    emp_temp = true;
                    $(`#employee_id_${data[1]+pre}`).on("change", function() {
                        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.employee-id').val($(
                            this).val());
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
            if (modo_proforma == false && emp_temp == true) {
                Swal.fire("Advertencia de Servicio", "Seleccione el empleado de servicio antes de ingresar otro item",
                    "warning");
            }
        }

        // editarProducto
        function edit() {
            
            var row_product_name_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find(
                '.product-title').text();
            var title = row_product_name_code.split(']');
            $('#modal_header').text(title[0] + ']');

            var qty = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val();
            $('input[name="edit_qty"]').val(qty);

            $('input[name="edit_discount"]').val(parseFloat(product_discount[rowindex]).toFixed(cantDecimal));
            $('input[name="edit_description"]').val(product_description[rowindex]);

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
                $.each(temp_unit_name, function(key, value) {
                    $('select[name="edit_unit"]').append('<option value="' + key + '">' + value + '</option>');
                });
                $("#edit_unit").show();
            } else {
                $("#edit_unit").hide();
            }
            $('input[name="edit_unit_price"]').val(product_price[rowindex].toFixed(cantDecimal));
            var montoFinal = (row_product_price - 0.001);
            $('input[name="edit_discount"]').attr({
                "min": 0,
                "max": montoFinal,
            });

            console.log("permission_discount_item " + permission_discount_item);
            if (permission_discount_item) {
                //$('input[name="edit_unit_price"]').prop('readOnly', true);
                var row_product_id = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-id')
                    .val();
                $.get('products/getprice/' + row_product_id + '/min', function(res) {
                    max_monto_permitido = res.price;
                    min_monto_price = res.price_default;
                    max_monto_permitido = (parseFloat($('input[name="edit_unit_price"]').val()) -
                        max_monto_permitido);
                    $('#editModal').modal('show');
                }).catch((error) => {
                    Swal.fire("Error de lista de Precios!",
                        "Error al consulta el precio MAX/MIN del producto, Contacte con Soporte", "error");
                });
            } else {
                //$('input[name="edit_unit_price"]').prop('readOnly', false);
                max_monto_permitido = (parseFloat($('input[name="edit_unit_price"]').val()) - 0.001);
                //min_monto_price = max_monto_permitido;
                min_monto_price = 0.001;
                $('#editModal').modal('show');
            }
            console.log("monto max: " + max_monto_permitido);
            console.log("monto min: " + min_monto_price);

            mostrarPorcentaDesdeMontoManual();
            $('.selectpicker').selectpicker('refresh');
        }

        function mostrarPorcentaDesdeMontoManual() {
            var montoDescuentoManual = parseFloat($('input[name="edit_discount"]').val());
            var montoDescuentoPorcentaje = (montoDescuentoManual * 100) / parseFloat($('input[name="edit_unit_price"]')
                .val());
            $("#porcentaje_discount").val(montoDescuentoPorcentaje.toFixed(2));
            var montoTotalConDescuento = parseFloat($('input[name="edit_unit_price"]').val()) - montoDescuentoManual;
            $("#descuento_unit_price").val(montoTotalConDescuento.toFixed(cantDecimal));

        }

        $(document).on('blur change', '#edit_unit_price_mod', function(e) {
            console.log("min monto:" + min_monto_price + " | current monto:" + $(this).val());
            if (min_monto_price > parseFloat($(this).val())) {
                var value = min_monto_price;
                $(this).val(value);
                Swal.fire('Advertencia', 'Monto mínimo permitido ' + value, "warning");
                mostrarPorcentaDesdeMontoManual();
            }
        });

        $(document).on('keyup change', '#edit_discount', function(e) {
            if (parseFloat($(this).val()) > max_monto_permitido) {
                var value = max_monto_permitido;
                $(this).val(value)
                Swal.fire('Advertencia', 'Monto máximo permitido ' + value, "warning")
            }
            if (parseFloat($(this).val()) < 0) {
                $(this).val('0');
                Swal.fire('error', 'Monto mínimo permitido 0')
            }
            mostrarPorcentaDesdeMontoManual();
        });

        function couponDiscount() {
            var rownumber = $('table.order-list tbody tr:last').index();
            if (rownumber < 0) {
                emp_temp = false;
                Swal.fire("Información de Items", "Por favor, inserte el producto para ordenar la tabla!", "info");

            } else if ($("#coupon-code").val() != '') {
                valid = 0;
                $.each(coupon_list, function(key, value) {
                    if ($("#coupon-code").val() == value['code']) {
                        valid = 1;
                        todyDate = <?php echo json_encode(date('Y-m-d')); ?>;
                        if (parseFloat(value['quantity']) <= parseFloat(value['used']))
                            Swal.fire("Error de Cupón!", "Este cupón ya no está disponible", "error");

                        else if (todyDate > value['expired_date'])
                            Swal.fire("Error de Cupón!", "Este cupón esta expirado", "error");

                        else if (value['type'] == 'fixed') {
                            if (parseFloat($('input[name="grand_total"]').val()) >= value['minimum_amount']) {
                                $('input[name="grand_total"]').val($('input[name="grand_total"]').val() - value[
                                    'amount']);
                                $('#grand-total').text(parseFloat($('input[name="grand_total"]').val()).toFixed(2));
                                if (!$('input[name="coupon_active"]').val())
                                    Swal.fire("Descuento Aplicado!", "¡Felicidades! Tú tienes " + value['amount'] +
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
                                Swal.fire("Error de Descuento!",
                                    "¡El gran total no es suficiente para el descuento! Requerido " + value[
                                        'minimum_amount'] + ' ' + currency, "error");

                        } else {
                            var grand_total = $('input[name="grand_total"]').val();
                            var coupon_discount = grand_total * (value['amount'] / 100);
                            grand_total = grand_total - coupon_discount;
                            $('input[name="grand_total"]').val(grand_total);
                            $('#grand-total').text(parseFloat(grand_total).toFixed(2));
                            if (!$('input[name="coupon_active"]').val())
                                Swal.fire("Descuento Aplicado!", "¡Felicidades! Tú tienes " + value['amount'] +
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
                    Swal.fire("Error de Cupón!", "Código de cupón inválido", "error");

            }
        }

        function checkQuantity(sale_qty, flag) {
            var row_product_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-code')
                .val();
            console.log("producto code: " + row_product_code);
            console.log(product_code);
            console.log(product_list);
            pos = product_code.indexOf(row_product_code);
            var alm = $('select[name="warehouse_id"]').val();
            console.log("producto index code: " + pos);
            if (modo_proforma == false && pos == -1) { //no existe producto
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').remove();
                Swal.fire("Error de Stock!", "No hay stock disponible!", "error");
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
                if (modo_proforma == false && total_qty > parseFloat(product_qty[pos])) {
                    Swal.fire("Advertencia de Stock!", "Cantidad excede el stock disponible!", "warning");
                    if (flag) {
                        sale_qty = 1;
                        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(sale_qty);
                        checkQuantity(sale_qty, true);
                    } else {
                        edit();
                        return;
                    }
                }
            } else if (product_type[pos] == 'combo' && modo_proforma == false) {
                child_id = producto_data[14].split(',');
                child_qty = producto_data[15].split(',');
                console.log("child_id : " + child_id + " - child_qty : " + child_qty);
                $.get('sales/getstockprofinish/' + row_product_code + '/' + alm, function(res) {
                    $(child_id).each(function(index) {
                        console.log("index: " + index);
                        console.log(sale_qty * child_qty[index] + " - " + res[1][index].qty + " - " + res[1]
                            [index].type);
                        if (modo_proforma == false && res[1][index].type != 'digital' && parseFloat(
                                sale_qty * child_qty[index]) >
                            res[1][index].qty) {
                            sale_qty = 1;
                            Swal.fire("Advertencia de Stock!",
                                "Cantidad excede el stock disponible! de uno o más productos, alerta en producto : " +
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
                    Swal.fire("Error de Productos!", "No hay stock disponible! en uno o más productos", "error");
                });
            } else if (product_type[pos] == 'producto_terminado' && modo_proforma == false) {
                child_id = producto_data[14].split(',');
                child_qty = producto_data[15].split(',');
                var sold = false;
                console.log("child_id : " + child_id + " - child_qty : " + child_qty);
                $.get('sales/getstockprofinish/' + row_product_code + '/' + alm, function(res) {
                    $(child_id).each(function(index) {
                        console.log(sale_qty * child_qty[index] + " - " + res[1][index].qty);
                        if (modo_proforma == false && parseFloat(sale_qty * child_qty[index]) > res[1][
                                index
                            ].qty) {
                            sold = true;
                            sale_qty = 1;
                            Swal.fire("Advertencia de Stock!",
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
                    });
                }).catch((error) => {
                    Swal.fire("Error de Insumos!", "No hay stock disponible! en uno o más insumos", "error");
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
                deleteItem();
            }
            calculateRowProductData(sale_qty);

        }

        function calculateRowProductData(quantity) {
            if (product_type[pos] == 'standard')
                unitConversion();
            else
                row_product_price = product_price[rowindex];

            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-description').val(
                product_description[rowindex]);
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.discount-value').val((product_discount[
                rowindex] * quantity).toFixed(2));
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-rate').val(tax_rate[rowindex]
                .toFixed(2));
            var is_basicservice = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.basic-service')
                .val();
            row_product_price = row_product_price - benefit_desc;
            console.log("row_product_price: " + row_product_price + " - benefit_desc: " + benefit_desc);
            var montoTasaDignidad = 0;
            var montoLey1886 = 0;
            if (is_basicservice == 1) {
                if (bandera_tasadignidad == 1) {
                    montoTasaDignidad = (porcentaje_tasadignidad * row_product_price) / 100;
                }
                if (bandera_ley1886 == 1) {
                    montoLey1886 = (porcentaje_ley1886 * row_product_price) / 100;
                }
                $('input[name="montoLey1886_hidden"]').val(montoLey1886);
                $('input[name="montoTasaDignidad_hidden"]').val(montoTasaDignidad);
                console.log("montoLey1886: " + montoLey1886);
                console.log("montoTasaDignidad: " + montoTasaDignidad);
            }

            if (tax_method[rowindex] == 1) {
                var net_unit_price = row_product_price - product_discount[rowindex];
                var tax = net_unit_price * quantity * (tax_rate[rowindex] / 100);
                var sub_total = (net_unit_price * quantity) + tax;

                if (parseFloat(quantity))
                    var sub_total_unit = sub_total / quantity;
                else
                    var sub_total_unit = sub_total;
                if (is_basicservice == 1) {
                    sub_total_unit = sub_total_unit - montoTasaDignidad - montoLey1886;
                    net_unit_price = net_unit_price - montoTasaDignidad - montoLey1886;
                    sub_total = sub_total - montoTasaDignidad - montoLey1886;
                }

                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.net_unit_price').val(net_unit_price
                    .toFixed(cantDecimal));
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

                if (is_basicservice == 1) {
                    sub_total_unit = sub_total_unit - montoTasaDignidad - montoLey1886;
                    net_unit_price = net_unit_price - montoTasaDignidad - montoLey1886;
                    sub_total = sub_total - montoTasaDignidad - montoLey1886;
                }
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.net_unit_price').val(net_unit_price
                    .toFixed(cantDecimal));
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


            if (tip_cost == 0) {
                //tips = Math.abs(tips) * -1;
                tips = tip_cost;
            } else {
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



        function cheque() {
            $("#MP_cheque").show();
        }

        function creditCard() {
            // const customerId = $('#customer_id').val();
            $("#MP_tarjeta").show();
        }

        function deposits() {
            // if ($('input[name="paid_amount"]').val() >= deposit[$('#customer_id').val()]) {
            //     alert('Monto excede el depósito del cliente! Depósito del cliente : ' + deposit[$('#customer_id').val()]);
            // }
            // $('#add-payment select[name="gift_card_id_select"]').attr('required', false);
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
                product_description.pop();
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
            $('input[name="benefits_value"]').val(0);
            $("#benefits").text(0.00);
            tips = 0;
            benefit_desc = 0;
            calculateTotal();
        }

        function confirmCancel() {
            var audio = $("#mysoundclip2")[0];
            audio.play();

            Swal.fire({
                    title: "Está seguro de querer cancelar?",
                    text: "Esta acción limpiará los item de la tabla!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((res) => {
                    if (res) {
                        cancel($('table.order-list tbody tr:last').index());
                        $('input[name="presale_id"]').val(0);
                        $("input[name=attentionshift_id]").val(0);
                        $("input[name=attentionshift_id]").val(0);
                    } else {
                        return false;
                    }
                });
        }

        $(document).on('submit', '.payment-form', function(e) {
            var rownumber = $('table.order-list tbody tr:last').index();
            if (rownumber < 0) {
                emp_temp = false;
                Swal.fire("Información", "Por favor, inserte el producto para ordenar la tabla!", "info");

                e.preventDefault();
                ocultarSpinner();
            } else if (parseFloat($('input[name="paying_amount"]').val()) < parseFloat($(
                    'input[name="paid_amount"]').val())) {
                Swal.fire("Información", "La cantidad de pago no puede ser más grande que la cantidad recibida",
                    "info");
                e.preventDefault();
                ocultarSpinner();
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
                    msg = new swal("Advertencia", "Se detecto datos de una preventa anterior se limpiara la venta.",
                        'info');
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
                    msg = new swal("Información", "Por favor, seleccione cliente!", "info");
                else if (!warehouse_id)
                    msg = new swal("Información", "Por favor, seleccione almacén!", "info");
                else {
                    list_item.forEach(element => {
                        var filter = [];
                            $.get("sales/search_product", { 
                                term: element.code,
                                id_customer: customer_id,  
                                id_warehouse: $('select[name="warehouse_id"]').val(),  
                            }, 
                            function(res) {
                                filter.push(element.code);
                                filter.push(customer_id);
                                product_code.push(res[0].code);
                                product_name.push(res[0].name);
                                product_qty.push(res[0].qty);
                                product_type.push(res[0].type);
                                product_id.push(res[0].id);
                                product_list.push(res[0].product_list);
                                qty_list.push(res[0].qty_list);
                                productSearch(filter, false, element.employee_id, element);
                            });
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
                    Swal.fire('Asignación', "Sin turnos disponibles, intente de nuevo!", "error");
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
                if ($("input[name='codigo_emision_hidden']").val() == 1) {
                    // Emisión_ONLINE
                    mostrarLabelContingencia();
                    getEstadoSIN();
                    determinarVigenciaCUFDxBiller();
                }
                $("#submit-btn").removeClass("disabled noselect");
                $('#myTab a[href="#segundoTab"]').tab('show');
            });

            $('#myTab a[href="#segundoTab"]').click(function(e) {
                e.preventDefault();
                if ($("input[name='codigo_emision_hidden']").val() == 1) {
                    // Emisión ONLINE
                    mostrarLabelContingencia();
                    getEstadoSIN();
                    determinarVigenciaCUFDxBiller();
                }
                $("#submit-btn").removeClass("disabled noselect");
            });

            $('#myTab a[href="#tercerTab"]').click(function(e) {
                e.preventDefault();
                if ($("input[name='codigo_emision_hidden']").val() == 1) {
                    // Emisión ONLINE
                    //mostrarLabelContingencia();
                    //getEstadoSIN();
                    determinarVigenciaCUFDxBiller();
                }
                $("#submit-btn").removeClass("disabled noselect");
            });

            $('#adsContinue').click(function(e) {
                e.preventDefault();
                $('#myTab a[href="#placementPanel"]').tab('show');
            });
        })

        // Funcion para el numero de tarjeta de credito/debito
        const formulario = document.querySelector('#number_card');

        formulario.addEventListener('keyup', (e) => {
            let valorInput = e.target.value;

            formulario.value = valorInput
                // Eliminamos espacios en blanco
                .replace(/\s/g, '')
                // Eliminar las letras
                .replace(/\D/g, '')
                // Ponemos espacio cada cuatro numeros
                .replace(/([0-9]{4})/g, '$1 ')
                // Elimina el ultimo espaciado
                .trim();

            if (valorInput == '') {
                $('.badge-card').text(' ');
            }

            if (valorInput[0] == 4) {
                $('.badge-card').text('Visa ');
            } else if (valorInput[0] == 5) {
                $('.badge-card').text('Master Card ');
            } else {
                $('.badge-card').text(' ');
            }

        });

        // Funciones para determinar los servicios de Impuestos Nacionales
        function getEstadoSIN() {
            var url = '{{ route('estado_servicios_sin') }}';
            console.log('funcion getEstadoSIN, para determinar los Servicios de Impuestos Nacionales');
            $('#label_contingencia').hide();
            $('#btn_modeOnline').hide();
            $.ajax({
                url: url,
                type: "GET",
                async: false,
                success: function(data) {
                    if (data == true) {
                        console.log('Servicios SIN en línea => ' + data);
                        bandera_servicio_sin = true;
                        /** Mostrar Boton para Modo Online y enviar paquetes contingencia **/
                        console.log(bandera_puntoventa_contingencia + '|' + tipo_evento_contigencia)
                        if (bandera_puntoventa_contingencia == true && tipo_evento_contigencia == 2) {
                            $('#btn_modeOnline').show();
                        }
                    } else {
                        console.log('Falso, los servicios no están funcionando, SIN caído => ' + data);
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
                        Swal.fire({
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
                        consultarEventoContingencia();
                    } else {
                        $('#label_contingencia').hide();
                        $('#toggle-event-mode').addClass("disabled noselect");
                        bandera_puntoventa_contingencia = false;
                    }
                }
            });

        }
        // Fin Funciones para determinar los servicios de Impuestos Nacionales

        function consultarEventoContingencia() {
            var id = $('select[name=biller_id]').val();
            var url = '{{ route('get_tipo_evento_contingencia', ':id') }}';
            url = url.replace(':id', id);

            $.ajax({
                url: url,
                type: "GET",
                success: function(data) {
                    tipo_evento_contigencia = data;
                    if (data >= 5) {
                        // El evento registrado es especial, mostrar los input
                        console.log('El evento de contingencia del punto de venta es => ' + data)
                        $("#evento_contingencia_div").show();
                        $('input[name=nro_factura_manual]').prop("required", true);
                        $('input[name=fecha_manual]').prop("required", true);
                        bandera_evento_contingencia = true;
                        consultarMinimoFechaManualCafc();
                    } else {
                        // ocultar los input
                        console.log('El evento es menor 5, evento dado => ' + data)
                        $("#evento_contingencia_div").hide();
                        $('input[name=nro_factura_manual]').prop("required", false);
                        $("input[name='nro_factura_manual']").val("");
                        $('input[name=fecha_manual]').prop("required", false);
                        $("input[name='fecha_manual']").val("");
                        bandera_evento_contingencia = false;
                    }
                }
            });
        }

        function registraEventoContingencia() {
            var id = $('select[name=biller_id]').val();
            var url = '{{ route('contingencia.registrar-evento-auto', ':id') }}';
            url = url.replace(':id', id);

            $.ajax({
                url: url,
                type: "GET",
                success: function(data) {
                    $("#spinner-contigencia-div").hide();
                    $("#submit-btn").removeClass("disabled noselect");
                    if (data.estado == true) {
                        Swal.fire("Mensaje", " " + data.mensaje, 'success');
                        mostrarLabelContingencia();
                    } else {
                        Swal.fire("Mensaje", " " + data.mensaje, 'warning');
                        $('#toggle-event-mode').prop('checked', false);
                        $("#toggle-event-mode").prop('disabled', false);
                        bandera_evento_contingencia = false;
                    }
                }
            });
        }

        function consultarMinimoFechaManualCafc() {

            var biller_id = $('select[name=biller_id]').val();
            var documento_sector_id = $("input[name='bandera_codigo_documento_sector_hidden']").val();
            var url_data = '{{ route('consultar_fecha_manual_cafc') }}';

            $.ajax({
                url: url_data,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    biller_id: biller_id,
                    nro_documento_sector: documento_sector_id
                },
                success: function(data) {
                    // Según la fecha, debemos colocarla como min en el input data-time
                    // la data es un fecha, se utilizará como min
                    bandera_fecha_manual_cafc = data;
                    $('input[name=fecha_manual]').val("");
                    //$('input[name=fecha_manual]').attr("min", data);

                }
            });
        }

        function revisarFechaManualCafc() {
            var fecha_seleccionada = $('input[name=fecha_manual]').val();
            /*if (Date.parse(fecha_seleccionada) < Date.parse(bandera_fecha_manual_cafc)) {
                $('input[name=fecha_manual]').val("");
                var fecha_literal = new Date(Date.parse(bandera_fecha_manual_cafc));
                Swal.fire("Error en la fecha", "La fecha ingresada no puede ser menor a " + fecha_literal.toLocaleString(),
                    'warning');
            }*/
        }

        function determinarVigenciaCUFDxBiller() {
            var id = $('select[name=biller_id]').val();
            var url = '{{ route('estado_vigencia_cufd', ':id') }}';
            url = url.replace(':id', id);

            $.ajax({
                url: url,
                type: "GET",
                async: false,
                success: function(data) {
                    console.log('determinarVigenciaCUFD del Biller => ' + id + ', respuesta => ' + data +
                        '  [1:ok, 0:not]');
                    /*if (data == true) {
                        // Por verdad, la hora actual es menos de las 23:30, la vigencia está ok
                        $("input[name='bandera_vigencia_cufd_hidden']").val(1);
                        console.log('La vigencia del cufd está dentro de la hora actual');

                    } else {
                        // Por falso, la hora actual es más de las 23:30 o pasada las 00:00
                        $("input[name='bandera_vigencia_cufd_hidden']").val(0);
                        console.log('La hora actual está en el límite de la vigencia del cufd');
                        Swal.fire({
                            icon: 'warning',
                            title: 'Vigencia del CUFD al límite de terminar',
                            showConfirmButton: false,
                            html: 'Se recomienda <b>renovar los cufd. </b> Pulsa el siguiente botón para ' +
                                '<button type="button" class="vigencia-renovar-cufd btn btn-warning">Renovar vigencia CUFD</button> ' +
                                'para el día siguiente. ',
                        });
                    }*/
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
                success: function(data) {
                    if (data == true) {
                        $("input[name='bandera_vigencia_cufd_hidden']").val(1);
                        Swal.fire('Renovación Exitosa', 'Cufd renovado para el punto de venta!');
                    } else {
                        $("input[name='bandera_vigencia_cufd_hidden']").val(0);
                        Swal.fire('Error', 'no se logró renovar los cufd.');
                    }
                },
                complete: function() {
                    $("#spinner-div").hide(); //Ocultar icon spinner de cargando
                },
                error: function() {
                    Swal.fire('Error', 'error en el servicio!');
                },
            });
        });

        // permite pulsar tecla enter y buscar coincidencias
        $('#sales_valor_documento').keypress(function(event) {
            var keycode = (event.keyCode ? event.keyCode : event.which);
            if (keycode == '13') {
                consultar_ValorDocumento();
                console.log(
                    'El valor documento ha sido presionado para buscar coincidencias en la Base de Datos -----');
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
            $("#spinner-contingencia-div").hide();
        }

        // Mostrar tabla con paginación de 5 items para botón ventasRecientes
        $('#ventas-recientes-table').DataTable({
            dom: 'rt<"d-flex align-items-baseline"lpi>',
            'language': {
                'lengthMenu': '_MENU_ {{ trans('file.records per page') }}',
                "info": '<small>{{ trans('file.Showing') }} _START_ - _END_ (_TOTAL_)</small>',
                "search": '{{ trans('file.Search') }}',
                'paginate': {
                    'previous': '<i class="dripicons-chevron-left"></i>',
                    'next': '<i class="dripicons-chevron-right"></i>'
                }
            },
            'lengthMenu': [
                [5, 10, -1],
                [5, 10, "All"]
            ],
            order: [],

        });

        // Modal VentasRecientes, botón que permite mostrar/imprimir venta facturada 
        $(document).on("click", ".imprimir-factura-modal", function(event) {
            var id = $(this).data('id').toString();
            
            console.log('🔍 Abriendo modal de factura, sale_id:', id);
            
            // Guardar el sale_id en el modal para usarlo al enviar por WhatsApp
            $('#imprimir-factura-modal').data('sale-id', id);
            
            var url = '{{ route('sales.obtener_bytes_factura', ':id') }}';
            url = url.replace(':id', id);
            $.ajax({
                url: url,
                type: "GET",
                dataType: "json",
                async: false,
                success: function(data) {
                    console.log(data);
                    $('#pdfID').attr('src', 'data:application/pdf;base64,' + data['bytes']);
                    console.log('data:application/pdf;base64,' + data['bytes']);
                },
            });
            $('#imprimir-factura-modal').modal('show');
        });

        // Enviar factura por WhatsApp desde el modal
        $(document).on("click", "#send-whatsapp-btn", function(event) {
            event.preventDefault();
            
            var phone = $('#whatsapp_phone').val().trim();
            var sale_id = $('#imprimir-factura-modal').data('sale-id');
            
            console.log('🔍 Click en botón WhatsApp (Modal)', {
                phone: phone,
                sale_id: sale_id,
                modal_data: $('#imprimir-factura-modal').data()
            });
            
            if (!phone) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Número requerido',
                    text: 'Por favor ingrese un número de teléfono'
                });
                return;
            }
            
            // Validar formato básico (solo números)
            if (!/^[0-9]+$/.test(phone)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Formato inválido',
                    text: 'Ingrese solo números, sin espacios ni caracteres especiales'
                });
                return;
            }
            
            if (!sale_id) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo identificar la factura'
                });
                return;
            }
            
            // Deshabilitar botón mientras se envía
            var $btn = $(this);
            var originalText = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Enviando...');
            
            $.ajax({
                url: '{{ route('sales.send-invoice-whatsapp') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    sale_id: sale_id,
                    phone: phone
                },
                success: function(response) {
                    console.log('Respuesta del servidor:', response);
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Enviado!',
                            text: response.message,
                            timer: 3000
                        });
                        $('#whatsapp_phone').val(''); // Limpiar campo
                    } else {
                        console.error('Error en respuesta:', response);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'No se pudo enviar la factura'
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Error AJAX:', xhr);
                    var errorMsg = 'Error al enviar la factura por WhatsApp';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg
                    });
                },
                complete: function() {
                    // Rehabilitar botón
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Enviar factura por WhatsApp desde el panel final (después de completar la venta)
        $(document).on("click", "#send-final-whatsapp-btn", function(event) {
            event.preventDefault();
            
            var phone = $('#final_whatsapp_phone').val().trim();
            var sale_id = $('input[name="ajax_sale_id"]').val();
            
            console.log('🔍 Click en botón WhatsApp (Final Tab)', {
                phone: phone,
                sale_id: sale_id,
                ajax_sale_id_value: $('input[name="ajax_sale_id"]').val(),
                ajax_sale_id_element: $('input[name="ajax_sale_id"]')
            });
            
            if (!phone) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Número requerido',
                    text: 'Por favor ingrese un número de teléfono'
                });
                return;
            }
            
            // Validar formato básico (solo números)
            if (!/^[0-9]+$/.test(phone)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Formato inválido',
                    text: 'Ingrese solo números, sin espacios ni caracteres especiales'
                });
                return;
            }
            
            if (!sale_id) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo identificar la venta'
                });
                return;
            }
            
            // Deshabilitar botón mientras se envía
            var $btn = $(this);
            var originalText = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Enviando...');
            
            console.log('Enviando factura por WhatsApp (Final Tab)', {
                sale_id: sale_id,
                phone: phone,
                url: '{{ route('sales.send-invoice-whatsapp') }}'
            });
            
            $.ajax({
                url: '{{ route('sales.send-invoice-whatsapp') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    sale_id: sale_id,
                    phone: phone
                },
                success: function(response) {
                    console.log('Respuesta del servidor (Final Tab):', response);
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Enviado!',
                            text: response.message,
                            timer: 3000
                        });
                        $('#final_whatsapp_phone').val(''); // Limpiar campo
                    } else {
                        console.error('Error en respuesta (Final Tab):', response);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'No se pudo enviar la factura'
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Error AJAX (Final Tab):', xhr);
                    var errorMsg = 'Error al enviar la factura por WhatsApp';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg
                    });
                },
                complete: function() {
                    // Rehabilitar botón
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        });

        // ============ DEBUG: Verificar que el script se cargó ============
        console.log('✅ Script POS cargado correctamente');
        console.log('✅ Botones WhatsApp registrados:', {
            modal: $('#send-whatsapp-btn').length,
            final: $('#send-final-whatsapp-btn').length
        });
        // ==================================================================

        function alertaTablaItem_o_Empleado_vacio() {
            if (emp_temp == false) {
                $("#submit-btn").removeClass("disabled noselect");
            } else {
                $("#submit-btn").addClass("disabled noselect");
                Swal.fire("Advertencia de Servicio", "Complete el empleado de servicio del item", "warning");
            }
            var rownumber = $('table.order-list tbody tr:last').index();
            if (rownumber < 0) {
                emp_temp = false;
                $("#submit-btn").addClass("disabled noselect");
                Swal.fire("Información de Items", "Por favor, inserte el producto para ordenar la tabla!",
                    "warning");
            }
            if ($("input[name='bandera_factura_hidden']").val() == true) {
                $("#submit-btn").addClass("disabled noselect");
            }
        }
        // Método inverso del porcentaje descuento de un producto, 
        $(document).on('keyup change', '#porcentaje_discount', function(e) {
            if (permission_discount_item) {
                var precioUnidad = parseFloat($('input[name="edit_unit_price"]').val());
                var limit = (max_monto_permitido * 100) / precioUnidad;
            } else {
                var limit = 99.99;
            }
            if (parseFloat($(this).val()) > limit) {
                let value = limit;
                $(this).val(limit)
                Swal.fire('Advertencia', 'Máximo % permitido ' + value, "warning")
            }
            if (parseFloat($(this).val()) < 0) {
                $(this).val('0');
                Swal.fire('error', 'Mínimo % permitido 0')
            }
            monstrarMontoManualDesdePorcentaje();
        });

        function monstrarMontoManualDesdePorcentaje() {
            var montoDescuentoPorcentaje = parseFloat($("#porcentaje_discount").val());
            var precioUnidad = parseFloat($('input[name="edit_unit_price"]').val());
            var operacion = (montoDescuentoPorcentaje * precioUnidad) / 100;

            $('input[name="edit_discount"]').val(operacion.toFixed(cantDecimal));
            var montoTotalConDescuento = parseFloat($('input[name="edit_unit_price"]').val()) - operacion;
            $("#descuento_unit_price").val(montoTotalConDescuento.toFixed(cantDecimal));
        }

        // descuentoGeneralManual
        $(document).on('keyup change', '#order_discount', function(e) {
            var max_monto_general = (parseFloat($('input[name="total_price"]').val()) - 0.01);

            if (parseFloat($(this).val()) > max_monto_general) {
                var value = max_monto_general;
                $(this).val(value)
                Swal.fire('Advertencia', 'Monto máximo permitido ' + value, "warning")
            }
            if (parseFloat($(this).val()) < 0) {
                $(this).val('0');
                Swal.fire('error', 'Monto mínimo permitido 0')
            }
            mostrarPorcentaje();
        });

        function mostrarPorcentaje() {
            var montoDescuentoManual = parseFloat($('input[name="order_discount"]').val());
            var montoDescuentoPorcentaje = (montoDescuentoManual * 100) / parseFloat($('input[name="total_price"]').val());
            $("#porcentaje_order_discount").val(montoDescuentoPorcentaje.toFixed(2));
        }

        $(document).on('keyup change', '#porcentaje_order_discount', function(e) {
            if (parseFloat($(this).val()) > 99.99) {
                let value = '99.99'
                $(this).val(value)
                Swal.fire('Advertencia', 'Máximo % permitido ' + value, "warning")
            }
            if (parseFloat($(this).val()) < 0) {
                $(this).val('0');
                Swal.fire('error', 'Mínimo % permitido 0')
            }
            mostrarMontoGeneral();
        });

        function mostrarMontoGeneral() {
            var montoDescuentoPorcentaje = parseFloat($("#porcentaje_order_discount").val());
            var precioGeneral = parseFloat($('input[name="total_price"]').val());
            var operacion = (montoDescuentoPorcentaje * precioGeneral) / 100;

            $('input[name="order_discount"]').val(operacion.toFixed(2));
        }

        function descuentoBeneficio() {
            var benefit_discount = parseFloat($('input[name="benefits_value"]').val()) || 0;
            var benefit_now = parseFloat(document.getElementById("benefits").innerHTML);
            console.log('Descuento beneficio: ' + benefit_discount + ' | ' + benefit_now);
            if (benefit_discount > 0 && benefit_discount != benefit_now) {
                $("table.order-list tbody tr").each(function(index) {
                    var row = $(this);
                    var unit_price = product_price[index];
                    var qty = parseFloat(row.find('.qty').val()) || 0;
                    benefit_desc = (benefit_discount * unit_price) / 100;
                    var new_unit_price = unit_price - benefit_desc;
                    var new_subtotal = new_unit_price * qty;
                    console.log('Descuento: ' + benefit_desc + ' | Precio unitario: ' + unit_price + ' | Cantidad: ' + qty + ' | Nuevo precio unitario: ' + new_unit_price + ' | Nuevo subtotal: ' + new_subtotal);
                    row.find('.product-price').text(new_unit_price.toFixed(2));
                    row.find('.net_unit_price').val(new_unit_price.toFixed(2));
                    row.find('.subtotal-value').val(new_subtotal.toFixed(2));
                    row.find('.sub_total_unit').val(new_subtotal.toFixed(2));
                    row.find('td:nth-child(4)').text(new_subtotal.toFixed(2));
                    
                });
                calculateTotal();
            }
            $("#benefits").text(benefit_discount.toFixed(2));
        }

        function consultarNroFacturaCorrelativo() {
            var id = $('input[name="nro_factura_manual"]').val();
            var biller_id = $('select[name=biller_id]').val();
            var documento_sector_id = $("input[name='bandera_codigo_documento_sector_hidden']").val();
            var url = '{{ route('consultar_nro_factura_manual') }}';
            url_data = url.replace(':id', id);

            $.ajax({
                url: url_data,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    nro_factura_manual: id,
                    biller_id: biller_id,
                    nro_documento_sector: documento_sector_id
                },
                success: function(data) {
                    if (!data == true) {
                        $("input[name='nro_factura_manual']").val("");
                        Swal.fire('Nro. Factura Incorrecta', 'revise su talonario.', 'warning');
                    }
                }
            });
        }

        function verificaEstadoPV() {
            var id = $('select[name=biller_id]').val();
            var url = '{{ route('puntoventa.estado', ':id') }}';
            url = url.replace(':id', id);
            $.ajax({
                url: url,
                type: "GET",
                success: function(data) {
                    console.log(data)
                    if (data.cashier == false) {
                        Swal.fire({
                                title: "Error Caja Efectivo!",
                                text: "Mensaje : " + data.message,
                                icon: "warning",
                                buttons: {
                                    cancel: "Salir!",
                                },
                                dangerMode: true
                            })
                            .then((value) => {
                                window.location.href = '{{ url('/home') }}';
                            });
                    } else if (data.status == false && data.cashier == true) {
                        Swal.fire({
                                title: "Error Punto de Venta!",
                                text: "Mensaje : " + data.message,
                                icon: "warning",
                                buttons: {
                                    cancel: "Salir!",
                                },
                                dangerMode: true
                            })
                            .then((value) => {
                                window.history.back();
                            });
                    }
                }
            });

        }

        /**** Modo Proforma  */
        // Bootstrap Toggle NO dispara el evento 'change' correctamente
        // Usamos un approach diferente: monitorear clicks en el contenedor del toggle
        $(document).on('click', '.toggle-group', function() {
            // Forzar actualización después del click
            setTimeout(function() {
                var isChecked = $('#toggle-event-pro').prop('checked');
                modo_proforma = isChecked;
                
                console.log('========================================');
                console.log('[Toggle Click] Estado actualizado:', isChecked);
                console.log('[Toggle Click] modo_proforma ahora es:', modo_proforma);
                console.log('========================================');
                
                if (isChecked == true) {
                    console.log('[Toggle] Modo PROFORMA ACTIVADO - Todos los productos');
                    $("#submit-btn").addClass("disabled noselect");
                    $("#credit-card-btn").addClass("disabled noselect");
                    $("#cash-btn").addClass("disabled noselect");
                    $("#cobrar-btn").addClass("disabled noselect");
                    $("#qrsimple-btn").addClass("disabled noselect");
                    $("#cheque-btn").addClass("disabled noselect");
                    $("#gift-card-btn").addClass("disabled noselect");
                    $("#deposit-btn").addClass("disabled noselect");
                    $("#presale-btn").addClass("disabled noselect");
                    $("#showpresale-btn").addClass("disabled noselect");
                    $("#abonar-btn").addClass("disabled noselect");
                    $("#proforma-btn").removeClass("disabled noselect");
                } else {
                    console.log('[Toggle] Modo NORMAL - Solo con stock');
                    cancel($('table.order-list tbody tr:last').index());
                    $("#credit-card-btn").removeClass("disabled noselect");
                    $("#cash-btn").removeClass("disabled noselect");
                    $("#cobrar-btn").removeClass("disabled noselect");
                    $("#qrsimple-btn").removeClass("disabled noselect");
                    $("#cheque-btn").removeClass("disabled noselect");
                    $("#gift-card-btn").removeClass("disabled noselect");
                    $("#deposit-btn").removeClass("disabled noselect");
                    $("#presale-btn").removeClass("disabled noselect");
                    $("#showpresale-btn").removeClass("disabled noselect");
                    $("#abonar-btn").removeClass("disabled noselect");
                    $("#proforma-btn").addClass("disabled noselect");
                    $("#submit-btn").removeClass("disabled noselect");
                }
            }, 100); // Pequeño delay para que Bootstrap Toggle termine su animación
        });
        
        // Evento change como respaldo (por si acaso funciona en algunos navegadores)
        $('#toggle-event-pro').change(function() {
            var isChecked = $(this).prop('checked');
            modo_proforma = isChecked;
            console.log('[Toggle change] Respaldo activado. modo_proforma:', modo_proforma);
        });

        $('#submitPro-btn').on('click', function() {
            $("#spinner-div").show();
            $('input[name="quotation_status"]').val($('select[name="quotation_statusModal"]').val());
            $('input[name="valid_date"]').val($('#valid_dateModal').val());
            $('input[name="note"]').val($('#noteModal').val());

            $('#proforma-modal').modal('hide')
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
            generaProforma();
        });

        function generaProforma() {
            blockAmounts()
            var audio = $("#mysoundclip2")[0];
            audio.play();
            $('input[name="status"]').val(1);
            $('input[name="paying_amount"]').prop('required', false);
            $('input[name="paid_amount"]').prop('required', false);
            var rownumber = $('table.order-list tbody tr:last').index();
            if (rownumber < 0) {
                emp_temp = false;
                Swal.fire("Información de Items", "Por favor, inserte el producto para ordenar la tabla!",
                    "warning");
            } else {
                var form_data = $("#formPayment").getFormObject();
                console.log(form_data);
                $.ajax({
                    type: 'POST',
                    url: '{{ route('quotations.store') }}',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: form_data,
                    success: function(data) {
                        //console.log(data);
                        $("#spinner-div").hide();
                        if (data.status) {
                            if (data.print) {
                                Swal.fire({
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
                                                var win = window.open('quotations/gen_invoice/' + data.id, '_blank');
                                                win.focus();
                                                // No recargar automáticamente al cerrar la ventana de impresión
                                            } else {
                                                // No recargar automáticamente; simplemente mostrar mensaje
                                                // location.reload(true);
                                            }
                                    });
                            } else {
                                Swal.fire("Mensaje", data.message, data.message_code);
                                location.reload(true);
                            }
                        } else {
                            Swal.fire("Mensaje", "Error al guardar/actualizar intente de nuevo",
                                "error");
                        }

                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        Swal.fire("Error", "Estado: " + textStatus + " Error: " + errorThrown,
                            "error");
                    }
                });
            }
        }

        function deleteItem() {
            console.log("Eliminando Item index: " + rowindex + 1);
            rowindex = $('table.order-list tbody').closest('tr').index();
            rowindex = rowindex + 1;
            product_price.splice(rowindex, 1);
            product_discount.splice(rowindex, 1);
            product_description.splice(rowindex, 1);
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
        }

        $('#btnSaveCustomer').click(function() {
            var form_data = $("#frmAddCustomer").serialize();
            console.log(form_data);
            $.ajax({
                url: '{{ route('customer.store') }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: form_data,
                success: function(data) {
                    console.log(data);
                    if (data.status) {
                        document.getElementById("frmAddCustomer").reset();
                        $("#lims_customerSearch").val(data.customer.name);
                        $("#customer_id").val(data.customer.id);
                        Swal.fire("Mensaje", data.message, 'success');
                        $('#addCustomer').modal('hide')
                        if (bandera_siat == 1)
                            consultarClientePOS();
                    } else {
                        Swal.fire("Mensaje", data.message, 'error');
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown, data) {
                    if (XMLHttpRequest.status === 422) {
                        var errors = $.parseJSON(XMLHttpRequest.responseText);
                        $.each(errors, function(key, value) {
                            // console.log(key+ " " +value);
                            if ($.isPlainObject(value)) {
                                $.each(value, function(key, value) {
                                    console.log(key + " " + value);
                                    Swal.fire("Error Validacion", "Estado: " + value,
                                        "error");
                                });
                            } else {
                                Swal.fire("Error Validacion", "Estado: " + value, "error");
                            }
                        });
                    } else {
                        Swal.fire("Error", "Estado: " + textStatus + " Error: " + errorThrown,
                            "error");
                    }
                }
            });
        });
    </script>
    <script type="text/javascript">
        // Botón para imprimir la vista previa sin recargar la página
        $(document).on('click', '#btn-print-preview', function() {
            var printContent = document.getElementById('print_preview_container').innerHTML;
            var originalContent = document.body.innerHTML;
            
            // Crear iframe oculto para imprimir
            var printFrame = document.createElement('iframe');
            printFrame.style.position = 'absolute';
            printFrame.style.width = '0';
            printFrame.style.height = '0';
            printFrame.style.border = 'none';
            document.body.appendChild(printFrame);
            
            var frameDoc = printFrame.contentWindow || printFrame.contentDocument;
            if (frameDoc.document) frameDoc = frameDoc.document;
            
            frameDoc.open();
            frameDoc.write('<html><head><title>Imprimir</title>');
            frameDoc.write('<style>');
            frameDoc.write('@media print { .hidden-print { display: none !important; } }');
            frameDoc.write('</style>');
            frameDoc.write('</head><body>');
            frameDoc.write(printContent);
            frameDoc.write('</body></html>');
            frameDoc.close();
            
            // Esperar a que cargue el contenido antes de imprimir
            setTimeout(function() {
                printFrame.contentWindow.focus();
                printFrame.contentWindow.print();
                
                // Remover el iframe después de imprimir
                setTimeout(function() {
                    document.body.removeChild(printFrame);
                }, 100);
            }, 500);
        });
        
        // ============ DEBUG FINAL: Test de eventos WhatsApp ============
        console.log('🧪 Script de impresión cargado');
        
        // Test directo del evento después de que todo cargue
        setTimeout(function() {
            console.log('🔍 Verificando botones WhatsApp después de 3 segundos:', {
                modal_btn: $('#send-whatsapp-btn').length,
                final_btn: $('#send-final-whatsapp-btn').length,
                modal_field: $('#whatsapp_phone').length,
                final_field: $('#final_whatsapp_phone').length,
                modal_element: document.getElementById('send-whatsapp-btn'),
                final_element: document.getElementById('send-final-whatsapp-btn')
            });
            
            // Intentar bind manual como test
            if ($('#send-whatsapp-btn').length > 0) {
                console.log('✅ Botón modal existe, agregando event listener DIRECTO como alternativa');
                
                // Event listener directo (no delegado) como alternativa
                $('#send-whatsapp-btn').off('click').on('click', function(e) {
                    console.log('🎯 CLICK DIRECTO DETECTADO en botón modal WhatsApp!');
                    e.preventDefault();
                    e.stopPropagation();
                    
                    var phone = $('#whatsapp_phone').val().trim();
                    var sale_id = $('#imprimir-factura-modal').data('sale-id');
                    
                    console.log('📞 Datos capturados:', {phone: phone, sale_id: sale_id});
                    
                    if (!phone) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Número requerido',
                            text: 'Por favor ingrese un número de teléfono'
                        });
                        return;
                    }
                    
                    if (!/^[0-9]+$/.test(phone)) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Formato inválido',
                            text: 'Ingrese solo números, sin espacios ni caracteres especiales'
                        });
                        return;
                    }
                    
                    if (!sale_id) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo identificar la factura'
                        });
                        return;
                    }
                    
                    var $btn = $(this);
                    var originalText = $btn.html();
                    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Enviando...');
                    
                    console.log('📤 Enviando petición AJAX...');
                    
                    $.ajax({
                        url: '{{ route('sales.send-invoice-whatsapp') }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            sale_id: sale_id,
                            phone: phone
                        },
                        success: function(response) {
                            console.log('✅ Respuesta recibida:', response);
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Enviado!',
                                    text: response.message,
                                    timer: 3000
                                });
                                $('#whatsapp_phone').val('');
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message || 'No se pudo enviar la factura'
                                });
                            }
                        },
                        error: function(xhr) {
                            console.error('❌ Error AJAX:', xhr);
                            var errorMsg = 'Error al enviar la factura por WhatsApp';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errorMsg
                            });
                        },
                        complete: function() {
                            $btn.prop('disabled', false).html(originalText);
                        }
                    });
                });
                
                console.log('✅ Event listener DIRECTO agregado al botón modal');
            } else {
                console.error('❌ Botón modal NO existe en el DOM');
            }
            
            // Lo mismo para el botón final
            if ($('#send-final-whatsapp-btn').length > 0) {
                console.log('✅ Botón final existe, agregando event listener DIRECTO como alternativa');
                
                $('#send-final-whatsapp-btn').off('click').on('click', function(e) {
                    console.log('🎯 CLICK DIRECTO DETECTADO en botón final WhatsApp!');
                    e.preventDefault();
                    e.stopPropagation();
                    
                    var phone = $('#final_whatsapp_phone').val().trim();
                    var sale_id = $('input[name="ajax_sale_id"]').val();
                    
                    console.log('📞 Datos capturados:', {phone: phone, sale_id: sale_id});
                    
                    if (!phone) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Número requerido',
                            text: 'Por favor ingrese un número de teléfono'
                        });
                        return;
                    }
                    
                    if (!/^[0-9]+$/.test(phone)) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Formato inválido',
                            text: 'Ingrese solo números, sin espacios ni caracteres especiales'
                        });
                        return;
                    }
                    
                    if (!sale_id) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo identificar la venta'
                        });
                        return;
                    }
                    
                    var $btn = $(this);
                    var originalText = $btn.html();
                    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Enviando...');
                    
                    console.log('📤 Enviando petición AJAX...');
                    
                    $.ajax({
                        url: '{{ route('sales.send-invoice-whatsapp') }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            sale_id: sale_id,
                            phone: phone
                        },
                        success: function(response) {
                            console.log('✅ Respuesta recibida:', response);
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Enviado!',
                                    text: response.message,
                                    timer: 3000
                                });
                                $('#final_whatsapp_phone').val('');
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message || 'No se pudo enviar la factura'
                                });
                            }
                        },
                        error: function(xhr) {
                            console.error('❌ Error AJAX:', xhr);
                            var errorMsg = 'Error al enviar la factura por WhatsApp';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errorMsg
                            });
                        },
                        complete: function() {
                            $btn.prop('disabled', false).html(originalText);
                        }
                    });
                });
                
                console.log('✅ Event listener DIRECTO agregado al botón final');
            }
        }, 3000);
        // ================================================================
    </script>
    <script type="text/javascript" src="/public/sale/mp/metodos_pagos.js"></script>
    <div id="imprimir-factura-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true" class="modal fade text-left">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title"> Imprimir Factura </h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                        <span aria-hidden="true">
                            <i class="dripicons-cross"></i></span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="italic">
                    </p>
                    <object>
                        <embed id="pdfID" type="text/html" width="780" height="450" src="" />
                    </object>
                    
                    <!-- Sección WhatsApp -->
                    <div class="mt-3 p-3 border-top">
                        <h6 class="text-primary"><i class="fa fa-whatsapp"></i> Enviar por WhatsApp</h6>
                        <div class="form-group">
                            <label for="whatsapp_phone">Número de teléfono</label>
                            <input type="tel" class="form-control" id="whatsapp_phone" 
                                placeholder="Ej: 59176543210" 
                                pattern="[0-9]+"
                                title="Ingrese solo números, formato internacional sin + ni espacios">
                            <small class="form-text text-muted">
                                Formato internacional sin + ni espacios (Ej: 59176543210)
                            </small>
                        </div>
                        <button type="button" class="btn btn-success btn-sm" id="send-whatsapp-btn">
                            <i class="fa fa-whatsapp"></i> Enviar Factura
                        </button>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('file.Close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
