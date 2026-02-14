<div class="container-fluid ">
    <ul id="nav-siat" class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link" id="log" href="{{ route('siat_panel.log_siat') }}">
                Registros Siat
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="act" href="{{ route('siat_panel.index') }}">
                Actividad Económica
            </a>
        </li>
        <li class="nav-item" >
            <a class="nav-link" id="doc" href="{{ route('siat_panel.documento_sector') }}">
                Documento Sector
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="prod" href="{{ route('siat_panel.productoservicio') }}">
                Productos Servicios
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="ley" href="{{ route('siat_panel.leyenda') }}">
                Leyendas Facturas
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