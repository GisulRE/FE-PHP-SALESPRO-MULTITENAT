@extends('layout.main') @section('content')
@if(session()->has('create_message'))
    <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('create_message') !!}</div> 
@endif
@if(session()->has('edit_message'))
    <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('edit_message') }}</div> 
@endif
@if(session()->has('import_message'))
    <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('import_message') !!}</div> 
@endif
@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div> 
@endif

<section>
    <div class="container-fluid">
        <h3>Listado de registros existentes/modificados*</h3>
    </div>
    <div class="table-responsive">
        <table id="customer-table" class="table">
            <thead>
                <tr>
                    <th>Grupo Clientes</th>
                    <th>Tipo Documento</th>
                    <th>Valor Documento</th>
                    <th>Complemento</th>
                    <th>Nombre / Razón Social</th>
                    <th>Correo Electrónico</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                    <th>País</th>
                    
                    <th class="not-exported">{{trans('file.action')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($listado_general['existente'] as $listado)
                <tr>
                    <td>
                        <?php $customer_group = DB::table('customer_groups')->where('id',$listado->customer_group_id)->first(); ?>
                        {{  $customer_group->name }}
                    </td>
                    <td>{{$listado->tipo_documento}}</td>
                    <td>{{$listado->valor_documento}}</td>
                    <td>{{$listado->complemento_documento}}</td>
                    <td>{{$listado->name}}</td>
                    <td>{{$listado->email}}</td>
                    <td>{{$listado->phone_number}}</td>
                    <td>{{$listado->address}}</td>
                    <td>{{$listado->country}}</td>
                    <td>Acciones</td>
                    
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <hr>
    <div class="container-fluid">
        <h3>Listado de registros nuevos*</h3>
    </div>
    <div class="table-responsive">
        <table id="customer-table" class="table">
            <thead>
                <tr>
                    <th>Grupo Clientes</th>
                    <th>Tipo Documento</th>
                    <th>Valor Documento</th>
                    <th>Complemento</th>
                    <th>Nombre / Razón Social</th>
                    <th>Correo Electrónico</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                    <th>País</th>
                    
                    <th class="not-exported">{{trans('file.action')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($listado_general['no_existente'] as $listado)
                <tr>
                    <td>
                        <?php $customer_group = DB::table('customer_groups')->where('id',$listado->customer_group_id)->first(); ?>
                        {{  $customer_group->name }}
                    </td>
                    <td>{{$listado->tipo_documento}}</td>
                    <td>{{$listado->valor_documento}}</td>
                    <td>{{$listado->complemento_documento}}</td>
                    <td>{{$listado->name}}</td>
                    <td>{{$listado->email}}</td>
                    <td>{{$listado->phone_number}}</td>
                    <td>{{$listado->address}}</td>
                    <td>{{$listado->country}}</td>
                    <td>Acciones</td>
                    
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>








<script type="text/javascript">
    $("ul#people").siblings('a').attr('aria-expanded','true');
    $("ul#people").addClass("show");
    $("ul#people #customer-list-menu").addClass("active");

    
</script>
@endsection