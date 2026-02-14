<?php

namespace App\Proxy;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProxyClient
{
    private Client $http;

    public function __construct(Client $http)
    {
        $this->http = $http;
    }

    /**
     * Forward request and return PSR-7 Response
     */
    public function forward(\Illuminate\Http\Request $laravelRequest): \Psr\Http\Message\ResponseInterface
    {
        $method  = $laravelRequest->getMethod();
        $uri     = $laravelRequest->path(); // /api/whatsapp/*
        $uri     = preg_replace('#^api/whatsapp/?#', '', $uri);
        $body    = $method === 'GET' ? null : $laravelRequest->getContent();
        $headers = $this->cleanHeaders($laravelRequest->headers->all());

        // $cacheKey = "proxy:$method:$uri:" . md5($body ?? '');

        // // ➊ Circuit-breaker
        // if (Cache::get('proxy_circuit_open')) {
        //     abort(503, 'Service temporarily unavailable');
        // }

        // // ➋ Cache GET 200 responses
        // if ($method === 'GET' && env('PROXY_CACHE_TTL', 0) > 0) {
        //     if ($cached = Cache::get($cacheKey)) {
        //         Log::info('[PROXY CACHE HIT]', ['uri' => $uri]);
        //         return $cached;
        //     }
        // }

        try {
            $resp = $this->http->request($method, $uri, [
                'headers' => $headers,
                'body'    => $body,
                'query'   => $laravelRequest->query(),
            ]);

            // // ➌ Cache successful GET
            // if ($method === 'GET' && $resp->getStatusCode() === 200) {
            //     Cache::put($cacheKey, $resp, env('PROXY_CACHE_TTL'));
            // }

            Log::info('[PROXY OK]', ['uri' => $uri, 'status' => $resp->getStatusCode()]);
            return $resp;

        } catch (TransferException $e) {
            Log::error('[PROXY ERROR]', ['uri' => $uri, 'exception' => $e->getMessage()]);
            // // ➍ Open circuit after 3 consecutive failures
            // $fails = Cache::increment('proxy_fail_count');
            // if ($fails >= env('PROXY_MAX_RETRY', 3)) {
            //     Cache::put('proxy_circuit_open', true, 60); // 1 min
            // }
            abort(502, 'Upstream error');
        }
    }

    /**
     * Strip hop-by-hop headers and Laravel internals
     */
    private function cleanHeaders(array $headers): array
    {
        $deny = [
            'host', 'connection', 'content-length', 'content-type',
            'x-php-ob-level', 'x-forwarded-for', 'x-forwarded-proto',
            'x-forwarded-port', 'x-forwarded-host',
        ];

        $out = [];
        foreach ($headers as $name => $values) {
            $l = strtolower($name);
            if (!in_array($l, $deny, true)) {
                $out[$name] = $values;
            }
        }
        return $out;
    }
}