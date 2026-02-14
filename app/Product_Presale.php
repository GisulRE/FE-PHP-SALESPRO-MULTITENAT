<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product_Presale extends Model
{
    protected $table = 'product_pre_sale';
    protected $fillable =[
        "presale_id", "product_id", "category_id", "variant_id", "employee_id", "qty", "sale_unit_id", "net_unit_price", "discount", "tax_rate", "tax", "total"
    ];

    public function employee()
    {
    	return $this->belongsTo('App\Employee');
    }

    public function product()
    {
    	return $this->belongsTo('App\Product');
    }
}
