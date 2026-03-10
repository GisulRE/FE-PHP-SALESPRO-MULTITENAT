<?php

use Illuminate\Http\Request;
use App\Http\Controllers\ProxyController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\WhatsAppSessionController;
use App\Http\Controllers\WhatsAppMessageController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/

// WhatsApp UI flow (POS Ajustes → Información de WhatsApp)
Route::post('/whatsapp/auth/register', [WhatsAppSessionController::class, 'register'])
    ->name('whatsapp.auth.register');
Route::post('/whatsapp/auth/login', [WhatsAppSessionController::class, 'authLogin'])
    ->name('whatsapp.auth.login');
Route::post('/whatsapp/auth/logout', [WhatsAppSessionController::class, 'authLogout'])
    ->name('whatsapp.auth.logout');
Route::post('/whatsapp/auth/refresh', [WhatsAppSessionController::class, 'authRefresh'])
    ->name('whatsapp.auth.refresh');

// Multi-session management
Route::get('/whatsapp/sessions', [WhatsAppSessionController::class, 'listSessions'])
    ->name('whatsapp.sessions.list');
Route::delete('/whatsapp/sessions/{id}/local', [WhatsAppSessionController::class, 'destroyLocalSession'])
    ->name('whatsapp.sessions.local.destroy');
Route::post('/whatsapp/sessions/activate', [WhatsAppSessionController::class, 'activateSession'])
    ->name('whatsapp.sessions.activate');
Route::post('/whatsapp/sessions/{sessionName}/token', [WhatsAppSessionController::class, 'createSessionToken'])
    ->name('whatsapp.sessions.token.create');
Route::delete('/whatsapp/sessions/{sessionName}/token', [WhatsAppSessionController::class, 'revokeSessionToken'])
    ->name('whatsapp.sessions.token.revoke');

Route::get('/whatsapp/start-client', [WhatsAppSessionController::class, 'startClient'])
    ->name('whatsapp.session.start');
Route::get('/whatsapp/qr', [WhatsAppSessionController::class, 'qr'])
    ->name('whatsapp.session.qr');

Route::get('/whatsapp/status', [WhatsAppSessionController::class, 'status'])
    ->name('whatsapp.session.status');

Route::post('/whatsapp/logout', [WhatsAppSessionController::class, 'logout'])
    ->name('whatsapp.session.logout');

Route::delete('/whatsapp/session', [WhatsAppSessionController::class, 'deleteSession'])
    ->name('whatsapp.session.delete');

Route::post('/whatsapp/update-session-name', [WhatsAppSessionController::class, 'updateSessionName'])
    ->name('whatsapp.session.update-name');

// ==================== WhatsApp Messaging Endpoints ====================
// Mensajes de texto
Route::post('/v1/messages/text', [WhatsAppMessageController::class, 'sendText'])
    ->name('whatsapp.messages.text');

// Documentos
Route::post('/v1/messages/document', [WhatsAppMessageController::class, 'sendDocument'])
    ->name('whatsapp.messages.document');
Route::post('/v1/messages/document/upload', [WhatsAppMessageController::class, 'sendDocumentUpload'])
    ->name('whatsapp.messages.document.upload');

// Imágenes
Route::post('/v1/messages/image', [WhatsAppMessageController::class, 'sendImage'])
    ->name('whatsapp.messages.image');
Route::post('/v1/messages/image/upload', [WhatsAppMessageController::class, 'sendImageUpload'])
    ->name('whatsapp.messages.image.upload');

// Audio
Route::post('/v1/messages/audio', [WhatsAppMessageController::class, 'sendAudio'])
    ->name('whatsapp.messages.audio');
Route::post('/v1/messages/audio/upload', [WhatsAppMessageController::class, 'sendAudioUpload'])
    ->name('whatsapp.messages.audio.upload');

// Video
Route::post('/v1/messages/video', [WhatsAppMessageController::class, 'sendVideo'])
    ->name('whatsapp.messages.video');
