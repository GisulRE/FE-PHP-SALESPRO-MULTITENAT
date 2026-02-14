<option  selected>Lista de Facturadores</option>
@foreach ($billers as $item)
    <option value="{{ $item->id }}">
        {{ $item->name }} {{$item->company_name}} |
        {{ $item->almacen->name }} {{$item->almacen->sucursal_siat }} |
        {{ $item->almacen->sucursal->sucursal }} {{ $item->almacen->sucursal->nombre }}, {{
        $item->almacen->sucursal->descripcion_sucursal }} |
        {{ $item->getpuntoventa->codigo_punto_venta }}-{{ $item->getpuntoventa->nombre_punto_venta }}
    </option>
@endforeach