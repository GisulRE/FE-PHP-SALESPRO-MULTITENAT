@csrf 

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Ambiente *</strong> </label>
            <div class="input-group">
                <select name="ambiente" class="form-control selectpicker" required>
                    @include('layout.partials.option-prueba-produccion', ['val' => $item->ambiente])
                </select>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Nombre Servicio *</strong> </label>
            <input type="text" name="nombre_servicio" id="nombre_servicio" class="form-control" required
                value="{{ old('nombre_servicio',$item->nombre_servicio) }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Tipo Url *</strong> </label>
            <div class="input-group">
                <select name="tipo_url" class="form-control selectpicker" required>
                    @include('layout.partials.option-tipo-url', ['val' => $item->tipo_url])
                </select>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Modalidad *</strong> </label>
            <div class="input-group">
                <select name="uso_modalidad" class="form-control selectpicker" required>
                    @include('layout.partials.option-modalidad', ['val' => $item->uso_modalidad])
                </select>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="form-group">
            <label>URL *</strong> </label>
            <input type="text" name="ruta_url" id="ruta_url" class="form-control" aria-describedby="url_des" required
                value="{{ old('ruta_url',$item->ruta_url) }}">
            <p id="url_des" class="ayuda">Url Https://www.ejemplo.com</p>

        </div>
    </div>

    <div class="col-12">
        <div class="form-group">
            <input type="submit" value="{{trans('file.submit')}}" id="submit-btn" class="btn btn-primary">
        </div>
    </div>

</div>