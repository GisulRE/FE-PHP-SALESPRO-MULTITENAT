{{-- Modal de búsqueda para selects con muchas opciones --}}
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalId }}Label">{{ $title ?? 'Buscar y Seleccionar' }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <input type="text" 
                           class="form-control search-input" 
                           id="{{ $modalId }}_search" 
                           placeholder="{{ $searchPlaceholder ?? 'Buscar...' }}"
                           autocomplete="off">
                </div>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover table-sm">
                        <thead class="thead-light" style="position: sticky; top: 0; z-index: 1;">
                            <tr>
                                @if(isset($columns) && is_array($columns))
                                    @foreach($columns as $column)
                                        <th>{{ $column }}</th>
                                    @endforeach
                                @else
                                    <th>Código</th>
                                    <th>Descripción</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody id="{{ $modalId }}_results">
                            @if(isset($items) && count($items) > 0)
                                @foreach($items as $item)
                                    <tr class="selectable-row" 
                                        data-id="{{ $item['id'] ?? $item->id }}" 
                                        data-text="{{ $item['text'] ?? $item->name ?? '' }}"
                                        style="cursor: pointer;">
                                        @if(isset($renderRow))
                                            {!! $renderRow($item) !!}
                                        @else
                                            <td>{{ $item['code'] ?? $item->code ?? $item['id'] ?? $item->id }}</td>
                                            <td>{{ $item['text'] ?? $item->name ?? $item['description'] ?? '' }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="{{ isset($columns) ? count($columns) : 2 }}" class="text-center text-muted">
                                        No hay datos disponibles
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">
                    <small class="text-muted">
                        <span id="{{ $modalId }}_count">{{ isset($items) ? count($items) : 0 }}</span> resultado(s) encontrado(s)
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<style>
#{{ $modalId }} .selectable-row:hover {
    background-color: #e3f2fd;
}
#{{ $modalId }} .selectable-row.selected {
    background-color: #bbdefb;
    font-weight: 500;
}
#{{ $modalId }} .table-responsive {
    border: 1px solid #dee2e6;
    border-radius: 4px;
}
</style>

<script>
$(document).ready(function() {
    const modalId = '{{ $modalId }}';
    const targetSelectId = '{{ $targetSelectId ?? '' }}';
    const targetInputId = '{{ $targetInputId ?? '' }}';
    const allRows = [];
    
    // Guardar todas las filas originales
    $('#' + modalId + '_results tr.selectable-row').each(function() {
        allRows.push({
            element: $(this),
            text: $(this).text().toLowerCase(),
            id: $(this).data('id'),
            displayText: $(this).data('text')
        });
    });
    
    // Búsqueda en tiempo real
    $('#' + modalId + '_search').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        let visibleCount = 0;
        
        allRows.forEach(function(row) {
            if (searchTerm === '' || row.text.indexOf(searchTerm) !== -1) {
                row.element.show();
                visibleCount++;
            } else {
                row.element.hide();
            }
        });
        
        $('#' + modalId + '_count').text(visibleCount);
    });
    
    // Selección de fila
    $('#' + modalId + '_results').on('click', 'tr.selectable-row', function() {
        const selectedId = $(this).data('id');
        const selectedText = $(this).data('text');
        
        // Remover selección previa
        $('#' + modalId + '_results tr.selectable-row').removeClass('selected');
        $(this).addClass('selected');
        
        // Actualizar el select o input destino
        if (targetSelectId) {
            $('#' + targetSelectId).val(selectedId);
            if ($('#' + targetSelectId).hasClass('selectpicker')) {
                $('#' + targetSelectId).selectpicker('refresh');
            }
            $('#' + targetSelectId).trigger('change');
        }
        
        if (targetInputId) {
            $('#' + targetInputId).val(selectedText);
        }
        
        // Cerrar modal
        $('#' + modalId).modal('hide');
    });
    
    // Limpiar búsqueda al abrir modal
    $('#' + modalId).on('show.bs.modal', function() {
        $('#' + modalId + '_search').val('').focus();
        $('#' + modalId + '_results tr.selectable-row').show();
        $('#' + modalId + '_count').text(allRows.length);
        
        // Marcar el item actualmente seleccionado
        if (targetSelectId) {
            const currentValue = $('#' + targetSelectId).val();
            $('#' + modalId + '_results tr.selectable-row').removeClass('selected');
            $('#' + modalId + '_results tr.selectable-row[data-id="' + currentValue + '"]').addClass('selected');
        }
    });
    
    // Focus en input de búsqueda al abrir
    $('#' + modalId).on('shown.bs.modal', function() {
        $('#' + modalId + '_search').focus();
    });
});
</script>
