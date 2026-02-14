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

        .invoice-ticket-container td.columna1 {
            
        }

        .invoice-ticket-container td.columna2 {
            
        }

        .invoice-ticket-container td.columna3 {        
            padding-right: 2px;
        }

        .invoice-ticket-container td.columna4 {
            
        }
    </style>
</head>

<body>

    <div class="invoice-ticket-container" style="max-width:100%;margin:0 auto">

        <div id="receipt-data">
            <div class="centered">
                <h2 class="contenido">PROFORMA</h2>
                <h3 class="contenido">{{ $lims_biller_data->company_name }}</h3>
            </div>
            <table>
                <tbody>
                    <tr>
                        <td>
                            <strong>{{ __('file.reference_quotation') }}:</strong>
                        </td>
                        <td>
                            <span>{{ $lims_quotation_data->reference_no }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ __('file.Date') }}:</strong>
                        </td>
                        <td>
                            <span>{{ \Carbon\Carbon::parse($lims_quotation_data->created_at)->format("$general_setting->date_format H:i:s") }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ __('file.Phone Number') }}:</strong>
                        </td>
                        <td>
                            <span>{{ $lims_quotation_data->warehouse->phone }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align:top">
                            <strong>{{ __('file.Address') }}:</strong>
                        </td>
                        <td>
                            <span>{{ $lims_quotation_data->warehouse->address }}</span>
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
                            <span>{{ $lims_quotation_data->customer->name }}</span>
                        </td>
                    </tr>
                    @if ($lims_quotation_data->valid_date)
                    <tr>
                        <td>
                            <strong>{{ __('file.date_valid') }}:</strong>
                        </td>
                        <td>
                            <span>{{ \Carbon\Carbon::parse($lims_quotation_data->valid_date)->format($general_setting->date_format) }}</span>
                        </td>
                    </tr>
                    @endif
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
                    @foreach ($lims_product_quotation_data as $product_quotation_data)
                        @php
                            $lims_product_data = \App\Product::find($product_quotation_data->product_id);
                            if ($product_quotation_data->variant_id) {
                                $variant_data = \App\Variant::find($product_quotation_data->variant_id);
                                $product_name = $lims_product_data->name . ' [' . $variant_data->name . ']';
                            } else {
                                $product_name = $lims_product_data->name;
                            }
                        @endphp
                        <tr>
                            <td class="columna1" style="text-align: center;vertical-align:top">
                                {{ $product_quotation_data->qty }}
                            </td>
                            <td class="columna2" style="text-align: left;vertical-align:top;">{{ $product_name }}</td>
                            <td class="columna3" style="text-align: center;vertical-align:top; text-align: right">
                                {{ number_format((float) ($product_quotation_data->total / $product_quotation_data->qty), 2, '.', ',') }}
                            </td>
                            <td class="columna4" style="text-align:center;vertical-align:top; text-align: right">
                                {{ number_format((float) $product_quotation_data->total, 2, '.', ',') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" style="text-align:right">{{ __('file.Total') }} {{ $general_setting->currency }}:</th>
                        <th colspan="3" style="text-align:right">
                            {{ number_format((float) $lims_quotation_data->total_price, 2, '.', ',') }}
                        </th>
                    </tr>
                    @if ($lims_quotation_data->order_tax)
                        <tr>
                            <th colspan="3" style="text-align:right">{{ __('file.Order Tax') }} {{ $general_setting->currency }}:</th>
                            <th colspan="3" style="text-align:right">
                                {{ number_format((float) $lims_quotation_data->order_tax, 2, '.', ',') }}
                            </th>
                        </tr>
                    @endif
                    @if ($lims_quotation_data->order_discount)
                        <tr>
                            <th colspan="3" style="text-align:right">{{ __('file.Order Discount') }} {{ $general_setting->currency }}:</th>
                            <th colspan="3" style="text-align:right">
                                {{ number_format((float) $lims_quotation_data->order_discount, 2, '.', ',') }}
                            </th>
                        </tr>
                    @endif
                    @if ($lims_quotation_data->shipping_cost)
                        <tr>
                            <th colspan="3" style="text-align:right">{{ __('file.Shipping Cost') }} {{ $general_setting->currency }}:</th>
                            <th colspan="3" style="text-align:right">
                                {{ number_format((float) $lims_quotation_data->shipping_cost, 2, '.', ',') }}
                            </th>
                        </tr>
                    @endif
                    <tr>
                        <th colspan="3" style="text-align:right">{{ __('file.grand total') }} {{ $general_setting->currency }}:</th>
                        <th colspan="3" style="text-align:right">
                            {{ number_format((float) $lims_quotation_data->grand_total, 2, '.', ',') }}
                        </th>
                    </tr>
                    <tr>
                        @if ($general_setting->currency_position == 'prefix')
                            <th colspan="10">{{ __('file.In Words') }}:
                                <span>{{ $general_setting->currency }}</span>
                                <span>{{ str_replace('-', ' ', $numberInWords) }} {{$cadenaCentavos}}</span>
                            </th>
                        @else
                            <th colspan="10">{{ __('file.In Words') }}:
                                <span>{{ str_replace('-', ' ', $numberInWords) }} {{$cadenaCentavos}}</span>
                                <span>{{ $general_setting->currency }}</span>
                            </th>
                        @endif
                    </tr>
                </tfoot>
            </table>            
        </div>
    </div>

    <script type="text/javascript">
        function auto_print() {
            window.print()
            window.history.go(-1);
            window.history.back();
        }
        setTimeout(auto_print, 1000);
    </script>

</body>

</html>
