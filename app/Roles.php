<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Roles extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        "name",
        "description",
        "guard_name",
        "is_active",
        "blocked_modules",
        "company_id"
    ];

    public function company()
    {
        return $this->belongsTo('App\Company');
    }

    protected static function boot()
    {
        parent::boot();

        $table = (new static)->getTable();
        static::addGlobalScope('company', function (Builder $builder) use ($table) {
            if (auth()->check() && auth()->user()->company_id) {
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
