@extends('layout.main') @section('content')
@if(empty($products_list))
<div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{'No existe datos en este rango de fechas y producto!'}}</div>
@endif
@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div> 
@endif

<section class="forms">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header mt-2">
                <h3 class="text-center">{{trans('file.Products With Lotes')}}</h3>
            </div>
            <div class="row mb-12">
            </div>
        </div>
    </div>
    <div class="table-responsive mb-4">
        <table id="report-table" class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{trans('file.Code')}}</th>
                    <th>{{trans('file.name')}}</th>
                    <th>{{trans('file.category')}}</th>
                    <th>Tipo</th>
                </tr>
            </thead>
            <tbody id="products_list">
                @if(!empty($products_list))
                @foreach($products_list as $key => $product)
                <tr class="clickable">
                    <?php
                        $lotes_list = App\ProductLote::select('name', 'expiration', 'qty','stock', 'status')->where([['idproduct', $product->id], ['status', '!=', 0]])->get();
                        $category = App\Category::select('name')->find($product->category_id);
                        if($category)
                            $category_name = $category->name;
                        else
                            $category_name = "Sin Categoria";
                    ?>
                    <td>{{$key + 1}}</td> 
                    <td><div> {{$product->code}} <button type="button" class="show-lote btn btn-link" title="Ver Lotes"  onclick="showLotes('{{$product->id}}', '{{$product->name}}')"> <i style="font-size:20px;color:rgb(12, 120, 207)" class="fa fa-eye"></i></button></div></td>
                    <td>{{$product->name}}</td>
                    <td>{{$category_name}}</td>
                    <td>{{$product->type}}</td>
                    <div>
                        @foreach($lotes_list as $key => $lote)
                        <tr id="group-of-rows-{{$product->id}}">
                        @if ($key == 0)
                            <td align="center"> <Strong> # </Strong><br><span style="color:darkgreen">{{$key + 1}}</span></td>
                            <td align="center"><Strong>{{trans('file.name')}}</Strong> <br> <span style="color:darkgreen">{{$lote->name}}</span></td>
                            <td align="center"><Strong>{{trans('file.Expiration')}}</Strong> <br><span style="color:darkgreen">{{$lote->expiration}}</span></td>
                            <td align="center"><Strong>{{trans('file.qty')}}</Strong> <br><span style="color:darkgreen">{{$lote->qty}}</span></td>
                            <td align="center"><Strong>{{trans('file.In Stock')}}</Strong> <br><span style="color:darkgreen">{{$lote->stock}}</span></td>
                        @else
                            <td align="center"><span style="color:darkgreen">{{$key + 1}}</span></td>
                            <td align="center"><span style="color:darkgreen">{{$lote->name}}</span></td>
                            <td align="center"><span style="color:darkgreen">{{$lote->expiration}}</span></td>
                            <td align="center"><span style="color:darkgreen">{{$lote->qty}}</span></td>
                            <td align="center"><span style="color:darkgreen">{{$lote->stock}}</span></td>
                        @endif
                        </tr>
                        @endforeach
                    </div>
                </tr>
                @endforeach
                @endif
            </tbody>
            
        </table>
    </div>

    <div id="showLoteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
        <div role="document" class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 id="title_lote" class="modal-title"></h5>
              <button type="button" id="close-btn" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 mt-2" id="product-lote-section">
                        <table id="table_lotes" class="table table-bordered table-hover lote-list">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{trans('file.name')}}</th>
                                <th>{{trans('file.Expiration')}}</th>
                                <th>{{trans('file.qty')}}</th>
                                <th>{{trans('file.In Stock')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
          </div>
        </div>
    </div>
</section>

<script type="text/javascript">
    //consultar();
    $("ul#report").siblings('a').attr('aria-expanded','true');
    $("ul#report").addClass("show");
    $("ul#report #proLotes-report-menu").addClass("active");
    var tempid;
    $('#report-table').DataTable( {
        "order": [],
        'language': {
            'lengthMenu': '_MENU_ {{trans("file.records per page")}}',
             "info":      '<small>{{trans("file.Showing")}} _START_ - _END_ (_TOTAL_)</small>',
            "search":  '{{trans("file.Search")}}',
            'paginate': {
                    'previous': '<i class="dripicons-chevron-left"></i>',
                    'next': '<i class="dripicons-chevron-right"></i>'
            }
        },
        'columnDefs': [
            {
                "orderable": false,
                'targets': 0
            },
            {
            
                'targets': [0]
            }
        ],
        'select': { style: 'multi',  selector: 'td:first-child'},
        'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, "All"]],
        dom: '<"row"lfB>rtip',
        drawCallback: function () {
            var api = this.api();
            
        }
    } );
    $('.dt-buttons').hide();

    function show(id){
        if(tempid != id){
            $(`#group-of-rows-${tempid}`).addClass('d-none');
            $(`#group-of-rows-${id}`).removeClass('d-none');
        }
        tempid = id;
        
    }
    function consultar(){
        var filter = $('#filter').val();
        var days = $('#days').val();
        var url = '<?php echo url('/'); ?>' + '/report/alert_expiration/'+filter+'/'+days;
        $("#consultabtn").attr("href", url)
    }

    function showLotes(id, namepro){
        var table = $('#table_lotes').DataTable();
        table.clear().draw();
        table.destroy();
        $.get('../lote/findbyproduct/' + id, function(res) {
        var lotes = res.lotes;
        $("#title_lote").text('Lotes de producto - ' + namepro);
        var cont = 1;
        for (var i in lotes) {
            const date = lotes[i].expiration;
            const [year, month, day] = date.split("-");
            const newDate = `${day}/${month}/${year}`;
            var newRow = $("<tr>");
            var cols = '';
                cols += '<td>' + cont + '</td>';
                cols += '<td>' + lotes[i].name + '</td>';
                cols += '<td>' + newDate + '</td>';
                cols += '<td>' + lotes[i].qty + '</td>';
                cols += '<td>' + lotes[i].stock + '</td>';
            newRow.append(cols);
            cont = cont + 1;
            $("#table_lotes tbody").append(newRow);
        }
        
        }).catch((error) => {
            var log = JSON.parse(error);
            console.warn(log);
        });
        $('#showLoteModal').modal();
    }
</script>
@endsection