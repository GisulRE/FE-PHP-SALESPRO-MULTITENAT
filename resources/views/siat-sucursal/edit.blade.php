@extends('layout.main')

@section('content')
<section class="forms">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h4>Actualizar Sucursal</h4>
                    </div>
                    
                    <div class="card-body">
                        <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
                        <form action="{{ route('sucursal.update', $sucursal->id) }}" method="POST">
                            @method('PUT')
                            @include('siat-sucursal._form')
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">

    $("ul#siat").siblings('a').attr('aria-expanded','true');
    $("ul#siat").addClass("show");
    $("ul#siat #siat-menu-sucursal").addClass("active");

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });




</script>
@endsection
