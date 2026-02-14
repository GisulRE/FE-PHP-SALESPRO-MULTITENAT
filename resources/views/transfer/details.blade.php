@extends('layout.main')
@section('content')
    <div class="container-fluid mt-4">

        @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                {{ session()->get('message') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-2">
            <h3 class="mb-0">
                Detalles de la Transferencia: {{ $transfer->reference_no }}
            </h3>
            @if(
                    $transfer->status == 2
                    && in_array('transfers-edit', $all_permission)
                    && in_array('accept-transfers', $all_permission)
                    && (
                        Auth::user()->role_id <= 2
                        || (optional(Auth::user()->biller)->warehouse_id == $transfer->to_warehouse_id)
                    )
                )
                <div>
                    <form action="{{ route('transfers.reject', $transfer->id) }}" method="POST" class="d-inline-block">
                        @csrf
                        <button class="btn btn-danger btn-md">Rechazar</button>
                    </form>
                    <form action="{{ route('transfers.approve', $transfer->id) }}" method="POST" class="d-inline-block">
                        @csrf
                        <button class="btn btn-success btn-md">Aprobar</button>
                    </form>
                </div>
            @endif


        </div>

        @php
            $transferStatuses = [
                1 => ['label' => trans('file.Completed'), 'class' => 'bg-success text-white'],
                2 => ['label' => trans('file.Pending'), 'class' => 'bg-warning text-white'],
                3 => ['label' => trans('file.Sent'), 'class' => 'bg-primary'],
                4 => ['label' => trans('file.Rejected'), 'class' => 'bg-danger text-white'],
            ];

            $currentStatus = $transferStatuses[$transfer->status] ?? ['label' => trans('file.Unknown'), 'class' => 'bg-secondary'];
        @endphp

        <p>
            <strong>Estado: </strong>
            <span class="badge {{ $currentStatus['class'] }}">{{ $currentStatus['label'] }}</span>
        </p>


        {{-- Información de almacenes --}}
        <div class="row mb-4">
            <div class="col-md-6 border-right">
                <h5 class="text-primary">Desde el Almacén</h5>
                <p class="mb-1"><strong>{{ $transfer->fromWarehouse->name }}</strong></p>
                <p class="mb-1">{{ $transfer->fromWarehouse->address }}</p>
                <p class="mb-0">{{ $transfer->fromWarehouse->phone }}</p>
            </div>
            <div class="col-md-6">
                <h5 class="text-primary">Hacia el Almacén</h5>
                <p class="mb-1"><strong>{{ $transfer->toWarehouse->name }}</strong></p>
                <p class="mb-1">{{ $transfer->toWarehouse->address }}</p>
                <p class="mb-0">{{ $transfer->toWarehouse->phone }}</p>
            </div>
        </div>

        <div class="table-responsive mb-4">
            <table class="table table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Costo Unitario</th>
                        <th>Impuesto</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transfer->items as $key => $item)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $item->product->name }}</td>
                            <td>{{ $item->qty }}</td>
                            <td>{{ number_format($item->unit_cost, 2) }}</td>
                            <td>{{ number_format($item->tax, 2) }}</td>
                            <td>{{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex mb-4 justify-content-between">
            <div class="flex-grow-1 mt-3 p-3 border rounded bg-light me-3 " style="min-width: 420px;">
                <h6 class="text-muted mb-1">Nota</h6>
                <p class="mb-0">{{ $transfer->note ?? 'Sin notas adicionales' }}</p>
            </div>

            <div class="mt-3 p-2 border rounded" style="min-width: 220px; text-align: right; background-color: #f8f9fa;">
                <p class="mb-1">
                    <span>Costo Total:</span>
                    <span style="display: inline-block; width: 90px; text-align: right;">
                        {{ number_format($transfer->total_cost, 2) }}
                    </span>
                </p>
                <p class="mb-1">
                    <span>Impuesto Total:</span>
                    <span style="display: inline-block; width: 90px; text-align: right;">
                        {{ number_format($transfer->total_tax, 2) }}
                    </span>
                </p>
                <p class="mb-1">
                    <span>Costo de Envío:</span>
                    <span style="display: inline-block; width: 90px; text-align: right;">
                        {{ number_format($transfer->shipping_cost, 2) }}
                    </span>
                </p>
                <p class="mb-0">
                    <span>Total General:</span>
                    <span style="display: inline-block; width: 90px; text-align: right;">
                        {{ number_format($transfer->grand_total, 2) }}
                    </span>
                </p>
            </div>
        </div>
    </div>
@endsection