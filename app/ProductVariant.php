<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'variant_id', 'position', 'item_code', 'additional_price', 'qty', 'company_id'];

    protected static function boot()
    {
        parent::boot();

        $table = (new static)->getTable();
        if (Schema::hasColumn($table, 'company_id')) {
            static::addGlobalScope('company', function (Builder $builder) use ($table) {
                if (auth()->check()) {
                    $builder->where($table . '.company_id', auth()->user()->company_id);
                }
            });

            static::creating(function ($model) {
                if (auth()->check() && empty($model->company_id)) {
                    $model->company_id = auth()->user()->company_id;
                }
            });
        }
    }

    public function scopeFindExactProduct($query, $product_id, $variant_id)
    {
    	return $query->where([
            ['product_id', $product_id],
            ['variant_id', $variant_id]
        ]);
    }

    public function scopeFindExactProductWithCode($query, $product_id, $item_code)
    {
    	return $query->where([
            ['product_id', $product_id],
            ['item_code', $item_code],
        ]);
    }
}
