<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tip extends Model
{
    protected $table = 'tip';
    protected $fillable =[
        "sale_id", "presale_id", "employee_id", "amount"
    ];

    public function employee()
    {
        return $this->belongsTo('App\Sale');
    }
}
