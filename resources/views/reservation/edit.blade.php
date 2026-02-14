@extends('layout.main')
@section('content')
  <section>
    <div class="container-fluid">
      <h3>Editar Reserva</h3>
      {!! Form::model($reservation, ['route' => ['reservations.update', $reservation->id], 'method' => 'PUT']) !!}
      <div class="row">
        <div class="col-md-6 form-group">
          <label>Nombre *</label>
          <input type="text" name="name" required class="form-control" value="{{ $reservation->name }}">
        </div>
        <div class="col-md-6 form-group">
          <label>Telefono *</label>
          <input type="text" name="phone" required class="form-control" value="{{ $reservation->phone }}">
        </div>
        <div class="col-md-6 form-group">
          <label>Email</label>
          <input type="email" name="email" class="form-control" value="{{ $reservation->email }}">
        </div>
        <div class="col-md-6 form-group">
          <label>Servicio (Producto)</label>
          <select name="product_id" class="form-control selectpicker" data-live-search="true">
            <option value="">-- Seleccionar --</option>
            @foreach($products as $p)
              <option value="{{ $p->id }}" @if($reservation->product_id == $p->id) selected @endif>{{ $p->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-6 form-group">
          <label>Sucursal</label>
          <select name="sucursal_id" class="form-control selectpicker" data-live-search="true">
            <option value="">-- Seleccionar --</option>
            @foreach($warehouses as $w)
              <option value="{{ $w->id }}" @if($reservation->sucursal_id == $w->id) selected @endif>{{ $w->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3 form-group">
          <label>Fecha *</label>
          <input type="date" name="reserved_date" required class="form-control" value="{{ $reservation->reserved_date }}">
        </div>
        <div class="col-md-3 form-group">
          <label>Hora *</label>
          <input type="time" name="reserved_time" required class="form-control" value="{{ $reservation->reserved_time }}">
        </div>
        <div class="col-md-3 form-group">
          <label>Duración (min)</label>
          <input type="number" name="duration_minutes" class="form-control" min="1"
            value="{{ $reservation->duration_minutes }}">
        </div>
        <div class="col-md-3 form-group">
          <label>Estado</label>
          <select name="status" class="form-control selectpicker">
            <option value="pending" @if($reservation->status == 'pending') selected @endif>Pendiente</option>
            <option value="confirmed" @if($reservation->status == 'confirmed') selected @endif>Confirmada</option>
            <option value="cancelled" @if($reservation->status == 'cancelled') selected @endif>Cancelada</option>
            <option value="completed" @if($reservation->status == 'completed') selected @endif>Completada</option>
          </select>
        </div>
        <div class="col-md-12 form-group">
          <label>Notas</label>
          <textarea name="notes" rows="3" class="form-control">{{ $reservation->notes }}</textarea>
        </div>
        <div class="col-md-12 form-group">
          <button type="submit" class="btn btn-primary">Actualizar</button>
        </div>
      </div>
      {!! Form::close() !!}
    </div>
  </section>
@endsection