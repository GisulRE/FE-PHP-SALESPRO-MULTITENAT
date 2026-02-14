@if(session()->has('message'))
    <div class="alert alert-success alert-dismissible text-center">
        <button type="button" class="close" data-dismiss="alert"
            aria-label="Close"><span aria-hidden="true">&times;</span>
        </button>
        {{ session()->get('message') }}
    </div>
@endif
@if(session()->has('not_permitted'))
    <div class="alert alert-danger alert-dismissible text-center">
        <button type="button" class="close" data-dismiss="alert"
            aria-label="Close"><span aria-hidden="true">&times;</span>
        </button>{{ session()->get('not_permitted') }}
    </div>
@endif
@if(session()->has('warning'))
    <div class="alert alert-warning alert-dismissible text-center">
        <button type="button" class="close" data-dismiss="alert"
            aria-label="Close"><span aria-hidden="true">&times;</span>
        </button>{{ session()->get('warning') }}
    </div>
@endif
@if(session()->has('message'))
    <script>
        Swal.fire({
            icon: 'success',
            title: "{{ session()->get('message') }}"
        });
    </script>  
    {{session()->forget('success')}}
@endif
@if(session()->has('not_permitted'))
    <script>
        Swal.fire({
            icon: 'error',
            title: "{{ session()->get('not_permitted') }}"
        });
    </script>  
    {{session()->forget('success')}}
@endif
@if(session()->has('message_error'))
    <script>
        Swal.fire({
            icon: 'info',
            title: "{{ session()->get('message_error') }}"
        });
    </script>  
    {{session()->forget('success')}}
@endif
@if(session()->has('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: "{{ session()->get('success') }}"
        });
    </script>  
    {{session()->forget('success')}}
@endif
@if(session()->has('warning'))
    <script>
        Swal.fire({
            icon: 'warning',
            title: '{{ session()->get('warning') }}',
            html:
                'Se recomienda revisar <b>Modo Contingencia</b>, ' +
                '<a href="{{ route('contingencia.index') }}">casos especiales</a> ' +
                'para generar facturas. ',
        });
    </script>  
    {{session()->forget('warning')}}
@endif