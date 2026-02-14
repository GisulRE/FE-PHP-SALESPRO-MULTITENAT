@extends('layout.main')
@section('content')
  @if (session()->has('create_message'))
    <div class="alert alert-success alert-dismissible text-center">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
          aria-hidden="true">&times;</span></button>{!! session()->get('create_message') !!}
    </div>
  @endif
  @if (session()->has('edit_message'))
    <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert"
        aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('edit_message') }}</div>
  @endif

  <section>

    <div class="container-fluid">
      <div style="display:flex;gap:10px;align-items:center;flex-wrap:nowrap;">
        @if (in_array('reservations-add', $all_permission))
          <a href="{{ route('reservations.create') }}" class="btn btn-info" style="flex:0 0 auto;"><i
              class="dripicons-plus"></i> Nuevo</a>
        @endif

        <div style="display:flex;gap:8px;align-items:center;flex:1 1 auto;min-width:0;">
          <label for="employee-filter" style="margin-right:6px;white-space:nowrap;">Empleado:</label>
          <select id="employee-filter" class="form-control" style="display:inline-block;width:auto;min-width:160px;">
            <option value="">Todos</option>
            @foreach($employees as $emp)
              <option value="{{ $emp->id }}">{{ $emp->name }}</option>
            @endforeach
          </select>

          <label for="status-filter" style="margin-left:6px;margin-right:6px;white-space:nowrap;">Estado:</label>
          <select id="status-filter" class="form-control" style="display:inline-block;width:auto;min-width:160px;">
            <option value="">Todos</option>
            <option value="pending">Pendiente</option>
            <option value="confirmed">Confirmada</option>
            <option value="cancelled">Cancelada</option>
            <option value="completed">Completada</option>
            <option value="expired">Expirada</option>
            <option value="absent">Ausente</option>
          </select>

          <label for="date-filter" style="margin-left:8px;margin-right:6px;white-space:nowrap;">Fecha:</label>
          <select id="date-filter" class="form-control" style="display:inline-block;width:auto;min-width:140px;">
            <option value="">Todos</option>
            <option value="today">Hoy</option>
            <option value="tomorrow">Mañana</option>
            <option value="custom">Personalizada</option>
          </select>
          <input type="date" id="custom-date" class="form-control"
            style="display:none;margin-left:8px;min-width:160px;" />

          <div class="toolbar-buttons-container" style="margin-left:8px;flex:0 0 auto;"></div>
        </div>
      </div>
    </div>
    <div class="table-responsive">
      <table id="reservation-table" class="table table-striped table-hover table-bordered" style="width: 100%;">
        <thead>
          <tr>
            <th class="not-exported"></th>
            <th>Nombre</th>
            <th>Telefono</th>
            <th>Servicio</th>
            <th>Empleado</th>
            <th>Sucursal</th>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Duración (min)</th>
            <th>Estado</th>
            <th class="not-exported">{{ trans('file.action') }}</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>

    <!-- Modal: Confirmar Marcar Asistencia -->
    <div class="modal fade" id="markAttendanceModal" tabindex="-1" role="dialog" aria-labelledby="markAttendanceLabel"
      aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="markAttendanceLabel">Confirmar asistencia</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <p>¿Deseas marcar asistencia para la reserva de <strong id="ma_customer_name"></strong>?</p>
            <p>Servicio: <span id="ma_service_name"></span></p>
            <p>Teléfono: <span id="ma_customer_phone"></span></p>
            <input type="hidden" id="ma_reservation_id" />
            <input type="hidden" id="ma_product_id" />
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primary" id="ma_confirm_btn">Marcar asistencia</button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <script type="text/javascript">
    $(document).ready(function () {
      var all_permission = <?php echo json_encode($all_permission); ?>;

      var table = $('#reservation-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
          url: '{{ url("reservations/list-data") }}',
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          data: function (d) {
            d.all_permission = all_permission;
            var emp = $('#employee-filter').val();
            if (emp) d.employee_id = emp;
            var st = $('#status-filter').val();
            if (st) d.status = st;
            var df = $('#date-filter').val();
            if (df) d.date_filter = df;
            var cd = $('#custom-date').val();
            if (cd) d.custom_date = cd;
          },
          type: 'POST',
          error: function (xhr, error, thrown) {
            console.error('DataTables Ajax error:', xhr.responseText || error || thrown);
            var msg = 'Error cargando reservas. Comprueba la consola para más detalles.';
            if (xhr.status === 419) msg = 'Sesión expirada o token CSRF inválido. Vuelve a iniciar sesión.';
            if (xhr.status === 401) msg = 'No autorizado. Comprueba permisos.';
            alert(msg);
          }
        },
        columns: [
          { data: 'key' },
          { data: 'name' },
          { data: 'phone' },
          { data: 'service' },
          { data: 'employee' },
          { data: 'warehouse' },
          { data: 'reserved_date' },
          { data: 'reserved_time' },
          { data: 'duration' },
          { data: 'status' },
          { data: 'options' }
        ],
        rowCallback: function (row, data) {
          if (data.id) $(row).attr('data-id', data.id);
        },
        'columnDefs': [{
          'render': function (data, type, row, meta) {
            if (type === 'display') {
              data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
            }
            return data;
          },
          'checkboxes': { 'selectRow': true, 'selectAllRender': '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>' },
          'targets': [0]
        }],
        select: { style: 'multi', selector: 'td:first-child' },
        dom: '<"row"lfB>rtip',
        buttons: [
          {
            text: 'Enviar recordatorios de hoy', className: 'buttons-remind btn btn-primary', action: function (e, dt, node, config) {
              var reservation_id = [];
              $('input.dt-checkboxes:checked').each(function () {
                var id = $(this).closest('tr').data('id');
                if (id) reservation_id.push(id);
              });
              if (!reservation_id.length) {
                swal('Mensaje', 'Ninguna Reserva seleccionada', 'info');
                return;
              }
              if (!confirm('¿Enviar recordatorios de hoy a las reservas seleccionadas?')) return;
              $.ajax({
                type: 'POST',
                url: '{{ url("reservations/send-reminders") }}',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: { reservationIdArray: reservation_id },
                success: function (data) {
                  swal('Mensaje', data, 'success');
                },
                error: function (xhr) {
                  console.error(xhr.responseText || xhr.statusText);
                  swal('Error', 'No se pudo enviar recordatorios. Comprueba la consola.', 'error');
                }
              });
            }
          }
        ]
      });

      // Mover los botones de DataTables al contenedor del toolbar para que queden en la misma fila
      try {
        table.buttons().container().appendTo($('.toolbar-buttons-container'));
      } catch (e) {
        console.warn('No se pudieron mover los botones de DataTables al toolbar', e);
      }

      // Mejora en el buscador: ejecutar búsqueda al presionar Enter
      $('#reservation-table_filter input[type="search"]').off('keyup').on('keyup', function (e) {
        if (e.keyCode === 13) {
          table.search(this.value).draw();
        }
      });

      // Recargar tabla automáticamente al cambiar el filtro de empleado
      $('#employee-filter').on('change', function () {
        table.ajax.reload();
      });
      // Recargar tabla al cambiar el filtro de estado
      $('#status-filter').on('change', function () {
        table.ajax.reload();
      });

      // Fecha: mostrar input si se selecciona 'Personalizada' y recargar tabla al cambiar
      $('#date-filter').on('change', function () {
        var v = $(this).val();
        if (v === 'custom') {
          $('#custom-date').show();
        } else {
          $('#custom-date').hide().val('');
        }
        table.ajax.reload();
      });
      $('#custom-date').on('change', function () {
        table.ajax.reload();
      });

      // Ajustes responsivos: asegurar que selects no expandan la fila
      $('#employee-filter, #status-filter').css({ 'width': 'auto', 'min-width': '140px' });

      // Delegated handler: abrir modal de confirmar asistencia
      $(document).on('click', '.mark-attendance', function (e) {
        e.preventDefault();
        var $el = $(this);
        var reservationId = $el.data('reservation-id');
        var productId = $el.data('product-id');
        var custName = $el.data('customer-name') ? decodeURIComponent($el.data('customer-name')) : '';
        var custPhone = $el.data('customer-phone') ? decodeURIComponent($el.data('customer-phone')) : '';
        $('#ma_reservation_id').val(reservationId);
        $('#ma_product_id').val(productId);
        $('#ma_customer_name').text(custName || 'Sin nombre');
        $('#ma_customer_phone').text(custPhone || '-');
        // try to display service name if available in row
        var serviceName = $el.closest('tr').find('td').eq(3).text() || '';
        $('#ma_service_name').text(serviceName.trim());
        $('#markAttendanceModal').modal('show');
      });

      // Confirmar: marcar asistencia por AJAX (sin redirección) y actualizar UI
      $('#ma_confirm_btn').on('click', function () {
        var resId = $('#ma_reservation_id').val();
        var token = $('meta[name="csrf-token"]').attr('content');
        if (!resId) {
          alert('Reserva inválida');
          return;
        }
        $(this).prop('disabled', true).text('Procesando...');
        $.ajax({
          url: '/reservations/' + encodeURIComponent(resId) + '/mark-attendance',
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': token },
          data: {},
          success: function (resp) {
            $('#markAttendanceModal').modal('hide');
            try { swal('Hecho', resp.message || 'Reserva marcada como asistida', 'success'); } catch (e) { alert(resp.message || 'Reservada marcada como asistida'); }
            // Recargar la tabla sin perder la página actual
            try { table.ajax.reload(null, false); } catch (e) { location.reload(); }
          },
          error: function (xhr) {
            console.error('Error marking attendance', xhr.responseText || xhr.statusText);
            var msg = 'No se pudo marcar asistencia. Comprueba la consola.';
            try { swal('Error', msg, 'error'); } catch (e) { alert(msg); }
          },
          complete: function () {
            $('#ma_confirm_btn').prop('disabled', false).text('Marcar asistencia');
          }
        });
      });
    });
  </script>
@endsection