<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Responder preflight OPTIONS sin pasar por el resto del pipeline.
        if (strtoupper($request->getMethod()) === 'OPTIONS') {
            return response('', 204, $this->corsHeaders($request));
        }

        $response = $next($request);

        foreach ($this->corsHeaders($request) as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }

    private function corsHeaders(Request $request): array
    {
        $origin = $request->headers->get('Origin');

        // Para apps web, lo más seguro es reflejar el Origin si existe.
        // Si no hay Origin (mismo host), no bloqueamos.
        $allowOrigin = $origin ?: '*';

        return [
            'Access-Control-Allow-Origin' => $allowOrigin,
            'Vary' => 'Origin',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN, X-Proxy-Version, X-Real-IP',
            'Access-Control-Max-Age' => '86400',
        ];
    }
}
