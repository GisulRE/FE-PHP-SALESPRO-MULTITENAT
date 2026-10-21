<!-- Modal Wizard: Nota de Crédito-Débito SIAT (Documento Sector 24) -->
<div id="modal-nota-credito-debito" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="ncdModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 95vw !important; width: 1180px !important; margin: 1.5rem auto !important;">
        <div class="modal-content border-0 shadow-lg">
            
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white py-3">
                <div class="d-flex align-items-center">
                    <i class="fa fa-undo fa-2x mr-3"></i>
                    <div>
                        <h5 class="modal-title font-weight-bold mb-0" id="ncdModalLabel">
                            Emitir Nota de Crédito-Débito
                        </h5>
                        <small class="text-white-50">Documento Sector 24 - Servicio de Impuestos Nacionales (SIAT)</small>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Stepper Progress Bar -->
            <div class="px-4 pt-3 pb-2 bg-light border-bottom">
                <div class="d-flex justify-content-between text-center ncd-stepper">
                    <div class="step-item active" id="ncd-step-indicator-1">
                        <span class="step-badge">1</span>
                        <span class="step-label d-none d-md-inline">Factura Original</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-item" id="ncd-step-indicator-2">
                        <span class="step-badge">2</span>
                        <span class="step-label d-none d-md-inline">Selección de Ítems</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-item" id="ncd-step-indicator-3">
                        <span class="step-badge">3</span>
                        <span class="step-label d-none d-md-inline">Liquidación & Pago</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-item" id="ncd-step-indicator-4">
                        <span class="step-badge">4</span>
                        <span class="step-label d-none d-md-inline">Emisión & Ticket</span>
                    </div>
                </div>
            </div>

            <!-- Modal Body with Steps -->
            <div class="modal-body p-4 position-relative" style="min-height: 400px;">
                
                <!-- Loading Spinner Overlay -->
                <div id="ncd-loading" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary" style="width: 3.5rem; height: 3.5rem;" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <h5 class="mt-3 font-weight-bold" id="ncd-loading-msg">Consultando información fiscal...</h5>
                    <p class="text-muted" id="ncd-loading-submsg">Por favor espere un momento.</p>
                </div>

                <!-- Alert Messages Container -->
                <div id="ncd-alert-container"></div>

                <!-- ================= STEP 1: Factura Original & Validación ================= -->
                <div id="ncd-step-1" class="ncd-step-panel">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card shadow-sm border mb-3">
                                <div class="card-header bg-light font-weight-bold d-flex justify-content-between align-items-center">
                                    <span><i class="fa fa-file-text-o mr-1"></i> Datos de la Factura Objeto de Ajuste</span>
                                    <span id="ncd-inv-status-badge" class="badge badge-success px-2 py-1">VIGENTE</span>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <span class="text-muted d-block small">N° Factura:</span>
                                            <strong class="h5 mb-0" id="ncd-inv-nro">-</strong>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <span class="text-muted d-block small">Fecha de Emisión:</span>
                                            <strong id="ncd-inv-fecha">-</strong>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <span class="text-muted d-block small">Razón Social:</span>
                                            <strong id="ncd-inv-razon">-</strong>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <span class="text-muted d-block small">NIT / Documento:</span>
                                            <strong id="ncd-inv-nit">-</strong>
                                        </div>
                                        <div class="col-md-12 mb-2">
                                            <span class="text-muted d-block small">Código de Autorización (CUF):</span>
                                            <code class="text-break small bg-light p-1 border rounded d-block" id="ncd-inv-cuf">-</code>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <!-- Validación Normativa SIAT Card -->
                            <div class="card shadow-sm border mb-3">
                                <div class="card-header bg-light font-weight-bold">
                                    <i class="fa fa-shield mr-1"></i> Validación Normativa SIAT
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="small font-weight-bold">Límite Normativo:</span>
                                            <span id="ncd-valid-plazo-badge" class="badge badge-success">OK (18 Meses)</span>
                                        </div>
                                        <div class="small text-muted" id="ncd-valid-plazo-desc">
                                            Antigüedad: 0 días.
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="small font-weight-bold">Estado Fiscal:</span>
                                            <span id="ncd-valid-estado-badge" class="badge badge-success">VIGENTE</span>
                                        </div>
                                        <div class="small text-muted">Apta para ajuste tributario Sector 24.</div>
                                    </div>

                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="small font-weight-bold">Tipo de Ajuste:</span>
                                            <span id="ncd-valid-tipo-badge" class="badge badge-info">Fiscal Estándar</span>
                                        </div>
                                        <div class="small text-muted" id="ncd-valid-tipo-desc">Ajuste IVA Débito/Crédito 13%.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border bg-light">
                        <div class="card-body py-3">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <div class="small text-muted">Monto Total Factura</div>
                                    <div class="h5 font-weight-bold text-dark mb-0" id="ncd-inv-monto-total">Bs. 0.00</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="small text-muted">Monto Sujeto a IVA</div>
                                    <div class="h5 font-weight-bold text-primary mb-0" id="ncd-inv-monto-sujeto">Bs. 0.00</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="small text-muted">Monto Tarjeta Regalo (Gift Card)</div>
                                    <div class="h5 font-weight-bold text-secondary mb-0" id="ncd-inv-monto-gc">Bs. 0.00</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= STEP 2: Selección de Ítems a Devolver ================= -->
                <div id="ncd-step-2" class="ncd-step-panel" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="font-weight-bold mb-0">
                            <i class="fa fa-cubes mr-1"></i> Seleccione los ítems y cantidades a devolver:
                        </h6>
                        <div>
                            <button type="button" class="btn btn-outline-success btn-sm mr-2" id="ncd-btn-select-all">
                                <i class="fa fa-check-square-o mr-1"></i> Devolución Total
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="ncd-btn-deselect-all">
                                <i class="fa fa-square-o mr-1"></i> Deseleccionar Todo
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive border rounded mb-3" style="max-height: 380px;">
                        <table class="table table-hover table-striped mb-0" id="ncd-items-table">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 40px;" class="text-center">#</th>
                                    <th>Producto / Detalle</th>
                                    <th class="text-center" style="width: 100px;">Cant. Original</th>
                                    <th class="text-center" style="width: 90px;">Ya Devuelto</th>
                                    <th class="text-center" style="width: 90px;">Disponible</th>
                                    <th class="text-center" style="width: 120px;">Cant. a Devolver</th>
                                    <th class="text-right" style="width: 100px;">P/U (Bs)</th>
                                    <th class="text-right" style="width: 110px;">Desc. Prop. (Bs)</th>
                                    <th class="text-right" style="width: 110px;">Subtotal (Bs)</th>
                                </tr>
                            </thead>
                            <tbody id="ncd-items-tbody">
                                <!-- Dynamic Rows -->
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info py-2 small mb-0">
                        <i class="fa fa-info-circle mr-1"></i>
                        El descuento se calcula de forma estrictamente proporcional al descuento emitido en la factura original según normativa SIAT.
                    </div>
                </div>

                <!-- ================= STEP 3: Liquidación Financiera & Configuración ================= -->
                <div id="ncd-step-3" class="ncd-step-panel" style="display: none;">
                    <div class="row">
                        <!-- Liquidación SIAT Card -->
                        <div class="col-lg-6 mb-3">
                            <div class="card shadow-sm border h-100">
                                <div class="card-header bg-light font-weight-bold">
                                    <i class="fa fa-calculator mr-1"></i> Liquidación Fiscal SIAT (Sector 24)
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted">Monto Total Devuelto (Bruto):</td>
                                            <td class="text-right font-weight-bold h6 mb-0" id="ncd-fin-total-devuelto">Bs. 0.00</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">(-) Descuento Proporcional Crédito/Débito:</td>
                                            <td class="text-right font-weight-bold text-danger h6 mb-0" id="ncd-fin-descuento">Bs. 0.00</td>
                                        </tr>
                                        <tr class="border-top">
                                            <td class="font-weight-bold">Monto Efectivo Crédito/Débito (Base Imponible):</td>
                                            <td class="text-right font-weight-bold text-primary h5 mb-0" id="ncd-fin-monto-efectivo">Bs. 0.00</td>
                                        </tr>
                                        <tr class="bg-light">
                                            <td class="font-weight-bold">Crédito Fiscal IVA (13%):</td>
                                            <td class="text-right font-weight-bold text-success h5 mb-0" id="ncd-fin-iva">Bs. 0.00</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Opciones de Devolución & Reembolso -->
                        <div class="col-lg-6 mb-3">
                            <div class="card shadow-sm border h-100">
                                <div class="card-header bg-light font-weight-bold">
                                    <i class="fa fa-money mr-1"></i> Configuración de Devolución
                                </div>
                                <div class="card-body">
                                    <!-- Reingreso a inventario -->
                                    <div class="form-group mb-3">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="ncd-adjust-inventory" checked>
                                            <label class="custom-control-label font-weight-bold" for="ncd-adjust-inventory">
                                                Reingresar productos devueltos al almacén/inventario
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">Aumentará el stock disponible en el almacén de la venta.</small>
                                    </div>

                                    <!-- Método de reembolso -->
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold small text-muted">MÉTODO DE REEMBOLSO AL CLIENTE *</label>
                                        <select class="form-control" id="ncd-refund-method">
                                            <option value="cash" selected>💵 Efectivo (Egreso en Caja Activa)</option>
                                            <option value="customer_balance">💳 Abono a Saldo del Cliente (Depósito)</option>
                                            <option value="gift_card" id="ncd-refund-opt-gc" style="display: none;">🎁 Restituir Saldo en Tarjeta de Regalo</option>
                                        </select>
                                    </div>

                                    <!-- Observaciones -->
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold small text-muted">MOTIVO / OBSERVACIONES</label>
                                        <textarea class="form-control form-control-sm" id="ncd-staff-note" rows="2" placeholder="Motivo de la devolución (opcional)..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= STEP 4: Confirmación & Emisión SIAT ================= -->
                <div id="ncd-step-4" class="ncd-step-panel" style="display: none;">
                    
                    <!-- Confirmation Box before emission -->
                    <div id="ncd-pre-emit-box">
                        <div class="alert alert-warning border mb-3">
                            <h6 class="font-weight-bold mb-1"><i class="fa fa-exclamation-triangle mr-1"></i> Confirmación Final antes de Emitir</h6>
                            <p class="mb-0 small">
                                Al confirmar, se generará el documento tributario oficial <strong>Nota de Crédito-Débito (Sector 24)</strong> y se transmitirá en tiempo real al <strong>Servicio de Impuestos Nacionales (SIAT)</strong>. Este proceso registrará el crédito/débito fiscal correspondiente.
                            </p>
                        </div>

                        <div class="card shadow-sm border mb-4">
                            <div class="card-body">
                                <h6 class="font-weight-bold border-bottom pb-2 mb-3">Resumen de la Transacción:</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <span class="text-muted small">Cliente:</span>
                                        <div class="font-weight-bold" id="ncd-summary-client">-</div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <span class="text-muted small">Factura Objeto de Ajuste:</span>
                                        <div class="font-weight-bold" id="ncd-summary-inv">-</div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <span class="text-muted small">Cantidad de Ítems a Devolver:</span>
                                        <div class="font-weight-bold" id="ncd-summary-items-count">0</div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <span class="text-muted small">Método de Reembolso:</span>
                                        <div class="font-weight-bold" id="ncd-summary-refund-method">-</div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <span class="text-muted small">Monto Efectivo a Devolver:</span>
                                        <div class="font-weight-bold text-primary h5 mb-0" id="ncd-summary-monto">Bs. 0.00</div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <span class="text-muted small">Crédito Fiscal IVA (13%):</span>
                                        <div class="font-weight-bold text-success h5 mb-0" id="ncd-summary-iva">Bs. 0.00</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="button" class="btn btn-success btn-lg px-5 font-weight-bold shadow" id="ncd-btn-confirm-emit">
                                <i class="fa fa-cloud-upload mr-2"></i> Confirmar y Emitir al SIAT
                            </button>
                        </div>
                    </div>

                    <!-- Success Screen after emission (con Visor PDF Oficial SIAT) -->
                    <div id="ncd-success-box" style="display: none;">
                        <div class="alert alert-success d-flex align-items-center justify-content-between p-3 mb-3 shadow-sm rounded">
                            <div class="d-flex align-items-center">
                                <i class="fa fa-check-circle fa-2x mr-3 text-success"></i>
                                <div>
                                    <h5 class="font-weight-bold mb-0 text-success">¡Nota de Crédito-Débito Emitida con Éxito!</h5>
                                    <small class="text-muted">Documento Sector 24 validado y registrado en el SIN (SIAT).</small>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="badge badge-primary px-3 py-2 mr-1">
                                    Nota N°: <strong id="ncd-succ-nro">-</strong>
                                </span>
                                <span class="badge badge-success px-3 py-2">
                                    Monto Devuelto: <strong id="ncd-succ-monto">-</strong>
                                </span>
                            </div>
                        </div>

                        <!-- Gift Card Aviso (si aplica) -->
                        <div id="ncd-succ-giftcard-notice" class="alert alert-warning mb-3" style="display: none;">
                            <i class="fa fa-info-circle mr-2"></i>
                            <strong>Devolución 100% Tarjeta de Regalo:</strong> El saldo fue restituido a la tarjeta del cliente. Al no haber base imponible sujeta a IVA original, no requiere emisión de documento fiscal Sector 24 ante el SIAT.
                        </div>

                        <!-- CUF pill -->
                        <div class="mb-3 px-3 py-2 bg-light border rounded small d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted font-weight-bold mr-2">CUF:</span>
                                <code id="ncd-succ-cuf" class="text-break text-dark select-all">-</code>
                            </div>
                        </div>

                        <!-- PDF Viewer Embed (igual a return-sale) -->
                        <div class="card border mb-3 shadow-sm" style="background-color: #525659;">
                            <div class="card-header bg-dark text-white py-2 d-flex justify-content-between align-items-center">
                                <span class="small font-weight-bold"><i class="fa fa-file-pdf-o text-danger mr-1"></i> Documento Oficial SIAT (SIN)</span>
                                <div>
                                    <button type="button" class="btn btn-sm btn-light py-0 px-2 mr-1" id="ncd-btn-print-direct" title="Imprimir PDF">
                                        <i class="fa fa-print"></i> Imprimir
                                    </button>
                                    <a href="#" target="_blank" class="btn btn-sm btn-outline-light py-0 px-2" id="ncd-btn-open-tab" title="Abrir en pestaña nueva">
                                        <i class="fa fa-external-link"></i> Abrir
                                    </a>
                                </div>
                            </div>
                            <div class="card-body p-0 position-relative" style="height: 520px; min-height: 520px;">
                                <div id="ncd-pdf-loading" class="position-absolute w-100 h-100 d-flex flex-column justify-content-center align-items-center bg-dark text-white" style="z-index: 5; opacity: 0.9; display: none;">
                                    <div class="spinner-border text-light mb-2" role="status"></div>
                                    <span>Cargando documento PDF oficial SIAT...</span>
                                </div>
                                <object id="ncd-pdf-object" data="" type="application/pdf" width="100%" height="100%">
                                    <embed id="ncd-pdf-embed" type="application/pdf" width="100%" height="100%" src="" />
                                    <div class="p-4 text-center text-white">
                                        <p>No se pudo cargar la vista previa directa del PDF.</p>
                                        <a href="#" target="_blank" class="btn btn-primary" id="ncd-pdf-fallback-btn">
                                            <i class="fa fa-download mr-1"></i> Abrir o Descargar PDF
                                        </a>
                                    </div>
                                </object>
                            </div>
                        </div>

                        <!-- Bottom action buttons -->
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                            <div>
                                <button type="button" class="btn btn-primary font-weight-bold mr-2" id="ncd-btn-print-80mm">
                                    <i class="fa fa-print mr-1"></i> Imprimir Ticket 80mm
                                </button>
                                <a href="#" target="_blank" class="btn btn-outline-secondary font-weight-bold" id="ncd-btn-download-pdf">
                                    <i class="fa fa-file-pdf-o mr-1"></i> Descargar PDF
                                </a>
                            </div>
                            <button type="button" class="btn btn-secondary font-weight-bold px-4" data-dismiss="modal">
                                Cerrar
                            </button>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Modal Footer Navigation -->
            <div class="modal-footer bg-light border-top justify-content-between py-2" id="ncd-modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" id="ncd-btn-cancel">
                    Cancelar
                </button>
                <div>
                    <button type="button" class="btn btn-outline-secondary mr-2" id="ncd-btn-prev" style="display: none;">
                        <i class="fa fa-arrow-left mr-1"></i> Anterior
                    </button>
                    <button type="button" class="btn btn-primary px-4" id="ncd-btn-next">
                        Siguiente <i class="fa fa-arrow-right ml-1"></i>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
