<li class="nav-item dropdown">
    <a id="server-latency-widget" rel="nofollow" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-item" title="Latencia / Rendimiento del Servidor" style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 20px; background: rgba(0,0,0,0.05); margin-right: 5px;">
        <span id="latency-indicator-dot" class="latency-dot status-green" style="width: 9px; height: 9px; border-radius: 50%; display: inline-block; background-color: #28a745; box-shadow: 0 0 5px #28a745;"></span>
        <span id="latency-text" class="latency-text" style="font-weight: 600; font-size: 12px; color: inherit;">-- ms</span>
    </a>
    <ul class="dropdown-menu dropdown-menu-right p-3" style="min-width: 260px; font-size: 0.85rem;">
        <li>
            <div class="d-flex align-items-center justify-content-between mb-2">
                <strong class="text-dark"><i class="fa fa-tachometer"></i> Conexión Servidor</strong>
                <span id="latency-status-badge" class="badge badge-success">Midiendo...</span>
            </div>
            <hr class="mt-1 mb-2">
            <div class="mb-1 d-flex justify-content-between">
                <span class="text-muted">Ping (Latencia):</span>
                <strong id="latency-ping-val">-- ms</strong>
            </div>
            <div class="mb-1 d-flex justify-content-between">
                <span class="text-muted">Última Petición:</span>
                <strong id="latency-last-ajax">-- ms</strong>
            </div>
            <div class="mb-2 d-flex justify-content-between">
                <span class="text-muted">Rendimiento:</span>
                <strong id="latency-network-status" class="text-success">Conectado</strong>
            </div>
            <div id="latency-warning-msg" class="alert alert-warning py-1 px-2 mb-2 d-none" style="font-size: 11px;">
                <i class="fa fa-exclamation-triangle"></i> Bajo rendimiento en la respuesta del servidor (>1500ms).
            </div>
            <button type="button" id="btn-refresh-latency" class="btn btn-sm btn-outline-primary btn-block mt-2">
                <i class="fa fa-refresh"></i> Medir Latencia Ahora
            </button>
        </li>
    </ul>
</li>
