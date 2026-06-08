<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class Company extends Model
{
    protected $fillable = [
        'name',
        'nit',
    ];

    public function users()
    {
        return $this->hasMany('App\User');
    }

    public function setNitAttribute($value)
    {
        $this->attributes['nit'] = trim((string) $value);
    }
}