/* Ancho amplio responsivo para Bootstrap 4.0.0 */
#modal-nota-credito-debito .modal-dialog {
    max-width: 95vw !important;
    width: 1180px !important;
    margin: 1.5rem auto !important;
}

#modal-nota-credito-debito .modal-content {
    border-radius: 10px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.45);
    overflow: hidden;
}

#modal-nota-credito-debito .table-responsive {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch;
    width: 100% !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
}

#modal-nota-credito-debito table#ncd-items-table {
    width: 100% !important;
    min-width: 860px;
    margin-bottom: 0;
    table-layout: auto;
}

#modal-nota-credito-debito table#ncd-items-table th,
#modal-nota-credito-debito table#ncd-items-table td {
    vertical-align: middle !important;
    padding: 0.65rem 0.5rem !important;
    font-size: 0.88rem;
    white-space: nowrap;
}

#modal-nota-credito-debito table#ncd-items-table th:nth-child(2),
#modal-nota-credito-debito table#ncd-items-table td:nth-child(2) {
    white-space: normal !important;
    min-width: 200px;
    max-width: 300px;
}

#modal-nota-credito-debito .ncd-item-qty {
    max-width: 85px !important;
    margin: 0 auto;
    font-weight: 700;
    display: inline-block;
}

.ncd-stepper {
    position: relative;
}
.ncd-stepper .step-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 2;
    flex: 1;
}
.ncd-stepper .step-badge {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background-color: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 13px;
    margin-bottom: 4px;
    transition: all 0.25s ease;
}
.ncd-stepper .step-item.active .step-badge {
    background-color: #007bff;
    color: #fff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
}
.ncd-stepper .step-item.completed .step-badge {
    background-color: #28a745;
    color: #fff;
}
.ncd-stepper .step-label {
    font-size: 11.5px;
    font-weight: 600;
    color: #6c757d;
}
.ncd-stepper .step-item.active .step-label {
    color: #007bff;
}
.ncd-stepper .step-line {
    flex-grow: 1;
    height: 3px;
    background-color: #e9ecef;
    margin-top: 14px;
    position: relative;
    z-index: 1;
}
</style>

