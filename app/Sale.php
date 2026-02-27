<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class Sale extends Model
{
    protected $fillable =[
        "reference_no", "user_id", "customer_id", "warehouse_id", "biller_id", "item", 
        "total_qty", "total_discount", "total_tax", "total_price", "order_tax_rate", 
        "order_tax", "order_discount","coupon_id", "coupon_discount", "shipping_cost", 
        "grand_total", "sale_status", "payment_status", "paid_amount", "document", 
        "sale_note", "staff_note", "date_sell", "total_tips", "invoice_no", "company_id"
    ];

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

    public function productSales()
    {
        return $this->hasMany('App\Product_Sale', 'sale_id', 'id');
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
