<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\PosSetting;

class WhatsAppService
{
    /**
     * Provider mode: 'meta' or 'legacy'
     */
    protected $provider;

    /**
     * Meta WhatsApp Cloud API credentials and settings
     */
    protected $metaToken;
    protected $metaPhoneNumberId;
    protected $metaBusinessAccountId;
    protected $metaApiVersion;
    protected $metaOtpTemplate;
    protected $metaOtpLang;
    protected $metaOtpButtonType;

    /**
     * Legacy open-source gateway settings
     */
    protected $legacyEndpoint;
    protected $posSetting;

    public function __construct(?PosSetting $posSetting = null, array $config = [])
    {
        try {
            $this->posSetting = $posSetting ?: (class_exists(PosSetting::class) ? PosSetting::first() : null);
        } catch (\Throwable $e) {
            $this->posSetting = null;
        }

        $servicesConfig = function_exists('config') ? config('services.whatsapp', []) : [];
        if (!is_array($servicesConfig)) {
            $servicesConfig = [];
        }

        $firstNonEmpty = function (...$values) {
            foreach ($values as $v) {
                if ($v !== null && trim((string) $v) !== '') {
                    return trim((string) $v);
                }
            }
            return null;
        };

        $this->provider = strtolower((string) ($firstNonEmpty($config['provider'] ?? null, $servicesConfig['provider'] ?? null, env('WHATSAPP_PROVIDER')) ?: 'meta'));
        $this->metaToken = $firstNonEmpty(
            $config['token'] ?? null,
            $servicesConfig['token'] ?? null,
            env('WHATSAPP_CLOUD_TOKEN'),
            env('WHATSAPP_ACCESS_TOKEN'),
            $this->posSetting->whatsapp_access_token ?? null
        );
        $this->metaPhoneNumberId = $firstNonEmpty(
            $config['phone_number_id'] ?? null,
            $servicesConfig['phone_number_id'] ?? null,
            env('WHATSAPP_PHONE_NUMBER_ID'),
            $this->posSetting->whatsapp_phone_number_id ?? null
        );
        $this->metaBusinessAccountId = $firstNonEmpty(
            $config['business_account_id'] ?? null,
            $servicesConfig['business_account_id'] ?? null,
            env('WHATSAPP_BUSINESS_ACCOUNT_ID')
        );
        $this->metaApiVersion = $firstNonEmpty(
            $config['api_version'] ?? null,
            $servicesConfig['api_version'] ?? null,
            env('WHATSAPP_API_VERSION')
        ) ?: 'v21.0';
        $this->metaOtpTemplate = $firstNonEmpty(
            $config['otp_template_name'] ?? null,
            $servicesConfig['otp_template_name'] ?? null,
            env('WHATSAPP_OTP_TEMPLATE_NAME')
        );
        $this->metaOtpLang = $firstNonEmpty(
            $config['otp_template_lang'] ?? null,
            $servicesConfig['otp_template_lang'] ?? null,
            env('WHATSAPP_OTP_TEMPLATE_LANG')
        ) ?: 'es';
        $this->metaOtpButtonType = $firstNonEmpty(
            $config['otp_button_type'] ?? null,
            $servicesConfig['otp_button_type'] ?? null,
            env('WHATSAPP_OTP_BUTTON_TYPE')
        );

        // Legacy gateway endpoint resolution
        $defaultLegacy = 'http://154.53.54.236:3000/api/v1/messages/text';
        if ($this->posSetting && !empty($this->posSetting->url_whatsapp)) {
            $this->legacyEndpoint = rtrim($this->posSetting->url_whatsapp, '/') . '/api/v1/messages/text';
        } elseif ($this->posSetting && !empty($this->posSetting->whatsapp_endpoint)) {
            $this->legacyEndpoint = rtrim($this->posSetting->whatsapp_endpoint, '/') . '/api/v1/messages/text';
        } else {
            $this->legacyEndpoint = $defaultLegacy;
        }
    }

    /**
     * Checks if Meta WhatsApp Cloud API credentials are validly configured.
     */
    public function isMetaConfigured(): bool
    {
        return !empty($this->metaToken) && !empty($this->metaPhoneNumberId);
    }

    /**
     * Normalizes phone number to international E.164-compatible format without '+' or spaces.
     * Bolivia numbers (8 digits) are automatically prepended with country code 591.
     */
    public function formatPhoneNumber(string $number, string $defaultCountryCode = '591'): string
    {
        $clean = preg_replace('/[^0-9]/', '', $number);
        if (empty($clean)) {
            return '';
        }

        $clean = ltrim($clean, '0');

        if (strlen($clean) <= 8) {
            $clean = $defaultCountryCode . $clean;
        }

        return $clean;
    }

