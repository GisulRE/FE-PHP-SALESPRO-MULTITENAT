<?php

namespace App\Http\Controllers;

use App\PosSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsAppSessionController extends Controller
{
    /**
     * Inicia el servicio creando un nuevo sessionId y arrancando la sesión en el gateway.
     *
     * Endpoint esperado por la vista: GET /api/whatsapp/start-client
     */
    public function startClient(Request $request)
    {
        $posSetting = PosSetting::first();
        if (!$posSetting) {
            return response()->json([
                'ok' => false,
                'message' => 'No existe configuración POS (pos_setting).',
            ], 500);
        }

        $base = rtrim($this->resolveWhatsAppBaseUrl($posSetting), '/');
        $createUrl = $base . '/sessions';

        $force = filter_var($request->query('force', false), FILTER_VALIDATE_BOOLEAN);
        $existingSessionId = (string) ($posSetting->whatsapp_session_id ?? '');
        $existingSessionValid = false;

        // Si ya hay sesión y no se fuerza crear otra, usar status para confirmar.
        if (!$force && $existingSessionId !== '') {
            $statusUrl = $base . '/sessions/' . $existingSessionId . '/status';
            try {
                $statusResp = Http::timeout(15)->get($statusUrl);
                if ($statusResp->successful()) {
                    $existingSessionValid = true;
                    $statusBody = $this->safeJsonOrString($statusResp->body());

                    Log::info('WhatsApp start-client (reuse session)', [
                        'sessionId' => $existingSessionId,
                        'statusUrl' => $statusUrl,
                        'status' => $statusResp->status(),
                    ]);

                    return response()->json([
                        'ok' => true,
                        'sessionId' => $existingSessionId,
                        'upstreamStatus' => $statusResp->status(),
                        'upstreamBody' => $statusBody,
                        'upstreamUrl' => $statusUrl,
                        'reused' => true,
                    ], 200);
                }

                // Si la sesión ya no existe en el upstream, limpiar la sesión guardada.
                if ($statusResp->status() === 404) {
                    $posSetting->whatsapp_session_id = null;
                    $posSetting->save();
                    $existingSessionId = '';
                }
            } catch (\Throwable $e) {
                // Si falla el status, intentamos crear sesión nueva abajo.
                Log::warning('WhatsApp status check failed, will create new session', [
                    'sessionId' => $existingSessionId,
                    'statusUrl' => $statusUrl,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        try {
            // En esta API, la sesión se crea con POST /api/v1/sessions
            $upstream = Http::timeout(20)->post($createUrl, []);

            $bodyRaw = $upstream->body();
            $body = $this->safeJsonOrString($bodyRaw);
            $sessionId = is_array($body)
                ? ($body['data']['sessionId'] ?? $body['sessionId'] ?? null)
                : null;

            Log::info('WhatsApp start-client (create session)', [
                'url' => $createUrl,
                'status' => $upstream->status(),
                'sessionId' => $sessionId,
            ]);

            // Si el upstream rate-limitea, reutilizar una sesión existente si hay.
            if ($upstream->status() === 429) {
                // 1) Si hay una sesión válida ya confirmada, devolverla.
                if ($existingSessionValid && $existingSessionId !== '') {
                    return response()->json([
                        'ok' => true,
                        'sessionId' => $existingSessionId,
                        'message' => 'Upstream limitó la creación de sesiones (429). Reutilizando sesión existente.',
                        'upstreamStatus' => 429,
                        'upstreamBody' => $body,
                        'upstreamUrl' => $createUrl,
                        'reused' => true,
                    ], 200);
                }

                // 2) Fallback: usar sesión "test" si existe (permite generar QR mientras pasa el rate limit).
                $fallbackSessionId = 'test';
                try {
                    $fallbackStatusUrl = $base . '/sessions/' . $fallbackSessionId . '/status';
                    $fallbackStatus = Http::timeout(15)->get($fallbackStatusUrl);
                    if ($fallbackStatus->successful()) {
                        $posSetting->whatsapp_session_id = $fallbackSessionId;
                        $posSetting->whatsapp_session_last_started_at = now();
                        $posSetting->save();

                        return response()->json([
                            'ok' => true,
                            'sessionId' => $fallbackSessionId,
                            'message' => 'Upstream limitó la creación de sesiones (429). Usando sesión de fallback: test.',
                            'upstreamStatus' => 429,
                            'upstreamBody' => $body,
                            'upstreamUrl' => $createUrl,
                            'reused' => true,
                        ], 200);
                    }
                } catch (\Throwable $e) {
                    // Si falla fallback, devolvemos 429 como error.
                }

                return response()->json([
                    'ok' => false,
                    'message' => 'Upstream limitó la creación de sesiones (429). Intenta nuevamente más tarde.',
                    'upstreamStatus' => 429,
                    'upstreamBody' => $body,
                    'upstreamUrl' => $createUrl,
                ], 429);
            }

            if (!$upstream->successful() || !$sessionId) {
                return response()->json([
                    'ok' => false,
                    'message' => 'No se pudo crear la sesión en el servicio de WhatsApp.',
                    'upstreamStatus' => $upstream->status(),
                    'upstreamBody' => $body,
                ], 502);
            }

            $posSetting->whatsapp_session_id = $sessionId;
            $posSetting->whatsapp_session_last_started_at = now();
            $posSetting->save();

            return response()->json([
                'sessionId' => $sessionId,
                'ok' => true,
                'upstreamStatus' => $upstream->status(),
                'upstreamBody' => $body,
                'upstreamUrl' => $createUrl,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('WhatsApp start-client error', [
                'base' => $base,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'sessionId' => $sessionId,
                'message' => 'No se pudo conectar con el servicio de WhatsApp.',
            ], 502);
        }
    }

    /**
     * Obtiene el QR como imagen.
     *
     * Endpoint esperado por la vista: GET /api/whatsapp/qr
     */
    public function qr(Request $request)
    {
        $posSetting = PosSetting::first();
        if (!$posSetting) {
            return response()->json([
                'ok' => false,
                'message' => 'No existe configuración POS (pos_setting).',
            ], 500);
        }

        $sessionId = (string) ($posSetting->whatsapp_session_id ?? '');
        if ($sessionId === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Servicio WhatsApp requiere la opción: Iniciar Servicio.',
            ], 400);
        }

        $base = $this->resolveWhatsAppBaseUrl($posSetting);
        $qrUrl = rtrim($base, '/') . '/sessions/' . $sessionId . '/qr';

        try {
            $upstream = Http::timeout(30)->get($qrUrl, [
                'format' => 'image',
            ]);

            $contentType = $upstream->header('Content-Type') ?: 'application/octet-stream';
            $status = $upstream->status();

            Log::info('WhatsApp qr', [
                'sessionId' => $sessionId,
                'url' => $qrUrl,
                'status' => $status,
                'contentType' => $contentType,
            ]);

            // Si upstream devuelve JSON con error, lo devolvemos igual (la vista decide qué hacer)
            return response($upstream->body(), $status)
                ->header('Content-Type', $contentType);
        } catch (\Throwable $e) {
            Log::error('WhatsApp qr error', [
                'sessionId' => $sessionId,
                'url' => $qrUrl,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'No se pudo obtener el QR desde el servicio de WhatsApp.',
            ], 502);
        }
    }

    /**
     * Consulta el estado de la sesión en el upstream.
     *
     * Endpoint para polling desde la vista: GET /api/whatsapp/status
     */
    public function status(Request $request)
    {
        $posSetting = PosSetting::first();
        if (!$posSetting) {
            return response()->json([
                'ok' => false,
                'message' => 'No existe configuración POS (pos_setting).',
            ], 500);
        }

        $sessionId = (string) ($posSetting->whatsapp_session_id ?? '');
        if ($sessionId === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Servicio WhatsApp requiere la opción: Iniciar Servicio.',
            ], 400);
        }

        $base = $this->resolveWhatsAppBaseUrl($posSetting);
        $statusUrl = rtrim($base, '/') . '/sessions/' . $sessionId . '/status';

        try {
            $upstream = Http::timeout(15)->get($statusUrl);
            $body = $this->safeJsonOrString($upstream->body());

            $statusValue = $this->extractUpstreamStatus($body);

            Log::info('WhatsApp status', [
                'sessionId' => $sessionId,
                'url' => $statusUrl,
                'status' => $upstream->status(),
                'sessionStatus' => $statusValue,
            ]);

            return response()->json([
                'ok' => $upstream->successful(),
                'sessionId' => $sessionId,
                'status' => $statusValue,
                'upstreamStatus' => $upstream->status(),
                'upstreamBody' => $body,
                'upstreamUrl' => $statusUrl,
            ], $upstream->status());
        } catch (\Throwable $e) {
            Log::error('WhatsApp status error', [
                'sessionId' => $sessionId,
                'url' => $statusUrl,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'No se pudo consultar el estado en el servicio de WhatsApp.',
            ], 502);
        }
    }

    private function generateSessionId(): string
    {
        // Evita caracteres especiales: solo alfanumérico
        return 'wa' . now()->format('YmdHis') . Str::lower(Str::random(8));
    }

    private function resolveWhatsAppBaseUrl(PosSetting $posSetting): string
    {
        // Preferir URL configurada en POS Ajustes
        $base = (string) ($posSetting->url_whatsapp ?? '');
        if ($base !== '') {
            // Quitar querystring
            $base = preg_replace('/\?.*$/', '', $base);
            // Si el usuario pegó una URL específica (ej: /sessions/test/qr), recortar a la base /api/v1
            $sessionsPos = stripos($base, '/sessions/');
            if ($sessionsPos !== false) {
                $base = substr($base, 0, $sessionsPos);
            }
            return $this->ensureApiV1Base(rtrim($base, '/'));
        }

        // Fallback a configuración del proxy
        $envBase = (string) env('PROXY_UPSTREAM', '');
        if ($envBase !== '') {
            return $this->ensureApiV1Base(rtrim($envBase, '/'));
        }

        // Fallback final
        return 'http://154.53.54.236:3000/api/v1';
    }

    private function ensureApiV1Base(string $base): string
    {
        // Si ya apunta a /api/v1, listo.
        if (preg_match('#/api/v1$#i', $base)) {
            return $base;
        }
        // Si ya contiene /api/ (otra versión), no forzamos.
        if (stripos($base, '/api/') !== false) {
            return $base;
        }
        // Si es solo host (o ruta sin /api), asumimos convención /api/v1.
        return rtrim($base, '/') . '/api/v1';
    }

    private function extractUpstreamStatus($body): ?string
    {
        if (!is_array($body)) {
            return null;
        }

        $candidates = [
            $body['data']['status'] ?? null,
            $body['data']['state'] ?? null,
            $body['status'] ?? null,
            $body['state'] ?? null,
        ];

        foreach ($candidates as $value) {
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    /**
     * Intenta parsear JSON; si no es JSON válido devuelve string.
     *
     * @return mixed
     */
    private function safeJsonOrString(string $body)
    {
        $trimmed = trim($body);
        if ($trimmed === '') {
            return '';
        }

        $decoded = json_decode($trimmed, true);
        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $body;
    }
}
