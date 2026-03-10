<?php

namespace App\Http\Controllers;

use App\PosSetting;
use App\WhatsAppSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

        $sessionName = trim((string) $request->input('session_name', ''));
        if ($sessionName !== '' && !preg_match('/^[a-zA-Z0-9_\-]+$/', $sessionName)) {
            return response()->json(['ok' => false, 'message' => 'Nombre de sesión inválido. Solo letras, números, guiones y guiones bajos.'], 422);
        }
        if ($sessionName === '') {
            $sessionName = 'company-' . ($posSetting->company_id ?? 'default');
        }

        $base      = rtrim($this->resolveWhatsAppBaseUrl($posSetting), '/');
        $createUrl = $base . '/sessions';
        $force     = filter_var($request->query('force', false), FILTER_VALIDATE_BOOLEAN);

        // Reutilizar si ya existe y está activa (sin force).
        if (!$force) {
            try {
                $statusUrl  = $base . '/sessions/' . $sessionName . '/status';
                $statusResp = $this->makeRequest('GET', $statusUrl, $posSetting);
                if ($statusResp->successful()) {
                    $this->recordSession((int) $posSetting->company_id, $sessionName, $posSetting);
                    Log::info('WhatsApp startClient (reuse)', ['session' => $sessionName]);
                    return response()->json([
                        'ok'             => true,
                        'sessionId'      => $sessionName,
                        'upstreamStatus' => $statusResp->status(),
                        'upstreamBody'   => $this->safeJsonOrString($statusResp->body()),
                        'reused'         => true,
                    ], 200);
                }
            } catch (\Throwable $e) {
                Log::warning('WhatsApp startClient status check failed', ['session' => $sessionName, 'error' => $e->getMessage()]);
            }
        }

        try {
            $upstream   = $this->makeRequest('POST', $createUrl, $posSetting, ['sessionId' => $sessionName]);
            $body       = $this->safeJsonOrString($upstream->body());
            $returnedId = is_array($body)
                ? ($body['data']['sessionId'] ?? $body['sessionId'] ?? $sessionName)
                : $sessionName;

            Log::info('WhatsApp startClient (create)', ['url' => $createUrl, 'session' => $sessionName, 'status' => $upstream->status()]);

            if (!$upstream->successful()) {
                return response()->json([
                    'ok'             => false,
                    'message'        => 'No se pudo crear la sesión en el servicio de WhatsApp.',
                    'upstreamStatus' => $upstream->status(),
                    'upstreamBody'   => $body,
                ], 502);
            }

            $this->recordSession((int) $posSetting->company_id, (string) $returnedId, $posSetting);

            return response()->json([
                'ok'             => true,
                'sessionId'      => $returnedId,
                'upstreamStatus' => $upstream->status(),
                'upstreamBody'   => $body,
                'upstreamUrl'    => $createUrl,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('WhatsApp startClient error', ['base' => $base, 'error' => $e->getMessage()]);
            return response()->json([
                'ok'      => false,
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

        $sessionId = $this->resolveSessionName($request, $posSetting);
        if ($sessionId === '') {
            return response()->json(['ok' => false, 'message' => 'Servicio WhatsApp requiere la opción: Iniciar Servicio.'], 400);
        }

        $base  = $this->resolveWhatsAppBaseUrl($posSetting);
        $qrUrl = rtrim($base, '/') . '/sessions/' . $sessionId . '/qr?format=image';

        try {
            $upstream    = $this->makeRequest('GET', $qrUrl, $posSetting);
            $contentType = $upstream->header('Content-Type') ?: 'image/png';

            Log::info('WhatsApp qr', ['sessionId' => $sessionId, 'url' => $qrUrl, 'status' => $upstream->status()]);

            return response($upstream->body(), $upstream->status())->header('Content-Type', $contentType);
        } catch (\Throwable $e) {
            Log::error('WhatsApp qr error', ['sessionId' => $sessionId, 'error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'No se pudo obtener el QR desde el servicio de WhatsApp.'], 502);
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

        $sessionId = $this->resolveSessionName($request, $posSetting);
        if ($sessionId === '') {
            return response()->json(['ok' => false, 'message' => 'Sin sesión activa.'], 400);
        }

        $base      = $this->resolveWhatsAppBaseUrl($posSetting);
        $statusUrl = rtrim($base, '/') . '/sessions/' . $sessionId . '/status';

        try {
            $upstream    = $this->makeRequest('GET', $statusUrl, $posSetting);
            $body        = $this->safeJsonOrString($upstream->body());
            $statusValue = $this->extractUpstreamStatus($body);

            Log::info('WhatsApp status', ['sessionId' => $sessionId, 'status' => $upstream->status(), 'sessionStatus' => $statusValue]);

            return response()->json([
                'ok'             => $upstream->successful(),
                'sessionId'      => $sessionId,
                'status'         => $statusValue,
                'upstreamStatus' => $upstream->status(),
                'upstreamBody'   => $body,
            ], $upstream->status());
        } catch (\Throwable $e) {
            Log::error('WhatsApp status error', ['sessionId' => $sessionId, 'error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'No se pudo consultar el estado en el servicio de WhatsApp.'], 502);
        }
    }

    /**
     * Actualiza el nombre/ID de sesión almacenado en PosSetting.
     * No realiza llamada upstream; solo actualiza la referencia local.
     *
     * Endpoint: POST /api/whatsapp/update-session-name
     */
    public function updateSessionName(Request $request)
    {
        $name = trim((string) $request->input('session_name', ''));

        if ($name === '') {
            return response()->json([
                'ok' => false,
                'message' => 'El nombre de sesión no puede estar vacío.',
            ], 422);
        }

        if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $name)) {
            return response()->json([
                'ok' => false,
                'message' => 'Solo se permiten letras, números, guiones (-) y guiones bajos (_).',
            ], 422);
        }

        $posSetting = PosSetting::first();
        if (!$posSetting) {
            return response()->json(['ok' => false, 'message' => 'No existe configuración POS.'], 500);
        }

        $posSetting->whatsapp_session_id = $name;
        $posSetting->save();

        return response()->json([
            'ok' => true,
            'data' => [
                'message' => 'Nombre de sesión actualizado correctamente.',
                'session_name' => $name,
            ],
        ]);
    }

    /**
     * Cierra sesión (logout) en el upstream.
     *
     * Endpoint: POST /api/whatsapp/logout
     */
    public function logout(Request $request)
    {
        $posSetting = PosSetting::first();
        if (!$posSetting) {
            return response()->json(['ok' => false, 'message' => 'No existe configuración POS.'], 500);
        }

        $sessionId = $this->resolveSessionName($request, $posSetting);
        if ($sessionId === '') {
            return response()->json(['ok' => false, 'message' => 'No hay sesión activa configurada.'], 400);
        }

        $base      = $this->resolveWhatsAppBaseUrl($posSetting);
        $logoutUrl = rtrim($base, '/') . '/sessions/' . $sessionId . '/logout';

        try {
            $upstream = $this->makeRequest('POST', $logoutUrl, $posSetting);
            $body     = $this->safeJsonOrString($upstream->body());

            Log::info('WhatsApp logout', ['sessionId' => $sessionId, 'status' => $upstream->status()]);

            return response()->json([
                'ok'             => $upstream->successful(),
                'data'           => ['message' => $upstream->successful() ? 'Sesión cerrada exitosamente' : 'Error al cerrar la sesión.'],
                'upstreamStatus' => $upstream->status(),
                'upstreamBody'   => $body,
            ], $upstream->successful() ? 200 : $upstream->status());
        } catch (\Throwable $e) {
            Log::error('WhatsApp logout error', ['sessionId' => $sessionId, 'error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'No se pudo cerrar la sesión.'], 502);
        }
    }

    /**
     * Elimina la sesión en el upstream y limpia el registro local.
     *
     * Endpoint: DELETE /api/whatsapp/session
     */
    public function deleteSession(Request $request)
    {
        $posSetting = PosSetting::first();
        if (!$posSetting) {
            return response()->json(['ok' => false, 'message' => 'No existe configuración POS.'], 500);
        }

        $sessionId = $this->resolveSessionName($request, $posSetting);
        if ($sessionId === '') {
            return response()->json(['ok' => false, 'message' => 'No hay sesión activa configurada.'], 400);
        }

        $base      = $this->resolveWhatsAppBaseUrl($posSetting);
        $deleteUrl = rtrim($base, '/') . '/sessions/' . $sessionId;

        try {
            $upstream = $this->makeRequest('DELETE', $deleteUrl, $posSetting);
            $body     = $this->safeJsonOrString($upstream->body());

            Log::info('WhatsApp deleteSession', ['sessionId' => $sessionId, 'status' => $upstream->status()]);

            if ($upstream->successful()) {
                WhatsAppSession::where('company_id', $posSetting->company_id)
                    ->where('session_name', $sessionId)
                    ->delete();
                if ($posSetting->whatsapp_session_id === $sessionId) {
                    $posSetting->whatsapp_session_id             = null;
                    $posSetting->whatsapp_session_last_started_at = null;
                    $posSetting->save();
                }
            }

            return response()->json([
                'ok'             => $upstream->successful(),
                'data'           => ['message' => $upstream->successful() ? 'Sesión eliminada exitosamente' : 'Error al eliminar la sesión.'],
                'upstreamStatus' => $upstream->status(),
                'upstreamBody'   => $body,
            ], $upstream->successful() ? 200 : $upstream->status());
        } catch (\Throwable $e) {
            Log::error('WhatsApp deleteSession error', ['sessionId' => $sessionId, 'error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'No se pudo eliminar la sesión.'], 502);
        }
    }

    /**
     * Registra una nueva cuenta en el servicio WhatsApp y guarda los tokens.
     *
     * Endpoint: POST /api/whatsapp/auth/register
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'nit'           => 'required|string|max:50',
            'businessName'  => 'required|string|max:191',
            'sucursal'      => 'required|string|max:191',
            'address'       => 'required|string|max:255',
            'phone'         => 'required|string|max:50',
            'companyEmail'  => 'required|email|max:191',
            'username'      => 'required|string|max:80',
            'email'         => 'required|email|max:191',
            'password'      => 'required|string|min:8',
            'firstName'     => 'required|string|max:100',
            'lastName'      => 'required|string|max:100',
        ]);

        $posSetting = PosSetting::first();
        if (!$posSetting) {
            return response()->json(['ok' => false, 'message' => 'No existe configuración POS.'], 500);
        }

        $base = $this->resolveWhatsAppBaseUrl($posSetting);
        $registerUrl = rtrim($base, '/') . '/auth/register';

        try {
            $upstream = Http::timeout(20)->post($registerUrl, $validated);
            $body = $this->safeJsonOrString($upstream->body());

            Log::info('WhatsApp auth register', [
                'url'    => $registerUrl,
                'status' => $upstream->status(),
            ]);

            if (!$upstream->successful()) {
                $msg = is_array($body) ? ($body['message'] ?? 'Error al registrar la cuenta.') : 'Error al registrar la cuenta.';
                return response()->json(['ok' => false, 'message' => $msg, 'upstreamBody' => $body], $upstream->status());
            }

            $this->saveAuthTokens($posSetting, $body);

            return response()->json([
                'ok'      => true,
                'message' => 'Cuenta registrada exitosamente.',
                'data'    => is_array($body) ? ($body['data'] ?? $body) : $body,
            ]);
        } catch (\Throwable $e) {
            Log::error('WhatsApp register error', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'No se pudo conectar con el servicio de WhatsApp.'], 502);
        }
    }

    /**
     * Inicia sesión en el servicio WhatsApp (auth) y guarda los tokens.
     *
     * Endpoint: POST /api/whatsapp/auth/login
     */
    public function authLogin(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:80',
            'password' => 'required|string|min:1',
        ]);

        $posSetting = PosSetting::first();
        if (!$posSetting) {
            return response()->json(['ok' => false, 'message' => 'No existe configuración POS.'], 500);
        }

        $base     = $this->resolveWhatsAppBaseUrl($posSetting);
        $loginUrl = rtrim($base, '/') . '/auth/login';

        try {
            $upstream = Http::timeout(20)->post($loginUrl, $validated);
            $body     = $this->safeJsonOrString($upstream->body());

            if (!$upstream->successful()) {
                $msg = is_array($body) ? ($body['message'] ?? 'Credenciales incorrectas.') : 'Credenciales incorrectas.';
                return response()->json(['ok' => false, 'message' => $msg], $upstream->status());
            }

            $this->saveAuthTokens($posSetting, $body);

            return response()->json([
                'ok'      => true,
                'message' => 'Sesión de cuenta iniciada exitosamente.',
                'data'    => is_array($body) ? ($body['data'] ?? $body) : $body,
            ]);
        } catch (\Throwable $e) {
            Log::error('WhatsApp authLogin error', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'No se pudo conectar con el servicio de WhatsApp.'], 502);
        }
    }

    /**
     * Elimina los tokens de cuenta guardados (desconectar cuenta).
     *
     * Endpoint: POST /api/whatsapp/auth/logout
     */
    public function authLogout(Request $request)
    {
        $posSetting = PosSetting::first();
        if (!$posSetting) {
            return response()->json(['ok' => false, 'message' => 'No existe configuración POS.'], 500);
        }

        $posSetting->whatsapp_access_token  = null;
        $posSetting->whatsapp_refresh_token = null;
        $posSetting->save();

        return response()->json(['ok' => true, 'message' => 'Cuenta desconectada correctamente.']);
    }

    // ─── Nuevos endpoints ────────────────────────────────────────────────────

    /**
     * Refresca el access token usando el refresh token.
     *
     * POST /api/whatsapp/auth/refresh
     */
    public function authRefresh(Request $request)
    {
        $posSetting = PosSetting::first();
        if (!$posSetting) {
            return response()->json(['ok' => false, 'message' => 'No existe configuración POS.'], 500);
        }

        if ($this->tryRefreshToken($posSetting)) {
            return response()->json(['ok' => true, 'message' => 'Token refrescado correctamente.']);
        }

        return response()->json(['ok' => false, 'message' => 'No se pudo refrescar el token. Vuelve a iniciar sesión.'], 401);
    }

    /**
     * Lista todas las sesiones WhatsApp de la empresa.
     *
     * GET /api/whatsapp/sessions
     */
    public function listSessions(Request $request)
    {
        $posSetting = PosSetting::first();
        if (!$posSetting) {
            return response()->json(['ok' => false, 'message' => 'No existe configuración POS.'], 500);
        }

        // Obtener estado real desde el upstream
        $upstreamSessions = [];
        try {
            $base        = $this->resolveWhatsAppBaseUrl($posSetting);
            $upstreamResp = $this->makeRequest('GET', rtrim($base, '/') . '/sessions', $posSetting);
            if ($upstreamResp->successful()) {
                $upBody = $this->safeJsonOrString($upstreamResp->body());
                $raw    = is_array($upBody) ? ($upBody['data'] ?? []) : [];
                foreach ($raw as $s) {
                    $sid = $s['id'] ?? $s['sessionId'] ?? null;
                    if ($sid) $upstreamSessions[$sid] = $s['status'] ?? null;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('WhatsApp listSessions upstream error', ['error' => $e->getMessage()]);
        }

        $local = WhatsAppSession::where('company_id', $posSetting->company_id)
            ->orderByDesc('is_active')
            ->orderBy('session_name')
            ->get(['id', 'session_name', 'is_active', 'created_at']);

        // Registrar en local las sesiones que el upstream conoce pero aún no tenemos en BD
        foreach (array_keys($upstreamSessions) as $sid) {
            WhatsAppSession::firstOrCreate(
                ['company_id' => $posSetting->company_id, 'session_name' => $sid]
            );
        }
        // Recargar si se insertaron filas nuevas
        if (count($upstreamSessions) > $local->count()) {
            $local = WhatsAppSession::where('company_id', $posSetting->company_id)
                ->orderByDesc('is_active')->orderBy('session_name')
                ->get(['id', 'session_name', 'is_active', 'created_at']);
        }

        return response()->json([
            'ok'       => true,
            'sessions' => $local->map(fn($s) => [
                'id'             => $s->id,
                'session_name'   => $s->session_name,
                'is_active'      => $s->is_active,
                'upstream_status'=> $upstreamSessions[$s->session_name] ?? null,
                'created_at'     => $s->created_at ? $s->created_at->toDateTimeString() : null,
            ]),
        ]);
    }

    /**
     * Elimina un registro de la tabla local whatsapp_sessions (sin llamar al upstream).
     *
     * DELETE /api/whatsapp/sessions/{id}/local
     */
    public function destroyLocalSession(Request $request, int $id)
    {
        $posSetting = PosSetting::first();
        if (!$posSetting) {
            return response()->json(['ok' => false, 'message' => 'No existe configuración POS.'], 500);
        }

        $session = WhatsAppSession::where('id', $id)
            ->where('company_id', $posSetting->company_id)
            ->first();

        if (!$session) {
            return response()->json(['ok' => false, 'message' => 'Sesión no encontrada.'], 404);
        }

        // Si era la sesión principal, limpiar el campo legacy
        if ($session->is_active && $posSetting->whatsapp_session_id === $session->session_name) {
            $posSetting->whatsapp_session_id              = null;
            $posSetting->whatsapp_session_last_started_at = null;
            $posSetting->save();
        }

        $session->delete();

        return response()->json(['ok' => true, 'message' => "Sesión '{$session->session_name}' eliminada del registro local."]);
    }

    /**
     * Marca una sesión como principal (para mensajes salientes).
     *
     * POST /api/whatsapp/sessions/activate
     */
    public function activateSession(Request $request)
    {
        $sessionName = trim((string) $request->input('session_name', ''));
        if ($sessionName === '') {
            return response()->json(['ok' => false, 'message' => 'Nombre de sesión requerido.'], 422);
        }

        $posSetting = PosSetting::first();
        if (!$posSetting) {
            return response()->json(['ok' => false, 'message' => 'No existe configuración POS.'], 500);
        }

        $companyId = $posSetting->company_id;
        WhatsAppSession::where('company_id', $companyId)->update(['is_active' => false]);
        WhatsAppSession::where('company_id', $companyId)->where('session_name', $sessionName)->update(['is_active' => true]);

        $posSetting->whatsapp_session_id = $sessionName;
        $posSetting->save();

        return response()->json(['ok' => true, 'message' => "Sesión '$sessionName' marcada como principal."]);
    }

    /**
     * Genera un nuevo token para una sesión.
     *
     * POST /api/whatsapp/sessions/{sessionName}/token
     */
    public function createSessionToken(Request $request, string $sessionName)
    {
        $posSetting = PosSetting::first();
        if (!$posSetting) {
            return response()->json(['ok' => false, 'message' => 'No existe configuración POS.'], 500);
        }

        $base = $this->resolveWhatsAppBaseUrl($posSetting);
        $url  = rtrim($base, '/') . '/sessions/' . $sessionName . '/token';

        try {
            $upstream = $this->makeRequest('POST', $url, $posSetting);
            $body = $this->safeJsonOrString($upstream->body());

            // Guardar el token en la tabla whatsapp_sessions para uso en mensajes
            if ($upstream->successful() && is_array($body)) {
                $token = $body['data']['token'] ?? $body['token'] ?? null;
                if ($token) {
                    WhatsAppSession::where('company_id', $posSetting->company_id)
                        ->where('session_name', $sessionName)
                        ->update(['session_token' => $token]);
                }
            }

            // Devolver el JSON completo tal como lo retorna el upstream
            return response()->json(
                is_array($body) ? $body : ['ok' => $upstream->successful(), 'raw' => $body],
                $upstream->status()
            );
        } catch (\Throwable $e) {
            Log::error('WhatsApp createSessionToken error', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'No se pudo generar el token.'], 502);
        }
    }

    /**
     * Lista los tokens de una sesión.
     *
     * GET /api/whatsapp/sessions/{sessionName}/tokens
     */
    public function listSessionTokens(Request $request, string $sessionName)
    {
        $posSetting = PosSetting::first();
        if (!$posSetting) {
            return response()->json(['ok' => false, 'message' => 'No existe configuración POS.'], 500);
        }

        $base = $this->resolveWhatsAppBaseUrl($posSetting);
        $url  = rtrim($base, '/') . '/sessions/' . $sessionName . '/tokens';

        try {
            $upstream = $this->makeRequest('GET', $url, $posSetting);
            $body     = $this->safeJsonOrString($upstream->body());

            return response()->json([
                'ok'   => $upstream->successful(),
                'data' => is_array($body) ? ($body['data'] ?? $body) : $body,
            ], $upstream->status());
        } catch (\Throwable $e) {
            Log::error('WhatsApp listSessionTokens error', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'No se pudieron listar los tokens.'], 502);
        }
    }

    /**
     * Revoca el token de sesión.
     *
     * DELETE /api/whatsapp/sessions/{sessionName}/token
     */
    public function revokeSessionToken(Request $request, string $sessionName)
    {
        $posSetting = PosSetting::first();
        if (!$posSetting) {
            return response()->json(['ok' => false, 'message' => 'No existe configuración POS.'], 500);
        }

        $base = $this->resolveWhatsAppBaseUrl($posSetting);
        $url  = rtrim($base, '/') . '/sessions/' . $sessionName . '/token';

        try {
            $upstream = $this->makeRequest('DELETE', $url, $posSetting);
            $body     = $this->safeJsonOrString($upstream->body());

            // Limpiar el token guardado en BD
            if ($upstream->successful()) {
                WhatsAppSession::where('company_id', $posSetting->company_id)
                    ->where('session_name', $sessionName)
                    ->update(['session_token' => null]);
            }

            return response()->json(
                is_array($body) ? $body : ['ok' => $upstream->successful(), 'raw' => $body],
                $upstream->successful() ? 200 : $upstream->status()
            );
        } catch (\Throwable $e) {
            Log::error('WhatsApp revokeSessionToken error', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'No se pudo revocar el token.'], 502);
        }
    }

    // ─── Helpers privados ────────────────────────────────────────────────────

    /**
     * Realiza una petición HTTP autenticada con Bearer token.
     * Si el upstream responde 401, intenta refrescar el access token y reintenta.
     */
    private function makeRequest(string $method, string $url, PosSetting $posSetting, array $data = []): \Illuminate\Http\Client\Response
    {
        $doCall = function () use ($method, $url, $posSetting, $data) {
            $pending = Http::timeout(25)->withHeaders($this->resolveWhatsAppHeaders($posSetting));
            $m       = strtolower($method);
            return ($m === 'get' || ($m === 'delete' && empty($data)))
                ? $pending->$m($url)
                : $pending->$m($url, $data);
        };

        $response = $doCall();

        if ($response->status() === 401 && $this->tryRefreshToken($posSetting)) {
            $response = $doCall();
        }

        return $response;
    }

    /**
     * Intenta refrescar el access token usando el refresh token.
     * Actualiza $posSetting en memoria y en BD si tiene éxito.
     */
    private function tryRefreshToken(PosSetting $posSetting): bool
    {
        $refreshToken = (string) ($posSetting->whatsapp_refresh_token ?? '');
        if ($refreshToken === '') return false;

        $base       = $this->resolveWhatsAppBaseUrl($posSetting);
        $refreshUrl = rtrim($base, '/') . '/auth/refresh';

        try {
            $resp = Http::timeout(15)->post($refreshUrl, ['refreshToken' => $refreshToken]);
            if ($resp->successful()) {
                $body       = $this->safeJsonOrString($resp->body());
                $newAccess  = is_array($body) ? ($body['data']['accessToken']  ?? null) : null;
                $newRefresh = is_array($body) ? ($body['data']['refreshToken'] ?? null) : null;

                if ($newAccess) {
                    $posSetting->whatsapp_access_token  = $newAccess;
                    $posSetting->whatsapp_refresh_token = $newRefresh ?? $refreshToken;
                    $posSetting->save();
                    Log::info('WhatsApp access token refreshed automatically');
                    return true;
                }
            }
        } catch (\Throwable $e) {
            Log::error('WhatsApp tryRefreshToken error', ['error' => $e->getMessage()]);
        }

        return false;
    }

    /** Guarda los tokens de cuenta (accessToken/refreshToken) en PosSetting. */
    private function saveAuthTokens(PosSetting $posSetting, $body): void
    {
        $accessToken  = is_array($body) ? ($body['data']['tokens']['accessToken']  ?? null) : null;
        $refreshToken = is_array($body) ? ($body['data']['tokens']['refreshToken'] ?? null) : null;
        if ($accessToken) {
            $posSetting->whatsapp_access_token  = $accessToken;
            $posSetting->whatsapp_refresh_token = $refreshToken;
            $posSetting->save();
        }
    }

    /** Registra o actualiza una sesión en la tabla whatsapp_sessions. */
    private function recordSession(int $companyId, string $sessionName, PosSetting $posSetting): void
    {
        WhatsAppSession::firstOrCreate(
            ['company_id' => $companyId, 'session_name' => $sessionName]
        );
        // Actualizar legacy field para compatibilidad con mensajería
        if ($posSetting->whatsapp_session_id === null) {
            $posSetting->whatsapp_session_id             = $sessionName;
            $posSetting->whatsapp_session_last_started_at = now();
            $posSetting->save();
        }
    }

    /**
     * Resuelve el nombre de sesión a usar: primero del request, luego sesión
     * activa en DB, luego campo legacy en pos_setting, luego cualquier sesión.
     */
    private function resolveSessionName(Request $request, PosSetting $posSetting): string
    {
        $name = trim((string) $request->input('session_name', ''));
        if ($name !== '') return $name;

        $companyId = $posSetting->company_id;

        $session = WhatsAppSession::where('company_id', $companyId)->where('is_active', true)->first();
        if ($session) return $session->session_name;

        $legacy = (string) ($posSetting->whatsapp_session_id ?? '');
        if ($legacy !== '') return $legacy;

        $any = WhatsAppSession::where('company_id', $companyId)->first();
        return $any ? $any->session_name : '';
    }

    private function resolveWhatsAppHeaders(PosSetting $posSetting): array
    {
        $token = (string) ($posSetting->whatsapp_access_token ?? '');
        if ($token === '') return [];
        return ['Authorization' => 'Bearer ' . $token];
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
