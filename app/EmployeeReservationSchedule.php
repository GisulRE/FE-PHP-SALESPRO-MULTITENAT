<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use App\Employee;

class EmployeeReservationSchedule extends Model
{
    protected $fillable = [
        'employee_id',
        'company_id',
        'day_of_week',
        'is_enabled',
        'start_time',
        'end_time',
        'interval_minutes',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        $table = (new static)->getTable();
        if (true) {
            static::addGlobalScope('company', function (Builder $builder) use ($table) {
                if (auth()->check()) {
                    $builder->where($table . '.company_id', auth()->user()->company_id);
                }
            });

            static::creating(function ($model) {
                if (auth()->check() && empty($model->company_id)) {
                    $model->company_id = auth()->user()->company_id;
                } elseif (empty($model->company_id) && !empty($model->employee_id)) {
                    $model->company_id = Employee::withoutGlobalScopes()
                        ->where('id', $model->employee_id)
                        ->value('company_id');
                }
            });
        }
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
