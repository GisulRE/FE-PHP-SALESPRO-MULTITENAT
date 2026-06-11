<div class="container-fluid ">
    <ul id="nav-siat" class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link" id="log" href="{{ route('siat_panel.log_siat') }}">
                Registros Siat
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="datos" href="{{ route('siat_panel.datos_sincronizados') }}">
                Datos Sincronizados
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="par" href="{{ route('siat_panel.parametros') }}">
                Paramétricas
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="pago" href="{{ route('method_payment.index') }}">
                Método de Pago
            </a>
        </li>
    </ul>
</div>