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
        .invoice-ticket-container,
        .invoice-ticket-container * {
            font-size: 12px;
            line-height: 1.3;
            font-family: sans-serif;
            text-transform: capitalize;
        }

        .invoice-ticket-container .contenido {
            text-transform: uppercase;
        }

        .invoice-ticket-container td,
        .invoice-ticket-container th,
        .invoice-ticket-container tr,
        .invoice-ticket-container table {
            border-collapse: collapse;
        }



        .invoice-ticket-container table {
            width: 100%;
            margin: 8px 0;
        }

        .invoice-ticket-container tfoot tr th:first-child {
            text-align: left;
        }

        .invoice-ticket-container .centered {
            text-align: center;
            align-content: center;
        }

        .invoice-ticket-container td.columna1 {}

        .invoice-ticket-container td.columna2 {}

        .invoice-ticket-container td.columna3 {
            padding-right: 2px;
        }

        .invoice-ticket-container td.columna4 {}
    </style>
</head>

<body>

    <div class="invoice-ticket-container">
        <div class="centered">
            <h2 class="contenido">{{ $lims_biller_data->company_name }}</h2>
        </div>
        <table>
            <tbody>
                <tr>
                    <td style="vertical-align:top">
                        <strong>{{ __('file.Address') }}:</strong>
                    </td>
                    <td>
                        <span>{{ $lims_warehouse_data->address }}</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>{{ __('file.Phone Number') }}:</strong>
                    </td>
                    <td>
                        <span>{{ $lims_warehouse_data->phone }}</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>{{ __('file.Date') }}:</strong>
                    </td>
                    <td>
                        <span>{{ \Carbon\Carbon::parse($lims_sale_data->date_sell)->format($lims_sale_data->formato_fecha) }}</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>{{ __('file.reference') }}:</strong>
                    </td>
                    <td>
                        <span>{{ $lims_sale_data->reference_no }}</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>{{ __('file.Biller') }}:</strong>
                    </td>
                    <td>
                        <span>{{ $lims_biller_data->name }}</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>{{ __('file.customer') }}:</strong>
                    </td>
                    <td>
                        <span>{{ $lims_customer_data->name }}</span>
                    </td>
                </tr>
            </tbody>
        </table>

        <table>
            <thead>
                <tr>
                    <th style="text-align: center">Cant.</th>
                    <th style="text-align: left">Detalle</th>
                    <th style="text-align: center">P/U</th>
                    <th style="text-align: center">Subtotal</th>
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
                        <td class="columna1" style="text-align: center;vertical-align:top">
                            {{ $product_sale_data->qty }}
                        </td>
                        <td class="columna2" style="text-align: left;vertical-align:top;">{{ $product_name }}</td>
                        <td class="columna3" style="vertical-align:top; text-align: right">
                            {{ number_format((float) ($product_sale_data->total / $product_sale_data->qty), 2, '.', ',') }}
                        </td>
                        <td class="columna4" style="vertical-align:top; text-align: right">
                            {{ number_format((float) $product_sale_data->total, 2, '.', ',') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" style="text-align:right">{{ __('file.Total') }}
                        {{ $general_setting->currency }}:</th>
                    <th colspan="3" style="text-align:right">
                        {{ number_format((float) $lims_sale_data->total_price, 2, '.', ',') }}
                    </th>
                </tr>
                @if ($lims_sale_data->order_tax)
                    <tr>
                        <th colspan="3" style="text-align:right">{{ __('file.Order Tax') }}
                            {{ $general_setting->currency }}:</th>
                        <th colspan="3" style="text-align:right">
                            {{ number_format((float) $lims_sale_data->order_tax, 2, '.', ',') }}
                        </th>
                    </tr>
                @endif
                @if ($lims_sale_data->order_discount)
                    <tr>
                        <th colspan="3" style="text-align:right">{{ __('file.Order Discount') }}
                            {{ $general_setting->currency }}:</th>
                        <th colspan="3" style="text-align:right">
                            {{ number_format((float) $lims_sale_data->order_discount, 2, '.', ',') }}
                        </th>
                    </tr>
                @endif
                @if ($lims_sale_data->coupon_discount)
                    <tr>
                        <th colspan="3" style="text-align:right">{{ __('file.Coupon Discount') }}
                            {{ $general_setting->currency }}:</th>
                        <th colspan="3" style="text-align:right">
                            {{ number_format((float) $lims_sale_data->coupon_discount, 2, '.', ',') }}
                        </th>
                    </tr>
                @endif
                @if ($lims_sale_data->shipping_cost)
                    <tr>
                        <th colspan="3" style="text-align:right">{{ __('file.Shipping Cost') }}
                            {{ $general_setting->currency }}:</th>
                        <th colspan="3" style="text-align:right">
                            {{ number_format((float) $lims_sale_data->shipping_cost, 2, '.', ',') }}
                        </th>
                    </tr>
                @endif
                @if ($lims_sale_data->total_tips > 0)
                    <tr>
                        <th colspan="3" style="text-align:right">Propinas {{ $general_setting->currency }}:</th>
                        <th colspan="3" style="text-align:right">
                            {{ number_format((float) $lims_sale_data->total_tips, 2, '.', ',') }}
                        </th>
                    </tr>
                @endif
                <tr>
                    <th colspan="3" style="text-align:right">{{ __('file.grand total') }}
                        {{ $general_setting->currency }}:</th>
                    <th colspan="3" style="text-align:right">
                        {{ number_format((float) $lims_sale_data->grand_total, 2, '.', ',') }}
                    </th>
                </tr>
                <tr>
                    @if ($general_setting->currency_position == 'prefix')
                        <th class="centered" colspan="10">{{ __('file.In Words') }}:
                            <span>{{ $general_setting->currency }}</span>
                            <span>{{ str_replace('-', ' ', $numberInWords) }} {{ $cadenaCentavos }}</span>
                        </th>
                    @else
                        <th class="centered" colspan="10">{{ __('file.In Words') }}:
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
                        <td class="centered" colspan="3">
                            {{ __('file.Thank you for shopping with us. Please come again') }}</td>
                    </tr>
                @else
                    @foreach ($lims_payment_data as $payment_data)
                        <tr style="">
                            <td style="padding: 5px;width:30%">{{ __('file.Paid By') }}:
                                {{ str_replace('_', ' ', $payment_data->paying_method) }}</td>
                            <td style="padding: 5px;width:40%">{{ __('file.Amount') }}:
                                {{ number_format((float) $payment_data->amount, 2, '.', ',') }}</td>
                            <td style="padding: 5px;width:30%">{{ __('file.Change') }}:
                                {{ number_format((float) $payment_data->change, 2, '.', ',') }}</td>
                        </tr>
                        <tr>
                            <td class="centered" colspan="3">
                                {{ __('file.Thank you for shopping with us. Please come again') }}</td>
                        </tr>
                    @endforeach

                @endif
            </tbody>
        </table>
    </div>

</body>

</html>
