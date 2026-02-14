<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
    'notes'
  ];

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
