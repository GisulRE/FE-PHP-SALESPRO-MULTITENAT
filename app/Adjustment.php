<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Adjustment extends Model
{
    protected $fillable =[
        "reference_no", "warehouse_id", "document", "total_qty", "item", 
        "note", "company_id"   
    ];

    public function warehouse()
    {
        return $this->belongsTo('App\Warehouse');
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('company', function (Builder $builder) {
            if (auth()->check() && auth()->user()->company_id) {
                $builder->where('adjustments.company_id', auth()->user()->company_id);
            }
        });

        static::creating(function ($model) {
            if (auth()->check() && empty($model->company_id)) {
                $model->company_id = auth()->user()->company_id;
            }
        });
    }
}
