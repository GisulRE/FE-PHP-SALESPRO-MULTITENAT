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
                $alert_lotes = ProductLote::select('expiration')
                    ->where('status', '!=', 0)
                    ->whereNull('low_date')
                    ->get();

                foreach ($alert_lotes as $lote) {
                    if (empty($lote->expiration)) {
                        continue;
                    }
                    $days = (int) floor((strtotime(date('Y-m-d', strtotime($lote->expiration))) - strtotime(date('Y-m-d'))) / 86400);
                    if ($days <= $alert_expiration) {
                        $alert_lote++;
                    }
                }
            }

            if (class_exists(SiatPuntoVenta::class) && Schema::hasTable((new SiatPuntoVenta)->getTable())) {
                $list_puntosVentas = SiatPuntoVenta::select('fecha_vigencia_cuis')
                    ->where('is_active', true)
                    ->get();

                foreach ($list_puntosVentas as $punto_venta) {
                    $fechaCuis = date('Y-m-d', strtotime($punto_venta->fecha_vigencia_cuis));
                    $diff = abs(strtotime($fechaCuis) - strtotime(date('Y-m-d')));
                    $years = floor($diff / (365 * 60 * 60 * 24));
                    $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                    $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
                    if ($years == 0 && $months == 0 && $days < 6) {
                        $alert_cuis++;
                    }
                }
            }
        }

        View::share('alert_cuis', $alert_cuis);
        View::share('alert_product', $alert_product);
        View::share('alert_lote', $alert_lote);

        return $next($request);
    }
}
