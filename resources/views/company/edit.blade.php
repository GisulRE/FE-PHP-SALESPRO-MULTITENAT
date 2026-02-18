@extends('layout.main') @section('content')

@if(session()->has('not_permitted'))
<div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif

<section>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h4>Editar Empresa</h4>
            </div>
            <div class="card-body">
                <p class="italic"><small>{{ trans('file.The field labels marked with * are required input fields') }}.</small></p>
                {!! Form::open(['route' => ['companies.update', $company->id], 'method' => 'put']) !!}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ trans('file.name') }} *</label>
                            <input type="text" name="name" required class="form-control" value="{{ $company->name }}">
                            @if ($errors->has('name'))
                                <span class="text-danger">{{ $errors->first('name') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <input type="submit" value="{{ trans('file.submit') }}" class="btn btn-primary">
                    <a href="{{ route('companies.index') }}" class="btn btn-secondary">{{ trans('file.Cancel') }}</a>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</section>

@endsection
