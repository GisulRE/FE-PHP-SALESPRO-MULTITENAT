<?php

namespace App;

use App\SiatSucursal;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable =[

        "name", "phone", "email", "address", "is_active",
        "sucursal_id"
    ];

    public function product()
    {
        return $this->hasMany('App\Product');
    }

    public function sucursal()
    {
        return $this->belongsTo(SiatSucursal::class, 'sucursal_id', 'id');
    }

    
}
