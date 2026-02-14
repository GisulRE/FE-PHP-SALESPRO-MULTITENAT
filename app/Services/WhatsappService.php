<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\PosSetting;

class WhatsAppService
{
  protected $endpoint;
  protected $posSetting;

  public function __construct()
  {
    $posSetting = PosSetting::first();
    $this->posSetting = $posSetting;

    $default = 'http://154.53.54.236:3000/api/v1/messages/text';

    if ($posSetting && !empty($posSetting->url_whatsapp)) {
      $this->endpoint = rtrim($posSetting->url_whatsapp, '/') . '/api/v1/messages/text';
      Log::info('WhatsAppService: usando campo url_whatsapp', ['endpoint' => $this->endpoint]);
    } elseif ($posSetting && !empty($posSetting->whatsapp_endpoint)) {
      $this->endpoint = rtrim($posSetting->whatsapp_endpoint, '/') . '/api/v1/messages/text';
      Log::info('WhatsAppService: usando campo whatsapp_endpoint (fallback)', ['endpoint' => $this->endpoint]);
    } else {
      $this->endpoint = $default;
      Log::info('WhatsAppService: usando endpoint por defecto (gateway)', ['endpoint' => $this->endpoint]);
    }
  }

  public function sendMessage(string $number, string $text)
  {
    if (empty($this->endpoint)) {
      Log::warning("No se pudo enviar mensaje a {$number}: WhatsApp endpoint no está configurado.");
      return false;
    }

    try {
      $sessionId = '';
      if ($this->posSetting && !empty($this->posSetting->whatsapp_session_id)) {
        $sessionId = (string) $this->posSetting->whatsapp_session_id;
      } else {
        $fresh = PosSetting::first();
        $sessionId = $fresh->whatsapp_session_id ?? '';
      }

      if (empty($sessionId)) {
        Log::warning("WhatsAppService: no hay `whatsapp_session_id` configurado en la BD. No se enviará el mensaje.", ['number' => $number]);
        return false;
      }

      $to = preg_replace('/^\+/', '', $number);
      $to = preg_replace('/\s+/', '', $to);

      $payload = [
        'sessionId' => $sessionId,
        'to' => $to,
        'text' => $text,
      ];

      Log::debug('WhatsAppService: enviando request', ['endpoint' => $this->endpoint, 'payload' => $payload]);

      $response = Http::post($this->endpoint, $payload)->throw(false);

      Log::info('WhatsAppService: respuesta recibida', ['status' => $response->status(), 'body' => $response->body()]);

      if ($response->successful()) {
        Log::info("Mensaje enviado correctamente a {$number}", ['endpoint' => $this->endpoint]);
        return true;
      }

      // If gateway returned 500 or other non-success, log and return false
      Log::warning("No se pudo enviar mensaje a {$number}", ['status' => $response->status(), 'body' => $response->body(), 'endpoint' => $this->endpoint]);
      return false;
    } catch (\Exception $e) {
      Log::error("Error enviando mensaje a {$number}: " . $e->getMessage(), ['exception' => $e]);
      return false;
    }
  }
}
