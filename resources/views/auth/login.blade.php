<?php 
$general_setting = $general_setting ?? DB::table('general_settings')->find(1) ?? DB::table('general_settings')->first() ?? (object)[
    'theme' => 'default.css',
    'site_logo' => 'logo.png',
    'site_title' => 'GISUL POS'
];
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $general_setting->site_title ?? 'GISUL POS' }} - Iniciar Sesión</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all,follow">
    <!-- Bootstrap CSS-->
    <link rel="stylesheet" href="/public/vendor/bootstrap/css/bootstrap.min.css" type="text/css">
    <!-- Font Awesome CSS-->
    <link rel="stylesheet" href="/public/vendor/font-awesome/css/font-awesome.min.css" type="text/css">
    <!-- Google fonts - Inter -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <!-- theme stylesheet-->
    <link rel="stylesheet" href="/public/css/style.default.css" id="theme-stylesheet" type="text/css">
    <link rel="stylesheet" href="/public/css/custom-{{ $general_setting->theme }}" type="text/css">
    <link rel="stylesheet" href="/public/css/dark-mode.css" type="text/css" id="dark-mode-style">
    <script>
        (function() {
            var theme = localStorage.getItem('theme');
            if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark-mode');
                document.addEventListener('DOMContentLoaded', function() {
                    document.body.classList.add('dark-mode');
                });
            }
        })();
    </script>
    <!-- Favicon-->
    <link rel="icon" type="image/png" href="{{ url('logo', $general_setting->site_logo) }}" />

    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .login-card-container {
            width: 100%;
            max-width: 440px;
            padding: 15px;
        }

        .login-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08), 0 4px 6px rgba(0, 0, 0, 0.04);
            border: 1px solid #e2e8f0;
            padding: 2.5rem 2rem;
            position: relative;
            transition: all 0.3s ease;
        }

        .login-brand {
            margin-bottom: 2rem;
            text-align: center;
        }

        .login-brand .logo-img {
            max-height: 55px;
            margin-bottom: 12px;
        }

        .login-brand h2 {
            font-size: 1.6rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.5px;
            margin: 0;
        }

        .login-brand p {
            color: #64748b;
            font-size: 0.9rem;
            margin-top: 4px;
        }

        .modern-form-group {
            margin-bottom: 1.4rem;
            text-align: left;
        }

        .modern-form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }

        .modern-input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .modern-input-group .input-icon {
            position: absolute;
            left: 14px;
            color: #94a3b8;
            font-size: 1.1rem;
            pointer-events: none;
            z-index: 5;
        }

        .modern-input-group input.form-control {
            height: 48px;
            padding-left: 44px;
            padding-right: 40px;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            color: #0f172a;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .modern-input-group input.form-control:focus {
            background-color: #ffffff;
            border-color: #0284c7;
            box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.15);
            outline: none;
        }

        .toggle-pwd-btn {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            color: #94a3b8;
            padding: 4px 8px;
            cursor: pointer;
            z-index: 5;
        }

        .toggle-pwd-btn:hover {
            color: #0284c7;
        }

        .btn-modern-submit {
            height: 48px;
            border-radius: 10px;
            background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%);
            border: none;
            color: #ffffff;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 0.3px;
            width: 100%;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
            transition: all 0.2s ease;
            margin-top: 1rem;
        }

        .btn-modern-submit:hover {
            background: linear-gradient(135deg, #0369a1 0%, #1d4ed8 100%);
            box-shadow: 0 6px 16px rgba(2, 132, 199, 0.4);
            transform: translateY(-1px);
            color: #fff;
        }

        .login-theme-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #475569;
            border-radius: 50%;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .login-theme-btn:hover {
            background: #e2e8f0;
            color: #0284c7;
        }

        /* DARK MODE STYLES FOR MODERN LOGIN */
        body.dark-mode {
            background-color: #090d16 !important;
            color: #f8fafc !important;
        }

        body.dark-mode .login-card {
            background: #0f172a !important;
            border-color: #1e293b !important;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6) !important;
        }

        body.dark-mode .login-brand h2 {
            color: #f8fafc !important;
        }

        body.dark-mode .login-brand p {
            color: #94a3b8 !important;
        }

        body.dark-mode .modern-form-group label {
            color: #cbd5e1 !important;
        }

        body.dark-mode .modern-input-group input.form-control {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #ffffff !important;
        }

        body.dark-mode .modern-input-group input.form-control:focus {
            background-color: #162032 !important;
            border-color: #38bdf8 !important;
            box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.2) !important;
        }

        body.dark-mode .modern-input-group input:-webkit-autofill,
        body.dark-mode .modern-input-group input:-webkit-autofill:hover,
        body.dark-mode .modern-input-group input:-webkit-autofill:focus,
        body.dark-mode .modern-input-group input:-webkit-autofill:active {
            -webkit-text-fill-color: #ffffff !important;
            -webkit-box-shadow: 0 0 0px 1000px #1e293b inset !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        body.dark-mode .login-theme-btn {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        body.dark-mode .login-theme-btn:hover {
            background: #334155 !important;
            color: #38bdf8 !important;
        }

        body.dark-mode .custom-control-label {
            color: #cbd5e1 !important;
        }

        body.dark-mode .forgot-pass-link {
            color: #38bdf8 !important;
        }
    </style>

    <script type="text/javascript" src="/public/vendor/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="/public/vendor/jquery/bootstrap-datepicker.min.js"></script>
    <script type="text/javascript" src="/public/vendor/popper.js/umd/popper.min.js"></script>
    <script type="text/javascript" src="/public/vendor/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="/public/vendor/jquery.cookie/jquery.cookie.js"></script>
    {!! RecaptchaV3::initJs() !!}
