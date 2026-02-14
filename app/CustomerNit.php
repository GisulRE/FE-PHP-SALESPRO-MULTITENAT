<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerNit extends Model
{
    protected $table = 'customer_nit';

    protected $fillable =[
        
        "tipo_documento", 
        "valor_documento", 
        "complemento_documento", 
        
        "razon_social", 
        "email", 
    ];
}
