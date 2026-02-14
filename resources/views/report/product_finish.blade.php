@extends('layout.main') @section('content')
@if(empty($product_details))
<div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{'No existe datos en este rango de fechas y producto!'}}</div>
@endif
@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div> 
@endif

<section class="forms">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header mt-2">
                <h3 class="text-center">{{trans('file.Product Report Finish')}}</h3>
            </div>
            {!! Form::open(['route' => 'report.productFinish', 'method' => 'post']) !!}
            <div class="row mb-12">
                <div class="col-md-6 mt-3" style="margin-left: 3.333%;">
                    <div class="form-group row">
                        <label class="d-tc mt-2"><strong>{{trans('file.Choose Your Date')}}</strong> &nbsp;</label>
                        <div class="d-tc">
                            <div class="input-group">
                                <input name="start_date" class="form-control" placeholder="DD/MM/YYYY" type="date" value="{{$start_date}}" required>
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <i class="dripicons-calendar tx-10 lh-0 op-4"></i>
                                    </div>
                                </div>
                                <label class="d-tc mt-2" style="margin-left: 5px"><strong>  A </strong> &nbsp;</label>
                                <input name="end_date" class="form-control" placeholder="DD/MM/YYYY" type="date" value="{{$end_date}}" required>
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <i class="dripicons-calendar tx-10 lh-0 op-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mt-3">
                    <div class="form-group row">
                        <label class="d-tc mt-2"><strong>{{trans('file.Choose Product')}}</strong> &nbsp;</label>
                        <div class="d-tc">
                            <input type="hidden" name="product_id_hidden" value="{{$product_id}}" />
                            <select id="product_id" name="product_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" >
                                @foreach($lims_product_all as $product)
                                <option value="{{$product->id}}">{{$product->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-1 offset-md-0 mt-3">
                    <div class="form-group">
                        <button class="btn btn-primary" type="submit">{{trans('file.submit')}}</button>
                    </div>
                </div>
                <div class="col-md-3 mt-3" style="margin-left: 3.333%;">
                    <div class="form-group row">
                        <label class="d-tc mt-2"><strong>Total Vendida</strong> &nbsp;</label>
                        <div class="d-tc">
                            <input type="number" name="total_sales" class="form-control center" value="{{number_format($total_sale, 2, '.', '')}}" readonly/>
                        </div>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
    <div class="container-fluid">
        <h2>Insumos Utilizados</h2>
    </div>
    <div class="table-responsive mb-4">
        <table id="report-table" class="table table-hover">
            <thead>
                <tr>
                    <th class="not-exported"></th>
                    <th>{{trans('file.Insumo')}}</th>
                    <th>{{trans('file.Qty')}}</th>
                    <th>{{trans('file.Unit')}}</th>
                    <th>{{trans('file.Cost')}} {{trans('file.Unit')}}</th>
                    <th>{{trans('file.Cost')}} Total Bs.</th>
                </tr>
            </thead>
            <tbody>
                @if(!empty($product_details))
                @foreach($product_details as $key => $pro)
                <tr>
                    <td>{{$key}}</td>
                    <td>{{$pro['name']}}</td>
                    <td>{{$pro['qty']}}</td>
                    <td>{{$pro['unit']}}</td>
                    <td>{{$pro['cost']}}</td>
                    <td>{{$pro['costotal']}}</td>
                </tr>
                @endforeach
                @endif
            </tbody>
            <tfoot>
                <th></th>
                <th>Total</th>
                <th>0.00</th>
                <th></th>
                <th>0.00</th>
                <th>0.00</th>
            </tfoot>
        </table>
    </div>
    <div class="container-fluid">
        <h5>Cantidad Vendida: {{number_format($total_sale, 2, '.', '')}}</h5><br>
        <h5 style="color:royalblue">Monto Venta (A): {{number_format($totalAmount_sale, 2, '.', '')}}</h5><br>
        <h5 style="color:deeppink">Costo Insumos (B): {{number_format($total_insumo, 2, '.', '')}}</h5><br>
        <h5 style="color:green">Ingreso Bruto (A - B): {{number_format($total_utilbruto, 2, '.', '')}}</h5><br>
    </div>
</section>

<script type="text/javascript">

    $("ul#report").siblings('a').attr('aria-expanded','true');
    $("ul#report").addClass("show");
    $("ul#report #productfinish-report-menu").addClass("active");

    $('#product_id').val($('input[name="product_id_hidden"]').val());
    $('.selectpicker').selectpicker('refresh');

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
                'render': function(data, type, row, meta){
                    if(type === 'display'){
                        data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                    }

                   return data;
                },
                'checkboxes': {
                   'selectRow': true,
                   'selectAllRender': '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>'
                },
                'targets': [0]
            }
        ],
        'select': { style: 'multi',  selector: 'td:first-child'},
        'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, "All"]],
        dom: '<"row"lfB>rtip',
        buttons: [
            {
                extend: 'pdf',
                text: '{{trans("file.PDF")}}',
                exportOptions: {
                    columns: ':visible:not(.not-exported)',
                    rows: ':visible'
                },
                action: function(e, dt, button, config) {
                    datatable_sum(dt, true);
                    $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
                    datatable_sum(dt, false);
                },
                footer:true
            },
            {
                extend: 'csv',
                text: '{{trans("file.CSV")}}',
                exportOptions: {
                    columns: ':visible:not(.not-exported)',
                    rows: ':visible'
                },
                action: function(e, dt, button, config) {
                    datatable_sum(dt, true);
                    $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
                    datatable_sum(dt, false);
                },
                footer:true
            },
            {
                extend: 'print',
                text: '{{trans("file.Print")}}',
                exportOptions: {
                    columns: ':visible:not(.not-exported)',
                    rows: ':visible'
                },
                action: function(e, dt, button, config) {
                    datatable_sum(dt, true);
                    $.fn.dataTable.ext.buttons.print.action.call(this, e, dt, button, config);
                    datatable_sum(dt, false);
                },
                footer:true
            },
            {
                extend: 'colvis',
                text: '{{trans("file.Column visibility")}}',
                columns: ':gt(0)'
            }
        ],
        drawCallback: function () {
            var api = this.api();
            datatable_sum(api, false);
        }
    } );

    function datatable_sum(dt_selector, is_calling_first) {
        if (dt_selector.rows( '.selected' ).any() && is_calling_first) {
            var rows = dt_selector.rows( '.selected' ).indexes();

            $( dt_selector.column( 2 ).footer() ).html(dt_selector.cells( rows, 2, { page: 'current' } ).data().sum().toFixed(2));
            $( dt_selector.column( 4 ).footer() ).html(dt_selector.cells( rows, 4, { page: 'current' } ).data().sum().toFixed(2));
            $( dt_selector.column( 5 ).footer() ).html(dt_selector.cells( rows, 5, { page: 'current' } ).data().sum().toFixed(2));
        }
        else {
            $( dt_selector.column( 2 ).footer() ).html(dt_selector.column( 2, {page:'current'} ).data().sum().toFixed(2));
            $( dt_selector.column( 4 ).footer() ).html(dt_selector.column( 4, {page:'current'} ).data().sum().toFixed(2));
            $( dt_selector.column( 5 ).footer() ).html(dt_selector.column( 5, {page:'current'} ).data().sum().toFixed(2));
        }
    }


$(".daterangepicker-field").daterangepicker({
  callback: function(startDate, endDate, period){
    var start_date = startDate.format('YYYY-MM-DD');
    var end_date = endDate.format('YYYY-MM-DD');
    var title = start_date + ' To ' + end_date;
    $(this).val(title);
    $('input[name="start_date"]').val(start_date);
    $('input[name="end_date"]').val(end_date);
  }
});

$('.fc-datepicker').datepicker({
		showOtherMonths: true,
		selectOtherMonths: true
	});

</script>
@endsection