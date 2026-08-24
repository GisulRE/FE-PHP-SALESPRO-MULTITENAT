<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class Biller_Warehouses extends Model
{
    protected $table = 'biller_warehouses';

    protected $fillable = [
        "biller_id",
        "warehouse_id",
        "company_id"
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
                }
            });
        }
    }

    public function biller()
    {
        return $this->belongsTo('App\Biller');
    }
    public function warehouse()
    {
        return $this->belongsTo('App\Warehouse');
    }
}