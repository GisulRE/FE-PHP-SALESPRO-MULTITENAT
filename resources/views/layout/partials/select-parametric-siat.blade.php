<form action="{{ route('parametric.siat') }}" class="btn siat-sincronizacion">
    <select name="tipo_clasificador" class="selectpicker form-control">
        <option selected>Seleccionar</option>
        <option value="mensajesServicios">MENSAJES_SERVICIOS</option>
        <option value="eventosSignificativos">EVENTOS_SIGNIFICATIVOS</option>
        <option value="motivoAnulacion">EVENTOS_SIGNIFICATIVOS</option>
        <option value="tipoDocumentoIdentidad">DOCUMENT0_IDENTIDAD</option>
        <option value="paisOrigen">PAIS_ORIGEN</option>
        <option value="tipoDocumentoSector">DOCUMENTO_SECTOR</option>
        <option value="tipoEmision">TIPO_EMISION</option>
        <option value="tipoHabitacion">TIPO_HABITACION</option>
    </select>
</form>