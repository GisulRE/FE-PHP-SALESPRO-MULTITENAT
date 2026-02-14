@forelse(($lista_documentos ?? []) as $documento)
    <option value="{{$documento->codigo_clasificador}}">{{$documento->descripcion}}</option>
@empty
    <option value="">No hay documentos disponibles</option>
@endforelse