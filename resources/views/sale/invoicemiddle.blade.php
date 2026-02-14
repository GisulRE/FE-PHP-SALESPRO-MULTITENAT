<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" type="image/png" href="{{ url('public/logo', $general_setting->site_logo) }}" />
    <title>Venta Normal - {{ $general_setting->site_title }}</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all,follow">

    <style type="text/css">
        @font-face {
            font-family: 'HelveticaNeueTicket';
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

        .invoice-ticket-container table {
            border-collapse: collapse;
        }

        .invoice-ticket-container tr {
            border-bottom: 1px solid #ccc;
        }

        .invoice-ticket-container tr {
            border-bottom: 1px dotted #ddd;
        }

        .invoice-ticket-container td,
        .invoice-ticket-container th {
            padding: 2px 0;
            width: 10%;
            text-align: start;
        }

        .invoice-ticket-container table {
            width: 95%;
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
                line-height: 20px;
            }

            .invoice-ticket-container td,
            .invoice-ticket-container th {
                padding: 1px 0;
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

        .invoice-ticket-container .left {
            float: left;
            width: 50%;
            text-align: right;
            margin: 0px 5px;
            display: inline;
            text-align: start
        }

        .invoice-ticket-container .right {
            float: left;
            text-align: left;
            margin: 0px 5px;
            display: inline;
            width: 40%
        }

        .invoice-ticket-container .mb-1 {
            margin-bottom: 4px
        }
    </style>
</head>

<body>

    <div class="invoice-ticket-container" style="max-width:90%;margin:0 auto">
        @if (preg_match('~[0-9]~', url()->previous()))
            @php $url = redirect()->to('pos'); @endphp
        @else
            @php $url = url()->previous(); @endphp
        @endif
        <div class="hidden-print">
            <table>
                <tr>
                    <td><a href="{{ route('sale.pos') }}" class="btn btn-info"><i class="fa fa-arrow-left"></i> Volver
                            POS</a> </td>
                    <td><button onclick="window.print();" class="btn btn-primary"><i class="dripicons-print"></i>
                            {{ trans('file.Print') }}</button></td>
                </tr>
            </table>
            <br>
        </div>

        <div id="receipt-data">
            <div class="centered">
                <h1 class="mb-1" style="font-size:20px">NOTA DE ENTREGA</h1> Telf: {{ $lims_warehouse_data->phone }}
            </div>
            <div class="left">
                <div class="left" style="width: 15%;">
                    @if ($general_setting->site_logo)
                        <img src="{{ url('public/logo', $general_setting->site_logo) }}" height="42" width="42"
                            style="margin:10px 0;">
                    @endif
                </div>
                <div class="right" style="width: 60%;">
                    {{ trans('file.customer') }}: {{ $lims_customer_data->name }}
                    <br>{{ trans('file.Address') }}: {{ $lims_customer_data->address }}
                    <br>{{ trans('file.Phone Number') }}: {{ $lims_customer_data->phone_number }}
                </div>
            </div>
            <div class="right">
                <p style="margin-left: 85px;margin-top: 2px;">
                    {{ trans('file.reference') }}: <span
                        style="font-size: large;font-weight: bold;">{{ $lims_sale_data->reference_no }}</span><br>
                    {{ trans('file.Date') }}: {{ \Carbon\Carbon::parse($lims_sale_data->date_sell)->format($lims_sale_data->formato_fecha) }}<br>
                    {{ trans('file.Biller') }}: {{ $lims_biller_data->name }}<br>
                    @if ($lims_sale_data->sale_status == 4)
                        {{ trans('file.Status') }}: {{ trans('file.Receivable') }}<br>
                    @endif
                </p>
            </div>

            <table>
                <thead>
                    <th style="text-align:center">{{ trans('file.Qty') }}</th>
                    <th style="text-align:center">{{ trans('file.Unit') }}</th>
                    <th>{{ trans('file.Description') }}</th>
                    <th style="text-align:center">{{ trans('file.Unit Price') }}</th>
                    <th style="text-align:center">{{ trans('file.Subtotal') }}</th>
                </thead>
                <tbody>
                    @foreach ($lims_product_sale_data as $product_sale_data)
                        @php
                            $lims_product_data = \App\Product::find($product_sale_data->product_id);
                            $lims_unit_data = \App\Unit::find($product_sale_data->sale_unit_id);
                            if (isset($lims_unit_data->unit_code)) {
                                $name_unit = $lims_unit_data->unit_code;
                            } else {
                                $name_unit = 'N/A';
                            }
                            if ($product_sale_data->variant_id) {
                                $variant_data = \App\Variant::find($product_sale_data->variant_id);
                                $product_name = $lims_product_data->name . ' [' . $variant_data->name . ']';
                            } else {
                                $product_name = $lims_product_data->name;
                            }
                        @endphp

                        <tr>
                            <td style="text-align:center">{{ $product_sale_data->qty }}</td>
                            <td style="text-align:center">{{ $name_unit }}</td>
                            <td style="width: 50%; text-align:start">{{ $product_name }}</td>
                            <td style="text-align:right;">
                                {{ number_format((float) ($product_sale_data->total / $product_sale_data->qty), 2, '.', ',') }}
                            </td>
                            <td style="text-align:right;vertical-align:bottom">
                                {{ number_format((float) $product_sale_data->total, 2, '.', ',') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot style="border-collapse: unset;">
                    <tr>
                        <th colspan="3"></th>
                        <th colspan="2" style="text-align:right">&nbsp;&nbsp;&nbsp;{{ trans('file.Total') }} {{ $general_setting->currency }}:
                            {{ number_format((float) $lims_sale_data->total_price, 2, '.', ',') }}</th>
                    </tr>
                    @if ($lims_sale_data->order_tax)
                        <tr>
                            <th colspan="3"></th>
                            <th style="text-align:right">{{ trans('file.Order Tax') }} {{ $general_setting->currency }}:
                                {{ number_format((float) $lims_sale_data->order_tax, 2, '.', ',') }}</th>
                        </tr>
                    @endif
                    @if ($lims_sale_data->order_discount)
                        <tr>
                            <th colspan="4"></th>
                            <th style="text-align:right">{{ trans('file.Order Discount') }} {{ $general_setting->currency }}:
                                {{ number_format((float) $lims_sale_data->order_discount, 2, '.', ',') }}</th>
                        </tr>
                    @endif
                    @if ($lims_sale_data->coupon_discount)
                        <tr>
                            <th colspan="3"></th>
                            <th style="text-align:right">{{ trans('file.Coupon Discount') }} {{ $general_setting->currency }}:
                                {{ number_format((float) $lims_sale_data->coupon_discount, 2, '.', ',') }}</th>
                        </tr>
                    @endif
                    @if ($lims_sale_data->shipping_cost)
                        <tr>
                            <th colspan="3"></th>
                            <th style="text-align:right">{{ trans('file.Shipping Cost') }} {{ $general_setting->currency }}:
                                {{ number_format((float) $lims_sale_data->shipping_cost, 2, '.', ',') }}</th>
                        </tr>
                    @endif
                    <tr>
                        @if ($general_setting->currency_position == 'prefix')
                            <th class="centered" colspan="4">{{ trans('file.In Words') }}:
                                <span>{{ $general_setting->currency }}</span>
                                <span>{{ str_replace('-', ' ', $numberInWords) }} {{$cadenaCentavos}}</span>
                            </th>
                        @else
                            <th class="centered" colspan="4">{{ trans('file.In Words') }}:
                                <span>{{ str_replace('-', ' ', $numberInWords) }} {{$cadenaCentavos}}</span>
                                <span>{{ $general_setting->currency }}</span>
                            </th>
                        @endif
                        <th style="text-align:right">
                            {{ trans('file.grand total') }} {{ $general_setting->currency }}: {{ number_format((float) $lims_sale_data->grand_total, 2, '.', ',') }}
                        </th>
                    </tr>
                </tfoot>
            </table>
            <table>
                <tbody>
                    <tr>
                        <td>
                            <p>.............................</p>
                            <p> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Enc. Almacen&nbsp;&nbsp;</p>
                        </td>
                        <td>
                            <p>.............................</p>
                            <p> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Enc. Venta&nbsp;&nbsp;</p>
                        </td>
                        <td>
                            <p>.............................</p>
                            <p> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Entregado a.&nbsp;&nbsp;</p>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="centered">


            </div>
            <!-- <div class="centered" style="margin:30px 0 50px">
            <small>{{ trans('file.Invoice Generated By') }} {{ $general_setting->site_title }}.
            {{ trans('file.Developed By') }} Gisul S.R.L</strong></small>
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
