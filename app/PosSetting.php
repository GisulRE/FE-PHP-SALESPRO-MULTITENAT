<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PosSetting extends Model
{
    protected $table = 'pos_setting';
    protected $fillable = [
        "customer_id",
        "warehouse_id",
        "biller_id",
        "product_number",
        "stripe_public_key",
        "stripe_secret_key",
        "keybord_active",
        "t_c",
        "print",
        "type_print",
        "date_sell",
        "print_order",
        "print_presale",
        "hour_resetshift",
        "facturacion_id",
        "codigo_emision",
        "tipo_moneda_siat",
        "nit_emisor",
        "razon_social_emisor",
        "direccion_emisor",
        "user_siat",
        "pass_siat",
        "url_siat",
        "url_operaciones",
        "url_optimo",
        "url_cobranza",
        "cant_max_contingencia",
        "cant_max_masiva",
        "quotation_printer",
        "customer_sucursal",
        "user_category",
        "cant_decimal",
        "cufd_centralizado",
        "url_whatsapp",
        "whatsapp_session_id",
        "whatsapp_session_last_started_at",
        "require_transfer_authorization"
    ];
}