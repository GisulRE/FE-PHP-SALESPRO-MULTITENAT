<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" type="image/png" href="{{ url('public/logo', $general_setting->site_logo) }}" />
    <title>{{ $general_setting->site_title }}</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all,follow">

    <style type="text/css">
        @font-face {
            font-family: 'HelveticaNeueTicket';
            /* font-style: normal; */
            src:
                local('Helvetica Neue'),
                url("../../public/fonts/HelveticaNeueLTStdCn.ttf")
        }

        .invoice-ticket-container,
        .invoice-ticket-container * {
            font-size: 12px;
            line-height: 16px;
            font-family: 'HelveticaNeueTicket', sans-serif;
            text-transform: capitalize;
        }


        .invoice-ticket-container .contenido {
            text-transform: uppercase;
        }

        .invoice-ticket-container .btn {
            padding: 7px 10px;
            text-decoration: none;
            border: none;
            display: block;
            text-align: center;
            margin: 7px;
            cursor: pointer;
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
        }

        .invoice-ticket-container tr {
            border-bottom: 1px dotted #ddd;
        }

        .invoice-ticket-container td,
        .invoice-ticket-container th {
            padding: 0px 0;
            width: 50%;
        }

        .invoice-ticket-container table {
            width: 100%;
            margin: 8px 0;
        }

        .invoice-ticket-container .grid {
            display: grid;
            grid-template-columns: 35% 65%
        }

        .invoice-ticket-container tfoot tr th:first-child {
            text-align: left;
        }

        .invoice-ticket-container .centered {
            text-align: center;
            align-content: center;
        }

        .invoice-ticket-container small {
            font-size: 11px;
        }

        @media print {
            .invoice-ticket-container,
            .invoice-ticket-container * {
                font-size: 12px;
                line-height: 16px;
            }

            .invoice-ticket-container td,
            .invoice-ticket-container th {
                padding: 0px 0;
            }

            .invoice-ticket-container .hidden-print {
                display: none !important;
            }

            @page {
                margin: 0;
            }

            body {
                margin: 0.5cm;
                margin-bottom: 1.6cm;
            }
        }
    </style>
</head>

<body>

    <div class="invoice-ticket-container" style="max-width:400px;margin:0 auto">
        @if (preg_match('~[0-9]~', url()->previous()))
            @php $url = '../../prepos'; @endphp
        @else
            @php $url = url()->previous(); @endphp
        @endif
        <div class="hidden-print">
            <table>
                <tr>
                    <td><a href="{{ $url }}" class="btn btn-info"><i class="fa fa-arrow-left"></i>
                            Volver Atras</a> </td>
                    <td><button onclick="window.print();" class="btn btn-primary"><i class="dripicons-print"></i>
                            {{ trans('file.Print') }}</button></td>
                </tr>
            </table>
            <br>
        </div>

        <div id="receipt-data">
            <div class="centered">
                {{-- @if ($general_setting->site_logo)
                <img src="{{url('public/logo', $general_setting->site_logo)}}" height="42" width="42" style="margin:10px 0;filter: brightness(0);">
            @endif --}}

                <h2 class="contenido">{{ $general_setting->site_title }}</h2>
            </div>
            <div class="grid">
                <strong>{{ trans('file.Date') }}/Hora:</strong>
                <span>{{ date(config('date_format'), strtotime($lims_sale_data->created_at)) }} {{ date('H:i:s', strtotime($lims_sale_data->created_at)) }}</span>
                @if ($lims_sale_data->attentionshift_id)
                    <strong>{{ trans('file.Attention Shift Nro') }}:</strong>
                    <span>{{ $lims_sale_data->attentionshift->reference_nro }}</span>
                @endif
                <strong>{{ trans('file.reference presale') }}:</strong>
                <span>{{ $lims_sale_data->reference_no }}</span>
                <strong>{{ trans('file.customer') }}:</strong>
                @if ($lims_sale_data->attentionshift->customer_name)
                    <span>{{ $lims_sale_data->attentionshift->customer_name }}</span>
                @else
                    <span>{{ $lims_customer_data->name }}</span>
                @endif
                <strong>{{ trans('file.Employee Service') }}:</strong>
                @php
                    if ($lims_sale_data->employee_id != null) {
                        $employee = \App\Employee::find($lims_sale_data->employee_id);
                        $name = $employee->name;
                    } else {
                        $name = 'Sin Empleado Asignado';
                    }
                @endphp
                <span>{{ $name }}</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 12% !important;text-align: center">Cant.</th>
                        <th colspan="5" style="text-align: left">Detalle</th>
                        <th style="text-align: center;width: 10%">P/U</th>
                        <th colspan="4" style="text-align: center">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lims_product_sale_data as $product_sale_data)
                        @php
                            $lims_product_data = \App\Product::find($product_sale_data->product_id);
                            if ($product_sale_data->variant_id) {
                                $variant_data = \App\Variant::find($product_sale_data->variant_id);
                                $product_name = $lims_product_data->name . ' [' . $variant_data->name . ']';
                            } else {
                                $product_name = $lims_product_data->name;
                            }
                        @endphp
                        <tr>
                            <td style="text-align: center;width: 12% !important;vertical-align:top">
                                {{ $product_sale_data->qty }} </td>
                            <td colspan="5" style="text-align: left;vertical-align:top;">{{ $product_name }}</td>
                            <td style="text-align: center;vertical-align:top;width: 10%">
                                {{ number_format((float) ($product_sale_data->total / $product_sale_data->qty), 2, '.', '') }}
                            </td>
                            <td colspan="4" style="text-align:center;vertical-align:top">
                                {{ number_format((float) $product_sale_data->total, 2, '.', '') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    @if ($lims_sale_data->order_tax)
                        <tr>
                            <th colspan="10">{{ trans('file.Order Tax') }}</th>
                            <th style="text-align:center">
                                {{ number_format((float) $lims_sale_data->order_tax, 2, '.', '') }}</th>
                        </tr>
                    @endif
                    <tr>
                        <th colspan="10">{{ trans('file.grand total') }} Pre-Venta</th>
                        <th style="text-align:center">
                            {{ number_format((float) $lims_sale_data->grand_total, 2, '.', '') }}</th>
                    </tr>
                </tfoot>
            </table>
            <table>
                <tbody>
                </tbody>
            </table>
            <!-- <div class="centered" style="margin:30px 0 50px">
            <small>{{ trans('file.Invoice Generated By') }} {{ $general_setting->site_title }}.
            {{ trans('file.Developed By') }} LionCoders</strong></small>
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
