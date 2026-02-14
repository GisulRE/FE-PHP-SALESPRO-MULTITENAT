<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Receivable extends Model
{
    //payment_with_receivable
    protected $table = 'payment_with_receivable';

    protected $fillable =[

        "account_id", "user_id", "amount", "sales", "status"
    ];
}
