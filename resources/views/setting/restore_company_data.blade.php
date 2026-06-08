@extends('layout.main')

@section('content')
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible text-center">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            {{ session()->get('message') }}
        </div>
    @endif

    @if (session()->has('not_permitted'))
        <div class="alert alert-danger alert-dismissible text-center">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            {{ session()->get('not_permitted') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible text-center">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <ul class="mb-0 text-left" style="display:inline-block;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Aviso prominente cuando no hay worker corriendo --}}
    @if (config('queue.default') === 'database')
        <div class="alert alert-info alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            <strong><i class="fa fa-info-circle"></i> Información sobre colas:</strong>
            Si decides utilizar el <strong>Modo Cola</strong> para la importación, ten en cuenta que los jobs encolados con modo <code>database</code> necesitan un proceso externo corriendo en el servidor:
            <code>php artisan queue:work --queue=imports,default</code><br>
            <strong>Si no tienes un worker activo o estás en un entorno local, puedes usar el <em>Modo Síncrono</em> para realizar la importación directamente desde el navegador.</strong>
        </div>
    @endif

    <section class="forms">
        <div class="container-fluid">
            <div class="row">
                {{-- ===================== COLUMNA IZQUIERDA: formulario ===================== --}}
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4 class="mb-0"><i class="fa fa-database mr-2"></i>Restaurar datos de empresa desde archivo SQL</h4>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">
                                Sube un dump SQL generado desde MySQL/phpMyAdmin. El sistema analiza las tablas, 
                                reconstruye las relaciones y las importa bajo la empresa destino seleccionada.
                            </p>

                            {{-- PASO 1: Análisis --}}
                            <div class="card border-info mb-4">
                                <div class="card-header bg-info text-white py-2">
                                    <strong>Paso 1 — Analizar archivo SQL</strong>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('setting.restoreCompanyDataPreview') }}" method="POST" enctype="multipart/form-data" id="form-preview">
                                        @csrf
                                        <div class="form-group">
                                            <label for="company_id">Empresa Destino *</label>
                                            <select name="company_id" id="company_id" class="selectpicker form-control" data-live-search="true" title="Seleccione empresa..." required>
                                                @foreach ($companies as $company)
                                                    <option value="{{ $company->id }}" {{ ((old('company_id') ?? $companyId ?? null) == $company->id) ? 'selected' : '' }}>
                                                        {{ $company->id }} - {{ $company->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="restore_file">Archivo SQL (.sql o .txt, máx 200 MB) *</label>
                                            <input type="file" name="restore_file" id="restore_file" class="form-control" accept=".sql,.txt" required>
                                        </div>
                                        <button type="submit" class="btn btn-info" id="btn-preview">
                                            <i class="fa fa-search mr-1"></i> Analizar archivo y dependencias
                                        </button>
                                    </form>
                                </div>
                            </div>

                            {{-- Advertencias del análisis --}}
                            @if (!empty($previewWarnings))
                                <div class="alert alert-warning mt-2">
                                    <strong>Advertencias del análisis:</strong>
                                    <ul class="mb-0 mt-1">
                                        @foreach ($previewWarnings as $warning)
                                            <li>{{ $warning }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- PASO 2: Resultado del análisis + opciones de importación --}}
                            @if (!empty($comparison))
                                <hr>

                                {{-- Resumen --}}
                                <div class="alert alert-info mb-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Empresa destino:</strong> {{ $companyId }}<br>
                                            <strong>Tablas detectadas:</strong> {{ $totalDetectedTables ?? count($comparison) }}<br>
                                            <strong>Tablas a importar:</strong> {{ $totalImportableTables ?? count($comparison) }}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Tablas raíz del lote:</strong><br>
                                            {{ !empty($rootTables) ? implode(', ', $rootTables) : 'No detectadas' }}
                                        </div>
                                    </div>
                                </div>

                                {{-- Orden de migración --}}
                                @if (!empty($migrationOrder))
                                    <div class="card mb-3">
                                        <div class="card-header py-2"><strong>Orden de importación</strong></div>
                                        <div class="card-body py-2">
                                            @foreach ($migrationOrder as $index => $tableName)
                                                <span class="badge badge-primary mb-1">{{ $index + 1 }}. {{ $tableName }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if (!empty($omittedExcludedTables))
                                    <div class="alert alert-secondary mb-2">
                                        <strong>Tablas excluidas (sistema):</strong>
                                        @foreach ($omittedExcludedTables as $t) <span class="badge badge-secondary">{{ $t }}</span> @endforeach
                                    </div>
                                @endif

                                @if (!empty($omittedNoRowsTables))
                                    <div class="alert alert-light mb-2">
                                        <strong>Tablas sin filas INSERT en el dump:</strong>
                                        @foreach ($omittedNoRowsTables as $t) <span class="badge badge-light border">{{ $t }}</span> @endforeach
                                    </div>
                                @endif

                                {{-- Preview completo --}}
                                @if (!empty($detectedTablesPreview))
                                    <div class="card mb-4">
                                        <div class="card-header py-2"><strong>Preview de tablas detectadas</strong></div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive" style="max-height:260px;overflow-y:auto;">
                                                <table class="table table-sm table-bordered mb-0">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>Tabla</th><th>Filas</th><th>Estado</th><th>Orden</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($detectedTablesPreview as $tr)
                                                            <tr>
                                                                <td>{{ $tr['table'] }}</td>
                                                                <td>{{ $tr['rows'] }}</td>
                                                                <td>
                                                                    @if ($tr['status'] === 'se_importa') <span class="badge badge-success">Se importa</span>
                                                                    @elseif ($tr['status'] === 'excluida') <span class="badge badge-danger">Excluida</span>
                                                                    @elseif ($tr['status'] === 'sin_filas') <span class="badge badge-secondary">Sin filas</span>
                                                                    @else <span class="badge badge-warning">Omitida</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if ($tr['order']) <span class="badge badge-primary">{{ $tr['order'] }}</span>
                                                                    @else <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- PASO 2: Elegir modo de ejecución --}}
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white py-2">
                                        <strong>Paso 2 — Iniciar importación</strong>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-3">Elige cómo deseas ejecutar la importación:</p>

                                        <div class="row">
                                            {{-- Opción A: Modo Síncrono (recomendado sin worker) --}}
                                            <div class="col-md-6 mb-3">
                                                <div class="card border-success h-100">
                                                    <div class="card-body">
                                                        <h6 class="text-success"><i class="fa fa-bolt mr-1"></i> Modo Síncrono <span class="badge badge-success ml-1">Recomendado</span></h6>
                                                        <p class="text-muted small mb-3">
                                                            Ejecuta la importación <strong>directamente en esta request</strong>,
                                                            sin necesidad de un worker corriendo en el servidor.
                                                            La página espera hasta que termine.
                                                        </p>
                                                        <form action="{{ route('setting.restoreCompanyDataStore') }}" method="POST"
                                                              onsubmit="return confirmSync(this)">
                                                            @csrf
                                                            <input type="hidden" name="company_id" value="{{ $companyId }}">
                                                            <input type="hidden" name="temp_file" value="{{ $tempPath }}">
                                                            <input type="hidden" name="execution_mode" value="sync">
                                                            <button type="submit" class="btn btn-success btn-block">
                                                                <i class="fa fa-play-circle mr-1"></i> Ejecutar ahora (síncrono)
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Opción B: Modo Cola --}}
                                            <div class="col-md-6 mb-3">
                                                <div class="card border-secondary h-100">
                                                    <div class="card-body">
                                                        <h6 class="text-secondary"><i class="fa fa-list-ol mr-1"></i> Modo Cola</h6>
                                                        <p class="text-muted small mb-3">
                                                            Encola el job para que un <strong>worker externo</strong> lo procese.
                                                            Requiere <code>php artisan queue:work --queue=imports</code> corriendo.
                                                            Permite seguimiento en tiempo real.
                                                        </p>
                                                        <form action="{{ route('setting.restoreCompanyDataStore') }}" method="POST"
                                                              onsubmit="return confirm('Se encolará la importación. Asegúrate de tener un worker corriendo. ¿Continuar?')">
                                                            @csrf
                                                            <input type="hidden" name="company_id" value="{{ $companyId }}">
                                                            <input type="hidden" name="temp_file" value="{{ $tempPath }}">
                                                            <input type="hidden" name="execution_mode" value="queue">
                                                            <button type="submit" class="btn btn-outline-secondary btn-block">
                                                                <i class="fa fa-clock-o mr-1"></i> Encolar (requiere worker)
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ===================== COLUMNA DERECHA: estado + historial ===================== --}}
                <div class="col-lg-4">
                    {{-- Estado de colas --}}
                    <div class="card mb-3" id="queue-summary" data-queue-status-url="{{ route('setting.restoreCompanyDataQueuesStatus') }}">
                        <div class="card-header d-flex justify-content-between align-items-center py-2">
                            <strong>Estado del sistema de colas</strong>
                            <form action="{{ route('setting.restoreCompanyDataQueuesStop') }}" method="POST" class="mb-0"
                                onsubmit="return confirm('Se enviará queue:restart a los workers. ¿Continuar?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger">Detener workers</button>
                            </form>
                        </div>
                        <div class="card-body py-2">
                            <div class="mb-1">
                                Driver: <strong id="queue-driver">{{ $queueOverview['default_driver'] ?? config('queue.default') }}</strong>
                                &nbsp;|&nbsp; Pendientes: <span class="badge badge-info" id="queue-pending-total">{{ $queueOverview['pending_total'] ?? 0 }}</span>
                                &nbsp; Fallidos: <span class="badge badge-danger" id="queue-failed-total">{{ $queueOverview['failed_total'] ?? 0 }}</span>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">Import jobs: </small>
                                <span class="badge badge-secondary" id="import-jobs-queued">queued: {{ $queueOverview['import_jobs']['queued'] ?? 0 }}</span>
                                <span class="badge badge-primary" id="import-jobs-running">running: {{ $queueOverview['import_jobs']['running'] ?? 0 }}</span>
                                <span class="badge badge-warning" id="import-jobs-partial">partial: {{ $queueOverview['import_jobs']['partial'] ?? 0 }}</span>
                                <span class="badge badge-danger" id="import-jobs-failed">failed: {{ $queueOverview['import_jobs']['failed'] ?? 0 }}</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0" id="queue-pending-table">
                                    <thead><tr><th>Queue</th><th>Pendientes</th><th>Reintentos</th></tr></thead>
                                    <tbody>
                                        @forelse (($queueOverview['pending_by_queue'] ?? []) as $queue)
                                            <tr>
                                                <td>{{ $queue->queue }}</td>
                                                <td>{{ $queue->total }}</td>
                                                <td>{{ $queue->with_attempts }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center text-muted">Sin pendientes.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Historial de importaciones --}}
                    <div class="card">
                        <div class="card-header py-2"><strong>Historial de importaciones</strong></div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr><th>#</th><th>Empresa</th><th>Estado</th><th></th></tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentJobs as $job)
                                            @php
                                                $statusMap = ['completed'=>'success','failed'=>'danger','partial'=>'warning','cancelled'=>'secondary','running'=>'primary','cancelling'=>'dark','cancel_requested'=>'dark'];
                                                $statusBadge = isset($statusMap[$job->status]) ? $statusMap[$job->status] : 'info';
                                            @endphp
                                            <tr>
                                                <td>{{ $job->id }}</td>
                                                <td>{{ optional($job->company)->name }}</td>
                                                <td><span class="badge badge-{{ $statusBadge }}">{{ $job->status }}</span></td>
                                                <td class="text-right" style="white-space:nowrap;">
                                                    <a href="{{ route('setting.restoreCompanyData', ['job_id' => $job->id]) }}" class="btn btn-link btn-sm py-0">Ver</a>

                                                    {{-- Botón "Ejecutar ahora" para jobs atascados --}}
                                                    @if ($job->status === 'queued')
                                                        <form action="{{ route('setting.restoreCompanyDataRunNow', $job->id) }}" method="POST" class="d-inline-block"
                                                              onsubmit="return confirmRunNow({{ $job->id }})">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success btn-sm py-0" title="Ejecutar directamente sin worker">
                                                                <i class="fa fa-play"></i> Ahora
                                                            </button>
                                                        </form>
                                                    @endif

                                                    @if (in_array($job->status, ['failed', 'partial', 'cancelled'], true))
                                                        <form action="{{ route('setting.restoreCompanyDataRunNow', $job->id) }}" method="POST" class="d-inline-block"
                                                              onsubmit="return confirmRunNow({{ $job->id }})">
                                                            @csrf
                                                            <button type="submit" class="btn btn-warning btn-sm py-0" title="Re-ejecutar directamente">
                                                                <i class="fa fa-refresh"></i>
                                                            </button>
                                                        </form>
                                                    @endif

                                                    @if (in_array($job->status, ['queued', 'running', 'cancel_requested', 'cancelling'], true))
                                                        <form action="{{ route('setting.restoreCompanyDataCancel', $job->id) }}" method="POST" class="d-inline-block">
                                                            @csrf
                                                            <input type="hidden" name="mode" value="hard">
                                                            <button type="submit" class="btn btn-outline-danger btn-sm py-0"
                                                                    onclick="return confirm('¿Cancelar Job #{{ $job->id }}?')">
                                                                <i class="fa fa-times"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center text-muted">Sin historial todavía.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===================== SEGUIMIENTO DEL JOB ACTIVO ===================== --}}
            @if ($activeJob)
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center py-2">
                                <strong>Seguimiento del Job #{{ $activeJob->id }} — {{ optional($activeJob->company)->name }}</strong>
                                <div class="d-flex align-items-center flex-wrap" style="gap:4px;">
                                    @if ($activeJob->status === 'queued')
                                        {{-- Botón principal: ejecutar ahora (sin worker) --}}
                                        <form action="{{ route('setting.restoreCompanyDataRunNow', $activeJob->id) }}" method="POST" class="mb-0"
                                              onsubmit="return confirmRunNow({{ $activeJob->id }})">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fa fa-play-circle mr-1"></i> Ejecutar ahora (sin worker)
                                            </button>
                                        </form>
                                    @endif

                                    @if (in_array($activeJob->status, ['queued', 'running', 'cancel_requested', 'cancelling'], true))
                                        <form action="{{ route('setting.restoreCompanyDataCancel', $activeJob->id) }}" method="POST" class="mb-0">
                                            @csrf
                                            <input type="hidden" name="mode" value="soft">
                                            <button type="submit" class="btn btn-sm btn-outline-warning"
                                                    onclick="return confirm('Se solicitará cancelación del Job #{{ $activeJob->id }}. ¿Continuar?')">Cancelar</button>
                                        </form>
                                        <form action="{{ route('setting.restoreCompanyDataCancel', $activeJob->id) }}" method="POST" class="mb-0">
                                            @csrf
                                            <input type="hidden" name="mode" value="hard">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Cancelar EN SECO el Job #{{ $activeJob->id }}. ¿Continuar?')">En seco</button>
                                        </form>
                                    @endif

                                    @if (in_array($activeJob->status, ['failed', 'partial', 'cancelled'], true))
                                        <form action="{{ route('setting.restoreCompanyDataRunNow', $activeJob->id) }}" method="POST" class="mb-0"
                                              onsubmit="return confirmRunNow({{ $activeJob->id }})">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning">
                                                <i class="fa fa-refresh mr-1"></i> Re-ejecutar (sin worker)
                                            </button>
                                        </form>
                                        <form action="{{ route('setting.restoreCompanyDataRetry', $activeJob->id) }}" method="POST" class="mb-0">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning">Reintentar (cola)</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body">
                                {{-- Alerta especial cuando está en queued --}}
                                @if ($activeJob->status === 'queued')
                                    <div class="alert alert-warning mb-3">
                                        <i class="fa fa-clock-o mr-1"></i>
                                        <strong>Este job está esperando en la cola</strong> — no hay ningún worker procesándolo.
                                        Haz clic en <strong>"Ejecutar ahora"</strong> para ejecutarlo directamente sin necesidad de un worker.
                                    </div>
                                @endif

                                <div id="import-job-summary"
                                     data-status-url="{{ route('setting.restoreCompanyDataStatus', $activeJob->id) }}"
                                     data-active-job-id="{{ $activeJob->id }}">
                                    <div class="row mb-3">
                                        <div class="col-md-3"><strong>Estado:</strong> <span id="job-status-text">{{ $activeJob->status }}</span></div>
                                        <div class="col-md-3"><strong>Archivo:</strong> {{ $activeJob->source_name }}</div>
                                        <div class="col-md-3"><strong>Progreso:</strong> <span id="job-progress-text">0%</span></div>
                                        <div class="col-md-3">
                                            <strong>Tablas:</strong>
                                            <span id="job-tables-text">{{ $activeJob->processed_tables }}/{{ $activeJob->total_tables }}</span>
                                        </div>
                                    </div>

                                    <div class="progress mb-4" style="height: 24px;">
                                        <div id="job-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated"
                                             role="progressbar" style="width: 0%;">0%</div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-7">
                                            <h5>Progreso por tabla</h5>
                                            <div class="table-responsive" style="max-height:360px;overflow-y:auto;">
                                                <table class="table table-bordered table-sm" id="job-details-table">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>#</th><th>Tabla</th><th>Estado</th>
                                                            <th>Total</th><th>OK</th><th>Err</th><th>Pend</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($activeJob->details as $detail)
                                                            <tr>
                                                                <td>{{ $detail->sort_order }}</td>
                                                                <td>{{ $detail->table_name }}</td>
                                                                <td>
                                                                    @php
                                                                        $detailMap = ['completed'=>'success','failed'=>'danger','partial'=>'warning','running'=>'primary','cancelled'=>'secondary','cancelling'=>'dark'];
                                                                        $db = isset($detailMap[$detail->status]) ? $detailMap[$detail->status] : 'info';
                                                                    @endphp
                                                                    <span class="badge badge-{{ $db }}">{{ $detail->status }}</span>
                                                                </td>
                                                                <td>{{ $detail->total_rows }}</td>
                                                                <td class="text-success">{{ $detail->processed_rows }}</td>
                                                                <td class="text-danger">{{ $detail->failed_rows }}</td>
                                                                <td class="text-warning">{{ $detail->deferred_rows }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-lg-5">
                                            <h5>Logs en vivo</h5>
                                            <div id="job-live-logs" class="border rounded p-2"
                                                 style="height: 360px; overflow-y: auto; background: #1a1a2e; color: #e0e0ff; font-family: monospace; font-size: 12px;">
                                                @foreach ($activeJob->logs->take(80) as $log)
                                                    <div class="log-entry log-{{ $log->level }}">
                                                        [{{ $log->created_at ? $log->created_at->format('H:i:s') : '--:--:--' }}]
                                                        {{ strtoupper($log->level) }} — {{ $log->message }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection

@section('scripts')
<style>
    .log-entry { padding: 1px 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
    .log-info    { color: #90caf9; }
    .log-warning { color: #fff176; }
    .log-error   { color: #ef9a9a; }
    .log-debug   { color: #a5d6a7; }
</style>
<script type="text/javascript">
(function () {
    // ——— Confirmaciones ———
    window.confirmSync = function (form) {
        return confirm(
            'La importación se ejecutará AHORA MISMO en el navegador.\n' +
            'La página se bloqueará hasta que termine (puede tardar varios minutos).\n\n' +
            '¿Deseas continuar?'
        );
    };

    window.confirmRunNow = function (jobId) {
        return confirm(
            'El Job #' + jobId + ' se ejecutará directamente sin worker de cola.\n' +
            'La página se bloqueará hasta que termine.\n\n¿Continuar?'
        );
    };

    // ——— Polling del job activo ———
    var summaryEl       = document.getElementById('import-job-summary');
    var queueSummaryEl  = document.getElementById('queue-summary');
    var statusUrl       = summaryEl      ? summaryEl.getAttribute('data-status-url')           : null;
    var queueStatusUrl  = queueSummaryEl ? queueSummaryEl.getAttribute('data-queue-status-url') : null;

    var progressBar         = document.getElementById('job-progress-bar');
    var progressText        = document.getElementById('job-progress-text');
    var statusText          = document.getElementById('job-status-text');
    var tablesText          = document.getElementById('job-tables-text');
    var detailsTableBody    = document.querySelector('#job-details-table tbody');
    var logBox              = document.getElementById('job-live-logs');
    var queueDriverEl       = document.getElementById('queue-driver');
    var queuePendingTotalEl = document.getElementById('queue-pending-total');
    var queueFailedTotalEl  = document.getElementById('queue-failed-total');
    var queuedJobsEl        = document.getElementById('import-jobs-queued');
    var runningJobsEl       = document.getElementById('import-jobs-running');
    var partialJobsEl       = document.getElementById('import-jobs-partial');
    var failedJobsEl        = document.getElementById('import-jobs-failed');
    var queuePendingTbody   = document.querySelector('#queue-pending-table tbody');
    var jobTimer            = null;
    var queueTimer          = null;

    function badgeClass(status) {
        var map = { completed:'success', failed:'danger', partial:'warning', cancelled:'secondary', running:'primary', cancelling:'dark', cancel_requested:'dark' };
        return map[status] || 'info';
    }

    function renderDetails(details) {
        if (!detailsTableBody) return;
        var html = '';
        for (var i = 0; i < details.length; i++) {
            var d = details[i];
            html += '<tr>' +
                '<td>' + d.sort_order + '</td>' +
                '<td>' + d.table_name + '</td>' +
                '<td><span class="badge badge-' + badgeClass(d.status) + '">' + d.status + '</span></td>' +
                '<td>' + d.total_rows + '</td>' +
                '<td class="text-success">' + d.processed_rows + '</td>' +
                '<td class="text-danger">'  + d.failed_rows    + '</td>' +
                '<td class="text-warning">' + d.deferred_rows  + '</td>' +
                '</tr>';
        }
        detailsTableBody.innerHTML = html;
    }

    function renderLogs(logs) {
        if (!logBox) return;
        var html = '';
        for (var i = logs.length - 1; i >= 0; i--) {
            var l = logs[i];
            var level = String(l.level || '').toLowerCase();
            var ts = l.created_at ? l.created_at.substring(11, 19) : '--:--:--';
            html += '<div class="log-entry log-' + level + '">[' + ts + '] ' +
                    level.toUpperCase() + ' — ' + l.message + '</div>';
        }
        logBox.innerHTML = html;
        logBox.scrollTop = 0;
    }

    function renderQueueOverview(overview) {
        if (!queueSummaryEl || !overview) return;
        var pq = overview.pending_by_queue || [];
        var ij = overview.import_jobs || {};

        if (queueDriverEl)       queueDriverEl.textContent       = overview.default_driver || '-';
        if (queuePendingTotalEl) queuePendingTotalEl.textContent = overview.pending_total  || 0;
        if (queueFailedTotalEl)  queueFailedTotalEl.textContent  = overview.failed_total   || 0;
        if (queuedJobsEl)        queuedJobsEl.textContent        = 'queued: '  + (ij.queued  || 0);
        if (runningJobsEl)       runningJobsEl.textContent       = 'running: ' + (ij.running || 0);
        if (partialJobsEl)       partialJobsEl.textContent       = 'partial: ' + (ij.partial || 0);
        if (failedJobsEl)        failedJobsEl.textContent        = 'failed: '  + (ij.failed  || 0);

        if (queuePendingTbody) {
            var html = pq.length === 0
                ? '<tr><td colspan="3" class="text-center text-muted">Sin pendientes.</td></tr>'
                : pq.map(function (q) {
                    return '<tr><td>' + q.queue + '</td><td>' + q.total + '</td><td>' + q.with_attempts + '</td></tr>';
                  }).join('');
            queuePendingTbody.innerHTML = html;
        }
    }

    function pollStatus() {
        if (!statusUrl) return;
        $.get(statusUrl, function (response) {
            var job = response.job;
            var pct = job.percentage || 0;

            if (statusText)   statusText.textContent   = job.status;
            if (progressText) progressText.textContent = pct + '%';
            if (tablesText)   tablesText.textContent   = (job.processed_tables || 0) + '/' + (job.total_tables || 0);

            if (progressBar) {
                progressBar.style.width   = pct + '%';
                progressBar.textContent   = pct + '%';
            }

            var terminal = ['completed','failed','partial','cancelled'];
            if (terminal.indexOf(job.status) !== -1) {
                if (progressBar) progressBar.classList.remove('progress-bar-animated');
                if (jobTimer) { window.clearInterval(jobTimer); jobTimer = null; }
            }

            renderDetails(response.details || []);
            renderLogs(response.logs || []);
            renderQueueOverview(response.queue_overview || null);
        }).fail(function () {
            /* silently ignore network errors during polling */
        });
    }

    function pollQueueStatus() {
        if (!queueStatusUrl) return;
        $.get(queueStatusUrl, function (response) { renderQueueOverview(response); });
    }

    if (summaryEl) {
        pollStatus();
        jobTimer = window.setInterval(pollStatus, 3000);
    }

    if (queueSummaryEl) {
        pollQueueStatus();
        queueTimer = window.setInterval(pollQueueStatus, 5000);
    }
})();
</script>
@endsection
