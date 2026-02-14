<!-- Modal -->
<div class="modal fade" id="modalBusquedaNit" tabindex="-1" data-backdrop="static" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background-color: darkgray !important; margin-left: 10px; margin-right: 10px;">
            <div class="modal-header">
                <h5 class="modal-title text-white" id="exampleModalLabel">Lista de documentos similares </h5>
            </div>
            <div class="modal-body">
                {{-- Insertando datos en forma de tabla --}}
                <div id="tabla_busqueda_modal">
                    <table class="table table-sm table-striped table-dark">
                        <thead class="thead-dark">
                            <tr>
                                <th>Razón Social</th>
                                <th>Correo Electrónico</th>
                                <th>Valor Documento</th>
                                <th>Complemento</th>
                                <th>Tipo Doc</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- llenado de la tabla por ajax --}}
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-cerrar-tabla-busqueda-nit btn btn-light">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>

    $('.btn-cerrar-tabla-busqueda-nit').on('click', function(){
        if ($('select[name=sales_tipo_documento]').val() == 1 ) {
            $('#sales_complemento').show();
        }
        $('#modalBusquedaNit').modal('hide');
    });
</script>