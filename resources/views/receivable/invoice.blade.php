<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" type="image/png" href="{{url('public/logo', $general_setting->site_logo)}}" />
    <title>{{$general_setting->site_title}}</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all,follow">

    <style type="text/css">
            .invoice-ticket-container,
            .invoice-ticket-container * {
            font-size: 14px;
            line-height: 24px;
            font-family: 'Ubuntu', sans-serif;
            text-transform: capitalize;
        }

        .invoice-ticket-container h1{
            font-size: 22px;
        }
        .invoice-ticket-container h2{
            font-size: 18px;
        }
        .invoice-ticket-container .btn {
            padding: 7px 10px;
            text-decoration: none;
            border: none;
            display: block;
            text-align: center;
            margin: 7px;
            cursor:pointer;
        }

        .invoice-ticket-container .btn-info {
            background-color: #999;
            color: #FFF;
        }

        .invoice-ticket-container .btn-primary {
            background-color: #6449e7;
            color: #FFF;
            width: 100%;
        }
        .invoice-ticket-container td,
        .invoice-ticket-container th,
        .invoice-ticket-container tr,
        .invoice-ticket-container table {
            border-collapse: collapse;
            color: rgb(36, 33, 33);  border: 2px solid #3971A5;
        }

        .invoice-ticket-container table {width: 100%;}
        .invoice-ticket-container tfoot tr th:first-child {text-align: left;}

        .invoice-ticket-container .centered {
            text-align: center;
            align-content: center;
        }
        .invoice-ticket-container small{font-size:11px;}
        @media print {
            .invoice-ticket-container,
            .invoice-ticket-container * {
                font-size:12px;
                line-height: 20px;
            }
            .invoice-ticket-container td,.invoice-ticket-container th {padding: 5px 0;}
            .invoice-ticket-container .hidden-print {
                display: none !important;
            }
            @page { margin: 0; } body { margin: 0.5cm; margin-bottom:1.6cm; } 
        }
    </style>
  </head>
<body>

<div class="invoice-ticket-container">
    <div class="hidden-print">

        <button onclick="window.print();" class="btn btn-primary"><i class="dripicons-print"></i> {{trans('file.Print')}}</button>
        <br>
    </div>
        
    <div id="receipt-data">
        <div class="centered">
            
            <h1>Reporte Ventas Pagados - Cuentas Por Cobrar</h1>
            <h2>{{$general_setting->site_title}}</h2>
            
        </div>
        <strong>Fecha/Hora: </strong>{{date($general_setting->date_format, strtotime($lims_receivable_data->created_at->toDateString())) . ' '. $lims_receivable_data->created_at->toTimeString() }} <br>
        <strong>Total Pagado: </strong>{{number_format($lims_receivable_data->amount, 2)}}
        <table id="sale-table" class="table sale-list" style="width: 100%;">
            <thead>
                <tr>
                    <th>{{trans('file.Date')}}</th>
                    <th>{{trans('file.reference')}}</th>
                    <th>{{trans('file.Biller')}}</th>
                    <th>{{trans('file.customer')}}</th>
                    <th>{{trans('file.Sale Status')}}</th>
                    <th>{{trans('file.Payment Status')}}</th>
                    <th>{{trans('file.grand total')}}</th>
                    <th>Cobrado</th>
                    <th>Por Cobrar</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $key=>$sale)
                <tr class="sale-link" data-id="{{$sale->id}}">
                    <td style="text-align:center">{{ date($general_setting->date_format, strtotime($sale->date_sell))}}</td>
                    <td style="text-align:center">{{ $sale->reference_no }}</td>
                    <td style="text-align:center">{{ $sale->name }}</td>
                    <td style="text-align:center">{{ $sale->customer }}</td>
                    @if($sale->sale_status == 1)
                        <td style="text-align:center"><div class="badge badge-success">{{trans('file.Completed')}}</div></td>
                    @elseif($sale->sale_status == 4)
                        <td style="text-align:center"><div class="badge badge-info">{{trans('file.Receivable')}}</div></td>
                    @else
                        <td style="text-align:center"><div class="badge badge-danger">{{trans('file.Pending')}}</div></td>
                    @endif
                    @if($sale->payment_status == 1)
                        <td style="text-align:center"><div class="badge badge-danger">{{trans('file.Pending')}}</div></td>
                    @elseif($sale->payment_status == 2)
                        <td style="text-align:center"><div class="badge badge-danger">{{trans('file.Due')}}</div></td>
                    @elseif($sale->payment_status == 3)
                        <td style="text-align:center"><div class="badge badge-warning">{{trans('file.Partial')}}</div></td>
                    @else
                        <td style="text-align:center"><div class="badge badge-success">{{trans('file.Paid')}}</div></td>
                    @endif
                    <td style="text-align:right">{{number_format($sale->grand_total, 2)}}</td>
                    <td style="text-align:right">{{number_format($sale->paid_amount, 2)}}</td>
                    <td style="text-align:right">{{number_format($sale->grand_total - $sale->paid_amount, 2)}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <!-- <div class="centered" style="margin:30px 0 50px">
            <small>{{trans('file.Invoice Generated By')}} {{$general_setting->site_title}}.
            {{trans('file.Developed By')}} LionCoders</strong></small>
        </div> -->
    </div>
</div>

<script type="text/javascript">
    function auto_print() {     
        window.print()
    }
    setTimeout(auto_print, 1000);
</script>

</body>
</html>