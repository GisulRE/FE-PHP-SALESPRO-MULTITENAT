<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShiftEmployee extends Model
{
    protected $table = 'shift_employee';
    protected $fillable = [
        "employee_id", "status", "position"
    ];

    public function employee()
    {
        return $this->belongsTo('App\Employee');
    }
}
