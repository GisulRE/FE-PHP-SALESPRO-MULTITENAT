<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Transfer extends Model
{
    protected $fillable = [
        "reference_no",
        "user_id",
        "status",
        "from_warehouse_id",
        "to_warehouse_id",
        "item",
        "total_qty",
        "total_tax",
        "total_cost",
        "shipping_cost",
        "grand_total",
        "document",
        "note",
        "company_id",
    ];

    public function fromWarehouse()
    {
        return $this->belongsTo('App\Warehouse', 'from_warehouse_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo('App\Warehouse', 'to_warehouse_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
    public function items()
    {
        return $this->hasMany(ProductTransfer::class, 'transfer_id', 'id');
    }
    public function logs()
    {
        return $this->hasMany(TransferRequestLog::class, 'transfer_id');
    }
    public function biller()
    {
        return $this->belongsTo(Biller::class, 'biller_id');
    }

    protected static function boot()
    {
        parent::boot();

        $table = (new static)->getTable();
        if (Schema::hasColumn($table, 'company_id')) {
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
}
