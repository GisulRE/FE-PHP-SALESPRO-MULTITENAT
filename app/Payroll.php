<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Payroll extends Model
{
    protected $fillable = [
        "reference_no", "employee_id", "account_id", "user_id",
        "amount", "paying_method", "note", "company_id"
    ];

    public function employee()
    {
        return $this->belongsTo('App\Employee');
    }

    public function account()
    {
        return $this->belongsTo('App\Account');
    }

    protected static function boot()
    {
        parent::boot();

        $table = (new static)->getTable();
        if (Schema::hasColumn($table, 'company_id')) {
            static::addGlobalScope('company', function (Builder $builder) use ($table) {
                if (auth()->check()) {
                    $builder->where($table . '.company_id', auth()->user()->company_id);
                }
            });

            static::creating(function ($model) {
                if (auth()->check() && empty($model->company_id)) {
                    $model->company_id = auth()->user()->company_id;
                }
            });
        }
    }
}
