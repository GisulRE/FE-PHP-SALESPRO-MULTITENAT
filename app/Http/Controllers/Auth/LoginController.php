<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Traits\CufdTrait;
use App\Http\Traits\SiatTrait;
use App\Company;
use App\PosSetting;
use App\SiatCufd;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        $request->validate([
            'nit_login' => ['required', 'string', 'max:30'],
        ]);
        Session::put('login_nit', $request->input('nit_login'));

        $companyId = Company::where('nit', trim((string) $request->input('nit_login')))->value('id');
        Session::put('login_company_id', $companyId);

        // La renovación de CUFD y obtención de token de SIAT se remueven de aquí
        // para ejecutarse asíncronamente vía AJAX tras cargar el Dashboard.
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
        $credentials = array_add($credentials, 'company_id', $companyId ?: -1);
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
        session()->flash('perform_cufd_renewal_ajax', true);
    }
}