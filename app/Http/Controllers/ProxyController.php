<?php

namespace App\Http\Controllers;

use App\Proxy\ProxyClient;
use Illuminate\Http\Request;

class ProxyController extends Controller
{
    private ProxyClient $proxy;

    public function __construct(ProxyClient $proxy) {
        $this->proxy = $proxy;
    }

    public function handle(Request $request)
    {
        $psrResponse = $this->proxy->forward($request);

        // Convert PSR-7 → Laravel response
        return response(
            $psrResponse->getBody()->getContents(),
            $psrResponse->getStatusCode(),
            $psrResponse->getHeaders()
        );
    }
}