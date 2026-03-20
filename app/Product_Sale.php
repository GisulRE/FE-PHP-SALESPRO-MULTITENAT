<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

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
        "description",
        "company_id"
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