    /**
     * Sends an OTP verification code.
     * Prioritizes Meta WhatsApp Cloud API (Template or Text) with fallback to legacy gateway.
     */
    public function sendOtp(string $number, string $code, ?string $fallbackText = null): bool
    {
        $to = $this->formatPhoneNumber($number);
        if (empty($to)) {
            Log::warning("WhatsAppService::sendOtp: Número de teléfono inválido o vacío: {$number}");
            return false;
        }

        $defaultText = "Tu código OTP es: {$code}. No lo compartas con nadie. Válido 5 minutos.";
        $text = $fallbackText ?: $defaultText;

        if ($this->provider === 'meta' && $this->isMetaConfigured()) {
            if (!empty($this->metaOtpTemplate)) {
                $sentTemplate = $this->sendMetaTemplateOtp($to, $code);
                if ($sentTemplate) {
                    return true;
                }
                Log::warning("WhatsAppService::sendOtp: Falló plantilla Meta '{$this->metaOtpTemplate}', reintentando texto directo.");
            }

            $sentText = $this->sendMetaText($to, $text);
            if ($sentText) {
                return true;
            }

            Log::warning("WhatsAppService::sendOtp: Falló envío por Meta Cloud API, evaluando fallback legacy.");
        }

        // Fallback to legacy gateway if Meta is not configured or failed
        return $this->sendLegacyMessage($to, $text);
    }

