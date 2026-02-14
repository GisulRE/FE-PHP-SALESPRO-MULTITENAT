<div class="row">
    <div class="form-group col-md-6">
        <label>Domicilio</label>
        <div class="input-group">
            <input type="text" class="form-control" id="domicilio_cliente" name="domicilio_cliente"
                placeholder="Direccion de Cliente" />
        </div>
    </div>
    <div class="form-group col-md-6">
        <label>Ciudad</label>
        <div class="input-group">
            <input type="text" class="form-control" id="ciudad" name="ciudad" placeholder="Ejm: Santa Cruz" />
        </div>
    </div>
    <div class="form-group col-md-6">
        <label>Zona</label>
        <div class="input-group">
            <input type="text" class="form-control" id="zona" name="zona" placeholder="Ejm: Zona Sur" />
        </div>
    </div>
    <div class="form-group col-md-3">
        <label>Consumo Periodo</label>
        <div class="input-group">
            <input type="text" class="form-control" id="consumo_periodo" name="consumo_periodo"
                placeholder="Ejm: 1" />
        </div>
    </div>
    <div class="form-group col-md-3">
        <label>Gestion</label>
        <div class="input-group">
            <input type="text" class="form-control" id="gestion" name="gestion" placeholder="Ejm: 2023" />
        </div>
    </div>
    <div class="form-group col-md-3">
        <label>Mes</label>
        <div class="input-group">
            <input type="text" class="form-control" id="mes" name="mes" placeholder="Ejm: Enero" />
        </div>
    </div>
    <div class="form-group col-md-3">
        <label>Numero Medidor</label>
        <div class="input-group">
            <input type="text" class="form-control" id="numero_medidor" name="numero_medidor"
                placeholder="Ejm: 12345" />
        </div>
    </div>
    <div class="form-group col-md-3">
        <label>Lectura Actual <span>(medidor)</span></label>
        <div class="input-group">
            <input type="text" class="form-control" id="lectura_medidor_actual" name="lectura_medidor_actual"
                placeholder="Ejm: 00001" />
        </div>
    </div>
    <div class="form-group col-md-3">
        <label>Lectura Anterior <span>(medidor)</span></label>
        <div class="input-group">
            <input type="text" class="form-control" id="lectura_medidor_anterior" name="lectura_medidor_anterior"
                placeholder="Ejm: 10000" />
        </div>
    </div>
    <div class="form-group col-md-3">
        <label>Tasa Aseo</span></label>
        <div class="input-group">
            <input type="number" class="form-control" id="tasa_aseo" name="tasa_aseo" value="0"
                placeholder="Ejm: 0" />
        </div>
    </div>
    <div class="form-group col-md-3">
        <label>Tasa Alumbrado</label>
        <div class="input-group">
            <input type="number" class="form-control" id="tasa_alumbrado" name="tasa_alumbrado" value="0"
                placeholder="Ejm: 0" />
        </div>
    </div>
    <div class="form-group col-md-3">
        <label>Otras Tasas</label>
        <div class="input-group">
            <input type="number" class="form-control" id="otras_tasas" name="otras_tasas" value="0"
                placeholder="Ejm: 0" />
        </div>
    </div>
    <div class="form-group col-md-6">
        <label>Ajuste Sujeto a IVA</label>
        <div class="table-responsive">
            <table id="table-detalleIVA" class="table">
                <thead>
                    <tr>
                        <th>Nro.</th>
                        <th>Descripcion</th>
                        <th>Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td><input type="text" name="descripcionItemIva[]" value="Otros Cargos" class="form-control"></td>
                        <td><input type="number" name="montoItemIva[]" value="0" min="0" class="form-control text-end"></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td><input type="text" name="descripcionItemIva[]" value="Otros" class="form-control"></td>
                        <td><input type="number" name="montoItemIva[]" value="0" min="0" class="form-control text-end"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="form-group col-md-6">
        <label>Ajuste No Sujeto a IVA</label>
        <div class="table-responsive">
            <table id="table-detalleNoIVA" class="table">
                <thead>
                    <tr>
                        <th>Nro.</th>
                        <th>Descripcion</th>
                        <th>Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td><input type="text" name="descripcionItemNoIva[]" value="Otros Cargos" class="form-control"></td>
                        <td><input type="number" name="montoItemNoIva[]" value="0" min="0" class="form-control text-end"></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td><input type="text" name="descripcionItemNoIva[]" value="Otros" class="form-control"></td>
                        <td><input type="number" name="montoItemNoIva[]" value="0" min="0" class="form-control text-end"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="form-group col-md-6">
        <label>Otros Pagos No Sujeto a IVA</label>
        <div class="table-responsive">
            <table id="table-detalleOtrosNoIVA" class="table">
                <thead>
                    <tr>
                        <th>Nro.</th>
                        <th>Descripcion</th>
                        <th>Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td><input type="text" name="descripcionOtroItemNoIva[]" value="Otros Pagos" class="form-control"></td>
                        <td><input type="number" name="otrosMontoItemNoIva[]" value="0" min="0" class="form-control text-end"></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td><input type="text" name="descripcionOtroItemNoIva[]" value="Otros" class="form-control"></td>
                        <td><input type="number" name="otrosMontoItemNoIva[]" value="0" min="0" class="form-control text-end"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
