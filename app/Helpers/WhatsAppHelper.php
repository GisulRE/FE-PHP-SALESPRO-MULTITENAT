<?php

/**
 * Clase helper para enviar mensajes de WhatsApp desde cualquier parte del sistema
 * 
 * Uso:
 * 
 * use App\Helpers\WhatsAppHelper;
 * 
 * // Enviar mensaje de texto
 * WhatsAppHelper::sendText('59176543210', '¡Hola desde el sistema!');
 * 
 * // Enviar documento
 * WhatsAppHelper::sendDocument('59176543210', 'https://ejemplo.com/factura.pdf', 'Factura-123.pdf', 'Su factura adjunta');
 * 
 * // Enviar imagen
 * WhatsAppHelper::sendImage('59176543210', 'https://ejemplo.com/producto.jpg', 'Producto disponible');
 */

namespace App\Helpers;

use App\PosSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppHelper
{
    /**
     * Obtiene la URL base del servicio de WhatsApp
     */
    private static function getBaseUrl(): string
    {
        $posSetting = PosSetting::first();
        
        if ($posSetting && $posSetting->url_whatsapp) {
            $base = (string) $posSetting->url_whatsapp;
            $base = preg_replace('/\?.*$/', '', $base);
            $sessionsPos = stripos($base, '/sessions/');
            if ($sessionsPos !== false) {
                $base = substr($base, 0, $sessionsPos);
            }
            return rtrim($base, '/') . '/api/v1';
        }

        return env('PROXY_UPSTREAM', 'http://154.53.54.236:3000/api/v1');
    }

    /**
     * Obtiene el sessionId de la base de datos
     */
    private static function getSessionId(): ?string
    {
        $posSetting = PosSetting::first();
        return $posSetting ? (string) $posSetting->whatsapp_session_id : null;
    }

    /**
     * Formatea el número de teléfono para WhatsApp
     * Acepta varios formatos y los normaliza
     * 
     * @param string $phone Número de teléfono (ej: +591 76543210, 591-76543210, 76543210)
     * @param string $defaultCountryCode Código de país por defecto (ej: '591' para Bolivia)
     * @return string Número formateado sin + ni espacios
     */
    public static function formatPhoneNumber(string $phone, string $defaultCountryCode = '591'): string
    {
        // Remover todos los caracteres no numéricos
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Si el número es muy corto, agregar código de país por defecto
        if (strlen($phone) < 10) {
            $phone = $defaultCountryCode . $phone;
        }
        
        return $phone;
    }

    /**
     * Envía un mensaje de texto
     * 
     * @param string $to Número de teléfono destino
     * @param string $text Texto del mensaje
     * @param string|null $sessionId SessionId opcional (si no se proporciona, usa el de la BD)
     * @return array Respuesta del servicio
     */
    public static function sendText(string $to, string $text, ?string $sessionId = null): array
    {
        try {
            $sessionId = $sessionId ?: self::getSessionId();
            if (!$sessionId) {
                return [
                    'success' => false,
                    'error' => 'No hay sesión de WhatsApp activa'
                ];
            }

            $to = self::formatPhoneNumber($to);
            $baseUrl = self::getBaseUrl();

            $response = Http::timeout(30)->post("{$baseUrl}/messages/text", [
                'sessionId' => $sessionId,
                'to' => $to,
                'text' => $text,
            ]);

            Log::info('WhatsAppHelper::sendText', [
                'to' => $to,
                'status' => $response->status(),
            ]);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'response' => $response->json() ?? $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error('WhatsAppHelper::sendText error', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Envía un documento por URL
     * 
     * @param string $to Número de teléfono destino
     * @param string $documentUrl URL del documento
     * @param string|null $fileName Nombre del archivo
     * @param string|null $caption Texto adicional
     * @param string|null $mimetype Tipo MIME (ej: application/pdf)
     * @return array Respuesta del servicio
     */
    public static function sendDocument(
        string $to, 
        string $documentUrl, 
        ?string $fileName = null, 
        ?string $caption = null,
        ?string $mimetype = null
    ): array {
        try {
            $sessionId = self::getSessionId();
            if (!$sessionId) {
                return [
                    'success' => false,
                    'error' => 'No hay sesión de WhatsApp activa'
                ];
            }

            $to = self::formatPhoneNumber($to);
            $baseUrl = self::getBaseUrl();

            $payload = [
                'sessionId' => $sessionId,
                'to' => $to,
                'documentUrl' => $documentUrl,
            ];

            if ($fileName) $payload['fileName'] = $fileName;
            if ($caption) $payload['caption'] = $caption;
            if ($mimetype) $payload['mimetype'] = $mimetype;

            $response = Http::timeout(60)->post("{$baseUrl}/messages/document", $payload);

            Log::info('WhatsAppHelper::sendDocument', [
                'to' => $to,
                'documentUrl' => $documentUrl,
                'status' => $response->status(),
            ]);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'response' => $response->json() ?? $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error('WhatsAppHelper::sendDocument error', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Envía un documento subiendo el archivo directamente
     * Útil cuando la URL del documento requiere autenticación
     * 
     * @param string $to Número de teléfono destino
     * @param string $filePath Ruta completa del archivo en el servidor
     * @param string|null $fileName Nombre del archivo para WhatsApp
     * @param string|null $caption Texto adicional
     * @return array Respuesta del servicio
     */
    public static function sendDocumentUpload(
        string $to, 
        string $filePath, 
        ?string $fileName = null, 
        ?string $caption = null
    ): array {
        try {
            $sessionId = self::getSessionId();
            if (!$sessionId) {
                return [
                    'success' => false,
                    'error' => 'No hay sesión de WhatsApp activa'
                ];
            }

            // Verificar que el archivo existe
            if (!file_exists($filePath)) {
                return [
                    'success' => false,
                    'error' => 'El archivo no existe: ' . $filePath
                ];
            }

            $to = self::formatPhoneNumber($to);
            $baseUrl = self::getBaseUrl();

            // Construir multipart correctamente (igual que en WhatsAppMessageController)
            $multipart = [
                ['name' => 'sessionId', 'contents' => $sessionId],
                ['name' => 'to', 'contents' => $to],
                [
                    'name' => 'document',  // Cambio: era 'file', debe ser 'document'
                    'contents' => fopen($filePath, 'r'),
                    'filename' => $fileName ?? basename($filePath),
                ],
            ];

            if ($caption) {
                $multipart[] = ['name' => 'caption', 'contents' => $caption];
            }

            // Usar asMultipart() explícitamente
            $response = Http::timeout(120)
                ->asMultipart()
                ->post("{$baseUrl}/messages/document/upload", $multipart);

            Log::info('WhatsAppHelper::sendDocumentUpload', [
                'to' => $to,
                'filePath' => $filePath,
                'fileName' => $fileName,
                'fileSize' => filesize($filePath),
                'status' => $response->status(),
            ]);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'response' => $response->json() ?? $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error('WhatsAppHelper::sendDocumentUpload error', [
                'to' => $to,
                'filePath' => $filePath ?? 'N/A',
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Envía una imagen por URL
     * 
     * @param string $to Número de teléfono destino
     * @param string $imageUrl URL de la imagen
     * @param string|null $caption Texto adicional
     * @return array Respuesta del servicio
     */
    public static function sendImage(string $to, string $imageUrl, ?string $caption = null): array
    {
        try {
            $sessionId = self::getSessionId();
            if (!$sessionId) {
                return [
                    'success' => false,
                    'error' => 'No hay sesión de WhatsApp activa'
                ];
            }

            $to = self::formatPhoneNumber($to);
            $baseUrl = self::getBaseUrl();

            $payload = [
                'sessionId' => $sessionId,
                'to' => $to,
                'imageUrl' => $imageUrl,
            ];

            if ($caption) $payload['caption'] = $caption;

            $response = Http::timeout(60)->post("{$baseUrl}/messages/image", $payload);

            Log::info('WhatsAppHelper::sendImage', [
                'to' => $to,
                'imageUrl' => $imageUrl,
                'status' => $response->status(),
            ]);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'response' => $response->json() ?? $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error('WhatsAppHelper::sendImage error', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Envía un audio por URL
     * 
     * @param string $to Número de teléfono destino
     * @param string $audioUrl URL del audio
     * @param bool $ptt Push To Talk (nota de voz) - true por defecto
     * @return array Respuesta del servicio
     */
    public static function sendAudio(string $to, string $audioUrl, bool $ptt = true): array
    {
        try {
            $sessionId = self::getSessionId();
            if (!$sessionId) {
                return [
                    'success' => false,
                    'error' => 'No hay sesión de WhatsApp activa'
                ];
            }

            $to = self::formatPhoneNumber($to);
            $baseUrl = self::getBaseUrl();

            $response = Http::timeout(60)->post("{$baseUrl}/messages/audio", [
                'sessionId' => $sessionId,
                'to' => $to,
                'audioUrl' => $audioUrl,
                'ptt' => $ptt,
            ]);

            Log::info('WhatsAppHelper::sendAudio', [
                'to' => $to,
                'audioUrl' => $audioUrl,
                'ptt' => $ptt,
                'status' => $response->status(),
            ]);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'response' => $response->json() ?? $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error('WhatsAppHelper::sendAudio error', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Envía un video por URL
     * 
     * @param string $to Número de teléfono destino
     * @param string $videoUrl URL del video
     * @param string|null $caption Texto adicional
     * @param bool $gifPlayback Reproducir como GIF en loop
     * @return array Respuesta del servicio
     */
    public static function sendVideo(
        string $to, 
        string $videoUrl, 
        ?string $caption = null,
        bool $gifPlayback = false
    ): array {
        try {
            $sessionId = self::getSessionId();
            if (!$sessionId) {
                return [
                    'success' => false,
                    'error' => 'No hay sesión de WhatsApp activa'
                ];
            }

            $to = self::formatPhoneNumber($to);
            $baseUrl = self::getBaseUrl();

            $payload = [
                'sessionId' => $sessionId,
                'to' => $to,
                'videoUrl' => $videoUrl,
                'gifPlayback' => $gifPlayback,
            ];

            if ($caption) $payload['caption'] = $caption;

            $response = Http::timeout(90)->post("{$baseUrl}/messages/video", $payload);

            Log::info('WhatsAppHelper::sendVideo', [
                'to' => $to,
                'videoUrl' => $videoUrl,
                'status' => $response->status(),
            ]);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'response' => $response->json() ?? $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error('WhatsAppHelper::sendVideo error', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verifica si hay una sesión de WhatsApp activa
     * 
     * @return bool
     */
    public static function hasActiveSession(): bool
    {
        $sessionId = self::getSessionId();
        return !empty($sessionId);
    }

    /**
     * Obtiene el estado de la sesión de WhatsApp
     * 
     * @return array Estado de la sesión
     */
    public static function getSessionStatus(): array
    {
        try {
            $sessionId = self::getSessionId();
            if (!$sessionId) {
                return [
                    'success' => false,
                    'error' => 'No hay sesión configurada'
                ];
            }

            $baseUrl = self::getBaseUrl();
            $response = Http::timeout(15)->get("{$baseUrl}/sessions/{$sessionId}/status");

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'sessionStatus' => $response->json()['status'] ?? 'UNKNOWN',
                'response' => $response->json() ?? $response->body(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
