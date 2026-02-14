<?php

namespace App\Providers;

use App\SiatPuntoVenta;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use DB;
use Auth;
use Illuminate\Support\Facades\URL;
use Spatie\Permission\Models\Role;
use App\Services\WhatsAppService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->singleton(WhatsAppService::class, WhatsAppService::class);
    }

    public function boot()
    {
        /*if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
        URL::forceScheme('https');
        }**/
        //setting language
        if (isset($_COOKIE['language'])) {
            \App::setLocale($_COOKIE['language']);
        } else {
            \App::setLocale('es');
        }
        //get general setting value
        $general_setting = null;
        $default_alert_expiration = 30;
        if (Schema::hasTable('general_settings')) {
            $general_setting = DB::table('general_settings')->latest()->first();
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
            config(['staff_access' => 'admin', 'date_format' => 'd-m-Y', 'currency' => '$', 'currency_position' => 'left']);
            $alert_expiration = $default_alert_expiration;
        }

        $query1 = ['DATEDIFF(expiration, CURDATE()) AS days'];
        $alert_product = 0;
        if (Schema::hasTable('products')) {
            $alert_product = DB::table('products')->where('is_active', true)->whereColumn('alert_quantity', '>', 'qty')->count();
        }
        $alert_lote = 0;
        if (Schema::hasTable('product_lot')) {
            $alert_lotes = DB::table('product_lot')->selectRaw(implode(',', $query1))->where([['status', '!=', 0], ['low_date', null]])->get();
            foreach ($alert_lotes as $lote) {
                if (($lote->days ?? PHP_INT_MAX) <= $alert_expiration) {
                    $alert_lote++;
                }
            }
        }
        //$controller = new \App\Http\Controllers\AttendanceController();
        //$controller->reset();

        $alert_cuis = 0;
        if (class_exists(SiatPuntoVenta::class) && Schema::hasTable((new SiatPuntoVenta)->getTable())) {
            $list_puntosVentas = SiatPuntoVenta::select('fecha_vigencia_cuis')->where('is_active', true)->get();
            foreach ($list_puntosVentas as $punto_venta) {
                $fechaCuis = date('Y-m-d', strtotime($punto_venta->fecha_vigencia_cuis));
                $diff = abs(strtotime($fechaCuis) - strtotime(date('Y-m-d')));
                $years = floor($diff / (365*60*60*24));
                $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
                $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
                if ($years == 0 && $months == 0 && $days < 6) {
                    // alertar renovar cuis
                    $alert_cuis++;
                }
            }
        }
        View::share('alert_cuis', $alert_cuis);
        View::share('alert_product', $alert_product);
        View::share('alert_lote', $alert_lote);
        Schema::defaultStringLength(191);
    }
}