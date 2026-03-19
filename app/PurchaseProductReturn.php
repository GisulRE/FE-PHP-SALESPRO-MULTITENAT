<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class PurchaseProductReturn extends Model
{
    protected $table = 'purchase_product_return';
    protected $fillable =[
        "return_id", "product_id", "variant_id", "qty", "purchase_unit_id", "net_unit_cost", "discount", "tax_rate", "tax", "total", "company_id"
    ];

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
}
