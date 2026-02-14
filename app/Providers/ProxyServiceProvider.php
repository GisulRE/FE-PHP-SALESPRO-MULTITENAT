<?php

namespace App\Providers;

use App\Proxy\ProxyClient;
use App\Proxy\ProxyMiddleware;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class ProxyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ProxyClient::class, function ($app) {
            return new ProxyClient(
                new Client([
                    'base_uri'        => env('PROXY_UPSTREAM'),
                    'timeout'         => env('PROXY_TIMEOUT', 15),
                    'connect_timeout' => env('PROXY_TIMEOUT', 15),
                    'http_errors'     => false, // we handle status ourselves
                    'verify'          => env('PROXY_SSL_VERIFY', true),
                ])
            );
        });
    }

    // public function boot(): void
    // {
    //     // global rate-limiter for proxy endpoints
    //     RateLimiter::for('proxy', fn($job) => Limit::perMinute(env('PROXY_RATE_LIMIT', 100)));
    // }
}