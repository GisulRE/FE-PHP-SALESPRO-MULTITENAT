<?php

namespace App\Http\Controllers;

use App\PosSetting;
use App\WhatsAppSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WhatsAppMessageController extends Controller
{
    /**
     * Envía un mensaje de texto.
     *
     * POST /api/v1/messages/text
     * Body: { sessionId, to, text }
     */
    public function sendText(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sessionId' => 'nullable|string',
            'to' => 'required|string',
            'text' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Datos inválidos.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $sessionId = $this->resolveSessionId($request->input('sessionId'));
        if (!$sessionId) {
            return response()->json([
                'ok' => false,
                'message' => 'No hay sesión de WhatsApp activa. Inicia el servicio primero.',
            ], 400);
        }

        $baseUrl = $this->getWhatsAppBaseUrl();
        $url = "{$baseUrl}/messages/text";

        try {
            $response = $this->authorizedHttp(30)->post($url, [
                'sessionId' => $sessionId,
                'to' => $request->input('to'),
                'text' => $request->input('text'),
            ]);

            $body = $this->safeJsonOrString($response->body());

            Log::info('WhatsApp sendText', [
                'sessionId' => $sessionId,
                'to' => $request->input('to'),
                'status' => $response->status(),
            ]);

            return response()->json([
                'ok' => $response->successful(),
                'upstreamStatus' => $response->status(),
                'upstreamBody' => $body,
            ], $response->status());
        } catch (\Throwable $e) {
            Log::error('WhatsApp sendText error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Error al enviar mensaje de texto.',
                'error' => $e->getMessage(),
            ], 502);
        }
    }

    /**
     * Envía un documento por URL.
     *
     * POST /api/v1/messages/document
     * Body: { sessionId, to, documentUrl, fileName, mimetype, caption }
     */
    public function sendDocument(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sessionId' => 'nullable|string',
            'to' => 'required|string',
            'documentUrl' => 'required|url',
            'fileName' => 'nullable|string',
            'mimetype' => 'nullable|string',
            'caption' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Datos inválidos.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $sessionId = $this->resolveSessionId($request->input('sessionId'));
        if (!$sessionId) {
            return response()->json([
                'ok' => false,
                'message' => 'No hay sesión de WhatsApp activa.',
            ], 400);
        }

        $baseUrl = $this->getWhatsAppBaseUrl();
        $url = "{$baseUrl}/messages/document";

        try {
            $payload = [
                'sessionId' => $sessionId,
                'to' => $request->input('to'),
                'documentUrl' => $request->input('documentUrl'),
            ];

            if ($request->filled('fileName')) {
                $payload['fileName'] = $request->input('fileName');
            }
            if ($request->filled('mimetype')) {
                $payload['mimetype'] = $request->input('mimetype');
            }
            if ($request->filled('caption')) {
                $payload['caption'] = $request->input('caption');
            }

            $response = $this->authorizedHttp(60)->post($url, $payload);
            $body = $this->safeJsonOrString($response->body());

            Log::info('WhatsApp sendDocument', [
                'sessionId' => $sessionId,
                'to' => $request->input('to'),
                'status' => $response->status(),
            ]);

            return response()->json([
                'ok' => $response->successful(),
                'upstreamStatus' => $response->status(),
                'upstreamBody' => $body,
            ], $response->status());
        } catch (\Throwable $e) {
            Log::error('WhatsApp sendDocument error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Error al enviar documento.',
                'error' => $e->getMessage(),
            ], 502);
        }
    }

    /**
     * Envía un documento por upload (multipart).
     *
     * POST /api/v1/messages/document/upload
     * Form data: sessionId, to, caption, document (file)
     */
    public function sendDocumentUpload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sessionId' => 'nullable|string',
            'to' => 'required|string',
            'document' => 'required|file',
            'caption' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Datos inválidos.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $sessionId = $this->resolveSessionId($request->input('sessionId'));
        if (!$sessionId) {
            return response()->json([
                'ok' => false,
                'message' => 'No hay sesión de WhatsApp activa.',
            ], 400);
        }

        $baseUrl = $this->getWhatsAppBaseUrl();
        $url = "{$baseUrl}/messages/document/upload";

        try {
            $file = $request->file('document');
            
            $multipart = [
                ['name' => 'sessionId', 'contents' => $sessionId],
                ['name' => 'to', 'contents' => $request->input('to')],
                [
                    'name' => 'document',
                    'contents' => fopen($file->getRealPath(), 'r'),
                    'filename' => $file->getClientOriginalName(),
                ],
            ];

            if ($request->filled('caption')) {
                $multipart[] = ['name' => 'caption', 'contents' => $request->input('caption')];
            }

            $response = $this->authorizedHttp(60)->asMultipart()->post($url, $multipart);
            $body = $this->safeJsonOrString($response->body());

            Log::info('WhatsApp sendDocumentUpload', [
                'sessionId' => $sessionId,
                'to' => $request->input('to'),
                'status' => $response->status(),
            ]);

            return response()->json([
                'ok' => $response->successful(),
                'upstreamStatus' => $response->status(),
                'upstreamBody' => $body,
            ], $response->status());
        } catch (\Throwable $e) {
            Log::error('WhatsApp sendDocumentUpload error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Error al enviar documento por upload.',
                'error' => $e->getMessage(),
            ], 502);
        }
    }

    /**
     * Envía una imagen por URL.
     *
     * POST /api/v1/messages/image
     * Body: { sessionId, to, imageUrl, caption }
     */
    public function sendImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sessionId' => 'nullable|string',
            'to' => 'required|string',
            'imageUrl' => 'required|url',
            'caption' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Datos inválidos.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $sessionId = $this->resolveSessionId($request->input('sessionId'));
        if (!$sessionId) {
            return response()->json([
                'ok' => false,
                'message' => 'No hay sesión de WhatsApp activa.',
            ], 400);
        }

        $baseUrl = $this->getWhatsAppBaseUrl();
        $url = "{$baseUrl}/messages/image";

        try {
            $payload = [
                'sessionId' => $sessionId,
                'to' => $request->input('to'),
                'imageUrl' => $request->input('imageUrl'),
            ];

            if ($request->filled('caption')) {
                $payload['caption'] = $request->input('caption');
            }

            $response = $this->authorizedHttp(60)->post($url, $payload);
            $body = $this->safeJsonOrString($response->body());

            Log::info('WhatsApp sendImage', [
                'sessionId' => $sessionId,
                'to' => $request->input('to'),
                'status' => $response->status(),
            ]);

            return response()->json([
                'ok' => $response->successful(),
                'upstreamStatus' => $response->status(),
                'upstreamBody' => $body,
            ], $response->status());
        } catch (\Throwable $e) {
            Log::error('WhatsApp sendImage error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Error al enviar imagen.',
                'error' => $e->getMessage(),
            ], 502);
        }
    }

    /**
     * Envía una imagen por upload (multipart).
     *
     * POST /api/v1/messages/image/upload
     * Form data: sessionId, to, caption, image (file)
     */
    public function sendImageUpload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sessionId' => 'nullable|string',
            'to' => 'required|string',
            'image' => 'required|file|mimes:jpg,jpeg,png,gif,webp',
            'caption' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Datos inválidos.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $sessionId = $this->resolveSessionId($request->input('sessionId'));
        if (!$sessionId) {
            return response()->json([
                'ok' => false,
                'message' => 'No hay sesión de WhatsApp activa.',
            ], 400);
        }

        $baseUrl = $this->getWhatsAppBaseUrl();
        $url = "{$baseUrl}/messages/image/upload";

        try {
            $file = $request->file('image');
            
            $multipart = [
                ['name' => 'sessionId', 'contents' => $sessionId],
                ['name' => 'to', 'contents' => $request->input('to')],
                [
                    'name' => 'image',
                    'contents' => fopen($file->getRealPath(), 'r'),
                    'filename' => $file->getClientOriginalName(),
                ],
            ];

            if ($request->filled('caption')) {
                $multipart[] = ['name' => 'caption', 'contents' => $request->input('caption')];
            }

            $response = $this->authorizedHttp(60)->asMultipart()->post($url, $multipart);
            $body = $this->safeJsonOrString($response->body());

            Log::info('WhatsApp sendImageUpload', [
                'sessionId' => $sessionId,
                'to' => $request->input('to'),
                'status' => $response->status(),
            ]);

            return response()->json([
                'ok' => $response->successful(),
                'upstreamStatus' => $response->status(),
                'upstreamBody' => $body,
            ], $response->status());
        } catch (\Throwable $e) {
            Log::error('WhatsApp sendImageUpload error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Error al enviar imagen por upload.',
                'error' => $e->getMessage(),
            ], 502);
        }
    }

    /**
     * Envía un audio por URL.
     *
     * POST /api/v1/messages/audio
     * Body: { sessionId, to, audioUrl, ptt }
     */
    public function sendAudio(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sessionId' => 'nullable|string',
            'to' => 'required|string',
            'audioUrl' => 'required|url',
            'ptt' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Datos inválidos.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $sessionId = $this->resolveSessionId($request->input('sessionId'));
        if (!$sessionId) {
            return response()->json([
                'ok' => false,
                'message' => 'No hay sesión de WhatsApp activa.',
            ], 400);
        }

        $baseUrl = $this->getWhatsAppBaseUrl();
        $url = "{$baseUrl}/messages/audio";

        try {
            $payload = [
                'sessionId' => $sessionId,
                'to' => $request->input('to'),
                'audioUrl' => $request->input('audioUrl'),
            ];

            // ptt = Push To Talk (nota de voz)
            if ($request->has('ptt')) {
                $payload['ptt'] = filter_var($request->input('ptt'), FILTER_VALIDATE_BOOLEAN);
            }

            $response = $this->authorizedHttp(60)->post($url, $payload);
            $body = $this->safeJsonOrString($response->body());

            Log::info('WhatsApp sendAudio', [
                'sessionId' => $sessionId,
                'to' => $request->input('to'),
                'status' => $response->status(),
            ]);

            return response()->json([
                'ok' => $response->successful(),
                'upstreamStatus' => $response->status(),
                'upstreamBody' => $body,
            ], $response->status());
        } catch (\Throwable $e) {
            Log::error('WhatsApp sendAudio error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Error al enviar audio.',
                'error' => $e->getMessage(),
            ], 502);
        }
    }

    /**
     * Envía un audio por upload (multipart).
     *
     * POST /api/v1/messages/audio/upload
     * Form data: sessionId, to, ptt, audio (file)
     */
    public function sendAudioUpload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sessionId' => 'nullable|string',
            'to' => 'required|string',
            'audio' => 'required|file',
            'ptt' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Datos inválidos.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $sessionId = $this->resolveSessionId($request->input('sessionId'));
        if (!$sessionId) {
            return response()->json([
                'ok' => false,
                'message' => 'No hay sesión de WhatsApp activa.',
            ], 400);
        }

        $baseUrl = $this->getWhatsAppBaseUrl();
        $url = "{$baseUrl}/messages/audio/upload";

        try {
            $file = $request->file('audio');
            
            $multipart = [
                ['name' => 'sessionId', 'contents' => $sessionId],
                ['name' => 'to', 'contents' => $request->input('to')],
                [
                    'name' => 'audio',
                    'contents' => fopen($file->getRealPath(), 'r'),
                    'filename' => $file->getClientOriginalName(),
                ],
            ];

            if ($request->has('ptt')) {
                $ptt = filter_var($request->input('ptt'), FILTER_VALIDATE_BOOLEAN);
                $multipart[] = ['name' => 'ptt', 'contents' => $ptt ? 'true' : 'false'];
            }

            $response = $this->authorizedHttp(60)->asMultipart()->post($url, $multipart);
            $body = $this->safeJsonOrString($response->body());

            Log::info('WhatsApp sendAudioUpload', [
                'sessionId' => $sessionId,
                'to' => $request->input('to'),
                'status' => $response->status(),
            ]);

            return response()->json([
                'ok' => $response->successful(),
                'upstreamStatus' => $response->status(),
                'upstreamBody' => $body,
            ], $response->status());
        } catch (\Throwable $e) {
            Log::error('WhatsApp sendAudioUpload error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Error al enviar audio por upload.',
                'error' => $e->getMessage(),
            ], 502);
        }
    }

    /**
     * Envía un video por URL.
     *
     * POST /api/v1/messages/video
     * Body: { sessionId, to, videoUrl, caption, gifPlayback }
     */
    public function sendVideo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sessionId' => 'nullable|string',
            'to' => 'required|string',
            'videoUrl' => 'required|url',
            'caption' => 'nullable|string',
            'gifPlayback' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Datos inválidos.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $sessionId = $this->resolveSessionId($request->input('sessionId'));
        if (!$sessionId) {
            return response()->json([
                'ok' => false,
                'message' => 'No hay sesión de WhatsApp activa.',
            ], 400);
        }

        $baseUrl = $this->getWhatsAppBaseUrl();
        $url = "{$baseUrl}/messages/video";

        try {
            $payload = [
                'sessionId' => $sessionId,
                'to' => $request->input('to'),
                'videoUrl' => $request->input('videoUrl'),
            ];

            if ($request->filled('caption')) {
                $payload['caption'] = $request->input('caption');
            }

            if ($request->has('gifPlayback')) {
                $payload['gifPlayback'] = filter_var($request->input('gifPlayback'), FILTER_VALIDATE_BOOLEAN);
            }

            $response = $this->authorizedHttp(90)->post($url, $payload);
            $body = $this->safeJsonOrString($response->body());

            Log::info('WhatsApp sendVideo', [
                'sessionId' => $sessionId,
                'to' => $request->input('to'),
                'status' => $response->status(),
            ]);

            return response()->json([
                'ok' => $response->successful(),
                'upstreamStatus' => $response->status(),
                'upstreamBody' => $body,
            ], $response->status());
        } catch (\Throwable $e) {
            Log::error('WhatsApp sendVideo error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Error al enviar video.',
                'error' => $e->getMessage(),
            ], 502);
        }
    }

    /**
     * Envía un video por upload (multipart).
     *
     * POST /api/v1/messages/video/upload
     * Form data: sessionId, to, caption, gifPlayback, video (file)
     */
    public function sendVideoUpload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sessionId' => 'nullable|string',
            'to' => 'required|string',
            'video' => 'required|file',
            'caption' => 'nullable|string',
            'gifPlayback' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Datos inválidos.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $sessionId = $this->resolveSessionId($request->input('sessionId'));
        if (!$sessionId) {
            return response()->json([
                'ok' => false,
                'message' => 'No hay sesión de WhatsApp activa.',
            ], 400);
        }

        $baseUrl = $this->getWhatsAppBaseUrl();
        $url = "{$baseUrl}/messages/video/upload";

        try {
            $file = $request->file('video');
            
            $multipart = [
                ['name' => 'sessionId', 'contents' => $sessionId],
                ['name' => 'to', 'contents' => $request->input('to')],
                [
                    'name' => 'video',
                    'contents' => fopen($file->getRealPath(), 'r'),
                    'filename' => $file->getClientOriginalName(),
                ],
            ];

            if ($request->filled('caption')) {
                $multipart[] = ['name' => 'caption', 'contents' => $request->input('caption')];
            }

            if ($request->has('gifPlayback')) {
                $gifPlayback = filter_var($request->input('gifPlayback'), FILTER_VALIDATE_BOOLEAN);
                $multipart[] = ['name' => 'gifPlayback', 'contents' => $gifPlayback ? 'true' : 'false'];
            }

            $response = $this->authorizedHttp(90)->asMultipart()->post($url, $multipart);
            $body = $this->safeJsonOrString($response->body());

            Log::info('WhatsApp sendVideoUpload', [
                'sessionId' => $sessionId,
                'to' => $request->input('to'),
                'status' => $response->status(),
            ]);

            return response()->json([
                'ok' => $response->successful(),
                'upstreamStatus' => $response->status(),
                'upstreamBody' => $body,
            ], $response->status());
        } catch (\Throwable $e) {
            Log::error('WhatsApp sendVideoUpload error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Error al enviar video por upload.',
                'error' => $e->getMessage(),
            ], 502);
        }
    }

    /**
     * Envía una ubicación.
     *
     * POST /api/v1/messages/location
     * Body: { sessionId, to, latitude, longitude, name?, address? }
     */
    public function sendLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sessionId' => 'nullable|string',
            'to'        => 'required|string',
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'name'      => 'nullable|string|max:255',
            'address'   => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok'      => false,
                'message' => 'Datos inválidos.',
                'errors'  => $validator->errors(),
            ], 400);
        }

        $sessionId = $this->resolveSessionId($request->input('sessionId'));
        if (!$sessionId) {
            return response()->json(['ok' => false, 'message' => 'No hay sesión de WhatsApp activa.'], 400);
        }

        $payload = [
            'sessionId' => $sessionId,
            'to'        => $request->input('to'),
            'latitude'  => (float) $request->input('latitude'),
            'longitude' => (float) $request->input('longitude'),
        ];
        if ($request->filled('name'))    { $payload['name']    = $request->input('name'); }
        if ($request->filled('address')) { $payload['address'] = $request->input('address'); }

        try {
            $response = $this->authorizedHttp(30)->post("{$this->getWhatsAppBaseUrl()}/messages/location", $payload);
            $body = $this->safeJsonOrString($response->body());
            Log::info('WhatsApp sendLocation', ['sessionId' => $sessionId, 'to' => $request->input('to'), 'status' => $response->status()]);
            return response()->json(['ok' => $response->successful(), 'upstreamStatus' => $response->status(), 'upstreamBody' => $body], $response->status());
        } catch (\Throwable $e) {
            Log::error('WhatsApp sendLocation error', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Error al enviar ubicación.', 'error' => $e->getMessage()], 502);
        }
    }

    /**
     * Envía un contacto (vCard).
     *
     * POST /api/v1/messages/contact
     * Body: { sessionId, to, vcard }
     */
    public function sendContact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sessionId' => 'nullable|string',
            'to'        => 'required|string',
            'vcard'     => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['ok' => false, 'message' => 'Datos inválidos.', 'errors' => $validator->errors()], 400);
        }

        $sessionId = $this->resolveSessionId($request->input('sessionId'));
        if (!$sessionId) {
            return response()->json(['ok' => false, 'message' => 'No hay sesión de WhatsApp activa.'], 400);
        }

        try {
            $response = $this->authorizedHttp(30)->post("{$this->getWhatsAppBaseUrl()}/messages/contact", [
                'sessionId' => $sessionId,
                'to'        => $request->input('to'),
                'vcard'     => $request->input('vcard'),
            ]);
            $body = $this->safeJsonOrString($response->body());
            Log::info('WhatsApp sendContact', ['sessionId' => $sessionId, 'to' => $request->input('to'), 'status' => $response->status()]);
            return response()->json(['ok' => $response->successful(), 'upstreamStatus' => $response->status(), 'upstreamBody' => $body], $response->status());
        } catch (\Throwable $e) {
            Log::error('WhatsApp sendContact error', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Error al enviar contacto.', 'error' => $e->getMessage()], 502);
        }
    }

    /**
     * Envía un mensaje con botones interactivos.
     *
     * POST /api/v1/messages/buttons
     * Body: { sessionId, to, text, buttons: [{id, text},...], footer? }
     */
    public function sendButtons(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sessionId'      => 'nullable|string',
            'to'             => 'required|string',
            'text'           => 'required|string',
            'buttons'        => 'required|array|min:1|max:3',
            'buttons.*.id'   => 'required|string',
            'buttons.*.text' => 'required|string',
            'footer'         => 'nullable|string|max:60',
        ]);

        if ($validator->fails()) {
            return response()->json(['ok' => false, 'message' => 'Datos inválidos.', 'errors' => $validator->errors()], 400);
        }

        $sessionId = $this->resolveSessionId($request->input('sessionId'));
        if (!$sessionId) {
            return response()->json(['ok' => false, 'message' => 'No hay sesión de WhatsApp activa.'], 400);
        }

        $payload = [
            'sessionId' => $sessionId,
            'to'        => $request->input('to'),
            'text'      => $request->input('text'),
            'buttons'   => $request->input('buttons'),
        ];
        if ($request->filled('footer')) { $payload['footer'] = $request->input('footer'); }

        try {
            $response = $this->authorizedHttp(30)->post("{$this->getWhatsAppBaseUrl()}/messages/buttons", $payload);
            $body = $this->safeJsonOrString($response->body());
            Log::info('WhatsApp sendButtons', ['sessionId' => $sessionId, 'to' => $request->input('to'), 'status' => $response->status()]);
            return response()->json(['ok' => $response->successful(), 'upstreamStatus' => $response->status(), 'upstreamBody' => $body], $response->status());
        } catch (\Throwable $e) {
            Log::error('WhatsApp sendButtons error', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Error al enviar mensaje con botones.', 'error' => $e->getMessage()], 502);
        }
    }

    /**
     * Envía un mensaje con lista interactiva.
     *
     * POST /api/v1/messages/list
     * Body: { sessionId, to, text, title, buttonText, sections: [{title, rows:[{id,title,description?}]}], footer? }
     */
    public function sendList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sessionId'                        => 'nullable|string',
            'to'                               => 'required|string',
            'text'                             => 'required|string',
            'title'                            => 'required|string|max:60',
            'buttonText'                       => 'required|string|max:20',
            'sections'                         => 'required|array|min:1',
            'sections.*.title'                 => 'nullable|string',
            'sections.*.rows'                  => 'required|array|min:1',
            'sections.*.rows.*.id'             => 'required|string',
            'sections.*.rows.*.title'          => 'required|string',
            'sections.*.rows.*.description'    => 'nullable|string',
            'footer'                           => 'nullable|string|max:60',
        ]);

        if ($validator->fails()) {
            return response()->json(['ok' => false, 'message' => 'Datos inválidos.', 'errors' => $validator->errors()], 400);
        }

        $sessionId = $this->resolveSessionId($request->input('sessionId'));
        if (!$sessionId) {
            return response()->json(['ok' => false, 'message' => 'No hay sesión de WhatsApp activa.'], 400);
        }

        $payload = [
            'sessionId'  => $sessionId,
            'to'         => $request->input('to'),
            'text'       => $request->input('text'),
            'title'      => $request->input('title'),
            'buttonText' => $request->input('buttonText'),
            'sections'   => $request->input('sections'),
        ];
        if ($request->filled('footer')) { $payload['footer'] = $request->input('footer'); }

        try {
            $response = $this->authorizedHttp(30)->post("{$this->getWhatsAppBaseUrl()}/messages/list", $payload);
            $body = $this->safeJsonOrString($response->body());
            Log::info('WhatsApp sendList', ['sessionId' => $sessionId, 'to' => $request->input('to'), 'status' => $response->status()]);
            return response()->json(['ok' => $response->successful(), 'upstreamStatus' => $response->status(), 'upstreamBody' => $body], $response->status());
        } catch (\Throwable $e) {
            Log::error('WhatsApp sendList error', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Error al enviar lista interactiva.', 'error' => $e->getMessage()], 502);
        }
    }

    /**
     * Envío masivo de mensajes (bulk).
     *
     * POST /api/v1/messages/bulk
     * Body: { messages: [...], delay? (ms between each) }
     */
    public function sendBulk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'messages'         => 'required|array|min:1',
            'messages.*.to'    => 'required|string',
            'messages.*.text'  => 'required|string',
            'messages.*.sessionId' => 'nullable|string',
            'delay'            => 'nullable|integer|min:0|max:60000',
        ]);

        if ($validator->fails()) {
            return response()->json(['ok' => false, 'message' => 'Datos inválidos.', 'errors' => $validator->errors()], 400);
        }

        $defaultSessionId = $this->resolveSessionId(null);

        // Asegurar que cada mensaje tenga sessionId
        $messages = collect($request->input('messages'))->map(function ($msg) use ($defaultSessionId) {
            if (empty($msg['sessionId'])) {
                $msg['sessionId'] = $defaultSessionId;
            }
            return $msg;
        })->values()->all();

        $payload = ['messages' => $messages];
        if ($request->filled('delay')) {
            $payload['delay'] = (int) $request->input('delay');
        }

        try {
            $response = $this->authorizedHttp(120)->post("{$this->getWhatsAppBaseUrl()}/messages/bulk", $payload);
            $body = $this->safeJsonOrString($response->body());
            Log::info('WhatsApp sendBulk', ['count' => count($messages), 'status' => $response->status()]);
            return response()->json(['ok' => $response->successful(), 'upstreamStatus' => $response->status(), 'upstreamBody' => $body], $response->status());
        } catch (\Throwable $e) {
            Log::error('WhatsApp sendBulk error', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Error en envío masivo.', 'error' => $e->getMessage()], 502);
        }
    }

    /**
     * Verifica si un número está registrado en WhatsApp.
     *
     * GET /api/v1/sessions/{sessionId}/check-number/{phone}
     */
    public function checkNumber(Request $request, string $phone)
    {
        $sessionId = $this->resolveSessionId($request->query('sessionId'));
        if (!$sessionId) {
            return response()->json(['ok' => false, 'message' => 'No hay sesión de WhatsApp activa.'], 400);
        }

        // Sanitizar: solo dígitos y +
        $phone = preg_replace('/[^\d+]/', '', $phone);
        if (empty($phone)) {
            return response()->json(['ok' => false, 'message' => 'Número de teléfono inválido.'], 400);
        }

        $baseUrl = $this->getWhatsAppBaseUrl();
        // La URL base ya es .../api/v1 — necesitamos /sessions/{id}/check-number/{phone}
        // Removemos /messages para subir al nivel base si fuera necesario
        $apiBase = preg_replace('#/messages$#', '', $baseUrl);
        $url = "{$apiBase}/sessions/{$sessionId}/check-number/{$phone}";

        try {
            $response = $this->authorizedHttp(20)->get($url);
            $body = $this->safeJsonOrString($response->body());
            Log::info('WhatsApp checkNumber', ['sessionId' => $sessionId, 'phone' => $phone, 'status' => $response->status()]);
            return response()->json(['ok' => $response->successful(), 'upstreamStatus' => $response->status(), 'upstreamBody' => $body], $response->status());
        } catch (\Throwable $e) {
            Log::error('WhatsApp checkNumber error', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Error al verificar número.', 'error' => $e->getMessage()], 502);
        }
    }

    // ==================== MÉTODOS AUXILIARES ====================

    /**
     * Retorna un PendingRequest de Http ya configurado con el timeout indicado
     * y el Bearer token de la sesión activa (si existe en BD).
     */
    private function authorizedHttp(int $timeout = 30): \Illuminate\Http\Client\PendingRequest
    {
        $http = Http::timeout($timeout);
        $token = $this->resolveSessionToken();
        if ($token) {
            $http = $http->withHeaders(['Authorization' => 'Bearer ' . $token]);
        }
        return $http;
    }

    /**
     * Obtiene el session token (API key) de la sesión activa desde la BD.
     */
    private function resolveSessionToken(): ?string
    {
        $sessionId = $this->resolveSessionId(null);
        if (!$sessionId) {
            return null;
        }
        $posSetting = PosSetting::first();
        $companyId  = $posSetting ? $posSetting->company_id : null;

        $session = WhatsAppSession::where('session_name', $sessionId)
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->first();

        return $session ? $session->session_token : null;
    }

    /**
     * Resuelve el sessionId: si se proporciona lo usa, si no, busca en la BD.
     */
    private function resolveSessionId(?string $sessionId): ?string
    {
        if ($sessionId && trim($sessionId) !== '') {
            return trim($sessionId);
        }

        $posSetting = PosSetting::first();
        if ($posSetting && $posSetting->whatsapp_session_id) {
            return (string) $posSetting->whatsapp_session_id;
        }

        return null;
    }

    /**
     * Obtiene la URL base del servicio de WhatsApp.
     */
    private function getWhatsAppBaseUrl(): string
    {
        $posSetting = PosSetting::first();
        
        if ($posSetting && $posSetting->url_whatsapp) {
            $base = (string) $posSetting->url_whatsapp;
            $base = preg_replace('/\?.*$/', '', $base);
            $sessionsPos = stripos($base, '/sessions/');
            if ($sessionsPos !== false) {
                $base = substr($base, 0, $sessionsPos);
            }
            return $this->ensureApiV1Base(rtrim($base, '/'));
        }

        $envBase = (string) env('PROXY_UPSTREAM', '');
        if ($envBase !== '') {
            return $this->ensureApiV1Base(rtrim($envBase, '/'));
        }

        return 'http://154.53.54.236:3000/api/v1';
    }

    /**
     * Asegura que la URL base termine en /api/v1.
     */
    private function ensureApiV1Base(string $base): string
    {
        if (preg_match('#/api/v1$#i', $base)) {
            return $base;
        }
        if (stripos($base, '/api/') !== false) {
            return $base;
        }
        return rtrim($base, '/') . '/api/v1';
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