</head>

<body>
    <div class="login-card-container">
        <div class="login-card">
            <button type="button" id="login-theme-toggle" class="login-theme-btn" title="Cambiar Tema (Modo Nocturno)">
                <i class="fa fa-moon-o" id="login-theme-icon"></i>
            </button>

            <div class="login-brand">
                @if(!empty($general_setting->site_logo))
                    <img src="{{ url('logo', $general_setting->site_logo) }}" class="logo-img" alt="Logo">
                @endif
                <h2>{{ $general_setting->site_title ?? 'GISUL POS' }}</h2>
                <p>Ingresa tus credenciales para acceder al sistema</p>
            </div>

            @if (session()->has('delete_message'))
                <div class="alert alert-danger alert-dismissible fade show text-center mb-4">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    {{ session()->get('delete_message') }}
                </div>
            @endif

            @if ($errors->has('nit_login') && str_contains($errors->first('nit_login'), 'suspendido'))
                <div class="alert alert-danger alert-dismissible fade show text-center mb-4">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <strong>Acceso bloqueado:</strong> {{ $errors->first('nit_login') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="login-form">
                @csrf

                <!-- CAMPO NIT -->
                <div class="modern-form-group">
                    <label for="login-nit">NIT *</label>
                    <div class="modern-input-group">
                        <i class="fa fa-id-card-o input-icon"></i>
                        <input id="login-nit" type="text" name="nit_login" required class="form-control"
                            placeholder="Número de NIT"
                            value="{{ old('nit_login') ?? \Illuminate\Support\Facades\Cookie::get('remember_nit') ?? session('login_nit') }}" maxlength="30">
                    </div>
                    @if ($errors->has('nit_login'))
                        <small class="text-danger font-weight-bold mt-1 d-block">{{ $errors->first('nit_login') }}</small>
                    @endif
                </div>

                <!-- CAMPO USUARIO -->
                <div class="modern-form-group">
                    <label for="login-username">{{ trans('file.UserName') }} *</label>
                    <div class="modern-input-group">
                        <i class="fa fa-user-o input-icon"></i>
                        <input id="login-username" type="text" name="name" required class="form-control"
                            placeholder="Nombre de usuario" value="{{ old('name') ?? \Illuminate\Support\Facades\Cookie::get('remember_username') }}">
                    </div>
                    @if ($errors->has('name'))
                        <small class="text-danger font-weight-bold mt-1 d-block">{{ $errors->first('name') }}</small>
                    @endif
                </div>

                <!-- CAMPO CONTRASEÑA -->
                <div class="modern-form-group">
                    <label for="login-password">{{ trans('file.Password') }} *</label>
                    <div class="modern-input-group">
                        <i class="fa fa-lock input-icon"></i>
                        <input id="login-password" type="password" name="password" required class="form-control"
                            placeholder="••••••••" value="">
                        <button type="button" id="toggle-password" class="toggle-pwd-btn" title="Mostrar/Ocultar contraseña">
                            <i class="fa fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group mt-2 mb-3">
                    {!! RecaptchaV3::field('login') !!}
                    @if ($errors->has('g-recaptcha-response'))
                        <small class="text-danger font-weight-bold d-block">{{ $errors->first('g-recaptcha-response') }}</small>
                    @endif
                </div>

                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="remember" class="custom-control-input" id="remember" {{ old('remember') || true ? 'checked' : '' }}>
                        <label class="custom-control-label" for="remember" style="font-size: 0.85rem; cursor: pointer;">Recordar sesión</label>
                    </div>
                    <div>
                        <a href="{{ route('password.request') }}" class="forgot-pass-link" style="font-size: 0.85rem; text-decoration: none; font-weight: 500;">{{ trans('file.Forgot Password?') }}</a>
                    </div>
                </div>

                <button type="submit" class="btn btn-modern-submit">{{ trans('file.LogIn') }}</button>
            </form>

            <div class="text-center mt-4 pt-3 border-top" style="border-color: rgba(148, 163, 184, 0.15) !important;">
                <p class="mb-1" style="font-size: 0.82rem; color: #94a3b8;">
                    {{ trans('file.Developed By') }} <a href="http://www.gisul.com.bo/" class="external font-weight-bold" style="color: #0284c7; text-decoration: none;">Gisul S.R.L.</a>
                </p>
                <span class="badge badge-secondary px-2 py-1 mt-1" style="font-size: 0.75rem; background-color: #334155; color: #cbd5e1;">v2.5.0-20260822</span>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {
            // Toggle password visibility with e.preventDefault()
            $(document).on('click', '#toggle-password', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var $input = $('#login-password');
                var currentType = $input.attr('type');
                var newType = currentType === 'password' ? 'text' : 'password';
                $input.attr('type', newType);
                $(this).find('i').toggleClass('fa-eye fa-eye-slash');
            });

            // Remember session & prefill NIT + Username
            var isRemembered = localStorage.getItem('login_remember') === 'true' || (typeof $.cookie === 'function' && $.cookie('login_remember') === 'true');
            var savedNit = localStorage.getItem('login_nit') || (typeof $.cookie === 'function' ? $.cookie('remember_nit') : '');
            var savedUser = localStorage.getItem('login_username') || (typeof $.cookie === 'function' ? $.cookie('remember_username') : '');

            if (savedNit && !$('#login-nit').val()) {
                $('#login-nit').val(savedNit);
            }
            if (savedUser && !$('#login-username').val()) {
                $('#login-username').val(savedUser);
            }

            if (isRemembered || savedNit || savedUser) {
                $('#remember').prop('checked', true);
            }

            $('#login-form').on('submit', function() {
                var isChecked = $('#remember').is(':checked');
                var nit = $('#login-nit').val();
                var user = $('#login-username').val();

                if (isChecked) {
                    localStorage.setItem('login_remember', 'true');
                    if (nit) localStorage.setItem('login_nit', nit);
                    if (user) localStorage.setItem('login_username', user);

                    if (typeof $.cookie === 'function') {
                        $.cookie('login_remember', 'true', { expires: 365, path: '/' });
                        if (nit) $.cookie('remember_nit', nit, { expires: 365, path: '/' });
                        if (user) $.cookie('remember_username', user, { expires: 365, path: '/' });
                    }
                } else {
                    localStorage.removeItem('login_remember');
                    localStorage.removeItem('login_nit');
                    localStorage.removeItem('login_username');

                    if (typeof $.cookie === 'function') {
                        $.cookie('login_remember', null, { path: '/' });
                        $.cookie('remember_nit', null, { path: '/' });
                        $.cookie('remember_username', null, { path: '/' });
                    }
                }
            });

            // Theme Toggle Handler on Login
            function updateLoginThemeIcon() {
                var icon = document.getElementById('login-theme-icon');
                if (!icon) return;
                if (document.documentElement.classList.contains('dark-mode') || document.body.classList.contains('dark-mode')) {
                    icon.className = 'fa fa-sun-o';
                } else {
                    icon.className = 'fa fa-moon-o';
                }
            }
            updateLoginThemeIcon();

            $('#login-theme-toggle').on('click', function(e) {
                e.preventDefault();
                var isDark = $('body').toggleClass('dark-mode').hasClass('dark-mode');
                $('html').toggleClass('dark-mode', isDark);
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
                updateLoginThemeIcon();
            });
        });
    </script>
</body>

</html>
