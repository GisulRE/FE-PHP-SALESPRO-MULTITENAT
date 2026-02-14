{{-- Vista parcial para mostrar la factura SIAT dentro del modal --}}
<div style="width:100%; min-height:500px;">
    <div class="text-center mb-3">
        <h4>Factura Generada Exitosamente</h4>
    </div>
    
    <div id="invoice-content" style="border: 1px solid #ddd; padding: 10px; background: #fff;">
        @if(isset($data['bytes']) && !empty($data['bytes']))
            <object style="width:100%; height:600px;">
                <embed id="pdfPreview" type="application/pdf" width="100%" height="600" 
                       src="data:application/pdf;base64,{{$data['bytes']}}" />
            </object>
        @else
            <div class="alert alert-warning">
                No se pudo cargar la vista previa del PDF. 
                <a href="{{ url('sales/imprimir_factura/' . $venta_id) }}" 
                   target="_blank">Abrir factura completa</a>
            </div>
        @endif
    </div>
</div>

<script>
function printInvoice() {
    var printWindow = window.open('{{ url("sales/imprimir_factura/" . $venta_id) }}', '_blank');
    printWindow.onload = function() {
        printWindow.print();
    };
}
</script>
