<?php

namespace App;

use App\SiatSucursal;
use App\SiatPuntoVenta;
use Illuminate\Database\Eloquent\Model;

class Biller extends Model
{
    protected $fillable = [
        "name",
        "image",
        "company_name",
        "vat_number",
        "email",
        "phone_number",
        "address",
        "city",
        "state",
        "postal_code",
        "country",
        "account_id",
        "account_id_cheque",
        "account_id_tarjeta",
        "account_id_qr",
        "account_id_deposito",
        "account_id_receivable",
        "account_id_giftcard",
        "account_id_vale",
        "account_id_otros",
        "account_id_pagoposterior",
        "account_id_transferenciabancaria",
        "account_id_swift",
        "warehouse_id",
        "customer_id",
        "is_active",
        "sucursal",
        "punto_venta_siat",
    ];

    public function sale()
    {
        return $this->hasMany('App\Sale');
    }

    public function almacen()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }


    public function getpuntoventa()
    {
        return $this->belongsTo(SiatPuntoVenta::class, 'punto_venta_siat', 'codigo_punto_venta');
    }
}