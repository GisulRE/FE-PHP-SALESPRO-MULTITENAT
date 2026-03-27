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
        <div class="col-md-6 form-group">
          <label>Empleado</label>
          <select name="employee_id" class="form-control selectpicker" data-live-search="true">
            <option value="">-- Seleccionar --</option>
            @if(!empty($employees))
              @foreach($employees as $emp)
                <option value="{{ $emp->id }}" @if($reservation->employee_id == $emp->id) selected @endif>{{ $emp->name }}
                </option>
              @endforeach
            @endif
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
          <div id="availabilityMessage"></div>
        </div>
        <div class="col-md-12 form-group">
          <button type="submit" id="btnSave" class="btn btn-primary">Actualizar</button>
        </div>
      </div>
      {!! Form::close() !!}
    </div>
  </section>

  <script>
    (function () {
      var dateInput = document.querySelector('input[name="reserved_date"]');
      var timeInput = document.querySelector('input[name="reserved_time"]');
      var durationInput = document.querySelector('input[name="duration_minutes"]');
      var sucursalSelect = document.querySelector('select[name="sucursal_id"]');
      var employeeSelect = document.querySelector('select[name="employee_id"]');
      var saveBtn = document.getElementById('btnSave');
      var availMsg = document.getElementById('availabilityMessage');
      var reservationId = {{ $reservation->id }};
      var apiUrl = "{{ url('api/reservations/check-availability') }}";

      function showAvailability(ok, text, extra) {
        if (!availMsg) return;
        var html = ok ? '<div class="alert alert-success">' + text : '<div class="alert alert-danger">' + text;
        if (extra && extra.until) html += ' <strong>Hasta:</strong> ' + extra.until;
        html += '</div>';
        availMsg.innerHTML = html;
        if (saveBtn) saveBtn.disabled = !ok;
      }

      function checkAvailability() {
        console.log('checkAvailability llamado');

        // Obtener valores (selectpicker puede cambiar el elemento)
        var dateVal = dateInput ? dateInput.value : '';
        var timeVal = timeInput ? timeInput.value : '';
        var sucursalVal = sucursalSelect ? sucursalSelect.value : '';
        var employeeVal = employeeSelect ? employeeSelect.value : '';
        var durationVal = durationInput ? durationInput.value : '30';

        console.log('Valores:', { date: dateVal, time: timeVal, sucursal: sucursalVal, employee: employeeVal });

        if (!dateVal || !timeVal) {
          showAvailability(true, 'Completa fecha y hora para comprobar disponibilidad.');
          return;
        }

        if (!sucursalVal && !employeeVal) {
          showAvailability(true, 'Selecciona una sucursal o empleado para comprobar disponibilidad.');
          return;
        }

        var payload = {
          reserved_date: dateVal,
          reserved_time: timeVal,
          duration_minutes: durationVal ? parseInt(durationVal) : 30,
          exclude_id: reservationId
        };

        if (sucursalVal) payload.sucursal_id = parseInt(sucursalVal);
        if (employeeVal) payload.employee_id = parseInt(employeeVal);

        console.log('Enviando request a:', apiUrl, payload);

        fetch(apiUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify(payload)
        }).then(function (res) {
          console.log('Response status:', res.status);
          return res.json().then(function (body) { return { status: res.status, body: body }; });
        }).then(function (j) {
          console.log('Response body:', j.body);
          if (j.status >= 400) {
            var msg = (j.body && j.body.message) ? j.body.message : 'Error comprobando disponibilidad';
            showAvailability(false, msg);
            return;
          }
          if (j.body && j.body.available) {
            var msg = (j.body && j.body.message) ? j.body.message : 'Horario disponible. Puedes guardar la reserva.';
            showAvailability(true, msg, j.body);
          } else {
            var m = (j.body && j.body.message) ? j.body.message : 'No disponible en ese horario.';
            showAvailability(false, m, j.body);
          }
        }).catch(function (err) {
          console.error('Error en fetch:', err);
          showAvailability(false, 'Error comprobando disponibilidad. Revisa la consola.');
        });
      }

      function checkEmployeeAvailabilitySlots() {
        var dateVal = dateInput ? dateInput.value : '';
        var employeeVal = employeeSelect ? employeeSelect.value : '';
        var durationVal = durationInput ? durationInput.value : '30';
        if (!dateVal || !employeeVal) return;

        var url = '/api/reservations/employee-availability?employee_id=' + encodeURIComponent(employeeVal)
          + '&date=' + encodeURIComponent(dateVal)
          + '&duration_minutes=' + encodeURIComponent(durationVal ? parseInt(durationVal) : 30);

        fetch(url, { method: 'GET', headers: { 'Accept': 'application/json' } })
          .then(function (res) {
            return res.json().then(function (body) { return { status: res.status, body: body }; });
          })
          .then(function (j) {
            if (j.status >= 400) {
              var errMsg = (j.body && (j.body.error || j.body.message)) ? (j.body.error || j.body.message) : 'No se pudo validar disponibilidad del barbero.';
              showAvailability(false, errMsg);
              return;
            }
            if (j.body && j.body.has_available_slots === false) {
              showAvailability(false, j.body.message || 'El barbero no tiene horarios disponibles para la fecha seleccionada.');
            }
          })
          .catch(function (err) {
            console.error('Error en endpoint employee-availability:', err);
          });
      }

      // eventos para disparar la comprobación
      if (dateInput) dateInput.addEventListener('change', checkAvailability);
      if (timeInput) timeInput.addEventListener('change', checkAvailability);
      if (durationInput) durationInput.addEventListener('input', function () { setTimeout(checkAvailability, 200); });
      if (dateInput) dateInput.addEventListener('change', checkEmployeeAvailabilitySlots);
      if (durationInput) durationInput.addEventListener('input', function () { setTimeout(checkEmployeeAvailabilitySlots, 200); });

      // Para selectpicker, usar jQuery para capturar el evento change
      $(document).ready(function () {
        $('select[name="sucursal_id"]').on('changed.bs.select change', checkAvailability);
        $('select[name="employee_id"]').on('changed.bs.select change', function () {
          checkAvailability();
          checkEmployeeAvailabilitySlots();
        });

        // comprobar al cargar
        setTimeout(checkAvailability, 800);
        setTimeout(checkEmployeeAvailabilitySlots, 900);
      });
    })();
  </script>
@endsection