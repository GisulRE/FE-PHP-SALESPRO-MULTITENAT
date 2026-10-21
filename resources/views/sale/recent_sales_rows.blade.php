@foreach ($recent_sale as $sale)
    @php
        $customer = isset($customers) && isset($customers[$sale->customer_id]) 
            ? $customers[$sale->customer_id] 
            : \DB::table('customers')->find($sale->customer_id);
        $venta_facturada = isset($customerSales) && isset($customerSales[$sale->id]) 
            ? $customerSales[$sale->id] 
            : \DB::table('customer_sales')->where('sale_id', $sale->id)->first();
        $nota_credito = \DB::table('customer_sales')
            ->where('sale_id', $sale->id)
            ->where('codigo_documento_sector', 24)
            ->latest('id')
            ->first();
    @endphp
    <tr>
        <td>{{ date('d-m-Y H:i', strtotime($sale->created_at)) }}</td>
        @if (!empty($venta_facturada) && $venta_facturada->estado_factura != null)
            <td>
                @if ($venta_facturada->nro_factura != null)
                    {{ $sale->reference_no . ' [FACT#' . $venta_facturada->nro_factura . '|' . $venta_facturada->estado_factura . ']' }}
                @else
                    {{-- la factura es manual --}}
                    {{ $sale->reference_no . ' [FACT-' }} <div class="badge badge-info">Manual</div> {{ '#' . $venta_facturada->nro_factura_manual . '|' . $venta_facturada->estado_factura . ']' }}
                @endif
            </td>
            <td>{{ $venta_facturada->razon_social . ($venta_facturada->email ? ' - ' . $venta_facturada->email : '') }}</td>
            <td>{{ number_format((float)$sale->grand_total, 2, '.', '') }}</td>
            <td>
                <div class="btn-group">
                    @if ($venta_facturada->estado_factura != 'ANULADO')
                        <button type="button"
                            class="imprimir-factura-modal btn btn-info btn-sm mr-1"
                            data-id="{{ $sale->id }}"
                            data-toggle="modal"
                            data-target="#imprimir-factura-modal"
                            title="Imprimir Factura">
                            <i class="fa fa-print"></i>
                        </button>
                        <button type="button"
                            class="emitir-nota-credito-debito-btn btn btn-warning btn-sm"
                            data-id="{{ $sale->id }}"
                            title="Emitir Nota de Crédito/Débito">
                            <i class="fa fa-undo"></i>
                        </button>
                        @if (!empty($nota_credito))
                            <button type="button"
                                class="imprimir-ncd-btn btn btn-dark btn-sm ml-1"
                                data-id="{{ $nota_credito->id }}"
                                data-nro="{{ $nota_credito->nro_factura }}"
                                data-cuf="{{ $nota_credito->cuf }}"
                                data-monto="{{ $nota_credito->monto_total ?? $nota_credito->monto_total_sujeto_iva ?? 0 }}"
                                title="Ver / Imprimir Nota Crédito-Débito SIAT #{{ $nota_credito->nro_factura }}">
                                <i class="fa fa-file-pdf-o"></i>
                            </button>
                        @endif
                    @endif
                </div>
            </td>
        @else
            <td>{{ $sale->reference_no }}</td>
            <td>{{ $customer->name ?? '' }}</td>
            <td>{{ number_format((float)$sale->grand_total, 2, '.', '') }}</td>
            <td>
                <div class="btn-group">
                    @if (isset($all_permission) && in_array('sales-edit', $all_permission))
                        <a href="{{ route('sales.edit', $sale->id) }}"
                            class="btn btn-success btn-sm"
                            title="Edit"><i class="dripicons-document-edit"></i></a>&nbsp;
                    @endif
                    @if (isset($all_permission) && in_array('sales-delete', $all_permission))
                        {{ Form::open(['route' => ['sales.destroy', $sale->id], 'method' => 'DELETE', 'style' => 'display:inline']) }}
                        <button type="submit"
                            class="btn btn-danger btn-sm"
                            onclick="return confirmDelete()"
                            title="Delete"><i class="dripicons-trash"></i></button>
                        {{ Form::close() }}
                    @endif
                </div>
            </td>
        @endif
    </tr>
@endforeach
