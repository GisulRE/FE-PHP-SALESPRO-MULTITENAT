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

        try {
            $companyId = Auth::check() ? Auth::user()->company_id : null;
            if ($companyId) {
                $general_setting = \DB::table('general_settings')
                    ->where('company_id', $companyId)
                    ->latest()
                    ->first();
            }

            if (!$general_setting) {
                $general_setting = \DB::table('general_settings')->latest()->first();
            }
        } catch (\Throwable $e) {
            // fallback if DB error
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

        config([
            'staff_access' => $general_setting->staff_access ?? 'admin',
            'date_format' => $general_setting->date_format ?? 'd-m-Y',
            'currency' => $general_setting->currency ?? '$',
            'currency_position' => $general_setting->currency_position ?? 'left',
        ]);
        $alert_expiration = $general_setting->alert_expiration ?? $default_alert_expiration;

        $alert_product = 0;
        $alert_lote = 0;
        $alert_cuis = 0;

        // Solo calculamos alertas en background o usamos valores seguros
        if (Auth::check()) {
            try {
                $alert_product = session('alert_product', 0);
                $alert_lote = session('alert_lote', 0);
                $alert_cuis = session('alert_cuis', 0);
            } catch (\Throwable $e) {
                // ignore
            }
        }

        View::share('alert_cuis', $alert_cuis);
        View::share('alert_product', $alert_product);
        View::share('alert_lote', $alert_lote);

        return $next($request);
    }
}
