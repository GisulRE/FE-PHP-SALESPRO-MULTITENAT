@extends('layout.main') @section('content')
@if(empty($lotes_list))
<div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{'No existe datos en este rango de fechas y producto!'}}</div>
@endif
@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div> 
@endif

<section class="forms">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header mt-2">
                <h3 class="text-center">{{trans('file.Alert of Lotes')}}</h3>
            </div>
            <div class="row mb-12">
                <div class="col-md-2 mt-2"></div>
                <div class="col-md-3 mt-2">
                    <div class="form-group row">
                        <label class="d-tc mt-2"><strong>{{trans('file.Choose Filter')}}</strong> &nbsp;</label>
                        <div class="d-tc">
                            <input type="hidden" name="filter_hidden" value="{{$filter}}" />
                            <select id="filter" name="filter" class="selectpicker form-control" onchange="consultar()">
                                <option value="0">Por Expirar</option>
                                <option value="1">Expirados</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mt-2">
                    <div class="form-group row">
                        <label class="d-tc mt-2"><strong>Filtro Dias</strong> &nbsp;</label>                       
                         <div class="d-tc" style="width: 60%;">
                            <input id="days" type="number" name="alert_expiration" value="{{$days}}" min="0" max="1800" step="15" onchange="consultar()"/>
                            
                        </div>
                    </div>
                </div>
                <div class="col-md-1 offset-md-0 mt-2">
                    <div class="form-group">
                        <a id="consultabtn" class="btn btn-primary" href="#">{{trans('file.submit')}}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="table-responsive mb-4">
        <table id="report-table" class="table table-hover">
            <thead>
                <tr>
                    <th class="not-exported"></th>
                    <th>{{trans('file.name')}}</th>
                    <th style="width: 30% !important">{{trans('file.name')}} {{trans('file.product')}}</th>
                    <th>Total</th>
                    <th>{{trans('file.In Stock')}}</th>
                    <th>{{trans('file.Expiration')}}</th>
                    <th>{{trans('file.Status')}}</th>
                </tr>
            </thead>
            <tbody>
                @if(!empty($lotes_list))
                @foreach($lotes_list as $key => $lote)
                <tr>
                    <td>{{$key}}</td>
                    <td>{{$lote->name}}</td>
                    <td style="width: 30% !important">{{$lote->product}}</td>
                    <td>{{$lote->qty}}</td>
                    <td>{{$lote->stock}}</td>
                    <td>{{date($general_setting->date_format, strtotime($lote->expiration))}}</td>
                    @if ($lote->status == 0)
                        <td><div class="badge badge-danger">Expirado/Baja</div></td>
                    @else
                        <td><div class="badge badge-warning">En alerta x expirar</div></td> 
                    @endif
                </tr>
                @endforeach
                @endif
            </tbody>
        </table>
    </div>
</section>

<script type="text/javascript">
    consultar()
    $("ul#report").siblings('a').attr('aria-expanded','true');
    $("ul#report").addClass("show");
    $("ul#report #loteAlert-report-menu").addClass("active");

    $('#filter').val($('input[name="filter_hidden"]').val());
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
            },
            {
                extend: 'csv',
                text: '{{trans("file.CSV")}}',
                exportOptions: {
                    columns: ':visible:not(.not-exported)',
                    rows: ':visible'
                },
            },
            {
                extend: 'print',
                text: '{{trans("file.Print")}}',
                exportOptions: {
                    columns: ':visible:not(.not-exported)',
                    rows: ':visible'
                },
            },
            {
                extend: 'colvis',
                text: '{{trans("file.Column visibility")}}',
                columns: ':gt(0)'
            }
        ],
        drawCallback: function () {
            var api = this.api();
            
        }
    } );
    $("input[name='alert_expiration']").inputSpinner();
    
    function consultar(){
        var filter = $('#filter').val();
        var days = $('#days').val();
        var url = '<?php echo url('/'); ?>' + '/report/alert_expiration/'+filter+'/'+days;
        $("#consultabtn").attr("href", url)
    }
</script>
@endsection