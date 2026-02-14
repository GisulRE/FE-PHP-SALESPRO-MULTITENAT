<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product_Sale extends Model
{
    protected $table = 'product_sales';
    protected $fillable = [
        "sale_id",
        "product_id",
        "category_id",
        "variant_id",
        "employee_id",
        "cost",
        "qty",
        "sale_unit_id",
        "net_unit_price",
        "discount",
        "tax_rate",
        "tax",
        "total",
        "description"
    ];

    public function employee()
    {
        return $this->belongsTo('App\Employee');
    }

    public function product()
    {
        return $this->belongsTo('App\Product');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }
}
