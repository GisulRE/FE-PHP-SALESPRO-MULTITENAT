<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Auth;

class Returns extends Model
{
	protected $table = 'returns';
    protected $fillable =[
        "reference_no", "user_id", "customer_id", "customer_sale_id", "cuf", "warehouse_id",
        "biller_id", "account_id", "item", "total_qty", "total_discount", "total_tax", "total_price",
        "order_tax_rate", "order_tax", "grand_total", "document", "return_note", "staff_note", "is_active",
        "company_id"
    ];

    protected static function boot()
    {
        parent::boot();

        $table = (new static)->getTable();
        if (Schema::hasColumn($table, 'company_id')) {
            static::addGlobalScope('company', function (Builder $builder) {
                if (auth()->check()) {
                    $builder->where($builder->getModel()->getTable() . '.company_id', auth()->user()->company_id);
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
}
