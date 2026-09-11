<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PerformanceMiddleware
{
    /**
     * Umbral por defecto en milisegundos para detectar y registrar respuestas lentas.
     *
     * @var float
     */
    protected $defaultThresholdMs = 1500;

    /**
     * Claves sensibles que deben redactarse antes de registrar parámetros en los logs.
     *
     * @var array
     */
    protected $sensitiveKeys = [
        'password',
        'password_confirmation',
        '_token',
        'secret',
        'card_number',
        'cvv',
        'token',
        'authorization',
        'api_key',
        'pin',
        'credit_card',
        'c_v_v',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // 1. Mide el tiempo total de procesamiento PHP desde LARAVEL_START o microtime(true)
        $startTime = defined('LARAVEL_START') ? LARAVEL_START : microtime(true);

        $response = $next($request);

        $durationMs = round((microtime(true) - $startTime) * 1000, 2);

        // 2. Inyecta universalmente el encabezado de respuesta X-Response-Time
        // Compatible con respuestas Laravel (Response, JsonResponse) y Symfony (BinaryFileResponse, StreamedResponse)
        if ($response && isset($response->headers) && method_exists($response->headers, 'set')) {
            $response->headers->set('X-Response-Time', $durationMs . 'ms');
        }

        // 3. Verificación de umbral de lentitud (configurable via config/env)
        $thresholdMs = (float) config('app.slow_request_threshold', env('SLOW_REQUEST_THRESHOLD', $this->defaultThresholdMs));

        if ($durationMs >= $thresholdMs) {
            // 4. Envuelto en try-catch (\Throwable) para garantizar cero interrupciones en la app
            try {
                $tenantInfo = $this->getTenantContext();
                $userId = null;
                $userName = null;

                try {
                    if (Auth::check()) {
                        $user = Auth::user();
                        $userId = $user->id ?? null;
                        $userName = $user->name ?? null;
                    }
                } catch (\Throwable $authException) {
                    // Fallo silencioso si la sesión o auth no están listos
                }

                $memoryUsageMb = round(memory_get_peak_usage(true) / 1024 / 1024, 2);
                $sanitizedParams = $this->sanitizeParams($request->all());

                Log::warning('Slow server response detected', [
                    'url'           => $request->fullUrl(),
                    'method'        => $request->method(),
                    'duration_ms'   => $durationMs,
                    'threshold_ms'  => $thresholdMs,
                    'memory_usage'  => $memoryUsageMb . ' MB',
                    'user_id'       => $userId,
                    'user_name'     => $userName,
                    'ip'            => $request->ip(),
                    'user_agent'    => $request->userAgent(),
                    'is_ajax'       => $request->ajax(),
                    'tenant_id'     => $tenantInfo['tenant_id'] ?? null,
                    'tenant_name'   => $tenantInfo['tenant_name'] ?? null,
                    'tenant_domain' => $tenantInfo['tenant_domain'] ?? null,
                    'params'        => $sanitizedParams,
                ]);
            } catch (\Throwable $e) {
                // Registro silencioso: nada debe interrumpir el ciclo de vida de la petición
            }
        }

        return $response;
    }

    /**
     * Obtiene de forma segura la información del Tenant activo en el sistema multi-tenant.
     *
     * @return array
     */
    protected function getTenantContext()
    {
        $tenantId = null;
        $tenantName = null;
        $tenantDomain = null;

        try {
            if (Auth::check()) {
                $user = Auth::user();
                $tenantId = $user->company_id ?? null;

                if (!empty($user->company) && is_object($user->company)) {
                    $tenantName = $user->company->name ?? null;
                }
            }

            if (!$tenantId && function_exists('session')) {
                $tenantId = session('company_id') ?? session('tenant_id') ?? null;
                $tenantName = $tenantName ?? session('company_name') ?? null;
            }

            if (isset($_SERVER['HTTP_HOST'])) {
                $tenantDomain = $_SERVER['HTTP_HOST'];
            }
        } catch (\Throwable $t) {
            // Ignorado por seguridad
        }

        return [
            'tenant_id'     => $tenantId,
            'tenant_name'   => $tenantName,
            'tenant_domain' => $tenantDomain,
        ];
    }

    /**
     * Sanitiza parámetros de la solicitud:
     * - Redacta campos sensibles (passwords, tokens, api keys, tarjetas).
     * - Formatea objetos UploadedFile mostrando nombre original y tamaño sin serializar binarios.
     * - Trunca textos mayores a 500 caracteres.
     *
     * @param  array  $params
     * @return array
     */
    protected function sanitizeParams(array $params)
    {
        $sanitized = [];

        foreach ($params as $key => $value) {
            $lowerKey = strtolower((string) $key);

            // Redacción de datos sensibles
            if ($this->isSensitiveKey($lowerKey)) {
                $sanitized[$key] = '******** [REDACTED]';
                continue;
            }

            // Manejo de archivos subidos
            if ($value instanceof UploadedFile) {
                $sanitized[$key] = sprintf(
                    '[UploadedFile: %s, %s bytes, mime: %s]',
                    $value->getClientOriginalName(),
                    $value->getSize(),
                    $value->getClientMimeType()
                );
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeParams($value);
            } elseif (is_object($value)) {
                $sanitized[$key] = '[Object: ' . get_class($value) . ']';
            } elseif (is_string($value)) {
                if (mb_strlen($value) > 500) {
                    $sanitized[$key] = mb_substr($value, 0, 500) . '... [TRUNCATED ' . (mb_strlen($value) - 500) . ' CHARS]';
                } else {
                    $sanitized[$key] = $value;
                }
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Comprueba si una clave coincide con los patrones de datos sensibles.
     *
     * @param  string  $key
     * @return bool
     */
    protected function isSensitiveKey($key)
    {
        foreach ($this->sensitiveKeys as $sensitive) {
            if ($key === $sensitive || strpos($key, $sensitive) !== false) {
                return true;
            }
        }

        return false;
    }
}
