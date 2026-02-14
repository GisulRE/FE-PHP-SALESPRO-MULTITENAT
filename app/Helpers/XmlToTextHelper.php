<?php

namespace App\Helpers;

class XmlToTextHelper
{
    /**
     * Convierte el XML de una factura SIAT a formato texto legible
     * 
     * @param string $xmlString El XML completo de la factura
     * @return string Texto formateado de la factura
     */
    public static function convertirFacturaSiatATexto($xmlString)
    {
        try {
            // Parsear el XML
            $xml = simplexml_load_string($xmlString);
            
            if ($xml === false) {
                return "Error: No se pudo parsear el XML de la factura.";
            }

            $texto = "═══════════════════════════════════════════════════════\n";
            $texto .= "           FACTURA COMPUTARIZADA SIAT\n";
            $texto .= "           COMPRA VENTA - ORIGINAL\n";
            $texto .= "═══════════════════════════════════════════════════════\n\n";

            // DATOS DEL EMISOR
            $texto .= "DATOS DEL EMISOR:\n";
            $texto .= "-----------------------------------------------------------\n";
            $texto .= "NIT: " . (string)$xml->cabecera->nitEmisor . "\n";
            $texto .= "Razón Social: " . (string)$xml->cabecera->razonSocialEmisor . "\n";
            $texto .= "Sucursal: " . (string)$xml->cabecera->codigoSucursal . "\n";
            $texto .= "Punto de Venta: " . (string)$xml->cabecera->codigoPuntoVenta . "\n";
            $texto .= "Dirección: " . (string)$xml->cabecera->direccion . "\n";
            $texto .= "Municipio: " . (string)$xml->cabecera->municipio . "\n";
            $texto .= "Teléfono: " . (string)$xml->cabecera->telefono . "\n\n";

            // DATOS DE LA FACTURA
            $texto .= "DATOS DE LA FACTURA:\n";
            $texto .= "-----------------------------------------------------------\n";
            $texto .= "Número de Factura: " . (string)$xml->cabecera->numeroFactura . "\n";
            $texto .= "Fecha de Emisión: " . (string)$xml->cabecera->fechaEmision . "\n";
            $texto .= "Usuario: " . (string)$xml->cabecera->usuario . "\n";
            $texto .= "Código Documento Sector: " . (string)$xml->cabecera->codigoDocumentoSector . "\n\n";

            // CUF Y CUFD
            $texto .= "CÓDIGOS DE CONTROL:\n";
            $texto .= "-----------------------------------------------------------\n";
            $texto .= "CUF: " . (string)$xml->cabecera->cuf . "\n";
            $texto .= "CUFD: " . (string)$xml->cabecera->cufd . "\n\n";

            // DATOS DEL CLIENTE
            $texto .= "DATOS DEL CLIENTE:\n";
            $texto .= "-----------------------------------------------------------\n";
            $texto .= "Nombre/Razón Social: " . (string)$xml->cabecera->nombreRazonSocial . "\n";
            $texto .= "Tipo de Documento: " . (string)$xml->cabecera->codigoTipoDocumentoIdentidad . "\n";
            $texto .= "Número de Documento: " . (string)$xml->cabecera->numeroDocumento;
            
            if (!empty((string)$xml->cabecera->complemento)) {
                $texto .= " - " . (string)$xml->cabecera->complemento;
            }
            $texto .= "\n";
            $texto .= "Código Cliente: " . (string)$xml->cabecera->codigoCliente . "\n\n";

            // MÉTODO DE PAGO
            $texto .= "MÉTODO DE PAGO:\n";
            $texto .= "-----------------------------------------------------------\n";
            $texto .= "Código Método: " . (string)$xml->cabecera->codigoMetodoPago . "\n";
            
            if (!empty((string)$xml->cabecera->numeroTarjeta)) {
                $texto .= "Número de Tarjeta: " . (string)$xml->cabecera->numeroTarjeta . "\n";
            }
            $texto .= "\n";

            // DETALLE DE PRODUCTOS/SERVICIOS
            $texto .= "DETALLE DE LA FACTURA:\n";
            $texto .= "═══════════════════════════════════════════════════════\n";
            $texto .= "Cant. | Descripción                    | P.Unit  | Subtotal\n";
            $texto .= "-----------------------------------------------------------\n";

            $total_items = 0;
            foreach ($xml->detalle as $detalle) {
                $cantidad = (float)$detalle->cantidad;
                $descripcion = (string)$detalle->descripcion;
                $precioUnitario = (float)$detalle->precioUnitario;
                $subTotal = (float)$detalle->subTotal;
                
                // Truncar descripción si es muy larga
                if (strlen($descripcion) > 30) {
                    $descripcion = substr($descripcion, 0, 27) . '...';
                }
                
                $texto .= sprintf(
                    "%5.2f | %-30s | %7.2f | %8.2f\n",
                    $cantidad,
                    $descripcion,
                    $precioUnitario,
                    $subTotal
                );
                
                // Agregar detalles adicionales si existen
                if (!empty((string)$detalle->numeroSerie)) {
                    $texto .= "       Serie: " . (string)$detalle->numeroSerie . "\n";
                }
                if (!empty((string)$detalle->numeroImei)) {
                    $texto .= "       IMEI: " . (string)$detalle->numeroImei . "\n";
                }
                if ((float)$detalle->montoDescuento > 0) {
                    $texto .= "       Descuento: Bs. " . number_format((float)$detalle->montoDescuento, 2) . "\n";
                }
                
                $total_items++;
            }

            $texto .= "-----------------------------------------------------------\n";
            $texto .= "Total de items: " . $total_items . "\n\n";

            // MONTOS TOTALES
            $texto .= "MONTOS:\n";
            $texto .= "═══════════════════════════════════════════════════════\n";
            $texto .= "Monto Total Sujeto a IVA: Bs. " . number_format((float)$xml->cabecera->montoTotalSujetoIva, 2) . "\n";
            
            if ((float)$xml->cabecera->descuentoAdicional > 0) {
                $texto .= "Descuento Adicional: Bs. " . number_format((float)$xml->cabecera->descuentoAdicional, 2) . "\n";
            }
            
            if (!empty((string)$xml->cabecera->montoGiftCard) && (float)$xml->cabecera->montoGiftCard > 0) {
                $texto .= "Gift Card: Bs. " . number_format((float)$xml->cabecera->montoGiftCard, 2) . "\n";
            }
            
            $texto .= "\n";
            $texto .= "MONTO TOTAL: Bs. " . number_format((float)$xml->cabecera->montoTotal, 2) . "\n";
            $texto .= "\n";
            
            // Moneda
            $texto .= "Código Moneda: " . (string)$xml->cabecera->codigoMoneda . " (Tipo Cambio: " . (string)$xml->cabecera->tipoCambio . ")\n";
            $texto .= "Monto Total en Moneda: " . number_format((float)$xml->cabecera->montoTotalMoneda, 2) . "\n\n";

            // LEYENDA
            $texto .= "LEYENDA:\n";
            $texto .= "-----------------------------------------------------------\n";
            $texto .= (string)$xml->cabecera->leyenda . "\n\n";

            // CÓDIGOS DE EXCEPCIÓN Y CAFC (si existen)
            if (!empty((string)$xml->cabecera->codigoExcepcion)) {
                $texto .= "Código de Excepción: " . (string)$xml->cabecera->codigoExcepcion . "\n";
            }
            if (!empty((string)$xml->cabecera->cafc)) {
                $texto .= "CAFC: " . (string)$xml->cabecera->cafc . "\n";
            }

            $texto .= "\n═══════════════════════════════════════════════════════\n";
            $texto .= "   ESTE DOCUMENTO ES LA REPRESENTACIÓN GRÁFICA\n";
            $texto .= "   DE UN DOCUMENTO FISCAL DIGITAL VERIFICABLE\n";
            $texto .= "   EN EL PORTAL DEL SERVICIO DE IMPUESTOS\n";
            $texto .= "   NACIONALES www.impuestos.gob.bo\n";
            $texto .= "═══════════════════════════════════════════════════════\n";

            return $texto;
            
        } catch (\Exception $e) {
            return "Error al convertir XML a texto: " . $e->getMessage();
        }
    }
}
