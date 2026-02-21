<?php

namespace App\Http\Controllers;

use App\Attendance;
use App\Biller;
use App\Category;
use App\Customer;
use App\Employee;
use App\Expense;
use App\ExpenseCategory;
use App\GeneralSetting;
use App\Holiday;
use App\HrmSetting;
use App\Http\Controllers\BillerController;
use App\Payment;
use App\Payroll;
use App\PosSetting;
use App\Product;
use App\Product_Sale;
use App\Product_Warehouse;
use App\ProductLote;
use App\ProductPurchase;
use App\ProductQuotation;
use App\ProductReturn;
use App\ProductTransfer;
use App\ProductVariant;
use App\Purchase;
use App\PurchaseProductReturn;
use App\Quotation;
use App\ReturnPurchase;
use App\Returns;
use App\Sale;
use App\Supplier;
use App\Tax;
use App\Transfer;
use App\Unit;
use App\User;
use App\Variant;
use App\Warehouse;
use App\CustomerSale;
use App\Account;
use App\AdjustmentAccount;
use App\SiatSucursal;
use Auth;
use DB;
use Illuminate\Http\Request;
use Log;
use Spatie\Permission\Models\Role;

class ReportController extends Controller
{

    public function productQuantityAlert()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('product-qty-alert')) {
            $lims_product_data = Product::select('name', 'code', 'image', 'qty', 'alert_quantity')->where('is_active', true)->whereColumn('alert_quantity', '>', 'qty')->get();
            return view('report.qty_alert_report', compact('lims_product_data'));
        } else {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }
    }

    public function warehouseStock()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('warehouse-stock-report')) {
            $total_item = DB::table('product_warehouse')
                ->join('products', 'product_warehouse.product_id', '=', 'products.id')
                ->where([
                    ['products.is_active', true],
                    ['product_warehouse.qty', '>', 0],
                ])->count();

            $total_qty = Product::where('is_active', true)->sum('qty');
            $total_price = DB::table('products')->where('is_active', true)->sum(DB::raw('price * qty'));
            $total_cost = DB::table('products')->where('is_active', true)->sum(DB::raw('cost * qty'));
            if (Auth::user()->role_id > 2 && Auth::user()->biller_id) {
                $lims_warehouse_list = app(BillerController::class)::warehouseAuthorizate(Auth::user()->biller_id);
            } else {
                $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            }
            $warehouse_id = 0;
            return view('report.warehouse_stock', compact('total_item', 'total_qty', 'total_price', 'total_cost', 'lims_warehouse_list', 'warehouse_id'));
        } else {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }
    }

    public function warehouseStockById(Request $request)
    {
        $data = $request->all();
        if ($data['warehouse_id'] == 0) {
            return redirect()->back();
        }

        $total_item = DB::table('product_warehouse')
            ->join('products', 'product_warehouse.product_id', '=', 'products.id')
            ->where([
                ['products.is_active', true],
                ['product_warehouse.qty', '>', 0],
                ['product_warehouse.warehouse_id', $data['warehouse_id']],
            ])->count();
        $total_qty = DB::table('product_warehouse')
            ->join('products', 'product_warehouse.product_id', '=', 'products.id')
            ->where([
                ['products.is_active', true],
                ['product_warehouse.warehouse_id', $data['warehouse_id']],
            ])->sum('product_warehouse.qty');
        $total_price = DB::table('product_warehouse')
            ->join('products', 'product_warehouse.product_id', '=', 'products.id')
            ->where([
                ['products.is_active', true],
                ['product_warehouse.warehouse_id', $data['warehouse_id']],
            ])->sum(DB::raw('products.price * product_warehouse.qty'));
        $total_cost = DB::table('product_warehouse')
            ->join('products', 'product_warehouse.product_id', '=', 'products.id')
            ->where([
                ['products.is_active', true],
                ['product_warehouse.warehouse_id', $data['warehouse_id']],
            ])->sum(DB::raw('products.cost * product_warehouse.qty'));
        if (Auth::user()->role_id > 2 && Auth::user()->biller_id) {
            $lims_warehouse_list = app(BillerController::class)::warehouseAuthorizate(Auth::user()->biller_id);
        } else {
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        }
        $warehouse_id = $data['warehouse_id'];
        return view('report.warehouse_stock', compact('total_item', 'total_qty', 'total_price', 'total_cost', 'lims_warehouse_list', 'warehouse_id'));
    }

    public function dailySale($year, $month)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('daily-sale')) {
            $start = 1;
            $number_of_day = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            while ($start <= $number_of_day) {
                if ($start < 10) {
                    $date = $year . '-' . $month . '-0' . $start;
                } else {
                    $date = $year . '-' . $month . '-' . $start;
                }

                $query1 = array(
                    'SUM(total_discount) AS total_discount',
                    'SUM(order_discount) AS order_discount',
                    'SUM(total_tax) AS total_tax',
                    'SUM(order_tax) AS order_tax',
                    'SUM(shipping_cost) AS shipping_cost',
                    'SUM(grand_total) AS grand_total',
                );
                $sale_data = Sale::whereDate('date_sell', $date)->selectRaw(implode(',', $query1))->get();
                $total_discount[$start] = $sale_data[0]->total_discount;
                $order_discount[$start] = $sale_data[0]->order_discount;
                $total_tax[$start] = $sale_data[0]->total_tax;
                $order_tax[$start] = $sale_data[0]->order_tax;
                $shipping_cost[$start] = $sale_data[0]->shipping_cost;
                $grand_total[$start] = $sale_data[0]->grand_total;
                $start++;
            }
            $start_day = date('w', strtotime($year . '-' . $month . '-01')) + 1;
            $prev_year = date('Y', strtotime('-1 month', strtotime($year . '-' . $month . '-01')));
            $prev_month = date('m', strtotime('-1 month', strtotime($year . '-' . $month . '-01')));
            $next_year = date('Y', strtotime('+1 month', strtotime($year . '-' . $month . '-01')));
            $next_month = date('m', strtotime('+1 month', strtotime($year . '-' . $month . '-01')));
            if (Auth::user()->role_id > 2 && Auth::user()->biller_id) {
                $lims_warehouse_list = app(BillerController::class)::warehouseAuthorizate(Auth::user()->biller_id);
            } else {
                $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            }
            $warehouse_id = 0;
            return view('report.daily_sale', compact('total_discount', 'order_discount', 'total_tax', 'order_tax', 'shipping_cost', 'grand_total', 'start_day', 'year', 'month', 'number_of_day', 'prev_year', 'prev_month', 'next_year', 'next_month', 'lims_warehouse_list', 'warehouse_id'));
        } else {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }
    }

    public function dailySaleByWarehouse(Request $request, $year, $month)
    {
        $data = $request->all();
        if ($data['warehouse_id'] == 0) {
            return redirect()->back();
        }

        $start = 1;
        $number_of_day = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        while ($start <= $number_of_day) {
            if ($start < 10) {
                $date = $year . '-' . $month . '-0' . $start;
            } else {
                $date = $year . '-' . $month . '-' . $start;
            }

            $query1 = array(
                'SUM(total_discount) AS total_discount',
                'SUM(order_discount) AS order_discount',
                'SUM(total_tax) AS total_tax',
                'SUM(order_tax) AS order_tax',
                'SUM(shipping_cost) AS shipping_cost',
                'SUM(grand_total) AS grand_total',
            );
            $sale_data = Sale::where('warehouse_id', $data['warehouse_id'])->whereDate('date_sell', $date)->selectRaw(implode(',', $query1))->get();
            $total_discount[$start] = $sale_data[0]->total_discount;
            $order_discount[$start] = $sale_data[0]->order_discount;
            $total_tax[$start] = $sale_data[0]->total_tax;
            $order_tax[$start] = $sale_data[0]->order_tax;
            $shipping_cost[$start] = $sale_data[0]->shipping_cost;
            $grand_total[$start] = $sale_data[0]->grand_total;
            $start++;
        }
        $start_day = date('w', strtotime($year . '-' . $month . '-01')) + 1;
        $prev_year = date('Y', strtotime('-1 month', strtotime($year . '-' . $month . '-01')));
        $prev_month = date('m', strtotime('-1 month', strtotime($year . '-' . $month . '-01')));
        $next_year = date('Y', strtotime('+1 month', strtotime($year . '-' . $month . '-01')));
        $next_month = date('m', strtotime('+1 month', strtotime($year . '-' . $month . '-01')));
        if (Auth::user()->role_id > 2 && Auth::user()->biller_id) {
            $lims_warehouse_list = app(BillerController::class)::warehouseAuthorizate(Auth::user()->biller_id);
        } else {
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        }
        $warehouse_id = $data['warehouse_id'];
        return view('report.daily_sale', compact('total_discount', 'order_discount', 'total_tax', 'order_tax', 'shipping_cost', 'grand_total', 'start_day', 'year', 'month', 'number_of_day', 'prev_year', 'prev_month', 'next_year', 'next_month', 'lims_warehouse_list', 'warehouse_id'));
    }

    public function dailyPurchase($year, $month)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('daily-purchase')) {
            $start = 1;
            $number_of_day = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            while ($start <= $number_of_day) {
                if ($start < 10) {
                    $date = $year . '-' . $month . '-0' . $start;
                } else {
                    $date = $year . '-' . $month . '-' . $start;
                }

                $query1 = array(
                    'SUM(total_discount) AS total_discount',
                    'SUM(order_discount) AS order_discount',
                    'SUM(total_tax) AS total_tax',
                    'SUM(order_tax) AS order_tax',
                    'SUM(shipping_cost) AS shipping_cost',
                    'SUM(grand_total) AS grand_total',
                );
                $purchase_data = Purchase::whereDate('created_at', $date)->selectRaw(implode(',', $query1))->get();
                $total_discount[$start] = $purchase_data[0]->total_discount;
                $order_discount[$start] = $purchase_data[0]->order_discount;
                $total_tax[$start] = $purchase_data[0]->total_tax;
                $order_tax[$start] = $purchase_data[0]->order_tax;
                $shipping_cost[$start] = $purchase_data[0]->shipping_cost;
                $grand_total[$start] = $purchase_data[0]->grand_total;
                $start++;
            }
            $start_day = date('w', strtotime($year . '-' . $month . '-01')) + 1;
            $prev_year = date('Y', strtotime('-1 month', strtotime($year . '-' . $month . '-01')));
            $prev_month = date('m', strtotime('-1 month', strtotime($year . '-' . $month . '-01')));
            $next_year = date('Y', strtotime('+1 month', strtotime($year . '-' . $month . '-01')));
            $next_month = date('m', strtotime('+1 month', strtotime($year . '-' . $month . '-01')));
            if (Auth::user()->role_id > 2 && Auth::user()->biller_id) {
                $lims_warehouse_list = app(BillerController::class)::warehouseAuthorizate(Auth::user()->biller_id);
            } else {
                $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            }
            $warehouse_id = 0;
            return view('report.daily_purchase', compact('total_discount', 'order_discount', 'total_tax', 'order_tax', 'shipping_cost', 'grand_total', 'start_day', 'year', 'month', 'number_of_day', 'prev_year', 'prev_month', 'next_year', 'next_month', 'lims_warehouse_list', 'warehouse_id'));
        } else {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }
    }

    public function dailyPurchaseByWarehouse(Request $request, $year, $month)
    {
        $data = $request->all();
        if ($data['warehouse_id'] == 0) {
            return redirect()->back();
        }

        $start = 1;
        $number_of_day = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        while ($start <= $number_of_day) {
            if ($start < 10) {
                $date = $year . '-' . $month . '-0' . $start;
            } else {
                $date = $year . '-' . $month . '-' . $start;
            }

            $query1 = array(
                'SUM(total_discount) AS total_discount',
                'SUM(order_discount) AS order_discount',
                'SUM(total_tax) AS total_tax',
                'SUM(order_tax) AS order_tax',
                'SUM(shipping_cost) AS shipping_cost',
                'SUM(grand_total) AS grand_total',
            );
            $purchase_data = Purchase::where('warehouse_id', $data['warehouse_id'])->whereDate('created_at', $date)->selectRaw(implode(',', $query1))->get();
            $total_discount[$start] = $purchase_data[0]->total_discount;
            $order_discount[$start] = $purchase_data[0]->order_discount;
            $total_tax[$start] = $purchase_data[0]->total_tax;
            $order_tax[$start] = $purchase_data[0]->order_tax;
            $shipping_cost[$start] = $purchase_data[0]->shipping_cost;
            $grand_total[$start] = $purchase_data[0]->grand_total;
            $start++;
        }
        $start_day = date('w', strtotime($year . '-' . $month . '-01')) + 1;
        $prev_year = date('Y', strtotime('-1 month', strtotime($year . '-' . $month . '-01')));
        $prev_month = date('m', strtotime('-1 month', strtotime($year . '-' . $month . '-01')));
        $next_year = date('Y', strtotime('+1 month', strtotime($year . '-' . $month . '-01')));
        $next_month = date('m', strtotime('+1 month', strtotime($year . '-' . $month . '-01')));
        if (Auth::user()->role_id > 2 && Auth::user()->biller_id) {
            $lims_warehouse_list = app(BillerController::class)::warehouseAuthorizate(Auth::user()->biller_id);
        } else {
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        }
        $warehouse_id = $data['warehouse_id'];

        return view('report.daily_purchase', compact('total_discount', 'order_discount', 'total_tax', 'order_tax', 'shipping_cost', 'grand_total', 'start_day', 'year', 'month', 'number_of_day', 'prev_year', 'prev_month', 'next_year', 'next_month', 'lims_warehouse_list', 'warehouse_id'));
    }

    public function monthlySale($year)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('monthly-sale')) {
            $start = strtotime($year . '-01-01');
            $end = strtotime($year . '-12-31');
            while ($start <= $end) {
                $start_date = $year . '-' . date('m', $start) . '-' . '01';
                $end_date = $year . '-' . date('m', $start) . '-' . '31';

                $temp_total_discount = Sale::whereDate('date_sell', '>=', $start_date)->whereDate('date_sell', '<=', $end_date)->sum('total_discount');
                $total_discount[] = number_format((float) $temp_total_discount, 2, '.', '');

                $temp_order_discount = Sale::whereDate('date_sell', '>=', $start_date)->whereDate('date_sell', '<=', $end_date)->sum('order_discount');
                $order_discount[] = number_format((float) $temp_order_discount, 2, '.', '');

                $temp_total_tax = Sale::whereDate('date_sell', '>=', $start_date)->whereDate('date_sell', '<=', $end_date)->sum('total_tax');
                $total_tax[] = number_format((float) $temp_total_tax, 2, '.', '');

                $temp_order_tax = Sale::whereDate('date_sell', '>=', $start_date)->whereDate('date_sell', '<=', $end_date)->sum('order_tax');
                $order_tax[] = number_format((float) $temp_order_tax, 2, '.', '');

                $temp_shipping_cost = Sale::whereDate('date_sell', '>=', $start_date)->whereDate('date_sell', '<=', $end_date)->sum('shipping_cost');
                $shipping_cost[] = number_format((float) $temp_shipping_cost, 2, '.', '');

                $temp_total = Sale::whereDate('date_sell', '>=', $start_date)->whereDate('date_sell', '<=', $end_date)->sum('grand_total');
                $total[] = number_format((float) $temp_total, 2, '.', '');
                $start = strtotime("+1 month", $start);
            }
            if (Auth::user()->role_id > 2 && Auth::user()->biller_id) {
                $lims_warehouse_list = app(BillerController::class)::warehouseAuthorizate(Auth::user()->biller_id);
            } else {
                $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            }
            $warehouse_id = 0;
            return view('report.monthly_sale', compact('year', 'total_discount', 'order_discount', 'total_tax', 'order_tax', 'shipping_cost', 'total', 'lims_warehouse_list', 'warehouse_id'));
        } else {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }
    }

    public function monthlySaleByWarehouse(Request $request, $year)
    {
        $data = $request->all();
        if ($data['warehouse_id'] == 0) {
            return redirect()->back();
        }

        $start = strtotime($year . '-01-01');
        $end = strtotime($year . '-12-31');
        while ($start <= $end) {
            $start_date = $year . '-' . date('m', $start) . '-' . '01';
            $end_date = $year . '-' . date('m', $start) . '-' . '31';

            $temp_total_discount = Sale::where('warehouse_id', $data['warehouse_id'])->whereDate('date_sell', '>=', $start_date)->whereDate('date_sell', '<=', $end_date)->sum('total_discount');
            $total_discount[] = number_format((float) $temp_total_discount, 2, '.', '');

            $temp_order_discount = Sale::where('warehouse_id', $data['warehouse_id'])->whereDate('date_sell', '>=', $start_date)->whereDate('date_sell', '<=', $end_date)->sum('order_discount');
            $order_discount[] = number_format((float) $temp_order_discount, 2, '.', '');

            $temp_total_tax = Sale::where('warehouse_id', $data['warehouse_id'])->whereDate('date_sell', '>=', $start_date)->whereDate('date_sell', '<=', $end_date)->sum('total_tax');
            $total_tax[] = number_format((float) $temp_total_tax, 2, '.', '');

            $temp_order_tax = Sale::where('warehouse_id', $data['warehouse_id'])->whereDate('date_sell', '>=', $start_date)->whereDate('date_sell', '<=', $end_date)->sum('order_tax');
            $order_tax[] = number_format((float) $temp_order_tax, 2, '.', '');

            $temp_shipping_cost = Sale::where('warehouse_id', $data['warehouse_id'])->whereDate('date_sell', '>=', $start_date)->whereDate('date_sell', '<=', $end_date)->sum('shipping_cost');
            $shipping_cost[] = number_format((float) $temp_shipping_cost, 2, '.', '');

            $temp_total = Sale::where('warehouse_id', $data['warehouse_id'])->whereDate('date_sell', '>=', $start_date)->whereDate('date_sell', '<=', $end_date)->sum('grand_total');
            $total[] = number_format((float) $temp_total, 2, '.', '');
            $start = strtotime("+1 month", $start);
        }
        if (Auth::user()->role_id > 2 && Auth::user()->biller_id) {
            $lims_warehouse_list = app(BillerController::class)::warehouseAuthorizate(Auth::user()->biller_id);
        } else {
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        }
        $warehouse_id = $data['warehouse_id'];
        return view('report.monthly_sale', compact('year', 'total_discount', 'order_discount', 'total_tax', 'order_tax', 'shipping_cost', 'total', 'lims_warehouse_list', 'warehouse_id'));
    }

    public function monthlyPurchase($year)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('monthly-purchase')) {
            $start = strtotime($year . '-01-01');
            $end = strtotime($year . '-12-31');
            while ($start <= $end) {
                $start_date = $year . '-' . date('m', $start) . '-' . '01';
                $end_date = $year . '-' . date('m', $start) . '-' . '31';

                $query1 = array(
                    'SUM(total_discount) AS total_discount',
                    'SUM(order_discount) AS order_discount',
                    'SUM(total_tax) AS total_tax',
                    'SUM(order_tax) AS order_tax',
                    'SUM(shipping_cost) AS shipping_cost',
                    'SUM(grand_total) AS grand_total',
                );
                $purchase_data = Purchase::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->selectRaw(implode(',', $query1))->get();

                $total_discount[] = number_format((float) $purchase_data[0]->total_discount, 2, '.', '');
                $order_discount[] = number_format((float) $purchase_data[0]->order_discount, 2, '.', '');
                $total_tax[] = number_format((float) $purchase_data[0]->total_tax, 2, '.', '');
                $order_tax[] = number_format((float) $purchase_data[0]->order_tax, 2, '.', '');
                $shipping_cost[] = number_format((float) $purchase_data[0]->shipping_cost, 2, '.', '');
                $grand_total[] = number_format((float) $purchase_data[0]->grand_total, 2, '.', '');
                $start = strtotime("+1 month", $start);
            }
            if (Auth::user()->role_id > 2 && Auth::user()->biller_id) {
                $lims_warehouse_list = app(BillerController::class)::warehouseAuthorizate(Auth::user()->biller_id);
            } else {
                $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            }
            $warehouse_id = 0;
            return view('report.monthly_purchase', compact('year', 'total_discount', 'order_discount', 'total_tax', 'order_tax', 'shipping_cost', 'grand_total', 'lims_warehouse_list', 'warehouse_id'));
        } else {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }
    }

    public function monthlyPurchaseByWarehouse(Request $request, $year)
    {
        $data = $request->all();
        if ($data['warehouse_id'] == 0) {
            return redirect()->back();
        }

        $start = strtotime($year . '-01-01');
        $end = strtotime($year . '-12-31');
        while ($start <= $end) {
            $start_date = $year . '-' . date('m', $start) . '-' . '01';
            $end_date = $year . '-' . date('m', $start) . '-' . '31';

            $query1 = array(
                'SUM(total_discount) AS total_discount',
                'SUM(order_discount) AS order_discount',
                'SUM(total_tax) AS total_tax',
                'SUM(order_tax) AS order_tax',
                'SUM(shipping_cost) AS shipping_cost',
                'SUM(grand_total) AS grand_total',
            );
            $purchase_data = Purchase::where('warehouse_id', $data['warehouse_id'])->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->selectRaw(implode(',', $query1))->get();

            $total_discount[] = number_format((float) $purchase_data[0]->total_discount, 2, '.', '');
            $order_discount[] = number_format((float) $purchase_data[0]->order_discount, 2, '.', '');
            $total_tax[] = number_format((float) $purchase_data[0]->total_tax, 2, '.', '');
            $order_tax[] = number_format((float) $purchase_data[0]->order_tax, 2, '.', '');
            $shipping_cost[] = number_format((float) $purchase_data[0]->shipping_cost, 2, '.', '');
            $grand_total[] = number_format((float) $purchase_data[0]->grand_total, 2, '.', '');
            $start = strtotime("+1 month", $start);
        }
        if (Auth::user()->role_id > 2 && Auth::user()->biller_id) {
            $lims_warehouse_list = app(BillerController::class)::warehouseAuthorizate(Auth::user()->biller_id);
        } else {
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        }
        $warehouse_id = $data['warehouse_id'];
        return view('report.monthly_purchase', compact('year', 'total_discount', 'order_discount', 'total_tax', 'order_tax', 'shipping_cost', 'grand_total', 'lims_warehouse_list', 'warehouse_id'));
    }

    public function bestSeller()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('best-seller')) {
            $start = strtotime(date("Y-m", strtotime("-2 months")) . '-01');
            $end = strtotime(date("Y") . '-' . date("m") . '-31');

            while ($start <= $end) {
                $start_date = date("Y-m", $start) . '-' . '01';
                $end_date = date("Y-m", $start) . '-' . '31';

                $best_selling_qty = Product_Sale::select(DB::raw('product_id, sum(qty) as sold_qty'))->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->groupBy('product_id')->orderBy('sold_qty', 'desc')->take(1)->get();
                if (!count($best_selling_qty)) {
                    $product[] = '';
                    $sold_qty[] = 0;
                }
                foreach ($best_selling_qty as $best_seller) {
                    $product_data = Product::find($best_seller->product_id);
                    $product[] = $product_data->name . ': ' . $product_data->code;
                    $sold_qty[] = $best_seller->sold_qty;
                }
                $start = strtotime("+1 month", $start);
            }
            $start_month = date("F Y", strtotime('-2 month'));
            if (Auth::user()->role_id > 2 && Auth::user()->biller_id) {
                $lims_warehouse_list = app(BillerController::class)::warehouseAuthorizate(Auth::user()->biller_id);
            } else {
                $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            }
            $warehouse_id = 0;
            return view('report.best_seller', compact('product', 'sold_qty', 'start_month', 'lims_warehouse_list', 'warehouse_id'));
        } else {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }
    }

    public function bestSellerByWarehouse(Request $request)
    {
        $data = $request->all();
        if ($data['warehouse_id'] == 0) {
            return redirect()->back();
        }

        $start = strtotime(date("Y-m", strtotime("-2 months")) . '-01');
        $end = strtotime(date("Y") . '-' . date("m") . '-31');

        while ($start <= $end) {
            $start_date = date("Y-m", $start) . '-' . '01';
            $end_date = date("Y-m", $start) . '-' . '31';

            $best_selling_qty = DB::table('sales')
                ->join('product_sales', 'sales.id', '=', 'product_sales.sale_id')->select(DB::raw('product_sales.product_id, sum(product_sales.qty) as sold_qty'))->where('sales.warehouse_id', $data['warehouse_id'])->whereDate('sales.created_at', '>=', $start_date)->whereDate('sales.created_at', '<=', $end_date)->groupBy('product_id')->orderBy('sold_qty', 'desc')->take(1)->get();

            if (!count($best_selling_qty)) {
                $product[] = '';
                $sold_qty[] = 0;
            }
            foreach ($best_selling_qty as $best_seller) {
                $product_data = Product::find($best_seller->product_id);
                $product[] = $product_data->name . ': ' . $product_data->code;
                $sold_qty[] = $best_seller->sold_qty;
            }
            $start = strtotime("+1 month", $start);
        }
        $start_month = date("F Y", strtotime('-2 month'));
        if (Auth::user()->role_id > 2 && Auth::user()->biller_id) {
            $lims_warehouse_list = app(BillerController::class)::warehouseAuthorizate(Auth::user()->biller_id);
        } else {
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        }
        $warehouse_id = $data['warehouse_id'];
        return view('report.best_seller', compact('product', 'sold_qty', 'start_month', 'lims_warehouse_list', 'warehouse_id'));
    }

    public function profitLoss(Request $request)
    {
        $start_date = $request['start_date'];
        $end_date = $request['end_date'];
        $query1 = array(
            'SUM(grand_total) AS grand_total',
            'SUM(paid_amount) AS paid_amount',
            'SUM(total_tax + order_tax) AS tax',
        );
        $query2 = array(
            'SUM(grand_total) AS grand_total',
            'SUM(total_tax + order_tax) AS tax',
        );
        $warehouse_sale = [];
        $warehouse_purchase = [];
        $warehouse_return = [];
        $warehouse_purchase_return = [];
        $warehouse_expense = [];
        $purchase = Purchase::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->selectRaw(implode(',', $query1))->get();
        $total_purchase = Purchase::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->count();
        $sale = Sale::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->selectRaw(implode(',', $query1))->get();
        $total_sale = Sale::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->count();
        $return = Returns::where('is_active', true)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->selectRaw(implode(',', $query2))->get();
        $total_return = Returns::where('is_active', true)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->count();
        $purchase_return = ReturnPurchase::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->selectRaw(implode(',', $query2))->get();
        $total_purchase_return = ReturnPurchase::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->count();
        $expense = Expense::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('amount');
        $total_expense = Expense::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->count();
        $payroll = Payroll::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('amount');
        $total_payroll = Payroll::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->count();
        $total_item = DB::table('product_warehouse')
            ->join('products', 'product_warehouse.product_id', '=', 'products.id')
            ->where([
                ['products.is_active', true],
                ['product_warehouse.qty', '>', 0],
            ])->count();
        $payment_recieved_number = DB::table('payments')->whereNotNull('sale_id')->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)->count();
        $payment_recieved = DB::table('payments')->whereNotNull('sale_id')->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('payments.amount');
        $credit_card_payment_sale = DB::table('payments')
            ->where('paying_method', 'Credit Card')
            ->whereNotNull('payments.sale_id')
            ->whereDate('payments.created_at', '>=', $start_date)
            ->whereDate('payments.created_at', '<=', $end_date)->sum('payments.amount');
        $cheque_payment_sale = DB::table('payments')
            ->where('paying_method', 'Cheque')
            ->whereNotNull('payments.sale_id')
            ->whereDate('payments.created_at', '>=', $start_date)
            ->whereDate('payments.created_at', '<=', $end_date)->sum('payments.amount');
        $gift_card_payment_sale = DB::table('payments')
            ->where('paying_method', 'Gift Card')
            ->whereNotNull('sale_id')
            ->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->sum('amount');
        $paypal_payment_sale = DB::table('payments')
            ->where('paying_method', 'Paypal')
            ->whereNotNull('sale_id')
            ->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->sum('amount');
        $deposit_payment_sale = DB::table('payments')
            ->where('paying_method', 'Deposit')
            ->whereNotNull('sale_id')
            ->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->sum('amount');
        $cash_payment_sale = $payment_recieved - $credit_card_payment_sale - $cheque_payment_sale - $gift_card_payment_sale - $paypal_payment_sale - $deposit_payment_sale;
        $payment_sent_number = DB::table('payments')->whereNotNull('purchase_id')->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)->count();
        $payment_sent = DB::table('payments')->whereNotNull('purchase_id')->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('payments.amount');
        $credit_card_payment_purchase = DB::table('payments')
            ->where('paying_method', 'Gift Card')
            ->whereNotNull('payments.purchase_id')
            ->whereDate('payments.created_at', '>=', $start_date)
            ->whereDate('payments.created_at', '<=', $end_date)->sum('payments.amount');
        $cheque_payment_purchase = DB::table('payments')
            ->where('paying_method', 'Cheque')
            ->whereNotNull('payments.purchase_id')
            ->whereDate('payments.created_at', '>=', $start_date)
            ->whereDate('payments.created_at', '<=', $end_date)->sum('payments.amount');
        $cash_payment_purchase = $payment_sent - $credit_card_payment_purchase - $cheque_payment_purchase;
        $lims_warehouse_all = Warehouse::where('is_active', true)->get();
        $warehouse_name = [];
        foreach ($lims_warehouse_all as $warehouse) {
            $warehouse_name[] = $warehouse->name;
            $warehouse_sale[] = Sale::where('warehouse_id', $warehouse->id)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->selectRaw(implode(',', $query2))->get();
            $warehouse_purchase[] = Purchase::where('warehouse_id', $warehouse->id)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->selectRaw(implode(',', $query2))->get();
            $warehouse_return[] = Returns::where([['warehouse_id', $warehouse->id], ['is_active', true]])->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->selectRaw(implode(',', $query2))->get();
            $warehouse_purchase_return[] = ReturnPurchase::where('warehouse_id', $warehouse->id)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->selectRaw(implode(',', $query2))->get();
            $warehouse_expense[] = Expense::where('warehouse_id', $warehouse->id)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('amount');
        }

        return view('report.profit_loss', compact('purchase', 'total_purchase', 'sale', 'total_sale', 'return', 'purchase_return', 'total_return', 'total_purchase_return', 'expense', 'payroll', 'total_expense', 'total_payroll', 'payment_recieved', 'payment_recieved_number', 'cash_payment_sale', 'cheque_payment_sale', 'credit_card_payment_sale', 'gift_card_payment_sale', 'paypal_payment_sale', 'deposit_payment_sale', 'payment_sent', 'payment_sent_number', 'cash_payment_purchase', 'cheque_payment_purchase', 'credit_card_payment_purchase', 'warehouse_name', 'warehouse_sale', 'warehouse_purchase', 'warehouse_return', 'warehouse_purchase_return', 'warehouse_expense', 'start_date', 'end_date'));
    }

    public function productReport(Request $request)
    {
        if (is_null($request->warehouse_id)) {
            $warehouse_id = Warehouse::where('is_active', true)->first()->id;
            $stock = true;
        } else {
            $data = $request->all();
            $stock = ((isset($data['con_stock']) && ($data['con_stock'] == 'true' || $data['con_stock'] == 'true')) ? true : false);
            $warehouse_id = $data['warehouse_id'];
        }
        $list_report = collect();
        Log::info("Filtrando Informe del Producto...");
        $lims_product_all = Product::select('id', 'code', 'name', 'qty', 'is_variant', 'sale_unit_id', 'category_id', 'brand_id')
            ->where([['is_active', true], ['type', 'standard']])->orWhere('type', 'insumo')->get();
        Log::info("productos encontrados: " . $lims_product_all->count());
        foreach ($lims_product_all as $key => $product) {
            $variant_id_all = [];
            $unit_data = Unit::select('id', 'unit_code')->find($product->sale_unit_id);
            if (isset($product->brand->title)) {
                $brand_name = $product->brand->title;
            } else {
                $brand_name = "Sin Marca";
            }

            if ($unit_data) {
                $unit_code = $unit_data->unit_code;
            } else {
                $unit_code = "N/A";
            }

            if ($warehouse_id == 0) {
                if ($product->is_variant) {
                    $variant_all = ProductVariant::where('product_id', $product->id)->get();
                    foreach ($variant_all as $key => $variant) {
                        $variant_data = Variant::find($variant->variant_id);
                        $qtystock = $variant->qty != null ? $variant->qty : 0;

                        $item = array(
                            'id' => $product->id,
                            'code' => $product->code,
                            'variant_id' => $variant_data->id,
                            'product' => $product->name . ' [' . $variant_data->name . ']',
                            'category' => $product->category->name,
                            'brand' => $brand_name,
                            'unit_code' => $unit_code,
                            'qty' => $qtystock,
                        );
                        if ($stock && $item['qty'] > 0) {
                            $list_report[] = (object) $item;
                        } else if ($stock == false) {
                            $list_report[] = (object) $item;
                        }
                    }
                } else {
                    $item = array(
                        'id' => $product->id,
                        'code' => $product->code,
                        'variant_id' => null,
                        'product' => $product->name,
                        'category' => $product->category->name,
                        'brand' => $brand_name,
                        'unit_code' => $unit_code,
                        'qty' => $product->qty
                    );
                    if ($stock && $item['qty'] > 0) {
                        $list_report[] = (object) $item;
                    } else if ($stock == false) {
                        $list_report[] = (object) $item;
                    }
                }
            } else {
                if ($product->is_variant) {
                    $variant_all = ProductVariant::where('product_id', $product->id)->get();
                    foreach ($variant_all as $key => $variant) {
                        $qty = Product_Warehouse::where([
                            ['product_id', $product->id],
                            ['variant_id', $variant->variant_id],
                            ['warehouse_id', $warehouse_id],
                        ])->sum('qty');
                        $qtystock = $qty != null ? $qty : 0;
                        $name_variant = Variant::find($variant->variant_id)->name;
                        $item = array(
                            'id' => $product->id,
                            'code' => $product->code,
                            'variant_id' => $variant->variant_id,
                            'product' => $product->name . ' [' . $name_variant . ']',
                            'category' => $product->category->name,
                            'brand' => $brand_name,
                            'unit_code' => $unit_code,
                            'qty' => $qtystock,
                        );
                        if ($stock && $item['qty'] > 0) {
                            $list_report[] = (object) $item;
                        } else if ($stock == false) {
                            $list_report[] = (object) $item;
                        }
                    }
                } else {
                    $qty = Product_Warehouse::where([
                        ['product_id', $product->id],
                        ['warehouse_id', $warehouse_id],
                    ])->sum('qty');
                    $qtystock = $qty != null ? $qty : 0;
                    $item = array(
                        'id' => $product->id,
                        'code' => $product->code,
                        'variant_id' => null,
                        'product' => $product->name,
                        'category' => $product->category->name,
                        'brand' => $brand_name,
                        'unit_code' => $unit_code,
                        'qty' => $qtystock,
                    );
                    if ($stock && $item['qty'] > 0) {
                        $list_report[] = (object) $item;
                    } else if ($stock == false) {
                        $list_report[] = (object) $item;
                    }
                }
            }
        }
        Log::info("Reportes generado filtrando: " . sizeof($list_report) . " productos");
        if (Auth::user()->role_id > 2 && Auth::user()->biller_id) {
            $lims_warehouse_list = app(BillerController::class)::warehouseAuthorizate(Auth::user()->biller_id);
        } else {
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        }
        return view('report.product_report', compact('stock', 'list_report', 'lims_warehouse_list', 'warehouse_id'));
    }

    public function purchaseReport(Request $request)
    {
        $data = $request->all();
        $start_date = $data['start_date'];
        $end_date = $data['end_date'];
        $warehouse_id = $data['warehouse_id'];
        $product_id = [];
        $variant_id = [];
        $product_name = [];
        $product_qty = [];
        $purchase_references = [];
        $purchase_ref_all = [];
        $lims_product_all = Product::select('id', 'name', 'qty', 'is_variant')->where('is_active', true)->get();
        foreach ($lims_product_all as $product) {
            $lims_product_purchase_data = null;
            $variant_id_all = [];
            if ($warehouse_id == 0) {
                if ($product->is_variant) {
                    $variant_id_all = ProductPurchase::distinct('variant_id')->where('product_id', $product->id)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->pluck('variant_id');
                    $purchase_ref_all = ProductPurchase::distinct('variant_id')->where('product_id', $product->id)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->pluck('purchase_id');
                } else {
                    $lims_product_purchase_data = ProductPurchase::where('product_id', $product->id)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->first();
                }
            } else {
                if ($product->is_variant) {
                    $variant_id_all = DB::table('purchases')
                        ->join('product_purchases', 'purchases.id', '=', 'product_purchases.purchase_id')
                        ->distinct('variant_id')
                        ->where([
                            ['product_purchases.product_id', $product->id],
                            ['purchases.warehouse_id', $warehouse_id],
                        ])->whereDate('purchases.created_at', '>=', $start_date)
                        ->whereDate('purchases.created_at', '<=', $end_date)
                        ->pluck('variant_id');
                    $purchase_ref_all = DB::table('purchases')
                        ->join('product_purchases', 'purchases.id', '=', 'product_purchases.purchase_id')
                        ->distinct('variant_id')
                        ->where([
                            ['product_purchases.product_id', $product->id],
                            ['purchases.warehouse_id', $warehouse_id],
                        ])->whereDate('purchases.created_at', '>=', $start_date)
                        ->whereDate('purchases.created_at', '<=', $end_date)
                        ->pluck('purchases.reference_no');
                } else {
                    $lims_product_purchase_data = DB::table('purchases')
                        ->join('product_purchases', 'purchases.id', '=', 'product_purchases.purchase_id')->where([
                                ['product_purchases.product_id', $product->id],
                                ['purchases.warehouse_id', $warehouse_id],
                            ])->whereDate('purchases.created_at', '>=', $start_date)
                        ->whereDate('purchases.created_at', '<=', $end_date)
                        ->first();
                }
            }

            if ($lims_product_purchase_data) {
                $purchase_data = Purchase::select('reference_no')->find($lims_product_purchase_data->purchase_id);
                $product_name[] = $product->name;
                $product_id[] = $product->id;
                $variant_id[] = null;
                $purchase_references[] = $purchase_data->reference_no;
                if ($warehouse_id == 0) {
                    $product_qty[] = $product->qty;
                } else {
                    $product_qty[] = Product_Warehouse::where([
                        ['product_id', $product->id],
                        ['warehouse_id', $warehouse_id],
                    ])->sum('qty');
                }
            } elseif (count($variant_id_all)) {
                foreach ($variant_id_all as $key => $variantId) {
                    $variant_data = Variant::find($variantId);
                    $product_name[] = $product->name . ' [' . $variant_data->name . ']';
                    $product_id[] = $product->id;
                    $variant_id[] = $variant_data->id;
                    $purchase_data = Purchase::select('reference_no')->find($purchase_ref_all[0]);
                    $purchase_references[] = $purchase_data->reference_no;
                    if ($warehouse_id == 0) {
                        $product_qty[] = ProductVariant::FindExactProduct($product->id, $variant_data->id)->first()->qty;
                    } else {
                        $product_qty[] = Product_Warehouse::where([
                            ['product_id', $product->id],
                            ['variant_id', $variant_data->id],
                            ['warehouse_id', $warehouse_id],
                        ])->first()->qty;
                    }
                }
            }
        }

        if (Auth::user()->role_id > 2 && Auth::user()->biller_id) {
            $lims_warehouse_list = app(BillerController::class)::warehouseAuthorizate(Auth::user()->biller_id);
        } else {
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        }
        return view('report.purchase_report', compact('product_id', 'variant_id', 'product_name', 'product_qty', 'start_date', 'end_date', 'lims_warehouse_list', 'warehouse_id', 'purchase_references'));
    }

    public function saleReport(Request $request)
    {
        $data = $request->all();
        $start_date = $data['start_date'];
        $end_date = $data['end_date'];
        $warehouse_id = $data['warehouse_id'];
        $product_id = [];
        $variant_id = [];
        $product_name = [];
        $product_qty = [];
        $lims_product_all = Product::select('id', 'name', 'qty', 'is_variant')->where('is_active', true)->get();
        foreach ($lims_product_all as $product) {
            $lims_product_sale_data = null;
            $variant_id_all = [];
            if ($warehouse_id == 0) {
                if ($product->is_variant) {
                    $variant_id_all = Product_Sale::distinct('variant_id')->where('product_id', $product->id)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->pluck('variant_id');
                } else {
                    $lims_product_sale_data = Product_Sale::where('product_id', $product->id)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->first();
                }
            } else {
                if ($product->is_variant) {
                    $variant_id_all = DB::table('sales')
                        ->join('product_sales', 'sales.id', '=', 'product_sales.sale_id')
                        ->distinct('variant_id')
                        ->where([
                            ['product_sales.product_id', $product->id],
                            ['sales.warehouse_id', $warehouse_id],
                        ])->whereDate('sales.created_at', '>=', $start_date)
                        ->whereDate('sales.created_at', '<=', $end_date)
                        ->pluck('variant_id');
                } else {
                    $lims_product_sale_data = DB::table('sales')
                        ->join('product_sales', 'sales.id', '=', 'product_sales.sale_id')->where([
                                ['product_sales.product_id', $product->id],
                                ['sales.warehouse_id', $warehouse_id],
                            ])->whereDate('sales.created_at', '>=', $start_date)
                        ->whereDate('sales.created_at', '<=', $end_date)
                        ->first();
                }
            }
            if ($lims_product_sale_data) {
                $product_name[] = $product->name;
                $product_id[] = $product->id;
                $variant_id[] = null;
                if ($warehouse_id == 0) {
                    $product_qty[] = $product->qty;
                } else {
                    $product_qty[] = Product_Warehouse::where([
                        ['product_id', $product->id],
                        ['warehouse_id', $warehouse_id],
                    ])->sum('qty');
                }
            } elseif (count($variant_id_all)) {
                foreach ($variant_id_all as $key => $variantId) {
                    $variant_data = Variant::find($variantId);
                    $product_name[] = $product->name . ' [' . $variant_data->name . ']';
                    $product_id[] = $product->id;
                    $variant_id[] = $variant_data->id;
                    if ($warehouse_id == 0) {
                        $product_qty[] = ProductVariant::FindExactProduct($product->id, $variant_data->id)->first()->qty;
                    } else {
                        $product_qty[] = Product_Warehouse::where([
                            ['product_id', $product->id],
                            ['variant_id', $variant_data->id],
                            ['warehouse_id', $warehouse_id],
                        ])->first()->qty;
                    }
                }
            }
        }
        // dd($product_qty);
        if (Auth::user()->role_id > 2 && Auth::user()->biller_id) {
            $lims_warehouse_list = app(BillerController::class)::warehouseAuthorizate(Auth::user()->biller_id);
        } else {
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        }
        return view('report.sale_report', compact('product_id', 'variant_id', 'product_name', 'product_qty', 'start_date', 'end_date', 'lims_warehouse_list', 'warehouse_id'));
    }

    public function saleBillerReport(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $warehouse_id = $request->warehouse_id;
        $biller_id = $request->biller_id;
        if ($warehouse_id == 0) {
            $sales = Sale::with('customer')
                ->where($biller_id == 0 ? [] : [['biller_id', $biller_id]])
                ->whereDate('date_sell', ">=", $start_date)
                ->whereDate('date_sell', "<=", $end_date)
                ->get();
        } else {
            $sales = Sale::with('customer')
                ->where($biller_id == 0 ? [['warehouse_id', $warehouse_id]] : [['warehouse_id', $warehouse_id], ['biller_id', $biller_id]])
                ->whereDate('date_sell', ">=", $start_date)
                ->whereDate('date_sell', "<=", $end_date)
                ->get();
        }
        $lims_tax = Tax::where('is_active', true)->first();
        if (Auth::user()->role_id > 2 && Auth::user()->biller_id) {
            $billers = Biller::where('id', Auth::user()->biller_id)->where('is_active', true)->get();
            $lims_warehouse_list = app(BillerController::class)::warehouseAuthorizate(Auth::user()->biller_id);
        } else {
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $billers = Biller::where('is_active', true)->get();
        }
        return view('report.sale_biller_report', compact('sales', 'start_date', 'end_date', 'warehouse_id', 'lims_warehouse_list', 'billers', 'biller_id', 'lims_tax'));
    }

    public function saleCustomerReport(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $warehouse_id = $request->warehouse_id;
        $customer_id = $request->customer_id;
        if ($warehouse_id == 0) {
            $sales = Sale::with('customer')
                ->where($customer_id == 0 ? [] : [['customer_id', $customer_id]])
                ->whereDate('date_sell', ">=", $start_date)
                ->whereDate('date_sell', "<=", $end_date)
                ->get();
        } else {
            $sales = Sale::with('customer')
                ->where($customer_id == 0 ? [['warehouse_id', $warehouse_id]] : [['warehouse_id', $warehouse_id], ['customer_id', $customer_id]])
                ->whereDate('date_sell', ">=", $start_date)
                ->whereDate('date_sell', "<=", $end_date)
                ->get();
        }
        $lims_tax = Tax::where('is_active', true)->first();
        if (Auth::user()->role_id > 2 && Auth::user()->biller_id) {
            $lims_warehouse_list = app(BillerController::class)::warehouseAuthorizate(Auth::user()->biller_id);
        } else {
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        }
        $customers = Customer::where('is_active', true)->get();
        return view('report.sale_customer_report', compact('sales', 'start_date', 'end_date', 'warehouse_id', 'lims_warehouse_list', 'customers', 'customer_id', 'lims_tax'));
    }

    public function saleProductReport(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $end_date_temp = $end_date;
        $end_date = $end_date . " 23:59:59";
        $category_id = $request->category_id;

        $product_id = [];
        $variant_id = [];
        $product_name = [];
        $product_qty = [];
        $lims_product_all = Product::select('id', 'name', 'qty', 'is_variant')->where('is_active', true)->get();
        foreach ($lims_product_all as $product) {
            $lims_product_sale_data = null;
            $variant_id_all = [];
            if ($category_id == 0) {
                if ($product->is_variant) {
                    $variant_id_all = Product_Sale::distinct('variant_id')->where('product_id', $product->id)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->pluck('variant_id');
                } else {
                    $lims_product_sale_data = Product_Sale::where('product_id', $product->id)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->first();
                }
            } else {
                if ($product->is_variant) {
                    $variant_id_all = DB::table('sales')
                        ->join('product_sales', 'sales.id', '=', 'product_sales.sale_id')
                        ->distinct('variant_id')
                        ->where([
                            ['product_sales.product_id', $product->id],
                            ['product_sales.category_id', $category_id],
                        ])->whereDate('sales.date_sell', '>=', $start_date)
                        ->whereDate('sales.date_sell', '<=', $end_date)
                        ->pluck('variant_id');
                } else {
                    $lims_product_sale_data = DB::table('sales')
                        ->join('product_sales', 'sales.id', '=', 'product_sales.sale_id')->where([
                                ['product_sales.product_id', $product->id],
                                ['product_sales.category_id', $category_id],
                            ])->whereDate('sales.date_sell', '>=', $start_date)
                        ->whereDate('sales.date_sell', '<=', $end_date)
                        ->first();
                }
            }
            if ($lims_product_sale_data) {
                $product_name[] = $product->name;
                $product_id[] = $product->id;
                $variant_id[] = null;
                if ($category_id == 0) {
                    $product_qty[] = $product->qty;
                } else {
                    $product_qty[] = Product_Sale::where([
                        ['product_id', $product->id],
                        ['category_id', $category_id]
                    ])
                        ->whereDate('created_at', '>=', $start_date)
                        ->whereDate('created_at', '<=', $end_date)->sum('qty');
                }
            } elseif (count($variant_id_all)) {
                foreach ($variant_id_all as $key => $variantId) {
                    $variant_data = Variant::find($variantId);
                    $product_name[] = $product->name . ' [' . $variant_data->name . ']';
                    $product_id[] = $product->id;
                    $variant_id[] = $variant_data->id;
                    if ($category_id == 0) {
                        $product_qty[] = ProductVariant::FindExactProduct($product->id, $variant_data->id)->first()->qty;
                    } else {
                        $product_qty[] = Product_Sale::where([
                            ['product_id', $product->id],
                            ['variant_id', $variant_data->id],
                            ['category_id', $category_id],
                        ])->first()->qty;
                    }
                }
            }
        }
        $end_date = $end_date_temp;
        $lims_category_all = Category::where('is_active', true)->get();
        return view('report.sale_product_report', compact('product_id', 'variant_id', 'product_name', 'product_qty', 'start_date', 'end_date', 'category_id', 'lims_category_all'));
    }

    public function productFinishReport(Request $request)
    {
        $start_date = date('Y-m-d', strtotime(' -7 day'));
        $end_date = date('Y-m-d');
        $end_date_temp = $end_date;
        $end_date = $end_date . " 23:59:59";
        $lims_product_all = Product::select('id', 'name', 'code')->where([['type', 'producto_terminado'], ['is_active', true]])->get();
        $total_sale = 0;
        $total_insumo = 0;
        $total_utilbruto = 0;
        $totalAmount_sale = 0;
        $product_id = 0;
        $product_details = [];
        $product_list = [];
        if (!is_null($request->start_date) && !empty($request->start_date) && !is_null($request->end_date) || !empty($request->end_date)) {
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $end_date_temp = $end_date;
            $end_date = $end_date . " 23:59:59";
        }
        if (!is_null($request->product_id)) {
            $product_id = $request->product_id;
        } else {
            $product_id = $lims_product_all[0]->id;
        }
        if ($product_id > 0) {
            $lims_sale_list = Sale::select('id')->where([['sale_status', '1']])->whereBetween('date_sell', [$start_date, $end_date])->get();
            foreach ($lims_sale_list as $sale) {
                $lims_prosale_list = Product_Sale::where([['sale_id', $sale->id], ['product_id', $product_id]])->get();

                foreach ($lims_prosale_list as $product_sale) {
                    $lims_product_data = Product::find($product_sale->product_id);
                    $product_list = explode(",", $lims_product_data->product_list);
                    $qty_list = explode(",", $lims_product_data->qty_list);
                    $total_sale = $total_sale + $product_sale->qty;
                    $totalAmount_sale = $totalAmount_sale + $product_sale->total;
                }
            }
        }
        if ($product_list) {
            foreach ($product_list as $key => $pro) {
                $insumo = Product::select('id', 'name', 'sale_unit_id', 'cost')->find($pro);
                $unit = Unit::select('id', 'unit_code')->find($insumo->sale_unit_id);
                $qty = $qty_list[$key] * $total_sale;
                $product_details[] = array(
                    'id' => $insumo->id,
                    'name' => $insumo->name,
                    'qty' => $qty,
                    'unit' => $unit->unit_code,
                    'cost' => $insumo->cost,
                    'costotal' => $insumo->cost * $qty,
                );
                $total_insumo = $total_insumo + ($insumo->cost * $qty);
            }
        }
        $total_utilbruto = $totalAmount_sale - $total_insumo;
        $end_date = $end_date_temp;
        return view('report.product_finish', compact('product_id', 'lims_product_all', 'start_date', 'end_date', 'product_details', 'total_sale', 'totalAmount_sale', 'total_insumo', 'total_utilbruto'));
    }

    public function paymentReportByDate(Request $request)
    {
        $setting = $general_setting = GeneralSetting::latest()->first();
        $data = $request->all();
        $start_date = $data['start_date'];
        $end_date = $data['end_date'];
        $kind_payment = $data['kind_payment'];
        $report_data = [];
        $report_data_list = collect();
        switch ($kind_payment) {
            case 1:
                $lims_data = Sale::whereDate('date_sell', '>=', $start_date)->whereDate('date_sell', '<=', $end_date)->get();
                foreach ($lims_data as $sale) {
                    $due = $sale->grand_total;
                    $payments = Payment::where('sale_id', $sale->id)->get();
                    if (sizeof($payments) > 0) {
                        foreach ($payments as $key => $payment) {
                            $due = $due - $payment->amount;
                            $report_data['date'] = date($setting->date_format, strtotime($payment->created_at->toDateString())) . ' ' . $payment->created_at->toTimeString();
                            $report_data['reference_payment'] = $payment->payment_reference;
                            $report_data['reference_sale'] = $sale->reference_no;
                            $report_data['due'] = $due;
                            $report_data['amount'] = $payment->amount;
                            $report_data['method'] = $payment->paying_method;
                            $report_data['user_id'] = $payment->user_id;
                            $report_data_list[] = (object) $report_data;
                        }
                    } else {
                        $report_data['date'] = date($setting->date_format, strtotime($sale->date_sell)) . ' ' . date('H:s:i', strtotime($sale->date_sell));
                        $report_data['reference_payment'] = "";
                        $report_data['reference_sale'] = $sale->reference_no;
                        $report_data['due'] = $sale->grand_total;
                        $report_data['amount'] = 0.00;
                        $report_data['method'] = "";
                        $report_data['user_id'] = $sale->user_id;
                        $report_data_list[] = (object) $report_data;
                    }
                }
                break;
            case 2:
                $lims_data = Purchase::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->get();
                foreach ($lims_data as $purchase) {
                    $due = $purchase->grand_total;
                    $payments = Payment::where('purchase_id', $purchase->id)->get();
                    if (sizeof($payments) > 0) {
                        foreach ($payments as $key => $payment) {
                            $due = $due - $payment->amount;
                            $report_data['date'] = date($setting->date_format, strtotime($payment->created_at->toDateString())) . ' ' . $payment->created_at->toTimeString();
                            $report_data['reference_payment'] = $payment->payment_reference;
                            $report_data['reference_purchase'] = $purchase->reference_no;
                            $report_data['due'] = $due;
                            $report_data['amount'] = $payment->amount;
                            $report_data['method'] = $payment->paying_method;
                            $report_data['user_id'] = $payment->user_id;
                            $report_data_list[] = (object) $report_data;
                        }
                    } else {
                        $report_data['date'] = date($setting->date_format, strtotime($purchase->created_at->toDateString())) . ' ' . $purchase->created_at->toTimeString();
                        $report_data['reference_payment'] = "";
                        $report_data['reference_purchase'] = $purchase->reference_no;
                        $report_data['due'] = $due;
                        $report_data['amount'] = 0.00;
                        $report_data['method'] = "";
                        $report_data['user_id'] = $purchase->user_id;
                        $report_data_list[] = (object) $report_data;
                    }
                }
                break;
            default:
                $report_data_list = [];
        }
        return view('report.payment_report', compact('report_data_list', 'start_date', 'end_date', 'kind_payment'));
    }

    public function warehouseReport(Request $request)
    {
        $data = $request->all();
        $warehouse_id = $data['warehouse_id'];
        $start_date = $data['start_date'];
        $end_date = $data['end_date'];

        $lims_purchase_data = Purchase::where('warehouse_id', $warehouse_id)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->orderBy('created_at', 'desc')->get();
        $lims_sale_data = Sale::with('customer')->where('warehouse_id', $warehouse_id)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->orderBy('created_at', 'desc')->get();
        $lims_quotation_data = Quotation::with('customer')->where('warehouse_id', $warehouse_id)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->orderBy('created_at', 'desc')->get();
        $lims_return_data = Returns::with('customer', 'biller')->where([['warehouse_id', $warehouse_id], ['is_active', true]])->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->orderBy('created_at', 'desc')->get();
        $lims_expense_data = Expense::with('expenseCategory')->where('warehouse_id', $warehouse_id)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->orderBy('created_at', 'desc')->get();

        $lims_product_purchase_data = [];
        $lims_product_sale_data = [];
        $lims_product_quotation_data = [];
        $lims_product_return_data = [];

        foreach ($lims_purchase_data as $key => $purchase) {
            $lims_product_purchase_data[$key] = ProductPurchase::where('purchase_id', $purchase->id)->get();
        }
        foreach ($lims_sale_data as $key => $sale) {
            $lims_product_sale_data[$key] = Product_Sale::where('sale_id', $sale->id)->get();
        }
        foreach ($lims_quotation_data as $key => $quotation) {
            $lims_product_quotation_data[$key] = ProductQuotation::where('quotation_id', $quotation->id)->get();
        }
        foreach ($lims_return_data as $key => $return) {
            $lims_product_return_data[$key] = ProductReturn::where([['return_id', $return->id], ['is_active', true]])->get();
        }
        if (Auth::user()->role_id > 2 && Auth::user()->biller_id) {
            $lims_warehouse_list = app(BillerController::class)::warehouseAuthorizate(Auth::user()->biller_id);
        } else {
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        }
        return view('report.warehouse_report', compact('warehouse_id', 'start_date', 'end_date', 'lims_purchase_data', 'lims_product_purchase_data', 'lims_sale_data', 'lims_product_sale_data', 'lims_warehouse_list', 'lims_quotation_data', 'lims_product_quotation_data', 'lims_return_data', 'lims_product_return_data', 'lims_expense_data'));
    }

    public function userReport(Request $request)
    {
        $data = $request->all();
        $user_id = $data['user_id'];
        $start_date = $data['start_date'];
        $end_date = $data['end_date'];
        $lims_product_sale_data = [];
        $lims_product_purchase_data = [];
        $lims_product_quotation_data = [];
        $lims_product_transfer_data = [];

        $lims_sale_data = Sale::with('customer', 'warehouse')->where('user_id', $user_id)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->orderBy('created_at', 'desc')->get();
        $lims_purchase_data = Purchase::with('warehouse')->where('user_id', $user_id)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->orderBy('created_at', 'desc')->get();
        $lims_quotation_data = Quotation::with('customer', 'warehouse')->where('user_id', $user_id)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->orderBy('created_at', 'desc')->get();
        $lims_transfer_data = Transfer::with('fromWarehouse', 'toWarehouse')->where('user_id', $user_id)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->orderBy('created_at', 'desc')->get();
        $lims_payment_data = DB::table('payments')
            ->where('user_id', $user_id)
            ->whereDate('payments.created_at', '>=', $start_date)
            ->whereDate('payments.created_at', '<=', $end_date)
            ->orderBy('created_at', 'desc')
            ->get();
        $lims_expense_data = Expense::with('warehouse', 'expenseCategory')->where('user_id', $user_id)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->orderBy('created_at', 'desc')->get();
        $lims_payroll_data = Payroll::with('employee')->where('user_id', $user_id)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->orderBy('created_at', 'desc')->get();

        foreach ($lims_sale_data as $key => $sale) {
            $lims_product_sale_data[$key] = Product_Sale::where('sale_id', $sale->id)->get();
        }
        foreach ($lims_purchase_data as $key => $purchase) {
            $lims_product_purchase_data[$key] = ProductPurchase::where('purchase_id', $purchase->id)->get();
        }
        foreach ($lims_quotation_data as $key => $quotation) {
            $lims_product_quotation_data[$key] = ProductQuotation::where('quotation_id', $quotation->id)->get();
        }
        foreach ($lims_transfer_data as $key => $transfer) {
            $lims_product_transfer_data[$key] = ProductTransfer::where('transfer_id', $transfer->id)->get();
        }

        $lims_user_list = User::where('is_active', true)->get();
        return view('report.user_report', compact('lims_sale_data', 'user_id', 'start_date', 'end_date', 'lims_product_sale_data', 'lims_payment_data', 'lims_user_list', 'lims_purchase_data', 'lims_product_purchase_data', 'lims_quotation_data', 'lims_product_quotation_data', 'lims_transfer_data', 'lims_product_transfer_data', 'lims_expense_data', 'lims_payroll_data'));
    }

    public function customerReport(Request $request)
    {
        $data = $request->all();
        $customer_id = $data['customer_id'];
        $start_date = $data['start_date'];
        $end_date = $data['end_date'];
        $lims_sale_data = Sale::with('warehouse')->where('customer_id', $customer_id)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->orderBy('created_at', 'desc')->get();
        $lims_quotation_data = Quotation::with('warehouse')->where('customer_id', $customer_id)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->orderBy('created_at', 'desc')->get();
        $lims_return_data = Returns::with('warehouse', 'biller')->where([['customer_id', $customer_id], ['is_active', true]])->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->orderBy('created_at', 'desc')->get();
        $lims_payment_data = DB::table('payments')
            ->join('sales', 'payments.sale_id', '=', 'sales.id')
            ->where('customer_id', $customer_id)
            ->whereDate('payments.created_at', '>=', $start_date)
            ->whereDate('payments.created_at', '<=', $end_date)
            ->select('payments.*', 'sales.reference_no as sale_reference')
            ->orderBy('payments.created_at', 'desc')
            ->get();

        $lims_product_sale_data = [];
        $lims_product_quotation_data = [];
        $lims_product_return_data = [];

        foreach ($lims_sale_data as $key => $sale) {
            $lims_product_sale_data[$key] = Product_Sale::where('sale_id', $sale->id)->get();
        }
        foreach ($lims_quotation_data as $key => $quotation) {
            $lims_product_quotation_data[$key] = ProductQuotation::where('quotation_id', $quotation->id)->get();
        }
        foreach ($lims_return_data as $key => $return) {
            $lims_product_return_data[$key] = ProductReturn::where([['return_id', $return->id], ['is_active', true]])->get();
        }
        $lims_customer_list = Customer::where('is_active', true)->get();
        return view('report.customer_report', compact('lims_sale_data', 'customer_id', 'start_date', 'end_date', 'lims_product_sale_data', 'lims_payment_data', 'lims_customer_list', 'lims_quotation_data', 'lims_product_quotation_data', 'lims_return_data', 'lims_product_return_data'));
    }

    public function supplierReport(Request $request)
    {
        $data = $request->all();
        $supplier_id = $data['supplier_id'];
        $start_date = $data['start_date'];
        $end_date = $data['end_date'];
        $lims_purchase_data = Purchase::with('warehouse')->where('supplier_id', $supplier_id)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->orderBy('created_at', 'desc')->get();
        $lims_quotation_data = Quotation::with('warehouse', 'customer')->where('supplier_id', $supplier_id)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->orderBy('created_at', 'desc')->get();
        $lims_return_data = ReturnPurchase::with('warehouse')->where('supplier_id', $supplier_id)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->orderBy('created_at', 'desc')->get();
        $lims_payment_data = DB::table('payments')
            ->join('purchases', 'payments.purchase_id', '=', 'purchases.id')
            ->where('supplier_id', $supplier_id)
            ->whereDate('payments.created_at', '>=', $start_date)
            ->whereDate('payments.created_at', '<=', $end_date)
            ->select('payments.*', 'purchases.reference_no as purchase_reference')
            ->orderBy('payments.created_at', 'desc')
            ->get();

        $lims_product_purchase_data = [];
        $lims_product_quotation_data = [];
        $lims_product_return_data = [];

        foreach ($lims_purchase_data as $key => $purchase) {
            $lims_product_purchase_data[$key] = ProductPurchase::where('purchase_id', $purchase->id)->get();
        }
        foreach ($lims_return_data as $key => $return) {
            $lims_product_return_data[$key] = PurchaseProductReturn::where('return_id', $return->id)->get();
        }
        foreach ($lims_quotation_data as $key => $quotation) {
            $lims_product_quotation_data[$key] = ProductQuotation::where('quotation_id', $quotation->id)->get();
        }
        $lims_supplier_list = Supplier::where('is_active', true)->get();
        return view('report.supplier_report', compact('lims_purchase_data', 'lims_product_purchase_data', 'lims_payment_data', 'supplier_id', 'start_date', 'end_date', 'lims_supplier_list', 'lims_quotation_data', 'lims_product_quotation_data', 'lims_return_data', 'lims_product_return_data'));
    }

    public function dueReportByDate(Request $request)
    {
        $data = $request->all();
        $start_date = $data['start_date'];
        $end_date = $data['end_date'];
        $lims_sale_data = Sale::where('payment_status', '!=', 4)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->get();

        return view('report.due_report', compact('lims_sale_data', 'start_date', 'end_date'));
    }

    public function saleCourtesyReport(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $category_id = $request->category_id;

        $product_id = [];
        $variant_id = [];
        $product_name = [];
        $product_qty = [];
        $lims_product_all = Product::select('id', 'name', 'qty', 'is_variant')->where([['is_active', true], ['courtesy', 'TRUE']])->get();
        foreach ($lims_product_all as $product) {
            $lims_product_sale_data = null;
            $variant_id_all = [];
            if ($category_id == 0) {
                if ($product->is_variant) {
                    $variant_id_all = Product_Sale::distinct('variant_id')
                        ->where([['product_id', $product->id], ['total', 0]])->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->pluck('variant_id');
                } else {
                    $lims_product_sale_data = Product_Sale::where([['product_id', $product->id], ['total', 0]])->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->first();
                }
            } else {
                if ($product->is_variant) {
                    $variant_id_all = DB::table('sales')
                        ->join('product_sales', 'sales.id', '=', 'product_sales.sale_id')
                        ->distinct('variant_id')
                        ->where([
                            ['product_sales.product_id', $product->id],
                            ['product_sales.category_id', $category_id],
                            ['product_sales.total', 0],
                        ])->whereDate('sales.created_at', '>=', $start_date)
                        ->whereDate('sales.created_at', '<=', $end_date)
                        ->pluck('variant_id');
                } else {
                    $lims_product_sale_data = DB::table('sales')
                        ->join('product_sales', 'sales.id', '=', 'product_sales.sale_id')->where([
                                ['product_sales.product_id', $product->id],
                                ['product_sales.category_id', $category_id],
                                ['product_sales.total', 0],
                            ])->whereDate('sales.created_at', '>=', $start_date)
                        ->whereDate('sales.created_at', '<=', $end_date)
                        ->first();
                }
            }
            if ($lims_product_sale_data) {
                $product_name[] = $product->name;
                $product_id[] = $product->id;
                $variant_id[] = null;
                if ($category_id == 0) {
                    $product_qty[] = $product->qty;
                } else {
                    $product_qty[] = Product_Sale::where([
                        ['product_id', $product->id],
                        ['category_id', $category_id]
                    ])
                        ->whereDate('created_at', '>=', $start_date)
                        ->whereDate('created_at', '<=', $end_date)->sum('qty');
                }
            } elseif (count($variant_id_all)) {
                foreach ($variant_id_all as $key => $variantId) {
                    $variant_data = Variant::find($variantId);
                    $product_name[] = $product->name . ' [' . $variant_data->name . ']';
                    $product_id[] = $product->id;
                    $variant_id[] = $variant_data->id;
                    if ($category_id == 0) {
                        $product_qty[] = ProductVariant::FindExactProduct($product->id, $variant_data->id)->first()->qty;
                    } else {
                        $product_qty[] = Product_Sale::where([
                            ['product_id', $product->id],
                            ['variant_id', $variant_data->id],
                            ['category_id', $category_id],
                        ])->first()->qty;
                    }
                }
            }
        }
        $lims_category_all = Category::where('is_active', true)->get();
        return view('report.sale_courtesy_report', compact('product_id', 'variant_id', 'product_name', 'product_qty', 'start_date', 'end_date', 'category_id', 'lims_category_all'));
    }

    public function saleServiceReport(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $end_date_temp = $end_date;
        $end_date = $end_date . " 23:59:59";
        $employee_id = $request->employee_id;
        if ($employee_id == 0) {
            $comisiones = Product_Sale::select('product_sales.id', 'sales.date_sell', 'product_sales.net_unit_price as total', 'product_sales.employee_id', 'sales.reference_no', 'products.name')
                ->whereNotNull('employee_id')->join('employees', 'product_sales.employee_id', '=', 'employees.id')
                ->join('sales', 'product_sales.sale_id', '=', 'sales.id')->join('products', 'product_sales.product_id', '=', 'products.id')
                ->whereDate('sales.date_sell', ">=", $start_date)
                ->whereDate('sales.date_sell', "<=", $end_date)
                ->where('employees.contract_type', 'COMISION_UNICA')
                ->get();
        } else {
            $comisiones = Product_Sale::select('product_sales.id', 'sales.date_sell', 'product_sales.net_unit_price as total', 'product_sales.employee_id', 'sales.reference_no', 'products.name')
                ->where('employee_id', $employee_id)
                ->join('sales', 'product_sales.sale_id', '=', 'sales.id')->join('products', 'product_sales.product_id', '=', 'products.id')
                ->whereDate('sales.date_sell', ">=", $start_date)
                ->whereDate('sales.date_sell', "<=", $end_date)
                ->get();
        }

        $lims_employees_list = Employee::select('id', 'name')->where([['is_active', true], ['contract_type', 'COMISION_UNICA']])->get();
        $end_date = $end_date_temp;
        return view('report.employee_service_up', compact('comisiones', 'start_date', 'end_date', 'employee_id', 'lims_employees_list'));
    }

    public function saleServiceComissionReport(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $start_date_temp = $start_date;
        $end_date_temp = $end_date;
        $start_date = $start_date . " 00:00:00";
        $end_date = $end_date . " 23:59:59";
        $employee_id = $request->employee_id;
        $mode = ((isset($request->guess) && ($request->guess == 'true')) ? true : false);

        if ($employee_id == 0) {
            $comisiones = Product_Sale::select('product_sales.id', 'product_sales.sale_id', 'sales.date_sell', 'product_sales.total as total', 'product_sales.employee_id', 'sales.reference_no', 'products.name', 'product_sales.product_id', 'products.commission_percentage')
                ->whereNotNull('employee_id')->join('employees', 'product_sales.employee_id', '=', 'employees.id')
                ->join('sales', 'product_sales.sale_id', '=', 'sales.id')->join('products', 'product_sales.product_id', '=', 'products.id')
                ->whereDate('sales.date_sell', ">=", $start_date)
                ->whereDate('sales.date_sell', "<=", $end_date)
                ->where('employees.contract_type', 'COMISION_POR_SERVICIOS')
                ->get();
            $totalData = Product_Sale::select('product_sales.id', 'product_sales.sale_id', 'sales.date_sell', 'product_sales.total as total', 'product_sales.employee_id', 'sales.reference_no', 'products.name', 'product_sales.product_id', 'products.commission_percentage')
                ->whereNotNull('employee_id')->join('employees', 'product_sales.employee_id', '=', 'employees.id')
                ->join('sales', 'product_sales.sale_id', '=', 'sales.id')->join('products', 'product_sales.product_id', '=', 'products.id')
                ->whereDate('sales.date_sell', ">=", $start_date)
                ->whereDate('sales.date_sell', "<=", $end_date)
                ->where('employees.contract_type', 'COMISION_POR_SERVICIOS')
                ->count();
        } else {
            $comisiones = Product_Sale::select('product_sales.id', 'product_sales.sale_id', 'sales.date_sell', 'product_sales.total as total', 'product_sales.employee_id', 'sales.reference_no', 'products.name', 'product_sales.product_id', 'products.commission_percentage')
                ->where('employee_id', $employee_id)
                ->join('sales', 'product_sales.sale_id', '=', 'sales.id')->join('products', 'product_sales.product_id', '=', 'products.id')
                ->whereDate('sales.date_sell', ">=", $start_date)
                ->whereDate('sales.date_sell', "<=", $end_date)
                ->get();
            $totalData = Product_Sale::select('product_sales.id', 'product_sales.sale_id', 'sales.date_sell', 'product_sales.total as total', 'product_sales.employee_id', 'sales.reference_no', 'products.name', 'product_sales.product_id', 'products.commission_percentage')
                ->where('employee_id', $employee_id)
                ->join('sales', 'product_sales.sale_id', '=', 'sales.id')->join('products', 'product_sales.product_id', '=', 'products.id')
                ->whereDate('sales.date_sell', ">=", $start_date)
                ->whereDate('sales.date_sell', "<=", $end_date)
                ->count();
        }

        $lims_employees_list = Employee::select('id', 'name')->where([['is_active', true], ['contract_type', 'COMISION_POR_SERVICIOS']])->get();

        if ($mode) {
            $total_grand = 0;
            $total_qr = 0;
            $total_com = 0;
            $lims_pos_setting_data = PosSetting::select('qr_commission')->latest()->first();
            $data = [];
            $start = $request->input('start');
            $totalFiltered = $totalData;
            if ($request->input('length') != -1) {
                $limit = $request->input('length');
            } else {
                $limit = $totalData;
            }
            $comisionshow = Product_Sale::select('product_sales.id', 'product_sales.sale_id', 'sales.date_sell', 'product_sales.total as total', 'product_sales.employee_id', 'sales.reference_no', 'products.name', 'product_sales.product_id', 'products.commission_percentage')
                ->where('employee_id', $employee_id)
                ->join('sales', 'product_sales.sale_id', '=', 'sales.id')->join('products', 'product_sales.product_id', '=', 'products.id')
                ->whereDate('sales.date_sell', ">=", $start_date)
                ->whereDate('sales.date_sell', "<=", $end_date)
                ->offset($start)->limit($limit)->get();

            if (!empty($comisiones)) {
                /** Totales */
                foreach ($comisiones as $key => $comision) {
                    $total_grand = $total_grand + $comision->total;
                    if ($comision->commission_percentage == 0) {
                        $totalper = (float) $comision->total;
                    } else {
                        $totalper = ((float) $comision->commission_percentage * (float) $comision->total) / 100;
                    }
                    $payments = Payment::where([['sale_id', $comision->sale_id], ['paying_method', 'Qr_simple']])->get();
                    foreach ($payments as $payment) {
                        $totalper = $totalper - $lims_pos_setting_data->qr_commission;
                        $total_qr = $total_qr + $lims_pos_setting_data->qr_commission;
                    }
                    $total_com = $total_com + $totalper;
                }
                /*** Draw Table */
                foreach ($comisionshow as $key => $comision) {
                    $nestedData['id'] = $comision->id;
                    $nestedData['key'] = $key + 1;
                    $nestedData['reference_no'] = $comision->reference_no;
                    $nestedData['service'] = $comision->name;
                    $nestedData['employee'] = $comision->employee->name;
                    $nestedData['date'] = date(config('date_format'), strtotime($comision->date_sell));
                    $nestedData['total'] = number_format($comision->total, 2);
                    $nestedData['percentaje'] = number_format($comision->commission_percentage, 2);
                    if ($comision->commission_percentage == 0) {
                        $totalwithcom = (float) $comision->total;
                    } else {
                        $totalwithcom = ((float) $comision->commission_percentage * (float) $comision->total) / 100;
                    }
                    $payments = Payment::where([['sale_id', $comision->sale_id], ['paying_method', 'Qr_simple']])->get();
                    foreach ($payments as $payment) {
                        $totalwithcom = $totalwithcom - $lims_pos_setting_data->qr_commission;
                    }
                    if (sizeof($payments) > 0) {
                        $nestedData['comision_qr'] = number_format($lims_pos_setting_data->qr_commission, 2);
                    } else
                        $nestedData['comision_qr'] = number_format(0, 2);

                    $nestedData['comision'] = number_format($totalwithcom, 2);
                    $data[] = $nestedData;
                }
            }
            $json_data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $data,
                "total" => $total_grand,
                "total_qr" => $total_qr,
                "total_com" => $total_com
            );
            return $json_data;
        } else {
            // Compute overall totals across all records (not just current page)
            $total_grand = 0;
            $total_qr = 0;
            $total_com = 0;
            $lims_pos_setting_data = PosSetting::select('qr_commission')->latest()->first();
            if (!empty($comisiones)) {
                foreach ($comisiones as $comision) {
                    $total_grand += (float) $comision->total;
                    // Commission amount before QR deduction
                    if ($comision->commission_percentage == 0) {
                        $totalper = (float) $comision->total;
                    } else {
                        $totalper = ((float) $comision->commission_percentage * (float) $comision->total) / 100.0;
                    }
                    // Deduct per QR payment and accumulate QR commission
                    $payments = Payment::where([['sale_id', $comision->sale_id], ['paying_method', 'Qr_simple']])->get();
                    foreach ($payments as $payment) {
                        if ($lims_pos_setting_data) {
                            $totalper -= (float) $lims_pos_setting_data->qr_commission;
                            $total_qr += (float) $lims_pos_setting_data->qr_commission;
                        }
                    }
                    $total_com += $totalper;
                }
            }

            $start_date = $start_date_temp;
            $end_date = $end_date_temp;
            return view('report.employee_service_comission', compact('comisiones', 'start_date', 'end_date', 'employee_id', 'lims_employees_list', 'total_grand', 'total_qr', 'total_com'));
        }
    }

    public function generalallReport($start_date, $end_date, $category_id, $biller_id)
    {
        $end_date_temp = $end_date;
        $end_date = $end_date . " 23:59:59";
        $total_ingreso_2 = 0;
        $ingresosList = [];
        $egresosList = [];
        $result = [];
        $total_ingreso = 0;
        $total_egreso = 0;
        $total_general = 0;
        /* Ingreso */

        if ($category_id == 0 && $biller_id == 0) {

            $result[] = $this->salesAllRangeDate($start_date, $end_date);
            $ingresosList[] = $result[0]['saleresume'];
            $total_ingreso = $result[0]['total'];
        } else {

            if ($category_id != 0 && $biller_id == 0) {

                $result[] = $this->salesCategoryRangeDate($start_date, $end_date, $category_id);
                $ingresosList[] = $result[0]['saleresume'];
                $total_ingreso = $result[0]['total'];
            } else if ($category_id != 0 && $biller_id != 0) {

                $result[] = $this->salesBillCatRangeDate($start_date, $end_date, $category_id, $biller_id);
                $ingresosList[] = $result[0]['saleresume'];
                $total_ingreso = $result[0]['total'];
            } else if ($category_id == 0 && $biller_id != 0) {
                $result[] = $this->salesBillerRangeDate($start_date, $end_date, $biller_id);
                $ingresosList[] = $result[0]['saleresume'];
                $total_ingreso = $result[0]['total'];
            } else {
                $result[] = $this->salesAllRangeDate($start_date, $end_date);
                $ingresosList[] = $result[0]['saleresume'];
                $total_ingreso = $result[0]['total'];
            }
        }
        /* Egresos */
        if ($biller_id != 0) {
            $result = [];
            $result = $this->egresosBillerRangeDate($start_date, $end_date, $biller_id);
            $egresosList[] = $result['egresoresume'][0];
            $total_egreso = $result['total'];
        } else {
            $result = [];
            $result = $this->egresosAllRangeDate($start_date, $end_date);
            $egresosList[] = $result['egresoresume'][0];
            $total_egreso = $result['total'];
        }
        $total_general = $total_ingreso - $total_egreso;

        $lims_biller_list = Biller::where('is_active', true)->get();
        $lims_categorie_list = Category::where('is_active', true)->get();
        $end_date = $end_date_temp;
        return view('report.generall_report', compact('start_date', 'end_date', 'ingresosList', 'egresosList', 'total_ingreso', 'total_egreso', 'total_general', 'lims_biller_list', 'lims_categorie_list', 'biller_id', 'category_id'));
    }

    public function salesAllRangeDate($start_date, $end_date)
    {
        $total_ingreso = 0;
        $saleresume1 = [];
        $query3 = array(
            'payments.paying_method',
            'SUM(payments.amount) AS total',
        );
        $sales_list = Sale::select('id')->whereBetween('date_sell', [$start_date, $end_date])->get(); //don't need ->get() or ->first()
        $lims_sales_list = Payment::whereIn('payments.sale_id', $sales_list)->selectRaw(implode(',', $query3))->groupBy('payments.paying_method')->orderBy('payments.paying_method', 'ASC')->get();
        foreach ($lims_sales_list as $data) {
            $total_ingreso = $total_ingreso + $data->total;
            if ($data->paying_method == "Efectivo") {
                $saleresume1[] = array(
                    'id' => 1,
                    'name' => "Todos",
                    'paying_method' => "Efectivo",
                    'total' => $data->total,
                );
            } elseif ($data->paying_method == "Tarjeta_Credito_Debito") {
                $saleresume1[] = array(
                    'id' => 2,
                    'name' => "Todos",
                    'paying_method' => 'Tarjeta Credito/Debito',
                    'total' => $data->total,
                );
            } elseif ($data->paying_method == "Qr_simple" || $data->paying_method == "Qr_Simple") {
                $saleresume1[] = array(
                    'id' => 3,
                    'name' => "Todos",
                    'paying_method' => "QR",
                    'total' => $data->total,
                );
            } elseif ($data->paying_method == "Cheque") {
                $saleresume1[] = array(
                    'id' => 4,
                    'name' => "Todos",
                    'paying_method' => "Cheque",
                    'total' => $data->total,
                );
            } elseif ($data->paying_method == "Deposito") {
                $saleresume1[] = array(
                    'id' => 5,
                    'name' => "Todos",
                    'paying_method' => "Deposito",
                    'total' => $data->total,
                );
            }
        }

        // Ajustes de cuenta tipo INGRESO
        $lims_adjustment_account_ingreso_list = AdjustmentAccount::select('id', 'reference_no', 'amount', 'note')
            ->where([['is_active', true], ['type_adjustment', 'ING']])
            ->where('created_at', '>=', $start_date)
            ->where('created_at', '<=', $end_date)
            ->get();

        foreach ($lims_adjustment_account_ingreso_list as $adjustment) {
            $saleresume1[] = array(
                'id' => 6,
                'name' => "Todos",
                'paying_method' => "Ajuste Ingreso",
                'total' => $adjustment->amount,
                'reference' => $adjustment->reference_no,
                'detail' => $adjustment->note,
            );
            $total_ingreso += $adjustment->amount;
        }

        //$saleresume3[] = $saleresume1;
        return $data = array(
            'saleresume' => $saleresume1,
            'total' => $total_ingreso,
        );
    }

    public function salesCategoryRangeDate($start_date, $end_date, $category_id)
    {
        $total_ingreso = 0;
        $saleresume1 = [];
        $total_efectivo = 0;
        $total_tarjeta = 0;
        $total_cheque = 0;
        $total_qr = 0;
        $total_deposito = 0;

        $lims_category = Category::find($category_id);
        $sales_list = Sale::select('id')->whereBetween('date_sell', [$start_date, $end_date])->get(); //don't need ->get() or ->first()
        foreach ($sales_list as $sale) {
            $payment_data = Payment::where('sale_id', $sale->id)->first();
            $product_data = Product_Sale::where([['category_id', $category_id], ['sale_id', $sale->id]])->get();

            foreach ($product_data as $product) {
                if ($payment_data != null && $payment_data->paying_method == "Efectivo") {
                    $total_efectivo = $total_efectivo + $product->total;
                }
                if ($payment_data != null && $payment_data->paying_method == "Tarjeta_Credito_Debito") {
                    $total_tarjeta = $total_tarjeta + $product->total;
                }
                if ($payment_data != null && ($payment_data->paying_method == "Qr_simple" || $payment_data->paying_method == "Qr_Simple")) {
                    $total_qr = $total_qr + $product->total;
                }
                if ($payment_data != null && $payment_data->paying_method == "Cheque") {
                    $total_cheque = $total_cheque + $product->total;
                }
                if ($payment_data != null && $payment_data->paying_method == "Deposito") {
                    $total_deposito = $total_deposito + $product->total;
                }
            }
        }
        $saleresume1[] = array(
            'id' => 1,
            'name' => $lims_category->name,
            'paying_method' => "Efectivo",
            'total' => $total_efectivo,
        );
        $saleresume1[] = array(
            'id' => 2,
            'name' => $lims_category->name,
            'paying_method' => "Tarjeta Credito/Debito",
            'total' => $total_tarjeta,
        );
        $saleresume1[] = array(
            'id' => 3,
            'name' => $lims_category->name,
            'paying_method' => "QR",
            'total' => $total_qr,
        );
        $saleresume1[] = array(
            'id' => 4,
            'name' => $lims_category->name,
            'paying_method' => "Cheque",
            'total' => $total_cheque,
        );
        $saleresume1[] = array(
            'id' => 5,
            'name' => $lims_category->name,
            'paying_method' => "Deposito",
            'total' => $total_deposito,
        );
        $total_ingreso = $total_efectivo + $total_tarjeta + $total_qr + $total_cheque + $total_deposito;

        // Ajustes de cuenta tipo INGRESO
        $lims_adjustment_account_ingreso_list = AdjustmentAccount::select('id', 'reference_no', 'amount', 'note')
            ->where([['is_active', true], ['type_adjustment', 'ING']])
            ->where('created_at', '>=', $start_date)
            ->where('created_at', '<=', $end_date)
            ->get();

        foreach ($lims_adjustment_account_ingreso_list as $adjustment) {
            $saleresume1[] = array(
                'id' => 6,
                'name' => $lims_category->name,
                'paying_method' => "Ajuste Ingreso",
                'total' => $adjustment->amount,
                'reference' => $adjustment->reference_no,
                'detail' => $adjustment->note,
            );
            $total_ingreso += $adjustment->amount;
        }

        return $data = array(
            'saleresume' => $saleresume1,
            'total' => $total_ingreso,
        );
    }

    public function salesBillerRangeDate($start_date, $end_date, $biller_id)
    {
        $total_ingreso = 0;
        $total_efectivo = 0;
        $total_tarjeta = 0;
        $total_cheque = 0;
        $total_qr = 0;
        $total_deposito = 0;
        $saleresume1 = [];
        $biller_data = Biller::find($biller_id);
        $sales_list = Sale::select('id')->whereBetween('date_sell', [$start_date, $end_date])->get(); //don't need ->get() or ->first()
        foreach ($sales_list as $sale) {
            $payment_data = Payment::where('sale_id', $sale->id)->first();
            $product_data = Product_Sale::where([['sale_id', $sale->id]])->get();
            foreach ($product_data as $product) {
                if (
                    $payment_data != null && ($payment_data->account_id == $biller_data->account_id || $payment_data->account_id == $biller_data->account_id_deposito
                        || $payment_data->account_id == $biller_data->account_id_tarjeta || $payment_data->account_id == $biller_data->account_id_cheque
                        || $payment_data->account_id == $biller_data->account_id_giftcard || $payment_data->account_id == $biller_data->account_id_qr)
                ) {
                    if ($payment_data != null && $payment_data->paying_method == "Efectivo") {
                        $total_efectivo = $total_efectivo + $product->total;
                    }
                    if ($payment_data != null && $payment_data->paying_method == "Tarjeta_Credito_Debito") {
                        $total_tarjeta = $total_tarjeta + $product->total;
                    }
                    if ($payment_data != null && ($payment_data->paying_method == "Qr_simple" || $payment_data->paying_method == "Qr_Simple")) {
                        $total_qr = $total_qr + $product->total;
                    }
                    if ($payment_data != null && $payment_data->paying_method == "Cheque") {
                        $total_cheque = $total_cheque + $product->total;
                    }
                    if ($payment_data != null && $payment_data->paying_method == "Deposito") {
                        $total_deposito = $total_deposito + $product->total;
                    }
                }
            }
        }
        $saleresume1[] = array(
            'id' => 1,
            'name' => "Todos",
            'paying_method' => "Efectivo",
            'total' => $total_efectivo,
        );
        $saleresume1[] = array(
            'id' => 2,
            'name' => "Todos",
            'paying_method' => "Tarjeta Credito/Debito",
            'total' => $total_tarjeta,
        );
        $saleresume1[] = array(
            'id' => 3,
            'name' => "Todos",
            'paying_method' => "QR",
            'total' => $total_qr,
        );
        $saleresume1[] = array(
            'id' => 4,
            'name' => "Todos",
            'paying_method' => "Cheque",
            'total' => $total_cheque,
        );
        $saleresume1[] = array(
            'id' => 5,
            'name' => "Todos",
            'paying_method' => "Deposito",
            'total' => $total_deposito,
        );
        $total_ingreso = $total_efectivo + $total_tarjeta + $total_qr + $total_cheque + $total_deposito;

        // Ajustes de cuenta tipo INGRESO
        $lims_adjustment_account_ingreso_list = AdjustmentAccount::select('id', 'reference_no', 'amount', 'note')
            ->where([['is_active', true], ['type_adjustment', 'ING']])
            ->where('created_at', '>=', $start_date)
            ->where('created_at', '<=', $end_date)
            ->get();

        foreach ($lims_adjustment_account_ingreso_list as $adjustment) {
            $saleresume1[] = array(
                'id' => 6,
                'name' => "Todos",
                'paying_method' => "Ajuste Ingreso",
                'total' => $adjustment->amount,
                'reference' => $adjustment->reference_no,
                'detail' => $adjustment->note,
            );
            $total_ingreso += $adjustment->amount;
        }

        return $data = array(
            'saleresume' => $saleresume1,
            'total' => $total_ingreso,
        );
    }

    public function salesBillCatRangeDate($start_date, $end_date, $category_id, $biller_id)
    {
        $saleresume1 = [];
        $total_ingreso = 0;
        $total_efectivo = 0;
        $total_tarjeta = 0;
        $total_cheque = 0;
        $total_qr = 0;
        $total_deposito = 0;

        $biller_data = Biller::find($biller_id);
        $lims_category = Category::find($category_id);
        $sales_list = Sale::select('id')->whereBetween('date_sell', [$start_date, $end_date])->get(); //don't need ->get() or ->first()
        foreach ($sales_list as $sale) {
            $payment_data = Payment::where('sale_id', $sale->id)->first();
            $product_data = Product_Sale::where([['category_id', $category_id], ['sale_id', $sale->id]])->get();

            foreach ($product_data as $product) {
                if (
                    $payment_data != null && ($payment_data->account_id == $biller_data->account_id || $payment_data->account_id == $biller_data->account_id_deposito
                        || $payment_data->account_id == $biller_data->account_id_tarjeta || $payment_data->account_id == $biller_data->account_id_cheque
                        || $payment_data->account_id == $biller_data->account_id_giftcard || $payment_data->account_id == $biller_data->account_id_qr)
                ) {
                    if ($payment_data != null && $payment_data->paying_method == "Efectivo") {
                        $total_efectivo = $total_efectivo + $product->total;
                    }
                    if ($payment_data != null && $payment_data->paying_method == "Tarjeta_Credito_Debito") {
                        $total_tarjeta = $total_tarjeta + $product->total;
                    }
                    if ($payment_data != null && ($payment_data->paying_method == "Qr_simple" || $payment_data->paying_method == "Qr_Simple")) {
                        $total_qr = $total_qr + $product->total;
                    }
                    if ($payment_data != null && $payment_data->paying_method == "Cheque") {
                        $total_cheque = $total_cheque + $product->total;
                    }
                    if ($payment_data != null && $payment_data->paying_method == "Deposito") {
                        $total_deposito = $total_deposito + $product->total;
                    }
                }
            }
        }
        $saleresume1[] = array(
            'id' => 1,
            'name' => $lims_category->name,
            'paying_method' => "Efectivo",
            'total' => $total_efectivo,
        );
        $saleresume1[] = array(
            'id' => 2,
            'name' => $lims_category->name,
            'paying_method' => "Tarjeta Credito/Debito",
            'total' => $total_tarjeta,
        );
        $saleresume1[] = array(
            'id' => 3,
            'name' => $lims_category->name,
            'paying_method' => "QR",
            'total' => $total_qr,
        );
        $saleresume1[] = array(
            'id' => 4,
            'name' => $lims_category->name,
            'paying_method' => "Cheque",
            'total' => $total_cheque,
        );
        $saleresume1[] = array(
            'id' => 5,
            'name' => $lims_category->name,
            'paying_method' => "Deposito",
            'total' => $total_deposito,
        );
        $total_ingreso = $total_efectivo + $total_tarjeta + $total_qr + $total_cheque + $total_deposito;

        // Ajustes de cuenta tipo INGRESO
        $lims_adjustment_account_ingreso_list = AdjustmentAccount::select('id', 'reference_no', 'amount', 'note')
            ->where([['is_active', true], ['type_adjustment', 'ING']])
            ->where('created_at', '>=', $start_date)
            ->where('created_at', '<=', $end_date)
            ->get();

        foreach ($lims_adjustment_account_ingreso_list as $adjustment) {
            $saleresume1[] = array(
                'id' => 6,
                'name' => $lims_category->name,
                'paying_method' => "Ajuste Ingreso",
                'total' => $adjustment->amount,
                'reference' => $adjustment->reference_no,
                'detail' => $adjustment->note,
            );
            $total_ingreso += $adjustment->amount;
        }

        return $data = array(
            'saleresume' => $saleresume1,
            'total' => $total_ingreso,
        );
    }

    public function egresosAllRangeDate($start_date, $end_date, $purchase = true)
    {
        $details = [];
        $total_egreso = 0;
        $gastos = 0;
        $nominas = 0;
        $ajustegr = 0;
        $devolucion = 0;
        $transferencias = 0;
        $compras = 0;
        if ($purchase) {
            $lims_purchases_list = Purchase::select('purchases.id', 'purchases.reference_no', 'purchases.grand_total', 'payments.paying_method', 'purchases.note', 'payments.amount')
                ->join('payments', 'purchases.id', '=', 'payments.purchase_id')->whereDate('purchases.created_at', '>=', $start_date)->whereDate('purchases.created_at', '<=', $end_date)->get();
            $totalPurch = 0;
            $reference_no = 0;

            foreach ($lims_purchases_list as $purchase) {
                $totalPurch = $totalPurch + $purchase->amount;
                $reference_no = $reference_no + 1;
                $compras = $compras + $purchase->amount;
            }
            if ($totalPurch > 0) {
                $details[] = array(
                    'id' => 1,
                    'reference' => $reference_no,
                    'detail' => "Compras + Pago",
                    'amount' => $totalPurch,
                    'categorie' => "Compras",
                );
            }
        }
        $lims_returns_list = Returns::select('id', 'reference_no', 'grand_total', 'return_note')->where('is_active', true)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->get();
        foreach ($lims_returns_list as $return) {
            $devolucion = $devolucion + $return->grand_total;
            $details[] = array(
                'id' => $return->id,
                'reference' => $return->reference_no,
                'detail' => $return->return_note,
                'amount' => $return->grand_total,
                'categorie' => "return",
            );
        }

        /*$lims_send_money_via_transfers_list = MoneyTransfer::select('id', 'reference_no', 'amount')
        ->whereNotNull('from_account_id')->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->get();
        $totalTrans = 0;
        $reference_no = 0;
        foreach ($lims_send_money_via_transfers_list as $send_money_via_transfer) {
        $reference_no = $reference_no + 1;
        $totalTrans = $totalTrans + $send_money_via_transfer->amount;
        }

        if($totalTrans > 0){
        $details[] = array(
        'id' => $send_money_via_transfer->id,
        'reference' => $reference_no,
        'detail' => "Egreso de Efectivo",
        'amount' => $totalTrans,
        'categorie' => "Transferencias"
        );
        }*/
        $lims_expenses_list = Expense::select('id', 'reference_no', 'amount', 'note', 'expense_category_id')->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->get();
        $lims_categoriexpense_list = ExpenseCategory::get();

        foreach ($lims_categoriexpense_list as $cat) {
            $reference_no = 0;
            $totalExp = 0;
            $cat_id = null;
            foreach ($lims_expenses_list as $expense) {
                if ($cat->id == $expense->expense_category_id) {
                    $reference_no = $reference_no + 1;
                    $gastos = $gastos + $expense->amount;
                    $totalExp = $totalExp + $expense->amount;
                    $cat_id = $expense->expense_category_id;
                }
            }
            if ($totalExp > 0) {
                $details[] = array(
                    'id' => $cat->id,
                    'reference' => $reference_no,
                    'detail' => "Gastos Realizados en : " . $cat->code,
                    'amount' => $totalExp,
                    'categorie' => "Gastos - " . $cat->name,
                    'categorie_id' => $cat_id,
                );
            }
        }

        $lims_payrolls_list = Payroll::select('id', 'reference_no', 'amount', 'note', 'employee_id')->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->get();
        $lims_employee_list = Employee::get();
        foreach ($lims_employee_list as $employee) {
            $totalEmp = 0;
            $reference_no = 0;
            foreach ($lims_payrolls_list as $payroll) {
                if ($payroll->employee_id == $employee->id) {
                    $reference_no = $reference_no + 1;
                    $nominas = $nominas + $payroll->amount;
                    $totalEmp = $totalEmp + $payroll->amount;
                    $details[] = array(
                        'id' => $employee->id,
                        'reference' => $reference_no,
                        'detail' => $payroll->note,
                        'amount' => $payroll->amount,
                        'categorie' => "Empleado - " . $employee->name,
                        'employee' => $employee->id,
                    );
                }
            }
        }

        // Ajustes de cuenta tipo EGRESO
        $lims_adjustment_account_list = AdjustmentAccount::select('id', 'reference_no', 'amount', 'note')
            ->where([['is_active', true], ['type_adjustment', 'EGR']])
            ->where('created_at', '>=', $start_date)
            ->where('created_at', '<=', $end_date)
            ->get();
        $totalAjust = 0;
        $reference_no = 0;
        foreach ($lims_adjustment_account_list as $adjustment_account) {
            $reference_no = $reference_no + 1;
            $ajustegr = $ajustegr + $adjustment_account->amount;
            $totalAjust = $totalAjust + $adjustment_account->amount;
            $details[] = array(
                'id' => $adjustment_account->id,
                'reference' => $adjustment_account->reference_no,
                'detail' => $adjustment_account->note,
                'amount' => $adjustment_account->amount,
                'categorie' => "Ajuste Egreso",
                'ajustement' => true
            );
        }

        $accountfull[] = $details;
        $total_egreso = $compras + $devolucion + $gastos + (float) $nominas + $transferencias + $ajustegr;

        return $data = array(
            'egresoresume' => $accountfull,
            'total' => $total_egreso,
        );
    }

    public function egresosBillerRangeDate($start_date, $end_date, $biller_id, $purchase = true)
    {
        $gastosTotal = 0;
        $total_egreso = 0;
        $gastos = 0;
        $nominas = 0;
        $ajustegr = 0;
        $devolucion = 0;
        $transferencias = 0;
        $compras = 0;
        $details = [];
        $detailtrans = [];
        $biller_data = Biller::find($biller_id);
        if ($purchase) {
            $lims_purchases_list = Purchase::select('purchases.id', 'purchases.reference_no', 'purchases.grand_total', 'payments.paying_method', 'purchases.note', 'payments.amount', 'payments.account_id')
                ->join('payments', 'purchases.id', '=', 'payments.purchase_id')->whereDate('purchases.created_at', '>=', $start_date)->whereDate('purchases.created_at', '<=', $end_date)->get();
            $totalPurch = 0;
            $reference_no = 0;
            foreach ($lims_purchases_list as $purchase) {
                if (
                    $purchase != null && ($purchase->account_id == $biller_data->account_id || $purchase->account_id == $biller_data->account_id_deposito
                        || $purchase->account_id == $biller_data->account_id_tarjeta || $purchase->account_id == $biller_data->account_id_cheque
                        || $purchase->account_id == $biller_data->account_id_giftcard || $purchase->account_id == $biller_data->account_id_qr)
                ) {
                    $totalPurch = $totalPurch + $purchase->amount;
                    $reference_no = $reference_no + 1;
                    $compras = $compras + $purchase->amount;
                }
            }
            if ($totalPurch > 0) {
                $details[] = array(
                    'id' => 1,
                    'reference' => $reference_no,
                    'detail' => "Compras + Pago",
                    'amount' => $totalPurch,
                    'categorie' => "Compras",
                );
            }
        }
        $lims_returns_list = Returns::select('id', 'reference_no', 'grand_total', 'return_note', 'account_id')->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->get();
        foreach ($lims_returns_list as $return) {
            if (
                $return != null && ($return->account_id == $biller_data->account_id || $return->account_id == $biller_data->account_id_deposito
                    || $return->account_id == $biller_data->account_id_tarjeta || $return->account_id == $biller_data->account_id_cheque
                    || $return->account_id == $biller_data->account_id_giftcard || $return->account_id == $biller_data->account_id_qr)
            ) {
                $devolucion = $devolucion + $return->grand_total;
                $details[] = array(
                    'id' => $return->id,
                    'reference' => $return->reference_no,
                    'detail' => $return->return_note,
                    'amount' => $return->grand_total,
                    'categorie' => "return",
                );
            }
        }

        /*$lims_send_money_via_transfers_list = MoneyTransfer::select('id', 'reference_no', 'amount', 'from_account_id')
        ->whereNotNull('from_account_id')->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->get();
        $totalTrans = 0;
        $reference_no = 0;
        foreach ($lims_send_money_via_transfers_list as $send_money_via_transfer) {
        if($send_money_via_transfer != null && ($send_money_via_transfer->from_account_id == $biller_data->account_id || $send_money_via_transfer->from_account_id == $biller_data->account_id_deposito
        || $send_money_via_transfer->from_account_id == $biller_data->account_id_tarjeta || $send_money_via_transfer->from_account_id == $biller_data->account_id_cheque
        || $send_money_via_transfer->from_account_id == $biller_data->account_id_giftcard || $send_money_via_transfer->from_account_id == $biller_data->account_id_qr)){
        $reference_no = $reference_no + 1;
        $totalTrans = $totalTrans + $send_money_via_transfer->amount;
        $transferencias = $transferencias + $send_money_via_transfer->amount;
        }
        }

        if($totalTrans > 0){
        $detailtrans[] = array(
        'id' => $send_money_via_transfer->id,
        'reference' => $reference_no,
        'detail' => "Egreso de Efectivo",
        'amount' => $totalTrans,
        'categorie' => "Transferencias"
        );
        }*/
        $lims_expenses_list = Expense::select('id', 'reference_no', 'amount', 'note', 'expense_category_id', 'account_id')->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->get();
        $lims_categoriexpense_list = ExpenseCategory::get();

        foreach ($lims_categoriexpense_list as $cat) {
            $reference_no = 0;
            $totalExp = 0;
            $cat_id = null;
            foreach ($lims_expenses_list as $expense) {
                if (
                    $expense != null && ($expense->account_id == $biller_data->account_id || $expense->account_id == $biller_data->account_id_deposito
                        || $expense->account_id == $biller_data->account_id_tarjeta || $expense->account_id == $biller_data->account_id_cheque
                        || $expense->account_id == $biller_data->account_id_giftcard || $expense->account_id == $biller_data->account_id_qr)
                ) {
                    if ($expense->expense_category_id == $cat->id) {
                        $reference_no = $reference_no + 1;
                        $totalExp = $totalExp + $expense->amount;
                        $gastos = $gastos + $expense->amount;
                        $cat_id = $expense->expense_category_id;
                    }
                }
            }
            if ($totalExp > 0) {
                $details[] = array(
                    'id' => $cat->id,
                    'reference' => $reference_no,
                    'detail' => "Gastos Realizados en : " . $cat->code,
                    'amount' => $totalExp,
                    'categorie' => "Gastos - " . $cat->name,
                    'categorie_id' => $cat_id,
                );
            }
        }

        $lims_payrolls_list = Payroll::select('id', 'reference_no', 'amount', 'note', 'employee_id', 'account_id')->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->get();
        $lims_employee_list = Employee::get();
        foreach ($lims_employee_list as $employee) {
            $totalEmp = 0;
            $reference_no = 0;
            foreach ($lims_payrolls_list as $payroll) {
                if (
                    $payroll != null && ($payroll->account_id == $biller_data->account_id || $payroll->account_id == $biller_data->account_id_deposito
                        || $payroll->account_id == $biller_data->account_id_tarjeta || $payroll->account_id == $biller_data->account_id_cheque
                        || $payroll->account_id == $biller_data->account_id_giftcard || $payroll->account_id == $biller_data->account_id_qr)
                ) {
                    if ($payroll->employee_id == $employee->id) {
                        $reference_no = $reference_no + 1;
                        $totalEmp = $totalEmp + $payroll->amount;
                        $nominas = $nominas + $payroll->amount;
                        $details[] = array(
                            'id' => $employee->id,
                            'reference' => $reference_no,
                            'detail' => $payroll->note,
                            'amount' => $payroll->amount,
                            'categorie' => "Empleado - " . $employee->name,
                            'employee' => $employee->id,
                        );
                    }
                }
            }
        }

        /*$lims_adjustment_account_list = AdjustmentAccount::select('id', 'reference_no', 'amount', 'note', 'account_id')
        ->where([['is_active', true],['type_adjustment', 'EGR']])->whereDate('created_at', '>=' , $start_date)->whereDate('created_at', '<=' , $end_date)->get();
        $totalAjust = 0;
        $reference_no = 0;
        foreach ($lims_adjustment_account_list as $adjustment_account) {
        if($adjustment_account != null && ($adjustment_account->account_id == $biller_data->account_id || $adjustment_account->account_id == $biller_data->account_id_deposito
        || $adjustment_account->account_id == $biller_data->account_id_tarjeta || $adjustment_account->account_id == $biller_data->account_id_cheque
        || $adjustment_account->account_id == $biller_data->account_id_giftcard || $adjustment_account->account_id == $biller_data->account_id_qr)){
        $reference_no = $reference_no + 1;
        $totalAjust = $totalAjust + $adjustment_account->amount;
        $ajustegr = $ajustegr + $adjustment_account->amount;
        }
        }
        if($totalAjust > 0){
        $details[] = array(
        'id' => 1,
        'reference' => $reference_no,
        'detail' => "Ajuste Realizados",
        'amount' => $totalAjust,
        'categorie' => "Ajuste Egresos",
        'ajustement' => true
        );
        }*/
        $accountfull[] = $details;
        $tranfers[] = $detailtrans;
        $total_egreso = $compras + $devolucion + $gastos + (float) $nominas + $transferencias;

        return $data = array(
            'egresoresume' => $accountfull,
            'total' => $total_egreso,
        );
    }

    public function generalallUtilReport($start_date, $end_date, $category_id, $biller_id)
    {
        $end_date_temp = $end_date;
        $end_date = $end_date . " 23:59:59";
        $total_ingreso_2 = 0;
        $ingresosList = [];
        $costosList = [];
        $egresosList = [];
        $result = [];
        $result_util = [];
        $total_ingreso = 0;
        $total_costo = 0;
        $total_egreso = 0;
        $total_general = 0;
        /* Ingreso */

        if ($category_id == 0) {

            $result_util[] = $this->salesUtil($start_date, $end_date);
            $costosList[] = $result_util[0]['saleresume'];
            $total_costo = $result_util[0]['total'];

            $result[] = $this->salesAllRangeDate($start_date, $end_date);
            $ingresosList[] = $result[0]['saleresume'];
            $total_ingreso = $result[0]['total'];
        } else {
            $result_util[] = $this->salesUtil($start_date, $end_date, $category_id);
            $costosList[] = $result_util[0]['saleresume'];
            $total_costo = $result_util[0]['total'];

            $result[] = $this->salesCategoryRangeDate($start_date, $end_date, $category_id);
            $ingresosList[] = $result[0]['saleresume'];
            $total_ingreso = $result[0]['total'];
        }
        /* Egresos */
        if ($biller_id != 0) {
            $result = [];
            $result = $this->egresosBillerRangeDate($start_date, $end_date, $biller_id, false);
            $egresosList[] = $result['egresoresume'][0];
            $total_egreso = $result['total'];
        } else {
            $result = [];
            $result = $this->egresosAllRangeDate($start_date, $end_date, false);
            $egresosList[] = $result['egresoresume'][0];
            $total_egreso = $result['total'];
        }
        $total_general = $total_ingreso - $total_egreso;

        $lims_biller_list = Biller::where('is_active', true)->get();
        $lims_categorie_list = Category::where('is_active', true)->get();
        $end_date = $end_date_temp;
        return view('report.generallUtil_report', compact('start_date', 'end_date', 'ingresosList', 'costosList', 'egresosList', 'total_ingreso', 'total_egreso', 'total_general', 'total_costo', 'lims_biller_list', 'lims_categorie_list', 'biller_id', 'category_id'));
    }

    public function salesUtil($start_date, $end_date, $category_id = 0)
    {
        $total_Util = 0;
        $total_efectivo = 0;
        $total_qr = 0;
        $total_cheque = 0;
        $total_deposito = 0;
        $total_tarjeta = 0;
        $salebruto = [];
        $query3 = array(
            'payments.sale_id',
            'payments.paying_method',
        );
        $sales_list = Sale::select('id')->whereBetween('date_sell', [$start_date, $end_date])->get(); //don't need ->get() or ->first()
        $lims_sales_list = Payment::whereIn('payments.sale_id', $sales_list)->selectRaw(implode(',', $query3))->orderBy('payments.id', 'ASC')->get();
        foreach ($lims_sales_list as $data) {
            if ($category_id == 0) {
                $product_sales = Product_Sale::select('product_sales.product_id', 'product_sales.total', 'product_sales.qty', 'products.price', 'products.cost')
                    ->join('products', 'product_sales.product_id', '=', 'products.id')->where([['product_sales.sale_id', $data->sale_id]])->get();
            } else {
                $product_sales = Product_Sale::select('product_sales.product_id', 'product_sales.total', 'product_sales.qty', 'products.price', 'products.cost')
                    ->join('products', 'product_sales.product_id', '=', 'products.id')->where([['product_sales.sale_id', $data->sale_id], ['product_sales.category_id', $category_id]])->get();
            }
            $total = 0;
            $totalUtil = 0;
            foreach ($product_sales as $product) {
                /** Costo Total = costo * cantidad */
                $costotal = $product->cost * $product->qty;
                /** Costo Utilidad = Total venta - Costo Total */
                //$totalUtil =  $$product->total - $costotal;
                $total = $total + $costotal;
            }
            $total_Util = $total_Util + $total;
            if ($data->paying_method == "Efectivo") {
                $total_efectivo = $total_efectivo + $total;
            } elseif ($data->paying_method == "Tarjeta_Credito_Debito") {
                $total_tarjeta = $total_tarjeta + $total;
            } elseif ($data->paying_method == "Qr_simple" || $data->paying_method == "Qr_Simple") {
                $total_qr = $total_qr + $total;
            } elseif ($data->paying_method == "Cheque") {
                $total_cheque = $total_cheque + $total;
            } elseif ($data->paying_method == "Deposito") {
                $total_deposito = $total_deposito + $total;
            }
        }
        if ($category_id == 0) {
            if ($total_efectivo > 0) {
                $salebruto[] = array(
                    'id' => 1,
                    'name' => "Todos",
                    'paying_method' => "Efectivo",
                    'total' => $total_efectivo,
                );
            }

            if ($total_tarjeta > 0) {
                $salebruto[] = array(
                    'id' => 2,
                    'name' => "Todos",
                    'paying_method' => 'Tarjeta Credito/Debito',
                    'total' => $total_tarjeta,
                );
            }

            if ($total_qr > 0) {
                $salebruto[] = array(
                    'id' => 3,
                    'name' => "Todos",
                    'paying_method' => "QR",
                    'total' => $total_qr,
                );
            }

            if ($total_cheque > 0) {
                $salebruto[] = array(
                    'id' => 4,
                    'name' => "Todos",
                    'paying_method' => "Cheque",
                    'total' => $total_cheque,
                );
            }

            if ($total_deposito > 0) {
                $salebruto[] = array(
                    'id' => 5,
                    'name' => "Todos",
                    'paying_method' => "Deposito",
                    'total' => $total_deposito,
                );
            }
        } else {
            $lims_category = Category::select('name')->find($category_id);
            if ($total_efectivo > 0) {
                $salebruto[] = array(
                    'id' => 1,
                    'name' => $lims_category->name,
                    'paying_method' => "Efectivo",
                    'total' => $total_efectivo,
                );
            }

            if ($total_tarjeta > 0) {
                $salebruto[] = array(
                    'id' => 2,
                    'name' => $lims_category->name,
                    'paying_method' => 'Tarjeta Credito/Debito',
                    'total' => $total_tarjeta,
                );
            }

            if ($total_qr > 0) {
                $salebruto[] = array(
                    'id' => 3,
                    'name' => $lims_category->name,
                    'paying_method' => "QR",
                    'total' => $total_qr,
                );
            }

            if ($total_cheque > 0) {
                $salebruto[] = array(
                    'id' => 4,
                    'name' => $lims_category->name,
                    'paying_method' => "Cheque",
                    'total' => $total_cheque,
                );
            }

            if ($total_deposito > 0) {
                $salebruto[] = array(
                    'id' => 5,
                    'name' => $lims_category->name,
                    'paying_method' => "Deposito",
                    'total' => $total_deposito,
                );
            }
        }
        return $data = array(
            'saleresume' => $salebruto,
            'total' => $total_Util,
        );
    }

    public function lote_expirationReport($filter, $days)
    {
        $query1 = array(
            'product_lot.name',
            'products.name AS product',
            'product_lot.stock',
            'product_lot.qty',
            'product_lot.status',
            'product_lot.expiration',
            'DATEDIFF(product_lot.expiration, CURDATE()) AS days',
        );
        if ($filter == null) {
            $filter = 0;
        }

        if ($days == null) {
            $setting = $general_setting = GeneralSetting::latest()->first();
            $days = $setting->alert_expiration;
        }
        $lotes_list = [];
        if ($filter == 0) {
            $lims_lotes_list = ProductLote::join('products', 'product_lot.idproduct', '=', 'products.id')->where([['product_lot.status', '!=', $filter], ['low_date', null]])->selectRaw(implode(',', $query1))->get();
            foreach ($lims_lotes_list as $key => $lote) {
                if ($lote->days <= $days) {
                    $lotes_list[] = $lote;
                }
            }
        } else {
            $lotes_list = ProductLote::select('product_lot.name', 'products.name AS product', 'product_lot.stock', 'product_lot.qty', 'product_lot.status', 'product_lot.expiration')->join('products', 'product_lot.idproduct', '=', 'products.id')->where([['product_lot.status', '=', 0], ['low_date', '!=', null]])->get();
        }

        return view('report.lote_alert', compact('lotes_list', 'filter', 'days'));
    }

    public function products_lotesReport()
    {
        $products_list = Product::select('products.id', 'products.name', 'products.code', 'products.category_id', 'products.type')->join('product_lot', 'products.id', '=', 'product_lot.idproduct')->where([['products.is_active', '=', true], ['product_lot.status', '!=', 0]])->groupBy('products.id')->get();

        return view('report.product_lote', compact('products_list'));
    }

    public function salesByProductReport(Request $request)
    {
        $start_date = date('Y-m-d', strtotime(' -7 day'));
        $end_date = date('Y-m-d');
        $warehouse_id = 0;
        $biller_id = 0;
        $setting = $general_setting = GeneralSetting::latest()->first();
        $data = $request->all();
        if (!is_null($request->start_date) && !empty($request->start_date) && !is_null($request->end_date) || !empty($request->end_date)) {
            $start_date = $data['start_date'];
            $end_date = $data['end_date'];
        }
        if (!is_null($request->warehouse_id) && !empty($request->warehouse_id)) {
            $warehouse_id = $data['warehouse_id'];
        }
        if (!is_null($request->biller_id) && !empty($request->biller_id)) {
            $biller_id = $data['biller_id'];
        }
        $report_data = [];
        $report_data_list = collect();
        if ($warehouse_id != 0 && $biller_id != 0) {
            $lims_data = Sale::where([['warehouse_id', $warehouse_id], ['biller_id', $biller_id]])->whereDate('date_sell', '>=', $start_date)->whereDate('date_sell', '<=', $end_date)->get();
        } else if ($warehouse_id != 0) {
            $lims_data = Sale::where('warehouse_id', $warehouse_id)->whereDate('date_sell', '>=', $start_date)->whereDate('date_sell', '<=', $end_date)->get();
        } else if ($biller_id != 0) {
            $lims_data = Sale::where('biller_id', $biller_id)->whereDate('date_sell', '>=', $start_date)->whereDate('date_sell', '<=', $end_date)->get();
        } else {
            $lims_data = Sale::whereDate('date_sell', '>=', $start_date)->whereDate('date_sell', '<=', $end_date)->get();
        }
        foreach ($lims_data as $sale) {
            $items = Product_Sale::where('sale_id', $sale->id)->orderby('total', 'DESC')->get();
            if ($sale->grand_total != $sale->paid_amount) {
                $due = $sale->paid_amount - $sale->grand_total;
            } else {
                $due = 0;
            }
            foreach ($items as $key => $item) {
                $due = abs($due);
                if ($due >= $item->total) {
                    $total_due = $item->total;
                    $due = $due - $item->total;
                } else {
                    $total_due = $due;
                }
                if ($item->product->type == 'combo' || $item->product->type == 'terminado') {
                    $product_list = explode(",", $item->product->product_list);
                    $qty_list = explode(",", $item->product->qty_list);
                    $price_list = explode(",", $item->product->price_list);
                    foreach ($product_list as $key => $child_id) {
                        $child_data = Product::find($child_id);
                        $report_data['date'] = date($setting->date_format, strtotime($sale->date_sell));
                        $report_data['customer'] = $sale->customer->name;
                        $report_data['customer_price'] = $sale->customer->price_type;
                        $report_data['reference_sale'] = $sale->reference_no;
                        $report_data['warehouse'] = $sale->warehouse->name;
                        $report_data['product'] = "(Combo)(" . $child_data->code . ") " . $child_data->name;
                        $report_data['qty'] = $qty_list[$key];
                        $report_data['biller'] = $sale->biller->name;
                        $report_data['unit_price'] = $price_list[$key];
                        $report_data['total'] = $price_list[$key] * $qty_list[$key];
                        $report_data['amount'] = ($price_list[$key] * $qty_list[$key]) - $total_due;
                        $report_data['due'] = $total_due;
                        $report_data['user_id'] = $sale->user_id;
                        $report_data_list[] = (object) $report_data;
                    }
                } else {
                    $report_data['date'] = date($setting->date_format, strtotime($sale->date_sell));
                    $report_data['customer'] = $sale->customer->name;
                    $report_data['customer_price'] = $sale->customer->price_type;
                    $report_data['reference_sale'] = $sale->reference_no;
                    $report_data['warehouse'] = $sale->warehouse->name;
                    $report_data['product'] = "(" . $item->product->code . ") " . $item->product->name;
                    $report_data['qty'] = $item->qty;
                    $report_data['biller'] = $sale->biller->name;
                    $report_data['unit_price'] = $item->net_unit_price;
                    $report_data['total'] = $item->total;
                    $report_data['amount'] = $item->total - $total_due;
                    $report_data['due'] = $total_due;
                    $report_data['user_id'] = $sale->user_id;
                    $report_data_list[] = (object) $report_data;
                }
            }
        }
        if (Auth::user()->role_id > 2 && Auth::user()->biller_id) {
            $billers = Biller::where('id', Auth::user()->biller_id)->where('is_active', true)->get();
            $lims_warehouse_list = app(BillerController::class)::warehouseAuthorizate(Auth::user()->biller_id);
        } else {
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $billers = Biller::where('is_active', true)->get();
        }
        return view('report.sale_biller_detail', compact('report_data_list', 'start_date', 'end_date', 'biller_id', 'warehouse_id', 'lims_warehouse_list', 'billers'));
    }

    public function reportProducts()
    {

        $list_products = Product::where('is_active', true)->orderBy('code', 'ASC')->get();

        return view('report.product_details', compact('list_products'));
    }

    public function reportProductRenueve(Request $request)
    {
        $start_date = date('Y-m-d', strtotime(' -7 day'));
        $end_date = date('Y-m-d');
        $warehouse_id = 0;
        $biller_id = 0;
        $setting = $general_setting = GeneralSetting::latest()->first();
        $data = $request->all();

        if (!is_null($request->start_date) && !empty($request->start_date) && !is_null($request->end_date) || !empty($request->end_date)) {
            $start_date = $data['start_date'];
            $end_date = $data['end_date'];
        }
        if (!is_null($request->warehouse_id) && !empty($request->warehouse_id)) {
            $warehouse_id = $data['warehouse_id'];
        }
        if (!is_null($request->biller_id) && !empty($request->biller_id)) {
            $biller_id = $data['biller_id'];
        }
        
        $report_data = [];
        $report_data_list = collect();

        if ($warehouse_id != 0 && $biller_id != 0) {
            $lims_data = Sale::where([['warehouse_id', $warehouse_id], ['biller_id', $biller_id]])->whereDate('date_sell', '>=', $start_date)->whereDate('date_sell', '<=', $end_date)->get();
        } else if ($warehouse_id != 0) {
            $lims_data = Sale::where('warehouse_id', $warehouse_id)->whereDate('date_sell', '>=', $start_date)->whereDate('date_sell', '<=', $end_date)->get();
        } else if ($biller_id != 0) {
            $lims_data = Sale::where('biller_id', $biller_id)->whereDate('date_sell', '>=', $start_date)->whereDate('date_sell', '<=', $end_date)->get();
        } else {
            $lims_data = Sale::whereDate('date_sell', '>=', $start_date)->whereDate('date_sell', '<=', $end_date)->get();
        }

        foreach ($lims_data as $sale) {
            $items = Product_Sale::where('sale_id', $sale->id)->orderby('total', 'DESC')->get();
            
            if ($sale->grand_total != $sale->paid_amount) {
                $due = $sale->paid_amount - $sale->grand_total;
            } else {
                $due = 0;
            }

            // Se eliminó el cálculo de $sale_subtotal porque ya no se prorratea el descuento

            foreach ($items as $key => $item) {
                $due = abs($due);
                if ($due >= $item->total) {
                    $total_due = $item->total;
                    $due = $due - $item->total;
                } else {
                    $total_due = $due;
                }

                // MODIFICACIÓN: Solo se toma el descuento del item directo
                $item_discount = $item->discount; 

                $report_data['date'] = date($setting->date_format, strtotime($sale->date_sell));
                $report_data['customer'] = $sale->customer->name;
                $report_data['customer_price'] = $sale->customer->price_type;
                $report_data['reference_sale'] = $sale->reference_no;
                $report_data['warehouse'] = $sale->warehouse->name;
                $report_data['product'] = "(" . $item->product->code . ") " . $item->product->name;
                $report_data['qty'] = $item->qty;
                $report_data['biller'] = $sale->biller->name;
                $report_data['unit_price'] = $item->net_unit_price;
                $report_data['discount'] = $item_discount; // Aquí guardamos solo el descuento del item
                $report_data['total'] = $item->total;
                $report_data['cost'] = $item->cost;
                $report_data['cost_total'] = $item->cost * $item->qty;
                $report_data['amount'] = $item->total - $total_due;
                $report_data['due'] = $total_due;
                $report_data['user_id'] = $sale->user_id;
                $report_data['method'] = $this->paymentMethod($sale->id);
                $report_data_list[] = (object) $report_data;
            }
        }

        $lims_data_return = Returns::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->get();
        
        foreach ($lims_data_return as $return) {
            $items = ProductReturn::where('return_id', $return->id)->orderby('total', 'DESC')->get();
            
            if ($return->grand_total != $return->paid_amount) {
                $due = $return->paid_amount - $return->grand_total;
            } else {
                $due = 0;
            }

            // Se eliminó el cálculo de $return_subtotal

            foreach ($items as $key => $item) {
                if ($item->product->is_variant) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')->FindExactProductWithCode($item->product_id, $item->product->code)->first();
                    $cost = $this->costoActualizadoProducto($item->product_id, $lims_product_variant_data->variant_id);
                } else {
                    $cost = $this->costoActualizadoProducto($item->product_id, null);
                }

                $due = abs($due);
                if ($due >= $item->total) {
                    $total_due = $item->total;
                    $due = $due - $item->total;
                } else {
                    $total_due = $due;
                }

                // MODIFICACIÓN: Solo se toma el descuento del item directo
                $item_discount = $item->discount;

                $report_data['date'] = date($setting->date_format, strtotime($return->created_at));
                $report_data['customer'] = $return->customer->name;
                $report_data['customer_price'] = $return->customer->price_type;
                $report_data['reference_sale'] = $return->reference_no;
                $report_data['warehouse'] = $return->warehouse->name;
                $report_data['product'] = "(" . $item->product->code . ") " . $item->product->name;
                $report_data['qty'] = $item->qty * -1;
                $report_data['biller'] = $return->biller->name;
                $report_data['unit_price'] = $item->net_unit_price * -1;
                $report_data['discount'] = $item_discount * -1; // Descuento del item en negativo
                $report_data['total'] = ($item->total) * -1;
                $report_data['cost'] = $cost;
                $report_data['cost_total'] = ($cost * $item->qty) * -1;
                $report_data['amount'] = ($item->total - $total_due) * -1;
                $report_data['due'] = $total_due * -1;
                $report_data['user_id'] = $return->user_id;
                $report_data['method'] = $this->paymentMethod($return->id);
                $report_data_list[] = (object) $report_data;
            }
        }

        if (Auth::user()->role_id > 2 && Auth::user()->biller_id) {
            $billers = Biller::where('id', Auth::user()->biller_id)->where('is_active', true)->get();
            $lims_warehouse_list = app(BillerController::class)::warehouseAuthorizate(Auth::user()->biller_id);
        } else {
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $billers = Biller::where('is_active', true)->get();
        }
        return view('report.product_renueve', compact('report_data_list', 'start_date', 'end_date', 'biller_id', 'warehouse_id', 'lims_warehouse_list', 'billers'));
    }

    public function reportHolidayEmployee($start_date, $end_date, $employee_id)
    {
        $lims_holidays = [];
        $lims_employees_list = User::where('is_active', true)->get();
        if ($employee_id != 0) {
            $lims_holidays = Holiday::where('user_id', $employee_id)->whereDate('from_date', '>=', $start_date)->whereDate('to_date', '<=', $end_date)->get();
        } else {
            $lims_holidays = Holiday::whereDate('from_date', '>=', $start_date)->whereDate('to_date', '<=', $end_date)->get();
        }
        if (sizeof($lims_holidays) > 0) {
            foreach ($lims_holidays as $holiday) {
                $timeDiff = abs(strtotime($holiday->to_date) - strtotime($holiday->from_date));
                $numberDays = $timeDiff / 86400;
                $holiday->days = $numberDays;
            }
        } else {
            $lims_holidays = [];
        }
        return view('report.holiday_employee', compact('lims_holidays', 'start_date', 'end_date', 'employee_id', 'lims_employees_list'));
    }

    public function reportAttendanceEmployee($start_date, $end_date, $employee_id)
    {
        $lims_attendances = [];
        $lims_hrm_setting_data = HrmSetting::latest()->first();
        $checkin = $lims_hrm_setting_data->checkin;
        $lims_employee_list = Employee::where('is_active', true)->get();
        if ($employee_id != 0) {
            $lims_attendances = Attendance::where('employee_id', $employee_id)->whereDate('date', '>=', $start_date)->whereDate('date', '<=', $end_date)->get();
        }
        if (sizeof($lims_attendances) > 0) {
            foreach ($lims_attendances as $attendance) {
                if ($attendance->status == false) {
                    $timeDiff = strtotime($attendance->checkin) - strtotime($checkin);
                    $hours = $timeDiff / (60 * 60);
                    if ($hours < 1) {
                        $minutes = $hours * 60;
                        $attendance->hours = "00." . $minutes;
                    } else {
                        $whole = floor($hours); // 1
                        $fraction = $hours - $whole;
                        $minutes = $fraction * 60;
                        $attendance->hours = $whole . "." . $minutes;
                    }
                } else {
                    $attendance->hours = 0;
                }
            }
        } else {
            $lims_attendances = [];
        }
        return view('report.attendance_employee', compact('lims_attendances', 'start_date', 'end_date', 'employee_id', 'lims_employee_list'));
    }

    /**
     * Summary of paymentMethod
     * @param int $id
     * @return string
     */
    function paymentMethod(int $id)
    {
        $payments_list = Payment::select('id', 'paying_method')->where('sale_id', $id)->get();
        $method = "";
        foreach ($payments_list as $payment) {
            if ($payment->paying_method == "Efectivo") {
                $method = $method . "<i style='font-size: x-large;' class='fa fa-money' aria-hidden='true' title='Efectivo'></i> Efectivo ";
            }
            if ($payment->paying_method == "Tarjeta_Credito_Debito") {
                $method = $method . "<i style='font-size: x-large;' class='fa fa-credit-card' aria-hidden='true' title='Tarjeta Debito/Credito'></i> Tarjeta";
            }
            if ($payment->paying_method == "Qr_Simple" || $payment->paying_method == "Qr_simple") {
                $method = $method . "<i style='font-size: x-large;' class='fa fa-qrcode' aria-hidden='true' title='QR'></i> QR";
            }
            if ($payment->paying_method == "Cheque") {
                $method = $method . "<i style='font-size: x-large;' class='fa fa-ticket' aria-hidden='true' title='Cheque'></i> Cheque";
            }
            if ($payment->paying_method == "Deposito") {
                $method = $method . "<i style='font-size: x-large;' class='fa fa-university' aria-hidden='true' title='Deposito'></i> Deposito";
            }
            if ($payment->paying_method == "Tarjeta_Regalo") {
                $method = $method . "<i style='font-size: x-large;' class='fa fa-gift' aria-hidden='true' title='Tarjeta de Regalo'></i> Regalo";
            }
            if ($payment->paying_method == "Vale") {
                $method = $method . "<i style='font-size: x-large;' class='fa fa-address-card-o' aria-hidden='true' title='Vale'></i> Vale";
            }
            if ($payment->paying_method == "Otros") {
                $method = $method . "<i style='font-size: x-large;' class='fa fa-address-card' aria-hidden='true' title='Otros'></i> Otros";
            }
            if ($payment->paying_method == "Pago_Posterior") {
                $method = $method . "<i style='font-size: x-large;' class='fa fa-ravelry' aria-hidden='true' title='Pago_Posterior'></i> Pago Posterior";
            }
            if ($payment->paying_method == "Transferencia_Bancaria") {
                $method = $method . "<i style='font-size: x-large;' class='fa fa-superpowers' aria-hidden='true' title='Transferencia_Bancaria'></i> Transferencia";
            }
            if ($payment->paying_method == "Swift") {
                $method = $method . "<i style='font-size: x-large;' class='fa fa-meetup' aria-hidden='true' title='Swift'></i> Swift";
            }
            if ($payment->paying_method == "Canal_Pago") {
                $method = $method . "<i style='font-size: x-large;' class='fa fa-university' aria-hidden='true' title='Canal_Pago'></i> Canal Pago";
            }
            if ($payment->paying_method == "Billetera_Movil") {
                $method = $method . "<i style='font-size: x-large;' class='fa fa-university' aria-hidden='true' title='Billetera_Movil'></i> Billetera M.";
            }
            if ($payment->paying_method == "Pago_Online") {
                $method = $method . "<i style='font-size: x-large;' class='fa fa-university' aria-hidden='true' title='Pago_Online'></i> Pago Online";
            }
            if ($payment->paying_method == "Debito_Automatico") {
                $method = $method . "<i style='font-size: x-large;' class='fa fa-university' aria-hidden='true' title='Debito_Automatico'></i> Deb. Automatico";
            }
        }
        if ($method == "") {
            $method = "Sin Pagar";
        }
        return $method . "";
    }

    function costoActualizadoProducto($id_producto, $id_variant = null)
    {
        $costo = 0;
        if ($id_variant)
            $item = ProductPurchase::select('id', 'purchase_id', 'net_unit_cost')->where([['status', true], ['product_id', $id_producto], ['variant_id', $id_variant]])->orderBy('created_at', 'desc')->first();
        else
            $item = ProductPurchase::select('id', 'purchase_id', 'net_unit_cost')->where([['status', true], ['product_id', $id_producto]])->orderBy('created_at', 'desc')->first();
        if ($item) {
            //Log::info("Purchase get Cost id:" . $item->purchase_id);
            $costo = $item->net_unit_cost;
        } else {
            $producto = Product::select('id', 'cost')->find($id_producto);
            //Log::info("Product get Cost id:" . $producto->id);
            $costo = $producto->cost;
        }
        return $costo;
    }

    public function reportSales($start_date, $end_date, $sucursal = 0, $biller_id = 0)
    {
        $start_date == null ? date('Y-m-d', strtotime('-7 days')) : $start_date;
        $end_date == null ? date('Y-m-d') : $end_date;
        if ($biller_id > 0) {
            $biller_ini = $biller_id;
            $biller_fin = $biller_id;
        } else {
            $biller_ini = 1;
            $biller_fin = 9999999;
        }
        if ($sucursal > -1) {
            $sucursal_ini = $sucursal;
            $sucursal_fin = $sucursal;
        } else {
            $sucursal_ini = 0;
            $sucursal_fin = 9999999;
        }
        $sales = CustomerSale::whereHas(
            'sale',
            function ($query) use ($start_date, $end_date, $sucursal_ini, $sucursal_fin, $biller_ini, $biller_fin) {
                $query->with('customer', 'biller', 'warehouse', 'productSales', 'productSales.product')
                    ->whereBetween('sucursal', [$sucursal_ini, $sucursal_fin])
                    ->whereBetween('date_sell', [$start_date, $end_date . ' 23:59:59'])
                    ->whereBetween('biller_id', [$biller_ini, $biller_fin]);
            }
        )->get();
        $lims_sucursal_list = SiatSucursal::select('id', 'nombre', 'sucursal')->where('estado', true)->get();
        $lims_biller_list = Biller::where('is_active', true)->get();
        return view("report.sale_ac_report", compact('sales', 'lims_biller_list', 'biller_id', 'start_date', 'end_date', 'sucursal', 'lims_sucursal_list'));
    }

    public function reportResumenSaleAccount($start_date, $end_date, $account_id = 0, $sucursal = -1)
    {
        $start_date == null ? date('Y-m-d', strtotime('-7 days')) : $start_date;
        $end_date == null ? date('Y-m-d') . ' 23:59:59' : $end_date . ' 23:59:59';
        if ($account_id > 0) {
            $account_ini = $account_id;
            $account_fin = $account_id;
        } else {
            $account_ini = 1;
            $account_fin = 9999999;
        }
        if ($sucursal > -1) {
            $sucursal_ini = $sucursal;
            $sucursal_fin = $sucursal;
        } else {
            $sucursal_ini = 0;
            $sucursal_fin = 9999999;
        }
        $account_list = Account::where('is_active', true)
            ->whereBetween('id', [$account_ini, $account_fin])->pluck('id');
        if ($sucursal > -1) {
            $resumen = DB::table('product_sales')
                ->select('accounts.id', 'accounts.account_no', 'accounts.name', 'product_sales.total')
                ->join('sales', 'sales.id', 'product_sales.sale_id')
                ->leftJoin('customer_sales', 'customer_sales.sale_id', 'sales.id')
                ->join('products', 'products.id', 'product_sales.product_id')
                ->join('accounts', 'accounts.id', 'products.account_id')
                ->whereIn('products.account_id', $account_list)
                ->whereBetween('sales.date_sell', [$start_date, $end_date . ' 23:59:59'])
                ->whereBetween('customer_sales.sucursal', [$sucursal_ini, $sucursal_fin])
                ->groupBy('accounts.id', 'product_sales.total')
                ->orderBy('accounts.account_no', 'ASC')->get();
        } else {
            $resumen = DB::table('product_sales')
                ->select('accounts.id', 'accounts.account_no', 'accounts.name', 'product_sales.total')
                ->join('sales', 'sales.id', 'product_sales.sale_id')
                ->join('products', 'products.id', 'product_sales.product_id')
                ->join('accounts', 'accounts.id', 'products.account_id')
                ->whereIn('products.account_id', $account_list)
                ->whereBetween('sales.date_sell', [$start_date, $end_date . ' 23:59:59'])
                ->groupBy('accounts.id', 'product_sales.total')
                ->orderBy('accounts.account_no', 'ASC')->get();
        }
        $lims_sucursal_list = SiatSucursal::select('id', 'nombre', 'sucursal')->where('estado', true)->get();
        $lims_account_list = Account::where('is_active', true)->get();
        $listResumen = collect();
        if (sizeof($resumen) > 0) {
            foreach ($account_list as $key => $account) {
                $accountresumen = array();
                $total = 0;
                foreach ($resumen as $data) {
                    if ($account == $data->id) {
                        $total = $total + $data->total;
                        $account_data = Account::select('account_no', 'name')->find($account);
                        $accountresumen = (Object) array(
                            'id' => $account,
                            'account_no' => $account_data->account_no,
                            'name' => $account_data->name,
                            'ingreso' => $total
                        );
                    } else {
                        $account_data = Account::select('account_no', 'name')->find($account);
                        $accountresumen = (Object) array(
                            'id' => $account,
                            'account_no' => $account_data->account_no,
                            'name' => $account_data->name,
                            'ingreso' => $total
                        );
                    }
                }
                $listResumen[] = $accountresumen;
            }
        }
        // dd($listResumen);
        return view("report.sale_account_report", compact('listResumen', 'lims_account_list', 'lims_sucursal_list', 'start_date', 'end_date', 'account_id', 'sucursal'));
    }
}
