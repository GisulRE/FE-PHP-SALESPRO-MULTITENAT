<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class Coupon extends Model
{
    protected $fillable = [
        "code", "type", "amount", "minimum_amount", "user_id", "quantity", "used", "expired_date", "is_active", "company_id"
    ];

    protected static function booted()
    {
        static::addGlobalScope('company', function (Builder $builder) {
            try {
                if (Auth::check() && Schema::hasColumn('coupons', 'company_id')) {
                    $companyId = Auth::user()->company_id;
                    if ($companyId) {
                        $builder->where('coupons.company_id', $companyId);
                    }
                }
            } catch (\Exception $e) {}
        });

        static::creating(function ($model) {
            if (Auth::check() && empty($model->company_id)) {
                $model->company_id = Auth::user()->company_id;
            }
        });
    }
}
