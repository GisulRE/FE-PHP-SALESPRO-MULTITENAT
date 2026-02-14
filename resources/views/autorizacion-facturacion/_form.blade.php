@csrf 

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Fecha Solicitud *</strong> </label>
            <input type="date" name="fecha_solicitud" id="fecha_solicitud" class="form-control" required
                value="{{ old('fecha_solicitud',$item->fecha_solicitud) }}"> 
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Código Sistema *</strong> </label>
            <input type="text" name="codigo_sistema" id="codigo_sistema" class="form-control" required
                value="{{ old('codigo_sistema',$item->codigo_sistema) }}">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Tipo Sistema *</strong> </label>
            <div class="input-group">
                <select name="tipo_sistema" class="form-control selectpicker" required>
                    @include('layout.partials.option-tipo-sistema', ['val' => $item->tipo_sistema])
                </select>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label>Modalidad *</strong> </label>
            <div class="input-group">
                <select id="tipo_modalidad" name="tipo_modalidad" class="form-control selectpicker" required>
                    @include('layout.partials.option-modalidad2', ['val' => $item->tipo_modalidad])
                </select>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Token *</strong> </label>
            <textarea class="form-control" name="token" id="token"  rows="10" required>{{ old('token',$item->token) }}</textarea>
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label>Fecha Vencimiento Token *</strong> </label>
            <input type="datetime-local" name="fecha_vencimiento_token" id="fecha_vencimiento_token" class="form-control" required
                value="{{ old('fecha_vencimiento_token',$item->fecha_vencimiento_token) }}"> 
        </div>
    </div>

    {{-- URLs para Produccion / Pruebas --}}
    @isset($partials_create)
        @include('autorizacion-facturacion.partials-urls')
    @endisset
    @isset($partials_edit)
        @include('autorizacion-facturacion.partials-urls-edit')
    @endisset

    <div class="col-12 mt-5">
        <div class="form-group">
            <input type="submit" value="{{trans('file.submit')}}" id="submit-btn" class="btn btn-primary">
        </div>
    </div>

</div>