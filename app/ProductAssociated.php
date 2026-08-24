<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class ProductAssociated extends Model
{
    protected $table = 'product_associated';
  protected $fillable  = ['product_courtesy_id', 'product_associated_id', 'company_id'];
    public $timestamps = false;

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

    public function productAssociated(){
      return $this->belongsTo('App\Product');
    }
}
