<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdjustmentAccount extends Model
{
    protected $table = 'adjustment_accounts';
    protected $fillable =[
        "reference_no", "account_id", "note", "amount", "type_adjustment", "is_active"
    ];
}
