@extends('layout.main')

@section('content')
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible text-center">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{{ session()->get('message') }}
        </div>
    @endif

    @if (session()->has('not_permitted'))
        <div class="alert alert-danger alert-dismissible text-center">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible text-center">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <ul class="mb-0 text-left" style="display:inline-block;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="forms">
        <div class="container-fluid">
            @if (config('queue.default') === 'sync')
                <div class="alert alert-danger">
                    <strong>QUEUE_DRIVER actual:</strong> sync.
                    Para progreso en tiempo real debes cambiarlo a <strong>database</strong> y levantar un worker con
                    <strong>php artisan queue:work --queue=imports,default</strong>.
                </div>
            @endif

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4>Importación SQL Multitenant por company_id</h4>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">
                                Este flujo ya no inserta SQL crudo directamente. Primero analiza columnas y dependencias,
                                luego encola una importación con remapeo de IDs, migration_map y progreso por tabla.
                            </p>

                            <div class="alert alert-warning">
                                <strong>Importante:</strong> se omiten tablas de sistema y solo se procesan sentencias
                                INSERT. Las relaciones se reconstruyen mediante remapeo old_id -> new_id por empresa.
                            </div>

                            <form action="{{ route('setting.restoreCompanyDataPreview') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf

                                <div class="form-group">
                                    <label for="company_id">Empresa Destino *</label>
                                    <select name="company_id" id="company_id" class="selectpicker form-control"
                                        data-live-search="true" title="Seleccione empresa..." required>
                                        @foreach ($companies as $company)
                                            <option value="{{ $company->id }}"
                                                {{ ((old('company_id') ?? $companyId ?? null) == $company->id) ? 'selected' : '' }}>
                                                {{ $company->id }} - {{ $company->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="restore_file">Archivo SQL *</label>
                                    <input type="file" name="restore_file" id="restore_file" class="form-control"
                                        accept=".sql,.txt" required>
                                    <small class="form-text text-muted">
                                        Formatos permitidos: .sql, .txt (máximo 200 MB).
                                    </small>
                                </div>

                                <div class="form-group mt-4">
                                    <button type="submit" class="btn btn-info">
                                        Analizar Archivo y Dependencias
                                    </button>
                                </div>
                            </form>

                            @if (!empty($previewWarnings))
                                <div class="alert alert-warning mt-4">
                                    <strong>Advertencias del análisis:</strong>
                                    <ul class="mb-0 mt-2">
                                        @foreach ($previewWarnings as $warning)
                                            <li>{{ $warning }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if (!empty($comparison))
                                <hr>
                                <div class="card mb-3">
                                    <div class="card-header"><strong>Orden de importación (se aplicará en este orden)</strong></div>
                                    <div class="card-body">
                                        @if (!empty($migrationOrder))
                                            @foreach ($migrationOrder as $index => $tableName)
                                                <span class="badge badge-primary mb-1">{{ $index + 1 }}. {{ $tableName }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">No se pudo calcular.</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="alert alert-info mb-3">
                                    Empresa destino: <strong>{{ $companyId }}</strong><br>
                                    Tablas detectadas en el dump: <strong>{{ $totalDetectedTables ?? count($comparison) }}</strong><br>
                                    Tablas que se importarán: <strong>{{ $totalImportableTables ?? count($comparison) }}</strong><br>
                                    Tablas raíz del lote: <strong>{{ !empty($rootTables) ? implode(', ', $rootTables) : 'No detectadas' }}</strong>
                                </div>

                                @if (!empty($omittedExcludedTables))
                                    <div class="alert alert-warning mb-3">
                                        <strong>Tablas excluidas de importación (por configuración):</strong><br>
                                        @foreach ($omittedExcludedTables as $tableName)
                                            <span class="badge badge-secondary mb-1">{{ $tableName }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                @if (!empty($omittedNoRowsTables))
                                    <div class="alert alert-secondary mb-3">
                                        <strong>Tablas omitidas por no tener filas INSERT en el dump:</strong><br>
                                        @foreach ($omittedNoRowsTables as $tableName)
                                            <span class="badge badge-light mb-1">{{ $tableName }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                @if (!empty($detectedTablesPreview))
                                    <div class="card mb-3">
                                        <div class="card-header"><strong>Preview completo de tablas encontradas en el dump</strong></div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered mb-0">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>Tabla</th>
                                                            <th>Filas detectadas</th>
                                                            <th>Estado</th>
                                                            <th>Orden de importación</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($detectedTablesPreview as $tableRow)
                                                            <tr>
                                                                <td>{{ $tableRow['table'] }}</td>
                                                                <td>{{ $tableRow['rows'] }}</td>
                                                                <td>
                                                                    @if ($tableRow['status'] === 'se_importa')
                                                                        <span class="badge badge-success">Se importa</span>
                                                                    @elseif ($tableRow['status'] === 'excluida')
                                                                        <span class="badge badge-danger">Excluida</span>
                                                                    @elseif ($tableRow['status'] === 'sin_filas')
                                                                        <span class="badge badge-secondary">Sin filas INSERT</span>
                                                                    @else
                                                                        <span class="badge badge-warning">Omitida</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if ($tableRow['order'])
                                                                        <span class="badge badge-primary">{{ $tableRow['order'] }}</span>
                                                                    @else
                                                                        <span class="text-muted">-</span>
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

                                <form action="{{ route('setting.restoreCompanyDataStore') }}" method="POST" class="mt-4">
                                    @csrf
                                    <input type="hidden" name="company_id" value="{{ $companyId }}">
                                    <input type="hidden" name="temp_file" value="{{ $tempPath }}">
                                    <button type="submit" class="btn btn-primary"
                                        onclick="return confirm('Se encolará la importación multitenant con remapeo de relaciones. ¿Desea continuar?')">
                                        Iniciar Importación
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card mb-3" id="queue-summary"
                        data-queue-status-url="{{ route('setting.restoreCompanyDataQueuesStatus') }}">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <strong>Estado de colas</strong>
                            <form action="{{ route('setting.restoreCompanyDataQueuesStop') }}" method="POST" class="mb-0"
                                onsubmit="return confirm('Se enviara queue:restart. Los workers finalizaran el trabajo actual y se detendran. Deseas continuar?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger">Detener workers</button>
                            </form>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                Driver: <strong id="queue-driver">{{ $queueOverview['default_driver'] ?? config('queue.default') }}</strong>
                            </div>
                            <div class="mb-3">
                                Pendientes: <span class="badge badge-info" id="queue-pending-total">{{ $queueOverview['pending_total'] ?? 0 }}</span>
                                Fallidos: <span class="badge badge-danger" id="queue-failed-total">{{ $queueOverview['failed_total'] ?? 0 }}</span>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted d-block">Import jobs</small>
                                <span class="badge badge-secondary" id="import-jobs-queued">queued: {{ $queueOverview['import_jobs']['queued'] ?? 0 }}</span>
                                <span class="badge badge-primary" id="import-jobs-running">running: {{ $queueOverview['import_jobs']['running'] ?? 0 }}</span>
                                <span class="badge badge-info" id="import-jobs-cancel-requested">cancel_requested: {{ $queueOverview['import_jobs']['cancel_requested'] ?? 0 }}</span>
                                <span class="badge badge-dark" id="import-jobs-cancelling">cancelling: {{ $queueOverview['import_jobs']['cancelling'] ?? 0 }}</span>
                                <span class="badge badge-secondary" id="import-jobs-cancelled">cancelled: {{ $queueOverview['import_jobs']['cancelled'] ?? 0 }}</span>
                                <span class="badge badge-warning" id="import-jobs-partial">partial: {{ $queueOverview['import_jobs']['partial'] ?? 0 }}</span>
                                <span class="badge badge-danger" id="import-jobs-failed">failed: {{ $queueOverview['import_jobs']['failed'] ?? 0 }}</span>
                            </div>

                            <div class="table-responsive mb-2">
                                <table class="table table-sm table-bordered mb-0" id="queue-pending-table">
                                    <thead>
                                        <tr>
                                            <th>Queue</th>
                                            <th>Pendientes</th>
                                            <th>Reintentos</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse (($queueOverview['pending_by_queue'] ?? []) as $queue)
                                            <tr>
                                                <td>{{ $queue->queue }}</td>
                                                <td>{{ $queue->total }}</td>
                                                <td>{{ $queue->with_attempts }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">Sin pendientes.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <small class="text-muted">Senal de stop enviada:</small>
                            <span id="queue-stop-flag" class="badge badge-{{ !empty($queueOverview['workers_should_stop']) ? 'warning' : 'secondary' }}">
                                {{ !empty($queueOverview['workers_should_stop']) ? 'si' : 'no' }}
                            </span>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header">
                            <strong>Tablas raíz globales del proyecto</strong>
                        </div>
                        <div class="card-body">
                            @if (!empty($globalRootTables))
                                @foreach ($globalRootTables as $rootTable)
                                    <span class="badge badge-secondary mb-1">{{ $rootTable }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">No disponibles.</span>
                            @endif
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <strong>Historial de importaciones</strong>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Empresa</th>
                                            <th>Estado</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentJobs as $job)
                                            <tr>
                                                <td>{{ $job->id }}</td>
                                                <td>{{ optional($job->company)->name }}</td>
                                                <td>
                                                    @php
                                                        $statusBadge = 'info';
                                                        if (in_array($job->status, ['completed'], true)) {
                                                            $statusBadge = 'success';
                                                        } elseif (in_array($job->status, ['failed'], true)) {
                                                            $statusBadge = 'danger';
                                                        } elseif (in_array($job->status, ['partial'], true)) {
                                                            $statusBadge = 'warning';
                                                        } elseif (in_array($job->status, ['cancelled'], true)) {
                                                            $statusBadge = 'secondary';
                                                        } elseif (in_array($job->status, ['cancelling', 'cancel_requested'], true)) {
                                                            $statusBadge = 'dark';
                                                        }
                                                    @endphp
                                                    <span class="badge badge-{{ $statusBadge }}">
                                                        {{ $job->status }}
                                                    </span>
                                                </td>
                                                <td class="text-right">
                                                    <a href="{{ route('setting.restoreCompanyData', ['job_id' => $job->id]) }}" class="btn btn-link btn-sm">Ver</a>
                                                    @if (in_array($job->status, ['queued', 'running', 'cancel_requested', 'cancelling'], true))
                                                        <form action="{{ route('setting.restoreCompanyDataCancel', $job->id) }}" method="POST" class="d-inline-block">
                                                            @csrf
                                                            <input type="hidden" name="mode" value="soft">
                                                            <button type="submit" class="btn btn-outline-warning btn-sm"
                                                                onclick="return confirm('Se solicitara cancelacion del Job #{{ $job->id }}. Continuar?')">Cancelar</button>
                                                        </form>
                                                        <form action="{{ route('setting.restoreCompanyDataCancel', $job->id) }}" method="POST" class="d-inline-block">
                                                            @csrf
                                                            <input type="hidden" name="mode" value="hard">
                                                            <button type="submit" class="btn btn-outline-danger btn-sm"
                                                                onclick="return confirm('Se intentara cancelar EN SECO el Job #{{ $job->id }}. Continuar?')">En seco</button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">Sin historial todavía.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($activeJob)
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <strong>Seguimiento del Job #{{ $activeJob->id }}</strong>
                                <div class="d-flex align-items-center">
                                    @if (in_array($activeJob->status, ['queued', 'running', 'cancel_requested', 'cancelling'], true))
                                        <form action="{{ route('setting.restoreCompanyDataCancel', $activeJob->id) }}" method="POST" class="mb-0 mr-1">
                                            @csrf
                                            <input type="hidden" name="mode" value="soft">
                                            <button type="submit" class="btn btn-sm btn-outline-warning"
                                                onclick="return confirm('Se solicitara cancelacion del Job #{{ $activeJob->id }}. Continuar?')">Cancelar</button>
                                        </form>
                                        <form action="{{ route('setting.restoreCompanyDataCancel', $activeJob->id) }}" method="POST" class="mb-0 mr-1">
                                            @csrf
                                            <input type="hidden" name="mode" value="hard">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Se intentara cancelar EN SECO el Job #{{ $activeJob->id }}. Continuar?')">En seco</button>
                                        </form>
                                    @endif

                                    @if (in_array($activeJob->status, ['failed', 'partial', 'cancelled'], true))
                                        <form action="{{ route('setting.restoreCompanyDataRetry', $activeJob->id) }}" method="POST" class="mb-0">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning">Reintentar</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="import-job-summary"
                                    data-status-url="{{ route('setting.restoreCompanyDataStatus', $activeJob->id) }}"
                                    data-active-job-id="{{ $activeJob->id }}">
                                    <div class="row mb-3">
                                        <div class="col-md-3"><strong>Estado:</strong> <span id="job-status-text">{{ $activeJob->status }}</span></div>
                                        <div class="col-md-3"><strong>Empresa:</strong> {{ optional($activeJob->company)->name }}</div>
                                        <div class="col-md-3"><strong>Archivo:</strong> {{ $activeJob->source_name }}</div>
                                        <div class="col-md-3"><strong>Progreso:</strong> <span id="job-progress-text">0%</span></div>
                                    </div>

                                    <div class="progress mb-4" style="height: 22px;">
                                        <div id="job-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;">0%</div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-7">
                                            <h5>Progreso por tabla</h5>
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-sm" id="job-details-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Orden</th>
                                                            <th>Tabla</th>
                                                            <th>Estado</th>
                                                            <th>Total</th>
                                                            <th>Procesadas</th>
                                                            <th>Fallidas</th>
                                                            <th>Pendientes</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($activeJob->details as $detail)
                                                            <tr>
                                                                <td>{{ $detail->sort_order }}</td>
                                                                <td>{{ $detail->table_name }}</td>
                                                                <td>{{ $detail->status }}</td>
                                                                <td>{{ $detail->total_rows }}</td>
                                                                <td>{{ $detail->processed_rows }}</td>
                                                                <td>{{ $detail->failed_rows }}</td>
                                                                <td>{{ $detail->deferred_rows }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-lg-5">
                                            <h5>Logs en vivo</h5>
                                            <div id="job-live-logs" class="border rounded p-2" style="height: 360px; overflow-y: auto; background: #111; color: #ddd; font-family: monospace; font-size: 12px;">
                                                @foreach ($activeJob->logs->take(80) as $log)
                                                    <div>[{{ $log->created_at ? $log->created_at->format('H:i:s') : '--:--:--' }}] {{ strtoupper($log->level) }} - {{ $log->message }}</div>
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
<script type="text/javascript">
    (function () {
        var summaryEl = document.getElementById('import-job-summary');
        var queueSummaryEl = document.getElementById('queue-summary');

        var statusUrl = summaryEl ? summaryEl.getAttribute('data-status-url') : null;
        var queueStatusUrl = queueSummaryEl ? queueSummaryEl.getAttribute('data-queue-status-url') : null;
        var progressBar = document.getElementById('job-progress-bar');
        var progressText = document.getElementById('job-progress-text');
        var statusText = document.getElementById('job-status-text');
        var detailsTableBody = document.querySelector('#job-details-table tbody');
        var logBox = document.getElementById('job-live-logs');
        var queueDriverEl = document.getElementById('queue-driver');
        var queuePendingTotalEl = document.getElementById('queue-pending-total');
        var queueFailedTotalEl = document.getElementById('queue-failed-total');
        var queuedJobsEl = document.getElementById('import-jobs-queued');
        var runningJobsEl = document.getElementById('import-jobs-running');
        var cancelRequestedJobsEl = document.getElementById('import-jobs-cancel-requested');
        var cancellingJobsEl = document.getElementById('import-jobs-cancelling');
        var cancelledJobsEl = document.getElementById('import-jobs-cancelled');
        var partialJobsEl = document.getElementById('import-jobs-partial');
        var failedJobsEl = document.getElementById('import-jobs-failed');
        var queuePendingTableBody = document.querySelector('#queue-pending-table tbody');
        var queueStopFlagEl = document.getElementById('queue-stop-flag');
        var jobTimer = null;
        var queueTimer = null;

        function statusBadgeClass(status) {
            if (status === 'completed') return 'success';
            if (status === 'failed') return 'danger';
            if (status === 'partial') return 'warning';
            if (status === 'cancelled') return 'secondary';
            if (status === 'cancelling' || status === 'cancel_requested') return 'dark';
            return 'info';
        }

        function renderDetails(details) {
            if (!detailsTableBody) {
                return;
            }

            var html = '';
            for (var i = 0; i < details.length; i++) {
                html += '<tr>' +
                    '<td>' + details[i].sort_order + '</td>' +
                    '<td>' + details[i].table_name + '</td>' +
                    '<td><span class="badge badge-' + statusBadgeClass(details[i].status) + '">' + details[i].status + '</span></td>' +
                    '<td>' + details[i].total_rows + '</td>' +
                    '<td>' + details[i].processed_rows + '</td>' +
                    '<td>' + details[i].failed_rows + '</td>' +
                    '<td>' + details[i].deferred_rows + '</td>' +
                    '</tr>';
            }
            detailsTableBody.innerHTML = html;
        }

        function renderLogs(logs) {
            if (!logBox) {
                return;
            }

            var html = '';
            for (var i = logs.length - 1; i >= 0; i--) {
                html += '<div>[' + (logs[i].created_at || '--:--:--') + '] ' + String(logs[i].level || '').toUpperCase() + ' - ' + logs[i].message + '</div>';
            }
            logBox.innerHTML = html;
            logBox.scrollTop = 0;
        }

        function renderQueueOverview(overview) {
            if (!queueSummaryEl || !overview) {
                return;
            }

            var pendingByQueue = overview.pending_by_queue || [];
            var importJobs = overview.import_jobs || {};

            if (queueDriverEl) queueDriverEl.textContent = overview.default_driver || '-';
            if (queuePendingTotalEl) queuePendingTotalEl.textContent = overview.pending_total || 0;
            if (queueFailedTotalEl) queueFailedTotalEl.textContent = overview.failed_total || 0;
            if (queuedJobsEl) queuedJobsEl.textContent = 'queued: ' + (importJobs.queued || 0);
            if (runningJobsEl) runningJobsEl.textContent = 'running: ' + (importJobs.running || 0);
            if (cancelRequestedJobsEl) cancelRequestedJobsEl.textContent = 'cancel_requested: ' + (importJobs.cancel_requested || 0);
            if (cancellingJobsEl) cancellingJobsEl.textContent = 'cancelling: ' + (importJobs.cancelling || 0);
            if (cancelledJobsEl) cancelledJobsEl.textContent = 'cancelled: ' + (importJobs.cancelled || 0);
            if (partialJobsEl) partialJobsEl.textContent = 'partial: ' + (importJobs.partial || 0);
            if (failedJobsEl) failedJobsEl.textContent = 'failed: ' + (importJobs.failed || 0);

            if (queueStopFlagEl) {
                var shouldStop = !!overview.workers_should_stop;
                queueStopFlagEl.textContent = shouldStop ? 'si' : 'no';
                queueStopFlagEl.className = 'badge badge-' + (shouldStop ? 'warning' : 'secondary');
            }

            if (queuePendingTableBody) {
                var html = '';
                if (pendingByQueue.length === 0) {
                    html = '<tr><td colspan="3" class="text-center text-muted">Sin pendientes.</td></tr>';
                } else {
                    for (var i = 0; i < pendingByQueue.length; i++) {
                        html += '<tr>' +
                            '<td>' + pendingByQueue[i].queue + '</td>' +
                            '<td>' + pendingByQueue[i].total + '</td>' +
                            '<td>' + pendingByQueue[i].with_attempts + '</td>' +
                            '</tr>';
                    }
                }

                queuePendingTableBody.innerHTML = html;
            }
        }

        function pollStatus() {
            if (!statusUrl) {
                return;
            }

            $.get(statusUrl, function (response) {
                var job = response.job;
                var percentage = job.percentage || 0;

                statusText.textContent = job.status;
                progressText.textContent = percentage + '%';
                progressBar.style.width = percentage + '%';
                progressBar.textContent = percentage + '%';

                if (job.status === 'completed' || job.status === 'failed' || job.status === 'partial' || job.status === 'cancelled') {
                    progressBar.classList.remove('progress-bar-animated');
                    if (jobTimer) {
                        window.clearInterval(jobTimer);
                    }
                }

                renderDetails(response.details || []);
                renderLogs(response.logs || []);
                renderQueueOverview(response.queue_overview || null);
            });
        }

        function pollQueueStatus() {
            if (!queueStatusUrl) {
                return;
            }

            $.get(queueStatusUrl, function (response) {
                renderQueueOverview(response);
            });
        }

        if (summaryEl) {
            pollStatus();
            jobTimer = window.setInterval(pollStatus, 2500);
        }

        if (queueSummaryEl) {
            pollQueueStatus();
            queueTimer = window.setInterval(pollQueueStatus, 4000);
        }
    })();
</script>
@endsection
