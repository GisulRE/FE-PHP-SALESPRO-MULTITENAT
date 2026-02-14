<?php
$panelsiat = DB::table('permissions')
    ->where('name', 'panel_siat')
    ->first();
$panelsiat_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $panelsiat->id], ['role_id', $role->id]])
    ->first();
$sucursal_siat = DB::table('permissions')
    ->where('name', 'sucursal_siat')
    ->first();
$sucursalsiat_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $sucursal_siat->id], ['role_id', $role->id]])
    ->first();
$urlws_siat = DB::table('permissions')
    ->where('name', 'urlws_siat')
    ->first();
$urlwssiat_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $urlws_siat->id], ['role_id', $role->id]])
    ->first();
$authfact_siat = DB::table('permissions')
    ->where('name', 'authfact_siat')
    ->first();
$authfactsiat_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $authfact_siat->id], ['role_id', $role->id]])
    ->first();
$puntoventa_siat = DB::table('permissions')
    ->where('name', 'puntoventa_siat')
    ->first();
$puntoventasiat_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $puntoventa_siat->id], ['role_id', $role->id]])
    ->first();
$cafc_siat = DB::table('permissions')
    ->where('name', 'cafc_siat')
    ->first();
$cafcsiat_permission_active = DB::table('role_has_permissions')
    ->where([['permission_id', $cafc_siat->id], ['role_id', $role->id]])
    ->first();
?>
<li><a href="#siat" aria-expanded="false" data-toggle="collapse">
        <i class="dripicons-pamphlet"></i>
        <span>Parametros {{ __('file.Siat') }}</span>
    </a>
    <ul id="siat" class="collapse list-unstyled ">
        @if ($panelsiat_permission_active)
            <li id="siat-menu-panel">
                <a href="{{ route('siat_panel.log_siat') }}">
                    Panel SIAT
                </a>
            </li>
        @endif
        @if ($sucursalsiat_permission_active)
            <li id="siat-menu-sucursal">
                <a href="{{ route('sucursal.index') }}">
                    Sucursal Siat
                </a>
            </li>
        @endif
        @if ($puntoventasiat_permission_active)
            <li id="siat-menu-p_venta">
                <a href="{{ route('punto_venta.index') }}">
                    Punto Venta Siat
                </a>
            </li>
        @endif
        @if ($urlwssiat_permission_active)
            <li id="siat-menu-url">
                <a href="{{ route('url-ws.index') }}">
                    URL WS
                </a>
            </li>
        @endif
        @if ($authfactsiat_permission_active)
            <li id="siat-menu-autfac">
                <a href="{{ route('autorizacion.index') }}">
                    Autorización/Facturación
                </a>
            </li>
        @endif
        @if ($cafcsiat_permission_active)
            <li id="siat-menu-cafc">
                <a href="{{ route('credencial-cafc.index') }}">
                    Credenciales CAFC
                </a>
            </li>
        @endif
        <br>
    </ul>
</li>