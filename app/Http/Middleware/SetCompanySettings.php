<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use App\GeneralSetting;
use App\Product;
use App\ProductLote;
use App\SiatPuntoVenta;

class SetCompanySettings
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $general_setting = null;
        $default_alert_expiration = 30;

        if (Schema::hasTable('general_settings')) {
            if (Auth::check() && Schema::hasColumn('general_settings', 'company_id')) {
                $companyId = Auth::user()->company_id;
                $general_setting = DB::table('general_settings')
                    ->where('company_id', $companyId)
                    ->latest()
                    ->first();
            }

            // Fallback si no está autenticado o no se encontró registro para la empresa
            if (!$general_setting) {
                $query = DB::table('general_settings');
                if (Schema::hasColumn('general_settings', 'company_id')) {
                    $query->whereNull('company_id');
                }
                $general_setting = $query->latest()->first();
            }

            // Fallback absoluto por si no hay registros sin company_id
            if (!$general_setting) {
                $general_setting = DB::table('general_settings')->latest()->first();
            }
        }

        if (!$general_setting) {
            $general_setting = (object)[
                'site_title' => 'GISUL POS',
                'theme' => 'default.css',
                'site_logo' => 'logo.png',
                'currency' => 'Bs.',
                'currency_position' => 'left',
                'date_format' => 'd-m-Y',
                'staff_access' => 'admin',
                'alert_expiration' => 30
            ];
        }

        View::share('general_setting', $general_setting);

        if ($general_setting) {
            config([
                'staff_access' => $general_setting->staff_access ?? 'admin',
                'date_format' => $general_setting->date_format ?? 'd-m-Y',
                'currency' => $general_setting->currency ?? '$',
                'currency_position' => $general_setting->currency_position ?? 'left',
            ]);
            $alert_expiration = $general_setting->alert_expiration ?? $default_alert_expiration;
        } else {
            $alert_expiration = $default_alert_expiration;
        }

        $alert_product = 0;
        $alert_lote = 0;
        $alert_cuis = 0;

        // Solo calculamos alertas si el usuario está autenticado para evitar consultas lentas innecesarias en el login
        if (Auth::check()) {
            if (Schema::hasTable('products')) {
                $alert_product = Product::where('is_active', true)
                    ->whereIn('type', ['standard', 'insumo', 'producto_terminado'])
                    ->whereNotNull('alert_quantity')
                    ->whereColumn('alert_quantity', '>', 'qty')
                    ->count();
            }

            if (Schema::hasTable('product_lot')) {
                $alert_lote = ProductLote::where('status', '!=', 0)
                    ->whereNull('low_date')
                    ->whereNotNull('expiration')
                    ->whereRaw('expiration <= DATE_ADD(CURDATE(), INTERVAL ? DAY)', [$alert_expiration])
                    ->count();
            }

            if (class_exists(SiatPuntoVenta::class) && Schema::hasTable((new SiatPuntoVenta)->getTable())) {
                $alert_cuis = SiatPuntoVenta::where('is_active', true)
                    ->whereNotNull('fecha_vigencia_cuis')
                    ->whereRaw('ABS(DATEDIFF(fecha_vigencia_cuis, CURDATE())) < 6')
                    ->count();
            }
        }

        View::share('alert_cuis', $alert_cuis);
        View::share('alert_product', $alert_product);
        View::share('alert_lote', $alert_lote);

        return $next($request);
    }
}
