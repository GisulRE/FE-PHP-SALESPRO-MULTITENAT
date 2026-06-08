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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

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
        .invoice-ticket-container { overflow-x: hidden; }
        .invoice-ticket-container table { width:100% !important; table-layout:auto; word-break:break-word; }


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
            font-family: 'Nunito', sans-serif;
            font-weight: 600;
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
    @php
        $service_employee_name = null;
        foreach ($lims_product_sale_data as $service_product_sale_data) {
            if ($service_product_sale_data->employee_id) {
                $service_employee_data = \App\Employee::find($service_product_sale_data->employee_id);
                if ($service_employee_data) {
                    $service_employee_name = $service_employee_data->name;
                    break;
                }
            }
        }
    @endphp

    <div class="invoice-ticket-container" style="max-width:400px;margin:0 auto;">
        <div class="hidden-print">
            <table>
                <tr>
                    <td><button onclick="if(window.parent && window.parent.printPreviewFromModal){window.parent.printPreviewFromModal();}else{window.print();}" class="btn btn-primary"><i class="dripicons-print"></i>
                            Imprimir Vista Previa</button></td>
                </tr>
            </table>
            <br>
        </div>

        <div id="receipt-data">
            <div class="centered">
                {{-- @if ($general_setting->site_logo)
                <img src="{{url('public/logo', $general_setting->site_logo)}}" height="42" width="42" style="margin:10px 0;filter: brightness(0);">
            @endif --}}

                <h2 class="contenido">{{ $lims_biller_data->company_name }}</h2>
            </div>
            <div class="grid">
                <strong>{{ trans('file.Address') }}:</strong>
                <span>{{ $lims_warehouse_data->address }}</span>
                <strong>{{ trans('file.Phone Number') }}:</strong>
                <span>{{ $lims_warehouse_data->phone }}</span>
                <strong>{{ trans('file.Date') }}:</strong>
                <span>{{ \Carbon\Carbon::parse($lims_sale_data->date_sell)->format($lims_sale_data->formato_fecha) }}</span>
                <strong>{{ trans('file.reference') }}:</strong>
                <span>{{ $lims_sale_data->reference_no }}</span>
                @if ($service_employee_name)
                    <strong>Servicio por:</strong>
                    <span>{{ $service_employee_name }}</span>
                @endif
                <strong>{{ trans('file.customer') }}:</strong>
                <span>{{ $lims_customer_data->name }}</span>
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
                            if (!empty($product_sale_data->description)) {
                                $product_name .= ' - ' . $product_sale_data->description;
                            }
                        @endphp
                        <tr>
                            <td style="text-align: center;width: 12% !important;vertical-align:top">
                                {{ $product_sale_data->qty }} </td>
                            <td colspan="5" style="text-align: left;vertical-align:top;">{{ $product_name }}</td>
                            <td style="text-align: center;vertical-align:top;width: 10%">
                                {{ number_format((float) ($product_sale_data->total / $product_sale_data->qty), 2, '.', ',') }}
                            </td>
                            <td colspan="4" style="text-align:center;vertical-align:top">
                                {{ number_format((float) $product_sale_data->total, 2, '.', ',') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="10">{{ trans('file.Total') }} {{ $general_setting->currency }}:</th>
                        <th style="text-align:center">
                            {{ number_format((float) $lims_sale_data->total_price, 2, '.', ',') }}</th>
                    </tr>
                    @if ($lims_sale_data->order_tax)
                        <tr>
                            <th colspan="10">{{ trans('file.Order Tax') }} {{ $general_setting->currency }}:</th>
                            <th style="text-align:center">
                                {{ number_format((float) $lims_sale_data->order_tax, 2, '.', ',') }}</th>
                        </tr>
                    @endif
                    @if ($lims_sale_data->order_discount)
                        <tr>
                            <th colspan="10">{{ trans('file.Order Discount') }} {{ $general_setting->currency }}:
                            </th>
                            <th style="text-align:center">
                                {{ number_format((float) $lims_sale_data->order_discount, 2, '.', ',') }}</th>
                        </tr>
                    @endif
                    @if ($lims_sale_data->coupon_discount)
                        <tr>
                            <th colspan="10">{{ trans('file.Coupon Discount') }} {{ $general_setting->currency }}:
                            </th>
                            <th style="text-align:center">
                                {{ number_format((float) $lims_sale_data->coupon_discount, 2, '.', ',') }}</th>
                        </tr>
                    @endif
                    @if ($lims_sale_data->shipping_cost)
                        <tr>
                            <th colspan="10">{{ trans('file.Shipping Cost') }} {{ $general_setting->currency }}:
                            </th>
                            <th style="text-align:center">
                                {{ number_format((float) $lims_sale_data->shipping_cost, 2, '.', ',') }}</th>
                        </tr>
                    @endif
                    @if ($lims_sale_data->total_tips > 0)
                        <tr>
                            <th colspan="10">Propinas {{ $general_setting->currency }}:</th>
                            <th style="text-align:center">
                                {{ number_format((float) $lims_sale_data->total_tips, 2, '.', ',') }}</th>
                        </tr>
                    @endif
                    <tr>
                        <th colspan="10">{{ trans('file.grand total') }} {{ $general_setting->currency }}:</th>
                        <th style="text-align:center">
                            {{ number_format((float) $lims_sale_data->grand_total, 2, '.', ',') }}</th>
                    </tr>
                    <tr>
                        @if ($general_setting->currency_position == 'prefix')
                            <th class="centered" colspan="10">{{ trans('file.In Words') }}:
                                <span>{{ $general_setting->currency }}</span>
                                <span>{{ str_replace('-', ' ', $numberInWords) }} {{ $cadenaCentavos }}</span>
                            </th>
                        @else
                            <th class="centered" colspan="10">{{ trans('file.In Words') }}:
                                <span>{{ str_replace('-', ' ', $numberInWords) }} {{ $cadenaCentavos }}</span>
                                <span>{{ $general_setting->currency }}</span>
                            </th>
                        @endif
                    </tr>
                </tfoot>
            </table>
            <table>
                <tbody>
                    @if ($lims_sale_data->sale_status == '4')
                        <tr style="">
                            <td style="padding: 5px;width:40%">Venta Por Cobrar</td>
                            <td style="padding: 5px;width:60%"> {{ __('file.Amount') }} Deuda:
                                {{ number_format((float) $lims_sale_data->grand_total, 2, '.', ',') }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 5px;width:40%">Facturador:</td>
                            <td style="padding: 5px;width:60%">{{ $lims_biller_data->name }}</td>
                        </tr>
                        <tr>
                            <td class="centered" colspan="3">
                                {{ __('file.Thank you for shopping with us. Please come again') }}</td>
                        </tr>
                    @else
                        @foreach ($lims_payment_data as $payment_data)
                            <tr style="background-color:#ddd;">
                                <td style="padding: 5px;width:30%">{{ trans('file.Paid By') }}:
                                    {{ str_replace('_', ' ', $payment_data->paying_method) }}</td>
                                <td style="padding: 5px;width:40%">{{ trans('file.Amount') }}:
                                    {{ number_format((float) $payment_data->amount, 2, '.', ',') }}</td>
                                <td style="padding: 5px;width:30%">{{ trans('file.Change') }}:
                                    {{ number_format((float) $payment_data->change, 2, '.', ',') }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px;width:30%">Facturador:</td>
                                <td style="padding: 5px;width:40%">{{ $lims_biller_data->name }}</td>
                                <td style="padding: 5px;width:30%"></td>
                            </tr>
                            <tr>
                                <td class="centered" colspan="3">
                                    {{ trans('file.Thank you for shopping with us. Please come again') }}</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
            <!-- <div class="centered" style="margin:30px 0 50px">
            <small>{{ trans('file.Invoice Generated By') }} {{ $general_setting->site_title }}.
            {{ trans('file.Developed By') }} Gisul S.R.L</strong></small>
        </div> -->
        </div>
    </div>

    <script type="text/javascript">
        @if (!isset($is_preview_mode) || !$is_preview_mode)
        function auto_print() {
            window.print()
            //window.history.go(-1);
            //window.history.back();
            window.location.assign("{{ route('sale.pos') }}");
        }
        setTimeout(auto_print, 1000);
        @endif
    </script>

</body>

</html>
