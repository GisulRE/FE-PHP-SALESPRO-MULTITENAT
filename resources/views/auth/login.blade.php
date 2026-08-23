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
    <title>GISUL POS</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all,follow">
    <!-- Bootstrap CSS-->
    <link rel="stylesheet" href="/public/vendor/bootstrap/css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="/public/vendor/bootstrap/css/bootstrap-datepicker.min.css" type="text/css">
    <link rel="stylesheet" href="/public/vendor/bootstrap/css/bootstrap-select.min.css" type="text/css">
    <!-- Font Awesome CSS-->
    <link rel="stylesheet" href="/public/vendor/font-awesome/css/font-awesome.min.css" type="text/css">
    <!-- Google fonts - Roboto -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700">
    <!-- jQuery Circle-->
    <link rel="stylesheet" href="/public/css/grasp_mobile_progress_circle-1.0.0.min.css" type="text/css">
    <!-- Custom Scrollbar-->
    <link rel="stylesheet" href="/public/vendor/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.css" type="text/css">
    <!-- theme stylesheet-->
    <link rel="stylesheet" href="/public/css/style.default.css" id="theme-stylesheet" type="text/css">
    <!-- Custom stylesheet - for your changes-->
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

    <!-- Tweaks for older IEs--><!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->

    <script type="text/javascript" src="/public/vendor/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="/public/vendor/jquery/jquery-ui.min.js"></script>
    <script type="text/javascript" src="/public/vendor/jquery/bootstrap-datepicker.min.js"></script>
    <script type="text/javascript" src="/public/vendor/popper.js/umd/popper.min.js"></script>
    <script type="text/javascript" src="/public/vendor/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="/public/vendor/bootstrap/js/bootstrap-select.min.js"></script>
    <script type="text/javascript" src="/public/js/grasp_mobile_progress_circle-1.0.0.min.js"></script>
    <script type="text/javascript" src="/public/vendor/jquery.cookie/jquery.cookie.js"></script>
    <script type="text/javascript" src="/public/vendor/chart.js/Chart.min.js"></script>
    <script type="text/javascript" src="/public/vendor/jquery-validation/jquery.validate.min.js"></script>
    <script type="text/javascript" src="/public/vendor/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js"></script>
    <script type="text/javascript" src="/public/js/charts-home.js"></script>
    <script type="text/javascript" src="/public/js/front.js"></script>
    {!! RecaptchaV3::initJs() !!}
</head>

