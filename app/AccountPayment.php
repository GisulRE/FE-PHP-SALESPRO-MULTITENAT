<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccountPayment extends Model
{
    protected $table = 'account_method_pay';
    protected $fillable =[
        "account_id", "methodpay_id", "is_active"
    ];

    public function account()
    {
    	return $this->belongsTo('App\Account');
    }

    public function methodpayment()
    {
    	return $this->belongsTo('App\MethodPayment');
    }
}
