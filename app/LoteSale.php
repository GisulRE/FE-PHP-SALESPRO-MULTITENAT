<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoteSale extends Model
{
    protected $table = 'lote_sales';

    protected $fillable =[

        "sale_id", "lote_id", "qty", "data"
    ];
}