<script type="text/javascript">
(function() {
    var currentStep = 1;
    var saleData = null;
    var itemsList = [];
    var calculatedFinancials = {
        montoTotalDevuelto: 0,
        montoDescuento: 0,
        montoEfectivo: 0,
        montoIva: 0
    };
    var emittedResult = null;

    window.NotaCreditoDebitoWizard = {
        open: function(saleId) {
            currentStep = 1;
            saleData = null;
            itemsList = [];
            emittedResult = null;

            // Cerrar modales previos (POS transacción reciente o detalles de venta)
            try { $('#recentTransaction').modal('hide'); } catch(e) {}
            try { $('#sale-details').modal('hide'); } catch(e) {}

            $('#ncd-alert-container').empty();
            $('#ncd-pre-emit-box').show();
            $('#ncd-success-box').hide();
            $('#ncd-pdf-embed').attr('src', '');
            $('#ncd-pdf-object').attr('data', '');
            $('#ncd-pdf-loading').hide();
            $('#ncd-modal-footer').show();
            $('#modal-nota-credito-debito').modal('show');

            this.showLoading(true, 'Verificando Factura Original SIAT...');
            var self = this;

            $.ajax({
                url: "{{ url('nota-credito-debito/check-sale') }}/" + saleId,
                type: "GET",
                dataType: "json",
                success: function(resp) {
                    self.showLoading(false);
                    if (!resp.status) {
                        self.showAlert('danger', resp.message || 'No se puede procesar la Nota de Crédito-Débito.');
                        $('#ncd-btn-next').prop('disabled', true);
                        return;
                    }

                    saleData = resp;
                    itemsList = resp.items || [];
                    self.populateStep1(resp);
                    self.populateStep2(resp.items);
                    self.updateStepper(1);
                },
                error: function(xhr) {
                    self.showLoading(false);
                    var msg = 'Error al consultar la venta.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    self.showAlert('danger', msg);
                    $('#ncd-btn-next').prop('disabled', true);
                }
            });
        },

        showLoading: function(show, msg, submsg) {
            if (show) {
                $('#ncd-loading-msg').text(msg || 'Cargando...');
                $('#ncd-loading-submsg').text(submsg || 'Por favor espere un momento.');
                $('#ncd-loading').show();
                $('.ncd-step-panel').hide();
                $('#ncd-modal-footer button').prop('disabled', true);
            } else {
                $('#ncd-loading').hide();
                $('#ncd-modal-footer button').prop('disabled', false);
                $('#ncd-step-' + currentStep).show();
            }
        },

        showAlert: function(type, text) {
            var icon = type === 'danger' ? 'fa-times-circle' : (type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle');
            var html = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                       '<i class="fa ' + icon + ' mr-2"></i>' + text +
                       '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                       '<span aria-hidden="true">&times;</span></button></div>';
            $('#ncd-alert-container').html(html);
        },

        populateStep1: function(data) {
            var inv = data.invoice;
            $('#ncd-inv-nro').text(inv.nro_factura || '-');
            $('#ncd-inv-fecha').text(inv.fecha_emision || '-');
            $('#ncd-inv-razon').text(inv.razon_social || '-');
            $('#ncd-inv-nit').text((inv.nit_ci || '') + (inv.complemento ? ' - ' + inv.complemento : ''));
            $('#ncd-inv-cuf').text(inv.cuf || '-');
            $('#ncd-inv-status-badge').text(inv.estado_factura || 'VIGENTE');

            $('#ncd-inv-monto-total').text('Bs. ' + (parseFloat(inv.monto_total) || 0).toFixed(2));
            $('#ncd-inv-monto-sujeto').text('Bs. ' + (parseFloat(inv.monto_sujeto_iva) || 0).toFixed(2));
            $('#ncd-inv-monto-gc').text('Bs. ' + (parseFloat(inv.monto_gift_card) || 0).toFixed(2));

            // Desglose de plazo
            $('#ncd-valid-plazo-desc').text('Antigüedad: ' + inv.meses_transcurridos + ' meses (' + inv.dias_transcurridos + ' días).');
            if (inv.is_within_18_months) {
                $('#ncd-valid-plazo-badge').removeClass('badge-danger').addClass('badge-success').text('OK (18 Meses)');
            } else {
                $('#ncd-valid-plazo-badge').removeClass('badge-success').addClass('badge-danger').text('Expirado');
            }

            // Tipo de ajuste
            if (data.is_gift_card_only) {
                $('#ncd-valid-tipo-badge').removeClass('badge-info').addClass('badge-warning').text('100% Gift Card');
                $('#ncd-valid-tipo-desc').text('Sin IVA fiscal. Se restituye saldo en tarjeta de regalo.');
                $('#ncd-refund-opt-gc').show().prop('selected', true);
            } else if (data.has_gift_card) {
                $('#ncd-valid-tipo-badge').removeClass('badge-info').addClass('badge-secondary').text('Mixto Gift Card + IVA');
                $('#ncd-valid-tipo-desc').text('Ajusta IVA de la porción correspondiente.');
                $('#ncd-refund-opt-gc').show();
            } else {
                $('#ncd-valid-tipo-badge').removeClass('badge-secondary').addClass('badge-info').text('Fiscal Estándar');
                $('#ncd-valid-tipo-desc').text('Ajuste IVA Débito/Crédito 13% Sector 24.');
                $('#ncd-refund-opt-gc').hide();
                $('#ncd-refund-method').val('cash');
            }

            if (data.total_available_items <= 0) {
                this.showAlert('warning', 'Todos los ítems de esta factura ya fueron devueltos previamente.');
                $('#ncd-btn-next').prop('disabled', true);
            } else {
                $('#ncd-btn-next').prop('disabled', false);
            }
        },

        populateStep2: function(items) {
            var tbody = $('#ncd-items-tbody');
            tbody.empty();

            if (!items || items.length === 0) {
                tbody.append('<tr><td colspan="9" class="text-center text-muted py-4">No se encontraron productos disponibles para devolución.</td></tr>');
                return;
            }

            var self = this;
            $.each(items, function(i, item) {
                var canReturn = item.available_qty > 0;
                var rowClass = canReturn ? '' : 'text-muted bg-light opacity-50';
                var tr = $('<tr class="' + rowClass + '"></tr>');

                var chkBox = '<input type="checkbox" class="ncd-item-check" data-index="' + i + '" ' + (canReturn ? '' : 'disabled') + '>';
                var qtyInput = '<input type="number" class="form-control form-control-sm text-center ncd-item-qty" ' +
                               'data-index="' + i + '" min="0.01" max="' + item.available_qty + '" step="any" ' +
                               'value="' + item.available_qty + '" ' + (canReturn ? 'disabled' : 'disabled') + '>';

                tr.append('<td class="text-center">' + chkBox + '</td>');
                tr.append('<td><strong>' + item.product_name + '</strong><br><small class="text-muted">' + item.product_code + ' (' + item.unit_name + ')</small></td>');
                tr.append('<td class="text-center">' + item.original_qty.toFixed(2) + '</td>');
                tr.append('<td class="text-center">' + item.already_returned_qty.toFixed(2) + '</td>');
                tr.append('<td class="text-center font-weight-bold text-success">' + item.available_qty.toFixed(2) + '</td>');
                tr.append('<td class="text-center">' + qtyInput + '</td>');
                tr.append('<td class="text-right">' + item.net_unit_price.toFixed(2) + '</td>');
                tr.append('<td class="text-right ncd-row-desc text-danger">Bs. 0.00</td>');
                tr.append('<td class="text-right font-weight-bold ncd-row-subtotal">Bs. 0.00</td>');

                tbody.append(tr);
            });

            this.bindStep2Events();
        },

        bindStep2Events: function() {
            var self = this;
            $('.ncd-item-check').off('change').on('change', function() {
                var idx = $(this).data('index');
                var qtyInput = $('.ncd-item-qty[data-index="' + idx + '"]');
                if ($(this).is(':checked')) {
                    qtyInput.prop('disabled', false);
                } else {
                    qtyInput.prop('disabled', true);
                }
                self.recalculate();
            });

            $('.ncd-item-qty').off('input change').on('input change', function() {
                var idx = $(this).data('index');
                var max = parseFloat($(this).attr('max')) || 0;
                var val = parseFloat($(this).val()) || 0;

                if (val > max) {
                    $(this).val(max);
                } else if (val < 0) {
                    $(this).val(0);
                }
                self.recalculate();
            });

            $('#ncd-btn-select-all').off('click').on('click', function() {
                $('.ncd-item-check:not(:disabled)').each(function() {
                    $(this).prop('checked', true).trigger('change');
                });
            });

            $('#ncd-btn-deselect-all').off('click').on('click', function() {
                $('.ncd-item-check').each(function() {
                    $(this).prop('checked', false).trigger('change');
                });
            });
        },

        recalculate: function() {
            var totalDevuelto = 0;
            var totalDescuento = 0;
            var self = this;

            $('.ncd-item-check').each(function() {
                var idx = $(this).data('index');
                var item = itemsList[idx];
                var row = $(this).closest('tr');

                if ($(this).is(':checked') && item) {
                    var qty = parseFloat($('.ncd-item-qty[data-index="' + idx + '"]').val()) || 0;
                    if (qty > 0) {
                        var pu = item.net_unit_price || 0;
                        var descProp = (item.discount_per_unit || 0) * qty;
                        descProp = Math.round(descProp * 100) / 100;

                        var subtotalBruto = Math.round((qty * pu) * 100) / 100;
                        var subtotalNeto = Math.round((subtotalBruto - descProp) * 100) / 100;

                        row.find('.ncd-row-desc').text('Bs. ' + descProp.toFixed(2));
                        row.find('.ncd-row-subtotal').text('Bs. ' + subtotalNeto.toFixed(2));

                        totalDevuelto += subtotalBruto;
                        totalDescuento += descProp;
                    } else {
                        row.find('.ncd-row-desc').text('Bs. 0.00');
                        row.find('.ncd-row-subtotal').text('Bs. 0.00');
                    }
                } else {
                    row.find('.ncd-row-desc').text('Bs. 0.00');
                    row.find('.ncd-row-subtotal').text('Bs. 0.00');
                }
            });

            var montoEfectivo = Math.round((totalDevuelto - totalDescuento) * 100) / 100;
            var montoIva = Math.round((montoEfectivo * 0.13) * 100) / 100;

            calculatedFinancials = {
                montoTotalDevuelto: totalDevuelto,
                montoDescuento: totalDescuento,
                montoEfectivo: montoEfectivo,
                montoIva: montoIva
            };

            $('#ncd-fin-total-devuelto').text('Bs. ' + totalDevuelto.toFixed(2));
            $('#ncd-fin-descuento').text('Bs. ' + totalDescuento.toFixed(2));
            $('#ncd-fin-monto-efectivo').text('Bs. ' + montoEfectivo.toFixed(2));
            $('#ncd-fin-iva').text('Bs. ' + montoIva.toFixed(2));

            // Step 4 summaries
            $('#ncd-summary-monto').text('Bs. ' + montoEfectivo.toFixed(2));
            $('#ncd-summary-iva').text('Bs. ' + montoIva.toFixed(2));
        },

        getSelectedItems: function() {
            var selected = [];
            $('.ncd-item-check:checked').each(function() {
                var idx = $(this).data('index');
                var item = itemsList[idx];
                var qty = parseFloat($('.ncd-item-qty[data-index="' + idx + '"]').val()) || 0;
                if (qty > 0 && item) {
                    selected.push({
                        product_id: item.product_id,
                        qty: qty,
                        net_unit_price: item.net_unit_price,
                        discount: Math.round(((item.discount_per_unit || 0) * qty) * 100) / 100,
                        product_name: item.product_name,
                        product_code: item.product_code
                    });
                }
            });
            return selected;
        },

        updateStepper: function(step) {
            currentStep = step;
            $('.ncd-step-panel').hide();
            $('#ncd-step-' + step).fadeIn(200);

            // Breadcrumbs
            for (var s = 1; s <= 4; s++) {
                var el = $('#ncd-step-indicator-' + s);
                el.removeClass('active completed');
                if (s < step) {
                    el.addClass('completed');
                } else if (s === step) {
                    el.addClass('active');
                }
            }

            // Buttons visibility
            $('#ncd-btn-prev').toggle(step > 1 && step < 4);
            $('#ncd-btn-next').toggle(step < 4);
            $('#ncd-btn-cancel').toggle(step < 4);

            if (step === 2) {
                $('#ncd-btn-next').text('Continuar a Liquidación ');
                $('#ncd-btn-next').append('<i class="fa fa-arrow-right ml-1"></i>');
            } else if (step === 3) {
                $('#ncd-btn-next').text('Revisar y Confirmar ');
                $('#ncd-btn-next').append('<i class="fa fa-arrow-right ml-1"></i>');
            } else {
                $('#ncd-btn-next').text('Siguiente ');
                $('#ncd-btn-next').append('<i class="fa fa-arrow-right ml-1"></i>');
            }

            if (step === 4) {
                var selected = this.getSelectedItems();
                $('#ncd-summary-client').text(saleData ? (saleData.invoice.razon_social + ' (' + saleData.invoice.nit_ci + ')') : '-');
                $('#ncd-summary-inv').text(saleData ? ('N° ' + saleData.invoice.nro_factura) : '-');
                $('#ncd-summary-items-count').text(selected.length + ' producto(s)');
                
                var refMethodText = $('#ncd-refund-method option:selected').text();
                $('#ncd-summary-refund-method').text(refMethodText);
            }
        },

        nextStep: function() {
            if (currentStep === 1) {
                this.updateStepper(2);
            } else if (currentStep === 2) {
                var selected = this.getSelectedItems();
                if (selected.length === 0) {
                    alert('Debe seleccionar al menos un producto para devolver.');
                    return;
                }
                this.recalculate();
                this.updateStepper(3);
            } else if (currentStep === 3) {
                this.updateStepper(4);
            }
        },

        prevStep: function() {
            if (currentStep > 1) {
                this.updateStepper(currentStep - 1);
            }
        },

        emitirSIAT: function() {
            var selected = this.getSelectedItems();
            if (selected.length === 0) {
                alert('No hay productos seleccionados.');
                return;
            }

            var self = this;
            var payload = {
                sale_id: saleData.sale.id,
                items: selected,
                refund_method: $('#ncd-refund-method').val(),
                adjust_inventory: $('#ncd-adjust-inventory').is(':checked') ? 1 : 0,
                staff_note: $('#ncd-staff-note').val(),
                _token: "{{ csrf_token() }}"
            };

            this.showLoading(true, 'Transmitiendo Nota de Crédito-Débito al SIAT...', 'Comunicando con el Servicio de Impuestos Nacionales.');

            $.ajax({
                url: "{{ route('nota-credito-debito.emitir') }}",
                type: "POST",
                data: JSON.stringify(payload),
                contentType: "application/json",
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                success: function(resp) {
                    self.showLoading(false);
                    if (resp.status) {
                        emittedResult = resp;
                        self.showSuccess(resp);
                        if (typeof window.cargarVentasRecientes === 'function') {
                            try { window.cargarVentasRecientes(); } catch(e) {}
                        }
                    } else {
                        self.updateStepper(4);
                        $('#ncd-pre-emit-box').show();
                        self.showAlert('danger', resp.message || 'Error al emitir la Nota ante el SIAT.');
                    }
                },
                error: function(xhr) {
                    self.showLoading(false);
                    self.updateStepper(4);
                    $('#ncd-pre-emit-box').show();
                    var msg = 'Error en la conexión con el servicio SIAT.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    self.showAlert('danger', msg);
                }
            });
        },

        showSuccess: function(data) {
            $('.ncd-step-panel').hide();
            $('#ncd-step-4').show();
            $('#ncd-pre-emit-box').hide();
            $('#ncd-modal-footer').hide();
            $('#ncd-alert-container').empty();
            $('#ncd-success-box').fadeIn(200);

            for (var s = 1; s <= 4; s++) {
                $('#ncd-step-indicator-' + s).removeClass('active').addClass('completed');
            }

            if (data.is_gift_card_only) {
                $('#ncd-succ-nro').text('DEV-GC');
                $('#ncd-succ-cuf').text('Sin ajuste fiscal SIAT (100% Tarjeta de Regalo - Saldo Restituido)');
                $('#ncd-succ-giftcard-notice').show();
            } else {
                $('#ncd-succ-nro').text(data.nro_factura || '1');
                $('#ncd-succ-cuf').text(data.cuf || '-');
                $('#ncd-succ-giftcard-notice').hide();
            }
            var monto = parseFloat(data.monto_efectivo) || (calculatedFinancials ? calculatedFinancials.montoEfectivo : 0);
            $('#ncd-succ-monto').text('Bs. ' + monto.toFixed(2));

            var targetId = data.customer_sale_id || data.return_id;
            var printUrl = "{{ url('nota-credito-debito/print-80mm') }}/" + targetId;
            var pdfUrl = "{{ url('nota-credito-debito/download-pdf') }}/" + targetId;

            $('#ncd-btn-print-80mm').off('click').on('click', function() {
                var printWin = window.open(printUrl + '?print=1', '_blank', 'width=420,height=600');
                if (printWin) printWin.focus();
            });

            $('#ncd-btn-download-pdf').attr('href', pdfUrl);
            $('#ncd-pdf-fallback-btn').attr('href', pdfUrl);

            // Cargar y mostrar PDF oficial SIAT en el visor integrado (estilo return-sale)
            var self = this;
            if (data.bytes) {
                self.loadPdfBytes(data.bytes, pdfUrl);
            } else if (data.customer_sale_id || data.sale_id) {
                $('#ncd-pdf-loading').show();
                var bytesUrl = "{{ url('nota-credito-debito/obtener-bytes') }}/" + (data.customer_sale_id || data.sale_id);
                $.ajax({
                    url: bytesUrl,
                    type: "GET",
                    dataType: "json",
                    success: function(bResp) {
                        $('#ncd-pdf-loading').hide();
                        if (bResp && bResp.bytes) {
                            self.loadPdfBytes(bResp.bytes, pdfUrl);
                        } else {
                            $('#ncd-pdf-embed').attr('src', pdfUrl + '?inline=1');
                            $('#ncd-pdf-object').attr('data', pdfUrl + '?inline=1');
                        }
                    },
                    error: function() {
                        $('#ncd-pdf-loading').hide();
                        $('#ncd-pdf-embed').attr('src', pdfUrl + '?inline=1');
                        $('#ncd-pdf-object').attr('data', pdfUrl + '?inline=1');
                    }
                });
            } else {
                $('#ncd-pdf-embed').attr('src', pdfUrl + '?inline=1');
                $('#ncd-pdf-object').attr('data', pdfUrl + '?inline=1');
            }

            // Refrescar tabla de ventas o POS si está disponible
            if (typeof $('#sale-table').DataTable === 'function') {
                try { $('#sale-table').DataTable().ajax.reload(null, false); } catch(e) {}
            }
        },

        loadPdfBytes: function(base64Bytes, fallbackUrl) {
            var dataUri = 'data:application/pdf;base64,' + base64Bytes;
            $('#ncd-pdf-embed').attr('src', dataUri);
            $('#ncd-pdf-object').attr('data', dataUri);

            try {
                var byteCharacters = atob(base64Bytes);
                var byteNumbers = new Array(byteCharacters.length);
                for (var i = 0; i < byteCharacters.length; i++) {
                    byteNumbers[i] = byteCharacters.charCodeAt(i);
                }
                var byteArray = new Uint8Array(byteNumbers);
                var blob = new Blob([byteArray], { type: 'application/pdf' });
                var blobUrl = URL.createObjectURL(blob);

                $('#ncd-btn-open-tab').attr('href', blobUrl);
                $('#ncd-btn-print-direct').off('click').on('click', function() {
                    var iframe = document.getElementById('ncd-pdf-print-iframe');
                    if (!iframe) {
                        iframe = document.createElement('iframe');
                        iframe.id = 'ncd-pdf-print-iframe';
                        iframe.style.position = 'fixed';
                        iframe.style.right = '0';
                        iframe.style.bottom = '0';
                        iframe.style.width = '0';
                        iframe.style.height = '0';
                        iframe.style.border = '0';
                        document.body.appendChild(iframe);
                    }
                    iframe.src = blobUrl;
                    iframe.onload = function() {
                        try {
                            iframe.contentWindow.focus();
                            iframe.contentWindow.print();
                        } catch(e) {
                            window.open(blobUrl, '_blank');
                        }
                    };
                });
            } catch(e) {
                console.warn('Error creando Blob URL del PDF:', e);
                $('#ncd-btn-open-tab').attr('href', fallbackUrl || dataUri);
                $('#ncd-btn-print-direct').off('click').on('click', function() {
                    window.open(fallbackUrl || dataUri, '_blank');
                });
            }
        },

        openPreviewPdf: function(id) {
            currentStep = 4;
            emittedResult = null;
            try { $('#recentTransaction').modal('hide'); } catch(e) {}
            try { $('#sale-details').modal('hide'); } catch(e) {}
            $('#ncd-alert-container').empty();
            $('#ncd-pre-emit-box').hide();
            $('#ncd-modal-footer').hide();
            $('.ncd-step-panel').hide();
            $('#ncd-step-4').show();
            $('#ncd-success-box').show();
            $('#modal-nota-credito-debito').modal('show');

            for (var s = 1; s <= 4; s++) {
                $('#ncd-step-indicator-' + s).removeClass('active').addClass('completed');
            }

            var pdfUrl = "{{ url('nota-credito-debito/download-pdf') }}/" + id;
            var printUrl = "{{ url('nota-credito-debito/print-80mm') }}/" + id;

            $('#ncd-btn-print-80mm').off('click').on('click', function() {
                var printWin = window.open(printUrl + '?print=1', '_blank', 'width=420,height=600');
                if (printWin) printWin.focus();
            });
            $('#ncd-btn-download-pdf').attr('href', pdfUrl);
            $('#ncd-pdf-fallback-btn').attr('href', pdfUrl);

            $('#ncd-pdf-loading').show();
            var self = this;
            $.ajax({
                url: "{{ url('nota-credito-debito/obtener-bytes') }}/" + id,
                type: "GET",
                dataType: "json",
                success: function(resp) {
                    $('#ncd-pdf-loading').hide();
                    if (resp && resp.bytes) {
                        self.loadPdfBytes(resp.bytes, pdfUrl);
                    } else {
                        $('#ncd-pdf-embed').attr('src', pdfUrl + '?inline=1');
                        $('#ncd-pdf-object').attr('data', pdfUrl + '?inline=1');
                    }
                },
                error: function() {
                    $('#ncd-pdf-loading').hide();
                    $('#ncd-pdf-embed').attr('src', pdfUrl + '?inline=1');
                    $('#ncd-pdf-object').attr('data', pdfUrl + '?inline=1');
                }
            });
        }
    };

    // DOM event listeners
    $(document).ready(function() {
        $('#ncd-btn-next').on('click', function() {
            window.NotaCreditoDebitoWizard.nextStep();
        });

        $('#ncd-btn-prev').on('click', function() {
            window.NotaCreditoDebitoWizard.prevStep();
        });

        $('#ncd-btn-confirm-emit').on('click', function() {
            window.NotaCreditoDebitoWizard.emitirSIAT();
        });

        // Event delegation for trigger buttons across tables and POS
        $(document).on('click', '.emitir-nota-credito-debito-btn', function(e) {
            e.preventDefault();
            var saleId = $(this).data('id');
            if (saleId) {
                window.NotaCreditoDebitoWizard.open(saleId);
            }
        });

        $(document).on('click', '.imprimir-ncd-btn', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            if (id) {
                window.NotaCreditoDebitoWizard.openPreviewPdf(id);
            }
        });
    });
})();
</script>