<body>
    <div class="page login-page">
        <div class="container">
            <div class="form-outer text-center d-flex align-items-center">
                <div class="form-inner">
                    <button type="button" id="login-theme-toggle" class="btn btn-sm btn-outline-secondary" style="position: absolute; top: 15px; right: 15px; border-radius: 50%; width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center; z-index: 10;" title="Cambiar Tema (Modo Nocturno)">
                        <i class="fa fa-moon-o" id="login-theme-icon"></i>
                    </button>
                    <div class="logo"><span>GISUL POS</span></div>
                    @if (session()->has('delete_message'))
                        <div class="alert alert-danger alert-dismissible text-center"><button type="button"
                                class="close" data-dismiss="alert" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>{{ session()->get('delete_message') }}
                        </div>
                    @endif
                    @if ($errors->has('nit_login') && str_contains($errors->first('nit_login'), 'suspendido'))
                        <div class="alert alert-danger alert-dismissible text-center">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <strong>Acceso bloqueado:</strong> {{ $errors->first('nit_login') }}
                        </div>
                    @endif
                    <form method="POST" action="{{ route('login') }}" id="login-form">
                        @csrf
                        <div class="form-group-material">
                            <input id="login-nit" type="text" name="nit_login" required class="input-material"
                                value="{{ old('nit_login') ?? \Illuminate\Support\Facades\Cookie::get('remember_nit') ?? session('login_nit') }}" maxlength="30">
                            <label for="login-nit" class="label-material">NIT *</label>
                            @if ($errors->has('nit_login'))
                                <p>
                                    <strong>{{ $errors->first('nit_login') }}</strong>
                                </p>
                            @endif
                        </div>

                        <div class="form-group-material">
                            <input id="login-username" type="text" name="name" required class="input-material"
                                value="">
                            <label for="login-username" class="label-material">{{ trans('file.UserName') }}</label>
                            @if ($errors->has('name'))
                                <p>
                                    <strong>{{ $errors->first('name') }}</strong>
                                </p>
                            @endif
                        </div>

                        <div class="form-group-material position-relative">
                            <input id="login-password" type="password" name="password" required class="input-material"
                                value="">
                            <button type="button" id="toggle-password" class="btn btn-link" aria-label="Mostrar contraseña" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); padding:0; color:#6c757d;">
                                <i class="fa fa-eye" aria-hidden="true"></i>
                            </button>
                            <label for="login-password" class="label-material">{{ trans('file.Password') }}</label>
                            @if ($errors->has('name'))
                                <p>
                                    <strong>{{ $errors->first('name') }}</strong>
                                </p>
                            @endif
                        </div>
                        <div class="form-group-material mt-3">
                            {!! RecaptchaV3::field('login') !!}
                            @if ($errors->has('g-recaptcha-response'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('g-recaptcha-response') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group text-left mb-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="remember" class="custom-control-input" id="remember" {{ old('remember') || true ? 'checked' : '' }}>
                                <label class="custom-control-label" for="remember" style="font-size: 0.85rem; color: #6c757d; cursor: pointer;">Recordar sesión</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">{{ trans('file.LogIn') }}</button>
                    </form>
                    <!-- This two button for demo only-->
                    <!-- <button type="submit" class="btn btn-success admin-btn">LogIn as Admin</button>
            <button type="submit" class="btn btn-info staff-btn">LogIn as Staff</button>
            <br><br> -->
                    <a href="{{ route('password.request') }}"
                        class="forgot-pass">{{ trans('file.Forgot Password?') }}</a>
                    {{-- <p>{{trans('file.Do not have an account?')}}</p><a href="{{url('register')}}" class="signup">{{trans('file.Register')}}</a> --}}
                </div>
                <div class="copyrights text-center">
                    <p>{{ trans('file.Developed By') }} <a href="http://www.gisul.com.bo/" class="external">Gisul
                            S.R.L.</a></p>
                    <div class="mt-2">
                        <span class="badge badge-secondary p-2">v2.5.0-20260822</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>

<script type="text/javascript">
    $('.admin-btn').on('click', function() {
        $("input[name='name']").focus().val('admin');
        $("input[name='password']").focus().val('admin');
    });

    $('.staff-btn').on('click', function() {
        $("input[name='name']").focus().val('staff');
        $("input[name='password']").focus().val('staff');
    });
    // ------------------------------------------------------- //
    // Material Inputs
    // ------------------------------------------------------ //

    var materialInputs = $('input.input-material');

    // activate labels for prefilled values
    materialInputs.filter(function() {
        return $(this).val() !== "";
    }).siblings('.label-material').addClass('active');

    // move label on focus
    materialInputs.on('focus', function() {
        $(this).siblings('.label-material').addClass('active');
    });

    // remove/keep label on blur
    materialInputs.on('blur', function() {
        $(this).siblings('.label-material').removeClass('active');

        if ($(this).val() !== '') {
            $(this).siblings('.label-material').addClass('active');
        } else {
            $(this).siblings('.label-material').removeClass('active');
        }
    });

    // Toggle password visibility
    $('#toggle-password').on('click', function() {
        var $input = $('#login-password');
        var type = $input.attr('type') === 'password' ? 'text' : 'password';
        $input.attr('type', type);
        $(this).attr('aria-label', type === 'password' ? 'Mostrar contraseña' : 'Ocultar contraseña');
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });

    // Persist NIT in localStorage and Cookies, and prefill on next login.
    var savedNit = localStorage.getItem('login_nit') || (typeof $.cookie === 'function' ? $.cookie('remember_nit') : '');
    if (savedNit && !$('#login-nit').val()) {
        $('#login-nit').val(savedNit);
    }
    if ($('#login-nit').val() !== '') {
        $('#login-nit').siblings('.label-material').addClass('active');
    }

    $('#login-form').on('submit', function() {
        var nit = $('#login-nit').val();
        if (nit) {
            localStorage.setItem('login_nit', nit);
            if (typeof $.cookie === 'function') {
                $.cookie('remember_nit', nit, { expires: 365, path: '/' });
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
</script>
