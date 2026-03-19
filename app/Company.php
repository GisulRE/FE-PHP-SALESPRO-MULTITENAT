<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class Company extends Model
{
    public function users()
    {
        return $this->hasMany('App\User');
    }
}
