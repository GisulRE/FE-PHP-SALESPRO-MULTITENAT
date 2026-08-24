<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class CustomerCompany extends Model
{

    protected $table = 'customer_company';
    protected $fillable = [
        "customer_id",
        "fullname",
        "company_name",
        "phone",
        "telephone",
        "address",
        "lat",
        "lon",
        "description",
        "url_custom",
        "is_active",
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

    public function customer()
    {
        return $this->belongsTo('App\Customer');
    }
}