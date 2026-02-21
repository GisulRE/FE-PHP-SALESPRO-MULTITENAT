@php
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

if (Schema::hasColumn('siat_parametricas_varios', 'tipo_clasificador')) {
    $lista_monedas = DB::table('siat_parametricas_varios')
        ->where('tipo_clasificador', 'tipoMoneda')
        ->get();
} else {
    $lista_monedas = collect();
}
@endphp

@foreach ($lista_monedas as $moneda)
    <option value="{{ $moneda->codigo_clasificador }}">{{ $moneda->descripcion }}</option>
@endforeach