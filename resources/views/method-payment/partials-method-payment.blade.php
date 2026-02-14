@foreach ($lista_parametros_siat as $tipo_metodo)
    <option value="{{$tipo_metodo->codigo_clasificador}}">{{$tipo_metodo->descripcion}}</option>
@endforeach