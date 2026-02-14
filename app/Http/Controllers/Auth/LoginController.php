<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Traits\CufdTrait;
use App\Http\Traits\SiatTrait;
use App\PosSetting;
use App\SiatCufd;
use Log;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers, SiatTrait, CufdTrait;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    public function credentials(Request $request)
    {
        $this->getToken();
        $auth_siat = Session::get('auth_siat');
        Log::info('Login: auth_siat=' . ($auth_siat ? 'true' : 'false'));
        if ($auth_siat == true) {
            // Si está configurado CUFD centralizado, verificar existencia de CUFD central activo
            $pos_setting = PosSetting::latest()->first();
            if ($pos_setting) {
                $flag = ($pos_setting->cufd_centralizado ?? 0);
                Log::info('Login: pos_setting encontrado, cufd_centralizado=' . $flag);
            } else {
                Log::warning('Login: pos_setting no encontrado');
                $flag = 0;
            }

            if ($flag == 1) {
                Log::info('Login: modo CUFD centralizado activado, buscando CUFD central activo...');
                $centralCufd = SiatCufd::where('estado', true)
                    ->where(function ($q) {
                        $q->where('codigo_punto_venta', 0)->orWhere('sucursal', 0);
                    })->first();
                if ($centralCufd) {
                    Log::info('CUFD centralizado activo encontrado (ID=' . $centralCufd->id . ', codigo=' . substr($centralCufd->codigo_cufd,0,20) . '...), se omite renovación en login.');
                } else {
                    Log::info('No se encontró CUFD central activo; se procederá a renovar CUFD en todos los puntos.');
                    // No existe CUFD central activo, proceder a renovar normalmente
                    $this->tareaRenovarCufd();
                }
            } else {
                Log::info('Login: modo CUFD centralizado no activado; se procederá a renovar CUFD en login.');
                // Modo normal: renovar CUFD en login
                $this->tareaRenovarCufd();
            }
        } else {
            Log::info('Login: auth_siat es false, no se intentará renovar CUFD.');
        }
        $this->verificaGestionPV();
        $activeRecaptcha = false;
        if ((env('RECAPTCHAV3_SITEKEY') != null && env('RECAPTCHAV3_SITEKEY') != '') && (env('RECAPTCHAV3_SECRET') != null && env('RECAPTCHAV3_SECRET') != '')) {
            $activeRecaptcha = true;
        } else if (config('recaptchav3')) {
            $recaptcha = config('recaptchav3');
            if ($recaptcha['sitekey'] != '' && $recaptcha['secret'] != '') {
                $activeRecaptcha = true;
            } else {
                $activeRecaptcha = false;
            }
        } else {
            $activeRecaptcha = false;
        }

        if ($activeRecaptcha) {
            $this->validate($request, [
                'g-recaptcha-response' => [
                    'required',
                    'recaptchav3:login,0.5'
                ],
            ]);
        }

        $credentials = $request->only($this->username(), 'password');
        $credentials = array_add($credentials, 'is_deleted', '0');
        return $credentials;
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function username()
    {
        return 'name';
    }

    /**
     * After the user is authenticated, flash a flag to open notifications modal.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed $user
     * @return void
     */
    protected function authenticated(Request $request, $user)
    {
        session()->flash('show_notifications_modal', true);
    }
}