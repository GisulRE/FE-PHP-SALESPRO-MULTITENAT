<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>PROFORMA - {{ $general_setting->site_title }}</title>
    <style type="text/css">
        @page {
            margin: 25px 30px;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        .table-full {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .header-table td {
            vertical-align: top;
        }

        .company-logo {
            max-height: 55px;
            max-width: 180px;
        }

        .title-box {
            text-align: right;
        }

        .title-box h1 {
            font-size: 22px;
            margin: 0 0 5px 0;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .title-box p {
            margin: 2px 0;
            font-size: 11px;
            color: #4b5563;
        }

        .info-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            margin-top: 15px;
            margin-bottom: 15px;
            padding: 10px;
        }

        .info-table td {
            vertical-align: top;
            font-size: 11px;
        }

        .info-title {
            font-weight: bold;
            color: #1e3a8a;
            margin-bottom: 5px;
            font-size: 12px;
            text-transform: uppercase;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 3px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 10px;
        }

        .items-table th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 7px 6px;
            border: 1px solid #1e3a8a;
        }

        .items-table td {
            padding: 6px;
            font-size: 10px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .items-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .text-left {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 15px;
        }

        .totals-table td {
            vertical-align: top;
        }

        .words-box {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 8px;
            font-size: 10px;
            border-radius: 4px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 4px 8px;
            font-size: 10.5px;
        }

        .summary-label {
            text-align: right;
            font-weight: bold;
            color: #475569;
        }

        .summary-value {
            text-align: right;
            font-weight: bold;
            color: #0f172a;
        }

        .grand-total-row td {
            background-color: #1e3a8a;
            color: #ffffff;
            font-size: 12px;
            padding: 6px 8px;
        }

        .grand-total-row .summary-label,
        .grand-total-row .summary-value {
            color: #ffffff;
        }

        .footer-note {
            margin-top: 25px;
            text-align: center;
            font-size: 9.5px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }
    </style>
</head>

<body>

    <div class="invoice-container">
        <!-- CABECERA -->
        <table class="table-full header-table">
            <tr>
                <td style="width: 55%;">
                    @if ($general_setting->site_logo && file_exists(public_path('logo/' . $general_setting->site_logo)))
                        <img src="{{ public_path('logo/' . $general_setting->site_logo) }}" class="company-logo" alt="Logo">
                    @endif
                    <div style="margin-top: 5px;">
                        <strong style="font-size: 14px; color: #0f172a;">{{ $general_setting->site_title }}</strong><br>
                        @if(isset($lims_quotation_data->warehouse->address))
                            {{ $lims_quotation_data->warehouse->address }}<br>
                        @endif
                        @if(isset($lims_quotation_data->warehouse->phone))
                            <strong>Teléfono:</strong> {{ $lims_quotation_data->warehouse->phone }}<br>
                        @endif
                        @if(isset($lims_quotation_data->warehouse->email))
                            <strong>Email:</strong> {{ $lims_quotation_data->warehouse->email }}
                        @endif
                    </div>
                </td>
                <td style="width: 45%;" class="title-box">
                    <h1>PROFORMA</h1>
                    <p><strong>N° Referencia:</strong> <span style="color: #1e3a8a;">{{ $lims_quotation_data->reference_no }}</span></p>
                    <p><strong>Fecha de Emisión:</strong> {{ date($general_setting->date_format, strtotime($lims_quotation_data->created_at->toDateString())) }}</p>
                    @if ($lims_quotation_data->valid_date != null)
                        <p><strong>Válido Hasta:</strong> {{ date($general_setting->date_format, strtotime($lims_quotation_data->valid_date)) }}</p>
                    @endif
                    <p><strong>Estado:</strong> 
                        @if ($lims_quotation_data->quotation_status == 1)
                            <span style="color: #d97706;">Pendiente</span>
                        @elseif ($lims_quotation_data->quotation_status == 2)
                            <span style="color: #2563eb;">Enviado</span>
                        @endif
                    </p>
                </td>
            </tr>
        </table>

        <!-- INFORMACIÓN DEL CLIENTE Y COTIZACIÓN -->
        <div class="info-box">
            <table class="table-full info-table">
                <tr>
                    <td style="width: 50%; padding-right: 10px;">
                        <div class="info-title">DATOS DEL CLIENTE</div>
                        <strong>Nombre / Razón Social:</strong> {{ $lims_quotation_data->customer->name }}<br>
                        @if($lims_quotation_data->customer->company_name)
                            <strong>Empresa:</strong> {{ $lims_quotation_data->customer->company_name }}<br>
                        @endif
                        <strong>Dirección:</strong> {{ $lims_quotation_data->customer->address ?? 'N/A' }}<br>
                        <strong>Teléfono:</strong> {{ $lims_quotation_data->customer->phone_number ?? 'N/A' }}<br>
                        <strong>Email:</strong> {{ $lims_quotation_data->customer->email ?? 'N/A' }}
                    </td>
                    <td style="width: 50%; padding-left: 10px; border-left: 1px solid #e2e8f0;">
                        <div class="info-title">DATOS DE ATENCIÓN</div>
                        <strong>Vendedor / Emisor:</strong> {{ $lims_biller_data->name }}<br>
                        <strong>Sucursal / Almacén:</strong> {{ $lims_quotation_data->warehouse->name ?? 'N/A' }}
                    </td>
                </tr>
            </table>
        </div>

        <!-- TABLA DE PRODUCTOS -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 8%;" class="text-center">Cant.</th>
                    <th style="width: 10%;" class="text-center">Unidad</th>
                    <th style="width: 44%;" class="text-left">Descripción</th>
                    <th style="width: 13%;" class="text-right">P. Unitario</th>
                    <th style="width: 11%;" class="text-right">Desc.</th>
                    <th style="width: 14%;" class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($lims_product_quotation_data as $key => $product_quotation_data)
                    @php
                        $lims_product_data = \App\Product::find($product_quotation_data->product_id);
                        $lims_unit_data = \App\Unit::find($product_quotation_data->sale_unit_id);
                        $name_unit = isset($lims_unit_data->unit_code) ? $lims_unit_data->unit_code : 'N/A';
                        if ($product_quotation_data->variant_id) {
                            $variant_data = \App\Variant::find($product_quotation_data->variant_id);
                            $product_name = $lims_product_data->name . ' [' . ($variant_data->name ?? '') . ']';
                        } else {
                            $product_name = $lims_product_data->name;
                        }
                        $unit_price = $product_quotation_data->qty > 0 ? ($product_quotation_data->total / $product_quotation_data->qty) : 0;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $product_quotation_data->qty }}</td>
                        <td class="text-center">{{ $name_unit }}</td>
                        <td class="text-left">{{ $product_name }}</td>
                        <td class="text-right">{{ number_format((float) $unit_price, 2, '.', ',') }}</td>
                        <td class="text-right">{{ number_format((float) $product_quotation_data->discount, 2, '.', ',') }}</td>
                        <td class="text-right">{{ number_format((float) $product_quotation_data->total, 2, '.', ',') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- TOTALES Y NOTAS -->
        <table class="totals-table">
            <tr>
                <td style="width: 55%; padding-right: 15px;">
                    <div class="words-box">
                        <strong>SON:</strong>
                        @if ($general_setting->currency_position == 'prefix')
                            <span>{{ $general_setting->currency }}</span>
                            <span>{{ str_replace('-', ' ', $numberInWords) }}</span>
                        @else
                            <span>{{ str_replace('-', ' ', $numberInWords) }}</span>
                            <span>{{ isset($cadenaCentavos) ? $cadenaCentavos : '' }} {{ $general_setting->currency }}</span>
                        @endif
                    </div>
                    @if($lims_quotation_data->note)
                        <div style="margin-top: 10px; font-size: 10px; color: #475569;">
                            <strong>Notas / Observaciones:</strong><br>
                            {{ $lims_quotation_data->note }}
                        </div>
                    @endif
                </td>
                <td style="width: 45%;">
                    <table class="summary-table">
                        <tr>
                            <td class="summary-label">Subtotal:</td>
                            <td class="summary-value">{{ number_format((float) $lims_quotation_data->total_price, 2, '.', ',') }}</td>
                        </tr>
                        @if ($lims_quotation_data->order_tax)
                            <tr>
                                <td class="summary-label">Impuesto:</td>
                                <td class="summary-value">{{ number_format((float) $lims_quotation_data->order_tax, 2, '.', ',') }}</td>
                            </tr>
                        @endif
                        @if ($lims_quotation_data->order_discount)
                            <tr>
                                <td class="summary-label">Descuento Orden:</td>
                                <td class="summary-value">-{{ number_format((float) $lims_quotation_data->order_discount, 2, '.', ',') }}</td>
                            </tr>
                        @endif
                        @if ($lims_quotation_data->shipping_cost)
                            <tr>
                                <td class="summary-label">Costo Envío:</td>
                                <td class="summary-value">{{ number_format((float) $lims_quotation_data->shipping_cost, 2, '.', ',') }}</td>
                            </tr>
                        @endif
                        <tr class="grand-total-row">
                            <td class="summary-label">TOTAL ({{ $general_setting->currency }}):</td>
                            <td class="summary-value">{{ number_format((float) $lims_quotation_data->grand_total, 2, '.', ',') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="footer-note">
            Documento de Proforma generado por {{ $general_setting->site_title }}. Válido según fecha indicada.
        </div>
    </div>

</body>

</html>
