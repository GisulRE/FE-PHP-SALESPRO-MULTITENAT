<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class Payment extends Model
{
    protected $fillable = [
        "purchase_id", "user_id", "sale_id", "account_id", "payment_reference",
        "amount", "change", "paying_method", "payment_note", "company_id"
    ];

    protected static function boot()
    {
        parent::boot();

        $table = (new static)->getTable();
        if (Schema::hasColumn($table, 'company_id')) {
            static::addGlobalScope('company', function (Builder $builder) {
                if (auth()->check()) {
                    $builder->where($builder->getModel()->getTable() . '.company_id', auth()->user()->company_id);
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
