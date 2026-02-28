<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class Account extends Model
{
    protected $fillable = [
        "account_no",
        "name",
        "initial_balance",
        "total_balance",
        "note",
        "is_default",
        "is_active",
        "type",
        "company_id"
    ];

    public function product()
    {
        return $this->belongsTo('App\Product', 'account_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();

        $table = (new static)->getTable();
        if (Schema::hasColumn($table, 'company_id')) {
            static::addGlobalScope('company', function (Builder $builder) {
                if (auth()->check()) {
                    $builder->where('accounts.company_id', auth()->user()->company_id);
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
