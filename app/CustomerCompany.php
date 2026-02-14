<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
        "is_active"
    ];

    public function customer()
    {
        return $this->belongsTo('App\Customer');
    }
}