<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class Unit extends Model
{
    protected $fillable = [
        "unit_code", "unit_name", "base_unit", "operator", "operation_value", "is_active",
        "codigo_clasificador_siat", "company_id"
    ];

    public function product()
    {
        return $this->hasMany('App\Product');
    }

    protected static function boot()
    {
        parent::boot();

        $table = (new static)->getTable();
        if (Schema::hasColumn($table, 'company_id')) {
            static::addGlobalScope('company', function (Builder $builder) {
                if (auth()->check()) {
                    $builder->where('company_id', auth()->user()->company_id);
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
