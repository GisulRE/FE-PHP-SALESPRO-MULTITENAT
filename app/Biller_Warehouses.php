<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Biller_Warehouses extends Model
{
    protected $table = 'biller_warehouses';

    protected $fillable = [
        "biller_id",
        "warehouse_id"
    ];

    public function biller()
    {
        return $this->belongsTo('App\Biller');
    }
    public function warehouse()
    {
        return $this->belongsTo('App\Warehouse');
    }
}