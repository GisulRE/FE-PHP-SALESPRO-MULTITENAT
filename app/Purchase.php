<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class Purchase extends Model
{
    protected $fillable =[

        "reference_no", "user_id", "warehouse_id", "supplier_id", "item", "total_qty", 
        "total_discount", "total_tax", "total_cost", "order_tax_rate", "order_tax", 
        "order_discount", "shipping_cost", "grand_total","paid_amount", "status", 
        "payment_status", "document", "note", "created_at", "company_id"
    ];

    public function supplier()
    {
    	return $this->belongsTo('App\Supplier');
    }

    public function warehouse()
    {
    	return $this->belongsTo('App\Warehouse');
    }

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
