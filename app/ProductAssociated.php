<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductAssociated extends Model
{
    protected $table = 'product_associated';
    protected $fillable  = ['product_courtesy_id', 'product_associated_id'];
    public $timestamps = false;

    public function productAssociated(){
      return $this->belongsTo('App\Product');
    }
}
