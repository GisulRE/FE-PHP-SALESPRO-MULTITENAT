<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable =[

        "name", "code", "type", "barcode_symbology", "brand_id", "category_id", "unit_id", "purchase_unit_id", "sale_unit_id", "cost",
        "price", "price_a", "price_b", "price_c", "qty", "alert_quantity", "promotion", "promotion_price", "starting_date", "last_date",
        "tax_id", "tax_method", "image", "file", "is_variant", "featured", "product_list", "qty_list", "price_list", "is_pricelist", 
        "product_details", "is_active", "courtesy", "permanent", "starting_date_courtesy", "ending_date_courtesy", "courtesy_clearance_price",
        "commission_percentage", "codigo_actividad", "codigo_producto_servicio", "is_basicservice", "account_id"
    ];

    public function category()
    {
    	return $this->belongsTo('App\Category');
    }

    public function brand()
    {
    	return $this->belongsTo('App\Brand');
    }

    public function unit()
    {
        return $this->belongsTo('App\Unit');
    }

    public function variant()
    {
        return $this->belongsToMany('App\Variant', 'product_variants')->withPivot('id', 'item_code', 'additional_price');
    }

    public function account()
    {
        return $this->belongsTo('App\Account', 'account_id');
    }


    public function scopeActiveStandard($query)
    {
        return $query->where([
            ['is_active', true],
            ['type', 'standard']
        ]);
    }

    public function scopeActiveInsumo($query)
    {
        return $query->where([
            ['is_active', true],
            ['type', 'insumo']
        ]);
    }

    public function scopeActiveFeatured($query)
    {
        return $query->where([
            ['is_active', true],
            ['featured', 1],
        ]);
    }

    public function scopeActiveServiceBasic($query)
    {
        return $query->where([
            ['is_active', true],
            ['is_basicservice', true],
        ]);
    }
}
