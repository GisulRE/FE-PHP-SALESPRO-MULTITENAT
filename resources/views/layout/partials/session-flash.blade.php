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

@if(session()->has('success'))
    <script>
        swal("{{ session()->get('success') }}", {
        icon: "success",
        });
        </script>  
        {{session()->forget('success')}}
@endif
@if(session()->has('warning'))
    <script>
        swal("{{ session()->get('warning') }}", {
        icon: "warning",
        });
    </script>  
    {{session()->forget('warning')}}
@endif
@if(session()->has('info'))
    <script>
        swal("{{ session()->get('info') }}", {
        icon: "info",
        });
    </script>  
    {{session()->forget('info')}}
@endif