Route::post('/v1/messages/video/upload', [WhatsAppMessageController::class, 'sendVideoUpload'])
    ->name('whatsapp.messages.video.upload');

// Ubicación
Route::post('/v1/messages/location', [WhatsAppMessageController::class, 'sendLocation'])
    ->name('whatsapp.messages.location');

// Contacto
Route::post('/v1/messages/contact', [WhatsAppMessageController::class, 'sendContact'])
    ->name('whatsapp.messages.contact');

// Botones interactivos
Route::post('/v1/messages/buttons', [WhatsAppMessageController::class, 'sendButtons'])
    ->name('whatsapp.messages.buttons');

// Lista interactiva
Route::post('/v1/messages/list', [WhatsAppMessageController::class, 'sendList'])
    ->name('whatsapp.messages.list');

// Envío masivo
Route::post('/v1/messages/bulk', [WhatsAppMessageController::class, 'sendBulk'])
    ->name('whatsapp.messages.bulk');

// Verificar número
Route::get('/v1/sessions/check-number/{phone}', [WhatsAppMessageController::class, 'checkNumber'])
    ->name('whatsapp.sessions.check-number');

Route::any('/whatsapp/{path}', [ProxyController::class, 'handle'])
    ->where('path', '.*')
    ->middleware(['proxy'])
    ->name('whatsapp.gateway');

// Ruta pública GET que devuelve todos los registros de la tabla `warehouses` en JSON
Route::get('/warehouses-public', [WarehouseController::class, 'apiAll']);

// Ruta pública GET que devuelve un warehouse por id en JSON
Route::get('/warehouses-public/{id}', [WarehouseController::class, 'apiShow'])
    ->where('id', '[0-9]+');

// Ruta pública GET que devuelve productos (servicios) con filtro por category_id y paginación
// Query params: category_id (optional), per_page (optional, default 10), page (optional)
Route::get('/products-public', [ProductController::class, 'apiIndex']);

// Ruta pública GET que devuelve todas las categorías
Route::get('/categories-public', [ProductController::class, 'apiCategories']);

// Public endpoints para reservas: comprobar disponibilidad y obtener franjas horarias
// Permitimos GET y POST para comprobar disponibilidad (acepta llamadas desde cliente y fetch POST).
Route::match(['get', 'post'], '/reservations/check-availability', [ReservationController::class, 'publicCheckAvailability']);
Route::get('/reservations/timeslots', [ReservationController::class, 'publicTimeSlots']);
// Endpoint público para crear reserva (asigna un empleado libre automáticamente)
// Permitimos tanto POST (recomendado) como GET (con parámetros query) para clientes simples.
Route::match(['get', 'post'], '/reservations/book', [ReservationController::class, 'publicCreateReservation']);

// Ruta pública para login/consulta por teléfono de cliente (legacy: /api/login-customer?phone=...)
Route::get('/login-customer', [CustomerController::class, 'apiByPhone']);
// Ruta pública GET para registrar un cliente mediante query params
Route::get('/register-customer', [CustomerController::class, 'apiRegister']);
// Ruta pública GET para enviar OTP vía WhatsApp: /api/send-otp?phone=+59112345678
Route::get('/send-otp', [CustomerController::class, 'apiSendOtp']);
// Ruta pública GET para verificar OTP: /api/verify-otp?phone=...&code=...
Route::get('/verify-otp', [CustomerController::class, 'apiVerifyOtp']);
Route::post('/whatsapp/logout', [WhatsAppSessionController::class, 'logout'])
    ->name('whatsapp.session.logout');

Route::delete('/whatsapp/session', [WhatsAppSessionController::class, 'deleteSession'])
    ->name('whatsapp.session.delete');

Route::post('/whatsapp/update-session-name', [WhatsAppSessionController::class, 'updateSessionName'])
    ->name('whatsapp.session.update-name');
