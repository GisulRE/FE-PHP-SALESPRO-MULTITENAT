<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductLote extends Model
{
    protected $table = 'product_lot';
    protected $fillable =[

        "purchase_id", "name", "idwarehouse", "idproducto", "expiration", "supplier", "fabrication_date", "status", "qty", "stock", "low_date"
    ];

}
