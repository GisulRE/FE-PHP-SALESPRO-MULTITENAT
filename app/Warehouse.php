<?php

namespace App;

use App\SiatSucursal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Warehouse extends Model
{
    protected $fillable = [
        "name", "phone", "email", "address", "is_active",
        "sucursal_id", "sucursal_siat", "company_id"
    ];

    public function product()
    {
        return $this->hasMany('App\Product');
    }

    public function sucursal()
    {
        return $this->belongsTo(SiatSucursal::class, 'sucursal_id', 'id');
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
