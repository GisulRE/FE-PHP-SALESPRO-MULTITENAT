@extends('layout.main') @section('content')

<section class="forms">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header mt-2">
                <h3 class="text-center">Reporte General + Utilidades</h3>
            </div>
                <div class="row mb-12">
                    <div class="col-md-5 mt-3" style="margin-left: 3.333%;">
                        <div class="form-group">
                            <label class="d-tc mt-2"><strong>{{trans('file.Choose Your Date')}}</strong> &nbsp;</label>
                            <div class="d-tc">
                                <div class="input-group">
                                    <input id="start_date" name="start_date" class="form-control" placeholder="DD/MM/YYYY" type="date" value="{{$start_date}}" onchange="consultar()" required>
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <i class="dripicons-calendar tx-10 lh-0 op-4"></i>
                                        </div>
                                    </div>
                                    <label class="d-tc mt-2" style="margin-left: 5px"><strong>  A </strong> &nbsp;</label>
                                    <input id="end_date" name="end_date" class="form-control" placeholder="DD/MM/YYYY" type="date" value="{{$end_date}}" onchange="consultar()" required>
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <i class="dripicons-calendar tx-10 lh-0 op-4"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mt-3" style="margin-left: 3.333%;">
                        <div class="form-group">
                            <label class="d-tc mt-2"><strong>{{trans('file.Choose Category')}}</strong> &nbsp;</label>
                            <div class="d-tc">
                                <input type="hidden" name="category_id_hidden" value="{{$category_id}}" />
                                <select id="category_id" name="category_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" onchange="consultar()">
                                    <option value="0">{{trans('file.All Category')}}</option>
                                    @foreach($lims_categorie_list as $category)
                                    <option value="{{$category->id}}">{{$category->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mt-3" style="margin-left: 3.333%; display: none">
                        <div class="form-group row">
                            <label class="d-tc mt-2"><strong>{{trans('file.Choose Biller')}}</strong> &nbsp;</label>
                            <div class="d-tc">
                                <input type="hidden" name="biller_id_hidden" value="{{$biller_id}}" />
                                <select id="biller_id" name="biller_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" onchange="consultar()">
                                    <option value="0">Todos los Vendedores</option>
                                    @foreach($lims_biller_list as $biller)
                                    <option value="{{$biller->id}}">{{$biller->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1 offset-md-0 mt-5">
                        <div class="form-group">
                            <a id="consultabtn" class="btn btn-primary" href="#">{{trans('file.submit')}}</a>
                        </div>
                    </div>
                    <div class="col-md-1 offset-md-0 mt-5">
                        <button id="print-btn" type="button" class="btn btn-default btn-sm" onclick="print()"><i class="dripicons-print"></i> {{trans('file.Print')}}</button>
                    </div>
                </div>
        </div>
    <div id="tbs_prints">
    <div id="tb_ingresos" class="table-responsive mb-4">
        <h4 style="color: green">INGRESOS VENTAS</h4>
        <table id="resume-table" class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Categoria</th>
                            <th>Metodo Pago</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($ingresosList != null)
                            @foreach($ingresosList as $key=>$paying)
                                @foreach($paying as $key1=>$pay)
                                <tr>
                                    <td class="center">{{$key1}}</td>
                                    <td class="center">{{$paying[$key1]['name']}} : </td>
                                    <td class="center">{{$paying[$key1]['paying_method']}} </td>
                                    <td class="center">{{number_format((float)$paying[$key1]['total'], 2, '.', '')}}</td>
                                </tr>
                                @endforeach
                            @endforeach
                        @endif
                    </tbody>
                    <tfoot class="tfoot active">
                        <th></th>
                        <th></th>
                        <th>Total Ingresos :</th>
                        <th>{{number_format((float)$total_ingreso, 2, '.', '')}}</th>
                    </tfoot>
        </table>
    </div>
    <div id="tb_costos" class="table-responsive mb-4">
        <h4 style="color: rgb(94, 18, 216)">COSTOS</h4>
        <table id="costos-table" class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Categoria</th>
                            <th>Metodo Pago</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($costosList != null)
                            @foreach($costosList as $key=>$paying)
                                @foreach($paying as $key1=>$pay)
                                <tr>
                                    <td class="center">{{$key1}}</td>
                                    <td class="center">{{$paying[$key1]['name']}} : </td>
                                    <td class="center">{{$paying[$key1]['paying_method']}} </td>
                                    <td class="center">{{number_format((float)$paying[$key1]['total'], 2, '.', '')}}</td>
                                </tr>
                                @endforeach
                            @endforeach
                        @endif
                    </tbody>
                    <tfoot class="tfoot active">
                        <th></th>
                        <th></th>
                        <th>Total Costo :</th>
                        <th>{{number_format((float)$total_costo, 2, '.', '')}}</th>
                    </tfoot>
        </table>
    </div>
    <div class="table-responsive mb-4">
        <h4 style="color: red">GASTOS</h4>
        <table id="egreso-table" data-page-length="-1" class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nro. Items</th>
                    <th>Categoria</th>
                    <th>Descripcion</th>
                    <th>Monto Bs.</th>
                </tr>
            </thead>
            <tbody>
                @if($egresosList != null)
                @foreach($egresosList as $key=>$account)
                    @foreach($account as $key1=>$data)
                    <tr>
                        <td class="center">{{$key1+1}}</td>
                        <td class="center">{{$egresosList[$key][$key1]['reference']}}</td>
                        <td>{{$egresosList[$key][$key1]['categorie']}}</td>
                        <td>{{$egresosList[$key][$key1]['detail']}}</td>
                        <td class="center">{{number_format((float)$egresosList[$key][$key1]['amount'], 2, '.', '')}}</td>
                    </tr>
                    @endforeach
                @endforeach
                @endif
            </tbody>
            <tfoot class="tfoot active">
                <th></th>
                <th></th>
                <th></th>
                <th>Total Gastos</th>
                <th></th>
            </tfoot>
        </table>
    </div>
    <div class="table-responsive mb-4">
        <h4 style="color: blue">RESUMEN</h4>
        <table id="total-table" class="table table-hover">
            <thead>
            </thead>
            <tbody>
                <tr>
                    <td style="font-weight: bold;"> TOTAL INGRESOS (I): </td>
                    <td class="center" style="font-weight: bold;"><span id="totalingresos">0.00</span> Bs.</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;"> TOTAL COSTOS (C): </td>
                    <td class="center" style="font-weight: bold;"><span id="totalcostos">0.00</span> Bs.</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;"> TOTAL UTILIDADES (U) (I - C): </td>
                    <td class="center" style="font-weight: bold;"><span id="totalutilidad">0.00</span> Bs.</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;"> TOTAL GASTOS (G): </td>
                    <td class="center" style="font-weight: bold;"><span id="totalegresos">0.00</span> Bs.</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;color:teal"> TOTAL GENERAL (I - G): </td>
                    <td class="center" style="font-weight: bold;"><span id="totalgral">0.00</span> Bs.</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;color:green"> TOTAL UTILIDAD NETA ( (I - C) - G) : </td>
                    <td class="center" style="font-weight: bold;"><span id="totalutilneto">0.00</span> Bs.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div id="print-report" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="demo modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <h5 id="modal_header" class="modal-title">Reporte General + Utilidades</h5>&nbsp;&nbsp;
              <button id="print-btn" type="button" class="btn btn-default btn-sm" onclick="printJS('barcode', 'html')"><i class="dripicons-print"></i> {{trans('file.Print')}}</button>
              <button type="button" id="close-btn" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
            </div>
            <div id="report_gral" class="modal-body">
                <div id="report_print">
                    <div class="card-header mt-2">
                        <h1 id="title_g">REPORTE GENERAL + UTILIDAD</h1>
                        <h2 id="title" class="text-center">{{$general_setting->site_title}}</h2>
                        <p class="text-center"><span class="bold">Rango de Fecha: </span> {{date('d-m-Y', strtotime($start_date))}} <span class="bold">Al</span> {{date('d-m-Y', strtotime($end_date))}}  <span class="bold"> Categoria: </span><span id="categ_print"></span></p>
                    </div>
                    <h4 id="ing_title" style="color: green">INGRESOS</h4>
                    <div id="tb_ingresos_total" class="table-responsive mb-4"></div>
                    <h4 id="ing_title" style="color: rgb(94, 18, 216)">COSTOS</h4>
                    <div id="tb_costos_total" class="table-responsive mb-4"></div>
                    <br><h4 id="egr_title" style="color: red">GASTOS</h4><br>
                    <div id="tb_egresos_detalle" class="table-responsive mb-4"></div>
                    <br><h4 id="totales_res" style="color: blue">RESUMEN</h4><br>
                    <div id="tb_totales_ig" class="table-responsive mb-4"></div>
                </div>
            </div>
        </div>
    </div>
</div>
</section>

<script type="text/javascript">

    $("ul#report").siblings('a').attr('aria-expanded','true');
    $("ul#report").addClass("show");
    $("ul#report #general-util-report-menu").addClass("active");
    //var btn = document.getElementById("biller_id");
    //btn.disabled = true;
    $('#category_id').val($('input[name="category_id_hidden"]').val());
    $('#biller_id').val($('input[name="biller_id_hidden"]').val());
    $('.selectpicker').selectpicker('refresh');
    var saldoant = 0;
    var totaling = <?php echo $total_ingreso ?>;
    var totalieg = <?php echo $total_egreso ?>;
    var totalcost = <?php echo $total_costo ?>;
    var totaltarjcred = 0;
    var totalqr = 0;
    var totalcheq = 0;
    var totaldep = 0;
    var totalvale = 0;
    var totalajusteing = 0;
    var totalcompras = 0;
    var totaltranfs = 0;
    var totalnominas = 0;
    var totalgastos = 0;
    var totaldevols = 0;
    var totalajustegr = 0;
    var totalgeneral = totaling - totalieg;
    var totalutilidad = totaling - totalcost;
    var totalutilneto = (totaling - totalcost) - totalieg;
    console.log("Total I-E : " + totalgeneral);

    $('#totalcomp').text(totalcompras.toFixed(2));
    $('#totaltrans').text(totaltranfs.toFixed(2));
    $('#totalgast').text(totalgastos.toFixed(2));
    $('#totalnomi').text(totalnominas.toFixed(2));
    $('#totaldevol').text(totaldevols.toFixed(2));
    $('#totalingresos').text(totaling.toFixed(2));
    $('#totalegresos').text(totalieg.toFixed(2));
    $('#totalcostos').text(totalcost.toFixed(2));
    $('#totalutilidad').text(totalutilidad.toFixed(2));
    $('#totalgral').text(totalgeneral.toFixed(2));
    $('#totalutilneto').text(totalutilneto.toFixed(2));
    consultar();
    var table = $('#egreso-table').DataTable( {
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
                "orderable": true,
                'targets': 0
            }
        ],
        'select': { style: 'multi',  selector: 'td:first-child'},
        'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, "All"]],
        //'iDisplayLength: -1',
        dom: '<"row"lfB>rtip',
        drawCallback: function () {
            var api = this.api();
            datatable_sum(api, false);
        }
    } );
    $('.dt-buttons').hide();
    function datatable_sum(dt_selector, is_calling_first) {
        if (dt_selector.rows( '.selected' ).any() && is_calling_first) {
            var rows = dt_selector.rows( '.selected' ).indexes();
            $( dt_selector.column( 4 ).footer() ).html(dt_selector.cells( rows, 4, { page: 'current' } ).data().sum().toFixed(2));
        }
        else {
            $( dt_selector.column( 4 ).footer() ).html(dt_selector.cells( rows, 4, { page: 'current' } ).data().sum().toFixed(2));
        }
    }

    function consultar(){
        var start = $('#start_date').val();
        var end = $('#end_date').val();
        var cat = $('#category_id').val();
        var bill = $('#biller_id').val();
        var url = '<?php echo url('/'); ?>' + '/report/generalutil_report/'+start+'/'+end+'/'+cat+'/'+bill;
        $("#consultabtn").attr("href", url)
    }

    function print(){
        var categ = $('#category_id').find(":selected").text();
        var biller = $('#biller_id').find(":selected").text();
        var div_1 = $('#resume-table').clone();
        var div_2 = $('#costos-table').clone();
        var div_3 = $('#egreso-table').clone();
        var div_4 = $('#total-table').clone();
        
        $('#tb_ingresos_total').html(div_1);
        $('#tb_costos_total').html(div_2);
        $('#tb_egresos_detalle').html(div_3);
        $('#tb_totales_ig').html(div_4);
        //$('#print-report').modal('show');
        $('#categ_print').text(categ);
        $('#biller_print').text(biller);
        printJS({ printable: 'report_print', type: 'html',
        style: '#title, #title_g { text-align: center !important;} #ing_title{color: green} #egr_title{color: red} #totales_res{color: blue}' + 
        '.center{text-align: center;} .table, .table thead th, .table td{border: 2px solid #3971A5;} .bold{font-weight: bold;}'
        });
        //$('#report_print').hide();
    }

</script>
@endsection