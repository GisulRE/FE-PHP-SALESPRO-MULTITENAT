@extends('layout.main')
@section('content')

  <section class="forms">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-12">

          <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-start">
              <div class="text-start">
                <h4 class="mb-1">Actividad de Transferencias</h4>
                @if(isset($transfer))
                  <small class="text-muted d-block">
                    Origen: {{ $transfer->fromWarehouse->name ?? '-' }}
                  </small>
                  <small class="text-muted d-block">
                    Destino: {{ $transfer->toWarehouse->name ?? '-' }}
                  </small>
                @endif
              </div>
              <small class="text-muted align-self-end">Últimos primero</small>
            </div>


            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                  <thead class="bg-white">
                    <tr>
                      <th>#</th>
                      <th>Referencia</th>
                      <th>Usuario</th>
                      <th>Acción</th>
                      <th>Nota</th>
                      <th>Fecha</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($logs->sortByDesc('created_at') as $log)
                      <tr>
                        <td>{{ $log->transfer_id }}</td>
                        <td>{{ $log->transfer->reference_no ?? '-' }}</td>
                        <td>{{ $log->user->name ?? 'Sistema' }}</td>
                        <td>
                          @php
                            $actions = [
                              'created' => 'primary',
                              'aprobada' => 'success',
                              'rechazada' => 'danger',
                              'enviado' => 'info',
                              'recibido' => 'success',
                              'bloqueado' => 'warning',
                              'liberado' => 'secondary',
                              'desbloqueado' => 'secondary',
                            ];
                          @endphp
                          <span class="badge badge-{{ $actions[$log->action] ?? 'dark' }}">
                            {{ ucfirst($log->action) }}
                          </span>
                        </td>
                        <td>{{ $log->note }}</td>
                        <td>
                          @php
                            $diff = now()->diffInMinutes($log->created_at);
                          @endphp
                          @if($diff < 1)
                            hace un momento
                          @elseif($diff < 60)
                            hace {{ $diff }} minuto{{ $diff > 1 ? 's' : '' }}
                          @else
                            {{ $log->created_at->format('d/m/Y H:i:s') }}
                          @endif
                        </td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="6" class="text-center text-muted">No hay registros de logs.</td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>

              {{-- Paginación --}}
              <div class="mt-3 d-flex justify-content-center">
                {{ $logs->links() }}
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </section>

@endsection