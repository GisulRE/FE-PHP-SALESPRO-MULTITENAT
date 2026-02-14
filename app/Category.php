<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable =[

        "name", 'image', "parent_id", "is_active",
        "codigo_actividad", "codigo_producto_servicio"
    ];

    public function product()
    {
    	return $this->hasMany('App\Product');
    }
}
