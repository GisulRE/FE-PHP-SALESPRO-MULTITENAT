<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $customer_sale ? ('Nota de Crédito - Débito N° ' . $customer_sale->nro_factura) : ('Comprobante de Devolución - ' . ($return->reference_no ?? '')) }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style type="text/css">
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-size: 11px;
            line-height: 1.25;
            font-family: 'Courier New', Courier, monospace, sans-serif;
            color: #000;
        }

        body {
            background-color: #f2f2f2;
            padding: 10px 0;
        }

        .ticket-container {
            width: 76mm;
            max-width: 80mm;
            margin: 0 auto;
            background: #fff;
            padding: 8px 6px;
            border: 1px solid #ddd;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .bold {
            font-weight: bold;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .title-header {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .subtitle-header {
            font-size: 10px;
            margin-bottom: 2px;
        }

        .doc-title {
            font-size: 13px;
            font-weight: bold;
            margin: 6px 0 2px 0;
            letter-spacing: 0.5px;
        }

        .doc-subtitle {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        .double-divider {
            border-top: 2px solid #000;
            margin: 6px 0;
        }

        .info-table, .items-table, .totals-table {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0;
        }

        .info-table td {
            padding: 1px 0;
            vertical-align: top;
            font-size: 10px;
        }

        .items-table th {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 3px 1px;
            font-size: 9.5px;
            font-weight: bold;
        }

        .items-table td {
            padding: 2px 1px;
            vertical-align: top;
            font-size: 9.5px;
        }

        .totals-table td {
            padding: 1.5px 0;
            font-size: 10.5px;
        }

        .cuf-box {
            word-break: break-all;
            font-size: 8.5px;
            text-align: center;
            line-height: 1.15;
            padding: 2px 0;
        }

        .qr-section {
            text-align: center;
            margin: 8px 0 4px 0;
        }

        .qr-section img {
            max-width: 120px;
            height: auto;
        }

        .legal-legend {
            font-size: 8.5px;
            text-align: center;
            margin: 4px 0;
            line-height: 1.15;
        }

        .action-buttons {
            text-align: center;
            margin-top: 12px;
            padding: 8px;
        }

        .btn-print {
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 4px;
            margin-right: 6px;
        }

        .btn-pdf {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
        }

        @media print {
            body {
                background: none;
                padding: 0;
            }
            .ticket-container {
                width: 100% !important;
                max-width: 100% !important;
                border: none !important;
                padding: 0 !important;
            }
            .action-buttons {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="ticket-container">
        <!-- Encabezado de la Empresa -->
        <div class="text-center">
            <div class="title-header uppercase">{{ $pos_setting->razon_social_emisor ?? 'EMPRESA' }}</div>
            <div class="subtitle-header uppercase">
                @if(($customer_sale->sucursal ?? 0) == 0)
                    CASA MATRIZ
                @else
                    SUCURSAL N° {{ $customer_sale->sucursal }}
                @endif
            </div>
            <div class="subtitle-header">No. Punto de Venta: {{ $customer_sale->codigo_punto_venta ?? 0 }}</div>
            <div class="subtitle-header uppercase">{{ $warehouse->address ?? ($biller->address ?? 'DIRECCIÓN') }}</div>
            <div class="subtitle-header">Teléfono: {{ $biller->phone_number ?? ($warehouse->phone ?? '0') }}</div>
            <div class="subtitle-header uppercase">{{ $biller->city ?? 'LA PAZ' }} - BOLIVIA</div>
        </div>

        <div class="double-divider"></div>

        <!-- Título del Documento Sector 24 o Comprobante -->
        <div class="text-center">
            @if($customer_sale)
                <div class="doc-title uppercase">NOTA DE CRÉDITO - DÉBITO</div>
                <div class="doc-subtitle">(Documento Sector 24)</div>
            @else
                <div class="doc-title uppercase">COMPROBANTE DE DEVOLUCIÓN</div>
                <div class="doc-subtitle">(Restitución de Saldo - Sin Ajuste Fiscal)</div>
            @endif
        </div>

        <div class="divider"></div>

        <!-- Identificación Tributaria -->
        <table class="info-table">
            <tr>
                <td style="width: 45%;" class="bold">NIT:</td>
                <td style="width: 55%;" class="text-right">{{ $pos_setting->nit_emisor ?? '' }}</td>
            </tr>
            @if($customer_sale)
            <tr>
                <td class="bold">NOTA FISCAL N°:</td>
                <td class="text-right bold" style="font-size: 12px;">{{ $customer_sale->nro_factura }}</td>
            </tr>
            <tr>
                <td colspan="2" class="bold" style="padding-top: 3px;">CÓD. AUTORIZACIÓN (CUF):</td>
            </tr>
            <tr>
                <td colspan="2" class="cuf-box">{{ $customer_sale->cuf }}</td>
            </tr>
            @else
            <tr>
                <td class="bold">N° DEVOLUCIÓN:</td>
                <td class="text-right bold" style="font-size: 12px;">{{ $return->reference_no ?? '-' }}</td>
            </tr>
            @endif
        </table>

        <div class="divider"></div>

        <!-- Referencia a la Factura Original -->
        <div class="text-center bold" style="font-size: 9.5px; margin: 2px 0;">DATOS DE LA FACTURA OBJETO DE AJUSTE</div>
        <table class="info-table" style="background-color: #f9f9f9; padding: 2px;">
            <tr>
                <td style="width: 45%;" class="bold">Factura N°:</td>
                <td style="width: 55%;" class="text-right">{{ $original_customer_sale->nro_factura ?? ($sale->id ?? '-') }}</td>
            </tr>
            <tr>
                <td class="bold">Fecha Emisión:</td>
                <td class="text-right">{{ $original_customer_sale ? \Carbon\Carbon::parse($original_customer_sale->created_at)->format('d/m/Y H:i') : ($sale ? \Carbon\Carbon::parse($sale->date_sell)->format('d/m/Y H:i') : '-') }}</td>
            </tr>
            <tr>
                <td colspan="2" class="bold">CUF Factura Original:</td>
            </tr>
            <tr>
                <td colspan="2" class="cuf-box" style="font-size: 8px;">{{ $original_customer_sale->cuf ?? ($return->cuf ?? '-') }}</td>
            </tr>
        </table>

        <div class="divider"></div>

        <!-- Datos del Cliente -->
        <table class="info-table">
            <tr>
                <td style="width: 38%;" class="bold">Fecha/Hora:</td>
                <td style="width: 62%;">{{ $customer_sale ? \Carbon\Carbon::parse($customer_sale->created_at)->format('d/m/Y H:i:s') : ($return ? \Carbon\Carbon::parse($return->created_at)->format('d/m/Y H:i:s') : date('d/m/Y H:i:s')) }}</td>
            </tr>
            <tr>
                <td class="bold">Señor(es):</td>
                <td class="uppercase">{{ $customer_sale->razon_social ?? ($customer->name ?? ($customer->razon_social ?? 'CLIENTE')) }}</td>
            </tr>
            <tr>
                <td class="bold">NIT/CI/CEX:</td>
                <td>{{ $customer_sale->valor_documento ?? ($customer->valor_documento ?? '') }}{{ !empty($customer_sale->complemento_documento ?? $customer->complemento_documento) ? ' - ' . ($customer_sale->complemento_documento ?? $customer->complemento_documento) : '' }}</td>
            </tr>
            <tr>
                <td class="bold">Cod. Cliente:</td>
                <td>{{ $customer->id ?? ($customer_sale->customer_id ?? '1') }}</td>
            </tr>
        </table>

        <div class="divider"></div>

        <!-- Detalle de Devolución -->
        <div class="bold uppercase" style="font-size: 9.5px; margin-bottom: 2px;">DETALLE DE LA DEVOLUCIÓN O RESCISIÓN</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th class="text-left" style="width: 15%;">CANT.</th>
                    <th class="text-left" style="width: 45%;">DETALLE</th>
                    <th class="text-right" style="width: 20%;">P/U</th>
                    <th class="text-right" style="width: 20%;">SUBTOTAL</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $sum_subtotal = 0;
                    $sum_descuento = 0;
                @endphp
                @foreach ($product_returns as $item)
                    @php
                        $prod = $item->product;
                        $cant = (float) $item->qty;
                        $pu = (float) $item->net_unit_price;
                        $desc = (float) $item->discount;
                        $sub = (float) $item->total;
                        $sum_subtotal += ($cant * $pu);
                        $sum_descuento += $desc;
                    @endphp
                    <tr>
                        <td class="text-left">{{ number_format($cant, 2) }}</td>
                        <td class="text-left uppercase">
                            {{ $prod ? $prod->name : 'PRODUCTO' }}
                            @if($desc > 0)
                                <div style="font-size: 8px; color: #555;">(Desc: Bs. {{ number_format($desc, 2) }})</div>
                            @endif
                        </td>
                        <td class="text-right">{{ number_format($pu, 2) }}</td>
                        <td class="text-right">{{ number_format($sub, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        <!-- Liquidación de Totales Normativa SIAT -->
        @php
            $monto_total_devuelto = $sum_subtotal > 0 ? $sum_subtotal : ($return ? (float)$return->total_price : 0);
            $monto_descuento = $sum_descuento > 0 ? $sum_descuento : ($return ? (float)$return->total_discount : 0);
            $monto_efectivo = $monto_total_devuelto - $monto_descuento;
            $credito_fiscal_iva = round($monto_efectivo * 0.13, 2);
        @endphp
        <table class="totals-table">
            <tr>
                <td style="width: 65%;">Monto Total Devuelto (Bs):</td>
                <td style="width: 35%;" class="text-right">{{ number_format($monto_total_devuelto, 2) }}</td>
            </tr>
            @if($monto_descuento > 0)
            <tr>
                <td>(-) Descuento Crédito/Débito (Bs):</td>
                <td class="text-right">{{ number_format($monto_descuento, 2) }}</td>
            </tr>
            @endif
            <tr class="bold">
                <td>Monto Efectivo Crédito/Débito (Bs):</td>
                <td class="text-right" style="font-size: 11.5px;">{{ number_format($monto_efectivo, 2) }}</td>
            </tr>
            <tr class="bold">
                <td>Monto Crédito Fiscal IVA (13%) (Bs):</td>
                <td class="text-right">{{ number_format($credito_fiscal_iva, 2) }}</td>
            </tr>
        </table>

        <!-- Monto en palabras -->
        <div style="font-size: 9px; margin-top: 4px; padding: 2px 0;">
            <span class="bold">Son:</span> {{ strtoupper($numberInWords) }} {{ $cadenaCentavos }}
        </div>

        <div class="divider"></div>

        <!-- Código QR Oficial SIAT o Identificador -->
        @if(!empty($qr_url))
        <div class="qr-section">
            <img src="data:image/png;base64,{{ DNS2D::getBarcodePNG($qr_url, 'QRCODE') }}" alt="Código QR" />
            <div style="font-size: 8px; color: #555; margin-top: 2px;">
                {{ $customer_sale ? 'Consulte la validez fiscal escaneando el código QR' : 'Comprobante interno de devolución' }}
            </div>
        </div>
        <div class="divider"></div>
        @endif

        <!-- Leyendas Institucionales Normativas SIAT -->
        @if($customer_sale)
        <div class="legal-legend uppercase bold">
            ESTE DOCUMENTO ES UNA NOTA DE AJUSTE QUE MODIFICA EL DÉBITO O CRÉDITO FISCAL DE LA FACTURA ORIGINAL CONFORME A LA NORMATIVA VIGENTE
        </div>

        <div class="legal-legend" style="margin-top: 4px;">
            "{{ $leyenda }}"
        </div>
        @else
        <div class="legal-legend uppercase bold">
            DEVOLUCIÓN DE MERCADERÍA / COMPROBANTE DE REINGRESO A INVENTARIO Y RESTITUCIÓN DE VALOR
        </div>
        @endif

        <div class="text-center" style="font-size: 8px; margin-top: 6px; color: #555;">
            Operador: {{ $customer_sale->usuario ?? ($return->user->name ?? 'SISTEMA') }} | Ref: {{ $return->reference_no ?? 'NCD' }}
        </div>

        @if(empty($is_pdf))
        <!-- Botones de Acción (ocultos al imprimir y no presentes en PDF generado) -->
        <div class="action-buttons d-print-none">
            <button type="button" class="btn-print" onclick="window.print()">
                🖨️ Imprimir Ticket
            </button>
            <a href="{{ route('nota-credito-debito.downloadPdf', $customer_sale ? $customer_sale->id : ($return->id ?? 0)) }}" class="btn-pdf">
                📥 Descargar PDF
            </a>
        </div>
        @endif
    </div>

    <script type="text/javascript">
        // Auto-imprimir si viene parámetro ?print=1 o hash #print
        if (window.location.search.indexOf('print=1') !== -1 || window.location.hash === '#print') {
            window.onload = function() {
                setTimeout(function() {
                    window.print();
                }, 350);
            };
        }
    </script>
</body>
</html>
