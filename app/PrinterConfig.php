<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PrinterConfig extends Model
{
    protected $table = 'printers';
    protected $fillable =[
        "name", "printer", "host_address", "type", "category_id", "status"
    ];
}
