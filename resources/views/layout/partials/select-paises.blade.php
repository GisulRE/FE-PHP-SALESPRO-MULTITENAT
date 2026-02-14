<select name="paisOrigen" class="selectpicker form-control">
    <option selected>Seleccionar</option>
    @foreach ($paises as $pais)
        <option value="pais_origen">{{ $pais->codigo_clasificador }} | {{ $pais->descripcion }}</option> 
    @endforeach
</select>