<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cashier extends Model
{
    protected $table = 'cashier';
    protected $fillable =[
        "account_id", "note", "amount_start", "amount_end", "is_active", "start_date", "end_date"
    ];
}
