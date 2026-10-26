<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\WhatsAppService;
use App\PosSetting;
use App\Customer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class WhatsAppServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_format_phone_number_various_formats()
    {
        $service = new WhatsAppService(null, [
            'token' => 'test-token',
            'phone_number_id' => '123456789',
        ]);

        // 8-digit Bolivian number -> adds 591
        $this->assertEquals('59176543210', $service->formatPhoneNumber('76543210'));

        // Number with country code +591
        $this->assertEquals('59176543210', $service->formatPhoneNumber('+591 76543210'));
        $this->assertEquals('59176543210', $service->formatPhoneNumber('591-76543210'));

        // Number with leading zero
        $this->assertEquals('59176543210', $service->formatPhoneNumber('076543210'));

        // International number
        $this->assertEquals('16505551234', $service->formatPhoneNumber('+1 (650) 555-1234'));

        // Invalid or empty
        $this->assertEquals('', $service->formatPhoneNumber(''));
        $this->assertEquals('', $service->formatPhoneNumber('+---()'));
    }

    public function test_is_meta_configured()
    {
        $serviceNotConfigured = new WhatsAppService(null, [
            'token' => '',
            'phone_number_id' => '',
        ]);
        $this->assertFalse($serviceNotConfigured->isMetaConfigured());

        $serviceConfigured = new WhatsAppService(null, [
            'token' => 'meta_access_token_123',
            'phone_number_id' => 'phone_id_999',
        ]);
        $this->assertTrue($serviceConfigured->isMetaConfigured());
    }

    public function test_meta_cloud_api_text_message_success()
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::response([
                'messaging_product' => 'whatsapp',
                'contacts' => [['input' => '59176543210', 'wa_id' => '59176543210']],
                'messages' => [['id' => 'wamid.HBgL12345']],
            ], 200),
        ]);

        $service = new WhatsAppService(null, [
            'provider' => 'meta',
            'token' => 'valid-meta-token',
            'phone_number_id' => '9876543210',
            'api_version' => 'v21.0',
        ]);

        $sent = $service->sendMessage('+591 76543210', 'Mensaje de prueba');
        $this->assertTrue($sent);

        Http::assertSent(function ($request) {
            $data = $request->data();
            return $request->url() === 'https://graph.facebook.com/v21.0/9876543210/messages'
                && $request->hasHeader('Authorization', 'Bearer valid-meta-token')
                && $data['messaging_product'] === 'whatsapp'
                && $data['to'] === '59176543210'
                && $data['type'] === 'text'
                && $data['text']['body'] === 'Mensaje de prueba';
        });
    }

    public function test_meta_cloud_api_template_otp_success()
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::response([
                'messaging_product' => 'whatsapp',
                'messages' => [['id' => 'wamid.OTP_SUCCESS_123']],
            ], 200),
        ]);

        $service = new WhatsAppService(null, [
            'provider' => 'meta',
            'token' => 'valid-meta-token',
            'phone_number_id' => '9876543210',
            'api_version' => 'v21.0',
            'otp_template_name' => 'auth_otp_code',
            'otp_template_lang' => 'es',
        ]);

        $sent = $service->sendOtp('76543210', '482910');
        $this->assertTrue($sent);

        Http::assertSent(function ($request) {
            $data = $request->data();
            return $request->url() === 'https://graph.facebook.com/v21.0/9876543210/messages'
                && $data['to'] === '59176543210'
                && $data['type'] === 'template'
                && $data['template']['name'] === 'auth_otp_code'
                && $data['template']['language']['code'] === 'es'
                && $data['template']['components'][0]['type'] === 'body'
                && $data['template']['components'][0]['parameters'][0]['text'] === '482910';
        });
    }

    public function test_meta_cloud_api_template_otp_with_copy_code_button()
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::response([
                'messaging_product' => 'whatsapp',
                'messages' => [['id' => 'wamid.OTP_BTN_123']],
            ], 200),
        ]);

        $service = new WhatsAppService(null, [
            'provider' => 'meta',
            'token' => 'valid-meta-token',
            'phone_number_id' => '9876543210',
            'otp_template_name' => 'auth_otp_with_copy',
            'otp_button_type' => 'copy_code',
        ]);

        $sent = $service->sendOtp('76543210', '998877');
        $this->assertTrue($sent);

        Http::assertSent(function ($request) {
            $data = $request->data();
            return $data['type'] === 'template'
                && count($data['template']['components']) === 2
                && $data['template']['components'][1]['type'] === 'button'
                && $data['template']['components'][1]['sub_type'] === 'copy_code'
                && $data['template']['components'][1]['index'] === 0
                && $data['template']['components'][1]['parameters'][0]['type'] === 'coupon_code'
                && $data['template']['components'][1]['parameters'][0]['coupon_code'] === '998877';
        });
    }

    public function test_meta_cloud_api_template_otp_with_url_button()
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::response([
                'messaging_product' => 'whatsapp',
                'messages' => [['id' => 'wamid.OTP_URL_123']],
            ], 200),
        ]);

        $service = new WhatsAppService(null, [
            'provider' => 'meta',
            'token' => 'valid-meta-token',
            'phone_number_id' => '9876543210',
            'otp_template_name' => 'auth_otp_with_url',
            'otp_button_type' => 'url',
        ]);

        $sent = $service->sendOtp('76543210', '554433');
        $this->assertTrue($sent);

        Http::assertSent(function ($request) {
            $data = $request->data();
            return $data['type'] === 'template'
                && count($data['template']['components']) === 2
                && $data['template']['components'][1]['type'] === 'button'
                && $data['template']['components'][1]['sub_type'] === 'url'
                && $data['template']['components'][1]['index'] === 0
                && $data['template']['components'][1]['parameters'][0]['type'] === 'text'
                && $data['template']['components'][1]['parameters'][0]['text'] === '554433';
        });
    }

    public function test_meta_template_fails_falls_back_to_meta_text()
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::sequence()
                ->push(['error' => ['message' => 'Template does not exist', 'code' => 100]], 400)
                ->push(['messages' => [['id' => 'wamid.TEXT_FALLBACK']]], 200),
        ]);

        $service = new WhatsAppService(null, [
            'provider' => 'meta',
            'token' => 'valid-meta-token',
            'phone_number_id' => '9876543210',
            'otp_template_name' => 'missing_template',
        ]);

        $sent = $service->sendOtp('76543210', '123456');
        $this->assertTrue($sent);

        Http::assertSentCount(2);
    }

    public function test_meta_not_configured_falls_back_to_legacy_gateway()
    {
        $mockPosSetting = new PosSetting();
        $mockPosSetting->url_whatsapp = 'http://154.53.54.236:3000';
        $mockPosSetting->whatsapp_session_id = 'session-gisul-1';

        Http::fake([
            'http://154.53.54.236:3000/*' => Http::response(['status' => 'success'], 200),
        ]);

        $service = new WhatsAppService($mockPosSetting, [
            'token' => '',
            'phone_number_id' => '',
        ]);

        $sent = $service->sendOtp('76543210', '654321');
        $this->assertTrue($sent);

        Http::assertSent(function ($request) {
            $data = $request->data();
            return $request->url() === 'http://154.53.54.236:3000/api/v1/messages/text'
                && $data['sessionId'] === 'session-gisul-1'
                && $data['to'] === '59176543210'
                && strpos($data['text'], '654321') !== false;
        });
    }

    public function test_empty_phone_returns_false_without_calling_api()
    {
        Http::fake();

        $service = new WhatsAppService(null, [
            'token' => 'token',
            'phone_number_id' => '123',
        ]);

        $this->assertFalse($service->sendOtp('', '123456'));
        $this->assertFalse($service->sendMessage('', 'test'));

        Http::assertNothingSent();
    }

    public function test_token_fallback_to_pos_setting_when_config_is_empty()
    {
        $mockPosSetting = new PosSetting();
        $mockPosSetting->whatsapp_access_token = 'db_token_xyz';

        $service = new WhatsAppService($mockPosSetting, [
            'token' => '',
            'phone_number_id' => 'phone_id_123',
        ]);

        $this->assertTrue($service->isMetaConfigured());
    }

    public function test_send_meta_text_and_template_formats_unformatted_destinations()
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.1']]], 200),
        ]);

        $service = new WhatsAppService(null, [
            'provider' => 'meta',
            'token' => 'test-token',
            'phone_number_id' => '123',
            'otp_template_name' => 'tpl_test',
        ]);

        // Direct call to sendMetaText with unformatted phone
        $service->sendMetaText('+591 76543210', 'Texto');

        Http::assertSent(function ($request) {
            return $request->data()['to'] === '59176543210';
        });

        // Direct call to sendMetaTemplateOtp with unformatted phone
        $service->sendMetaTemplateOtp('+591 76543210', '999111');

        Http::assertSent(function ($request) {
            return $request->data()['to'] === '59176543210';
        });
    }

    public function test_otp_cache_dual_key_consistency()
    {
        $phoneInput = '+591 76543210';
        $code = 654987;

        $cleanCache = preg_replace('/\D/', '', $phoneInput);
        Cache::put('otp:' . $cleanCache, $code, 300);
        if (strlen($cleanCache) > 8 && substr($cleanCache, 0, 3) === '591') {
            Cache::put('otp:' . substr($cleanCache, 3), $code, 300);
        }

        // Verify retrieval works using 8 digits
        $lookupPhone = '76543210';
        $clean = preg_replace('/\D/', '', $lookupPhone);
        $cached = Cache::get('otp:' . $clean);
        if (!$cached && strlen($clean) === 8) {
            $cached = Cache::get('otp:591' . $clean);
        }

        $this->assertEquals($code, $cached);
    }
}
