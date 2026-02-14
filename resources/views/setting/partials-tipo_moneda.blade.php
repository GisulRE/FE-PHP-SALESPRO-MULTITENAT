{{$lista_monedas = DB::table('siat_parametricas_varios')->where('tipo_clasificador', 'tipoMoneda')->get()}}

@foreach ($lista_monedas as $moneda)
    <option value="{{$moneda->codigo_clasificador}}">{{$moneda->descripcion}}</option>
@endforeach