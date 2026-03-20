<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class PreSale extends Model
{
    protected $table = 'pre_sale';

    protected $fillable = [
        "reference_no", "user_id", "customer_id", "warehouse_id", "employee_id", "attentionshift_id", "item",
        "total_qty", "grand_total", "order_discount", "total_discount", "shipping_cost", "tips", "status", "company_id"
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

    public function biller()
    {
        return $this->belongsTo('App\Biller');
    }

    public function customer()
    {
        return $this->belongsTo('App\Customer');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Warehouse');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function attentionshift()
    {
    	return $this->belongsTo('App\AttentionShift');
    }

}
