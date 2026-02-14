<?php

namespace App\Proxy;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ProxyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // // ➎ Rate limit
        // $key = 'proxy:' . $request->ip();
        // if (RateLimiter::tooManyAttempts($key, env('PROXY_RATE_LIMIT', 100))) {
        //     abort(429, 'Too many requests');
        // }
        // RateLimiter::hit($key, 60);

        // // ➏ Optional: API-key gate
        // if ($request->header('X-Proxy-Key') !== env('PROXY_KEY')) {
        //     abort(401, 'Missing or invalid proxy key');
        // }

        // ➐ Inject custom headers for upstream
        $request->headers->set('X-Proxy-Version', '1.0');
        $request->headers->set('X-Real-IP', $request->ip());

        return $next($request);
    }
}