    /**
     * Sends an OTP message via Meta WhatsApp Cloud API template.
     */
    public function sendMetaTemplateOtp(string $to, string $code, array $extraBodyParams = []): bool
    {
        if (!$this->isMetaConfigured()) {
            Log::warning("WhatsAppService::sendMetaTemplateOtp: Meta Cloud API no configurado.");
            return false;
        }

        $formattedTo = $this->formatPhoneNumber($to);
        if (empty($formattedTo)) {
            Log::warning("WhatsAppService::sendMetaTemplateOtp: Destinatario inválido o vacío: {$to}");
            return false;
        }

        try {
            $url = "https://graph.facebook.com/{$this->metaApiVersion}/{$this->metaPhoneNumberId}/messages";

            $bodyParams = [
                [
                    'type' => 'text',
                    'text' => (string) $code,
                ],
            ];
            foreach ($extraBodyParams as $extra) {
                $bodyParams[] = [
                    'type' => 'text',
                    'text' => (string) $extra,
                ];
            }

            $components = [
                [
                    'type' => 'body',
                    'parameters' => $bodyParams,
                ],
            ];

            if (!empty($this->metaOtpButtonType)) {
                $btnType = strtolower(trim($this->metaOtpButtonType));
                if ($btnType === 'copy_code') {
                    $btnParam = [
                        'type' => 'coupon_code',
                        'coupon_code' => (string) $code,
                    ];
                } else {
                    $btnParam = [
                        'type' => 'text',
                        'text' => (string) $code,
                    ];
                }

                $components[] = [
                    'type' => 'button',
                    'sub_type' => $btnType,
                    'index' => 0,
                    'parameters' => [$btnParam],
                ];
            }

            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $formattedTo,
                'type' => 'template',
                'template' => [
                    'name' => $this->metaOtpTemplate,
                    'language' => [
                        'code' => $this->metaOtpLang ?: 'es',
                    ],
                    'components' => $components,
                ],
            ];

            Log::debug('WhatsAppService::sendMetaTemplateOtp request', [
                'url' => $url,
                'to' => $formattedTo,
                'template' => $this->metaOtpTemplate,
            ]);

            $response = Http::withToken($this->metaToken)
                ->timeout(15)
                ->post($url, $payload);

            if ($response->successful()) {
                Log::info("WhatsAppService: OTP enviado vía Meta Cloud API Template a {$formattedTo}", [
                    'message_id' => $response->json('messages.0.id'),
                    'template' => $this->metaOtpTemplate,
                ]);
                return true;
            }

            Log::warning("WhatsAppService: Falló Meta Cloud API Template a {$formattedTo}", [
                'status' => $response->status(),
                'error' => $response->json('error') ?: $response->body(),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::error("WhatsAppService::sendMetaTemplateOtp excepción al enviar a {$formattedTo}: " . $e->getMessage(), [
                'exception' => $e,
            ]);
            return false;
        }
    }

    /**
     * Sends a text message via Meta WhatsApp Cloud API.
     */
    public function sendMetaText(string $to, string $text): bool
    {
        if (!$this->isMetaConfigured()) {
            Log::warning("WhatsAppService::sendMetaText: Meta Cloud API no configurado.");
            return false;
        }

        $formattedTo = $this->formatPhoneNumber($to);
        if (empty($formattedTo)) {
            Log::warning("WhatsAppService::sendMetaText: Destinatario inválido o vacío: {$to}");
            return false;
        }

        try {
            $url = "https://graph.facebook.com/{$this->metaApiVersion}/{$this->metaPhoneNumberId}/messages";

            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $formattedTo,
                'type' => 'text',
                'text' => [
                    'preview_url' => false,
                    'body' => $text,
                ],
            ];

            Log::debug('WhatsAppService::sendMetaText request', ['url' => $url, 'to' => $formattedTo]);

            $response = Http::withToken($this->metaToken)
                ->timeout(15)
                ->post($url, $payload);

            if ($response->successful()) {
                Log::info("WhatsAppService: Mensaje de texto enviado vía Meta Cloud API a {$formattedTo}", [
                    'message_id' => $response->json('messages.0.id'),
                ]);
                return true;
            }

            Log::warning("WhatsAppService: Falló Meta Cloud API texto a {$formattedTo}", [
                'status' => $response->status(),
                'error' => $response->json('error') ?: $response->body(),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::error("WhatsAppService::sendMetaText excepción al enviar a {$formattedTo}: " . $e->getMessage(), [
                'exception' => $e,
            ]);
            return false;
        }
    }

    /**
     * General method for sending WhatsApp messages.
     * Keeps full compatibility with existing controllers (ReservationController, etc.).
     */
    public function sendMessage(string $number, string $text): bool
    {
        $to = $this->formatPhoneNumber($number);
        if (empty($to)) {
            Log::warning("WhatsAppService: Número de teléfono inválido o vacío: {$number}");
            return false;
        }

        if ($this->provider === 'meta' && $this->isMetaConfigured()) {
            $sent = $this->sendMetaText($to, $text);
            if ($sent) {
                return true;
            }
            Log::info("WhatsAppService: Falló Meta Cloud API, intentando legacy gateway como fallback.");
        }

        return $this->sendLegacyMessage($to, $text);
    }

    /**
     * Sends a message through the legacy open-source gateway.
     */
    public function sendLegacyMessage(string $to, string $text): bool
    {
        if (empty($this->legacyEndpoint)) {
            Log::warning("WhatsAppService::sendLegacyMessage: Gateway endpoint no está configurado.");
            return false;
        }

        try {
            $sessionId = '';
            if ($this->posSetting && !empty($this->posSetting->whatsapp_session_id)) {
                $sessionId = (string) $this->posSetting->whatsapp_session_id;
            } else {
                $fresh = class_exists(PosSetting::class) ? PosSetting::first() : null;
                $sessionId = $fresh->whatsapp_session_id ?? '';
            }

            if (empty($sessionId)) {
                Log::warning("WhatsAppService: No hay `whatsapp_session_id` configurado en la BD. No se enviará el mensaje.", ['number' => $to]);
                return false;
            }

            $cleanTo = preg_replace('/^\+/', '', $to);
            $cleanTo = preg_replace('/\s+/', '', $cleanTo);

            $payload = [
                'sessionId' => $sessionId,
                'to' => $cleanTo,
                'text' => $text,
            ];

            Log::debug('WhatsAppService::sendLegacyMessage enviando request', [
                'endpoint' => $this->legacyEndpoint,
                'payload' => $payload,
            ]);

            $response = Http::timeout(15)->post($this->legacyEndpoint, $payload)->throw(false);

            Log::info('WhatsAppService::sendLegacyMessage respuesta recibida', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if ($response->successful()) {
                Log::info("WhatsAppService: Mensaje legacy enviado correctamente a {$to}", [
                    'endpoint' => $this->legacyEndpoint,
                ]);
                return true;
            }

            Log::warning("WhatsAppService: No se pudo enviar mensaje legacy a {$to}", [
                'status' => $response->status(),
                'body' => $response->body(),
                'endpoint' => $this->legacyEndpoint,
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::error("WhatsAppService::sendLegacyMessage error enviando a {$to}: " . $e->getMessage(), [
                'exception' => $e,
            ]);
            return false;
        }
    }

    // Getters for configuration and testing
    public function getProvider(): string { return (string) $this->provider; }
    public function getMetaPhoneNumberId(): ?string { return $this->metaPhoneNumberId; }
    public function getMetaApiVersion(): string { return $this->metaApiVersion; }
    public function getMetaOtpTemplate(): ?string { return $this->metaOtpTemplate; }
}

if (!class_exists(\App\Services\WhatsappService::class, false)) {
    class_alias(\App\Services\WhatsAppService::class, 'App\Services\WhatsappService');
}
