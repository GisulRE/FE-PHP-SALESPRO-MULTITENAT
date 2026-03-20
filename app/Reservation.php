<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Reservation extends Model
{
  protected $table = 'reservations';

  protected $fillable = [
    'name',
    'phone',
    'email',
    'product_id',
    'sucursal_id',
    'employee_id',
    'reserved_date',
    'reserved_time',
    'duration_minutes',
    'status',
    'notes',
    'company_id'
  ];

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

  public function product()
  {
    return $this->belongsTo(Product::class, 'product_id');
  }

  public function warehouse()
  {
    return $this->belongsTo(Warehouse::class, 'sucursal_id');
  }

  public function employee()
  {
    return $this->belongsTo(\App\Employee::class, 'employee_id');
  }
}
