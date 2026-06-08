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
        config(['staff_access' => 'admin', 'date_format' => 'd-m-Y', 'currency' => '$', 'currency_position' => 'left']);
        if ($this->app->runningInConsole()) {
            Schema::defaultStringLength(191);
            return;
        }
        Schema::defaultStringLength(191);
    }
}