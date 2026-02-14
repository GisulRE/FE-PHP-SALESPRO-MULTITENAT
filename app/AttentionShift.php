<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AttentionShift extends Model
{
    protected $table = 'attention_shift';
    protected $fillable = [
       "reference_nro", "employee_id", "user_id", "customer_id", "customer_name", "status"
    ];
    public function employee()
    {
    	return $this->belongsTo('App\Employee');
    }
}
