<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        "account_no",
        "name",
        "initial_balance",
        "total_balance",
        "note",
        "is_default",
        "is_active",
        "type"
    ];

    public function product()
    {
        return $this->belongsTo('App\Product', 'account_id', 'id');
    }

}
