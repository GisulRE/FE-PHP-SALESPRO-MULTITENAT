@php
    $permissions = session('permissions');
    $role = DB::table('roles')->find(Auth::user()->role_id);

    // Si hay datos de prueba en sesión, sobrescribir las variables usadas por el dropdown/modal
    if (session()->has('test_notifications')) {
        $tn = session('test_notifications');
        $alert_product = $tn['alert_product'] ?? ($alert_product ?? 0);
        $alert_lote = $tn['alert_lote'] ?? ($alert_lote ?? 0);
        $alert_cuis = $tn['alert_cuis'] ?? ($alert_cuis ?? 0);
        $pendingTransfers = collect($tn['pendingTransfers'] ?? []);
        $pendingTransfersCount = $pendingTransfers->count();
    }
@endphp

<header class="header">
    <nav class="navbar">
        <div class="container-fluid">
            <div class="navbar-holder d-flex align-items-center justify-content-between">
                <ul class="nav-menu list-unstyled d-flex flex-md-row align-items-md-center">
                    <a id="toggle-btn" href="#" class="menu-btn"><i class="fa fa-bars"> </i></a>
                    @if (session()->has('token_siat'))
                        <li class="nav-item"><span><img src="{{ url('public/logo/logo_siat.png') }}" alt="logo_siat"
                                    width="60px"></span></li>
                    @endif
                </ul>
                <span class="brand-big">
                    @if ($general_setting->site_logo)
                        <img src="{{ url('public/logo', $general_setting->site_logo) }}" width="50">&nbsp;&nbsp;
                    @endif
                    <a href="{{ url('/') }}">
                        <h1 class="d-inline">{{ $general_setting->site_title }}</h1>
                    </a>
                </span>

                <ul class="nav-menu list-unstyled d-flex flex-md-row align-items-md-center">

                    @if (in_array('sales-add', $permissions))
                        <li class="nav-item">
                            <a class="dropdown-item btn-pos btn-sm" href="{{ route('sale.pos') }}"><i
                                    class="dripicons-shopping-bag"></i><span>
                                    POS</span>
                            </a>
                        </li>
                    @endif
                    <li class="nav-item"><a id="btnFullscreen"><i class="dripicons-expand"></i></a></li>
                    <li class="nav-item">
                        <a rel="nofollow" data-target="#" href="#" data-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false" class="nav-link dropdown-item">
                            <i class="dripicons-bell"></i>
                            <span class="badge badge-danger">
                                {{ $alert_product + $alert_lote + $alert_cuis + ($pendingTransfersCount ?? 0) }}
                            </span>
                        </a>

                        <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default notifications"
                            user="menu">

                            {{-- Productos --}}
                            @if ($alert_product > 0)
                                <li class="notifications" style="width: 100%;">
                                    <a href="{{ route('report.qtyAlert') }}" class="btn btn-link">
                                        {{ $alert_product }} producto(s) exceden cantidad de alerta
                                    </a>
                                </li>
                            @endif

                            {{-- Lotes --}}
                            @if ($alert_lote > 0)
                                <li class="notifications" style="width: 100%;">
                                    <a href="{{ url('report/alert_expiration/0/' . $general_setting->alert_expiration) }}"
                                        class="btn btn-link">
                                        {{ $alert_lote }} lote(s) por expirar pronto
                                    </a>
                                </li>
                            @endif

                            {{-- CUIS --}}
                            @if ($alert_cuis > 0)
                                <li class="notifications" style="width: 100%;">
                                    <a href="{{ url('punto_venta') }}" class="btn btn-link">
                                        {{ $alert_cuis }} cuis por expirar pronto! Punto Venta
                                    </a>
                                </li>
                            @endif

                            {{-- Transferencias pendientes --}}
                            @if ($pendingTransfers->count() > 0)
                                <li class="notifications" style="width: 100%;">
                                    <ul style="list-style: none; padding-left: 0; margin: 0;">
                                        @foreach ($pendingTransfers as $transfer)
                                            @if($transfer->status == 2)
                                                <li style="margin-bottom: 3px;">
                                                    <a href="{{ url('transfers/' . $transfer->id . '/details') }}"
                                                        class="btn btn-link text-truncate"
                                                        style="width: 100%; text-align: left; padding: 8px 10px; display: block; white-space: normal;">
                                                        Solicitud de transferencia del
                                                        <strong>{{ $transfer->fromWarehouse->name }}</strong><br>
                                                        <span class="text-muted small">
                                                            Fecha: {{ $transfer->created_at->format('d \d\e F Y') }}
                                                        </span>
                                                    </a>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </li>
                            @endif
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a rel="nofollow" data-target="#" href="#" data-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false" class="nav-link dropdown-item"><i class="dripicons-web"></i>
                            <span>{{ __('file.language') }}</span> <i class="fa fa-angle-down"></i></a>
                        <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                            <li>
                                <a href="{{ url('language_switch/en') }}" class="btn btn-link"> English</a>
                            </li>
                            <li>
                                <a href="{{ url('language_switch/es') }}" class="btn btn-link"> Español</a>
                            </li>
                            <li>
                                <a href="{{ url('language_switch/ar') }}" class="btn btn-link"> عربى</a>
                            </li>
                            <li>
                                <a href="{{ url('language_switch/pt_BR') }}" class="btn btn-link">
                                    Portuguese</a>
                            </li>
                            <li>
                                <a href="{{ url('language_switch/fr') }}" class="btn btn-link"> Français</a>
                            </li>
                            <li>
                                <a href="{{ url('language_switch/de') }}" class="btn btn-link"> Deutsche</a>
                            </li>
                            <li>
                                <a href="{{ url('language_switch/id') }}" class="btn btn-link"> Malay</a>
                            </li>
                            <li>
                                <a href="{{ url('language_switch/hi') }}" class="btn btn-link"> हिंदी</a>
                            </li>
                            <li>
                                <a href="{{ url('language_switch/vi') }}" class="btn btn-link"> Tiếng Việt</a>
                            </li>
                            <li>
                                <a href="{{ url('language_switch/ru') }}" class="btn btn-link"> русский</a>
                            </li>
                            <li>
                                <a href="{{ url('language_switch/tr') }}" class="btn btn-link"> Türk</a>
                            </li>
                            <li>
                                <a href="{{ url('language_switch/it') }}" class="btn btn-link"> Italiano</a>
                            </li>
                            <li>
                                <a href="{{ url('language_switch/nl') }}" class="btn btn-link"> Nederlands</a>
                            </li>
                            <li>
                                <a href="{{ url('language_switch/lao') }}" class="btn btn-link"> Lao</a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="{{ url('read_me') }}" target="_blank"><i
                                class="dripicons-information"></i> {{ trans('file.Help') }}</a>
                    </li>
                    <li class="nav-item">
                        <a rel="nofollow" data-target="#" href="#" data-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false" class="nav-link dropdown-item"><i class="dripicons-user"></i>
                            <span>{{ ucfirst(Auth::user()->name) }}</span> <i class="fa fa-angle-down"></i>
                        </a>
                        <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                            <li>
                                <a href="{{ route('user.profile', ['id' => Auth::id()]) }}"><i
                                        class="dripicons-user"></i> {{ trans('file.profile') }}</a>
                            </li>
                            @if (in_array('general_setting', $permissions))
                                <li>
                                    <a href="{{ route('setting.general') }}"><i class="dripicons-gear"></i>
                                        {{ trans('file.settings') }}</a>
                                </li>
                            @endif
                            <li>
                                <a href="{{ url('my-transactions/' . date('Y') . '/' . date('m')) }}"><i
                                        class="dripicons-swap"></i> {{ trans('file.My Transaction') }}</a>
                            </li>
                            <li>
                                <a href="{{ url('holidays/my-holiday/' . date('Y') . '/' . date('m')) }}"><i
                                        class="dripicons-vibrate"></i> {{ trans('file.My Holiday') }}</a>
                            </li>
                            @if (in_array('empty_database', $permissions))
                                <li>
                                    <a onclick="return confirm('Está seguro de eliminar todo? Si tu aceptas esta accion limpiara y se perdera los datos.')"
                                        href="{{ route('setting.emptyDatabase') }}"><i class="dripicons-stack"></i>
                                        {{ trans('file.Empty Database') }}</a>
                                </li>
                            @endif
                            @if (in_array('backup_database', $permissions))
                                <li>
                                    <a onclick="return confirm('Realizará una copia actual de la base de datos, ¿Deseas Descargar la copia?.')"
                                        href="{{ route('setting.backupDatabase') }}"><i class="dripicons-stack"></i>
                                        {{ trans('file.Backup Database') }}</a>
                                </li>
                            @endif
                            <li>
                                <a href="{{ route('logout') }}" onclick="event.preventDefault();
                                     document.getElementById('logout-form').submit();"><i class="dripicons-power"></i>
                                    {{ trans('file.logout') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                    style="display: none;">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<!-- Notifications Modal: muestra las mismas alertas que el dropdown -->
<div class="modal fade" id="notificationsModal" tabindex="-1" role="dialog" aria-labelledby="notificationsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Notificaciones y Alertas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height:60vh; overflow:auto;">
                <div style="padding:0 8px;">
                    {{-- Alertas (productos, lotes, cuis) --}}
                    <h6 class="mb-2"><i class="fa fa-exclamation-circle text-danger"></i> Alertas</h6>
                    <ul style="list-style: none; padding-left: 0; margin: 0 0 12px 0;">
                        @if ($alert_product > 0)
                            <li class="mb-2">
                                <a href="{{ route('report.qtyAlert') }}" class="d-block text-danger">
                                    <i class="fa fa-box"></i>
                                    <strong>{{ $alert_product }}</strong> producto(s) exceden cantidad de alerta
                                </a>
                            </li>
                        @endif

                        @if ($alert_lote > 0)
                            <li class="mb-2">
                                <a href="{{ url('report/alert_expiration/0/' . $general_setting->alert_expiration) }}" class="d-block text-warning">
                                    <i class="fa fa-calendar-alt"></i>
                                    <strong>{{ $alert_lote }}</strong> lote(s) por expirar pronto
                                </a>
                            </li>
                        @endif

                        @if ($alert_cuis > 0)
                            <li class="mb-2">
                                <a href="{{ url('punto_venta') }}" class="d-block text-info">
                                    <i class="dripicons-map"></i>
                                    <strong>{{ $alert_cuis }}</strong> cuis por expirar pronto (Punto Venta)
                                </a>
                            </li>
                        @endif
                    </ul>

                    {{-- Notificaciones (transferencias u otras) --}}
                    <h6 class="mb-2"><i class="dripicons-bell text-primary"></i> Notificaciones</h6>
                    <ul style="list-style: none; padding-left: 0; margin: 0;">
                        @if ($pendingTransfers->count() > 0)
                            @foreach ($pendingTransfers as $transfer)
                                @if($transfer->status == 2)
                                    <li class="mb-2">
                                        <a href="{{ url('transfers/' . $transfer->id . '/details') }}" class="d-block text-body" style="text-decoration:none;">
                                            <span class="mr-2 text-primary"><i class="fa fa-exchange-alt"></i></span>
                                            Solicitud de transferencia de <strong>{{ $transfer->fromWarehouse->name }}</strong>
                                            <br><small class="text-muted">{{ 
                                                isset($transfer->created_at) ? (\Carbon\Carbon::parse($transfer->created_at)->format('d/m/Y H:i')) : ''
                                            }}</small>
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        @else
                            <li class="text-muted">No hay notificaciones recientes.</li>
                        @endif
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@if(session()->has('show_notifications_modal'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            try {
                $('#notificationsModal').modal('show');
            } catch (e) {
                // fallback si jQuery/Bootstrap no están disponibles inmediatamente
                var modal = document.getElementById('notificationsModal');
                if (modal) {
                    // usar jQuery si existe
                    if (window.jQuery && jQuery(modal).modal) {
                        jQuery(modal).modal('show');
                    }
                }
            }
        });
    </script>
@endif