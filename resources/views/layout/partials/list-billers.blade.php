<option selected>Lista de Facturadores</option>
@foreach ($billers as $item)
    <option value="{{ $item->id }}">
        {{ $item->name }} {{ $item->company_name }} |
        {{ optional($item->almacen)->name }} {{ optional($item->almacen)->sucursal_siat }} |
        {{ optional(optional($item->almacen)->sucursal)->sucursal }} {{ optional(optional($item->almacen)->sucursal)->nombre }}, {{ optional(optional($item->almacen)->sucursal)->descripcion_sucursal }} |
        {{ optional($item->getpuntoventa)->codigo_punto_venta }}-{{ optional($item->getpuntoventa)->nombre_punto_venta }}
    </option>
@endforeach