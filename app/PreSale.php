<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PreSale extends Model
{
    protected $table = 'pre_sale';

    protected $fillable = [
        "reference_no", "user_id", "customer_id", "warehouse_id", "employee_id", "attentionshift_id", "item",
        "total_qty", "grand_total", "order_discount", "total_discount", "shipping_cost", "tips", "status"
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

    public function attentionshift()
    {
    	return $this->belongsTo('App\AttentionShift');
    }

}
