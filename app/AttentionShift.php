<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class AttentionShift extends Model
{
    protected $table = 'attention_shift';
    protected $fillable = [
       "reference_nro", "employee_id", "user_id", "customer_id", "customer_name", "status", "company_id"
    ];

    protected static function booted()
    {
        static::addGlobalScope('company', function (Builder $builder) {
            try {
                if (Auth::check() && Schema::hasColumn('attention_shift', 'company_id')) {
                    $companyId = Auth::user()->company_id;
                    if ($companyId) {
                        $builder->where('attention_shift.company_id', $companyId);
                    }
                }
            } catch (\Exception $e) {}
        });

        static::creating(function ($model) {
            if (Auth::check() && empty($model->company_id)) {
                $model->company_id = Auth::user()->company_id;
            }
        });
    }

    public function employee()
    {
        return $this->belongsTo('App\Employee');
    }
}
