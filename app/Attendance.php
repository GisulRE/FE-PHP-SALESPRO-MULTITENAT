<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable =[
        "date", "employee_id", "user_id",
        "checkin", "checkout", "status", "note"
    ];

    public function employee()
    {
    	return $this->belongsTo('App\Employee');
    }

    public function user()
    {
    	return $this->belongsTo('App\User');
    }
}
