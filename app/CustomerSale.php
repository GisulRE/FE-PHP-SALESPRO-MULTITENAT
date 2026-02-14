<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerSale extends Model
{
    protected $table = 'customer_sales';

    protected $fillable = [
        "sale_id",
        "customer_id",
        "codigofijo",
        "razon_social",
        "email",
        "tipo_documento",
        "valor_documento",
        "complemento_documento",
        "numero_tarjeta_credito_debito",
        "tipo_caso_especial",
        "tipo_metodo_pago",
        "nro_factura",
        "codigo_recepcion",
        "cuf",
        "estado_factura",

        "sucursal",
        "codigo_punto_venta",

        "usuario",
        "nro_factura_manual",
        "fecha_manual",
        "codigo_excepcion",
        "codigo_documento_sector",
        "glosa_periodo_facturado",

        "categoria",
        "numero_medidor",
        "lectura_medidor_anterior",
        "lectura_medidor_actual",
        "gestion",
        "mes",
        "ciudad",
        "zona",
        "domicilio_cliente",
        "consumo_periodo",

        "beneficiario_ley_1886",
        "monto_descuento_ley_1886",
        "monto_descuento_tarifa_dignidad",

        "tasa_aseo",
        "tasa_alumbrado",
        "otras_tasas",

        "ajuste_no_sujeto_iva",
        "detalle_ajuste_no_sujeto_iva",

        "ajuste_sujeto_iva",
        "detalle_ajuste_sujeto_iva",

        "otros_pagos_no_sujeto_iva",
        "detalle_otros_pagos_no_sujeto_iva",
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }
}
