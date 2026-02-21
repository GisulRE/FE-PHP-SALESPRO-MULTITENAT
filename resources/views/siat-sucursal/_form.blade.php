@csrf 

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label> Sucursal *</strong> </label>
            <input type="text" name="sucursal" id="sucursal" class="form-control" required 
                value="{{ old('sucursal',$sucursal->codigo) }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label> Nombre Sucursal *</strong> </label>
            <input type="text" name="nombre" id="nombre" class="form-control" required 
                value="{{ old('nombre',$sucursal->nombre) }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Descripción *</strong> </label>
            <input type="text" name="descripcion_sucursal" id="descripcion_sucursal" class="form-control" required
                value="{{ old('descripcion_sucursal',$sucursal->direccion) }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Domicilio Tributario *</strong> </label>
            <input type="text" name="domicilio_tributario" id="domicilio_tributario" class="form-control" aria-describedby="domicilio_tributario" required
                value="{{ old('domicilio_tributario',$sucursal->direccion) }}">
            <p id="domicilio_tributario" class="ayuda">El domicilio tributario correcto a ley.</p>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Departamento *</strong> </label>
            <input type="text" name="departamento" id="departamento" class="form-control" aria-describedby="departamento" required
                value="{{ old('departamento',$sucursal->departamento ?? '') }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Ciudad Municipio *</strong> </label>
            <input type="text" name="ciudad_municipio" id="ciudad_municipio" class="form-control" required 
                value="{{ old('ciudad_municipio',$sucursal->ciudad) }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Email *</strong> </label>
            <input type="email" name="email" id="email" class="form-control" required 
                value="{{ old('email',$sucursal->email ?? '') }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Teléfono *</strong> </label>
            <input type="text" name="telefono" id="telefono" class="form-control" required
                value="{{ old('telefono',$sucursal->telefono) }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Estado *</strong> </label>
            <div class="input-group">
                <select name="estado" class="form-control selectpicker" required>
                    @include('layout.partials.option-active-inactive', ['val' => $sucursal->estado])
                </select>
            </div>
        </div>
    </div>
    <div class="col-12 mt-3">
        <div class="form-group">
            <input type="submit" value="{{trans('file.submit')}}" id="submit-btn" class="btn btn-primary">
        </div>
    </div>
</div>
