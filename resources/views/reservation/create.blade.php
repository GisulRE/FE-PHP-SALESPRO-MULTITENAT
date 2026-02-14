@extends('layout.main')
@section('content')
  <section>
    <div class="container-fluid">
      <h3>Crear Reserva</h3>
      <form action="{{ route('reservations.store') }}" method="POST">
        @csrf
        <div class="row">
          <div class="col-md-6 form-group">
            <label>Nombre *</label>
            <input type="text" name="name" required class="form-control">
          </div>
          <div class="col-md-6 form-group">
            <label>Telefono *</label>
            <input type="text" name="phone" required class="form-control">
          </div>
          <div class="col-md-6 form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control">
          </div>
          <div class="col-md-6 form-group">
            <label>Servicio (Producto)</label>
            <select name="product_id" class="form-control selectpicker" data-live-search="true">
              <option value="">-- Seleccionar --</option>
              @foreach($products as $p)
                <option value="{{ $p->id }}">{{ $p->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-6 form-group">
            <label>Sucursal</label>
            <select name="sucursal_id" class="form-control selectpicker" data-live-search="true">
              <option value="">-- Seleccionar --</option>
              @foreach($warehouses as $w)
                <option value="{{ $w->id }}">{{ $w->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-6 form-group">
            <label>Empleado (opcional, asigna uno específico)</label>
            <select name="employee_id" class="form-control selectpicker" data-live-search="true">
              <option value="">-- Seleccionar --</option>
              @if(!empty($employees))
                @foreach($employees as $emp)
                  <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                @endforeach
              @endif
            </select>
          </div>
          <div class="col-md-3 form-group">
            <label>Fecha *</label>
            <input id="reserved_date" type="date" name="reserved_date" required class="form-control">
          </div>
          <div class="col-md-3 form-group">
            <label>Hora *</label>
            <input id="reserved_time" type="time" name="reserved_time" required class="form-control">
          </div>
          <div class="col-md-3 form-group">
            <label>Duración (min)</label>
            <input type="number" name="duration_minutes" class="form-control" min="1">
          </div>
          <div class="col-md-3 form-group">
            <label>Estado</label>
            <select name="status" class="form-control selectpicker">
              <option value="pending">Pendiente</option>
              <option value="confirmed">Confirmada</option>
              <option value="cancelled">Cancelada</option>
              <option value="completed">Completada</option>
            </select>
          </div>
          <div class="col-md-12 form-group">
            <label>Notas</label>
            <textarea name="notes" rows="3" class="form-control"></textarea>
          </div>
          <div class="col-md-12 form-group">
            <button id="btnSave" type="submit" class="btn btn-primary">Guardar</button>
          </div>
          <div class="col-md-12 form-group">
            <div id="availabilityMessage" style="margin-top:10px;"></div>
          </div>
        </div>
      </form>
    </div>
  </section>
  <script>
    // Forzar sólo fechas futuras en el selector y evitar horas pasadas si la fecha es hoy
    (function () {
      var dateInput = document.getElementById('reserved_date');
      var timeInput = document.getElementById('reserved_time');
      if (!dateInput || !timeInput) return;

      function pad(n) { return n < 10 ? '0' + n : n; }

      function setMinDate() {
        var today = new Date();
        var yyyy = today.getFullYear();
        var mm = pad(today.getMonth() + 1);
        var dd = pad(today.getDate());
        dateInput.min = yyyy + '-' + mm + '-' + dd;
      }

      function setMinTimeIfToday() {
        var selected = dateInput.value;
        var now = new Date();
        if (!selected) {
          timeInput.min = '';
          return;
        }
        var sel = new Date(selected + 'T00:00:00');
        if (sel.toDateString() === now.toDateString()) {
          // min time is current time + 5 minutes
          now.setMinutes(now.getMinutes() + 5);
          var hh = pad(now.getHours());
          var min = pad(now.getMinutes());
          timeInput.min = hh + ':' + min;
        } else {
          timeInput.min = '';
        }
      }

      setMinDate();
      dateInput.addEventListener('change', setMinTimeIfToday);
      // also set on load if date prefilled
      setMinTimeIfToday();

      // Validación de disponibilidad en cliente
      var durationInput = document.querySelector('input[name="duration_minutes"]');
      var sucursalSelect = document.querySelector('select[name="sucursal_id"]');
      var employeeSelect = document.querySelector('select[name="employee_id"]');
      var saveBtn = document.getElementById('btnSave');
      var availMsg = document.getElementById('availabilityMessage');

      function showAvailability(ok, text, extra) {
        if (!availMsg) return;
        var html = ok ? '<div class="alert alert-success">' + text : '<div class="alert alert-danger">' + text;
        if (extra && extra.until) html += ' <strong>Hasta:</strong> ' + extra.until;
        html += '</div>';
        availMsg.innerHTML = html;
        if (saveBtn) saveBtn.disabled = !ok;
      }

      function checkAvailability() {
        if (!dateInput.value || !timeInput.value || !sucursalSelect || !sucursalSelect.value) {
          // no hay datos suficientes
          showAvailability(true, 'Completa fecha, hora y sucursal para comprobar disponibilidad.');
          return;
        }
        var payload = {
          reserved_date: dateInput.value,
          reserved_time: timeInput.value,
          duration_minutes: durationInput && durationInput.value ? parseInt(durationInput.value) : 30,
          sucursal_id: sucursalSelect && sucursalSelect.value ? parseInt(sucursalSelect.value) : null
        };
        if (employeeSelect && employeeSelect.value) payload.employee_id = parseInt(employeeSelect.value);


        fetch('/api/reservations/check-availability', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify(payload)
        }).then(function (res) {
          return res.json().then(function (body) { return { status: res.status, body: body }; });
        }).then(function (j) {
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
          console.error(err);
          showAvailability(false, 'Error comprobando disponibilidad. Revisa la consola.');
        });
      }

      // eventos para disparar la comprobación
      if (dateInput) dateInput.addEventListener('change', checkAvailability);
      if (timeInput) timeInput.addEventListener('change', checkAvailability);
      if (durationInput) durationInput.addEventListener('input', function () { setTimeout(checkAvailability, 200); });
      if (sucursalSelect) sucursalSelect.addEventListener('change', checkAvailability);
      if (employeeSelect) employeeSelect.addEventListener('change', checkAvailability);

      // comprobar al cargar si hay valores
      setTimeout(checkAvailability, 500);
    })();
  </script>
@endsection